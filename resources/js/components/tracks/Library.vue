<template>
    <div class="library-container p-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Моя библиотека</h2>
            <div class="flex gap-4">
                <button 
                    v-for="filter in filters" 
                    :key="filter"
                    @click="currentFilter = filter"
                    :class="[
                        'px-4 py-2 rounded-full transition-colors',
                        currentFilter === filter 
                            ? 'bg-primary text-white' 
                            : 'bg-gray-100 hover:bg-gray-200'
                    ]"
                >
                    {{ filter }}
                </button>
            </div>
        </div>

        <div v-if="isLoading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary"></div>
        </div>

        <div v-else-if="error" class="text-center text-red-500 py-8">
            {{ error }}
        </div>

        <div v-else-if="filteredTracks.length === 0" class="text-center py-8 text-gray-500">
            Нет сохраненных треков
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div v-for="track in filteredTracks" 
                :key="track.id" 
                class="track-card bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-lg transition-all duration-300">
                <div class="relative group">
                    <img :src="track.cover_url || '/default-cover.png'" 
                         class="w-full aspect-square object-cover rounded-md mb-3"
                         alt="Track cover">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 rounded-md flex items-center justify-center">
                        <button @click="playTrack(track)" 
                                class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-primary text-white p-3 rounded-full hover:scale-110 transform transition">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                </div>
                <div class="track-info">
                    <h3 class="font-semibold text-lg truncate dark:text-white">{{ track.title }}</h3>
                    <p class="text-gray-600 dark:text-gray-400 truncate">{{ track.artist }}</p>
                </div>
                <div class="track-controls mt-2 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ formatDuration(track.duration) }}</span>
                    <button @click="toggleFavorite(track)" 
                            class="text-gray-400 hover:text-primary transition-colors">
                        <i :class="['fas', track.is_favorite ? 'fa-heart text-primary' : 'fa-heart']"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const tracks = ref([]);
const isLoading = ref(true);
const error = ref(null);
const filters = ['Все', 'Треки', 'Альбомы', 'Исполнители'];
const currentFilter = ref('Все');

const filteredTracks = computed(() => {
    if (currentFilter.value === 'Все') return tracks.value;
    return tracks.value.filter(track => {
        switch (currentFilter.value) {
            case 'Треки': return !track.is_album;
            case 'Альбомы': return track.is_album;
            case 'Исполнители': return track.is_artist;
            default: return true;
        }
    });
});

const loadTracks = async () => {
    try {
        isLoading.value = true;
        const response = await axios.get('/api/library');
        tracks.value = response.data;
    } catch (err) {
        error.value = 'Ошибка при загрузке библиотеки';
        console.error('Error loading library:', err);
    } finally {
        isLoading.value = false;
    }
};

const playTrack = (track) => {
    // Эмитим событие для родительского компонента
    emit('play-track', track);
};

const toggleFavorite = async (track) => {
    try {
        await axios.post(`/api/tracks/${track.id}/toggle-favorite`);
        track.is_favorite = !track.is_favorite;
    } catch (err) {
        console.error('Error toggling favorite:', err);
    }
};

const formatDuration = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

onMounted(() => {
    loadTracks();
});
</script>

<style scoped>
.library-container {
    min-height: calc(100vh - 64px);
}

.track-card {
    transition: transform 0.2s ease;
}

.track-card:hover {
    transform: translateY(-4px);
}

.track-info {
    overflow: hidden;
}
</style> 