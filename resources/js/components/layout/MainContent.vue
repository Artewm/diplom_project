<script setup>
import { ref, onMounted, inject } from 'vue'
import axios from 'axios'
import TrackList from '../tracks/TrackList.vue'
import personalIcon from '../../../images/personal.png'
import Personal from '../auth/Personal.vue'

const openLoginModal = inject('openLoginModal')
const openRegistrationModal = inject('openRegistrationModal')
const openPersonalModal = inject('openPersonalModal')
const playlists = ref([
  {
    id: 1,
    title: 'Мой плейлист 1',
    description: 'Kendrick Lamar, Drake, J. Cole и другие',
    image: 'https://via.placeholder.com/150'
  },
])

const showPersonalMenu = ref(false)
const personalMenuPosition = ref({ top: 0, right: 0 })

const togglePersonalMenu = (event) => {
  showPersonalMenu.value = !showPersonalMenu.value
  if (showPersonalMenu.value) {
    // Позиционируем меню под кнопкой
    const rect = event.target.getBoundingClientRect()
    personalMenuPosition.value = {
      top: `${rect.bottom + 5}px`,
      right: `${window.innerWidth - rect.right}px`
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

onMounted(async () => {
  document.addEventListener('click', closeMenuOnClickOutside)
  
  try {
    const response = await axios.get('/api/tracks')
    tracks.value = response.data
  } catch (error) {
    console.error('Ошибка при загрузке треков:', error)
  }
})

const tracks = ref([])
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
      <div class="flex items-center space-x-4">
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
        <div class="relative">
          <button 
            class="bg-spotify-gray rounded-full p-2 text-black hover:bg-gray font-semibold personal-button"
            @click="togglePersonalMenu($event)">
            <img :src="personalIcon" alt="Personal" class="w-6 h-6">
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
      
        <TrackList :tracks="tracks" />

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
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
