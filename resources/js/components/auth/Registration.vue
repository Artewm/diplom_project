<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="$emit('close')"></div>
        <div class="flex flex-col items-center justify-between w-1/5 h-2/3 bg-zinc-800 rounded-lg z-10">
            <div class="flex gap-2 items-center justify-center w-full mt-20">
                <img :src="spotifyLogo" alt="logo" class="w-10 h-10">
                <h2 class="text-xl font-bold">MUSIC LISTENER</h2>
            </div>
            <div class="flex gap-4 items-center justify-center w-full mt-20">
                <button 
                    class="text-xl relative nav-link" 
                    @click="openLogin">Вход</button>
                <button 
                    class="text-xl relative nav-link active"
                    @click="activeTab = 'register'">Регистрация</button>
            </div>
            <div class="flex flex-col items-center justify-center w-full p-10 mb-40 gap-4">
                <input v-model="name" type="text" placeholder="Имя" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input v-model="email" type="text" placeholder="Email" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input v-model="password" type="password" placeholder="Пароль" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input v-model="password_confirmation" type="password" placeholder="Подтвердите пароль" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <button @click.prevent="register" class="w-full p-2 rounded-md bg-green-500 text-white">Зарегистрироваться</button>
            </div>
        </div>
    </div>
</template>
  
<script>
import spotifyLogo from '../../../images/spotify.png';
import { ref, inject } from 'vue';

export default {
    name: 'Registration',
    emits: ['close'],
    setup(props, { emit }) {
        const activeTab = ref('register');
        const name = ref('');
        const email = ref('');
        const password = ref('');
        const password_confirmation = ref('');
        
        const openLoginModal = inject('openLoginModal');
        
        const openLogin = () => {
            emit('close');
            openLoginModal();
        };
        
        const register = () => {
            axios.post('/api/registration', {
                name: name.value,
                email: email.value,
                password: password.value,
                password_confirmation: password_confirmation.value
            }).then(response => {
                console.log(response);
                emit('close');
            }).catch(error => {
                console.error('Ошибка регистрации:', error);
            });
        };
        
        return {
            activeTab,
            name,
            email,
            password,
            password_confirmation,
            spotifyLogo,
            register,
            openLogin
        };
    }
}
</script>
  
<style scoped>
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
  