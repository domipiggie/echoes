import { apiService } from './apiService.js';
import { useMessageStore } from '../store/MessageStore.js';

export const fileService = {
  async uploadFile(file) {
    try {
      const response = await apiService.post('/files', file, {
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
      const messageStore = useMessageStore();
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
  },
  async getFiles() {
    try {
      const response = await apiService.get('/files');
      return response.data;
    } catch (error) {
      console.error('Error fetching files:', error);
      throw error;
    }
  },
  async deleteFile(fileID) {
    try {
      const response = await apiService.delete(`/files/${fileID}`);
      return response.data;
    } catch (error) {
      console.error('Error deleting file:', error);
      throw error;
    }
  }
};