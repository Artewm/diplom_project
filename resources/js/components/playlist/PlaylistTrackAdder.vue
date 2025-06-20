<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="flex flex-col w-1/2 min-w-80 max-h-3/4 bg-zinc-800 rounded-lg z-10">
      <div class="p-6 border-b border-gray-700">
        <h2 class="text-xl font-bold">Добавить треки в плейлист</h2>
      </div>
      
      <div class="p-4">
        <div class="relative flex ">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Поиск треков" 
            class="w-full p-3 pl-10 rounded-md bg-spotify-black border border-gray-700 text-white focus:outline-none focus:border-spotify-green"
          >
          
        </div>
      </div>
      
      <div v-if="loading" class="flex-1 flex items-center justify-center p-6">
        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-spotify-green"></div>
      </div>
      
      <div v-else-if="error" class="flex-1 flex items-center justify-center p-6 text-red-400">
        {{ error }}
      </div>
      
      <div v-else-if="filteredTracks.length === 0" class="flex-1 flex items-center justify-center p-6 text-gray-400">
        <p v-if="searchQuery">Ничего не найдено</p>
        <p v-else>Нет доступных треков для добавления</p>
      </div>
      
      <div v-else class="flex-1 overflow-y-auto p-2">
        <div 
          v-for="track in filteredTracks" 
          :key="track.id" 
          class="flex items-center justify-between p-3 hover:bg-white/5 rounded-md transition-colors"
        >
          <div class="flex items-center">
            <div class="w-12 h-12 bg-spotify-black rounded-md mr-3 flex-shrink-0">
              <img 
                :src="track.cover_path ? '/storage/' + track.cover_path : (track.image ? track.image : defaultCover)" 
                alt="Track cover" 
                class="w-full h-full object-cover rounded-md"
              >
            </div>
            <div>
              <p class="text-white font-medium">{{ track.title }}</p>
              <p class="text-gray-400 text-sm">{{ track.artist }}</p>
            </div>
          </div>
          <button 
            @click="addTrackToPlaylist(track.id)"
            :disabled="addingTrack === track.id"
            class="text-white bg-spotify-green py-1 px-3 rounded-md hover:bg-opacity-80 transition-colors disabled:opacity-50"
          >
            <span v-if="addingTrack === track.id">Добавление...</span>
            <span v-else>Добавить</span>
          </button>
        </div>
      </div>
      
      <div class="p-4 border-t border-gray-700 flex justify-end">
        <button 
          @click="$emit('close')"
          class="px-4 py-2 rounded-md bg-transparent border border-gray-600 text-gray-300 hover:bg-gray-700 transition-colors"
        >
          Закрыть
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import defaultCover from '../../../images/baseMusic.png';
import playlistService from '../../services/playlist';

export default {
  name: 'PlaylistTrackAdder',
  props: {
    playlistId: {
      type: [Number, String],
      required: true
    }
  },
  emits: ['close', 'track-added'],
  setup(props, { emit }) {
    const searchQuery = ref('');
    const tracks = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const addingTrack = ref(null); // ID трека, который в процессе добавления
    
    // Фильтрация треков по поисковому запросу
    const filteredTracks = computed(() => {
      if (!searchQuery.value) return tracks.value;
      
      const query = searchQuery.value.toLowerCase();
      return tracks.value.filter(track => 
        track.title.toLowerCase().includes(query) || 
        track.artist.toLowerCase().includes(query)
      );
    });
    
    // Загрузка всех треков
    const loadTracks = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        // Загружаем все треки
        const tracksResponse = await axios.get('/api/tracks');
        const allTracks = tracksResponse.data;
        
        // Загружаем треки, которые уже есть в плейлисте
        const playlistResponse = await playlistService.getPlaylist(props.playlistId);
        const playlistTracks = playlistResponse.tracks || [];
        
        // Получаем ID треков, которые уже в плейлисте
        const playlistTrackIds = playlistTracks.map(track => track.id);
        
        // Фильтруем треки, исключая те, которые уже в плейлисте
        tracks.value = allTracks.filter(track => !playlistTrackIds.includes(track.id));
      } catch (err) {
        console.error('Ошибка при загрузке треков:', err);
        error.value = 'Не удалось загрузить треки';
      } finally {
        loading.value = false;
      }
    };
    
    // Добавление трека в плейлист
    const addTrackToPlaylist = async (trackId) => {
      addingTrack.value = trackId;
      
      try {
        await playlistService.addTrackToPlaylist(props.playlistId, trackId);
        
        // Удаляем добавленный трек из списка
        tracks.value = tracks.value.filter(track => track.id !== trackId);
        
        // Уведомляем родительский компонент о добавлении трека
        emit('track-added');
      } catch (err) {
        console.error('Ошибка при добавлении трека в плейлист:', err);
        error.value = 'Не удалось добавить трек в плейлист';
      } finally {
        addingTrack.value = null;
      }
    };
    
    // Обновляем список треков при изменении ID плейлиста
    watch(() => props.playlistId, () => {
      if (props.playlistId) {
        loadTracks();
      }
    });
    
    onMounted(() => {
      loadTracks();
    });
    
    return {
      searchQuery,
      tracks,
      loading,
      error,
      filteredTracks,
      addingTrack,
      addTrackToPlaylist,
      defaultCover
    };
  }
}
</script>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: hsla(0,0%,100%,.3);
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: hsla(0,0%,100%,.4);
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}
</style> 