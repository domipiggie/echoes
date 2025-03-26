<script setup>
  import { defineProps, defineEmits, ref } from 'vue';
  import { userdataStore } from '../store/UserdataStore';
  import NewChatDialog from './NewChatDialog.vue';
  
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
  
  // New chat dialog visibility
  const showNewChatDialog = ref(false);
  
  const handleNewChat = () => {
    showNewChatDialog.value = true;
  };
  
  const handleChatCreated = (newChat) => {
    // You might want to refresh the chat list or add the new chat directly
    console.log('New chat created:', newChat);
  };

  // Sample friend requests data
  const friendRequests = ref([
    {
      id: 1,
      username: 'KovácsAnna',
      avatar: null,
      timestamp: new Date(Date.now() - 3600000 * 2) // 2 hours ago
    }
  ]);

  const formatTime = (date) => {
    const now = new Date();
    const diff = Math.floor((now - date) / 60000); // difference in minutes
    
    if (diff < 60) {
      return `${diff} perce`;
    } else if (diff < 1440) {
      return `${Math.floor(diff / 60)} órája`;
    } else {
      return date.toLocaleDateString('hu-HU');
    }
  };

  const acceptFriendRequest = (request) => {
    console.log('Friend request accepted:', request);
    // Here you would call your API to accept the request
    friendRequests.value = friendRequests.value.filter(r => r.id !== request.id);
  };

  const rejectFriendRequest = (request) => {
    console.log('Friend request rejected:', request);
    // Here you would call your API to reject the request
    friendRequests.value = friendRequests.value.filter(r => r.id !== request.id);
  };
</script>

<template>
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="title-section">
        <h1>Üzenetek</h1>
        <div class="header-buttons">
          <button class="action-btn" @click="handleNewChat">
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
      <div v-if="friendRequests.length === 0" class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="8.5" cy="7" r="4"></circle>
          <line x1="20" y1="8" x2="20" y2="14"></line>
          <line x1="23" y1="11" x2="17" y2="11"></line>
        </svg>
        <p>Nincsenek függőben lévő barátkérelmek</p>
      </div>
      
      <div v-else>
        <div v-for="request in friendRequests" :key="request.id" class="friend-request-item">
          <div class="avatar">
            <div class="avatar-circle">
              <!-- Display first letter of username if no avatar -->
              <span v-if="!request.avatar">{{ request.username.charAt(0) }}</span>
            </div>
          </div>
          <div class="request-info">
            <div class="request-name">{{ request.username }}</div>
            <div class="request-time">{{ formatTime(request.timestamp) }}</div>
          </div>
          <div class="request-actions">
            <button class="accept-btn round accept" @click="acceptFriendRequest(request)">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </button>
            <button class="reject-btn round reject" @click="rejectFriendRequest(request)">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f44336" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- New Chat Dialog -->
    <NewChatDialog 
      v-if="showNewChatDialog" 
      @close="showNewChatDialog = false"
      @chat-created="handleChatCreated"
    />
  </div>
</template>
  
<style lang="scss" scoped>
@import '../styles/SideBar.scss';

.friend-request-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 4px;
  transition: background-color 0.2s ease;
  
  &:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }
  
  .avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #5a62d3;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 500;
    font-size: 16px;
  }
  
  .request-info {
    flex: 1;
    margin-left: 12px;
  }
  
  .request-name {
    font-weight: 500;
    color: white;
    margin-bottom: 2px;
  }
  
  .request-time {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
  }
  
  .request-actions {
    display: flex;
    gap: 10px;
  }
  
  .accept-btn, .reject-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    background: white;
  }
  
  .accept-btn {
    &:hover {
      background: #f5f5f5;
      transform: scale(1.05);
    }
    
    &:active {
      transform: scale(0.95);
    }
  }
  
  .reject-btn {
    &:hover {
      background: #f5f5f5;
      transform: scale(1.05);
    }
    
    &:active {
      transform: scale(0.95);
    }
  }
}
</style>
