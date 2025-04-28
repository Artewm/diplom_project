import { createRouter, createWebHistory } from 'vue-router'
import MainContent from './components/MainContent.vue'
import Search from './components/Search.vue'
import Library from './components/Library.vue'

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
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router 