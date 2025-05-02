<script setup>
import { ref, onMounted } from 'vue';
import Sidebar from './SideBar.vue';
import ChatWindow from './ChatWindow.vue';
import { userdataStore } from '../store/UserdataStore';
import { useWebSocket } from '../composables/useWebSocket';
import { useFriendshipStore } from '../store/FriendshipStore';
import { useChannelStore } from '../store/ChannelStore';
import { useMessageStore } from '../store/MessageStore';
import { messageFormatter } from '../utils/messageFormatter';
import { chatService } from '../services/chatService';
import { fileService } from '../services/fileService';
import { WS_CONFIG } from '../config/ws';

const userStore = userdataStore();
const friendshipStore = useFriendshipStore();
const channelStore = useChannelStore();
const messageStore = useMessageStore();
const showChat = ref(false);
const isMobile = ref(false);

const processedFriendRequests = ref(new Set());

const handleWebSocketMessage = (data) => {
  console.log('[WebSocket] New message received:', data);
  
  if (data.type === 'new_message') {
    if (parseInt(data.message.userID) === parseInt(userStore.getUserID())) {
      console.log('[WebSocket] Ignoring message from self');
      return;
    }
    
    if (messageStore.getCurrentChannelId == data.channelID) {
      const formattedMessage = messageFormatter.formatIncomingMessage(data.message);
      console.log('[WebSocket] Adding formatted message to chat:', formattedMessage);
      messageStore.getMessages.push(formattedMessage);
    }
  } else if (data.type === 'friend_request') {
    const friendshipID = data.friendRequest?.friendshipID;
    
    if (friendshipID && !processedFriendRequests.value.has(friendshipID)) {
      console.log('[WebSocket] Dispatching friend request event for ID:', friendshipID);
      processedFriendRequests.value.add(friendshipID);
      
      window.dispatchEvent(new CustomEvent('new-friend-request', { 
        detail: data 
      }));
    } else {
      console.log('[WebSocket] Skipping duplicate friend request dispatch for ID:', friendshipID);
    }
  }
};

const { connectWebSocket } = useWebSocket(handleWebSocketMessage);

onMounted(async () => {
  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);
  
  try {
    await friendshipStore.fetchFriendships();
    await channelStore.fetchAllChannels();

    console.log('Channels loaded:', channelStore.getFriendChannels, channelStore.getFriendChannels);
    console.log('Friendships loaded:', friendshipStore.getFriendships);
  } catch (error) {
    console.error('Failed to load channels:', error);
  }
  console.log('[Chat] Attempting to connect to WebSocket server at:', WS_CONFIG.BASE_URL);
  connectWebSocket();
  console.log('[Chat] WebSocket connection initiated');
});

const checkScreenSize = () => {
  isMobile.value = window.innerWidth <= 768;
};

const handleChatSelect = async (chat) => {
  messageStore.setCurrentChannelId(chat.getChannelID());
  
  var name = userStore.getUserID() == chat.getUser1().getUserID() 
    ? chat.getUser2().getUserName() 
    : chat.getUser1().getUserName();
  
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
  const newId = messageStore.getMessages.length + 1;
  let messageContent;
  let messageType = 'text';
  let replyToId = null;
  
  const messageObj = messageFormatter.formatOutgoingMessage(text, newId);
  // Hozzáadjuk az üzenetet a store-hoz
  messageStore.getMessages.push(messageObj);
  
  if (typeof text === 'object') {
    messageContent = text.text;
    messageType = text.type || 'text';
    
    // Ha válasz üzenet, akkor elmentjük a válaszolt üzenet ID-jét
    if (text.replyTo) {
      replyToId = text.replyTo;
      console.log('Válasz üzenet küldése, válaszolt üzenet ID:', replyToId);
    }
  } else {
    messageContent = text;
  }
  
  try {
    const response = await chatService.sendMessage(
      messageStore.getCurrentChannelId,
      messageContent,
      messageType,
      replyToId // Továbbítjuk a válaszolt üzenet ID-jét
    );
    
    console.log('Message sent successfully:', response);
    
    if (response && response.messageID) {
      // Frissítjük az üzenet ID-t a válasz alapján
      const msgIndex = messageStore.getMessages.findIndex(msg => msg.id === messageObj.id);
      if (msgIndex !== -1) {
        messageStore.getMessages[msgIndex].id = response.messageID;
      }
    }
  } catch (error) {
    console.error('Error sending message:', error);
  }
};


const updateMessage = async (updatedMessage) => {
  console.log('Üzenet frissítés fogadva:', updatedMessage);
  
  const messageIndex = messageStore.getMessages.findIndex(msg => msg.id === updatedMessage.id);
  
  if (messageIndex !== -1) {
    messageStore.getMessages[messageIndex] = updatedMessage;
    
    try {
      if (updatedMessage.isRevoked) {
        await chatService.deleteMessage(messageStore.getCurrentChannelId, updatedMessage.id);
      } else {
        await chatService.updateMessage(
          messageStore.getCurrentChannelId,
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
    console.log('Uploading file:', fileData.file.name);
    const response = await fileService.uploadFile(fileData.file, fileData.channelID);
    console.log('File uploaded successfully:', response);
    
    if (response && response.messageID) {
      const tempMessageIndex = messageStore.getMessages.findIndex(
        msg => msg.fileName === fileData.file.name && msg.sender === 'me'
      );
      
      if (tempMessageIndex !== -1) {
        messageStore.getMessages[tempMessageIndex].id = response.messageID;
        console.log('Updated message with server ID:', response.messageID);
      }
    }
  } catch (error) {
    console.error('Error uploading file:', error);
  }
};
</script>

<template>
  <div class="chat-application">
    <Sidebar 
      v-if="!isMobile || !showChat" 
      @select-chat="handleChatSelect" 
    />
    <ChatWindow
      v-if="!isMobile || showChat"
      @send-message="sendMessage"
      @send-file="handleFileUpload"
      @go-back="goBackToList"
      @update-message="updateMessage"
    />
  </div>
</template>

<style lang="scss" scoped>
@import '../styles/Chat.scss';
</style>
