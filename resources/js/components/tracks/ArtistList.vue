<template>
  <div class="w-full overflow-x-auto">
    <table class="min-w-full text-sm text-gray-300">
      <thead class="uppercase text-xs font-semibold border-b border-white/10">
        <tr>
          <th class="px-12 py-3 text-left">#</th>
          <th class="px-6 py-3 text-left">Имя исполнителя</th>
          <th class="px-6 py-3 text-left">Количество треков</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-white/5">
        <tr 
          v-for="(artist, index) in artists" 
          :key="index"
          class="group hover:bg-white/5 transition duration-200"
        >
          <td class="px-12 py-3 text-center text-gray-400 group-hover:text-white">
            {{ index + 1 }}
          </td>

          <td class="px-6 py-3">
            <div class="flex items-center min-w-0">
              <div class="text-white font-medium truncate">
                {{ artist.name }}
              </div>
            </div>
          </td>

          <td class="px-6 py-3 text-gray-400 truncate">
            {{ artist.trackCount }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

defineProps({
  artists: {
    type: Array,
    default: () => []
  }
});
</script>

<style scoped>
/* Наследуем стили от TrackList */
</style> 