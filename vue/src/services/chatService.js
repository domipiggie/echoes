import axios from 'axios';
import { userdataStore } from '../store/UserdataStore';
import { API_CONFIG } from '../config/api.js';

export const chatService = {
  async getChannels() {
    const userStore = userdataStore();
    try {
      const response = await axios.get(`${API_CONFIG.BASE_URL}/usrinfo/channellist`, {
        headers: {
          'Authorization': `Bearer ${userStore.getAccessToken()}`
        }
      });
      return response.data;
    } catch (error) {
      console.error('Error fetching channels:', error);
      throw error;
    }
  },

  async getMessages(channelID) {
    const userStore = userdataStore();
    try {
      const response = await axios.get(`${API_CONFIG.BASE_URL}/message/get`, {
        params: { channelID },
        headers: {
          'Authorization': `Bearer ${userStore.getAccessToken()}`
        }
      });
      return response.data;
    } catch (error) {
      console.error('Error fetching messages:', error);
      throw error;
    }
  },

  async sendMessage(channelID, content, type = 'text', replyToId = null) {
    const userStore = userdataStore();
    try {
      const messageData = {
        channelID,
        content,
        type
      };
      
      // Ha van válaszolt üzenet ID, akkor hozzáadjuk a kéréshez
      if (replyToId) {
        messageData.replyToId = replyToId;
      }
      
      const response = await axios.post(`${API_CONFIG.BASE_URL}/message/send`, messageData, {
        headers: {
          'Authorization': `Bearer ${userStore.getAccessToken()}`
        }
      });
      return response.data;
    } catch (error) {
      console.error('Error sending message:', error);
      throw error;
    }
  }
};