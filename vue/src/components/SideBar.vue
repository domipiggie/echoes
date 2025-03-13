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
  width: 100%;
  max-width: 360px;
  background: linear-gradient(135deg, #b13030, rgb(222, 96, 33)); 
  color: #e0e0e0; 
  display: flex;
  flex-direction: column;
  height: 100vh;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5); 
  transition: transform 0.3s ease-in-out;
}



.sidebar-header {
  padding: 20px;
  background: rgba(255, 255, 255, 0.08); 
  border-bottom: 1px solid #333; 
}

.title-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 15px;
}

.title-section h1 {
  font-size: 28px;
  margin: 0;
  color: #e0e0e0; 
  font-weight: 600;
}

.new-message-btn {
  background: linear-gradient(135deg, rgb(222, 96, 33), rgb(255, 120, 0)); 
  border: none;
  color: #fff;
  cursor: pointer;
  padding: 12px;
  border-radius: 50%;
  transition: transform 0.2s ease-in-out;
}

.new-message-btn:hover {
  transform: scale(1.1);
}

.search-bar {
  display: flex;
  align-items: center;
  background-color: rgba(255, 255, 255, 0.15); 
  border-radius: 30px;
  padding: 12px 18px;
  gap: 10px;
  border: 2px solid #333; 
  transition: border-color 0.3s;
}

.search-bar:focus-within {
  border-color: rgb(255, 120, 0); 
}

.search-bar input {
  background: none;
  border: none;
  color: #e0e0e0; 
  width: 100%;
  font-size: 16px;
}

.search-bar input::placeholder {
  color: #9e9e9e; 
}

.chats-list {
  overflow-y: auto;
  flex-grow: 1;
  padding-bottom: 16px; /* Add padding to ensure last item is fully visible */
}

.chat-item {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  cursor: pointer;
  border-radius: 10px;
}

.chat-item:hover {
  background-color: rgba(255, 255, 255, 0.1); 
  transform: translateX(4px);
}

.avatar-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #333, #555); 
  margin-right: 15px;
}

.chat-name {
  font-size: 18px;
  color: #e0e0e0; 
  font-weight: 500;
}

.last-seen {
  font-size: 14px;
  color: #9e9e9e; 
}

@media (max-width: 768px) {
  .sidebar {
    max-width: 100%;
    border-radius: 0;
  }
}
</style>