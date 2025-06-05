<template>
  <div class="fixed bottom-0 left-0 right-0 bg-[#181818] border-t border-[#282828] px-4 py-2">
    <div class="flex items-center justify-between max-w-screen-xl mx-auto">
      <!-- Track Info -->
      <div class="flex items-center min-w-[180px] w-[30%]">
        <img 
          :src="currentTrack.image || 'https://via.placeholder.com/56'" 
          :alt="currentTrack.title"
          class="h-14 w-14 rounded shadow mr-3"
        />
        <div class="mr-4">
          <h4 class="text-sm text-white font-medium truncate">{{ currentTrack.title || 'Не выбран трек' }}</h4>
          <p class="text-xs text-gray-400 truncate">{{ currentTrack.artist || 'Неизвестный исполнитель' }}</p>
        </div>
        <button class="text-gray-400 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
        </button>
      </div>

      <!-- Player Controls -->
      <div class="flex flex-col items-center max-w-[722px] w-[40%]">
        <div class="flex items-center space-x-4 mb-1">
          <button class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M6 3v18M18 3v18"/>
            </svg>
          </button>
          <button class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M19 5L5 12l14 7V5z" transform="rotate(180 12 12)"/>
            </svg>
          </button>
          <button class="w-8 h-8 bg-white rounded-full flex items-center justify-center hover:scale-105">
            <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 5v14l11-7z"/>
            </svg>
          </button>
          <button class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M5 5v14l11-7z"/>
            </svg>
          </button>
          <button class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.001 12a5 5 0 0 1-9.999 0 5 5 0 0 1 9.999 0Z"/>
            </svg>
          </button>
        </div>
        
        <div class="flex items-center w-full space-x-2">
          <span class="text-xs text-gray-400 w-10 text-right">0:00</span>
          <div class="flex-1 h-1 bg-gray-600 rounded-full">
            <div class="h-full w-0 bg-white rounded-full relative">
              <div class="absolute -right-2 -top-2 w-4 h-4 bg-white rounded-full opacity-0 group-hover:opacity-100"></div>
            </div>
          </div>
          <span class="text-xs text-gray-400 w-10">0:00</span>
        </div>
      </div>

      <!-- Volume Controls -->
      <div class="flex items-center justify-end min-w-[180px] w-[30%] space-x-3">
        <button class="text-gray-400 hover:text-white">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M9 6l6 6-6 6"/>
          </svg>
        </button>
        <button class="text-gray-400 hover:text-white">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.625l4.5 4.5-4.5 4.5v-9Z"/>
          </svg>
        </button>
        <div class="w-24">
          <div class="h-1 bg-gray-600 rounded-full">
            <div class="h-full w-1/2 bg-white rounded-full relative group">
              <div class="absolute -right-2 -top-2 w-4 h-4 bg-white rounded-full opacity-0 group-hover:opacity-100"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Аудио элемент для воспроизведения -->
    <audio 
      ref="audioPlayer"
      :src="currentTrack.url"
      @timeupdate="onTimeUpdate"
      @loadedmetadata="onLoadedMetadata"
      @ended="onTrackEnded"
    ></audio>
  </div>
</template>

<script>
export default {
  name: 'Player',
  // Состояние плеера
  data() {
    return {
      isPlaying: false, // Статус воспроизведения
      currentTime: 0, // Текущее время трека
      duration: 0, // Длительность трека
      volume: 70, // Громкость (0-100)
      previousVolume: 70, // Сохраненная громкость для восстановления после мута
      currentTrack: {
        title: '',
        artist: '',
        image: '',
        url: ''
      }
    }
  },
  // Вычисляемые свойства
  computed: {
    progress() {
      return this.duration ? (this.currentTime / this.duration) * 100 : 0
    }
  },
  // Методы управления плеером
  methods: {
    togglePlay() {
      if (this.isPlaying) {
        this.$refs.audioPlayer.pause()
      } else {
        this.$refs.audioPlayer.play()
      }
      this.isPlaying = !this.isPlaying
    },
    formatTime(seconds) {
      const mins = Math.floor(seconds / 60)
      const secs = Math.floor(seconds % 60)
      return `${mins}:${secs.toString().padStart(2, '0')}`
    },
    onTimeUpdate() {
      this.currentTime = this.$refs.audioPlayer.currentTime
    },
    onLoadedMetadata() {
      this.duration = this.$refs.audioPlayer.duration
    },
    seek(event) {
      const rect = event.currentTarget.getBoundingClientRect()
      const percent = (event.clientX - rect.left) / rect.width
      this.currentTime = this.duration * percent
      this.$refs.audioPlayer.currentTime = this.currentTime
    },
    adjustVolume(event) {
      const rect = event.currentTarget.getBoundingClientRect()
      const percent = Math.max(0, Math.min(100, ((event.clientX - rect.left) / rect.width) * 100))
      this.volume = percent
      this.$refs.audioPlayer.volume = percent / 100
    },
    toggleMute() {
      if (this.volume > 0) {
        this.previousVolume = this.volume
        this.volume = 0
      } else {
        this.volume = this.previousVolume
      }
      this.$refs.audioPlayer.volume = this.volume / 100
    },
    previousTrack() {
      // Implement previous track logic
    },
    nextTrack() {
      // Implement next track logic
    },
    onTrackEnded() {
      this.isPlaying = false
      this.currentTime = 0
      // Implement auto-next track logic if needed
    },
    setTrackAndPlay(track) {
      this.currentTrack = track;
      this.$nextTick(() => {
        this.$refs.audioPlayer.currentTime = 0;
        this.$refs.audioPlayer.play();
        this.isPlaying = true;
      });
    }
  },
  mounted() {
    this.$refs.audioPlayer.volume = this.volume / 100
    window.emitter.on('play-track', this.setTrackAndPlay);
  },
  beforeUnmount() {
    window.emitter.off('play-track', this.setTrackAndPlay);
  }
}
</script>

<style scoped>
.group:hover .group-hover\:bg-\[\#1db954\] {
  background-color: #1db954;
}
</style> 