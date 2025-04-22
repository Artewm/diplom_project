<script setup>

</script>
<template>
    <footer class="bg-[#181818] p-4 flex items-center justify-between border-t border-gray-800">
        <!-- Информация о треке -->
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gray-600 rounded"></div>
            <div>
                <p class="font-semibold">Название трека</p>
                <p class="text-sm text-gray-400">Артист</p>
            </div>
        </div>

        <!-- Элементы управления -->
        <div class="flex items-center space-x-4">
            <button class="text-gray-400 hover:text-white">
                <span class="material-icons">skip_previous</span>
            </button>
            <button class="text-white bg-green-500 rounded-full p-2">
                <span class="material-icons">play_arrow</span>
            </button>
            <button class="text-gray-400 hover:text-white">
                <span class="material-icons">skip_next</span>
            </button>
        </div>

        <!-- Ползунок громкости -->
        <div class="flex items-center space-x-2">
            <span class="material-icons text-gray-400">volume_up</span>
            <div class="w-24 h-1 bg-gray-600 rounded">
                <div class="w-1/2 h-full bg-white rounded"></div>
            </div>
        </div>
    </footer>
</template>

<style scoped>

</style>
