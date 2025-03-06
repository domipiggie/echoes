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
          <img :src="currentChat.avatar" alt="Avatar" class="avatar-img" />
        </div>
        <div class="user-name">{{ currentChat.name }}</div>
      </div>
      <div class="header-actions">
        <button class="more-button" aria-label="További beállítások">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
          </svg>
        </button>
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
          <button class="send-button" @click="submitMessage" aria-label="Üzenet küldése">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
              <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
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
  background: linear-gradient(135deg, #121212, #212121); 
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
  color: #e0e0e0; 
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  background: linear-gradient(135deg, #222, #333); 
  border-bottom: 1px solid #444;
  height: 70px;
}

.user-info {
  display: flex;
  align-items: center;
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #222, #444);
  margin-right: 16px;
  overflow: hidden;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
  /*ide kkep jon majd*/
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-name {
  font-size: 20px;
  font-weight: 600;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

.more-button {
  background: none;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: #e0e0e0;
  transition: transform 0.2s ease-in-out, opacity 0.3s ease;
  opacity: 0.8;
}

.more-button:hover {
  transform: scale(1.1);
  opacity: 1;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

.message {
  max-width: 80%;
  margin-bottom: 16px;
  display: flex;
}

.message-received {
  align-self: flex-start;
}

.message-sent {
  align-self: flex-end;
}

.message-bubble {
  padding: 16px 22px;
  border-radius: 20px;
  word-break: break-word;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.3);
  animation: bounceIn 0.2s ease;
  transition: background-color 0.2s ease, transform 0.2s ease;
}

.message-received .message-bubble {
  background-color: #333;
  color: #e0e0e0;
  transform: translateX(2px);
}

.message-sent .message-bubble {
  background: linear-gradient(135deg, #ac3333, rgb(222, 96, 33)); 
  color: #e0e0e0;
  transform: translateX(-2px);
}

.message-bubble:hover {
  transform: scale(1.02);
}

.input-area {
  padding: 20px;
  background-color: #212121; 
  border-top: 1px solid #333;
}

.message-box {
  display: flex;
  align-items: center;
  background-color: #333;
  border-radius: 30px;
  padding: 0px 20px;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3);
}

.message-box input {
  flex: 1;
  border: none;
  outline: none;
  padding: 5px 0;
  font-size: 16px;
  color: #e0e0e0;
  background-color: transparent;
}

.message-box input::placeholder {
  color: #9e9e9e;
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
  color: #444;
  transition: transform 0.5s ease-in-out, color 0.5s ease;
}

.send-button:hover {
  transform: scale(1.1);
  color: rgb(255, 120, 0);
}

.send-button.sending { 
  transform: scale(1.2);
  color: #444;
}

.gif-button {
  background-color: #444;
  border-radius: 5px;
  border: 1px solid #555;
  padding: 8px 12px;
  color: #e0e0e0;
  cursor: pointer;
  margin-right: 8px;
  transition: transform 0.5s ease-in-out, color 0.5s ease;
}

.gif-button:hover {
  background-color: rgb(255, 120, 0);
}
</style>