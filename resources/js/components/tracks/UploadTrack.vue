<template>
    <div class="upload-track-container p-4">
        <h2 class="text-2xl font-bold mb-4  text-white">Загрузить трек</h2>
        <form @submit.prevent="uploadTrack" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-white">Название трека</label>
                <input 
                    type="text" 
                    v-model="trackData.title"
                    class="mt-1 pl-2 block w-full h-10 rounded-md bg-transparent border-white border-1 text-white shadow-sm"
                    required
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-white">Исполнитель</label>
                <input 
                    type="text" 
                    v-model="trackData.artist"
                    class="mt-1 pl-2 block h-10 w-full rounded-md bg-transparent border-white border-1 text-white shadow-sm"
                    required
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-white">Жанр</label>
                <input 
                    type="text" 
                    v-model="trackData.genre"
                    class="mt-1 pl-2 block h-10 w-full rounded-md bg-transparent border-white border-1 text-white shadow-sm"
                    placeholder="Укажите жанр (необязательно)"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-white">Аудио файл</label>
                <input 
                    type="file" 
                    @change="handleFileUpload"
                    accept="audio/*"
                    class="mt-1 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                    required
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-white">Обложка (необязательно)</label>
                <input 
                    type="file" 
                    @change="handleImageUpload"
                    accept="image/*"
                    class="mt-1 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                >
            </div>
            <div class="flex gap-2">
                <button 
                    type="submit"
                    class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :disabled="isUploading"
                >
                    {{ isUploading ? 'Загрузка...' : 'Загрузить' }}
                </button>
                <button 
                    type="button"
                    @click="closeModal"
                    class="flex-1 flex justify-center py-2 px-4 border border-gray-600 rounded-md shadow-sm text-sm font-medium text-white bg-gray-700 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                >
                    Отмена
                </button>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    name: 'UploadTrack',
    emits: ['close', 'show-toast'],
    data() {
        return {
            trackData: {
                title: '',
                artist: '',
                genre: '',
                file: null,
                image: null,
            },
            isUploading: false
        }
    },
    methods: {
        getAuthToken() {
            return localStorage.getItem('token');
        },
        handleFileUpload(event) {
            this.trackData.file = event.target.files[0];
        },
        handleImageUpload(event) {
            this.trackData.image = event.target.files[0];
        },
        closeModal() {
            this.$emit('close');
        },
        async uploadTrack() {
            if (!this.trackData.file) {
                this.$emit('show-toast', { message: 'Выберите аудио файл', type: 'error' });
                return;
            }

            this.isUploading = true;
            const formData = new FormData();
            formData.append('title', this.trackData.title);
            formData.append('artist', this.trackData.artist);
            formData.append('genre', this.trackData.genre || 'Неизвестный');
            formData.append('file', this.trackData.file);
            if (this.trackData.image) {
                formData.append('image', this.trackData.image);
            }

            try {
                const token = this.getAuthToken();
                if (!token) {
                    throw new Error('Необходима авторизация');
                }

                const response = await fetch('/api/tracks', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData,
                    credentials: 'include'
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Ошибка загрузки');
                }

                console.log('Успешно загружен трек:', data);
                this.$emit('show-toast', { message: 'Трек успешно загружен', type: 'success' });
                this.resetForm();
                this.$emit('close');
            } catch (error) {
                console.error('Ошибка при загрузке:', error);
                this.$emit('show-toast', { 
                    message: error.message || 'Ошибка при загрузке трека', 
                    type: 'error' 
                });
            } finally {
                this.isUploading = false;
            }
        },
        resetForm() {
            this.trackData = {
                title: '',
                artist: '',
                genre: '',
                file: null,
                image: null
            };
        }
    }
}
</script> 