<!-- Корневой компонент приложения -->
<template>
  <div class="h-screen flex flex-col bg-black text-white">
    <div class="flex-1 flex overflow-hidden">
      <!-- Sidebar -->
      <Sidebar />

      <!-- Main Content -->
      <main class="flex-1 flex flex-col overflow-hidden">
        <router-view></router-view>
      </main>
    </div>

    <!-- Player -->
    <Player />
    
    <!-- Модальные окна -->
    <LoginModal v-if="isLoginModalOpen" @close="closeLoginModal" />
    <RegistrationModal v-if="isRegistrationModalOpen" @close="closeRegistrationModal" />
  </div>
</template>

<script>
import Sidebar from './components/Sidebar.vue'
import Player from './components/Player.vue'
import LoginModal from './components/Login.vue'
import RegistrationModal from './components/Registration.vue'
import { ref, provide } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export default {
  name: 'App',
  components: {
    Sidebar,
    Player,
    LoginModal,
    RegistrationModal
  },
  setup() {
    const isLoginModalOpen = ref(false)
    const isRegistrationModalOpen = ref(false)
    const route = useRoute()
    const router = useRouter()
    
    const openLoginModal = () => {
      isLoginModalOpen.value = true
      router.push('/', { replace: true })
    }
    
    const closeLoginModal = () => {
      isLoginModalOpen.value = false
    }
    
    const openRegistrationModal = () => {
      isRegistrationModalOpen.value = true
      router.push('/', { replace: true })
    }
    
    const closeRegistrationModal = () => {
      isRegistrationModalOpen.value = false
    }
    
    // Отслеживаем изменение маршрутов
    router.beforeEach((to, from, next) => {
      if (to.path === '/login') {
        openLoginModal()
        return next(false)
      } else if (to.path === '/registration') {
        openRegistrationModal()
        return next(false)
      }
      return next()
    })
    
    // Предоставляем функции для использования в дочерних компонентах
    provide('openLoginModal', openLoginModal)
    provide('openRegistrationModal', openRegistrationModal)
    
    return { 
      isLoginModalOpen, 
      isRegistrationModalOpen, 
      openLoginModal, 
      closeLoginModal, 
      openRegistrationModal, 
      closeRegistrationModal 
    }
  }
}
</script>

<style>
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';

::-webkit-scrollbar {
  width: 12px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.4);
}
</style> 