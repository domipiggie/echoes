import { apiService } from './apiService.js';

export const chatService = {
  async getChannels() {
    try {
      const response = await apiService.get('/usrinfo/channellist');
      return response.data;
    } catch (error) {
      console.error('Error fetching channels:', error);
      throw error;
    }
  },

  async getMessages(channelID) {
    try {
      const response = await apiService.get('/message/get', { channelID });
      return response.data;
    } catch (error) {
      console.error('Error fetching messages:', error);
      throw error;
    }
  },

  async sendMessage(channelID, content, type = 'text', replyToId = null) {
    try {
      const messageData = {
        channelID,
        content,
        type
      };
      
      if (replyToId) {
        messageData.replyToId = replyToId;
      }
      
      const response = await apiService.post('/message/send', messageData);
      return response.data;
    } catch (error) {
      console.error('Error sending message:', error);
      throw error;
    }
  }
};