<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="flex flex-col items-center justify-between w-1/4 bg-zinc-800 rounded-lg z-10 p-6">
      <h2 class="text-xl font-bold mb-6">Создать плейлист</h2>
      
      <div v-if="error" class="w-full p-2 mb-4 bg-red-500/20 text-red-500 rounded-md text-center">
        {{ error }}
      </div>
      
      <div class="w-full mb-6">
        <label class="block text-sm font-medium text-gray-300 mb-2">Название плейлиста</label>
        <input 
          v-model="name" 
          type="text" 
          placeholder="Мой новый плейлист" 
          class="w-full p-2 rounded-md bg-transparent border-2 border-gray-600"
          @keyup.enter="createPlaylist">
      </div>
      
      <div class="w-full mb-4">
        <label class="flex items-center space-x-2">
          <input 
            v-model="isPublic" 
            type="checkbox" 
            class="rounded text-green-500 focus:ring-green-500">
          <span class="text-sm text-gray-300">Открытый плейлист</span>
        </label>
      </div>
      
      <div class="flex items-center justify-end w-full gap-4">
        <button 
          @click="$emit('close')"
          class="px-4 py-2 rounded-md bg-transparent border border-gray-600 text-gray-300 hover:bg-gray-700 transition-colors">
          Отмена
        </button>
        <button 
          @click="createPlaylist"
          :disabled="loading || !name.trim()"
          class="px-4 py-2 rounded-md bg-green-500 text-white hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
          <span v-if="loading">Создание...</span>
          <span v-else>Создать</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import playlistService from '../../services/playlist';

export default {
  name: 'CreatePlaylistModal',
  props: {
    trackId: {
      type: Number,
      default: null
    }
  },
  emits: ['close', 'playlist-created'],
  setup(props, { emit }) {
    const router = useRouter();
    const name = ref('');
    const isPublic = ref(true);
    const loading = ref(false);
    const error = ref(null);
    
    const createPlaylist = async () => {
      if (!name.value.trim()) {
        error.value = 'Введите название плейлиста';
        return;
      }
      
      loading.value = true;
      error.value = null;
      
      try {
        const newPlaylist = await playlistService.createPlaylist({
          name: name.value.trim(),
          is_public: isPublic.value
        });
        
        // Если указан trackId, добавляем трек в новый плейлист
        if (props.trackId) {
          await playlistService.addTrackToPlaylist(newPlaylist.id, props.trackId);
        }
        
        // Уведомляем родительский компонент о создании плейлиста
        emit('playlist-created', newPlaylist);
        
        // Если trackId не указан, переходим на страницу нового плейлиста
        if (!props.trackId) {
          router.push({ name: 'playlist', params: { id: newPlaylist.id } });
        }
      } catch (err) {
        console.error('Ошибка при создании плейлиста:', err);
        error.value = 'Не удалось создать плейлист';
      } finally {
        loading.value = false;
      }
    };
    
    return {
      name,
      isPublic,
      loading,
      error,
      createPlaylist
    };
  }
}
</script> 