<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import Sidebar from './SideBar.vue';
import ChatWindow from './ChatWindow.vue';
import { userdataStore } from '../store/UserdataStore';

const userStore = userdataStore();
const showChat = ref(false);
const isMobile = ref(false);
const recentChats = ref([]);
const socket = ref(null);
const connected = ref(false);
const reconnectAttempts = ref(0);
const maxReconnectAttempts = 5;
const reconnectInterval = 5000;
const pingInterval = 25000;
let pingTimer = null;

onMounted(async () => {
  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);
  await axios.get('http://localhost/usrinfo/channellist',{
    headers: {
      'Authorization': `Bearer ${userStore.getAccessToken()}`
    }
  })
  .then(response => {
    recentChats.value = response.data.friendshipChannels;
    console.log(recentChats.value);
  })
  .catch(error => {
    console.error('Error fetching recent chats:', error);
  });
  
  // Initialize WebSocket connection
  connectWebSocket();
});

onUnmounted(() => {
  // Clean up WebSocket connection when component is unmounted
  disconnectWebSocket();
});

const connectWebSocket = () => {
  try {
    socket.value = new WebSocket('ws://localhost:8080');
  } catch (e) {
    console.error('Error creating WebSocket connection:', e.message);
    scheduleReconnect();
    return;
  }

  socket.value.onopen = () => {
    console.log('[WebSocket] Connection established');
    connected.value = true;
    reconnectAttempts.value = 0;

    // Send authentication message
    socket.value.send(JSON.stringify({
      type: 'auth',
      userID: userStore.getAccessToken()
    }));
    
    startPingInterval();
  };

  socket.value.onmessage = (event) => {
    try {
      const data = JSON.parse(event.data);
      
      if (data.type === 'ping') {
        handlePing(data);
        return;
      }

      if (data.type === 'auth_success') {
        console.log('[WebSocket] Authentication successful');
      } else if (data.type === 'auth_error') {
        console.error('[WebSocket] Authentication failed:', data.message);
        disconnectWebSocket();
      } else if (data.type === 'error') {
        console.error('[WebSocket] Server error:', data.message);
      } else if (data.type === 'new_message') {
        console.log('[WebSocket] New message received in channel:', data.channelID);
        console.log('[WebSocket] Message data:', data.message);
        
        // Ignore messages sent by the current user (already displayed when sent)
        if (parseInt(data.message.userID) === parseInt(userStore.getUserID())) {
          console.log('[WebSocket] Ignoring message from self');
          return;
        }
        
        // Check if the message is for the currently selected chat
        if (currentChat.value && currentChat.value.channelID == data.channelID) {
          const message = data.message;
          const formattedMessage = {
            id: message.messageID,
            text: message.content,
            sender: 'other', // Always 'other' since we're filtering out self messages
            time: new Date(message.sent_at || Date.now()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            type: message.type || 'text',
            fileName: message.fileName,
            fileSize: message.fileSize,
            fileType: message.fileType
          };
          
          console.log('[WebSocket] Adding formatted message to chat:', formattedMessage);
          messages.value.push(formattedMessage);
        } else {
          console.log('[WebSocket] Message not for current chat. Current:', 
                     currentChat.value?.channelID, 'Message for:', data.channelID);
        }
      }
    } catch (e) {
      console.error('[WebSocket] Error processing message:', e.message);
    }
  };

  socket.value.onclose = (event) => {
    console.log('[WebSocket] Connection closed:', event.reason || 'No reason provided');
    connected.value = false;
    stopPingInterval();
    
    scheduleReconnect();
  };

  socket.value.onerror = (error) => {
    console.error('[WebSocket] Error occurred');
  };
};

const handlePing = (data) => {
  if (connected.value && socket.value && socket.value.readyState === WebSocket.OPEN) {
    socket.value.send(JSON.stringify({
      type: 'pong',
      timestamp: data.timestamp
    }));
  }
};

const startPingInterval = () => {
  stopPingInterval();
  pingTimer = setInterval(() => {
    if (connected.value && socket.value && socket.value.readyState === WebSocket.OPEN) {
      socket.value.send(JSON.stringify({
        type: 'ping',
        timestamp: Date.now()
      }));
    }
  }, pingInterval);
};

const stopPingInterval = () => {
  if (pingTimer) {
    clearInterval(pingTimer);
    pingTimer = null;
  }
};

const scheduleReconnect = () => {
  if (reconnectAttempts.value >= maxReconnectAttempts) {
    console.error('[WebSocket] Maximum reconnection attempts reached');
    return;
  }
  
  reconnectAttempts.value++;
  const delay = reconnectInterval * Math.pow(1.5, reconnectAttempts.value - 1);
  
  console.log(`[WebSocket] Scheduling reconnect attempt ${reconnectAttempts.value} in ${delay}ms`);
  setTimeout(() => connectWebSocket(), delay);
};

const disconnectWebSocket = () => {
  stopPingInterval();
  if (socket.value) {
    socket.value.close();
    socket.value = null;
  }
  connected.value = false;
};

const checkScreenSize = () => {
  isMobile.value = window.innerWidth <= 768;
};

const currentChat = ref({
  id: 1,
  name: 'Név',
  online: true,
  lastSeen: '2 perce'
});

const messages = ref([
  { id: 1, text: 'Ez egy hosszabb üzenet, ami több helyet foglal el a chat ablakban', sender: 'other', time: '14:32' },
  { id: 2, text: 'Válasz a másik személytől', sender: 'other', time: '14:33' },
  { id: 3, text: 'Ez egy hosszabb üzenet tőlem, ami több helyet foglal el', sender: 'me', time: '14:35' },
  { id: 4, text: 'Egy újabb válasz', sender: 'other', time: '14:36' },
  { id: 5, text: 'Válasz', sender: 'other', time: '14:40' }
]);

const handleChatSelect = async (chat) => {
  currentChat.value = chat;
  
  currentChat.value.name = userStore.getUserID() == chat.user1.id 
    ? chat.user2.username 
    : chat.user1.username;
  
  messages.value = [];
  
  try {
    const response = await axios.get(`http://localhost/message/get`, {
      params: {
        channelID: chat.channelID
      },
      headers: {
        'Authorization': `Bearer ${userStore.getAccessToken()}`
      }
    });
    
    if (response.data && Array.isArray(response.data.messages)) {
      console.log(response.data);
      messages.value = response.data.messages.map(msg => ({
        id: msg.messageID,
        text: msg.content,
        sender: parseInt(msg.userID) === parseInt(userStore.getUserID()) ? 'me' : 'other',
        time: new Date(msg.sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        type: msg.type || 'text',
        fileName: msg.fileName,
        fileSize: msg.fileSize,
        fileType: msg.fileType
      })).reverse();
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
  let messageObj = {};
  
  if (typeof text === 'object') {
    messageContent = text.text;
    messageType = text.type || 'text';
    messageObj = {
      id: newId,
      text: text.text,
      type: text.type,
      fileName: text.fileName,
      fileSize: text.fileSize,
      fileType: text.fileType,
      sender: 'me',
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    };
  } else {
    messageContent = text;
    messageObj = {
      id: newId,
      text,
      sender: 'me',
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    };
  }
  
  messages.value.push(messageObj);
  
  try {
    const response = await axios.post('http://localhost/message/send', {
      channelID: currentChat.value.channelID,
      content: messageContent,
      type: messageType
    }, {
      headers: {
        'Authorization': `Bearer ${userStore.getAccessToken()}`
      }
    });
    
    console.log('Message sent successfully:', response.data);
    
    if (response.data && response.data.messageID) {
      messageObj.id = response.data.messageID;
      console.log(response.data)
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

<style lang="scss">
@import '../styles/Chat.scss';
</style>
