import axios from 'axios';
import { userdataStore } from '../store/UserdataStore';
import { API_CONFIG } from '../config/api.js';

export default {
  async getPendingFriendRequests() {
    try {
      const store = userdataStore();
      const token = store.getAccessToken();

      if (!token) {
        throw new Error('User not authenticated');
      }

      const response = await axios.get(`${API_CONFIG.BASE_URL}/usrinfo/friendlist`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      const pendingRequests = response.data.filter(friend =>
        friend.status === 0 &&
        friend.initiator != store.getUserID()
      );

      return pendingRequests.map(request => ({
        id: request.friendshipID,
        friendID: request.friendID,
        username: request.username,
        displayName: request.displayName,
        profilePicture: request.profilePicture,
        timestamp: new Date()
      }));
    } catch (error) {
      console.error('Error fetching friend requests:', error);
      throw error;
    }
  },

  async acceptFriendRequest(userID) {
    try {
      const store = userdataStore();
      const token = store.getAccessToken();

      if (!token) {
        throw new Error('User not authenticated');
      }

      const response = await axios.put(`${API_CONFIG.BASE_URL}/friend/accept`,
        { userID },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );

      return response.data;
    } catch (error) {
      console.error('Error accepting friend request:', error);
      throw error;
    }
  },

  async declineFriendRequest(userID) {
    try {
      const store = userdataStore();
      const token = store.getAccessToken();

      if (!token) {
        throw new Error('User not authenticated');
      }

      const response = await axios.put(`${API_CONFIG.BASE_URL}/friend/deny`,
        { userID },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );

      return response.data;
    } catch (error) {
      console.error('Error declining friend request:', error);
      throw error;
    }
  },
  
  async sendFriendRequest(username) {
    try {
      const store = userdataStore();
      const token = store.getAccessToken();
      
      if (!token) {
        throw new Error('User not authenticated');
      }
      
      const userResponse = await axios.get(`${API_CONFIG.BASE_URL}/usrinfo/search`, {
        params: { username },
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      
      if (!userResponse.data || !userResponse.data.userID) {
        throw new Error('User not found');
      }
      
      const userID = userResponse.data.userID;
      
      const response = await axios.post(`${API_CONFIG.BASE_URL}/friend/add`, 
        { userID },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );
      
      return response.data;
    } catch (error) {
      console.error('Error sending friend request:', error);
      throw error;
    }
  }
};