<template>
  <div class="w-full h-full bg-black pr-4 pl-4 pt-4 ">
  <div class="w-full h-full bg-gradient-to-b rounded-lg from-indigo-900 to-black  flex flex-col">
    <div v-if="loading" class="flex items-center justify-center h-full">
      <div class="text-white">Загрузка избранных треков...</div>
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-spotify-green ml-4"></div>
    </div>
    <div v-else-if="error" class="flex items-center justify-center h-full">
      <div class="text-red-400">{{ error }}</div>
    </div>
    <div v-else-if="tracks.length === 0" class="flex items-center justify-center h-full">
      <div class="text-gray-400">У вас пока нет избранных треков</div>
    </div>
    <div v-else class="flex flex-col h-full w-full">
      <!-- Верхняя информация о плейлисте -->
      <div class="flex items-start gap-6 mb-8 p-4 headerFavoritesMb">
        <div class="w-64 h-64 liked-songs-gradient rounded-lg shadow-xl flex items-center justify-center heart-icon-container headerFavoritesMbIcon">
          <svg width="100" height="100" viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 3.17647C7.0253 1.94647 5.19012 1.52647 3.66012 2.38235C2.13012 3.23823 1.49717 5.05647 2.0853 6.55823C2.67344 8.06 6.04295 11.36 8 12C9.95705 11.36 13.3266 8.06 13.9147 6.55823C14.5028 5.05647 13.8699 3.23823 12.3399 2.38235C10.8099 1.52647 8.9747 1.94647 8 3.17647Z" fill="white"/>
          </svg>
        </div>
        <div class="flex flex-col justify-end h-64 py-4 headerFavoritesMbTitleContainer">
          <div class="text-white text-sm font-medium mb-3">Плейлист</div>
          <h1 class="text-white text-7xl font-bold mb-6 headerFavoritesMbTitle">Любимые треки</h1>
          <div class="flex items-center text-white text-sm opacity-80">
            <span>{{ username }}</span>
            <span class="mx-1">•</span>
            <span>{{ tracks.length }} {{ getNounForm(tracks.length, 'трек', 'трека', 'треков') }}</span>
          </div>
        </div>
      </div>
      
      <!-- Кнопки управления и таблица треков -->
      <div class="w-full  bg-black bg-opacity-10 backdrop-blur-lg rounded-md pb-4 h-full p-4">
        <div class="flex items-center gap-6 pl-4 pb-2">
          <button class="bg-spotify-green hover:bg-spotify-green-light p-2 rounded-full shadow-xl transform transition-transform hover:scale-105">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
              <path d="M8 5v14l11-7z" fill="white"/>
            </svg>
          </button>
        </div>
        
        <div class="flex-grow overflow-y-auto overflow-x-auto pb-28">
          <div style="max-height: 60vh; min-height: 200px; overflow-y: auto; padding: 1rem;">
            <TrackList 
              class="w-full" 
              :tracks="tracks" 
              @remove-from-favorites="removeFromFavorites" 
              :show-remove-from-favorites="true"
              :show-add-to-favorites="false"
              :favorites="tracks"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>
 
<script>
import playTrack from '../../../images/play.png'
import { ref, onMounted, computed, watch, inject } from 'vue'
import TrackList from '../tracks/TrackList.vue'
import favoritesService from '../../services/favorites'
import { useRouter } from 'vue-router'
import mitt from 'mitt'

// Создаем экземпляр шины событий если её еще нет
const emitter = window.emitter || (window.emitter = mitt())

