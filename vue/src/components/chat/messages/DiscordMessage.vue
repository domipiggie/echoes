<script setup>
import { useMessageStore } from '../../../store/MessageStore';
const messageStore = useMessageStore();

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
    <div class="discord-message-container">
      <div v-if="index === 0 || messages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()" class="discord-avatar">
        <div class="avatar-circle">
          <span>{{ message.getUser().getUserID() == userID ? 'Y' : currentChannelName.charAt(0) }}</span>
        </div>
      </div>
      <div v-else class="discord-avatar-placeholder"></div>
      
      <div class="discord-message-content">
        <div v-if="index === 0 || messages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()" class="discord-username">
          {{ message.getUser().getUserID() == userID ? 'You' : currentChannelName }}
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
              <span class="reply-text">{{ messageStore.getMessageById(message.getReplyTo()).getContent() }}</span>
            </div>
          </div>
          
          <div class="message-bubble discord-bubble">
            <!-- Image and GIF handling -->
            <img v-if="message.getType() === 'gif' || message.getType() === 'image'" 
                 :src="message.getContent()" 
                 class="message-gif" />
            
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
                <button @click.stop="$emit('toggle-video-options', message.getMessageID())" class="video-options-button">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="12" cy="5" r="1"></circle>
                    <circle cx="12" cy="19" r="1"></circle>
                  </svg>
                </button>
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
            <span v-else-if="message.getType() !== 'audio' && message.getType() !== 'file'" 
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
</template>

<style lang="scss" scoped>
@import '../../../styles/chat/messages/MessageBase.scss';
@import '../../../styles/chat/messages/MessageActions.scss';
@import '../../../styles/chat/messages/MediaElements.scss';
@import '../../../styles/chat/themes/DiscordTheme.scss';
@import '../../../styles/chat/Avatar.scss';
</style>