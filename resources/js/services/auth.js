import axios from 'axios';

// Настройка axios для автоматического добавления токена в заголовки
const setAuthHeader = (token) => {
  if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    delete axios.defaults.headers.common['Authorization'];
  }
};

// Проверка наличия токена и его установка при инициализации приложения
const token = localStorage.getItem('token');
if (token) {
  setAuthHeader(token);
}

export default {
  // Получить текущего пользователя
  async getCurrentUser() {
    try {
      console.log('Получение данных пользователя, токен:', localStorage.getItem('token'));
      
      // Декодируем данные из JWT токена
      const token = localStorage.getItem('token');
      if (token) {
        try {
          // Получаем среднюю часть JWT токена (payload)
          const base64Url = token.split('.')[1];
          const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
          const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
          }).join(''));
          
          // Парсим данные пользователя из токена
          const tokenData = JSON.parse(jsonPayload);
          console.log('Данные из токена:', tokenData);
          
          // Проверяем наличие основных данных в токене
          if (tokenData.name && tokenData.email) {
            console.log('ДАННЫЕ ПОЛЬЗОВАТЕЛЯ:');
            console.log('Имя пользователя:', tokenData.name);
            console.log('Email пользователя:', tokenData.email);
            console.log('ID пользователя:', tokenData.sub);
            return tokenData;
          }
          
          // Если в токене только ID, получаем дополнительные данные
          const userId = tokenData.sub;
          if (userId) {
            try {
              // Запрашиваем полные данные пользователя из API по ID
              const userResponse = await axios.get(`/api/users/${userId}`);
              console.log('Полные данные пользователя из БД:', userResponse.data);
              
              // Объединяем данные из токена и API
              return {
                ...tokenData,
                ...userResponse.data
              };
            } catch (userApiError) {
              console.error('Ошибка при запросе данных пользователя по ID:', userApiError);
              // Если запрос по ID не удался, возвращаем хотя бы данные из токена
              return tokenData;
            }
          }
          
          // Возвращаем данные пользователя из токена
          return tokenData;
        } catch (err) {
          console.error('Ошибка при декодировании токена:', err);
        }
      }
      
      // Если не удалось получить данные из токена, используем запасной вариант
      return {
        name: 'Пользователь',
        email: 'user@example.com'
      };
    } catch (error) {
      console.error('Ошибка при получении данных пользователя:', error);
      if (error.response) {
        console.error('Статус ошибки:', error.response.status);
        console.error('Данные ошибки:', error.response.data);
      }
      // Используем заглушку в случае ошибки
      return {
        name: 'Пользователь',
        email: 'user@example.com'
      };
    }
  },

  // Проверка аутентификации пользователя
  isAuthenticated() {
    return !!localStorage.getItem('token');
  },

  // Вход пользователя
  async login(credentials) {
    try {
      // Выполняем запрос на вход
      const response = await axios.post('/api/auth/login', credentials);
      console.log('Ответ API при логине:', response.data);
      
      const token = response.data.access_token || response.data.token;
      if (!token) {
        console.error('API не вернул токен в ожидаемом формате');
        throw new Error('Токен доступа не получен');
      }
      
      localStorage.setItem('token', token);
      setAuthHeader(token);
      return response.data;
    } catch (error) {
      console.error('Ошибка при входе:', error);
      if (error.response) {
        console.error('Статус ошибки:', error.response.status);
        console.error('Данные ошибки:', error.response.data);
      }
      throw error;
    }
  },

  // Регистрация пользователя
  async register(userData) {
    try {
      // Выполняем запрос на регистрацию
      const response = await axios.post('/api/auth/register', userData);
      console.log('Ответ API при регистрации:', response.data);
      
      const token = response.data.access_token || response.data.token;
      if (!token) {
        console.error('API не вернул токен в ожидаемом формате');
        throw new Error('Токен доступа не получен');
      }
      
      localStorage.setItem('token', token);
      setAuthHeader(token);
      return response.data;
    } catch (error) {
      console.error('Ошибка при регистрации:', error);
      if (error.response) {
        console.error('Статус ошибки:', error.response.status);
        console.error('Данные ошибки:', error.response.data);
      }
      throw error;
    }
  },

  // Выход пользователя
  async logout() {
    try {
      // Выполняем запрос на выход на сервере
      await axios.post('/api/auth/logout');
    } catch (error) {
      console.error('Ошибка при выходе:', error);
    } finally {
      // В любом случае удаляем токен из localStorage и заголовков
      localStorage.removeItem('token');
      setAuthHeader(null);
    }
  }
}; 