export default {
 components: {
   TrackList
 },
 setup() {
   const router = useRouter();
   const tracks = ref([]);
   const loading = ref(true);
   const error = ref(null);
   
   // Получение данных пользователя
   const currentUser = inject('currentUser');
   
   // Вычисляемое значение для имени пользователя
   const username = computed(() => {
     return currentUser.value?.name || 'Пользователь';
   });
   
   // Функция для склонения существительных в зависимости от числа
   const getNounForm = (number, one, two, five) => {
     number = Math.abs(number);
     number %= 100;
     if (number >= 5 && number <= 20) {
       return five;
     }
     number %= 10;
     if (number === 1) {
       return one;
     }
     if (number >= 2 && number <= 4) {
       return two;
     }
     return five;
   };
   
   // Вычисляемое значение для общей длительности
   const totalDuration = computed(() => {
     const seconds = tracks.value.reduce((total, track) => total + (track.duration || 0), 0);
     const minutes = Math.floor(seconds / 60);
     const remainingSeconds = seconds % 60;
     return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
   });
   
   // Получение избранных треков пользователя
   const fetchFavorites = async () => {
     loading.value = true;
     error.value = null;
     
     try {
       const response = await favoritesService.getUserFavorites();
       // Если ответ приходит правильно с данными, используем их
       if (response && response.data) {
         tracks.value = response.data;
         console.log('Загружены избранные треки:', tracks.value);
       } else {
         // Если пустой ответ или неверный формат
         tracks.value = [];
         console.warn('Неверный формат данных для избранных треков:', response);
       }
     } catch (err) {
       console.error('Ошибка при загрузке избранных треков:', err);
       error.value = 'Не удалось загрузить избранные треки: ' + (err.message || 'Неизвестная ошибка');
       tracks.value = []; // Очищаем треки при ошибке
     } finally {
       loading.value = false;
     }
   };
   
   // Удаление трека из избранного
   const removeFromFavorites = async (trackId) => {
     try {
       await favoritesService.removeFromFavorites(trackId);
       tracks.value = tracks.value.filter(track => track.id !== trackId);
       // Отправляем событие об удалении трека из избранного
       emitter.emit('favorite-removed', trackId);
     } catch (err) {
       console.error('Ошибка при удалении трека из избранного:', err);
     }
   };
   
   onMounted(() => {
     fetchFavorites();
     
     // Подписываемся на событие добавления трека в избранное
     emitter.on('favorite-added', (trackId) => {
       fetchFavorites();
     });
     
     // Выводим отладочную информацию
     console.log('Favorites component mounted, loading:', loading.value);
   });
   
   // Отслеживаем изменения в треках для отладки
   watch(tracks, (newTracks) => {
     console.log('Tracks updated in Favorites component:', newTracks);
   });
   
   return {
     tracks,
     username,
     playTrackIcon: playTrack,
     totalDuration,
     loading,
     error,
     removeFromFavorites,
     getNounForm
   }
 }
}
</script>
 
<style scoped>
/* Дополнительные стили если потребуются */
.heart-icon-container {
  position: relative;
  overflow: hidden;
}

.heart-icon-container::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at center, rgba(255,255,255,0.2) 0%, transparent 70%);
  z-index: 1;
}

.heart-icon-container svg {
  position: relative;
  z-index: 2;
  filter: drop-shadow(0px 0px 8px rgba(255,255,255,0.5));
}

.liked-songs-gradient {
  background: linear-gradient(135deg, #450af5 0%, #8e8ee5 40%, #c4efd9 100%);
}

/* Улучшенное отображение треков */
:deep(table) {
  background: transparent;
}

:deep(tr:hover) {
  background-color: rgba(255, 255, 255, 0.1) !important;
}

:deep(th) {
  color: rgba(255, 255, 255, 0.6);
  font-weight: normal;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  font-size: 0.75rem;
}
@media (max-width: 767px) {
  .headerFavoritesMb {
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0 !important;
    padding: 0 !important;
    margin: 20px 10px !important;
  }
  .headerFavoritesMbIcon {
    width: 100px;
    height: 100px;
  }
  .headerFavoritesMbIcon svg {
    width: 50px;
    height: 50px;
  }
  .headerFavoritesMbTitle {
    font-size: 32px;
  }
  .headerFavoritesMbTitleContainer {
    margin-top: 0 !important;
    height: auto !important;
  }
}
</style>
 