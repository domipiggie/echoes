<script setup>
import { ref } from 'vue';
import { useMessageStore } from '../../../store/MessageStore';
import { API_CONFIG } from '../../../config/api';
import ImageModal from './ImageModal.vue';

const messageStore = useMessageStore();
const selectedImage = ref(null);

const openImageModal = (imageUrl) => {
  selectedImage.value = imageUrl;
};

const closeImageModal = () => {
  selectedImage.value = null;
};

const props = defineProps({
  message: Object,
  index: Number,
  messages: Array,
  userID: String,
  currentChannelName: String,
  showVideoOptions: Object
});

const emit = defineEmits([
  'toggle-video-options', 
  'toggle-fullscreen', 
  'toggle-mute', 
  'start-reply', 
  'start-editing', 
  'delete-message'
]);
</script>

<template>
  <div
    class="message discord-style"
    :class="[
      message.getUser().getUserID() == userID ? 'message-sent' : 'message-received',
      {
        'first-message': index === 0 || messages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()
      }
    ]"
  >
    
    <div class="message-content">
      <div class="discord-message-container">
        <div v-if="index === 0 || messages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()" class="discord-avatar">
          <div class="avatar-circle">
            <img v-if="message.getUser().getProfilePicture()" :src="API_CONFIG.BASE_URL + message.getUser().getProfilePicture()" alt="Avatar" class="avatar-image">
            <span v-else>{{ message.getUser().getUserName().charAt(0) }}</span>
          </div>
        </div>
        <div v-else class="discord-avatar-placeholder"></div>
        
        <div class="discord-message-content">
          <div v-if="index === 0 || messages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()" class="discord-username">
            {{ message.getUser().getUserName() }}
          </div>
          
          <div class="discord-message-wrapper">
            <!-- Reply indicator -->
            <div v-if="message.getReplyTo()" class="discord-reply-indicator">
              <div class="reply-arrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="9 14 4 9 9 4"></polyline>
                  <path d="M20 20v-7a4 4 0 0 0-4-4H4"></path>
                </svg>
              </div>
              <div class="reply-content">
                <template v-if="messageStore.getMessageById(message.getReplyTo())?.getType() === 'gif'">
                  <div class="reply-gif-container">
                    <img :src="messageStore.getMessageById(message.getReplyTo())?.getContent()" class="reply-gif" />
                  </div>
                </template>
                <span v-else class="reply-text">{{ messageStore.getMessageById(message.getReplyTo())?.getContent() || "Üzenet betöltése sikertelen" }}</span>
              </div>
            </div>
            
            <div class="message-bubble discord-bubble">
              <!-- Image and GIF handling -->
              <div v-if="message.getType() === 'gif'" class="gif-container">
                <img :src="message.getContent()" class="message-gif" />
              </div>
              <img v-else-if="message.getType() === 'image'" 
                   :src="message.getContent()" 
                   class="message-image"
                   @click="openImageModal(message.getContent())" />
              <ImageModal v-if="selectedImage" :imageUrl="selectedImage" :onClose="closeImageModal" />
              
              <!-- Video handling -->
              <div v-else-if="message.getType() === 'video'" class="video-container">
                <video 
                  :src="message.getContent()" 
                  class="message-video" 
                  controls
                  @click.stop
                  preload="metadata"
                ></video>
                <div class="video-options">
                  <span @click.stop="$emit('toggle-video-options', message.getMessageID())" class="video-options-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="1"></circle>
                      <circle cx="12" cy="5" r="1"></circle>
                      <circle cx="12" cy="19" r="1"></circle>
                    </svg>
                  </span>
                  <div v-if="showVideoOptions[message.getMessageID()]" class="video-options-menu">
                    <div class="video-option-item" @click.stop="$emit('toggle-fullscreen', $event.target.closest('.video-container').querySelector('video'))">
                      Teljes képernyő
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Text messages -->
              <span v-else-if="message.getType() == 'text'" 
                    :class="{ 'revoked-message': message.isRevoked }">
                {{ message.getContent() }}
              </span>
              
              <!-- Discord hover actions -->
              <div class="discord-hover-actions">
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
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../../styles/chat/messages/MessageBase.scss';
@import '../../../styles/chat/messages/MessageActions.scss';
@import '../../../styles/chat/messages/MediaElements.scss';
@import '../../../styles/chat/themes/DiscordTheme.scss';
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

.message-image {
  max-width: 300px;
  max-height: 300px;
  border-radius: 4px;
}

// Discord küldő neve stílus
.sender-name {
  margin-left: 72px; // Igazítás az avatar helyéhez
  margin-bottom: 0;
  font-size: 11px;
  font-weight: 600;
}

.discord-hover-actions {
  position: absolute;
  right: 0px;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.8);
  border-radius: 8px;
  padding: 0px 8px;
  z-index: 100;
  display: flex;
  flex-direction: row;
  
  .hover-action-btn {
    display: block;
    margin: 4px 0;
    padding: 8px;
    width: 32px;
    height: 32px;
    transition: background-color 0.2s ease;
    border-radius: 4px;
    text-align: center;
    
    svg {
      width: 16px;
      height: 16px;
    }
    
    &:hover {
      background: rgba(255, 255, 255, 0.1);
    }
  }
}

// Saját üzenetek szövegének világosítása
.message-sent {
  .message-bubble {
    color: #ffffff; // Fehér szöveg a saját üzenetekhez
    font-weight: 500; // Kicsit vastagabb betűtípus a jobb olvashatóságért
  }
  
  // Válasz szöveg világosítása
  .reply-content {
    .reply-text {
      color: #333333; // Sötétebb szín a válasz szövegének a világosabb háttéren
      opacity: 1;
    }
  }
  
  // Visszavont üzenetek szövegének világosítása
  .revoked-message {
    color: rgba(255, 255, 255, 0.7); // Halványabb fehér a visszavont üzenetekhez
    text-decoration: line-through;
  }
}

// Válasz doboz háttérszínének világosítása
.discord-reply-indicator {
  background-color: #e0e5ff; // Világos kékes háttér
  padding: 4px 8px;
  border-radius: 4px;
  margin-bottom: 4px;
}

// Válasz szöveg világosítása általánosan
.reply-content {
  .reply-text {
    color: #333333; // Sötétebb szín a válasz szövegének a világosabb háttéren
    opacity: 1;
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
}
</style>