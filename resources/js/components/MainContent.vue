<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const tracks = ref([])

onMounted(async () => {
    const response = await axios.get('/api/tracks')
    tracks.value = response.data
})
</script>

<template>
    <main class="flex-1 bg-[#181818] p-6 overflow-y-auto">
        <!-- Заголовок -->
        <h1 class="text-3xl font-bold mb-6">Добро пожаловать</h1>

        <!-- Секция контента -->
        <section>
            <h2 class="text-xl font-semibold mb-4">Популярные треки</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="track in tracks"
                    :key="track.id"
                    class="bg-[#282828] p-4 rounded hover:bg-[#383838] transition">
                    <img src="#" alt="#">
                    <div class="w-32 h-32 bg-gray-600 rounded mb-4"></div>
                    <h3 class="font-semibold">{{ track.title }}</h3>
                    <p class="text-sm text-gray-400">{{ track.artist }}</p>
                </div>
            </div>
        </section>
    </main>
</template>
