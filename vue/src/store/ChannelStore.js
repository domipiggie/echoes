import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { channelService } from '../services/channelService';
import FriendshipChannel from '../classes/FriendshipChannel';
import GroupChannel from '../classes/GroupChannel';
import User from '../classes/User';

export const useChannelStore = defineStore('channel', () => {
  const friendChannels = ref([]);
  const groupChannels = ref([]);
  const isLoading = ref(false);
  const error = ref(null);

  const getIsLoading = computed(() => isLoading.value);
  const getError = computed(() => error.value);
  const getFriendChannels = computed(() => friendChannels.value);
  const getGroupChannels = computed(() => groupChannels.value);

  const getFriendChannelById = computed(() => (channelId) => {
    return friendChannels.value.find(channel => channel.getChannelID() === channelId);
  });

  const getGroupChannelById = computed(() => (channelId) => {
    return groupChannels.value.find(channel => channel.getChannelID() === channelId);
  });

  function updateLastMessage(user, message, id) {
    var channel = getFriendChannelById.value(id) || getGroupChannelById.value(id);
  
    if (!channel) {
      return false;
    }
  
    channel.setLastMessageUsername(user);
    channel.setLastMessage(message);
    
    if (getFriendChannelById.value(id)) {
      const index = friendChannels.value.findIndex(c => c.getChannelID() === id);
      if (index !== -1) {
        const updatedChannels = [...friendChannels.value];
        friendChannels.value = updatedChannels;
      }
    } else if (getGroupChannelById.value(id)) {
      const index = groupChannels.value.findIndex(c => c.getChannelID() === id);
      if (index !== -1) {
        const updatedChannels = [...groupChannels.value];
        groupChannels.value = updatedChannels;
      }
    }
    
    return true;
  }

  function mapToUserInstance(userData) {
    return new User(
      userData.id,
      userData.username,
      userData.displayName || '',
      userData.profilePicture
    );
  }

  async function fetchFriendChannels() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await channelService.getFriendChannels();

      if (response.success) {
        friendChannels.value = response.data.map(channel =>
          new FriendshipChannel(
            channel.channelID,
            mapToUserInstance(channel.user1),
            mapToUserInstance(channel.user2),
            channel.lastMessageUsername,
            channel.lastMessage
          )
        );
      } else {
        throw new Error('Failed to fetch friend channels');
      }
    } catch (err) {
      error.value = err.message || 'Failed to fetch friend channels';
      console.error('Error fetching friend channels:', err);
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchGroupChannels() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await channelService.getGroupChannels();

      if (response.success) {
        groupChannels.value = response.data.map(channel =>
          new GroupChannel(
            channel.channelID,
            channel.users.map(user => mapToUserInstance(user)),
            channel.groupName,
            channel.groupPicture,
            channel.groupOwnerID,
            channel.lastMessageUsername,
            channel.lastMessage
          )
        );
      } else {
        throw new Error('Failed to fetch group channels');
      }
    } catch (err) {
      error.value = err.message || 'Failed to fetch group channels';
      console.error('Error fetching group channels:', err);
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchAllChannels() {
    await Promise.all([
      fetchFriendChannels(),
      fetchGroupChannels()
    ]);
  }

  function clearChannels() {
    friendChannels.value = [];
    groupChannels.value = [];
    error.value = null;
  }

  return {
    getIsLoading,
    getError,
    getFriendChannels,
    getGroupChannels,
    getFriendChannelById,
    getGroupChannelById,
    updateLastMessage,
    fetchFriendChannels,
    fetchGroupChannels,
    fetchAllChannels,
    clearChannels
  };
});