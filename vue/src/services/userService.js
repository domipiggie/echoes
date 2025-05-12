import { apiService } from './apiService.js';

export const userService = {
  async getUserByUsername(username) {
    try {
      const response = await apiService.get(`/userInfo/username/${username.trim()}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching user by username:', error);
      throw error;
    }
  },
  
  async getUserByID(id) {
    try {
      const response = await apiService.get(`/userInfo/id/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error fetching user by id:', error);
      throw error;
    }
  },

  async updateUser(body) {
    try {
      const response = await apiService.put(`/userData/updateProfile`, body);
      return response.data;
    } catch (error) {
      console.error('Error updating user:', error);
      throw error;
    }
  }
};