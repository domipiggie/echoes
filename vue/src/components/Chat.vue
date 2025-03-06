<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Sidebar from './SideBar.vue';
import ChatWindow from './ChatWindow.vue';
import { userdataStore } from '../store/UserdataStore';

const userdata = userdataStore();
const friendList = ref([]);

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

const loadChatList = async () => {
  friendList.value = [];

  await axios.get('http://localhost/usrinfo/friendlist', {
    headers: {
      Authorization: `Bearer ${userdata.getAccessToken()}`
    }
  })
    .then(response => {
      friendList.value = response.data;
    })

  recentChats.value = [];

  friendList.value.forEach((user) => {
    axios.get('http://localhost/usrinfo/userdata/' + user.user2ID)
      .then(response => {
        recentChats.value.push({ id: response.data.userID, name: response.data.username, lastSeen: '-', avatar: response.data.profilePicture })
      })
  })
}

const sendMessage = (text) => {
  const newId = messages.value.length + 1;
  messages.value.push({
    id: newId,
    text,
    sender: 'me',
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  });
};

loadChatList();
</script>

<template>
  <div class="app-container">
    <div class="floating-container">
      <div class="chat-application">
        <Sidebar :recents="recentChats" />
        <ChatWindow :currentChat="currentChat" :messages="messages" @send-message="sendMessage" />
      </div>
    </div>
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

.chat-window {
  background-color: #7078e6;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.message {
  margin: 10px;
  padding: 10px;
  border-radius: 8px;
  animation: fadeIn 1s ease-out;
}

.message.me {
  background-color: #d1eeff;
  align-self: flex-end;
}

.message.other {
  background-color: #e6e6e6;
  align-self: flex-start;
}

@keyframes fadeIn {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }

  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
