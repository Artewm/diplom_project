<template>
    <div :class="['overflow-x-auto pb-20', $attrs.class]">
      <table class="min-w-full text-sm text-gray-300">
        <thead class="uppercase text-xs font-semibold border-b border-white/10">
          <tr>
            <th class="px-12 py-3 text-left w-12">#</th>
            <th class="px-6 py-3 text-left">Название</th>
            <th class="px-6 py-3 text-left">Исполнитель</th>
            <th class="px-6 py-3 text-right w-24 pl-4">
              <div class="flex items-center justify-center">
                <img :src="durationIcon" alt="duration" class="w-4 h-4">
              </div>
            </th>
            <th v-if="canRemoveTracks" class="px-6 py-3 text-right w-16">Действия</th>
            <th v-if="isPlaylist && canRemoveTracks" class="px-6 py-3 text-right w-16">Действия</th>
          </tr>
        </thead>
  
        <tbody class="divide-y divide-white/5">
          <tr 
            v-for="(track, index) in props.tracks" 
            :key="track.id"
            class="group hover:bg-white/5 transition duration-200"
            @mouseenter="hoveredTrack = track.id"
            @mouseleave="hoveredTrack = null"
          >
            <!-- Номер или кнопка Play -->
            <td class="px-12 py-3 text-center text-gray-400 group-hover:text-white">
              <span v-if="!hoveredTrack || hoveredTrack !== track.id">{{ index + 1 }}</span>
              <button v-else @click="playTrack(track)" class="focus:outline-none">
                <img :src="playIcon" class="w-10 " alt="play" />
              </button>
            </td>
  
            <!-- Название трека -->
            <td class="px-6 py-3">
              <div class="flex items-center min-w-0">
                <img
                  :src="track.cover_path ? '/storage/' + track.cover_path : PlayIcon"
                  :alt="track.title"
                  class="w-12 h-12 object-cover rounded-full bg-spotify-gray mr-4 flex-shrink-0 "
                />
                <div class="text-white font-medium truncate">
                  {{ track.title }}
                </div>
              </div>
            </td>
  
            <!-- Исполнитель -->
            <td class="px-6 py-3 text-gray-400 truncate">
              {{ track.artist }}
            </td>
  
            <!-- Длительность -->
            <td class="px-6 py-3 text-center text-gray-400">
              {{ formatDuration(track.duration) }}
            </td>
            
            <!-- Кнопки управления -->
            <td class="px-6 py-3 text-center text-gray-400">
              <div class="flex items-center justify-end gap-2">
                <!-- Кнопка удаления для плейлиста -->
                <button 
                  v-if="isPlaylist && canRemoveTracks"
                  @click="removeTrackFromPlaylist(track.id)" 
                  class="opacity-0 group-hover:opacity-100 hover:text-red-500 transition-opacity p-1"
                  title="Удалить из плейлиста">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                  </svg>
                </button>
                
                <!-- Кнопка удаления из избранного -->
                <button 
                  v-if="showRemoveFromFavorites && isAuthenticated && isInFavorites(track.id)"
                  @click="removeFromFavorites(track.id)" 
                  class="opacity-70 group-hover:opacity-100 hover:text-red-500 transition-all p-1"
                  title="Удалить из избранного">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 text-red-500 fill-current">
                    <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                  </svg>
                </button>
                
                <!-- Кнопка добавления в избранное -->
                <button 
                  v-if="showAddToFavorites && isAuthenticated && !isInFavorites(track.id)"
                  @click="addToFavorites(track.id)" 
                  class="opacity-70 group-hover:opacity-100 hover:text-red-500 transition-all p-1"
                  title="Добавить в избранное">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                  </svg>
                </button>
                
                <!-- Кнопка добавления в плейлист -->
                <button 
                  v-if="!isPlaylist && isAuthenticated"
                  @click="openPlaylistSelector(track.id)" 
                  class="opacity-0 group-hover:opacity-100 hover:text-green-500 transition-opacity p-1"
                  title="Добавить в плейлист">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m3-3H9m4.06-7.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                  </svg>
                </button>
              </div>
            </td>
            
            <!-- Кнопка удаления трека (отдельная колонка) -->
            <td v-if="canRemoveTracks" class="px-6 py-3 text-center text-gray-400">
              <button 
                @click="$emit('remove-track', track.id)" 
                class="hover:text-red-500 transition-colors duration-200 px-2 py-1 rounded-md bg-red-500 bg-opacity-20 hover:bg-opacity-30"
                title="Удалить трек">
                <img :src="deleteIcon" alt="delete" class="w-5 h-5">
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Модальное окно выбора плейлиста -->
    <PlaylistSelector
      v-if="showPlaylistSelector"
      :track-id="selectedTrackId"
      @close="showPlaylistSelector = false"
    />
    
    <!-- Модальное окно создания плейлиста -->
    <CreatePlaylistModal
      v-if="showCreatePlaylistModal"
      :track-id="selectedTrackId"
      @close="showCreatePlaylistModal = false"
      @playlist-created="onPlaylistCreated"
    />
  </template>
  
