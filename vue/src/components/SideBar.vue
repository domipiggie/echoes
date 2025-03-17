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
        <div class="search-container">
          <div class="search-bar">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" placeholder="Keresés az üzenetek között..." />
          </div>
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
            <div class="last-message">{{ chat.lastMessage || 'Még nincs üzenet' }}</div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <style scoped>
.sidebar {
  width: 100%;
  max-width: 360px;
  background: linear-gradient(135deg, #7078e6, #4e55df); 
  color: #e0e0e0; 
  display: flex;
  flex-direction: column;
  height: 95vh; /* Changed from 100vh */
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5); 
  transition: transform 0.3s ease-in-out;
}

.chats-list {
  overflow-y: auto;
  flex-grow: 1;
  padding-bottom: 12px; /* Reduced from 24px */
  scrollbar-width: none;
  -ms-overflow-style: none;
  margin-bottom: 8px; /* Reduced from 12px */
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
}

.chat-item:last-child {
  margin-bottom: 0; /* Ensure last item has no margin */
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
  background: linear-gradient(135deg, #7078e6, #969bdf); 
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

/* Updated search styles */
.search-container {
  margin-bottom: 15px;
}

.search-bar {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 10px 16px;
  gap: 12px;
  transition: all 0.3s ease;
  border: 2px solid rgba(255, 255, 255, 0.05);
}

.search-bar:focus-within {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.search-bar svg {
  color: rgba(255, 255, 255, 0.6);
}

.search-bar input {
  background: none;
  border: none;
  color: #fff;
  width: 100%;
  font-size: 14px;
  outline: none;
  padding: 4px 0;
}

.search-bar input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

/* Remove all duplicate search-bar styles below this point */
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
  border-color:#7078e6; 
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
  padding-bottom: 16px;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE and Edge */
}

.chats-list::-webkit-scrollbar {
  display: none; /* Chrome, Safari and Opera */
  width: 0;
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

.chat-info {
  flex: 1;
  min-width: 0; /* Prevents text overflow */
}

.last-message {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.5);
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.last-seen {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.4);
  margin-top: 1px;
}

@media (max-width: 768px) {
  .sidebar {
    max-width: 100%;
    border-radius: 0;
  }
}
</style>