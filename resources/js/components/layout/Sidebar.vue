<!-- Боковая панель навигации -->
<template>
  <aside v-if="isCompact" class="sidebar-bar w-60 flex flex-col bg-spotify-black text-white h-screen items-center">
    <!-- Logo -->
    <div class="sidebar-logo flex flex-col items-center justify-center py-4">
      <img :src="spotifyLogo" class="h-8 w-8 text-white" alt="spotify logo">
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav flex flex-col items-center gap-4 mt-2 mb-4">
      <router-link to="/" class="sidebar-icon-link flex flex-col items-center justify-center">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12.5 3.247a1 1 0 0 0-1 0L4 7.577V20h4.5v-6a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v6H20V7.577l-7.5-4.33zm-2-1.732a3 3 0 0 1 3 0l7.5 4.33a2 2 0 0 1 1 1.732V21a1 1 0 0 1-1 1h-6.5a1 1 0 0 1-1-1v-6h-3v6a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.577a2 2 0 0 1 1-1.732l7.5-4.33z"/>
        </svg>
      </router-link>
      <router-link to="/search" class="sidebar-icon-link flex flex-col items-center justify-center">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
          <path d="M10.533 1.279c-5.18 0-9.407 4.14-9.407 9.279s4.226 9.279 9.407 9.279c2.234 0 4.29-.77 5.907-2.058l4.353 4.353a1 1 0 1 0 1.414-1.414l-4.344-4.344a9.157 9.157 0 0 0 2.077-5.816c0-5.14-4.226-9.28-9.407-9.28zm-7.407 9.279c0-4.006 3.302-7.279 7.407-7.279s7.407 3.273 7.407 7.279-3.302 7.279-7.407 7.279-7.407-3.273-7.407-7.279z"/>
        </svg>
      </router-link>
      <router-link to="/library" class="sidebar-icon-link flex flex-col items-center justify-center">
        <img :src="mediateka" class="w-6 h-6" alt="mediateka">
      </router-link>
    </nav>

    <!-- Liked tracks -->
    <div class="sidebar-liked flex flex-col items-center mb-2">
      <router-link v-if="isAuthenticated" to="/favorites" class="sidebar-icon-link flex flex-col items-center justify-center">
        <div class="liked-songs-icon w-6 h-6 flex items-center justify-center">
          <svg width="22" height="22" viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 3.17647C7.0253 1.94647 5.19012 1.52647 3.66012 2.38235C2.13012 3.23823 1.49717 5.05647 2.0853 6.55823C2.67344 8.06 6.04295 11.36 8 12C9.95705 11.36 13.3266 8.06 13.9147 6.55823C14.5028 5.05647 13.8699 3.23823 12.3399 2.38235C10.8099 1.52647 8.9747 1.94647 8 3.17647Z" fill="white"/>
          </svg>
        </div>
      </router-link>
    </div>

    <!-- Create Playlist -->
    <div class="sidebar-create flex flex-col items-center mb-2">
      <button v-if="isAuthenticated" @click="showCreatePlaylistModal = true" class="sidebar-icon-link flex flex-col items-center justify-center">
        <div class="w-6 h-6 bg-white/10 rounded-sm flex items-center justify-center">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
          </svg>
        </div>
      </button>
    </div>

    <!-- Divider -->
    <div class="sidebar-divider w-full flex flex-col items-center mb-2">
      <div class="h-[1px] w-8 bg-white/10"></div>
    </div>

    <!-- Playlists List -->
    <div class="sidebar-playlists flex-1 flex flex-col items-center gap-2 overflow-y-auto w-full">
      <ul v-if="isAuthenticated" class="flex flex-col items-center gap-2 w-full">
        <li v-for="playlist in playlists" :key="playlist.id" class="w-full flex justify-center">
          <router-link :to="{ name: 'playlist', params: { id: playlist.id } }" class="sidebar-icon-link flex flex-row items-center justify-start w-full gap-2 px-2 py-1">
            <img
              :src="getPlaylistCover(playlist)"
              alt="cover"
              class="w-8 h-8 object-cover rounded mr-2"
            />
            <span class="truncate">{{ playlist.name }}</span>
          </router-link>
        </li>
      </ul>
    </div>
    <!-- Модальное окно создания плейлиста -->
    <CreatePlaylistModal
      v-if="showCreatePlaylistModal"
      @close="showCreatePlaylistModal = false"
      @playlist-created="onPlaylistCreated"
    />
  </aside>
  <aside v-else class="w-60 flex flex-col bg-spotify-black text-white h-screen">
    <!-- старая широкая версия -->
    <div class="flex items-center justify-start gap-4 p-6">
      <img :src="spotifyLogo" class="h-8 text-white" alt="spotify logo">
      <h2 class="sidebar-title text-sm font-bold">MUSIC LISTENER</h2>
    </div>
    <nav class="px-2 h-64">
      <ul class="space-y-2">
        <li>
          <router-link to="/" class="flex items-center px-4 py-2 text-gray-300 hover:text-white transition-colors">
            <svg class="w-6 h-6 mr-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12.5 3.247a1 1 0 0 0-1 0L4 7.577V20h4.5v-6a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v6H20V7.577l-7.5-4.33zm-2-1.732a3 3 0 0 1 3 0l7.5 4.33a2 2 0 0 1 1 1.732V21a1 1 0 0 1-1 1h-6.5a1 1 0 0 1-1-1v-6h-3v6a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.577a2 2 0 0 1 1-1.732l7.5-4.33z"/>
            </svg>
            <span class="sidebar-link-text">Главная</span>
          </router-link>
        </li>
        <li>
          <router-link to="/search" class="flex items-center px-4 py-2 text-gray-300 hover:text-white transition-colors">
            <svg class="w-6 h-6 mr-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M10.533 1.279c-5.18 0-9.407 4.14-9.407 9.279s4.226 9.279 9.407 9.279c2.234 0 4.29-.77 5.907-2.058l4.353 4.353a1 1 0 1 0 1.414-1.414l-4.344-4.344a9.157 9.157 0 0 0 2.077-5.816c0-5.14-4.226-9.28-9.407-9.28zm-7.407 9.279c0-4.006 3.302-7.279 7.407-7.279s7.407 3.273 7.407 7.279-3.302 7.279-7.407 7.279-7.407-3.273-7.407-7.279z"/>
            </svg>
            <span class="sidebar-link-text">Поиск</span>
          </router-link>
        </li>
        <li>
          <router-link to="/library" class="flex items-center px-4 py-2 text-gray-300 hover:text-white transition-colors">
            <img :src="mediateka" class="w-6 h-6 mr-4" alt="mediateka">
            <span class="sidebar-link-text">Медиатека</span>
          </router-link>
        </li>
      </ul>
    </nav>
    <div class="">
      <router-link v-if="isAuthenticated" to="/favorites" class="flex items-center px-4 py-2 text-gray-300 hover:text-white transition-colors">
        <div class="liked-songs-icon w-6 h-6 mr-2 flex items-center justify-center">
          <svg width="22" height="22" viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 3.17647C7.0253 1.94647 5.19012 1.52647 3.66012 2.38235C2.13012 3.23823 1.49717 5.05647 2.0853 6.55823C2.67344 8.06 6.04295 11.36 8 12C9.95705 11.36 13.3266 8.06 13.9147 6.55823C14.5028 5.05647 13.8699 3.23823 12.3399 2.38235C10.8099 1.52647 8.9747 1.94647 8 3.17647Z" fill="white"/>
          </svg>
        </div>
        <span class="sidebar-link-text">Любимые треки</span>
      </router-link>
    </div>
    <div class="px-6 py-4">
      <button v-if="isAuthenticated" @click="showCreatePlaylistModal = true" class="flex items-center space-x-2 text-gray-300 hover:text-white transition-colors">
        <div class="w-6 h-6 bg-white/10 rounded-sm flex items-center justify-center">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
          </svg>
        </div>
        <span class="sidebar-link-text">Создать плейлист</span>
      </button>
      <div v-else class="text-sm text-gray-400 py-2 sidebar-link-text">
        Войдите, чтобы создавать плейлисты
      </div>
    </div>
    <div class="px-6">
      <div class="h-[1px] bg-white/10"></div>
    </div>
    <div class="flex-1 px-2 py-4 overflow-y-auto">
      <div v-if="loading" class="text-center py-4 text-gray-400">
        Загрузка плейлистов...
      </div>
      <div v-else-if="error" class="text-center py-4 text-red-400">
        {{ error }}
      </div>
      <ul v-else class="space-y-2">
        <li v-for="playlist in playlists" :key="playlist.id">
          <router-link :to="{ name: 'playlist', params: { id: playlist.id } }" class="block px-4 py-2 text-sm text-gray-300 hover:text-white transition-colors truncate flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
              <path d="M9 3v18M21 3v18M3 9h18M3 15h18"/>
            </svg>
            <span class="sidebar-link-text">{{ playlist.name }}</span>
            <span class="sidebar-link-text text-xs text-gray-400 ml-2">({{ playlist.tracks_count || 0 }})</span>
          </router-link>
        </li>
        <li v-if="playlists.length === 0 && isAuthenticated" class="text-center py-2 text-gray-400 text-sm sidebar-link-text">
          У вас пока нет плейлистов
        </li>
      </ul>
    </div>
    <CreatePlaylistModal
      v-if="showCreatePlaylistModal"
      @close="showCreatePlaylistModal = false"
      @playlist-created="onPlaylistCreated"
    />
  </aside>
