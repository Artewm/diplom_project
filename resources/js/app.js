import './bootstrap';
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import '../css/app.css'
import mitt from 'mitt'

// Создаем глобальный экземпляр шины событий
window.emitter = mitt()

const app = createApp(App)
app.use(router)
app.mount('#app')
