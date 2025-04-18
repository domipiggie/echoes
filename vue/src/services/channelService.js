import axios from 'axios';
import { API_CONFIG } from '../config/api.js';
import { userdataStore } from '../store/UserdataStore.js';

export const channelService = {
  async getFriendChannels() {
    const userStore = userdataStore();
    try {
      const response = await axios.get(`${API_CONFIG.BASE_URL}/userData/friendChannels`, {
        headers: {
          'Authorization': `Bearer ${userStore.getAccessToken()}`
        }
      });
      return response.data;
    } catch (error) {
      console.error('Error fetching friend channels:', error);
      throw error;
    }
  },

  async getGroupChannels() {
    const userStore = userdataStore();
    try {
      const response = await axios.get(`${API_CONFIG.BASE_URL}/userData/groupChannels`, {
        headers: {
          'Authorization': `Bearer ${userStore.getAccessToken()}`
        }
      });
      return response.data;
    } catch (error) {
      console.error('Error fetching group channels:', error);
      throw error;
    }
  }
};