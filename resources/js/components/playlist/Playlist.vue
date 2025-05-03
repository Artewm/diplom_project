<template>
   <div class="w-full h-full bg-black rounded-md p-4 flex flex-col">
    <div v-if="loading" class="flex items-center justify-center h-full">
      <!-- <div class="text-white">Загрузка плейлиста...</div> -->
    </div>
    <div v-else-if="error" class="flex items-center justify-center h-full">
      <div class="text-red-400">{{ error }}</div>
    </div>
    <div v-else class="flex flex-col h-full playlist-header w-full rounded-t-md">
        <div class="flex items-center justify-start p-4 bg-transparent rounded-t-lg">
            <div class="flex items-center justify-start w-auto ml-4">
                <img :src="coverImage" alt="playlist" class="w-64 bg-spotify-black p-2 shadow-xl shadow-spotify-black/70 rounded-md hover:shadow-2xl hover:shadow-spotify-black/90 transition-shadow duration-300">
            </div>
            <div class="flex items-center justify-start">
                <!-- тут название приватный или публичный и количество треков -->
                <div class="flex flex-col items-start justify-between ml-4 gap-4">
                    <span class="text-white text-xl font-roboto">{{ playlist.is_public ? 'Открытый плейлист' : 'Приватный плейлист' }}</span>
                    <div class="flex items-center gap-4">
                      <span class="text-white text-4xl font-bold font-roboto">{{ playlist.name }}</span>
                      <button @click="showEditModal = true" class="text-gray-400 hover:text-white">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"></path>
                        </svg>
                      </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-white text-sm font-roboto">Количество треков: {{ tracks.length }}</span>
                        <span class="text-white text-sm font-roboto ml-4">Длительность: {{ totalDuration }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="playlist-gradient flex flex-col w-full h-full">
            <div class="flex items-center justify-start bg-transparent p-4 backdrop-opacity-10 backdrop-blur-sm">
              <div class="flex items-center justify-between w-full ml-4 gap-4">
                  <button class="bg-spotify-green p-2 rounded-full">
                      <img :src="playTrackIcon" alt="play" class="w-6 h-6">
                  </button>
                  <div class="flex items-center justify-start w-auto ml-4 gap-4">
                  <button 
                    @click="toggleVisibility" 
                    class="text-white text-sm font-roboto bg-spotify-black p-2 rounded-md hover:bg-spotify-gray transition-colors duration-300">
                      <span>{{ playlist.is_public ? 'Сделать приватным' : 'Сделать публичным' }}</span>
                  </button>
                  <button 
                    @click="showAddTracksModal = true" 
                    class="text-white text-sm font-roboto bg-spotify-green p-2 rounded-md hover:bg-opacity-80 transition-colors duration-300">
                      <img :src="addIcon" alt="add" class="w-5 h-5">
                  </button>
                  <button 
                    @click="deletePlaylist" 
                    class="text-white text-sm font-roboto bg-red-600 p-2 rounded-md hover:bg-red-700 transition-colors duration-300">
                      <img :src="deleteIcon" alt="delete" class="w-5 h-5">
                  </button>
                  </div>
              </div>
            </div>
            <div class="flex-grow bg-transparent overflow-y-auto border-t border-spotify-gray backdrop-blur-sm">
                <TrackList 
                  class="w-full h-full" 
                  :tracks="tracks" 
                  @remove-track="removeTrack" 
                  :is-playlist="true" 
                  :can-remove-tracks="isPlaylistOwner"
                />
            </div>
        </div>
    </div>
    
    <!-- Модальное окно редактирования плейлиста -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="showEditModal = false"></div>
      <div class="flex flex-col items-center justify-between w-1/4 bg-zinc-800 rounded-lg z-10 p-6">
        <h2 class="text-xl font-bold mb-6">Редактировать плейлист</h2>
        
        <div v-if="editError" class="w-full p-2 mb-4 bg-red-500/20 text-red-500 rounded-md text-center">
          {{ editError }}
        </div>
        
        <div class="w-full mb-6">
          <label class="block text-sm font-medium text-gray-300 mb-2">Название плейлиста</label>
          <input 
            v-model="editedName" 
            type="text" 
            placeholder="Название плейлиста" 
            class="w-full p-2 rounded-md bg-transparent border-2 border-gray-600">
        </div>
        
        <div class="flex items-center justify-end w-full gap-4">
          <button 
            @click="showEditModal = false"
            class="px-4 py-2 rounded-md bg-transparent border border-gray-600 text-gray-300 hover:bg-gray-700 transition-colors">
            Отмена
          </button>
          <button 
            @click="updatePlaylist"
            :disabled="editLoading || !editedName.trim()"
            class="px-4 py-2 rounded-md bg-green-500 text-white hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <span v-if="editLoading">Сохранение...</span>
            <span v-else>Сохранить</span>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Модальное окно добавления треков -->
    <PlaylistTrackAdder
      v-if="showAddTracksModal"
      :playlist-id="playlist.id"
      @close="closeAddTracksModal"
      @track-added="refreshPlaylist"
    />
   </div>
</template>
  
<script>
import PlayIcon from '../../../images/baseMusic.png'
import playTrack from '../../../images/play.png'
import { ref, onMounted, computed, watch, inject } from 'vue'
import TrackList from '../tracks/TrackList.vue'
import PlaylistTrackAdder from './PlaylistTrackAdder.vue'
import playlistService from '../../services/playlist'
import { useRoute, useRouter } from 'vue-router'
import mitt from 'mitt'
import deleteIcon from '../../../images/delete.png'
import addIcon from '../../../images/add.png'

// Создаем экземпляр шины событий если её еще нет
const emitter = window.emitter || (window.emitter = mitt())

export default {
  components: {
    TrackList,
    PlaylistTrackAdder
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const playlist = ref({});
    const tracks = ref([]);
    const loading = ref(true);
    const error = ref(null);
   
    
    // Получаем данные о текущем пользователе
    const currentUser = inject('currentUser');
    
    // Состояние для редактирования
    const showEditModal = ref(false);
    const editedName = ref('');
    const editLoading = ref(false);
    const editError = ref(null);
    
    // Состояние для добавления треков
    const showAddTracksModal = ref(false);
    
    // Вычисляемое значение для обложки плейлиста
    const coverImage = computed(() => {
      return playlist.value.cover_image || PlayIcon;
    });
    
    // Вычисляемое значение для общей длительности плейлиста
    const totalDuration = computed(() => {
      const seconds = tracks.value.reduce((total, track) => total + (track.duration || 0), 0);
      const minutes = Math.floor(seconds / 60);
      const remainingSeconds = seconds % 60;
      return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    });
    
    // Проверка, является ли текущий пользователь владельцем плейлиста
    const isPlaylistOwner = computed(() => {
      if (!currentUser.value || !playlist.value.user_id) return false;
      return currentUser.value.sub === playlist.value.user_id.toString();
    });
    
    // Получение данных плейлиста
    const fetchPlaylist = async (id) => {
      loading.value = true;
      error.value = null;
      
      try {
        const playlistData = await playlistService.getPlaylist(id);
        playlist.value = playlistData;
        tracks.value = playlistData.tracks || [];
        editedName.value = playlistData.name;
      } catch (err) {
        console.error('Ошибка при загрузке плейлиста:', err);
        error.value = 'Не удалось загрузить плейлист';
      } finally {
        loading.value = false;
      }
    };
    
    // Переключение приватности плейлиста
    const toggleVisibility = async () => {
      try {
        const updatedPlaylist = await playlistService.updatePlaylist(playlist.value.id, {
          is_public: !playlist.value.is_public
        });
        playlist.value = updatedPlaylist;
      } catch (err) {
        console.error('Ошибка при изменении приватности плейлиста:', err);
      }
    };
    
    // Обновление плейлиста
    const updatePlaylist = async () => {
      if (!editedName.value.trim()) {
        editError.value = 'Введите название плейлиста';
        return;
      }
      
      editLoading.value = true;
      editError.value = null;
      
      try {
        const updatedPlaylist = await playlistService.updatePlaylist(playlist.value.id, {
          name: editedName.value.trim()
        });
        
        playlist.value = updatedPlaylist;
        showEditModal.value = false;
      } catch (err) {
        console.error('Ошибка при обновлении плейлиста:', err);
        editError.value = 'Не удалось обновить плейлист';
      } finally {
        editLoading.value = false;
      }
    };
    
    // Удаление плейлиста
    const deletePlaylist = async () => {
      if (!confirm('Вы действительно хотите удалить этот плейлист?')) {
        return;
      }
      
      try {
        await playlistService.deletePlaylist(playlist.value.id);
        // Отправляем событие об удалении плейлиста
        emitter.emit('playlist-deleted', playlist.value.id);
        router.push('/');
      } catch (err) {
        console.error('Ошибка при удалении плейлиста:', err);
        alert('Не удалось удалить плейлист');
      }
    };
    
    // Удаление трека из плейлиста
    const removeTrack = async (trackId) => {
      try {
        await playlistService.removeTrackFromPlaylist(playlist.value.id, trackId);
        tracks.value = tracks.value.filter(track => track.id !== trackId);
      } catch (err) {
        console.error('Ошибка при удалении трека из плейлиста:', err);
      }
    };
    
    // Закрытие модального окна добавления треков и обновление плейлиста
    const closeAddTracksModal = () => {
      showAddTracksModal.value = false;
    };
    
    // Обновление данных плейлиста
    const refreshPlaylist = () => {
      if (playlist.value.id) {
        fetchPlaylist(playlist.value.id);
      }
    };
    
    // Следим за изменением ID плейлиста в URL
    watch(() => route.params.id, (newId) => {
      if (newId) {
        fetchPlaylist(newId);
      }
    }, { immediate: true });
    
    onMounted(() => {
      if (route.params.id) {
        fetchPlaylist(route.params.id);
      }
    });
    
    return {
      playlist,
      tracks,
      coverImage,
      playIcon: PlayIcon,
      playTrackIcon: playTrack,
      totalDuration,
      loading,
      error,
      showEditModal,
      editedName,
      editLoading,
      editError,
      showAddTracksModal,
      toggleVisibility,
      updatePlaylist,
      deletePlaylist,
      removeTrack,
      closeAddTracksModal,
      refreshPlaylist,
      isPlaylistOwner,
      deleteIcon,
      addIcon
    }
  }
}
</script>
  
<style scoped>
.playlist-gradient {
  background: linear-gradient(to bottom, #202020,  #0c0c0c, #070707);
}
.playlist-header {
  background: linear-gradient(to bottom, #333333,  #121212, #121212);
}
</style>
  