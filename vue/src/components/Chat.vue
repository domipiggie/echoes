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
  
  var name = userStore.getUserID() == chat.user1.id 
    ? chat.user2.username 
    : chat.user1.username;
  
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
    console.log('Uploading file:', fileData.file.name);
    const response = await fileService.uploadFile(fileData.file, fileData.channelID);
    console.log('File uploaded successfully:', response);
    
    if (response && response.messageID) {
      const tempMessageIndex = messages.value.findIndex(
        msg => msg.fileName === fileData.file.name && msg.sender === 'me'
      );
      
      if (tempMessageIndex !== -1) {
        messages.value[tempMessageIndex].id = response.messageID;
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
    <!--<ChatWindow
      v-if="!isMobile || showChat"
      @send-message="sendMessage"
      @send-file="handleFileUpload"
      @go-back="goBackToList"
      @update-message="updateMessage"
    />-->
  </div>
</template>

<style lang="scss" scoped>
@import '../styles/Chat.scss';
</style>
