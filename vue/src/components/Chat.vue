<script setup>
import { ref } from 'vue';
import SideBar from './SideBar.vue';
import ChatWindow from './ChatWindow.vue';


const recentChats = ref([
  { id: 1, name: 'János', lastSeen: '2 perce' },
  { id: 2, name: 'Éva', lastSeen: '10 perce' },
  { id: 3, name: 'Péter', lastSeen: '15 perce' },
  { id: 4, name: 'Anna', lastSeen: '1 órája' },
  { id: 5, name: 'Gábor', lastSeen: '3 órája' },
  { id: 6, name: 'Márta', lastSeen: '5 órája' },
  { id: 7, name: 'Tamás', lastSeen: '1 napja' }
]);

const currentChat = ref({
  id: 1,
  name: 'Név',
  online: true
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
<script>
export default {
  name: 'Chat'
}
</script>
<template>
  <div class="chat-application">
    <SideBar :recents="recentChats" />
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
  height: 100vh;
  width: 100%;
  overflow: hidden;
  font-family: Arial, sans-serif;
}
</style>