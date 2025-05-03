<template>
    <div class="w-full overflow-x-auto">
      <table class="min-w-full text-sm text-gray-300">
        <thead class="uppercase text-xs font-semibold border-b border-white/10">
          <tr>
            <th class="px-6 py-3 text-left w-12">#</th>
            <th class="px-6 py-3 text-left">Название</th>
            <th class="px-6 py-3 text-left">Исполнитель</th>
            <th class="px-6 py-3 text-right w-24 pl-4">
              <div class="flex items-center justify-center">
                <img :src="durationIcon" alt="duration" class="w-4 h-4">
              </div>
            </th>
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
            <td class="px-6 py-3 text-center text-gray-400 group-hover:text-white">
              <span v-if="!hoveredTrack || hoveredTrack !== track.id">{{ index + 1 }}</span>
              <button v-else @click="playTrack(track)" class="focus:outline-none">
                <PlayIcon class="w-5 h-5" />
              </button>
            </td>
  
            <!-- Название трека -->
            <td class="px-6 py-3">
              <div class="flex items-center min-w-0">
                <img
                  :src="track.image || PlayIcon"
                  :alt="track.title"
                  class="w-10 h-10 object-cover rounded-full bg-spotify-gray mr-4 flex-shrink-0 p-2"
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
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  
  
  
  
  <script setup>
  import { ref, defineProps } from 'vue'
//   import { PlayIcon } from '@heroicons/vue/24/solid'
import PlayIcon from '../../../images/baseMusic.png'
import durationIcon from '../../../images/duration.png'
  
  const props = defineProps({
    tracks: {
      type: Array,
      default: () => []
    }
  })
  
  const hoveredTrack = ref(null)
  
  const playTrack = (track) => {
    console.log('Playing track:', track.title)
  }
  
  const formatDuration = (seconds) => {
    if (!seconds) return '0:00'
    const minutes = Math.floor(seconds / 60)
    const remainingSeconds = seconds % 60
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
  }
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
  