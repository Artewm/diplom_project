<script setup>
const menuItems = [
    { name: 'Главная', icon: 'home', active: true },
    { name: 'Поиск', icon: 'search', active: false },
    { name: 'Библиотека', icon: 'library', active: false },
];
</script>

<template>
    <aside class="w-128 bg-[#121212] p-6 flex flex-col space-y-6">
        <!-- Логотип -->
        <div class="text-2xl text-spotify-white font-bold">Spotify</div>

        <!-- Меню -->
        <nav class="space-y-2">
            <a
                v-for="item in menuItems"
                :key="item.name"
                href="#"
                :class="[
          'flex items-center space-x-3 p-2 rounded',
          item.active ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-spotify-white'
        ]"
            >
<!--                <span class="material-icons">{{ item.icon }}</span>-->
                <span>{{ item.name }}</span>
            </a>
        </nav>

        <!-- Плейлисты -->
        <div class="flex-1">
            <h3 class="text-sm font-semibold text-spotify-white-400 mb-2">Плейлисты</h3>
            <ul class="space-y-2 text-gray-400">
                <li><a href="#" class="hover:text-white flex items-center">Плейлист 1</a></li>
                <li><a href="#" class="hover:text-white flex items-center">Плейлист 2</a></li>
                <li><a href="#" class="hover:text-white flex items-center">Плейлист 3</a></li>
            </ul>
        </div>
    </aside>
</template>

<style scoped>

</style>
