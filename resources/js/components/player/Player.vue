<template>
  <div class="fixed bottom-0 left-0 right-0 bg-[#181818] border-t border-[#282828] px-4 py-2 z-40">
    <div class="flex items-center justify-between max-w-screen-xl mx-auto">
      <!-- Track Info -->
      <div class="flex items-center min-w-[180px] w-[30%]">
        <img 
          :src="currentTrack.image ? currentTrack.image : (currentTrack.cover_path ? '/storage/' + currentTrack.cover_path : baseMusic)" 
          :alt="currentTrack.title"
          class="h-14 w-14 rounded shadow mr-3"
        />
        <div class="mr-4">
          <h4 class="text-sm text-white font-medium truncate">{{ currentTrack.title || 'Не выбран трек' }}</h4>
          <p class="text-xs text-gray-400 truncate">{{ currentTrack.artist || 'Неизвестный исполнитель' }}</p>
        </div>
        <button class="text-gray-400 hover:text-white" @click="toggleFavorite">
          <svg :class="[{ 'heart-animate': heartAnimate }]" class="w-5 h-5" :fill="isCurrentTrackFavorite ? '#ef4444' : 'none'" :stroke="isCurrentTrackFavorite ? '#ef4444' : 'currentColor'" viewBox="0 0 24 24" @animationend="heartAnimate = false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
        </button>
      </div>

      <!-- Player Controls -->
      <div class="flex flex-col items-center max-w-[722px] w-[40%]">
        <div class="flex items-center space-x-4 mb-1">
          <button class="text-gray-400 hover:text-white" @click="previousTrack">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M6 3v18M18 3v18"/>
            </svg>
          </button>
          <button class="text-gray-400 hover:text-white" @click="previousTrack">
            <img :src="nextTrack" alt="nextTrack" class="w-5 h-5 rotate-180">
          </button>
          <button class="w-8 h-8 bg-white rounded-full flex items-center justify-center hover:scale-105" @click="togglePlay">
            <svg v-if="!isPlaying" class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 5v14l11-7z"/>
            </svg>
            <svg v-else class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 24 24">
              <rect x="6" y="5" width="4" height="14"/>
              <rect x="14" y="5" width="4" height="14"/>
            </svg>
          </button>
          <button class="text-gray-400 hover:text-white" @click="nextTrackHandler">
            <img :src="nextTrack" alt="nextTrack" class="w-5 h-5">
          </button>
        </div>
        
        <div class="flex items-center w-full space-x-2">
          <span class="text-xs text-gray-400 w-10 text-right">{{ formatTime(currentTime) }}</span>
          <div class="flex-1 h-1 bg-gray-600 rounded-full cursor-pointer group"
               @click="seek"
               @mousedown="startSeekDrag">
            <div class="h-full bg-white rounded-full relative" :style="{ width: progress + '%' }">
              <div class="absolute -right-2 -top-2 w-4 h-4 bg-white rounded-full opacity-0 group-hover:opacity-100"></div>
            </div>
          </div>
          <span class="text-xs text-gray-400 w-10">{{ formatTime(duration) }}</span>
        </div>
      </div>

      <!-- Volume Controls -->
      <div class="flex items-center justify-end min-w-[180px] w-[30%] space-x-3">
        <button class="text-gray-400 hover:text-white" @click="toggleMute">
          <img :src="volumeIcon" alt="volumeIcon" class="w-5 h-5">
        </button>
        <div class="w-24">
          <div class="h-1 bg-gray-600 rounded-full cursor-pointer" 
               @click="adjustVolume"
               @mousedown="startVolumeDrag">
            <div class="h-full bg-white rounded-full relative group" :style="{ width: volume + '%' }">
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
import { ref, onMounted, computed, watch, inject, toRef, provide } from 'vue'
import nextTrack from '../../../images/arrowNext.png'
import maxVolume from '../../../images/maxVolume.png'
import minVolume from '../../../images/lowVolume.png'
import mutt from '../../../images/mutt.png'
import baseMusic from '../../../images/baseMusic.png'
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
      nextTrack: nextTrack,
      maxVolume: maxVolume,
      minVolume: minVolume,
      mutt: mutt,
      currentTrack: {
        title: '',
        artist: '',
        cover_path: '',
        url: ''
      },
      volumeBarRef: null, // ссылка на элемент полосы громкости
      playlistTracks: [], // массив треков плейлиста
      currentTrackIndex: -1, // индекс текущего трека
      favoriteTrackIds: [], // id избранных треков
      heartAnimate: false,
      baseMusic: baseMusic,
      seekBarRef: null,
      isSeeking: false,
      _boundOnSeekDrag: null,
      _boundStopSeekDrag: null,
      wasPlaying: false,
    }
  },
  // Вычисляемые свойства
  computed: {
    progress() {
      return this.duration ? (this.currentTime / this.duration) * 100 : 0
    },
    volumeIcon() {
      if (this.volume === 0) return this.mutt;
      if (this.volume > 50) return this.maxVolume;
      return this.minVolume;
    },
    isCurrentTrackFavorite() {
      return this.favoriteTrackIds.includes(this.currentTrack.id);
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
      if (!this.isSeeking) {
        this.currentTime = this.$refs.audioPlayer.currentTime
      }
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
    adjustVolume(event, el = null) {
      // el — это элемент полосы громкости, если передан явно
      const bar = el || event.currentTarget || this.volumeBarRef;
      if (!bar) return;
      const rect = bar.getBoundingClientRect();
      const clientX = event.touches ? event.touches[0].clientX : event.clientX;
      const percent = Math.max(0, Math.min(100, ((clientX - rect.left) / rect.width) * 100));
      this.volume = percent;
      this.$refs.audioPlayer.volume = percent / 100;
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
      if (this.playlistTracks.length > 0) {
        if (this.currentTrackIndex > 0) {
          this.setTrackByIndex(this.currentTrackIndex - 1);
        } else {
          // если нет предыдущего — включаем последний
          this.setTrackByIndex(this.playlistTracks.length - 1);
        }
      }
    },
    nextTrackHandler() {
      if (this.playlistTracks.length > 0) {
        if (this.currentTrackIndex < this.playlistTracks.length - 1) {
          this.setTrackByIndex(this.currentTrackIndex + 1);
        } else {
          // если нет следующего — включаем первый
          this.setTrackByIndex(0);
        }
      }
    },
    setTrackByIndex(index) {
      const track = this.playlistTracks[index];
      if (track) {
        this.currentTrack = track;
        this.currentTrackIndex = index;
        window.emitter.emit('current-track-changed', this.currentTrack);
        this.$nextTick(() => {
          this.$refs.audioPlayer.currentTime = 0;
          this.$refs.audioPlayer.play();
          this.isPlaying = true;
        });
      }
    },
    setTrackAndPlay(payload) {
      // payload может быть треком или { track, playlistTracks, index }
      if (payload && Array.isArray(payload.playlistTracks)) {
        this.playlistTracks = payload.playlistTracks;
        this.currentTrackIndex = payload.index ?? 0;
        this.setTrackByIndex(this.currentTrackIndex);
      } else {
        this.currentTrack = payload;
        this.playlistTracks = [];
        this.currentTrackIndex = -1;
        window.emitter.emit('current-track-changed', this.currentTrack);
        this.$nextTick(() => {
          this.$refs.audioPlayer.currentTime = 0;
          this.$refs.audioPlayer.play();
          this.isPlaying = true;
        });
      }
    },
    onTrackEnded() {
      this.isPlaying = false;
      this.currentTime = 0;
      // Автоматически следующий трек
      if (this.playlistTracks.length > 0 && this.currentTrackIndex < this.playlistTracks.length - 1) {
        this.setTrackByIndex(this.currentTrackIndex + 1);
      }
    },
    startVolumeDrag(event) {
      // Сохраняем ссылку на элемент полосы громкости
      this.volumeBarRef = event.currentTarget;
      this.adjustVolume(event, this.volumeBarRef);
      document.addEventListener('mousemove', this.onVolumeDrag);
      document.addEventListener('mouseup', this.stopVolumeDrag);
    },
    onVolumeDrag(event) {
      this.adjustVolume(event, this.volumeBarRef);
    },
    stopVolumeDrag() {
      document.removeEventListener('mousemove', this.onVolumeDrag);
      document.removeEventListener('mouseup', this.stopVolumeDrag);
      this.volumeBarRef = null;
    },
    startSeekDrag(event) {
      this.isSeeking = true;
      this.seekBarRef = event.currentTarget;
      this.wasPlaying = !this.$refs.audioPlayer.paused;
      this.onSeekDrag(event);
      if (!this._boundOnSeekDrag) this._boundOnSeekDrag = (e) => this.onSeekDrag(e);
      if (!this._boundStopSeekDrag) this._boundStopSeekDrag = (e) => this.stopSeekDrag(e);
      document.addEventListener('mousemove', this._boundOnSeekDrag);
      document.addEventListener('mouseup', this._boundStopSeekDrag);
    },
    onSeekDrag(event) {
      if (!this.isSeeking || !this.seekBarRef) return;
      const rect = this.seekBarRef.getBoundingClientRect();
      const clientX = event.touches ? event.touches[0].clientX : event.clientX;
      let percent = (clientX - rect.left) / rect.width;
      percent = Math.max(0, Math.min(1, percent));
      this.currentTime = this.duration * percent;
    },
    stopSeekDrag() {
      this.isSeeking = false;
      this.seekBarRef = null;
      if (this._boundOnSeekDrag) document.removeEventListener('mousemove', this._boundOnSeekDrag);
      if (this._boundStopSeekDrag) document.removeEventListener('mouseup', this._boundStopSeekDrag);
      // После завершения drag выставляем точное время
      this.$refs.audioPlayer.currentTime = this.currentTime;
      // Синхронизируем визуальный прогресс
      this.currentTime = this.$refs.audioPlayer.currentTime;
      if (this.wasPlaying) {
        this.$refs.audioPlayer.play();
      } else {
        this.$refs.audioPlayer.pause();
      }
    },
    async fetchFavorites() {
      if (!localStorage.getItem('token')) {
        this.favoriteTrackIds = [];
        return;
      }
      try {
        const response = await fetch('/api/favorites', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        });
        const data = await response.json();
        this.favoriteTrackIds = Array.isArray(data) ? data.map(t => t.id) : [];
      } catch (e) {
        this.favoriteTrackIds = [];
      }
    },
    async toggleFavorite() {
      this.heartAnimate = false;
      void this.$nextTick(() => { this.heartAnimate = true })
      const id = this.currentTrack.id;
      if (!id) return;
      if (this.isCurrentTrackFavorite) {
        // удалить из избранного
        await fetch(`/api/favorites/${id}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        });
        this.favoriteTrackIds = this.favoriteTrackIds.filter(favId => favId !== id);
      } else {
        // добавить в избранное
        await fetch(`/api/favorites/${id}`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        });
        this.favoriteTrackIds.push(id);
      }
    },
  },
  mounted() {
    this.$refs.audioPlayer.volume = this.volume / 100
    window.emitter.on('play-track', this.setTrackAndPlay);
    this.fetchFavorites();
    provide('playerCurrentTrack', toRef(this, 'currentTrack'));
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
@keyframes heart-pop {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}
.heart-animate {
  animation: heart-pop 0.3s cubic-bezier(.4,2,.6,1) forwards;
}
</style> 