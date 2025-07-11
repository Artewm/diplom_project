<template>
    <div class="flex-1 overflow-y-auto bg-black">
      <!-- Search Header -->
      <div class="sticky top-0 z-10 bg-black/95 backdrop-blur-sm px-6 py-4">
        <div class="relative">
          <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input 
            type="text"
            v-model="searchQuery"
            placeholder="Что хотите послушать?"
            class="w-full h-12 pl-12 pr-4 bg-white/10 text-white placeholder-gray-400 rounded-full focus:outline-none focus:ring-2 focus:ring-white"
          />
        </div>
      </div>
  
      <!-- Search Results -->
      <div class="px-6 py-4">
        <template v-if="searchQuery">
          <!-- Tracks -->
          <div v-if="searchResults.tracks.length" class="mb-8">
            <h2 class="text-2xl font-bold text-white mb-4">Треки</h2>
            <div class="grid grid-cols-2 gap-4">
              <div 
                v-for="track in searchResults.tracks"
                :key="track.id"
                class="flex items-center p-4 bg-white/5 hover:bg-white/10 rounded-md group cursor-pointer"
                @click="playTrack(track)"
              >
                <img 
                  :src="track.image || 'https://via.placeholder.com/48'" 
                  :alt="track.title"
                  class="w-12 h-12 object-cover rounded"
                />
                <div class="ml-4 flex-1 min-w-0">
                  <div class="text-white font-medium truncate">{{ track.title }}</div>
                  <div class="text-sm text-gray-400 truncate">{{ track.artist }}</div>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Artists -->
          <div v-if="searchResults.artists.length" class="mb-8">
            <h2 class="text-2xl font-bold text-white mb-4">Исполнители</h2>
            <div class="grid grid-cols-5 gap-6">
              <div 
                v-for="artist in searchResults.artists" 
                :key="artist.id"
                class="p-4 bg-white/5 hover:bg-white/10 rounded-md text-center group cursor-pointer"
              >
                <img 
                  :src="artist.image || 'https://via.placeholder.com/160'" 
                  :alt="artist.name"
                  class="w-40 h-40 object-cover rounded-full mx-auto mb-4"
                />
                <div class="text-white font-medium mb-1">{{ artist.name }}</div>
                <div class="text-sm text-gray-400">Исполнитель</div>
              </div>
            </div>
          </div>
  
          <!-- No Results -->
          <div v-if="!hasResults" class="text-center py-12">
            <div class="text-gray-400 mb-2">Ничего не найдено по запросу "{{ searchQuery }}"</div>
            <div class="text-sm text-gray-500">Попробуйте другие ключевые слова</div>
          </div>
        </template>
  
        <!-- Browse All -->
        <template v-else>
          <h2 class="text-2xl font-bold text-white mb-6">Просмотреть все</h2>
          <div class="grid grid-cols-5 gap-6">
            <div 
              v-for="category in categories" 
              :key="category.id"
              class="relative overflow-hidden rounded-lg group cursor-pointer aspect-square"
              :style="{ backgroundColor: category.color }"
            >
            
              <img 
                :src="category.image"
                :alt="category.name"
                class="absolute bottom-5 right-5 w-[75px] h-[75px] rotate-[25deg] translate-x-[5%] translate-y-[5%]"
              />
              <div class="p-4">
                <h3 class="text-2xl font-bold text-white">{{ category.name }}</h3>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, watch } from 'vue'
  import axios from 'axios'
  
  const searchQuery = ref('')
  const searchResults = ref({ tracks: [], artists: [] })
  const isLoading = ref(false)
  
  const categories = [
    { id: 1, name: 'Поп', color: '#8D67AB', image: '/images/pop.png' },
    { id: 2, name: 'Хип-хоп', color: '#BA5D07', image: '/images/hiphop.png' },
    { id: 3, name: 'Рок', color: '#E8115B', image: '/images/rock.png' },
    { id: 4, name: 'Электронная', color: '#1E3264', image: '/images/electro.png' },
    { id: 5, name: 'Джаз', color: '#27856A', image: '/images/jazz.png' }
  ]
  
  const hasResults = computed(() => {
    return searchResults.value.tracks.length > 0 || searchResults.value.artists.length > 0
  })
  
  watch(searchQuery, async (newQuery) => {
    if (!newQuery.trim()) {
      searchResults.value = { tracks: [], artists: [] }
      return
    }
    await handleSearch()
  })
  
  async function handleSearch() {
    if (!searchQuery.value.trim()) {
      searchResults.value = { tracks: [], artists: [] }
      return
    }
  
    isLoading.value = true
    try {
      const response = await axios.get(`/api/search?q=${encodeURIComponent(searchQuery.value)}`)
      searchResults.value = {
        ...response.data,
        tracks: response.data.tracks.map(track => ({
          ...track,
          url: track.file_path ? `/storage/${track.file_path}` : undefined
        }))
      }
    } catch (error) {
      console.error('Ошибка поиска:', error)
    } finally {
      isLoading.value = false
    }
  }
  
  function playTrack(track) {
    window.emitter.emit('play-track', track)
  }
  </script>
  
  <style scoped>
  input[type="number"]::-webkit-inner-spin-button,
  input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  @media (max-width: 900px) {
    .grid-cols-5 {
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
    .grid-cols-2 {
      grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }
    .px-6 {
      padding-left: 8px !important;
      padding-right: 8px !important;
    }
  }
  @media (max-width: 600px) {
    .grid-cols-5 {
      grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }
    .grid-cols-2 {
      grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }
    .px-6 {
      padding-left: 4px !important;
      padding-right: 4px !important;
    }
    .py-4 {
      padding-top: 4px !important;
      padding-bottom: 4px !important;
    }
  }
  </style>
  