<script setup>
  import { defineProps, defineEmits, ref, watch, nextTick, onMounted } from 'vue';
  
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
</script>

<template>
  <div class="chat-window container-fluid p-0">
    <div class="chat-header row m-0 align-items-center">
      <div class="col d-flex align-items-center">
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
        <div class="user-name">{{ currentChat.name }}</div>
      </div>
      <div class="col-auto">
        <button class="more-button btn">
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
          {{ message.text }}
        </div>
      </div>
    </div>
    
    <div class="input-area">
      <div class="message-box">
        <input 
          v-model="newMessage" 
          type="text" 
          placeholder="Írj üzenetet..."
          @keyup.enter="submitMessage"
          class="form-control"
        />
        <div class="message-actions">
          <button class="gif-button">GIF</button>
          <button class="send-button bi bi-send" @click="submitMessage">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
              <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
    <ChatProfile v-if="showProfile" :currentChat="currentChat" />
  </div>
</template>
  
<style scoped>
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
  border-top: 1px solid rgba(112, 120, 230, 0.2);
}

.message-box {
  display: flex;
  align-items: center;
  background-color: #f6f8ff;
  border-radius: 24px;
  padding: 6px 16px;
  box-shadow: 0 2px 8px rgba(112, 120, 230, 0.1);
  transition: box-shadow 0.3s ease;
}

.message-box:focus-within {
  box-shadow: 0 4px 12px rgba(112, 120, 230, 0.2);
}

.message-box input {
  flex: 1;
  border: none;
  outline: none;
  padding: 10px 0;
  font-size: 14px;
  color: #334155;
  background-color: transparent;
}

.message-box input::placeholder {
  color: #94a3b8;
}

.message-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.send-button {
  background-color: #7078e6;
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  transition: all 0.3s ease;
}

.send-button:hover {
  background-color: #5a61d2;
  transform: scale(1.1);
}

.send-button:active {
  transform: scale(0.95);
}

.gif-button {
  background-color: white;
  border-radius: 16px;
  border: 1px solid rgba(112, 120, 230, 0.3);
  padding: 6px 12px;
  color: #7078e6;
  font-weight: 600;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.gif-button:hover {
  background-color: rgba(112, 120, 230, 0.1);
  border-color: #7078e6;
  transform: translateY(-1px);
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
  
  .gif-button {
    padding: 4px 8px;
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