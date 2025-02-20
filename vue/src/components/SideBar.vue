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

<template>
    <div class="sidebar">
      <div class="settings-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
          <path fill="white" d="M12,15.5A3.5,3.5,0,0,1,8.5,12,3.5,3.5,0,0,1,12,8.5a3.5,3.5,0,0,1,3.5,3.5A3.5,3.5,0,0,1,12,15.5Zm7.43-2.53c.1-.35.17-.71.23-1.09l2.2-.64a1,1,0,0,0,.73-1.2l-1.62-4.83a1,1,0,0,0-1.21-.73l-2.37.65a8.63,8.63,0,0,0-1.67-1l-.34-2.36a1,1,0,0,0-1-.86H9.59a1,1,0,0,0-1,.82L8.27,4.1a8.3,8.3,0,0,0-1.72,1l-2.2-.6a1,1,0,0,0-1.21.74L1.47,11a1,1,0,0,0,.73,1.2l2.11.56a8.13,8.13,0,0,0,.26,1.9l-1.63,1.5a1,1,0,0,0-.12,1.41l3.5,4.4a1,1,0,0,0,1.41.12l1.84-1.5a8.59,8.59,0,0,0,1.76.69l.53,2.19a1,1,0,0,0,1,.8h5.38a1,1,0,0,0,1-.81l.47-2.06a8.4,8.4,0,0,0,1.94-.73l1.8,1.44a1,1,0,0,0,1.41-.14l3.39-4.44a1,1,0,0,0-.09-1.36Z"/>
        </svg>
      </div>
      
      <div class="header">
        <h1>Chats</h1>
        <div class="search-bar">
          <input type="text" placeholder="Keresés..." />
        </div>
      </div>
      
      <div class="recent-header">
        <h2>Recents</h2>
      </div>
      
      <div class="recent-chats">
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
            <span class="chat-name">{{ chat.name }}</span>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  
  
  <style scoped>
  .sidebar {
    width: 320px;
    height: 100%;
    background-color: #17404D;
    color: white;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
  }
  
  .settings-icon {
    position: absolute;
    top: 16px;
    left: 16px;
    z-index: 10;
  }
  
  .header {
    padding: 16px;
    padding-top: 48px;
  }
  
  h1 {
    font-size: 32px;
    margin-bottom: 16px;
    font-weight: normal;
  }
  
  .search-bar input {
    width: 100%;
    padding: 12px;
    background-color: white;
    border: none;
    border-radius: 4px;
    margin-bottom: 16px;
  }
  
  .recent-header {
    padding: 0 16px;
  }
  
  h2 {
    font-size: 20px;
    margin-bottom: 8px;
    font-weight: normal;
  }
  
  .recent-chats {
    overflow-y: auto;
    flex: 1;
  }
  
  .chat-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    border-radius: 4px;
    margin: 0 8px 4px 8px;
    background-color: rgba(255, 255, 255, 0.1);
  }
  
  .avatar {
    margin-right: 12px;
  }
  
  .avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #4E7886;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .chat-info {
    flex: 1;
  }
  
  .chat-name {
    font-size: 16px;
    display: block;
  }
  </style>