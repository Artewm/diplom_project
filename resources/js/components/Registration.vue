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
                    @mouseover="activeTab = 'login'"
                    @mouseleave="activeTab = 'register'">Вход</RouterLink>
                <RouterLink to="/registration" 
                    class="text-xl relative nav-link"
                    :class="{ 'active': activeTab === 'register' }"
                    @mouseover="activeTab = 'register'">Регистрация</RouterLink>
            </div>
            <div class="flex flex-col items-center justify-center w-full p-10 mb-40 gap-4">
                <input v-model="name" type="text" placeholder="Имя" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input v-model="email" type="text" placeholder="Email" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input v-model="password" type="password" placeholder="Пароль" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input v-model="password_confirmation" type="password" placeholder="Подтвердите пароль" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input @click.prevent="store" type="submit" value="Зарегистрироваться" class="w-full p-2 rounded-md  bg-green-500 text-white">
            </div>
        </div>
    </div>
    
  </template>
  
  <script>
  import { RouterLink } from 'vue-router';
import spotifyLogo from '../../images/spotify.png'
import { ref } from 'vue';
import Registration from './Registration.vue'
import Login from './Login.vue'

export default {
    data() {
        return {
            name: null,
            email: null,
            password: null,
            password_confirmation: null,
            spotifyLogo,
            activeTab: 'register'
        }
    },
    methods: {
        store() {
            axios.post('/api/registration', {
                name: this.name,
                email: this.email,
                password: this.password,
                password_confirmation: this.password_confirmation
            }).then(response => {
                console.log(response);
            })
        }
    }
}

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
  