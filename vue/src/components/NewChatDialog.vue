<script setup>
import { ref, defineEmits } from 'vue';
import { userdataStore } from '../store/UserdataStore';
import { chatService } from '../services/chatService';

const emit = defineEmits(['close', 'chat-created']);
const userStore = userdataStore();
const username = ref('');
const isLoading = ref(false);
const errorMessage = ref('');

const createChat = async () => {
  if (!username.value.trim()) {
    errorMessage.value = 'Kérlek adj meg egy felhasználónevet!';
    return;
  }
  
  isLoading.value = true;
  errorMessage.value = '';
  
  try {
    const response = await chatService.createFriendship(username.value);
    console.log('Friendship created:', response);
    emit('chat-created', response);
    emit('close');
  } catch (error) {
    console.error('Error creating friendship:', error);
    errorMessage.value = error.response?.data?.message || 'Hiba történt a barátkérelem küldése közben.';
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="new-chat-overlay" @click.self="emit('close')">
    <div class="new-chat-dialog">
      <h2>Új beszélgetés</h2>
      
      <div class="input-container">
        <div class="search-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </div>
        <input 
          type="text" 
          v-model="username" 
          placeholder="Add meg a felhasználónevet" 
          @keyup.enter="createChat"
        />
      </div>
      
      <div class="error-message" v-if="errorMessage">{{ errorMessage }}</div>
      
      <div class="button-container">
        <button class="cancel-button" @click="emit('close')">Mégse</button>
        <button class="create-button" @click="createChat" :disabled="isLoading">
          <span>Barátkérelem küldése</span>
          <svg v-if="!isLoading" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
  width: 450px; /* Increased from 400px to 450px */
  max-width: 90%;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

h2 {
  color: #484a6a;
  margin: 0 0 20px 0;
  text-align: center;
}

.input-container {
  position: relative;
  margin-bottom: 20px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #484a6a;
  transition: all 0.2s ease;
  background: #f8f9fc;
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
}

input:focus {
  border-color: #7078e6;
  box-shadow: 0 0 0 3px rgba(112, 120, 230, 0.2);
}

input:focus + .search-icon {
  color: #7078e6;
}

.error-message {
  color: #e53e3e;
  font-size: 14px;
  margin-bottom: 20px;
}

.button-container {
  display: flex;
  justify-content: space-between; // Changed from flex-end to space-between
  gap: 12px;
}

.cancel-button, .create-button {
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.cancel-button {
  background: #f0f2f5;
  color: #484a6a;
}

.cancel-button:hover {
  background: #e4e6eb;
}

.create-button {
  background: #7078e6;
  color: white;
  display: flex;
  align-items: center;
  gap: 8px;
}

.create-button:hover {
  background: #5a62d3;
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
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>