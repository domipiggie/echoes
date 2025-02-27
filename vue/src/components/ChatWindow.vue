<script setup>
  import { defineProps, defineEmits, ref, watch, nextTick } from 'vue';
  
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
  
  const emit = defineEmits(['send-message']);
  const newMessage = ref('');
  const messagesContainer = ref(null);
  
  const submitMessage = () => {
    if (newMessage.value.trim()) {
      emit('send-message', newMessage.value);
      newMessage.value = '';
    }
  };

  watch(() => props.messages.length, async () => {
    await nextTick();
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
</script>

<template>
  <div class="chat-window">
    <div class="chat-header">
      <div class="user-info">
        <div class="avatar">
          <img src="" alt="" class="avatar-img" />
        </div>
        <div class="user-name">{{ currentChat.name }}</div>
      </div>
      <div class="header-actions">
        <button class="more-button">•••</button>
      </div>
    </div>
    
    <transition-group name="message-animation" tag="div" class="messages-container" ref="messagesContainer">
      <div 
        v-for="message in messages" 
        :key="message.id" 
        :class="['message', message.sender === 'me' ? 'message-sent' : 'message-received']"
      >
        <div class="message-bubble">
          {{ message.text }}
        </div>
      </div>
    </transition-group>
    
    <div class="input-area">
      <div class="message-box">
        <input 
          v-model="newMessage" 
          type="text" 
          placeholder="Írj üzenetet..."
          @keyup.enter="submitMessage"
        />
        <div class="message-actions">
          <button class="gif-button">GIF</button>
          <button class="send-button" @click="submitMessage">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
              <path fill="#4A148C" d="M2.01,21L23,12L2.01,3L2,10L17,12L2,14L2.01,21z" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chat-window {
  flex: 1;
  display: flex;
  flex-direction: column;
  background-color: #F3E5F5;
  position: relative;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  background-color: #CE93D8;
  color: #fff;
  border-bottom: 1px solid #BA68C8;
  height: 70px;
}

.user-info {
  display: flex;
  align-items: center;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #E1BEE7;
  margin-right: 12px;
  overflow: hidden;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-name {
  font-size: 18px;
  font-weight: 500;
}

.more-button {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #fff;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
}

.message {
  max-width: 70%;
  margin-bottom: 12px;
  display: flex;
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
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  animation: bounceIn 0.5s ease;
}

.message-received .message-bubble {
  background-color: #E1BEE7;
  color: #4A148C;
}

.message-sent .message-bubble {
  background-color: #D1C4E9;
  color: #311B92;
}

.input-area {
  padding: 16px;
  background-color: #F3E5F5;
  border-top: 1px solid #E1BEE7;
}

.message-box {
  display: flex;
  align-items: center;
  background-color: white;
  border-radius: 24px;
  padding: 8px 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.message-box input {
  flex: 1;
  border: none;
  outline: none;
  padding: 8px 0;
  font-size: 16px;
}

.message-actions {
  display: flex;
  align-items: center;
}

.send-button {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.message-animation-enter-active, .message-animation-leave-active {
  transition: all 0.4s ease;
}

.message-animation-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.message-animation-enter-to {
  opacity: 1;
  transform: translateY(0);
}

.message-animation-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.message-animation-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

@keyframes bounceIn {
  0% {
    opacity: 0;
    transform: scale(0.5);
  }
  50% {
    opacity: 1;
    transform: scale(1.1);
  }
  70% {
    transform: scale(0.9);
  }
  100% {
    transform: scale(1);
  }
}
.gif-button{
  background-color: #CE93D8;
  border-radius: 5px;
  border-color: #311B92;
}
</style>
