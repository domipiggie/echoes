import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { messageService } from '../services/messageService';
import Message from '../classes/Message';
import User from '../classes/User';

export const useMessageStore = defineStore('message', () => {
  const messages = ref([]);
  const currentChannelId = ref(null);
  const isLoading = ref(false);
  const error = ref(null);
  const pagination = ref({
    offset: 0,
    limit: 20,
    total: 0
  });

  const getMessages = computed(() => messages.value);
  const getCurrentChannelId = computed(() => currentChannelId.value);
  const getIsLoading = computed(() => isLoading.value);
  const getError = computed(() => error.value);
  const getPagination = computed(() => pagination.value);

  const getMessageById = computed(() => (messageId) => {
    return messages.value.find(message => message.getMessageID() === messageId);
  });

  function mapToMessageInstance(messageData) {
    let user = null;
    if (messageData.user) {
      user = new User(
        messageData.user.id,
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
      user
    );
  }

  async function fetchMessages(channelId, offset = 0, limit = 20) {
    if (!channelId) return;

    currentChannelId.value = channelId;
    isLoading.value = true;
    error.value = null;

    try {
      const response = await messageService.getChannelMessages(channelId, offset, limit);

      if (response && response.messages) {
        messages.value = response.messages.map(msg => mapToMessageInstance(msg));

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

      if (response && response.messages && response.messages.length > 0) {
        const newMessages = response.messages.map(msg => mapToMessageInstance(msg));
        messages.value = [...messages.value, ...newMessages];

        if (response.pagination) {
          pagination.value = {
            offset: response.pagination.offset,
            limit: response.pagination.limit,
            total: response.pagination.total
          };
        }
      }
    } catch (err) {
      error.value = err.message || 'Failed to load more messages';
      console.error('Error loading more messages:', err);
    } finally {
      isLoading.value = false;
    }
  }

  async function sendMessage(content, type = 'text') {
    if (!currentChannelId.value) {
      error.value = 'No channel selected';
      return null;
    }

    try {
      const response = await messageService.sendMessage(currentChannelId.value, content, type);

      if (response) {
        const newMessage = mapToMessageInstance(response);
        messages.value.unshift(newMessage);
        return newMessage;
      }
      return null;
    } catch (err) {
      error.value = err.message || 'Failed to send message';
      console.error('Error sending message:', err);
      return null;
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

  async function removeMessage(messageId) {
    try {
      const response = await messageService.deleteMessage(messageId);

      if (response) {
        messages.value = messages.value.filter(msg => msg.getMessageID() !== messageId);
        return true;
      }
      return false;
    } catch (err) {
      error.value = err.message || 'Failed to delete message';
      console.error('Error deleting message:', err);
      return false;
    }
  }

  function addWebSocketMessage(messageData) {
    if (messageData.channelId !== currentChannelId.value) return;

    const newMessage = mapToMessageInstance({
      messageID: messageData.messageId,
      channelID: messageData.channelId,
      content: messageData.content,
      type: messageData.messageType || 'text',
      sent_at: new Date().toISOString(),
      user: messageData.sender
    });

    messages.value.unshift(newMessage);
    return newMessage;
  }

  function updateWebSocketMessage(messageId, newContent) {
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

    return null;
  }

  function removeWebSocketMessage(messageId) {
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
    getCurrentChannelId,
    getIsLoading,
    getError,
    getPagination,
    getMessageById,

    fetchMessages,
    loadMoreMessages,
    sendMessage,
    updateMessage,
    removeMessage,
    addWebSocketMessage,
    updateWebSocketMessage,
    removeWebSocketMessage,
    clearMessages
  };
});