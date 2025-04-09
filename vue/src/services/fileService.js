import axios from 'axios';
import { userdataStore } from '../store/UserdataStore';
import { API_CONFIG } from '../config/api.js';

export const fileService = {
  async uploadFile(file, channelID) {
    const userStore = userdataStore();
    try {
      const formData = new FormData();
      formData.append('file', file);
      formData.append('channelID', channelID);

      const response = await axios.post(`${API_CONFIG.BASE_URL}/file/upload`, formData, {
        headers: {
          'Authorization': `Bearer ${userStore.getAccessToken()}`,
          'Content-Type': 'multipart/form-data'
        }
      });
      
      return response.data;
    } catch (error) {
      console.error('Error uploading file:', error);
      throw error;
    }
  }
};