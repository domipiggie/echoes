<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Sidebar from './SideBar.vue';
import ChatWindow from './ChatWindow.vue';
import { userdataStore } from '../store/UserdataStore';
import { useFriendshipStore } from '../store/FriendshipStore';
import { useChannelStore } from '../store/ChannelStore';
import { useMessageStore } from '../store/MessageStore';
import { useWebSocketStore } from '../store/WebSocketStore';

import { handleNewMessage, handleDeleteMessage, handleOnFriendAdded, handleGroupCreated } from '../composables/websocketFunctions.js';

const userStore = userdataStore();
const friendshipStore = useFriendshipStore();
const channelStore = useChannelStore();
const messageStore = useMessageStore();
const webSocketStore = useWebSocketStore();
const showChat = ref(false);
const isMobile = ref(false);

onMounted(async () => {
  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);

  try {
    await friendshipStore.fetchFriendships();
    await channelStore.fetchAllChannels();

    console.log('Channels loaded:', channelStore.getFriendChannels, channelStore.getFriendChannels);
    console.log('Friendships loaded:', friendshipStore.getFriendships);

    if (userStore.getAccessToken() != null) {
      webSocketStore.connect();

      webSocketStore.registerHandler('new_message', handleNewMessage);
      webSocketStore.registerHandler('message_deleted', handleDeleteMessage);
      webSocketStore.registerHandler('friend_add', handleOnFriendAdded);
      webSocketStore.registerHandler('group_created', handleGroupCreated);
    }
  } catch (error) {
    console.error('Failed to load channels:', error);
  }
});

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize);

  webSocketStore.unregisterHandler('new_message');
  webSocketStore.unregisterHandler('message_deleted');
  webSocketStore.unregisterHandler('friend_add');
  webSocketStore.unregisterHandler('group_created');
  webSocketStore.disconnect();
});

const checkScreenSize = () => {
  isMobile.value = window.innerWidth <= 768;
};

const handleChatSelect = async (chat) => {
  messageStore.setCurrentChannelId(chat.getChannelID());

  var name;

  try {
    name = userStore.getUserID() == chat.getUser1().getUserID()
      ? chat.getUser2().getUserName()
      : chat.getUser1().getUserName();
  } catch (e) {
    name = chat.getName();
  }

  messageStore.setCurrentChannelName(name);

  try {
    await messageStore.fetchMessages();

    console.log('Messages loaded:', messageStore.getMessages);
  } catch (error) {
    console.error('Error fetching messages:', error);
  }

  if (isMobile.value) {
    showChat.value = true;
  }
};

const goBackToList = () => {
  showChat.value = false;
};

const sendMessage = async (text) => {
  try {
    if (webSocketStore.getIsConnected) {
      console.log('Sending message:', text);
      webSocketStore.send({
        type: 'chatmessage_send',
        channelId: messageStore.getCurrentChannelId,
        content: text.text,
        messageType: text.type,
        replyTo: text.replyTo == undefined ? null : text.replyTo

      });
    }
  } catch (error) {
    console.error('Error sending message:', error);
  }
};

const deleteMessage = async (messageId) => {
  try {
    if (webSocketStore.getIsConnected) {
      webSocketStore.send({
        type: 'chatmessage_delete',
        channelId: messageStore.getCurrentChannelId,
        messageId: messageId
      });
    }
  } catch (error) {
    console.error('Error deleting message:', error);
  }
}

const updateMessage = async (updatedMessage) => {
  console.log('Üzenet frissítés fogadva:', updatedMessage);

  const messageIndex = messages.value.findIndex(msg => msg.id === updatedMessage.id);

  if (messageIndex !== -1) {
    messages.value[messageIndex] = updatedMessage;

    try {
      if (updatedMessage.isRevoked) {
        await chatService.deleteMessage(currentChat.value.channelID, updatedMessage.id);
      } else {
        await chatService.updateMessage(
          currentChat.value.channelID,
          updatedMessage.id,
          updatedMessage.text
        );
      }
      console.log('Message updated successfully on server');
    } catch (error) {
      console.error('Error updating message on server:', error);
    }
  }
};

const handleFileUpload = async (fileData) => {
  try {

  } catch (error) {
    console.error('Error uploading file:', error);
  }
};
</script>

<template>
  <div class="chat-application">
    <Sidebar v-if="!isMobile || !showChat" @select-chat="handleChatSelect" />
    <ChatWindow v-if="!isMobile || showChat" @send-message="sendMessage" @send-file="handleFileUpload"
      @go-back="goBackToList" @update-message="updateMessage" @delete-message="deleteMessage" />
  </div>
</template>

<style lang="scss" scoped>
@import '../styles/Chat.scss';
</style>
