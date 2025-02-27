<template>
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="title-section">
        <h1>Üzenetek</h1>
        <button class="new-message-btn">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
            <path fill="currentColor" d="M19,3H5C3.89,3,3,3.89,3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M17,13H13V17H11V13H7V11H11V7H13V11H17V13Z"/>
          </svg>
        </button>
      </div>
      <div class="search-bar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
          <path fill="currentColor" d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
        </svg>
        <input type="text" placeholder="Keresés az üzenetek között" />
      </div>
    </div>

    <div class="chats-list">
      <div 
        v-for="chat in recents" 
        :key="chat.id" 
        class="chat-item"
        @click="selectChat(chat)"
      >
        <div class="avatar">
          <div class="avatar-circle"></div>
        </div>
        <div class="chat-info">
          <div class="chat-name">{{ chat.name }}</div>
          <div class="last-seen">{{ chat.lastSeen }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  recents: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['select-chat']);

const selectChat = (chat) => {
  emit('select-chat', chat);
};
</script>

<style scoped>
.sidebar {
  width: 100%;
  max-width: 360px;
  background-color: #2b1285;
  color: rgb(55, 16, 99);
  display: flex;
  flex-direction: column;
  height: 100vh;
  border-radius: 8px;
  overflow: hidden;
}

.sidebar-header {
  padding: 16px;
  background: #4b42b8;
  border-bottom: 1px solid #3a3696;
}

.title-section h1 {
  font-size: 26px;
  margin: 0;
  color: #fff;
}

.new-message-btn {
  background: #6a61d1;
  border: none;
  color: #fff;
  cursor: pointer;
  padding: 10px;
  border-radius: 50%;
  transition: background 0.3s;
}

.new-message-btn:hover {
  background: #7b73e0;
}

.search-bar {
  display: flex;
  align-items: center;
  background-color: #6a61d1;
  border-radius: 30px;
  padding: 10px 15px;
  gap: 8px;
  border: 3px solid white;
}

.search-bar input {
  background: none;
  border: none;
  color: #fff;
  width: 100%;
}

.chat-item {
  display: flex;
  align-items: center;
  padding: 10px 15px;
  cursor: pointer;
  transition: background 0.3s;
  border-radius: 8px;
}

.chat-item:hover {
  background-color: #7b73e0;
}

.avatar-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-color: #4b42b8;
}

.chat-name {
  font-size: 16px;
  color: #fff;
  font-weight: bold;
}

.last-seen {
  font-size: 12px;
  color: #c0bfff;
}
.search-bar input::placeholder {
  color: #ffffff;
}

</style>
