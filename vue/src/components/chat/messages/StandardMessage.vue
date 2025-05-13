<script setup>
import { ref } from 'vue';
import { useMessageStore } from '../../../store/MessageStore';
import { API_CONFIG } from '../../../config/api';
import ImageModal from './ImageModal.vue';

const messageStore = useMessageStore();
const selectedImage = ref(null);

const props = defineProps({
  message: Object,
  index: Number,
  messages: Array,
  userID: String,
  currentChannelName: String
});

const emit = defineEmits([
  'start-reply', 
  'start-editing', 
  'delete-message'
]);

const openImageModal = (imageUrl) => {
  selectedImage.value = imageUrl;
};

const closeImageModal = () => {
  selectedImage.value = null;
};
</script>

<template>
  <div 
    :class="[
      'message', 
      message.getUser().getUserID() == userID ? 'message-sent' : 'message-received',
      {
        'first-message': index === 0 || messages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()
      }
    ]"
  >
    <!-- Küldő neve - csak csoportos beszélgetésben jelenik meg, ha változik a küldő -->
    <div v-if="currentChannelName && (index === 0 || messages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID())" class="sender-name">
      {{ message.getUser().getUserName() }}
    </div>
    
    <div class="message-content">
      <!-- Hover action buttons -->
      <div class="message-hover-actions">
        <!-- Csak a válasz gomb jelenik meg a másik félnél -->
        <template v-if="message.getUser().getUserID() != userID">
          <button class="hover-action-btn" @click="$emit('start-reply', message)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
          </button>
        </template>
        <!-- Minden gomb megjelenik a saját üzeneteknél -->
        <template v-else>
          <button v-if="!message.isRevoked" class="hover-action-btn" @click="$emit('start-editing', message)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </button>
          <button class="hover-action-btn" @click="$emit('start-reply', message)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
          </button>
          <button class="hover-action-btn" @click="$emit('delete-message', message.getMessageID())">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
            </svg>
          </button>
        </template>
      </div>
      
      <!-- Profilkép csak az első üzenetnél jelenik meg, ha ugyanaz a küldő -->
      <div class="message-avatar" v-if="index === 0 || messages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()">
        <div class="avatar-circle">
          <img v-if="message.getUser().getProfilePicture()" :src="API_CONFIG.BASE_URL + message.getUser().getProfilePicture()" alt="Avatar" class="avatar-image">
          <span v-else>{{ message.getUser().getUserName().charAt(0).toUpperCase() }}</span>
        </div>
      </div>
      
      <div class="message-bubble">
        <!-- Reply indicator -->
        <div v-if="message.getReplyTo()" class="reply-indicator">
          <div class="reply-content">
            <span class="reply-to">Válasz erre:</span>
            <template v-if="messageStore.getMessageById(message.getReplyTo())?.getType() === 'gif'">
              <div class="reply-gif-container">
                <img :src="messageStore.getMessageById(message.getReplyTo())?.getContent()" class="reply-gif" />
              </div>
            </template>
            <span v-else class="reply-text">{{ messageStore.getMessageById(message.getReplyTo())?.getContent() || "Üzenet betöltése sikertelen" }}</span>
          </div>
        </div>
        
        <!-- Image and GIF handling -->
        <div v-if="message.getType() === 'gif'" class="gif-container">
          <img :src="message.getContent()" class="message-gif" />
        </div>
        <div v-else-if="message.getType() === 'image'" class="message-image-container">
          <img :src="message.getContent()" class="message-image" @click="openImageModal(message.getContent())" alt="" aria-hidden="true" />
        </div>
        <ImageModal v-if="selectedImage" :imageUrl="selectedImage" :onClose="closeImageModal" />
        
        <!-- Video handling -->
        <div v-else-if="message.getType() === 'video'" class="video-container">
          <video 
            :src="message.getContent()" 
            class="message-video" 
            controls
            preload="metadata"
            aria-label="Beágyazott videó"
          ></video>
        </div>
        
        <!-- Text messages -->
        <span v-else-if="message.getType() == 'text'" 
              :class="{ 'revoked-message': message.isRevoked }">
            {{ message.getContent() }}
        </span>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../../styles/chat/messages/MessageBase.scss';
@import '../../../styles/chat/messages/MessageActions.scss';
@import '../../../styles/chat/messages/MediaElements.scss';
@import '../../../styles/chat/Avatar.scss';

// GIF konténer stílusok
.gif-container {
  background-color: rgba(138, 43, 226, 0.1); // Halvány lila háttér
  border-radius: 8px;
  padding: 8px;
  margin: 4px 0;
  
  .message-gif {
    max-width: 200px; // Kisebb méret
    max-height: 200px;
    border-radius: 4px;
  }
}

.message-image-container {
  max-width: 400px;
  max-height: 300px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.05);
  overflow: hidden;
  
  .message-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 4px;
    font-size: 0;
  }
}

.reply-gif-container {
  background-color: rgba(138, 43, 226, 0.1); // Halvány lila háttér
  border-radius: 6px;
  padding: 6px;
  margin: 2px 0;
  
  .reply-gif {
    max-width: 150px; // Még kisebb méret a válaszokban
    max-height: 150px;
    border-radius: 3px;
  }
}

.video-container {
  width: 400px;
  height: 300px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.05);
  overflow: hidden;

  .message-video {
    width: 100%;
    height: 100%;
    object-fit: contain;
    font-size: 0;
  }
}
</style>

