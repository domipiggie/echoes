<script setup>
  import { defineProps, defineEmits, ref, watch, nextTick, onMounted } from 'vue';
  import ChatProfile from './ChatProfile.vue';
  import GifPicker from './GifPicker.vue'; // Győződj meg róla, hogy ez az import létezik
  
  const props = defineProps({
    currentChat: {
      type: Object,
      required: true
    },
    messages: {
      type: Array,
      required: true
    }
  });
  
  const emit = defineEmits(['send-message', 'go-back']);
  const newMessage = ref('');
  const messagesContainer = ref(null);
  const isMobile = ref(false);
  const showProfile = ref(false);
  const showGifPicker = ref(false);
  const gifButtonRef = ref(null);
  
  onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
    scrollToBottom();
  });
  
  const checkScreenSize = () => {
    isMobile.value = window.innerWidth <= 768;
  };
  
  const submitMessage = () => {
    if (newMessage.value.trim()) {
      emit('send-message', newMessage.value);
      newMessage.value = '';
    }
  };
  
  const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  };
  
  watch(() => props.messages.length, scrollToBottom);
  
  const toggleGifPicker = (event) => {
    event.stopPropagation(); // Megakadályozza a kattintás továbbterjedését
    showGifPicker.value = !showGifPicker.value;
    console.log('GIF picker toggled:', showGifPicker.value);
  };
  
  const handleGifSelect = (gifUrl) => {
    emit('send-message', { text: gifUrl, type: 'gif' });
    showGifPicker.value = false;
  };
</script>

<template>
  <div class="chat-window container-fluid p-0">
    <div class="chat-header row m-0 align-items-center">
      <div class="col d-flex align-items-center">
        <div class="user-info">
          <button 
            class="back-button btn"
            @click="$emit('go-back')"
            v-if="isMobile"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
              <path fill="currentColor" d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/>
            </svg>
          </button>
          <div class="avatar">
            <img src=".src/images/test.jpg" alt="Profilkép">
          </div>
          <div class="user-details">
            <div class="user-name">{{ currentChat.name }}</div>
            <div class="user-status">Online</div>
          </div>
        </div>
      </div>
      <div class="col-auto">
        <button class="more-button btn" @click="showProfile = !showProfile">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="19" cy="12" r="1"></circle>
            <circle cx="5" cy="12" r="1"></circle>
          </svg>
        </button>
      </div>
    </div>
    
    <div class="messages-container" ref="messagesContainer">
      <div 
        v-for="message in messages" 
        :key="message.id" 
        :class="['message', message.sender === 'me' ? 'message-sent' : 'message-received']"
      >
        <div class="message-bubble">
          <img v-if="message.type === 'gif'" :src="message.text" class="message-gif" />
          <span v-else>{{ message.text }}</span>
        </div>
      </div>
    </div>
    
    <div class="input-area">
      <div class="modern-message-box">
        <div class="message-actions left-actions">
          <div class="gif-button-container" style="position: relative;">
            <button 
              class="action-button gif-button"
              @click.stop="toggleGifPicker"
            >
              <span>GIF</span>
            </button>
            <GifPicker
              v-if="showGifPicker"
              :is-open="showGifPicker"
              @select-gif="handleGifSelect"
              @close="showGifPicker = false"
            />
          </div>
        </div>
        <input 
          v-model="newMessage" 
          type="text" 
          placeholder="Írj üzenetet..."
          @keyup.enter="submitMessage"
        />
        <div class="message-actions">
          <button class="action-button send-button" @click="submitMessage">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <ChatProfile 
      v-if="showProfile" 
      :currentChat="currentChat" 
      class="chat-profile-sidebar" 
    />
  </div>
  <ChatProfile 
    v-if="showProfile" 
    :currentChat="currentChat" 
    class="chat-profile-sidebar"
    @update:showProfile="showProfile = $event"
  />
</template>
  
<style scoped>
.message-gif {
  max-width: 200px;
  border-radius: 8px;
}

.chat-window {
  overflow: visible !important;
}

.gif-button {
  background-color: #f0f4ff;
  color: #7078e6;
  font-weight: 600;
  font-size: 13px;
  border-radius: 16px;
  padding: 6px 12px;
  border: none;
}

.gif-button:hover {
  background-color: #e6eaff;
  transform: translateY(-1px);
}

