import axios from 'axios';
import { API_CONFIG } from '../config/api.js';

export const authService = {
  async register(username, email, password) {
    try {
      const response = await axios.post(`${API_CONFIG.BASE_URL}/auth/register`, {
        username,
        email,
        password
      });
      return response.data;
    } catch (error) {
      console.error('Registration error:', error);
      throw error;
    }
  },
  async login(email, password) {
    try {
      const response = await axios.post(`${API_CONFIG.BASE_URL}/auth/login`, {
        email,
        password
      });
      return response.data;
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  },
  async refresh(refreshToken) {
    try {
      const response = await axios.post(`${API_CONFIG.BASE_URL}/auth/refresh`, {
        refresh_token: refreshToken
      });
      return response.data;
    } catch (error) {
      console.error('Refresh token error:', error);
      throw error;
    }
  }
};