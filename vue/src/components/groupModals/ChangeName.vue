<script setup>
import { useWebSocketStore } from '../../store/WebSocketStore';
import { useMessageStore } from '../../store/messageStore';

import { ref } from 'vue';

const emit = defineEmits(['close']);
const webSocketStore = useWebSocketStore();
const messageStore = useMessageStore();

const groupName = ref('');

const changeName = () => {
    if (webSocketStore.isConnected) {
        webSocketStore.send({
            type: 'group_update_info',
            channelId: messageStore.getCurrentChannelId,
            groupName: groupName.value
        });
        emit('close');
    }
};
</script>

<template>
  <div class="new-group-overlay" @click.self="emit('close')">
    <div class="new-group-dialog">
      <div class="dialog-header">
        <h2>Csoport név módosítása</h2>
        <button class="close-button" @click="emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="input-container">
        <input type="text" v-model="groupName" :placeholder="messageStore.getCurrentChannelName" />
      </div>

      <div class="button-container">
        <button class="cancel-button" @click="emit('close')">Mégse</button>
        <button class="create-button" @click="changeName" :disabled="groupName.length === 0">
          <span>Kész</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.new-group-overlay {
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

.new-group-dialog {
  background: white;
  border-radius: 12px;
  padding: 24px;
  width: 450px;
  max-width: 90%;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  
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
}

h2 {
  color: #484a6a;
  margin: 0;
  
  .dark-mode & {
    color: #e0e0e0;
  }
}

.close-button {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f0f2f5;
  color: #484a6a;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  
  .dark-mode & {
    background: #2a2a3d;
    color: #e0e0e0;
  }
}

.close-button:hover {
  background: #e4e6eb;
  transform: scale(1.05);
}

.input-container {
  margin-bottom: 20px;
}

input {
  width: 100%;
  padding: 12px;
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

.friends-list-container {
  margin-bottom: 20px;
  overflow-y: auto;
  flex-grow: 1;
}

h3 {
  color: #484a6a;
  font-size: 16px;
  margin: 0 0 12px 0;
  
  .dark-mode & {
    color: #e0e0e0;
  }
}

.friends-list {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #e6e8f0;
  border-radius: 8px;
  
  .dark-mode & {
    border-color: #3a3a4f;
  }
}

.friend-item {
  display: flex;
  align-items: center;
  padding: 12px;
  border-bottom: 1px solid #e6e8f0;
  cursor: pointer;
  transition: background-color 0.2s ease;
  
  .dark-mode & {
    border-bottom-color: #3a3a4f;
  }
}

.friend-item:last-child {
  border-bottom: none;
}

.friend-item:hover {
  background-color: #f8f9fc;
  
  .dark-mode & {
    background-color: #333345;
  }
}

.friend-item.selected {
  background-color: rgba(112, 120, 230, 0.1);
  
  .dark-mode & {
    background-color: rgba(112, 120, 230, 0.2);
  }
}

.friend-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #7078e6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-right: 12px;
  
  .dark-mode & {
    background-color: #5a62d3;
  }
}

.friend-info {
  flex-grow: 1;
}

.friend-name {
  font-weight: 500;
  color: #484a6a;
  
  .dark-mode & {
    color: #e0e0e0;
  }
}

.selection-indicator {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid #e6e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  
  .dark-mode & {
    border-color: #3a3a4f;
  }
}

.friend-item.selected .selection-indicator {
  background-color: #7078e6;
  border-color: #7078e6;
}

.no-friends-message {
  padding: 20px;
  text-align: center;
  color: #6b7280;
  font-style: italic;
  
  .dark-mode & {
    color: #8a8a9a;
  }
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
  justify-content: space-between;
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

.create-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
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