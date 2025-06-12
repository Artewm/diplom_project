<template>
    <div class="bg-spotify-gray rounded-md min-w-[250px] shadow-lg overflow-hidden">
        <div v-if="currentUser" class="py-4 px-4 text-white bg-gray-700 border-b border-gray-600" @click="addTrack" style="cursor:pointer;">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white text-lg font-bold">
                    {{ userInitials }}
                </div>
                <div>
                    <div class="font-semibold text-lg">{{ displayName }}</div>
                    <div class="text-sm text-gray-300">{{ displayEmail }}</div>
                </div>
            </div>
        </div>
        <div v-else class="py-4 px-4 text-white bg-gray-700 border-b border-gray-600">
            <div class="text-center">
                Данные загружаются...
            </div>
        </div>
        <div class="flex items-center justify-start gap-4 py-3 px-4 text-white hover:bg-gray-200 cursor-pointer transition-colors duration-200" @click="addTrack">
            <img :src="addTrackIcon" alt="Добавить трек" class="w-5 h-5">
            <button class="text-white text-sm font-roboto" >Добавить трек</button>
        </div>
        <div class="flex items-center justify-start gap-4 py-3 px-4 text-white hover:bg-gray-200 cursor-pointer transition-colors duration-200" @click="deleteItem">
            <img :src="deleteItemIcon" alt="Удалить" class="w-5 h-5">
            Удалить
        </div>
        <div class="flex items-center justify-start gap-4 py-3 px-4 text-red-500 hover:bg-gray-200 cursor-pointer transition-colors duration-200" @click="logout">
            <img :src="logoutIcon" alt="Выйти" class="w-5 h-5">
            Выйти
        </div>
    </div>
    
    <!-- Модальное окно для загрузки трека -->
    <teleport to="body">
        <div v-if="showUploadTrack" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4" @click.self="closeUploadTrack">
            <div class="bg-spotify-gray rounded-lg p-4 max-w-2xl w-full" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-white border-b border-spotify-green  pb-1 text-xl">Загрузка трека</h2>
                    <button @click="closeUploadTrack" class="text-white text-xl">&times;</button>
                </div>
                <UploadTrack @close="closeUploadTrack" @show-toast="showToast" @track-added="onTrackAdded" />
            </div>
        </div>
    </teleport>
    
    <!-- Уведомление -->
    <div v-if="toast.show" class="fixed bottom-4 right-4 p-4 rounded-md shadow-lg z-50" :class="toastClasses">
        {{ toast.message }}
    </div>
    
    <!-- Модальное окно для удаления треков -->
    <teleport to="body">
        <div v-if="showDeleteTracksModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4" @click.self="closeDeleteTracksModal">
            <div class="bg-spotify-gray rounded-lg p-8 w-[98vw] max-w-[1400px] min-h-[500px] overflow-x-auto" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-white border-b border-spotify-green pb-1 text-xl">Удаление треков</h2>
                    <button @click="closeDeleteTracksModal" class="text-white text-xl">&times;</button>
                </div>
                <div v-if="loadingUserTracks" class="flex justify-center items-center h-32">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-spotify-green"></div>
                </div>
                <div v-else>
                    <div style="overflow-x:auto; overflow-y:auto; min-width:900px; max-height:70vh;">
                        <TrackList
                            :tracks="userTracks"
                            :canRemoveTracks="true"
                            @remove-track="handleRemoveTrack"
                        />
                    </div>
                    <div v-if="userTracks.length === 0" class="text-gray-400 text-center py-4">У вас нет загруженных треков</div>
                </div>
            </div>
        </div>
    </teleport>
</template>
  
<script>
import { ref, inject, computed, onMounted, shallowRef } from 'vue';
import addTrackIcon from '../../../images/add.png';
import deleteItemIcon from '../../../images/delete.png';
import logoutIcon from '../../../images/exit.png';
import UploadTrack from '../tracks/UploadTrack.vue';
import axios from 'axios';
import TrackList from '../tracks/TrackList.vue';
import mitt from 'mitt';

