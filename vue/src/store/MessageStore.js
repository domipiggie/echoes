import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { messageService } from '../services/messageService';
import Message from '../classes/Message';
import User from '../classes/User';

export const useMessageStore = defineStore('message', () => {
  const messages = ref([]);
  const currentChannelId = ref(null);
  const currentChannelName = ref("ccc");
  const isLoading = ref(false);
  const error = ref(null);
  const pagination = ref({
    offset: 0,
    limit: 20,
    total: 0
  });

  const getMessages = computed(() => messages.value);
  const getNotTextMessages = computed(() => {
    return messages.value.filter(message => message.getType() !== 'text');
  });
  const getCurrentChannelId = computed(() => currentChannelId.value);
  const getCurrentChannelName = computed(() => currentChannelName.value);
  const getIsLoading = computed(() => isLoading.value);
  const getError = computed(() => error.value);
  const getPagination = computed(() => pagination.value);

  const getMessageById = computed(() => (messageId) => {
    return messages.value.find(message => message.getMessageID() === messageId);
  });

  const setCurrentChannelId = (channelId) => {
    currentChannelId.value = channelId;
    pagination.value = {
      offset: 0,
      limit: 20,
      total: 0
    }
  };
  const setCurrentChannelName = (channelName) => {
    currentChannelName.value = channelName;
  };

  function mapToMessageInstance(messageData) {
    let user = null;
    if (messageData.user) {
      user = new User(
        messageData.user.userID,
        messageData.user.username,
        messageData.user.displayName || '',
        messageData.user.profilePicture
      );
    }

    return new Message(
      messageData.messageID,
      messageData.channelID,
      messageData.content,
      messageData.type || 'text',
      messageData.sent_at,
      user,
      messageData.replyTo
    );
  }

  async function fetchMessages(offset = 0, limit = 20) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await messageService.getChannelMessages(currentChannelId.value, offset, limit);
      
      if (response.data && response.data.messages) {
        const reversedMessages = [...response.data.messages].reverse();
        messages.value = reversedMessages.map(msg => mapToMessageInstance(msg));

        if (response.pagination) {
          pagination.value = {
            offset: response.pagination.offset,
            limit: response.pagination.limit,
            total: response.pagination.total
          };
        }
      } else {
        throw new Error('Invalid response format');
      }
    } catch (err) {
      error.value = err.message || 'Failed to fetch messages';
      console.error('Error fetching messages:', err);
    } finally {
      isLoading.value = false;
    }
  }

  async function loadMoreMessages() {
    if (!currentChannelId.value) return;

    const newOffset = pagination.value.offset + pagination.value.limit;
    isLoading.value = true;

    try {
      const response = await messageService.getChannelMessages(
        currentChannelId.value,
        newOffset,
        pagination.value.limit
      );

      if (response.data && response.data.messages && response.data.messages.length > 0) {
        const newMessages = response.data.messages.map(msg => mapToMessageInstance(msg));
        
        messages.value = [...newMessages.reverse(), ...messages.value];

        pagination.value = {
          offset: newOffset,
          limit: response.data.pagination.limit,
          total: response.data.pagination.total
        }
        
        return true;
      }
      return false;
    } catch (err) {
      error.value = err.message || 'Failed to load more messages';
      console.error('Error loading more messages:', err);
      return false;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateMessage(messageId, newContent) {
    try {
      const response = await messageService.editMessage(messageId, newContent);

      if (response) {
        const messageIndex = messages.value.findIndex(msg => msg.getMessageID() === messageId);

        if (messageIndex !== -1) {
          const updatedMessage = messages.value[messageIndex];

          const messageData = {
            messageID: updatedMessage.getMessageID(),
            channelID: updatedMessage.getChannelID(),
            content: newContent,
            type: updatedMessage.getType(),
            sent_at: updatedMessage.getSentAt(),
            user: updatedMessage.getUser()
          };

          const newMessage = mapToMessageInstance(messageData);
          messages.value.splice(messageIndex, 1, newMessage);
          return newMessage;
        }
      }
      return null;
    } catch (err) {
      error.value = err.message || 'Failed to update message';
      console.error('Error updating message:', err);
      return null;
    }
  }

  function addMessage(message) {
    messages.value.push(message);
  }

  function updateWebSocketMessage(messageId, newContent) {
    const messageIndex = messages.value.findIndex(msg => msg.getMessageID() === messageId);

    if (messageIndex !== -1) {
      const updatedMessage = messages.value[messageIndex];

      const newMessage = new Message(
        updatedMessage.getMessageID(),
        updatedMessage.getChannelID(),
        newContent,
        updatedMessage.getType(),
        updatedMessage.getSentAt(),
        updatedMessage.getUser(),
        updatedMessage.getReplyTo()
      );
      messages.value.splice(messageIndex, 1, newMessage);
      return newMessage;
    }

    return null;
  }

  function removeMessage(messageId) {
    messages.value = messages.value.filter(msg => msg.getMessageID() !== messageId);
  }

  function clearMessages() {
    messages.value = [];
    currentChannelId.value = null;
    error.value = null;
    pagination.value = {
      offset: 0,
      limit: 20,
      total: 0
    };
  }

  return {
    getMessages,
    getNotTextMessages,
    getCurrentChannelId,
    getCurrentChannelName,
    getIsLoading,
    getError,
    getPagination,
    getMessageById,

    setCurrentChannelId,
    setCurrentChannelName,

    fetchMessages,
    loadMoreMessages,
    updateMessage,
    removeMessage,
    addMessage,
    updateWebSocketMessage,
    clearMessages
  };
});