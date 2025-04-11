<template>
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Список треков</h1>
        <ul>
            <li v-for="track in tracks" :key="track.id" class="mb-2">
                🎵 {{ track.title }} — {{ track.artist }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const tracks = ref([])

onMounted(async () => {
    const response = await axios.get('/api/tracks')
    tracks.value = response.data
})
</script>
