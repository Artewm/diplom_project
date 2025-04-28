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
  </div>
</template>

<script>
import Sidebar from './components/Sidebar.vue'
import Player from './components/Player.vue'

export default {
  name: 'App',
  components: {
    Sidebar,
    Player
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