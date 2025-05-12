import { apiService } from './apiService.js';
import { userdataStore } from '../store/UserdataStore.js';
import { useMessageStore } from '../store/MessageStore.js';

const userStore = userdataStore();
const messageStore = useMessageStore();

export const fileService = {
  async uploadFile(file) {
    try {
      const formData = new FormData();
      formData.append('file', file);

      const response = await apiService.post('/files', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      return response.data;
    } catch (error) {
      console.error('Error uploading file:', error);
      throw error;
    }
  },

  async uploadUserProfile(file) {
    try {
      const formData = new FormData();
      formData.append('file', file);

      const response = await apiService.post('/pfp/user', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      return response.data;
    } catch (error) {
      console.error('Error uploading file:', error);
      throw error;
    }
  },

  async uploadGroupProfile(file) {
    try {
      const formData = new FormData();
      formData.append('file', file);

      console.log('Uploading profile picture...');

      const response = await apiService.post(`/pfp/group/${messageStore.getCurrentChannelId}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      return response.data;
    } catch (error) {
      console.error('Error uploading file:', error);
      throw error;
    }
  },
  async getUsedSpace() {
    try {
      const response = await apiService.get('/files/size');
      return response.data;
    } catch (error) {
      console.error('Error fetching used space:', error);
      throw error;
    }
  }
};