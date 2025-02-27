<script setup>
import { ref } from 'vue';
import Sidebar from './Sidebar.vue';
import ChatWindow from './ChatWindow.vue';

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
    <Sidebar :recents="recentChats" />
    <ChatWindow
      :currentChat="currentChat"
      :messages="messages"
      @send-message="sendMessage"
    />
  </div>
</template>

<style scoped>
.chat-application {
  display: flex;
  height: 100%;
  width: 100%;
  overflow: hidden;
  font-family: Arial, sans-serif;
}
</style>