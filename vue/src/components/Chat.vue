<script setup>
import { ref, onMounted } from 'vue';
import Sidebar from './SideBar.vue';
import ChatWindow from './ChatWindow.vue';

const showChat = ref(false);
const isMobile = ref(false);

onMounted(() => {
  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);
});

const checkScreenSize = () => {
  isMobile.value = window.innerWidth <= 768;
};

const recentChats = ref([
  { id: 1, name: 'János', lastSeen: '2 perce', avatar: '' },
  { id: 2, name: 'Éva', lastSeen: '10 perce', avatar: '' },
  { id: 3, name: 'Péter', lastSeen: '15 perce', avatar: '' },
  { id: 4, name: 'Anna', lastSeen: '1 órája', avatar: '' },
  { id: 5, name: 'Gábor', lastSeen: '3 órája', avatar: '' },
  { id: 6, name: 'Márta', lastSeen: '5 órája', avatar: '' },
  { id: 7, name: 'Tamás', lastSeen: '1 napja', avatar: '' },
  { id: 8, name: 'Tamás', lastSeen: '1 napja', avatar: '' },
  { id: 9, name: 'Tamás', lastSeen: '1 napja', avatar: '' }
]);

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

const handleChatSelect = (chat) => {
  currentChat.value = chat;
  if (isMobile.value) {
    showChat.value = true;
  }
};

const goBackToList = () => {
  showChat.value = false;
};

const sendMessage = (text) => {
  const newId = messages.value.length + 1;
  messages.value.push({
    id: newId,
    text,
    sender: 'me',
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  });
};
</script>

<template>
  <div class="chat-application">
    <div class="side-buttons">
      <button class="side-button active">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" />
          <path d="M12 4V2m0 20v-2m8-8h2M2 12h2m13.657-5.657L19.07 4.93M4.93 19.07l1.414-1.414m0-11.314L4.93 4.93m14.14 14.14l-1.414-1.414" />
        </svg>
      </button>
      <button class="side-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
        </svg>
      </button>
    </div>
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
</style>
