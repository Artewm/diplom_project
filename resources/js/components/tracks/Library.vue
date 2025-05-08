<template>
    <div class="library-container p-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Медиатека</h2>
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

        <div v-else-if="filteredItems.length === 0" class="text-center py-8 text-gray-400">
            Нет сохраненных элементов
        </div>

        <div v-else class="bg-spotify-black rounded-md">
            <TrackList 
                v-if="currentFilter !== 'Исполнители'" 
                :tracks="filteredTracks" 
                :favorites="favorites"
                :showAddToFavorites="true"
                :showRemoveFromFavorites="true"
                @add-to-favorites="addToFavorites"
                @remove-from-favorites="removeFromFavorites"
            />
            <ArtistList v-else :artists="uniqueArtists" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import TrackList from './TrackList.vue';
import ArtistList from './ArtistList.vue';
import favoritesService from '../../services/favorites';
import mitt from 'mitt';
import { inject } from 'vue';

// Получение данных аутентификации
const isAuthenticated = inject('isAuthenticated')

// Создаем экземпляр шины событий если её еще нет
const emitter = window.emitter || (window.emitter = mitt())

const tracks = ref([]);
const favorites = ref([]);
const isLoading = ref(true);
const loadingFavorites = ref(false);
const error = ref(null);
const filters = ['Все', 'Треки', 'Альбомы', 'Исполнители'];
const currentFilter = ref('Все');

const filteredTracks = computed(() => {
    if (currentFilter.value === 'Все') return tracks.value;
    if (currentFilter.value === 'Исполнители') return tracks.value;
    return tracks.value.filter(track => {
        switch (currentFilter.value) {
            case 'Треки': return !track.is_album;
            case 'Альбомы': return track.is_album;
            default: return true;
        }
    });
});

const uniqueArtists = computed(() => {
    // Получаем уникальных исполнителей и считаем их треки
    const artistMap = new Map();
    
    tracks.value.forEach(track => {
        if (!track.artist) return;
        
        if (artistMap.has(track.artist)) {
            const artist = artistMap.get(track.artist);
            artist.trackCount++;
        } else {
            artistMap.set(track.artist, {
                name: track.artist,
                trackCount: 1
            });
        }
    });
    
    // Преобразуем Map в массив
    return Array.from(artistMap.values());
});

const filteredItems = computed(() => {
    if (currentFilter.value === 'Исполнители') {
        return uniqueArtists.value;
    }
    return filteredTracks.value;
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

// Загрузка избранных треков
const fetchFavorites = async () => {
    if (!isAuthenticated.value) {
        favorites.value = [];
        return;
    }
    
    loadingFavorites.value = true;
    
    try {
        const response = await favoritesService.getUserFavorites();
        if (response && response.data) {
            favorites.value = response.data;
            console.log('Библиотека загрузила избранные треки:', favorites.value.length);
        } else {
            favorites.value = [];
        }
    } catch (err) {
        console.error('Ошибка при загрузке избранных треков в библиотеке:', err);
        favorites.value = [];
    } finally {
        loadingFavorites.value = false;
    }
};

// Добавление трека в избранное
const addToFavorites = async (trackId) => {
    try {
        await favoritesService.addToFavorites(trackId);
        await fetchFavorites(); // Обновляем список избранных треков
        emitter.emit('favorite-added', trackId); // Отправляем событие
    } catch (err) {
        console.error('Ошибка при добавлении трека в избранное:', err);
    }
};

// Удаление трека из избранного
const removeFromFavorites = async (trackId) => {
    try {
        await favoritesService.removeFromFavorites(trackId);
        favorites.value = favorites.value.filter(track => track.id !== trackId);
        emitter.emit('favorite-removed', trackId); // Отправляем событие
    } catch (err) {
        console.error('Ошибка при удалении трека из избранного:', err);
    }
};

// Следим за изменением статуса авторизации
watch(isAuthenticated, (newValue) => {
    if (newValue) {
        fetchFavorites();
    } else {
        favorites.value = [];
    }
});

onMounted(() => {
    loadTracks();
    
    // Загружаем избранные треки если пользователь авторизован
    if (isAuthenticated.value) {
        fetchFavorites();
    }
    
    // Подписываемся на события добавления/удаления из избранного
    emitter.on('favorite-added', () => {
        fetchFavorites();
    });
    
    emitter.on('favorite-removed', (trackId) => {
        favorites.value = favorites.value.filter(track => track.id !== trackId);
    });
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