<script setup>
import { ref, defineProps, defineEmits, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import PlayIcon from '../../../images/baseMusic.png'
import durationIcon from '../../../images/duration.png'
import playIcon from '../../../images/play.png'
import PlaylistSelector from '../playlist/PlaylistSelector.vue'
import CreatePlaylistModal from '../playlist/CreatePlaylistModal.vue'
import playlistService from '../../services/playlist'
import authService from '../../services/auth'
import favoritesService from '../../services/favorites'
import deleteIcon from '../../../images/delete.png'
  
const props = defineProps({
  tracks: {
    type: Array,
    default: () => []
  },
  isPlaylist: {
    type: Boolean,
    default: false
  },
  showAddToFavorites: {
    type: Boolean,
    default: false
  },
  showRemoveFromFavorites: {
    type: Boolean,
    default: false
  },
  favorites: {
    type: Array,
    default: () => []
  },
  canRemoveTracks: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['remove-track', 'add-to-favorites', 'remove-from-favorites', 'add-to-playlist'])

const router = useRouter()
const hoveredTrack = ref(null)
const isAuthenticated = computed(() => authService.isAuthenticated())

// Состояние для выбора плейлиста
const showPlaylistSelector = ref(false)
const selectedTrackId = ref(null)

// Состояние для создания плейлиста
const showCreatePlaylistModal = ref(false)
  
const playTrack = (track) => {
  const index = props.tracks.findIndex(t => t.id === track.id);
  const playlistTracks = props.tracks.map(t => ({
    ...t,
    url: '/storage/' + t.file_path
  }));
  window.emitter.emit('play-track', {
    playlistTracks,
    index
  });
}

const removeTrackFromPlaylist = (trackId) => {
  if (confirm('Вы действительно хотите удалить этот трек из плейлиста?')) {
    emit('remove-track', trackId)
  }
}

const addToFavorites = (trackId) => {
  emit('add-to-favorites', trackId)
}

const removeFromFavorites = (trackId) => {
  if (confirm('Вы действительно хотите удалить этот трек из избранного?')) {
    emit('remove-from-favorites', trackId)
  }
}

// Проверяет, есть ли трек в избранном
const isInFavorites = (trackId) => {
  // Проверяем, что favorites это массив и не пустой
  return Array.isArray(props.favorites) && props.favorites.length > 0
    ? props.favorites.some(favorite => favorite.id === trackId)
    : false
}

const openPlaylistSelector = (trackId) => {
  selectedTrackId.value = trackId
  showPlaylistSelector.value = true
}

const openCreatePlaylistModal = () => {
  showPlaylistSelector.value = false
  showCreatePlaylistModal.value = true
}

const onPlaylistCreated = (playlist) => {
  showCreatePlaylistModal.value = false
  
  // Если создан плейлист, переходим на его страницу
  router.push({ name: 'playlist', params: { id: playlist.id } })
}
  
const formatDuration = (duration) => {
  if (!duration) return '0:00';
  if (typeof duration === 'string' && duration.includes(':')) return duration;
  const seconds = Number(duration);
  if (isNaN(seconds)) return '0:00';
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

// Отладочный вывод полученных свойств при изменении
watch(() => props.tracks, (newTracks) => {
  console.log('TrackList получил новые треки:', newTracks);
}, { immediate: true });

watch(() => props.favorites, (newFavorites) => {
  console.log('TrackList получил новые избранные:', newFavorites);
}, { immediate: true });

watch(() => props.showRemoveFromFavorites, (value) => {
  console.log('TrackList showRemoveFromFavorites:', value);
}, { immediate: true });

defineOptions({ inheritAttrs: false })
</script>
  
<style scoped>
/* Можно добавить плавность для PlayIcon если нужно */
button > svg {
  transition: transform 0.2s ease;
}
button:hover > svg {
  transform: scale(1.1);
}
</style>
  