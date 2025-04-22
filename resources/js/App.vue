<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Sidebar from './components/Sidebar.vue';
import MainContent from './components/MainContent.vue';
import Player from './components/Player.vue';

const tracks = ref([])

onMounted(async () => {
    const response = await axios.get('/api/tracks')
    tracks.value = response.data
})
</script>

<template>
    <div class="h-screen bg-black text-spotify-white flex flex-col">
        <!-- Основной контейнер -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Боковая панель -->
            <Sidebar />
            <!-- Основной контент -->
            <MainContent />
        </div>
        <!-- Плеер -->
        <Player />
    </div>
</template>

<style scoped>

</style>
