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
                            ? 'bg-spotify-green text-white' 
                            : 'bg-spotify-black hover:bg-spotify-gray text-white'
                    ]"
                >
                    {{ filter }}
                </button>
            </div>
        </div>

        <div v-if="isLoading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-spotify-green"></div>
        </div>

        <div v-else-if="error" class="text-center text-red-500 py-8">
            {{ error }}
        </div>

        <div v-else-if="filteredTracks.length === 0" class="text-center py-8 text-gray-400">
            Нет сохраненных треков
        </div>

        <div v-else class="bg-spotify-black rounded-md">
            <TrackList :tracks="filteredTracks" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import TrackList from './TrackList.vue';

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
        const response = await axios.get('/api/tracks');
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