import axios from 'axios';
import { API_CONFIG } from '../config/api.js';

export const userService = {
  async getUserByUsername(username) {
    
    try {
      const response = await axios.get(`${API_CONFIG.BASE_URL}/userInfo/username/${username.trim()}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching user by username:', error);
      throw error;
    }
  }
};