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
  gap: 24px; /* Increased from 16px to 24px */
  background-color: #dfdfdf;
  margin: 2.5vh 2.5vw;
  border-radius: 12px;
}

@media (max-width: 768px) {
  .chat-application {
    flex-direction: column;
    height: calc(100vh - 20px);
    width: calc(100vw - 20px);
    margin: 10px;
    gap: 12px; /* Added gap for mobile view when components are stacked */
  }
}
</style>
