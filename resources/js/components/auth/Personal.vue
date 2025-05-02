<template>
    <div class="bg-spotify-gray rounded-md min-w-[250px] shadow-lg overflow-hidden">
        <div class="flex items-center justify-start gap-4 py-3 px-4 text-white hover:bg-gray-200 cursor-pointer transition-colors duration-200" @click="addTrack">
            <img :src="addTrackIcon" alt="Добавить трек" class="w-5 h-5">
            Добавить трек
        </div>
        <div class="flex items-center justify-start gap-4 py-3 px-4 text-white hover:bg-gray-200 cursor-pointer transition-colors duration-200" @click="deleteItem">
            <img :src="deleteItemIcon" alt="Удалить" class="w-5 h-5">
            Удалить
        </div>
        <div class="flex items-center justify-start gap-4 py-3 px-4 text-red-500 hover:bg-gray-200 cursor-pointer transition-colors duration-200" @click="logout">
            <img :src="logoutIcon" alt="Выйти" class="w-5 h-5">
            Выйти
        </div>
    </div>
</template>
  
<script>
import { ref } from 'vue';
import addTrackIcon from '../../../images/add.png';
import deleteItemIcon from '../../../images/delete.png';
import logoutIcon from '../../../images/exit.png';

export default {
    setup() {
        const isPersonalModalOpen = ref(false);
        return {
            isPersonalModalOpen,
            addTrackIcon,
            deleteItemIcon,
            logoutIcon
        };
    },
    methods: {
        addTrack() {
            console.log('Добавить трек');
            // Здесь будет логика добавления трека
        },
        deleteItem() {
            console.log('Удалить');
            // Здесь будет логика удаления элемента
        },
        logout() {
            console.log('Выйти');
            // Здесь будет логика выхода
        }
    }
}
</script>
<style scoped>
</style>
  