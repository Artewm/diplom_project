import { createRouter, createWebHistory } from 'vue-router'
import MainContent from './components/layout/MainContent.vue'
import Search from './components/ui/Search.vue'
import Library from './components/tracks/Library.vue'
import Personal from './components/auth/Personal.vue'

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
    component: MainContent
  },
  {
    path: '/registration',
    name: 'registration',
    component: MainContent
  },
  {
    path: '/personal',
    name: 'personal',
    component: Personal
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router 