export default {
    components: {
        UploadTrack,
        TrackList
    },
    props: {
        currentUser: Object
    },
    setup(props) {
        const isPersonalModalOpen = ref(false);
        const logoutUser = inject('logout');
        const showUploadTrack = ref(false);
        const showDeleteTracksModal = ref(false);
        const userTracks = shallowRef([]);
        const loadingUserTracks = ref(false);
        
        // Состояние для уведомлений
        const toast = ref({
            show: false,
            message: '',
            type: 'success'
        });
        
        // Классы для уведомлений в зависимости от типа
        const toastClasses = computed(() => {
            return {
                'bg-green-500 text-white': toast.value.type === 'success',
                'bg-red-500 text-white': toast.value.type === 'error',
                'bg-blue-500 text-white': toast.value.type === 'info'
            };
        });

        // Глобальный emitter (если не создан)
        const emitter = window.emitter || (window.emitter = mitt());

        // Отладочная информация
        onMounted(() => {
            console.log('Personal.vue mounted, currentUser:', props.currentUser);
            if (props.currentUser) {
                console.log('Данные пользователя:');
                console.log('Имя:', props.currentUser.name || 'Не указано');
                console.log('Email:', props.currentUser.email || 'Не указан');
                console.log('ID:', props.currentUser.sub || 'Не указан');
            }
            fetchUserDetails();
            emitter.on('user-track-added', loadUserTracks);
            emitter.on('user-track-removed', loadUserTracks);
        });

        // Состояние для хранения дополнительной информации о пользователе
        const userDetails = ref(null);

        // Функция для получения дополнительных данных о пользователе с сервера
        const fetchUserDetails = async () => {
            if (!props.currentUser || !props.currentUser.sub) {
                console.log('ID пользователя не найден в токене');
                return;
            }

            try {
                // Запрашиваем данные пользователя по ID из токена
                const userId = props.currentUser.sub;
                const response = await axios.get(`/api/users/${userId}`);
                userDetails.value = response.data;
                console.log('Получены данные пользователя из БД:', userDetails.value);
            } catch (error) {
                console.error('Ошибка при получении данных пользователя:', error);
            }
        }

        // Получение имени пользователя
        const displayName = computed(() => {
            // Если есть данные из дополнительного запроса
            if (userDetails.value && userDetails.value.name) {
                return userDetails.value.name;
            }
            
            if (!props.currentUser) return 'Пользователь';
            
            // Проверка всех возможных полей имени
            if (props.currentUser.name) return props.currentUser.name;
            if (props.currentUser.username) return props.currentUser.username;
            if (props.currentUser.email) return props.currentUser.email;
            
            // Если нет имени, отображаем ID или роль
            return `Пользователь ${userId.value || ''}`;
        });

        // Получение email пользователя
        const displayEmail = computed(() => {
            // Если есть данные из дополнительного запроса
            if (userDetails.value && userDetails.value.email) {
                return userDetails.value.email;
            }
            
            if (!props.currentUser) return '';
            
            // Прямой доступ к email
            if (props.currentUser.email) {
                return props.currentUser.email;
            }
            
            return '';
        });

        // Получение ID пользователя из токена
        const userId = computed(() => {
            if (!props.currentUser) return '';
            
            // ID в стандартном поле JWT
            if (props.currentUser.sub) {
                return props.currentUser.sub;
            }

            // ID в нестандартном поле
            if (props.currentUser.id) {
                return props.currentUser.id;
            }
            
            return '';
        });

        // Получение инициалов пользователя для аватара
        const userInitials = computed(() => {
            // Для простоты - используем первую букву имени или "П" (Пользователь)
            if (displayName.value && displayName.value !== 'Пользователь') {
                return displayName.value.charAt(0).toUpperCase();
            }
            
            return 'П';
        });

        // Функция для отображения уведомлений
        const showToast = ({ message, type = 'success' }) => {
            toast.value = {
                show: true,
                message,
                type
            };
            
            // Автоматически скрываем уведомление через 3 секунды
            setTimeout(() => {
                toast.value.show = false;
            }, 3000);
        };

        // Загружаем треки пользователя при открытии модального окна
        const openDeleteTracksModal = async () => {
            showDeleteTracksModal.value = true;
            await loadUserTracks();
        };

        // Функции компонента
        const addTrack = async () => {
            console.log('Добавить трек');
            showUploadTrack.value = true;
            await loadUserTracks(); // обновляем список треков при открытии модалки
        };
        
        const closeUploadTrack = async () => {
            showUploadTrack.value = false;
            await loadUserTracks(); // обновляем после закрытия модалки
        };
        
        const deleteItem = async () => {
            await openDeleteTracksModal();
        };
        
        const logout = async () => {
            try {
                await logoutUser();
                console.log('Выход выполнен');
                // Перезагружаем страницу для обновления состояния приложения
                window.location.href = '/';
            } catch (error) {
                console.error('Ошибка при выходе:', error);
            }
        };

        const loadUserTracks = async () => {
            loadingUserTracks.value = true;
            try {
                const userId = props.currentUser?.sub || props.currentUser?.id;
                const isAdmin = props.currentUser?.email === 'admin@test.com';
                const response = await axios.get('/api/tracks');
                console.log('userTracks (raw):', response.data, 'userId:', userId, 'currentUser:', props.currentUser);
                userTracks.value = isAdmin
                    ? response.data // админ видит все треки
                    : response.data.filter(track => track.user_id == userId);
                console.log('userTracks (filtered):', userTracks.value);
            } catch (e) {
                userTracks.value = [];
            } finally {
                loadingUserTracks.value = false;
            }
        };

        const handleRemoveTrack = async (trackId) => {
            if (!confirm('Вы действительно хотите удалить этот трек?')) return;
            try {
                await axios.delete(`/api/tracks/${trackId}`);
                showToast({ message: 'Трек удалён', type: 'success' });
                emitter.emit('user-track-removed', trackId); // событие удаления
                await loadUserTracks(); // обновляем после удаления
            } catch (e) {
                showToast({ message: 'Ошибка при удалении трека', type: 'error' });
            }
        };

        const closeDeleteTracksModal = () => {
            showDeleteTracksModal.value = false;
        };

        // Обновление списка треков после добавления
        const onTrackAdded = async () => {
            emitter.emit('user-track-added'); // событие добавления
            await loadUserTracks(); // обновляем после добавления
        };

        return {
            isPersonalModalOpen,
            addTrackIcon,
            deleteItemIcon,
            logoutIcon,
            currentUser: props.currentUser,
            displayName,
            displayEmail,
            userInitials,
            userId,
            addTrack,
            deleteItem,
            logout,
            showUploadTrack,
            closeUploadTrack,
            toast,
            toastClasses,
            showToast,
            showDeleteTracksModal,
            closeDeleteTracksModal,
            userTracks,
            loadingUserTracks,
            handleRemoveTrack,
            onTrackAdded,
            openDeleteTracksModal // экспортируем новую функцию
        };
    }
}
</script>
<style scoped>
</style>
  