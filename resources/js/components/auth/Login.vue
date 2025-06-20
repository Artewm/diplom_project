<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="$emit('close')"></div>
      <div class="flex flex-col items-center justify-between w-1/5 min-w-72 h-2/3 min-h-96 bg-zinc-800 rounded-lg z-10">
            <div class="flex gap-2 items-center justify-center w-full mt-20">
                <img :src="spotifyLogo" alt="logo" class="w-10 h-10">
                <h2 class="text-xl font-bold">MUSIC LISTENER</h2>
            </div>
            <div class="flex gap-4 items-center justify-center w-full mt-20">
                <button 
                  class="text-xl relative nav-link active" 
                  @click="activeTab = 'login'">Вход</button>
                <button 
                  class="text-xl relative nav-link"
                  @click="openRegistration">Регистрация</button>
            </div>
            <div class="flex flex-col items-center justify-center w-full p-10 mb-40 gap-4">
                <div v-if="errorMessage" class="w-full p-2 bg-red-500/20 text-red-500 rounded-md text-center">
                    {{ errorMessage }}
                </div>
                <input v-model="email" type="text" placeholder="Email" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <input v-model="password" type="password" placeholder="Пароль" class="w-full p-2 rounded-md bg-transparent border-2 border-gray-300">
                <button 
                  @click.prevent="login" 
                  class="w-full p-2 rounded-md bg-green-500 text-white flex items-center justify-center"
                  :disabled="isLoading">
                    <span v-if="isLoading">Загрузка...</span>
                    <span v-else>Войти</span>
                </button>
            </div>
        </div>
    </div>
</template>
  
<script>
import spotifyLogo from '../../../images/spotify.png';
import { ref, inject } from 'vue';

export default {
  name: 'Login',
  emits: ['close'],
  setup(props, { emit }) {
    const activeTab = ref('login');
    const email = ref('');
    const password = ref('');
    const errorMessage = ref('');
    const isLoading = ref(false);
    
    const openRegistrationModal = inject('openRegistrationModal');
    const loginUser = inject('login');
    
    const openRegistration = () => {
      emit('close');
      openRegistrationModal();
    };
    
    const login = async () => {
      // Сбрасываем сообщение об ошибке
      errorMessage.value = '';
      
      // Проверка валидации
      if (!email.value) {
        errorMessage.value = 'Введите email';
        return;
      }
      
      if (!password.value) {
        errorMessage.value = 'Введите пароль';
        return;
      }
      
      try {
        isLoading.value = true;
        await loginUser({
          email: email.value,
          password: password.value
        });
        emit('close');
      } catch (error) {
        console.error('Ошибка авторизации:', error);
        
        // Отображаем ошибку пользователю
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage.value = error.response.data.message;
        } else {
          errorMessage.value = 'Ошибка при входе. Проверьте логин и пароль.';
        }
      } finally {
        isLoading.value = false;
      }
    };
    
    return { 
      activeTab, 
      email, 
      password, 
      spotifyLogo, 
      openRegistration,
      login,
      errorMessage,
      isLoading
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
  