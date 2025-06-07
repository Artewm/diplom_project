<script setup>
import { ref, onMounted, inject, computed, watch } from 'vue'
import axios from 'axios'
import TrackList from '../tracks/TrackList.vue'
import personalIcon from '../../../images/personal.png'
import Personal from '../auth/Personal.vue'
import favoritesService from '../../services/favorites'
import mitt from 'mitt'

// Создаем экземпляр шины событий если её еще нет
const emitter = window.emitter || (window.emitter = mitt())

// Получение данных аутентификации
const isAuthenticated = inject('isAuthenticated')
const currentUser = inject('currentUser')

// Получение функций модальных окон
const openLoginModal = inject('openLoginModal')
const openRegistrationModal = inject('openRegistrationModal')
const openPersonalModal = inject('openPersonalModal')

// const playlists = ref([
//   {
//     id: 1,
//     title: 'Мой плейлист 1',
//     description: 'Kendrick Lamar, Drake, J. Cole и другие',
//     image: 'https://via.placeholder.com/150'
//   },
// ])

const showPersonalMenu = ref(false)
const personalMenuPosition = ref({ top: 0, right: 0 })
const tracks = ref([])
const favorites = ref([])
const loadingFavorites = ref(false)

// Получаем имя пользователя для отображения
const userName = computed(() => {
  if (!currentUser.value) return ''
  
  // Если есть имя из БД, используем его
  if (currentUser.value.name) return currentUser.value.name
  
  // Иначе пробуем получить из других полей
  if (currentUser.value.sub) return `Пользователь ${currentUser.value.sub}`
  
  return ''
})

const togglePersonalMenu = (event) => {
  showPersonalMenu.value = !showPersonalMenu.value
  if (showPersonalMenu.value) {
    // Позиционируем меню под кнопкой
    const rect = event.target.getBoundingClientRect()
    personalMenuPosition.value = {
      top: `${rect.bottom + 5}px`,
      right: `${window.innerWidth - rect.right + 20}px`
    }
  }
}

// Закрываем меню при клике вне его
const closeMenuOnClickOutside = (event) => {
  if (showPersonalMenu.value) {
    const isClickInsidePersonalButton = event.target.closest('.personal-button')
    const isClickInsideMenu = event.target.closest('.personal-menu-container')
    
    if (!isClickInsidePersonalButton && !isClickInsideMenu) {
      showPersonalMenu.value = false
    }
  }
}

// Загрузка избранных треков
const fetchFavorites = async () => {
  if (!isAuthenticated.value) {
    favorites.value = []
    return
  }
  
  loadingFavorites.value = true
  
  try {
    const response = await favoritesService.getUserFavorites()
    if (response && response.data) {
      favorites.value = response.data
      console.log('Загружено избранных треков:', favorites.value.length)
    } else {
      favorites.value = []
    }
  } catch (err) {
    console.error('Ошибка при загрузке избранных треков:', err)
    favorites.value = []
  } finally {
    loadingFavorites.value = false
  }
}

// Добавление трека в избранное
const addToFavorites = async (trackId) => {
  try {
    await favoritesService.addToFavorites(trackId)
    // Найти трек среди всех треков
    const track = tracks.value.find(t => t.id === trackId)
    if (track) {
      // Добавляем его в избранное локально
      favorites.value.push(track)
      console.log('Добавлен трек в избранное:', track.title)
    }
    // Оповещаем остальные компоненты
    emitter.emit('favorite-added', trackId)
  } catch (err) {
    console.error('Ошибка при добавлении трека в избранное:', err)
  }
}

// Удаление трека из избранного
const removeFromFavorites = async (trackId) => {
  try {
    await favoritesService.removeFromFavorites(trackId)
    favorites.value = favorites.value.filter(track => track.id !== trackId)
    emitter.emit('favorite-removed', trackId) // Отправляем событие
  } catch (err) {
    console.error('Ошибка при удалении трека из избранного:', err)
  }
}

// Следим за изменением статуса авторизации
watch(isAuthenticated, (newValue) => {
  if (newValue) {
    fetchFavorites()
  } else {
    favorites.value = []
  }
})

onMounted(async () => {
  document.addEventListener('click', closeMenuOnClickOutside)
  
  const loadTracks = async () => {
    try {
      const response = await axios.get('/api/tracks')
      tracks.value = response.data
    } catch (error) {
      console.error('Ошибка при загрузке треков:', error)
    }
  }

  await loadTracks()
  
  // Загружаем избранные треки
  if (isAuthenticated.value) {
    fetchFavorites()
  }
  
  // Подписываемся на события добавления/удаления из избранного
  emitter.on('favorite-added', () => {
    fetchFavorites()
  })
  
  emitter.on('favorite-removed', (trackId) => {
    favorites.value = favorites.value.filter(track => track.id !== trackId)
  })

  // Подписка на добавление трека
  emitter.on('user-track-added', loadTracks)
  // Подписка на удаление трека
  emitter.on('user-track-removed', loadTracks)
})
</script>

<template>
  <div class="flex flex-col h-screen bg-black">
    <!-- Header -->
    <header class="flex items-center justify-between px-8 py-4">
      <div class="flex items-center space-x-4">
        <button class="p-2 rounded-full bg-black/40">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button class="p-2 rounded-full bg-black/40">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
      
      <!-- Кнопки авторизации/пользователя -->
      <div class="flex items-center space-x-4">
        <!-- Для неавторизованных пользователей -->
        <template v-if="!isAuthenticated">
          <button 
            class="bg-white rounded-full p-2 text-black hover:bg-gray-300 font-semibold"
            @click="openRegistrationModal">
              Регистрация
          </button>
          <button 
            class="bg-white rounded-full p-2 text-black hover:bg-gray-300 font-semibold"
            @click="openLoginModal">
              Вход
          </button>
        </template>
        
        <!-- Для авторизованных пользователей -->
        <div v-if="isAuthenticated" class="relative">
          <button 
            class="bg-spotify-gray rounded-full p-2 text-white hover:bg-gray-700 font-semibold personal-button flex items-center gap-3 pl-4"
            @click="togglePersonalMenu($event)">
            <img :src="personalIcon" alt="Personal" class="w-6 h-6">
            <span v-if="userName" class="hidden sm:inline pr-4">{{ userName }}</span>
          </button>
          
          <!-- Выпадающее меню -->
          <div v-if="showPersonalMenu" 
               class="absolute z-50 personal-menu-container" 
               :style="{ top: personalMenuPosition.top, right: personalMenuPosition.right }">
            <Personal />
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-gradient-to-b from-indigo-900 to-black p-8">
      <h1 class="text-2xl font-bold text-white mb-6">Популярные треки</h1>
      <TrackList 
        :tracks="tracks" 
        :favorites="favorites" 
        :showAddToFavorites="isAuthenticated ? true : false" 
        :showRemoveFromFavorites="false"
        @add-to-favorites="addToFavorites" 
      />
    </main>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.personal-menu-container {
  animation: fadeIn 0.2s ease-out;
  transform: translateX(-30px);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translate(-30px, -10px); }
  to { opacity: 1; transform: translateX(-30px); }
}
</style>
