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
                <img :src="coverImage" alt="playlist"
                  class="w-80 h-80 bg-spotify-black p-2 rounded-md transition-shadow duration-300"
                  :style="{ boxShadow: '0 50px 300px 220px ' + coverShadow }"
                >
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
        <div class="bg-transparent flex flex-col w-full h-full">
            <div class="flex items-center zjustify-start bg-transparent p-4 backdrop-opacity-10 backdrop-blur-sm">
              <div class="flex items-center justify-between w-full ml-4 gap-4">
                  <button class="bg-spotify-green p-2 rounded-full" @click="playAll">
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
import { ref, onMounted, computed, watch, inject, nextTick } from 'vue'
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
    
    // Получаем текущий трек из плеера (через глобальный emitter или inject)
    const currentTrack = ref(null);
    const coverImage = ref(PlayIcon);
    const coverShadow = ref('rgba(0,0,0,0.5)');
    
    // Функция для получения цвета картинки
    function getDominantColor(imgUrl, cb) {
      const img = new window.Image();
      img.crossOrigin = 'Anonymous';
      img.src = imgUrl;
      img.onload = function() {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        let r=0,g=0,b=0,count=0;
        for(let i=0;i<data.length;i+=4){
          r+=data[i];g+=data[i+1];b+=data[i+2];count++;
        }
        r=Math.round(r/count);g=Math.round(g/count);b=Math.round(b/count);
        cb(`rgba(${r},${g},${b},0.6)`);
      }
    }

    function updateCover() {
      let url = PlayIcon;
      // Если currentTrack есть и у него есть cover_path или image — показываем его обложку
      if (currentTrack.value && (currentTrack.value.cover_path || currentTrack.value.image)) {
        url = currentTrack.value.cover_path ? '/storage/' + currentTrack.value.cover_path : currentTrack.value.image;
      } else if (tracks.value.length > 0 && tracks.value[0].cover_path) {
        url = '/storage/' + tracks.value[0].cover_path;
      }
      coverImage.value = url;
      // Обновить тень
      nextTick(() => getDominantColor(url, color => coverShadow.value = color));
      console.log('coverImage обновлён:', url, 'currentTrack:', currentTrack.value);
    }

    // Следим за треками и текущим треком
    watch([tracks, currentTrack], updateCover, { immediate: true });
    
    // Вычисляемое значение для общей длительности плейлиста
    const totalDuration = computed(() => {
      const seconds = tracks.value.reduce((total, track) => {
        let dur = track.duration;
        if (typeof dur === 'string') {
          if (dur.includes(':')) {
            // формат mm:ss
            const [min, sec] = dur.split(':').map(Number);
            dur = min * 60 + sec;
          } else {
            dur = parseInt(dur, 10);
          }
        }
        if (isNaN(dur) || dur == null) dur = 0;
        return total + dur;
      }, 0);
      const minutes = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${minutes}:${secs.toString().padStart(2, '0')}`;
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
    
    // Запуск всего плейлиста с первого трека
    function playAll() {
      if (tracks.value.length === 0) return;
      const playlistTracks = tracks.value.map(t => ({
        ...t,
        url: '/storage/' + t.file_path
      }));
      window.emitter.emit('play-track', {
        playlistTracks,
        index: 0
      });
    }
    
    onMounted(() => {
      if (route.params.id) {
        fetchPlaylist(route.params.id);
      }
      emitter.on('current-track-changed', (track) => {
        currentTrack.value = track;
        updateCover(); // сразу обновляем картинку
        console.log('current-track-changed:', track);
      });
    });
    
    return {
      playlist,
      tracks,
      coverImage,
      coverShadow,
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
      addIcon,
      playAll
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
  