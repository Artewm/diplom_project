<template>
   <div class="w-full h-full bg-black rounded-sm p-4 flex flex-col">
    <div class="flex flex-col h-full playlist-header w-full">
        <div class="flex items-center justify-start p-4 bg-transparent rounded-t-lg">
            <div class="flex items-center justify-start w-auto ml-4">
                <img :src="playIcon" alt="playlist" class="w-64 bg-spotify-black p-2 shadow-xl shadow-spotify-black/70 rounded-md hover:shadow-2xl hover:shadow-spotify-black/90 transition-shadow duration-300">
            </div>
            <div class="flex items-center justify-start">
                <!-- тут название приватный или публичный и количество треков -->
                <div class="flex flex-col items-start justify-between ml-4 gap-4">
                    <span class="text-white text-xl  font-roboto">Открытый плейлист</span>
                    <span class="text-white text-4xl font-bold font-roboto">Название плейлиста</span>
                    <div class="flex items-center justify-between">
                        <span class="text-white text-sm font-roboto">Количество треков: {{ tracks.length }}</span>
                        <span class="text-white text-sm font-roboto ml-4">Длительность: {{ totalDuration }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="playlist-gradient flex flex-col  w-full h-full">
            <div class="flex items-center justify-start bg-transparent p-4 backdrop-opacity-10 backdrop-blur-sm">
            <div class="flex items-center justify-start w-auto ml-4 gap-4">
                <button class="bg-spotify-green p-2 rounded-full">
                    <img :src="playTrack" alt="play" class="w-6 h-6">
                </button>
                <button class="text-white text-sm font-roboto bg-spotify-black p-2 rounded-md hover:bg-spotify-gray transition-colors duration-300">
                    <span>Приватный</span>
                </button>
            </div>
        </div>
        <div class="flex-grow bg-transparent  overflow-y-auto border-t border-spotify-gray backdrop-blur-sm">
            <TrackList class="w-full h-full" :tracks="tracks" />
        </div>
        </div>
    </div>
   </div>
</template>
  
<script>
import PlayIcon from '../../../images/baseMusic.png'
import playTrack from '../../../images/play.png'
import { ref, onMounted, computed } from 'vue'
import TrackList from '../tracks/TrackList.vue'
import axios from 'axios'

export default {
  components: {
    TrackList
  },
  setup() {
    const tracks = ref([])
    
    
    const totalDuration = computed(() => {
      const seconds = tracks.value.reduce((total, track) => total + (track.duration || 0), 0)
      const minutes = Math.floor(seconds / 60)
      const remainingSeconds = seconds % 60
      return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}` 

    })
    
    onMounted(async () => {
      try {
        const response = await axios.get('/api/tracks')
        tracks.value = response.data
      } catch (error) {
        console.error('Ошибка при загрузке треков:', error)
      }
    })
    
    return {
      playIcon: PlayIcon,
      playTrack,
      tracks,
      totalDuration
    }
  }
}
</script>
  
<style scoped>
.playlist-gradient {
  background: linear-gradient(to bottom, #202020,  #0c0c0c, #070707);
}
.playlist-header {
  background: linear-gradient(to bottom, #333333,  #121212, #121212);
}
</style>
  