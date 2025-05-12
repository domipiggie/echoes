import { apiService } from './apiService.js';

export const messageService = {
  async getChannelMessages(channelId, offset = 0, limit = 20) {
    try {
      const response = await apiService.get(`/messages/${channelId}`, {
        offset,
        limit
      });
      return response.data;
    } catch (error) {
      console.error(`Error fetching messages for channel ${channelId}:`, error);
      throw error;
    }
  },
  
  async editMessage(messageId, newContent) {
    try {
      const response = await apiService.put('/message/edit', {
        messageId,
        content: newContent
      });
      return response.data;
    } catch (error) {
      console.error(`Error editing message ${messageId}:`, error);
      throw error;
    }
  }
};