</template>

<script>
import { ref, onMounted, computed, inject, watch } from 'vue';
import spotifyLogo from '../../../images/spotify.png';
import playlistService from '../../services/playlist';
import CreatePlaylistModal from '../playlist/CreatePlaylistModal.vue';
import mitt from 'mitt';
import mediateka from '../../../images/mediateka.png';
import favorites from '../playlist/Favorites.vue';
import baseMusic from '../../../images/baseMusic.png'
// Получаем экземпляр шины событий если её еще нет
const emitter = window.emitter || (window.emitter = mitt());

export default {
  name: 'Sidebar',
  components: {
    CreatePlaylistModal
  },
  setup() {
    const playlists = ref([]);
    const loading = ref(false);
    const error = ref(null);
    
    // Получаем состояние аутентификации из App.vue через inject
    const isAuthenticated = inject('isAuthenticated');
    
    // Состояние для создания плейлиста
    const showCreatePlaylistModal = ref(false);
    
    // --- добавлено для адаптива ---
    const isCompact = ref(window.innerWidth <= 900);
    const handleResize = () => {
      isCompact.value = window.innerWidth <= 900;
    };
    onMounted(() => {
      window.addEventListener('resize', handleResize);
      loadUserPlaylists();
      emitter.on('playlist-deleted', removeDeletedPlaylist);
      emitter.on('playlist-updated', (updatedPlaylist) => {
        const idx = playlists.value.findIndex(p => p.id === updatedPlaylist.id);
        if (idx !== -1) {
          playlists.value[idx].name = updatedPlaylist.name;
        }
      });
    });
    // --- конец добавленного ---
    
    // Загрузка плейлистов пользователя
    const loadUserPlaylists = async () => {
      if (!isAuthenticated.value) {
        playlists.value = [];
        return;
      }
      
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
    
    // Обработка события создания плейлиста
    const onPlaylistCreated = (newPlaylist) => {
      // Добавляем новый плейлист в список
      playlists.value.push(newPlaylist);
      showCreatePlaylistModal.value = false;
    };
    
    // Обработка события удаления плейлиста
    const removeDeletedPlaylist = (playlistId) => {
      playlists.value = playlists.value.filter(p => p.id !== playlistId);
    };
    
    // Следим за изменением статуса аутентификации
    watch(isAuthenticated, (newValue) => {
      if (newValue) {
        // Если пользователь авторизовался, загружаем его плейлисты
        loadUserPlaylists();
      } else {
        // Если пользователь вышел, очищаем список плейлистов
        playlists.value = [];
      }
    });
    
    // Получить картинку для плейлиста (обложка первого трека или дефолт)
    const getPlaylistCover = (playlist) => {
      if (playlist.tracks && playlist.tracks.length > 0 && playlist.tracks[0].cover_path) {
        return '/storage/' + playlist.tracks[0].cover_path;
      }
      return baseMusic;
    };
    
    return {
      playlists,
      spotifyLogo,
      loading,
      error,
      isAuthenticated,
      showCreatePlaylistModal,
      onPlaylistCreated,
      mediateka,
      favorites,
      isCompact,
      getPlaylistCover
    }
  }
}
</script>

<style scoped>
.sidebar-bar {
  width: 60px;
  min-width: 60px;
  max-width: 60px;
  background: #181818;
  align-items: center;
}
.sidebar-logo {
  padding-top: 16px;
  padding-bottom: 8px;
}
.sidebar-nav, .sidebar-liked, .sidebar-create, .sidebar-divider, .sidebar-playlists {
  width: 100%;
}
.sidebar-icon-link {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: background 0.2s;
}
.sidebar-icon-link:hover {
  background: #232323;
}
.liked-songs-icon {
  background: linear-gradient(135deg, #450af5 0%, #8e8ee5 40%, #c4efd9 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
}
.sidebar-divider {
  margin: 8px 0;
}
@media (max-width: 1080px) {
  .sidebar-bar {
    width: 48px !important;
    min-width: 48px !important;
    max-width: 48px !important;
    padding: 0 !important;
  }
  .sidebar-logo img {
    width: 32px !important;
    height: 32px !important;
  }
  .sidebar-icon-link {
    width: 36px !important;
    height: 36px !important;
  }
  .sidebar-divider {
    width: 80% !important;
  }
}
@media (max-width: 600px) {
  .sidebar-bar {
    display: none !important;
  }
}
</style>
