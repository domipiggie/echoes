<script setup>
import { ref, defineEmits } from 'vue';
import { userdataStore } from '../store/UserdataStore';
import { useWebSocketStore } from '../store/WebSocketStore';
import { userService } from '../services/userService';
import { useFriendshipStore } from '../store/FriendshipStore.js';

const emit = defineEmits(['close', 'chat-created', 'open-group-dialog']);
const userStore = userdataStore();
const friendshipStore = useFriendshipStore();
const webSocketStore = useWebSocketStore();
const username = ref('');
const isLoading = ref(false);
const errorMessage = ref('');


const createChat = async () => {
  if (!username.value.trim()) {
    errorMessage.value = 'Kérlek adj meg egy felhasználónevet!';
    return;
  }
  try {
    const userData = await userService.getUserByUsername(username.value);
    
    if (!userData.success) {
      console.log(userData)
      errorMessage.value = 'Nem található felhasználó ezzel a névvel.';
      return;
    }
    
    const recipientId = userData.data.userID;
    
    if (webSocketStore.getIsConnected) {
      webSocketStore.send({
        type: 'friend_add',
        recipient_id: recipientId
      });
    }

    emit('close');
  } catch (error) {
    console.error('Error sending friend request:', error);
    if (error.response?.status === 400 && error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Hiba történt a barátkérelem küldése közben.';
    }
  } finally {
    isLoading.value = false;
  }
};

const openGroupCreation = () => {
  emit('open-group-dialog');
  emit('close');
};
</script>

<template>
  <div class="new-chat-overlay" @click.self="emit('close')">
    <div class="new-chat-dialog">
      <div class="dialog-header">
        <h2>Új beszélgetés</h2>
        <button class="group-button" @click="openGroupCreation">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </button>
      </div>

      <div class="input-container">
        <div class="search-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </div>
        <input type="text" v-model="username" placeholder="Add meg a felhasználónevet" @keyup.enter="createChat" />
      </div>

      <div class="error-message" v-if="errorMessage">{{ errorMessage }}</div>

      <div class="button-container">
        <button class="cancel-button" @click="emit('close')">Mégse</button>
        <button class="create-button" @click="createChat" :disabled="isLoading">
          <span>Barátkérelem küldése</span>
          <svg v-if="!isLoading" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"></path>
          </svg>
          <div v-else class="loading-spinner"></div>
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.new-chat-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.new-chat-dialog {
  background: white;
  border-radius: 12px;
  padding: 24px;
  width: 450px;
  /* Increased from 400px to 450px */
  max-width: 90%;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  
  .dark-mode & {
    background: #1e1e2d;
    color: #e0e0e0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
  }
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  position: relative;
}

h2 {
  color: #484a6a;
  margin: 0;
  text-align: center;
  flex-grow: 1;
  
  .dark-mode & {
    color: #e0e0e0;
  }
}

.group-button {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #7078e6;
  color: white;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  box-shadow: 0 2px 5px rgba(112, 120, 230, 0.3);
  padding: 0;
  margin: 0;
  flex-shrink: 0;
  
  .dark-mode & {
    background: #5a62d3;
    box-shadow: 0 2px 5px rgba(112, 120, 230, 0.4);
  }
}

.group-button svg {
  width: 16px;
  height: 16px;
  display: block;
}

.group-button:hover {
  background: #5a62d3;
  transform: translateY(-50%) scale(1.03);
  box-shadow: 0 3px 6px rgba(112, 120, 230, 0.3);
  
  .dark-mode & {
    background: #4e55df;
    box-shadow: 0 3px 6px rgba(112, 120, 230, 0.5);
  }
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #7078e6;
  color: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  
  .dark-mode & {
    background: #5a62d3;
  }
}

.input-container {
  position: relative;
  margin-bottom: 20px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 55%;
  transform: translateY(-50%);
  color: #4a50a1;
  transition: all 0.2s ease;
  background: #f8f9fc;
  
  .dark-mode & {
    color: #7078e6;
    background: #2a2a3d;
  }
}

input {
  width: 100%;
  padding: 12px 12px 12px 40px;
  border: 1px solid #e6e8f0;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s ease;
  background: #f8f9fc;
  
  .dark-mode & {
    background: #2a2a3d;
    border-color: #3a3a4f;
    color: #e0e0e0;
    
    &::placeholder {
      color: #8a8a9a;
    }
  }
}

input:focus {
  border-color: #7078e6;
  box-shadow: 0 0 0 3px rgba(112, 120, 230, 0.2);
  
  .dark-mode & {
    border-color: #7078e6;
    box-shadow: 0 0 0 3px rgba(112, 120, 230, 0.3);
  }
}

input:focus+.search-icon {
  color: #7078e6;
}

.error-message {
  color: #e53e3e;
  font-size: 14px;
  margin-bottom: 20px;
  
  .dark-mode & {
    color: #ff6b6b;
  }
}

.button-container {
  display: flex;
  justify-content: space-between; // Changed from flex-end to space-between
  gap: 12px;
}

.cancel-button,
.create-button {
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: none;
  position: relative;
  overflow: hidden;
}

.cancel-button {
  background: #f0f2f5;
  color: #484a6a;
  
  .dark-mode & {
    background: #2a2a3d;
    color: #e0e0e0;
  }
}

.cancel-button:hover {
  background: #e4e6eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  
  .dark-mode & {
    background: #333345;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  }
}

.create-button {
  background: #7078e6;
  color: white;
  display: flex;
  align-items: center;
  gap: 8px;
  
  .dark-mode & {
    background: #5a62d3;
  }
}

.create-button:hover {
  background: #5a62d3;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(112, 120, 230, 0.4);
  
  .dark-mode & {
    background: #4e55df;
    box-shadow: 0 4px 12px rgba(112, 120, 230, 0.5);
  }
}

.create-button:active,
.cancel-button:active {
  transform: scale(0.95);
  box-shadow: 0 0 0 3px rgba(112, 120, 230, 0.2);
  transition: all 0.1s cubic-bezier(0.4, 0, 0.2, 1);
}

.create-button:active {
  background: #4e55df;
  
  .dark-mode & {
    background: #4248c5;
  }
}

.cancel-button:active {
  background: #dcdfe6;
  
  .dark-mode & {
    background: #252536;
  }
}

.cancel-button::before,
.create-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg,
      transparent,
      rgba(255, 255, 255, 0.2),
      transparent);
  transition: left 0.7s ease;
}

.cancel-button:hover::before,
.create-button:hover::before {
  left: 100%;
}

.create-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.loading-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s linear infinite;
  
  .dark-mode & {
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-top-color: white;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>