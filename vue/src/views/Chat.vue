<template>
  <div class="chat-application">
    <ChatList 
      v-if="!currentChat || isMobile" 
      :chats="chats" 
      @select-chat="selectChat" 
    />
    <ChatWindow 
      v-if="currentChat" 
      :currentChat="currentChat" 
      :messages="messages" 
      @send-message="sendMessage"
      @go-back="goBack"
      @update-message="updateMessage"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import ChatList from '../components/ChatList.vue';
import ChatWindow from '../components/ChatWindow.vue';

// ... existing imports and code ...

// Üzenet frissítés metódus hozzáadása
const updateMessage = (updatedMessage) => {
  console.log('Üzenet frissítés fogadva:', updatedMessage);
  
  // Megkeressük az üzenetet az ID alapján
  const messageIndex = messages.value.findIndex(msg => msg.id === updatedMessage.id);
  
  if (messageIndex !== -1) {
    // Frissítjük az üzenetet a tömbben
    messages.value[messageIndex] = updatedMessage;
    
    // Ha van saveMessages függvény, akkor mentjük a frissített üzeneteket
    if (typeof saveMessages === 'function') {
      saveMessages();
    } else {
      // Ha nincs saveMessages függvény, akkor mentjük a localStorage-ba
      localStorage.setItem(`messages_${currentChat.value.id}`, JSON.stringify(messages.value));
    }
  }
};

// Ha még nincs saveMessages függvény, adjuk hozzá
const saveMessages = () => {
  if (currentChat.value) {
    localStorage.setItem(`messages_${currentChat.value.id}`, JSON.stringify(messages.value));
  }
};
</script>

<style lang="scss">
@import '../styles/Chat.scss';
</style>