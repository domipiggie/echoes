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

<style scoped>
.chat-application {
  display: flex;
  height: 95vh;
  width: 95vw;
  overflow: hidden;
  font-family: Arial, sans-serif;
  gap: 16px;
  background-color: #dfdfdf;
  margin: 2.5vh 2.5vw;
  border-radius: 12px;
}

/* Remove .side-buttons and .side-button styles */

/* Add this new style to ensure bottom corners are rounded */
:deep(.chat-window) {
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

:deep(.chat-window::-webkit-scrollbar) {
  display: none;
}

:deep(.messages-container) {
  padding-bottom: 24px;
  margin-bottom: 16px;
  scrollbar-width: none;
  -ms-overflow-style: none;
  overflow-y: scroll;
}

:deep(.messages-container::-webkit-scrollbar) {
  display: none;
  width: 0;
}
.side-buttons {
  width: 20px;
  background-color: #f8f9fa;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  padding: 8px 0;
  align-items: center;
  box-shadow: 0 2px 10px rgba(112, 120, 230, 0.1);
}

.side-button {
  width: 16px;
  height: 16px;
  border: none;
  background: none;
  color: #7078e6;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: 8px;
  border-radius: 4px;
  padding: 2px;
}

.side-button svg {
  width: 12px;
  height: 12px;
}

.side-button:hover {
  background-color: rgba(112, 120, 230, 0.1);
}

.side-button.active {
  color: #ffffff;
  background-color: #7078e6;
}

@media (max-width: 768px) {
  .chat-application {
    flex-direction: column;
    height: calc(100vh - 20px);
    width: calc(100vw - 20px);
    margin: 10px;
    gap: 12px;
  }
  
  .side-buttons {
    display: none; /* Hide on mobile */
  }
}

.messenger-theme :deep(.message) {
  display: flex;
  padding: 4px 16px;
  margin: 2px 0;
  max-width: 70%;
  align-items: flex-start;
}

.messenger-theme :deep(.message-avatar) {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-right: 12px;
  background-color: #e6eaee;
  overflow: hidden;
  border: 2px solid #7078e6;
  box-shadow: 0 2px 4px rgba(112, 120, 230, 0.2);
  flex-shrink: 0;
}

.messenger-theme :deep(.message-avatar img) {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.messenger-theme :deep(.message-sent) {
  margin-left: auto;
  margin-right: 0;
  flex-direction: row-reverse;
  
  .message-avatar {
    margin-right: 0;
    margin-left: 12px;
  }
}

.messenger-theme :deep(.message-received) {
  margin-right: auto;
  margin-left: 0;
}
</style>
