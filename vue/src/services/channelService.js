import { apiService } from './apiService.js';
import { API_CONFIG } from '../config/api.js';

export const channelService = {
  async getFriendChannels() {
    try {
      const response = await apiService.get('/userData/friendChannels');
      return response.data;
    } catch (error) {
      console.error('Error fetching friend channels:', error);
      throw error;
    }
  },

  async getGroupChannels() {
    try {
      const response = await apiService.get('/userData/groupChannels');
      return response.data;
    } catch (error) {
      console.error('Error fetching group channels:', error);
      throw error;
    }
  }
};