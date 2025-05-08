import axios from 'axios';

/**
 * Сервис для работы с избранными треками
 */
export default {
    /**
     * Получить список избранных треков пользователя
     *
     * @returns {Promise} Промис с результатом запроса
     */
    getUserFavorites() {
        return axios.get('/api/favorites')
            .then(response => {
                // Проверка ответа и логирование для отладки
                console.log('Ответ API favorites:', response);
                return response;
            })
            .catch(error => {
                console.error('Ошибка в запросе избранного:', error);
                throw error;
            });
    },

    /**
     * Добавить трек в избранное
     *
     * @param {Number} trackId ID трека
     * @returns {Promise} Промис с результатом запроса
     */
    addToFavorites(trackId) {
        return axios.post(`/api/favorites/${trackId}`);
    },

    /**
     * Удалить трек из избранного
     *
     * @param {Number} trackId ID трека
     * @returns {Promise} Промис с результатом запроса
     */
    removeFromFavorites(trackId) {
        return axios.delete(`/api/favorites/${trackId}`);
    },

    /**
     * Проверить, находится ли трек в избранном
     *
     * @param {Array} favorites Массив избранных треков
     * @param {Number} trackId ID трека для проверки
     * @returns {Boolean} true если трек в избранном, false в противном случае
     */
    isTrackFavorite(favorites, trackId) {
        if (!Array.isArray(favorites) || !favorites.length) return false;
        return favorites.some(track => track.id === trackId);
    }
}; 