<script setup>
  import { defineProps, defineEmits, ref } from 'vue';
  import { userdataStore } from '../store/UserdataStore';
  
  const userStore = userdataStore();
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
  
  // Aktív nézet kezelése
  const activeView = ref('chats'); // 'chats' vagy 'requests'
</script>

<template>
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="title-section">
        <h1>Üzenetek</h1>
        <div class="header-buttons">
          <button class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
              <path fill="currentColor" d="M19,3H5C3.89,3,3,3.89,3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M17,13H13V17H11V13H7V11H11V7H13V11H17V13Z"/>
            </svg>
          </button>
          <button class="action-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
              <path fill="currentColor" d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
            </svg>
          </button>
        </div>
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
    
    <!-- Navigációs gombok -->
    <div class="tab-navigation">
      <button 
        class="tab-button" 
        :class="{ active: activeView === 'chats' }"
        @click="activeView = 'chats'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        ÜZENETEK
      </button>
      <button 
        class="tab-button" 
        :class="{ active: activeView === 'requests' }"
        @click="activeView = 'requests'"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="8.5" cy="7" r="4"></circle>
          <line x1="20" y1="8" x2="20" y2="14"></line>
          <line x1="23" y1="11" x2="17" y2="11"></line>
        </svg>
        BARÁTKÉRELMEK
      </button>
    </div>

    <!-- Üzenetek lista -->
    <div v-if="activeView === 'chats'" class="chats-list">
      <div 
        v-for="chat in recents" 
        :key="chat.channelID" 
        class="chat-item"
        @click="selectChat(chat)"
      >
        <div class="avatar">
          <div class="avatar-circle"></div>
        </div>
        <div class="chat-info">
          <div class="chat-name">{{ userStore.getUserID() == chat.user1.id ? chat.user2.username : chat.user1.username }}</div>
          <div class="last-seen">{{  }}</div>
          <div class="last-message">{{ chat.lastMessage || 'Még nincs üzenet' }}</div>
        </div>
      </div>
    </div>

    <!-- Barátkérelmek lista -->
    <div v-if="activeView === 'requests'" class="chats-list">
      <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="8.5" cy="7" r="4"></circle>
          <line x1="20" y1="8" x2="20" y2="14"></line>
          <line x1="23" y1="11" x2="17" y2="11"></line>
        </svg>
        <p>Nincsenek függőben lévő barátkérelmek</p>
      </div>
    </div>
  </div>
</template>
  
<style scoped>
/* Megtartott lila design stílusok */
.sidebar {
  width: 100%;
  max-width: 360px;
  background: linear-gradient(135deg, #7078e6, #4e55df);
  color: #e0e0e0;
  display: flex;
  flex-direction: column;
  height: 95vh;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
  transition: transform 0.3s ease-in-out;
}

/* Második kép stílusú tab navigáció */
/* Tab navigáció stílusa */
.tab-navigation {
  display: flex;
  background: rgba(255, 255, 255, 0.1);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  position: relative;
  overflow: hidden;
}

.tab-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  flex: 1;
  padding: 14px;
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.6);
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  z-index: 1;
  overflow: hidden;
}

.tab-button::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 150%;
  height: 150%;
  background: rgba(255, 255, 255, 0.1);
  transform: translate(-50%, -50%) scale(0);
  border-radius: 50%;
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: -1;
}

.tab-button.active {
  color: #fff;
}

.tab-button.active::before {
  transform: translate(-50%, -50%) scale(1);
}

.tab-button.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: #fff;
  transform: scaleX(1);
  transform-origin: left;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-button:not(.active)::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: #fff;
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-button:hover:not(.active)::after {
  transform: scaleX(1);
  transform-origin: left;
}

.tab-button svg {
  opacity: 0.8;
  transform: scale(1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-button.active svg {
  opacity: 1;
  transform: scale(1.1);
}

.tab-button.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: #fff;
  transform: scaleX(1);
  transform-origin: left;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-button:not(.active)::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: #fff;
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-button:hover:not(.active)::after {
  transform: scaleX(1);
  transform-origin: left;
}

.tab-button svg {
  opacity: 0.8;
  transform: scale(1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-button.active svg {
  opacity: 1;
  transform: scale(1.1);
}

/* Meglévő stílusok megtartása */
.sidebar-header {
  padding: 20px;
  background: rgba(255, 255, 255, 0.08);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.title-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 15px;
}

.header-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
  background: linear-gradient(135deg, #7078e6, #969bdf);
  border: none;
  color: #fff;
  cursor: pointer;
  padding: 10px;
  border-radius: 50%;
  transition: all 0.2s ease-in-out;
}

.action-btn:hover {
  transform: scale(1.1);
  background: linear-gradient(135deg, #969bdf, #7078e6);
}

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

.chats-list {
  overflow-y: auto;
  flex-grow: 1;
  padding-bottom: 12px;
  scrollbar-width: none;
  -ms-overflow-style: none;
  margin-bottom: 8px;
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
}

.chats-list::-webkit-scrollbar {
  display: none;
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
  min-width: 0;
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

/* Üres állapot stílusa */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: rgba(255, 255, 255, 0.5);
  text-align: center;
  padding: 20px;
}

.empty-state svg {
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-state p {
  font-size: 14px;
}

@media (max-width: 768px) {
  .sidebar {
    max-width: 100%;
    border-radius: 0;
  }
}
</style>
