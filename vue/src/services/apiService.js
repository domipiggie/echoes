import axios from 'axios';
import { API_CONFIG } from '../config/api.js';
import { userdataStore } from '../store/UserdataStore.js';
import { authService } from './authService.js';

const apiClient = axios.create({
  baseURL: API_CONFIG.BASE_URL
});

let isRefreshing = false;
let failedQueue = [];

const processQueue = (error, token = null) => {
  failedQueue.forEach(prom => {
    if (error) {
      prom.reject(error);
    } else {
      prom.resolve(token);
    }
  });
  
  failedQueue = [];
};

apiClient.interceptors.response.use(
  response => response,
  async error => {
    const originalRequest = error.config;
    const userStore = userdataStore();
    
    if (error.response?.status === 401 && !originalRequest._retry && !isRefreshing) {
      originalRequest._retry = true;
      isRefreshing = true;
      
      try {
        const refreshToken = userStore.getRefreshToken();
        
        if (!refreshToken) {
          throw new Error('No refresh token available');
        }
        
        const response = await authService.refresh(refreshToken);
        
        if (response.data && response.data.status === "success") {
          userStore.setAccessToken(response.data.access_token);
          userStore.setRefreshToken(response.data.refresh_token);
          
          originalRequest.headers['Authorization'] = `Bearer ${response.data.access_token}`;
          
          processQueue(null, response.data.access_token);
          
          return apiClient(originalRequest);
        } else {
          throw new Error('Token refresh failed');
        }
      } catch (refreshError) {
        processQueue(refreshError, null);
        
        userStore.clearAuth();
        
        return Promise.reject(refreshError);
      } finally {
        isRefreshing = false;
      }
    } else if (error.response?.status === 401 && isRefreshing) {
      return new Promise((resolve, reject) => {
        failedQueue.push({ resolve, reject });
      })
        .then(token => {
          originalRequest.headers['Authorization'] = `Bearer ${token}`;
          return apiClient(originalRequest);
        })
        .catch(err => {
          return Promise.reject(err);
        });
    }
    
    return Promise.reject(error);
  }
);

const getAuthHeader = () => {
  const userStore = userdataStore();
  return {
    'Authorization': `Bearer ${userStore.getAccessToken()}`
  };
};

export const apiService = {
  get(url, params = {}) {
    return apiClient.get(url, { 
      params,
      headers: getAuthHeader()
    });
  },
  
  post(url, data = {}, config = {}) {
    return apiClient.post(url, data, {
      ...config,
      headers: {
        ...getAuthHeader(),
        ...(config.headers || {})
      }
    });
  },
  
  put(url, data = {}) {
    return apiClient.put(url, data, {
      headers: getAuthHeader()
    });
  },
  
  delete(url) {
    return apiClient.delete(url, {
      headers: getAuthHeader()
    });
  }
};