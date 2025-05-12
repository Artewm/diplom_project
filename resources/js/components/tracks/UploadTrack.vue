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
                <label class="block text-sm font-medium text-white">Аудио файл</label>
                <input 
                    type="file" 
                    @change="handleFileUpload"
                    accept="audio/*"
                    class="mt-1 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                    required
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
                file: null,
                image: null,
                
            },
            isUploading: false
        }
    },
    methods: {
        handleFileUpload(event) {
            this.trackData.file = event.target.files[0];
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
            formData.append('file', this.trackData.file);

            try {
                // TODO: Реализовать загрузку на сервер
                const response = await fetch('/api/tracks', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    this.$emit('show-toast', { message: 'Трек успешно загружен', type: 'success' });
                    this.resetForm();
                    this.$emit('close');
                } else {
                    throw new Error('Ошибка загрузки');
                }
            } catch (error) {
                this.$emit('show-toast', { message: 'Ошибка при загрузке трека', type: 'error' });
            } finally {
                this.isUploading = false;
            }
        },
        resetForm() {
            this.trackData = {
                title: '',
                artist: '',
                file: null
            };
        }
    }
}
</script> 