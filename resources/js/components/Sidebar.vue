<!-- Боковая панель навигации -->
<template>
  <aside class="w-60 flex flex-col bg-black text-white h-screen">
    <!-- Logo -->
    <div class="flex items-center justify-start gap-4 p-6">
      <img :src="spotifyLogo" class="h-8 text-white" alt="spotify logo">
      <h2 class="text-sm font-bold">MUSIC LISTENER</h2>
        
    </div>

    <!-- Navigation -->
    <nav class=" px-2 h-64">
      <ul class="space-y-2" >
        <li>
          <router-link to="/" class="flex items-center px-4 py-2 text-gray-300 hover:text-white transition-colors">
            <svg class="w-6 h-6 mr-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12.5 3.247a1 1 0 0 0-1 0L4 7.577V20h4.5v-6a1 1 0 0 1 1-1h5a1 1 0 0 1 1 1v6H20V7.577l-7.5-4.33zm-2-1.732a3 3 0 0 1 3 0l7.5 4.33a2 2 0 0 1 1 1.732V21a1 1 0 0 1-1 1h-6.5a1 1 0 0 1-1-1v-6h-3v6a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7.577a2 2 0 0 1 1-1.732l7.5-4.33z"/>
            </svg>
            Главная
          </router-link>
        </li>
        <li>
          <router-link to="/search" class="flex items-center px-4 py-2 text-gray-300 hover:text-white transition-colors">
            <svg class="w-6 h-6 mr-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M10.533 1.279c-5.18 0-9.407 4.14-9.407 9.279s4.226 9.279 9.407 9.279c2.234 0 4.29-.77 5.907-2.058l4.353 4.353a1 1 0 1 0 1.414-1.414l-4.344-4.344a9.157 9.157 0 0 0 2.077-5.816c0-5.14-4.226-9.28-9.407-9.28zm-7.407 9.279c0-4.006 3.302-7.279 7.407-7.279s7.407 3.273 7.407 7.279-3.302 7.279-7.407 7.279-7.407-3.273-7.407-7.279z"/>
            </svg>
            Поиск
          </router-link>
        </li>
        <li>
          <router-link to="/library" class="flex items-center px-4 py-2 text-gray-300 hover:text-white transition-colors">
            <svg class="w-6 h-6 mr-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M14.5 2.134a1 1 0 0 1 1 0l6 3.464a1 1 0 0 1 .5.866V21a1 1 0 0 1-1 1h-6a1 1 0 0 1-1-1V3a1 1 0 0 1 .5-.866zM16 4.732V20h4V7.041l-4-2.309zM3 22a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H3zm1-2h4V4H4v16z"/>
            </svg>
            Медиатека
          </router-link>
        </li>
      </ul>
    </nav>

    <!-- Playlists -->
    <div class="px-6 py-4">
      <button class="flex items-center space-x-2 text-gray-300 hover:text-white transition-colors">
        <div class="w-6 h-6 bg-white/10 rounded-sm flex items-center justify-center">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
          </svg>
        </div>
        <span>Создать плейлист</span>
      </button>
    </div>

    <!-- Divider -->
    <div class="px-6">
      <div class="h-[1px] bg-white/10"></div>
    </div>

    <!-- Playlists List -->
    <div class="flex-1 px-2 py-4 overflow-y-auto">
      <ul class="space-y-2">
        <li v-for="playlist in playlists" :key="playlist.id">
          <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:text-white transition-colors truncate">
            {{ playlist.name }}
          </a>
        </li>
      </ul>
    </div>
  </aside>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import spotifyLogo from '../../images/spotify.png';

export default {
  name: 'Sidebar',
  setup() {
    const router = useRouter();
    
    return {
      playlists: [
        { id: 1, name: 'Мой плейлист #1' },
        { id: 2, name: 'Любимые треки' },
      ],
      spotifyLogo
    }
  }
}
</script>

<style scoped>
/* Стилизация скроллбара */
.overflow-y-auto::-webkit-scrollbar {
  width: 12px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: hsla(0,0%,100%,.3);
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: hsla(0,0%,100%,.4);
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}
</style>
