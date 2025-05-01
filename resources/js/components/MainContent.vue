<script setup>
import { ref, onMounted, inject } from 'vue'
import axios from 'axios'
import TrackList from './TrackList.vue'

const openLoginModal = inject('openLoginModal')
const openRegistrationModal = inject('openRegistrationModal')

const playlists = ref([
  {
    id: 1,
    title: 'Мой плейлист 1',
    description: 'Kendrick Lamar, Drake, J. Cole и другие',
    image: 'https://via.placeholder.com/150'
  },
])



const tracks = ref([])

onMounted(async () => {
  try {
    const response = await axios.get('/api/tracks')
    tracks.value = response.data
  } catch (error) {
    console.error('Ошибка при загрузке треков:', error)
  }
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
      <div class="flex items-center space-x-4">
        <button 
          class="bg-white rounded-full p-2 text-black hover:bg-gray-300 font-semibold"
          @click="openRegistrationModal">
            Зарегистрироваться
        </button>
        <button 
          class="bg-white rounded-full p-2 text-black hover:bg-gray-300 font-semibold"
          @click="openLoginModal">
            Войти
        </button>
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
</style>
