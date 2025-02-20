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
  
  // Scroll to bottom when new messages arrive
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
          />
          <div class="message-actions">
            <button class="gif-button">GIF</button>
            <button class="send-button" @click="submitMessage">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path fill="#17404D" d="M2.01,21L23,12L2.01,3L2,10L17,12L2,14L2.01,21z" />
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
    background-color: #f5f5f5;
    position: relative;
  }
  
  .chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background-color: #fff;
    border-bottom: 1px solid #e0e0e0;
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
    background-color: #e0e0e0;
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
    color: #888;
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
  }
  
  .message-received .message-bubble {
    background-color: #e0e0e0;
  }
  
  .message-sent .message-bubble {
    background-color: #dcf8c6;
  }
  
  .input-area {
    padding: 16px;
    background-color: #f5f5f5;
    border-top: 1px solid #e0e0e0;
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
  
  .gif-button {
    background: none;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 4px 8px;
    margin-right: 8px;
    font-weight: bold;
    cursor: pointer;
  }
  
  .send-button {
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  </style>