<template>
    <div class="upload-track-container p-4">
        <h2 class="text-2xl font-bold mb-4">Загрузить трек</h2>
        <form @submit.prevent="uploadTrack" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Название трека</label>
                <input 
                    type="text" 
                    v-model="trackData.title"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Исполнитель</label>
                <input 
                    type="text" 
                    v-model="trackData.artist"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Аудио файл</label>
                <input 
                    type="file" 
                    @change="handleFileUpload"
                    accept="audio/*"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    required
                >
            </div>
            <div>
                <button 
                    type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    :disabled="isUploading"
                >
                    {{ isUploading ? 'Загрузка...' : 'Загрузить' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    name: 'UploadTrack',
    data() {
        return {
            trackData: {
                title: '',
                artist: '',
                file: null
            },
            isUploading: false
        }
    },
    methods: {
        handleFileUpload(event) {
            this.trackData.file = event.target.files[0];
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