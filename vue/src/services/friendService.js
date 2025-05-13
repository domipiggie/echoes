import { apiService } from './apiService.js';

const friendService = {
    async getFriendList() {
        try {
            const response = await apiService.get('/userData/friendList');
            return response.data;
        } catch (error) {
            console.error('Error fetching friend list:', error);
            throw error;
        }
    }
};

export default friendService;