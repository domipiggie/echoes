<script setup>
import { ref, onMounted } from 'vue';
import Sidebar from './SideBar.vue';
import ChatWindow from './ChatWindow.vue';
import { userdataStore } from '../store/UserdataStore';
import { useWebSocket } from '../composables/useWebSocket';
import { chatService } from '../services/chatService';
import { messageFormatter } from '../utils/messageFormatter';
import { WS_CONFIG } from '../config/ws';

const userStore = userdataStore();
const showChat = ref(false);
const isMobile = ref(false);
const recentChats = ref([]);
const currentChat = ref({
  id: 1,
  name: 'Név',
  online: true,
  lastSeen: '2 perce'
});
const messages = ref([]);

const processedFriendRequests = ref(new Set());

const handleWebSocketMessage = (data) => {
  console.log('[WebSocket] New message received:', data);
  
  if (data.type === 'new_message') {
    if (parseInt(data.message.userID) === parseInt(userStore.getUserID())) {
      console.log('[WebSocket] Ignoring message from self');
      return;
    }
    
    if (currentChat.value && currentChat.value.channelID == data.channelID) {
      const formattedMessage = messageFormatter.formatIncomingMessage(data.message);
      console.log('[WebSocket] Adding formatted message to chat:', formattedMessage);
      messages.value.push(formattedMessage);
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
    const channelsData = await chatService.getChannels();
    recentChats.value = channelsData.friendshipChannels;
    console.log('Channels loaded:', recentChats.value);
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
  currentChat.value = chat;
  
  currentChat.value.name = userStore.getUserID() == chat.user1.id 
    ? chat.user2.username 
    : chat.user1.username;
  
  messages.value = [];
  
  try {
    const messagesData = await chatService.getMessages(chat.channelID);
    
    if (messagesData && Array.isArray(messagesData.messages)) {
      messages.value = messagesData.messages
        .map(msg => messageFormatter.formatIncomingMessage(msg))
        .reverse();
    }
    
    console.log('Loaded messages:', messages.value);
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
  const newId = messages.value.length + 1;
  let messageContent;
  let messageType = 'text';
  
  const messageObj = messageFormatter.formatOutgoingMessage(text, newId);
  messages.value.push(messageObj);
  
  if (typeof text === 'object') {
    messageContent = text.text;
    messageType = text.type || 'text';
  } else {
    messageContent = text;
  }
  
  try {
    const response = await chatService.sendMessage(
      currentChat.value.channelID,
      messageContent,
      messageType
    );
    
    console.log('Message sent successfully:', response);
    
    if (response && response.messageID) {
      messageObj.id = response.messageID;
    }
  } catch (error) {
    console.error('Error sending message:', error);
  }
};
</script>

<template>
  <div class="chat-application">
    <Sidebar 
      v-if="!isMobile || !showChat" 
      :recents="recentChats" 
      @select-chat="handleChatSelect" 
    />
    <ChatWindow
      v-if="!isMobile || showChat"
      :currentChat="currentChat"
      :messages="messages"
      @send-message="sendMessage"
      @go-back="goBackToList"
    />
  </div>
</template>

<style lang="scss" scoped>
@import '../styles/Chat.scss';
</style>