/* Alapvető stílusok */
.chat-window {
  flex: 1;
  display: flex;
  flex-direction: column;
  background-color: #f8fafc;
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  color: #333;
  box-shadow: 0 4px 20px rgba(112, 120, 230, 0.15);
  height: 100%;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  background-color: #ffffff;
  border-bottom: 1px solid rgba(112, 120, 230, 0.2);
  height: 70px;
  box-shadow: 0 2px 10px rgba(112, 120, 230, 0.1);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.user-name {
  font-size: 16px;
  font-weight: 600;
  color: #484a6a;
}

.user-status {
  font-size: 12px;
  color: #7078e6;
}

.back-button {
  background: none;
  border: none;
  cursor: pointer;
  color: #7078e6;
  margin-right: 12px;
  padding: 4px;
  transition: transform 0.2s ease;
}

.back-button:hover {
  transform: translateX(-2px);
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #e6eaee;
  margin-right: 16px;
  overflow: hidden;
  border: 2px solid #7078e6;
  box-shadow: 0 0 0 2px rgba(112, 120, 230, 0.2);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.avatar:hover {
  transform: scale(1.05);
  box-shadow: 0 0 0 3px rgba(112, 120, 230, 0.3);
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-name {
  font-size: 16px;
  font-weight: 600;
  color: #484a6a;
  transition: color 0.2s ease;
}

.user-name:hover {
  color: #7078e6;
}

.more-button {
  background: none;
  border: none;
  cursor: pointer;
  color: #7078e6;
  transition: transform 0.3s ease;
  padding: 6px;
  border-radius: 50%;
}

.more-button:hover {
  transform: rotate(90deg);
  background-color: rgba(112, 120, 230, 0.1);
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  background-color: #ffffff;
  scrollbar-width: thin;
  scrollbar-color: #7078e6 #f0f0f0;
}

.messages-container::-webkit-scrollbar {
  width: 6px;
}

.messages-container::-webkit-scrollbar-track {
  background: #f0f0f0;
}

.messages-container::-webkit-scrollbar-thumb {
  background-color: #7078e6;
  border-radius: 10px;
}

.message {
  max-width: 75%;
  margin-bottom: 16px;
  display: flex;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message-received {
  align-self: flex-start;
}

.message-sent {
  align-self: flex-end;
}

.message-bubble {
  padding: 12px 16px;
  border-radius: 18px;
  word-break: break-word;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  font-size: 14px;
  line-height: 1.5;
  transition: transform 0.2s ease;
}

.message-bubble:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
}

.message-received .message-bubble {
  background-color: #f1f5f9;
  color: #334155;
  border-bottom-left-radius: 4px;
}

.message-sent .message-bubble {
  background-color: #7078e6;
  color: white;
  border-bottom-right-radius: 4px;
}

.input-area {
  padding: 16px;
  background-color: #ffffff;
  border-top: 1px solid rgba(112, 120, 230, 0.05);
}

.left-actions {
  margin-right: 8px;
  margin-left: 4px;
  position: relative;
}

.gif-button-container {
  position: relative;
  z-index: 1001;
  display: inline-block;
}

.modern-message-box {
  display: flex;
  align-items: center;
  background: #ffffff;
  border-radius: 24px;
  padding: 4px 8px 4px 8px;
  border: 1px solid rgba(112, 120, 230, 0.1);
}
input {
  flex: 1;
  border: none;
  outline: none;
  padding: 12px 24px 12px 8px;  /* Increased right padding */
  font-size: 15px;
  color: #2d3748;
  background: transparent;
}

.message-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-right: 4px;
  margin-left: 12px;  /* Added left margin */
}
input::placeholder {
  color: #a0aec0;
}

.message-actions {
  display: flex;
  align-items: center;
  gap: 16px;  /* Increased from 8px */
  margin-right: 4px;
}

.action-button {
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.action-button:hover {
  transform: translateY(-2px);
}

.action-button:active {
  transform: translateY(0);
}

.send-button {
  background-color: #7078e6;
  color: white;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.send-button:hover {
  background-color: #5a61d2;
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(112, 120, 230, 0.3);
}

.send-button:active {
  transform: scale(0.95);
}

.send-button svg {
  transform: rotate(45deg);
  margin-left: -2px;
  margin-top: -2px;
}
.chat-profile-sidebar {
  position: fixed;
  top: 0;
  right: 0;
  height: 100vh;
  width: 360px;
  background: #242526;
  box-shadow: -4px 0 20px rgba(0, 0, 0, 0.2);
  z-index: 9999;
  animation: slideIn 0.3s ease-out;
  overflow-y: auto;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Add to existing media query */
@media (max-width: 768px) {
  .chat-profile-sidebar {
    width: 100%;
  }
}

/* Reszponzív stílusok */
@media (max-width: 1200px) {
  .message {
    max-width: 85%;
  }
}

@media (max-width: 992px) {
  .chat-header {
    padding: 8px 12px;
    height: 60px;
  }

  .avatar {
    width: 36px;
    height: 36px;
  }
}

@media (max-width: 768px) {
  .message {
    max-width: 90%;
  }

  .messages-container {
    padding: 16px;
  }

  .input-area {
    padding: 12px;
  }
}

@media (max-width: 480px) {
  .message {
    max-width: 95%;
  }

  .message-bubble {
    padding: 10px 14px;
  }
  
  .avatar {
    width: 32px;
    height: 32px;
    margin-right: 12px;
  }
  
  .user-name {
    font-size: 14px;
  }
}
</style>