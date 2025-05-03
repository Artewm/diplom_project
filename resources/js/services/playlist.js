import axios from 'axios';

export default {
    // Получить все плейлисты пользователя
    async getUserPlaylists() {
        try {
            const response = await axios.get('/api/playlists');
            return response.data;
        } catch (error) {
            console.error('Ошибка при получении плейлистов:', error);
            throw error;
        }
    },
    
    // Создать новый плейлист
    async createPlaylist(playlistData) {
        try {
            const response = await axios.post('/api/playlists', playlistData);
            return response.data;
        } catch (error) {
            console.error('Ошибка при создании плейлиста:', error);
            throw error;
        }
    },
    
    // Получить плейлист по ID с треками
    async getPlaylist(id) {
        try {
            const response = await axios.get(`/api/playlists/${id}`);
            return response.data;
        } catch (error) {
            console.error(`Ошибка при получении плейлиста ID=${id}:`, error);
            throw error;
        }
    },
    
    // Обновить плейлист
    async updatePlaylist(id, playlistData) {
        try {
            const response = await axios.put(`/api/playlists/${id}`, playlistData);
            return response.data;
        } catch (error) {
            console.error(`Ошибка при обновлении плейлиста ID=${id}:`, error);
            throw error;
        }
    },
    
    // Удалить плейлист
    async deletePlaylist(id) {
        try {
            const response = await axios.delete(`/api/playlists/${id}`);
            return response.data;
        } catch (error) {
            console.error(`Ошибка при удалении плейлиста ID=${id}:`, error);
            throw error;
        }
    },
    
    // Добавить трек в плейлист
    async addTrackToPlaylist(playlistId, trackId) {
        try {
            const response = await axios.post(`/api/playlists/${playlistId}/tracks`, {
                track_id: trackId
            });
            return response.data;
        } catch (error) {
            console.error(`Ошибка при добавлении трека в плейлист ID=${playlistId}:`, error);
            throw error;
        }
    },
    
    // Удалить трек из плейлиста
    async removeTrackFromPlaylist(playlistId, trackId) {
        try {
            const response = await axios.delete(`/api/playlists/${playlistId}/tracks`, {
                data: { track_id: trackId }
            });
            return response.data;
        } catch (error) {
            console.error(`Ошибка при удалении трека из плейлиста ID=${playlistId}:`, error);
            throw error;
        }
    }
}; 