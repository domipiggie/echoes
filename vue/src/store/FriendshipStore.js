import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import friendService from '../services/friendService';
import Friendship from '../classes/Friendship';
import User from '../classes/User';

export const useFriendshipStore = defineStore('friendship', () => {
    const friendships = ref([]);
    const isLoading = ref(false);
    const error = ref(null);

    const getFriendships = computed(() => friendships.value);
    const getIsLoading = computed(() => isLoading.value);
    const getError = computed(() => error.value);

    const getPendingRequests = computed(() =>
        friendships.value.filter(friendship => friendship.getStatus() === 0)
    );

    const getAcceptedFriendships = computed(() =>
        friendships.value.filter(friendship => friendship.getStatus() === 1)
    );

    const getFriendshipById = computed(() => (friendshipId) =>
        friendships.value.find(friendship => friendship.getFriendshipID() === friendshipId)
    );

    const getFriendshipByUserId = computed(() => (userId) =>
        friendships.value.find(friendship =>
            friendship.getTargetUser() && friendship.getTargetUser().getUserID() === userId
        )
    );

    function mapToFriendshipInstance(friendshipData) {
        let targetUser = null;

        if (friendshipData.friendID) {
            targetUser = new User(
                friendshipData.friendID,
                friendshipData.username,
                friendshipData.displayName || '',
                friendshipData.profilePicture
            );
        }

        return new Friendship(
            friendshipData.friendshipID,
            friendshipData.statusID,
            friendshipData.status,
            targetUser
        );
    }

    async function fetchFriendships() {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await friendService.getFriendList();

            if (response.success) {
                friendships.value = response.data.map(friendship => mapToFriendshipInstance(friendship));
            } else {
                throw new Error(response.message || 'Failed to fetch friendships');
            }
        } catch (err) {
            error.value = err.message || 'Failed to fetch friendships';
            console.error('Error fetching friendships:', err);
        } finally {
            isLoading.value = false;
        }
    }

    async function updateFriendshipStatus(friendshipId, newStatus) {
        const friendshipIndex = friendships.value.findIndex(
            friendship => friendship.getFriendshipID() === friendshipId
        );

        if (friendshipIndex !== -1) {
            const existingFriendship = friendships.value[friendshipIndex];
            const targetUser = existingFriendship.getTargetUser();

            const updatedFriendship = new Friendship(
                existingFriendship.getFriendshipID(),
                existingFriendship.getStatusID(),
                newStatus,
                targetUser
            );

            friendships.value.splice(friendshipIndex, 1, updatedFriendship);
        }
    }

    function addWebSocketFriendRequest(friendRequestData) {
        const targetUser = new User(
            friendRequestData.sender.id,
            friendRequestData.sender.username,
            friendRequestData.sender.displayName || '',
            friendRequestData.sender.profilePicture
        );

        const newFriendship = new Friendship(
            friendRequestData.friendshipId,
            null,
            0,
            targetUser
        );

        if (!friendships.value.some(f => f.getFriendshipID() === friendRequestData.friendshipId)) {
            friendships.value.push(newFriendship);
        }
    }

    function updateWebSocketFriendshipStatus(friendshipId, newStatus) {
        const friendshipIndex = friendships.value.findIndex(
            friendship => friendship.getFriendshipID() === friendshipId
        );

        if (friendshipIndex !== -1) {
            const existingFriendship = friendships.value[friendshipIndex];
            const targetUser = existingFriendship.getTargetUser();

            const updatedFriendship = new Friendship(
                existingFriendship.getFriendshipID(),
                existingFriendship.getStatusID(),
                newStatus,
                targetUser
            );

            friendships.value.splice(friendshipIndex, 1, updatedFriendship);
        }
    }

    function clearFriendships() {
        friendships.value = [];
        error.value = null;
    }

    return {
        getFriendships,
        getIsLoading,
        getError,
        getPendingRequests,
        getAcceptedFriendships,
        getFriendshipById,
        getFriendshipByUserId,

        fetchFriendships,
        updateFriendshipStatus,
        addWebSocketFriendRequest,
        updateWebSocketFriendshipStatus,
        clearFriendships
    };
});