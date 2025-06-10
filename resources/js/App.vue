<!-- Корневой компонент приложения -->
<template>
  <div class="h-screen flex flex-col bg-black text-white">
    <div class="flex-1 flex overflow-hidden">
      <!-- Sidebar -->
      <Sidebar />

      <!-- Main Content -->
      <main class="flex-1 flex flex-col overflow-hidden">
        <router-view :current-user="currentUser" :is-authenticated="isAuthenticated" />
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
import Sidebar from './components/layout/Sidebar.vue'
import Player from './components/player/Player.vue'
import LoginModal from './components/auth/Login.vue'
import RegistrationModal from './components/auth/Registration.vue'
import { ref, provide, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import authService from './services/auth.js'

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

    // Состояние аутентификации и пользователя
    const isAuthenticated = ref(authService.isAuthenticated())
    const currentUser = ref(null)

    // Загрузка данных пользователя при инициализации
    onMounted(async () => {
      if (isAuthenticated.value) {
        try {
          currentUser.value = await authService.getCurrentUser()
          console.log('Данные пользователя в App.vue:')
          console.log('Имя:', currentUser.value.name || 'Не указано')
          console.log('Email:', currentUser.value.email || 'Не указан')
          console.log('ID:', currentUser.value.sub || 'Не указан')
        } catch (error) {
          console.error('Ошибка при получении данных пользователя:', error)
          isAuthenticated.value = false
        }
      }
    })

    // Методы для работы с аутентификацией
    const login = async (credentials) => {
      try {
        const result = await authService.login(credentials)
        isAuthenticated.value = true
        currentUser.value = await authService.getCurrentUser()
        closeLoginModal()
        return result
      } catch (error) {
        console.error('Ошибка при входе:', error)
        throw error
      }
    }

    const register = async (userData) => {
      try {
        const result = await authService.register(userData)
        isAuthenticated.value = true
        currentUser.value = await authService.getCurrentUser()
        closeRegistrationModal()
        return result
      } catch (error) {
        console.error('Ошибка при регистрации:', error)
        throw error
      }
    }

    const logout = async () => {
      try {
        await authService.logout()
        isAuthenticated.value = false
        currentUser.value = null
      } catch (error) {
        console.error('Ошибка при выходе:', error)
      }
    }

    // Методы для управления модальными окнами
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
    
    // Открытие модальных окон при переходе по маршрутам
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
    
    // Предоставляем функции и данные для использования в дочерних компонентах
    provide('isAuthenticated', isAuthenticated)
    provide('currentUser', currentUser)
    provide('login', login)
    provide('register', register)
    provide('logout', logout)
    provide('openLoginModal', openLoginModal)
    provide('openRegistrationModal', openRegistrationModal)
    provide('openPersonalModal', () => {}) // Заглушка для openPersonalModal
    
    return { 
      isLoginModalOpen, 
      isRegistrationModalOpen, 
      openLoginModal, 
      closeLoginModal, 
      openRegistrationModal, 
      closeRegistrationModal,
      isAuthenticated,
      currentUser
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