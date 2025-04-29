import { createRouter, createWebHistory } from 'vue-router'
import MainContent from './components/MainContent.vue'
import Search from './components/Search.vue'
import Library from './components/Library.vue'
import Login from './components/Login.vue'
import Registration from './components/Registration.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: MainContent
  },
  {
    path: '/search',
    name: 'search',
    component: Search
  },
  {
    path: '/library',
    name: 'library',
    component: Library
  },
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/registration',
      name: 'registration',
      component: Registration
    }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router 