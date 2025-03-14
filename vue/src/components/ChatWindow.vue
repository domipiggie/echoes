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
  const showProfile = ref(false); // Added missing ref
  
  onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
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
        <button 
          class="back-button"
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
    <ChatProfile v-if="showProfile" :currentChat="currentChat" />
  </div>
  </template>
  
  
  
  <style scoped>
  .chat-window {
    flex: 1;
    display: flex;
    flex-direction: column;
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
    background-color: #f0f2f5;
  }
  
  .chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background-color: #ffffff;
    border-bottom: 1px solid #e4e6eb;
    height: 70px;
  }
  
  .user-info {
    display: flex;
    align-items: center;
  }
  
  .avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #e4e6eb;
    margin-right: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  
  .user-name {
    font-size: 18px;
    font-weight: 600;
    color: #050505;
  }
  
  .more-button {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #65676b;
    transition: all 0.2s ease;
  }
  
  .more-button:hover {
    color: #1876f2;
  }
  
  .messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    background-color: #ffffff;
  }
  
  .message {
    max-width: 65%;
    margin-bottom: 16px;
    display: flex;
  }
  
  /* Removing the fadeIn animation and its keyframes */
  
  .message-bubble {
    padding: 12px 16px;
    border-radius: 18px;
    word-break: break-word;
  }
  
  .message-received .message-bubble {
    background-color: #e4e6eb;
    color: #050505;
  }
  
  .message-sent .message-bubble {
    background-color: #0084ff;
    color: #ffffff;
  }
  
  /* Remove hover effect */
  .message-bubble:hover {
    transform: none;
  }
  
  .message-received {
    align-self: flex-start;
  }
  
  .message-sent {
    align-self: flex-end;
  }
  
  .message-actions {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .gif-button {
    background-color: #0084ff;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    color: #ffffff;
    font-weight: 500;
    margin-right: 12px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 60px;
  }
  
  .message-received .message-bubble {
    background-color: #e4e6eb;
    color: #050505;
  }
  
  .message-sent .message-bubble {
    background-color: #0084ff;
    color: #ffffff;
  }
  
  .message-bubble:hover {
    transform: scale(1.02);
  }
  
  .input-area {
    padding: 16px;
    background-color: #ffffff;
    border-top: 1px solid #e4e6eb;
  }
  
  .message-box {
    display: flex;
    align-items: center;
    background-color: #f0f2f5;
    border-radius: 20px;
    padding: 8px 16px;
    transition: all 0.3s ease;
  }
  
  .message-box:focus-within {
    background-color: #ffffff;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  }
  
  .message-box input {
    flex: 1;
    border: none;
    outline: none;
    padding: 8px 0;
    font-size: 15px;
    color: #050505;
    background-color: transparent;
  }
  
  .message-box input::placeholder {
    color: #65676b;
  }
  
  .gif-button {
    background-color: #0084ff;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    color: #ffffff;
    font-weight: 500;
    margin-right: 12px;
    transition: all 0.2s ease;
  }
  
  .gif-button:hover {
    background-color: #0073e6;
    transform: translateY(-1px);
  }
  
  .send-button {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    color: #0084ff;
    transition: all 0.2s ease;
  }
  
  .send-button:hover {
    transform: scale(1.1);
  }
  
  .send-button svg path {
    fill: #0084ff;
  }
  
  /* Scrollbar styling */
  .messages-container::-webkit-scrollbar {
    width: 8px;
  }
  
  .messages-container::-webkit-scrollbar-track {
    background: transparent;
  }
  
  .messages-container::-webkit-scrollbar-thumb {
    background: #bcc0c4;
    border-radius: 4px;
  }
  
  /* Keep existing media queries but update some values */
  @media (max-width: 768px) {
    .message {
      max-width: 85%;
    }
  
    .messages-container {
      padding: 12px;
    }
  
    .input-area {
      padding: 12px;
    }
  }
</style>

