<script setup>
export default {
  data() {
    return {
      messages: [],
      newMessage: ""
    };
  },
  methods: {
    sendMessage() {
      if (this.newMessage.trim()) {
        this.messages.push(this.newMessage);
        this.newMessage = "";
      }
    }
  }
};
</script>
<template>
    <div class="chat-container">
      <div class="chat-messages">
        <div v-for="(message, index) in messages" :key="index" class="chat-message">
          {{ message }}
        </div>
      </div>
      <div class="chat-input">
        <label for="file-upload" class="upload-logo">📷</label>
        <input id="file-upload" type="file" class="file-input" />
        <input v-model="newMessage" type="text" placeholder="Írj valamit..." class="message-input" />
        <button @click="sendMessage" class="send-button">Küldés</button>
      </div>
    </div>
  </template>
  
  <style>
  .chat-container {
    display: flex;
    flex-direction: column;
    width: 400px;
    height: 500px;
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 10px;
  }
  .chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
  }
  .chat-message {
    background: #f1f1f1;
    padding: 8px;
    margin: 5px 0;
    border-radius: 5px;
  }
  .chat-input {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .message-input {
    flex: 1;
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  .send-button {
    background: #3AB982;
    color: white;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    border-radius: 5px;
  }
  .upload-logo {
    cursor: pointer;
    font-size: 20px;
  }
  .file-input {
    display: none;
  }
  </style>
  