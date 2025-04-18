import axios from 'axios';
import { API_CONFIG } from '../config/api.js';
import { userdataStore } from '../store/UserdataStore.js';
import User from '../classes/User';
import Friendship from '../classes/Friendship';

const friendService = {
    async getFriendList() {
        const userStore = userdataStore();
        try {
            const response = await axios.get(`${API_CONFIG.BASE_URL}/userData/friendList`, {
                headers: {
                    'Authorization': `Bearer ${userStore.getAccessToken()}`
                }
            });
            return response.data;
        } catch (error) {
            console.error('Error fetching friend list:', error);
            throw error;
        }
    }
};

export default friendService;