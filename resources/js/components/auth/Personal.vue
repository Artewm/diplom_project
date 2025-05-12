<template>
    <div class="bg-spotify-gray rounded-md min-w-[250px] shadow-lg overflow-hidden">
        <div v-if="currentUser" class="py-4 px-4 text-white bg-gray-700 border-b border-gray-600">
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
                <UploadTrack @close="closeUploadTrack" @show-toast="showToast" />
            </div>
        </div>
    </teleport>
    
    <!-- Уведомление -->
    <div v-if="toast.show" class="fixed bottom-4 right-4 p-4 rounded-md shadow-lg z-50" :class="toastClasses">
        {{ toast.message }}
    </div>
</template>
  
<script>
import { ref, inject, computed, onMounted } from 'vue';
import addTrackIcon from '../../../images/add.png';
import deleteItemIcon from '../../../images/delete.png';
import logoutIcon from '../../../images/exit.png';
import UploadTrack from '../tracks/UploadTrack.vue';
import axios from 'axios';

export default {
    components: {
        UploadTrack
    },
    setup() {
        const isPersonalModalOpen = ref(false);
        const currentUser = inject('currentUser');
        const logoutUser = inject('logout');
        const showUploadTrack = ref(false);
        
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

        // Отладочная информация
        onMounted(() => {
            console.log('Personal.vue mounted, currentUser:', currentUser.value);
            if (currentUser.value) {
                console.log('Данные пользователя:');
                console.log('Имя:', currentUser.value.name || 'Не указано');
                console.log('Email:', currentUser.value.email || 'Не указан');
                console.log('ID:', currentUser.value.sub || 'Не указан');
            }
            fetchUserDetails();
        });

        // Состояние для хранения дополнительной информации о пользователе
        const userDetails = ref(null);

        // Функция для получения дополнительных данных о пользователе с сервера
        const fetchUserDetails = async () => {
            if (!currentUser.value || !currentUser.value.sub) {
                console.log('ID пользователя не найден в токене');
                return;
            }

            try {
                // Запрашиваем данные пользователя по ID из токена
                const userId = currentUser.value.sub;
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
            
            if (!currentUser.value) return 'Пользователь';
            
            // Проверка всех возможных полей имени
            if (currentUser.value.name) return currentUser.value.name;
            if (currentUser.value.username) return currentUser.value.username;
            if (currentUser.value.email) return currentUser.value.email;
            
            // Если нет имени, отображаем ID или роль
            return `Пользователь ${userId.value || ''}`;
        });

        // Получение email пользователя
        const displayEmail = computed(() => {
            // Если есть данные из дополнительного запроса
            if (userDetails.value && userDetails.value.email) {
                return userDetails.value.email;
            }
            
            if (!currentUser.value) return '';
            
            // Прямой доступ к email
            if (currentUser.value.email) {
                return currentUser.value.email;
            }
            
            return '';
        });

        // Получение ID пользователя из токена
        const userId = computed(() => {
            if (!currentUser.value) return '';
            
            // ID в стандартном поле JWT
            if (currentUser.value.sub) {
                return currentUser.value.sub;
            }

            // ID в нестандартном поле
            if (currentUser.value.id) {
                return currentUser.value.id;
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

        // Функции компонента
        const addTrack = () => {
            console.log('Добавить трек');
            showUploadTrack.value = true;
        };
        
        const closeUploadTrack = () => {
            showUploadTrack.value = false;
        };
        
        const deleteItem = () => {
            console.log('Удалить');
            // Здесь будет логика удаления элемента
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

        return {
            isPersonalModalOpen,
            addTrackIcon,
            deleteItemIcon,
            logoutIcon,
            currentUser,
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
            showToast
        };
    }
}
</script>
<style scoped>
</style>
  