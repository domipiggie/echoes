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
  
  <style scoped>
  .sidebar {
    width: 360px;
    background-color: #242526;
    color: white;
    display: flex;
    flex-direction: column;
    height: 100%;
  }
  
  .sidebar-header {
    padding: 8px 16px;
    border-bottom: 1px solid #393a3b;
  }
  
  .title-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }
  
  .title-section h1 {
    font-size: 24px;
    margin: 0;
  }
  
  .new-message-btn {
    background: none;
    border: none;
    color: #e4e6eb;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
  }
  
  .new-message-btn:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }
  
  .search-bar {
    display: flex;
    align-items: center;
    background-color: #3a3b3c;
    border-radius: 50px;
    padding: 8px 12px;
    gap: 8px;
  }
  
  .search-bar svg {
    color: #b0b3b8;
  }
  
  .search-bar input {
    background: none;
    border: none;
    color: #e4e6eb;
    width: 100%;
    font-size: 15px;
  }
  
  .search-bar input::placeholder {
    color: #b0b3b8;
  }
  
  .chats-list {
    overflow-y: auto;
    flex: 1;
  }
  
  .chat-item {
    display: flex;
    align-items: center;
    padding: 8px 16px;
    gap: 12px;
    cursor: pointer;
  }
  
  .chat-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }
  
  .avatar {
    width: 56px;
    height: 56px;
  }
  
  .avatar-circle {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background-color: #3a3b3c;
  }
  
  .chat-info {
    flex: 1;
    border-bottom: 1px solid #393a3b;
    padding: 8px 0;
  }
  
  .chat-name {
    font-size: 15px;
    font-weight: 500;
    margin-bottom: 4px;
  }
  
  .last-seen {
    font-size: 13px;
    color: #b0b3b8;
  }
  </style>