<template>
    <div class="flex flex-col items-center justify-center h-screen">
        <div class="flex flex-col items-center justify-between w-1/4 h-2/3 bg-zinc-800 rounded-lg">
            <div class="flex gap-2 items-center justify-center w-full mt-20">
                <img :src="spotifyLogo" alt="logo" class="w-10 h-10">
                <h2 class="text-xl font-bold">MUSIC LISTENER</h2>
            </div>
            <div class="flex gap-4 items-center justify-center w-full mt-20">
                <RouterLink to="/login" 
                    class="text-xl relative nav-link" 
                    :class="{ 'active': activeTab === 'login' }"
                    @mouseover="activeTab = 'login'">Вход</RouterLink>
                <RouterLink to="/register" 
                    class="text-xl relative nav-link"
                    :class="{ 'active': activeTab === 'register' }"
                    @mouseover="activeTab = 'register'"
                    @mouseleave="activeTab = 'login'">Регистрация</RouterLink>
            </div>
            <div class="flex flex-col items-center justify-center w-full p-10 mb-40 gap-4">
                <input type="text" placeholder="Email" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input type="password" placeholder="Пароль" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input type="submit" value="Войти" class="w-full p-2 rounded-md  bg-green-500 text-white">
            </div>
        </div>
    </div>
    
  </template>
  
  <script setup>
  import { RouterLink } from 'vue-router';
import spotifyLogo from '../../../public/images/spotify.png'
import { ref } from 'vue';

const activeTab = ref('login');
  </script>
  
  <style scoped>
  .active-tab::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #22c55e;
  }

  .nav-link {
    position: relative;
  }

  .nav-link::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #22c55e;
    transform: scaleX(0);
    transition: transform 0.3s ease;
  }

  .nav-link.active::after {
    transform: scaleX(1);
  }
  </style>
  