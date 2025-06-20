<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="flex flex-col items-center justify-between w-1/4 min-w-96 bg-zinc-800 rounded-lg z-10 p-6">
      <h2 class="text-xl font-bold mb-6">Добавить трек в плейлист</h2>
      
      <div v-if="error" class="w-full p-2 mb-4 bg-red-500/20 text-red-500 rounded-md text-center">
        {{ error }}
      </div>
      
      <div v-if="success" class="w-full p-2 mb-4 bg-green-500/20 text-green-500 rounded-md text-center">
        {{ success }}
      </div>
      
      <div v-if="loading" class="w-full text-center py-4">
        <p class="text-gray-400">Загрузка плейлистов...</p>
      </div>
      
      <div v-else-if="playlists.length === 0" class="w-full text-center py-4">
        <p class="text-gray-400">У вас пока нет плейлистов</p>
        <button 
          @click="showCreateModal = true" 
          class="mt-4 px-4 py-2 bg-spotify-green text-white rounded-md hover:bg-opacity-80 transition-colors"
        >
          Создать плейлист
        </button>
      </div>
      
      <div v-else class="w-full max-h-60 overflow-y-auto mb-6">
        <div 
          v-for="playlist in playlists" 
          :key="playlist.id" 
          class="flex items-center justify-between p-3 hover:bg-white/5 rounded-md transition-colors cursor-pointer"
          @click="selectPlaylist(playlist.id)"
        >
          <div class="flex items-center">
            <div class="w-12 h-12 bg-spotify-black rounded-md mr-3 flex-shrink-0">
              <img 
                :src="playlist.cover_image || defaultCover" 
                alt="Playlist cover" 
                class="w-full h-full object-cover rounded-md"
              >
            </div>
            <div>
              <p class="text-white font-medium">{{ playlist.name }}</p>
              <p class="text-gray-400 text-sm">Треков: {{ playlist.tracks_count || 0 }}</p>
            </div>
          </div>
          <button 
            class="text-white bg-spotify-green py-1 px-3 rounded-md hover:bg-opacity-80 transition-colors"
            @click.stop="addToPlaylist(playlist.id)"
          >
            Добавить
          </button>
        </div>
      </div>
      
      <div class="flex items-center justify-end w-full">
        <button 
          @click="$emit('close')"
          class="px-4 py-2 rounded-md bg-transparent border border-gray-600 text-gray-300 hover:bg-gray-700 transition-colors"
        >
          Закрыть
        </button>
      </div>
    </div>
  </div>
  
  <!-- Модальное окно создания плейлиста -->
  <CreatePlaylistModal
    v-if="showCreateModal"
    :track-id="trackId"
    @close="showCreateModal = false"
    @playlist-created="onPlaylistCreated"
  />
</template>

<script>
import { ref, onMounted } from 'vue';
import playlistService from '../../services/playlist';
import defaultCover from '../../../images/baseMusic.png';
import CreatePlaylistModal from './CreatePlaylistModal.vue';

export default {
  name: 'PlaylistSelector',
  components: {
    CreatePlaylistModal
  },
  props: {
    trackId: {
      type: Number,
      required: true
    }
  },
  emits: ['close', 'create-playlist'],
  setup(props, { emit }) {
    const playlists = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const success = ref(null);
    const showCreateModal = ref(false);
    
    const loadPlaylists = async () => {
      loading.value = true;
      error.value = null;
      
      try {
        const userPlaylists = await playlistService.getUserPlaylists();
        playlists.value = userPlaylists;
      } catch (err) {
        console.error('Ошибка при загрузке плейлистов:', err);
        error.value = 'Не удалось загрузить плейлисты';
      } finally {
        loading.value = false;
      }
    };
    
    const addToPlaylist = async (playlistId) => {
      try {
        await playlistService.addTrackToPlaylist(playlistId, props.trackId);
        success.value = 'Трек добавлен в плейлист';
        
        // Обновляем список плейлистов, чтобы обновить счетчик треков
        loadPlaylists();
        
        // Через 2 секунды закрываем окно
        setTimeout(() => {
          emit('close');
        }, 2000);
        
      } catch (err) {
        console.error('Ошибка при добавлении трека в плейлист:', err);
        error.value = 'Не удалось добавить трек в плейлист';
      }
    };
    
    const selectPlaylist = (playlistId) => {
      console.log('Выбран плейлист:', playlistId);
    };
    
    const onPlaylistCreated = (playlist) => {
      // Закрываем модальное окно создания плейлиста
      showCreateModal.value = false;
      
      // Обновляем список плейлистов
      loadPlaylists();
      
      // Показываем сообщение об успешном добавлении трека в новый плейлист
      success.value = 'Трек добавлен в новый плейлист';
      
      // Через 2 секунды закрываем окно выбора плейлиста
      setTimeout(() => {
        emit('close');
      }, 2000);
    };
    
    onMounted(() => {
      loadPlaylists();
    });
    
    return {
      playlists,
      loading,
      error,
      success,
      showCreateModal,
      selectPlaylist,
      addToPlaylist,
      defaultCover,
      onPlaylistCreated
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