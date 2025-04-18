import axios from 'axios';
import { API_CONFIG } from '../config/api.js';
import { userdataStore } from '../store/UserdataStore.js';

export const messageService = {
  async getChannelMessages(channelId, offset = 0, limit = 20) {
    const userStore = userdataStore();
    try {
      const response = await axios.get(`${API_CONFIG.BASE_URL}/messages/${channelId}`, {
        headers: {
          'Authorization': `Bearer ${userStore.getAccessToken()}`
        },
        params: {
          offset,
          limit
        }
      });
      return response.data;
    } catch (error) {
      console.error(`Error fetching messages for channel ${channelId}:`, error);
      throw error;
    }
  }
};