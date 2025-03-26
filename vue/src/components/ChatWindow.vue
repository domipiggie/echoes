<script setup>
import { defineProps, defineEmits, ref, watch, nextTick, onMounted } from 'vue';
import ChatProfile from './ChatProfile.vue';
import GifPicker from './GifPicker.vue'; // Győződj meg róla, hogy ez az import létezik

// Add currentTheme ref to track the selected theme
const currentTheme = ref('messenger'); // Default theme

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
const showGifPicker = ref(false);
const gifButtonRef = ref(null);

// Add handler for theme changes
const handleThemeChange = (theme) => {
  console.log('Theme changed to:', theme);
  currentTheme.value = theme;
  // Optionally save to localStorage
  localStorage.setItem('chatTheme', theme);
};

// Load saved theme on mount
onMounted(() => {
  const savedTheme = localStorage.getItem('chatTheme');
  if (savedTheme) {
    currentTheme.value = savedTheme;
  }

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

const toggleGifPicker = (event) => {
  event.stopPropagation(); // Megakadályozza a kattintás továbbterjedését
  showGifPicker.value = !showGifPicker.value;
  console.log('GIF picker toggled:', showGifPicker.value);
};

const handleGifSelect = (gifUrl) => {
  emit('send-message', { text: gifUrl, type: 'gif' });
  showGifPicker.value = false;
};

const fileInput = ref(null);

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (!file || !(file.type.startsWith('image/') || file.type.startsWith('video/'))) return;

  console.log('Selected file:', file);
  console.log('File type:', file.type);

  const reader = new FileReader();
  reader.onload = (e) => {
    const message = {
      id: Date.now(), // Add unique ID
      text: e.target.result,
      type: file.type.startsWith('image/') ? 'image' : 'video',
      fileName: file.name,
      fileSize: file.size,
      fileType: file.type,
      sender: 'me',
      timestamp: new Date().toISOString()
    };

    console.log('Message structure:', {
      id: message.id,
      type: message.type,
      fileName: message.fileName,
      fileSize: message.fileSize,
      textLength: message.text.length,
      sender: message.sender
    });

    emit('send-message', message);
  };

  reader.onerror = (error) => {
    console.error('Error reading file:', error);
  };

  reader.readAsDataURL(file);
  event.target.value = '';
};

const toggleFullscreen = (video) => {
  if (!document.fullscreenElement) {
    video.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
};

const showVideoOptions = ref({});

const toggleVideoOptions = (messageId) => {
  showVideoOptions.value[messageId] = !showVideoOptions.value[messageId];
};

const videoStates = ref({});

const toggleMute = (video, messageId) => {
  video.muted = !video.muted;
  videoStates.value[messageId] = {
    ...videoStates.value[messageId],
    muted: video.muted
  };
};

</script>

  const toggleMute = (video, messageId) => {
    video.muted = !video.muted;
    videoStates.value[messageId] = {
      ...videoStates.value[messageId],
      muted: video.muted
    };
  };

  </script>

  <template>
    <div class="chat-window container-fluid p-0" :class="currentTheme">
      <div class="chat-header row m-0 align-items-center">
        <div class="col d-flex align-items-center">
          <div class="user-info">
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
              <div class="avatar-circle">
                <span>{{ currentChat.name.charAt(0) }}</span>
              </div>
            </div>
            <div class="user-details">
              <div class="user-name">{{ currentChat.name }}</div>
              <div class="user-status">Online</div>
            </div>
          </div>
        </div>
        <div class="col-auto">
          <button class="more-button btn" @click="showProfile = !showProfile">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="1"></circle>
              <circle cx="19" cy="12" r="1"></circle>
              <circle cx="5" cy="12" r="1"></circle>
            </svg>
          </button>
          <div class="avatar">
            <img src="../images/test.jpg" alt="Profilkép">
          </div>
          <div class="user-details">
            <div class="user-name">{{ currentChat.name }}</div>
            <div class="user-status">Online</div>
          </div>
        </div>
      </div>
      
      <div class="messages-container" ref="messagesContainer">
        <!-- A template részben javítsuk a Discord-specifikus részt -->
        <!-- Discord üzenetek esetén -->
        <template v-if="currentTheme === 'discord'">
          <div 
            v-for="(message, index) in messages" 
            :key="message.id" 
            class="message discord-style"
            :class="[
              message.sender === 'me' ? 'message-sent' : 'message-received',
              {
                'first-message': index === 0 || messages[index - 1]?.sender !== message.sender
              }
            ]"
          >
            <div class="discord-message-container">
              <div v-if="index === 0 || messages[index - 1]?.sender !== message.sender" class="discord-avatar">
                <div class="avatar-circle">
                  <span>{{ message.sender === 'me' ? 'Y' : currentChat.name.charAt(0) }}</span>
                </div>
              </div>
              <div v-else class="discord-avatar-placeholder"></div>
              
              <div class="discord-message-content">
                <div v-if="index === 0 || messages[index - 1]?.sender !== message.sender" class="discord-username">
                  {{ message.sender === 'me' ? 'You' : currentChat.name }}
                </div>
                
                <div class="discord-message-wrapper">
                  <div class="message-bubble discord-bubble">
                    <!-- Kép és GIF kezelés -->
                    <img v-if="message.type === 'gif' || message.type === 'image'" 
                         :src="message.text" 
                         class="message-gif"
                         @load="console.log('Image loaded successfully:', message.fileName)"
                         @error="(e) => console.error('Image load error:', message.fileName, e)" />
                    
                    <!-- Javított videó kezelés -->
                    <div v-else-if="message.type === 'video'" class="video-container">
                      <video 
                        :src="message.text" 
                        class="message-video" 
                        controls
                        @click.stop
                        ref="videoRef"
                        preload="metadata"
                      ></video>
                      <div class="video-options">
                        <button @click.stop="toggleVideoOptions(message.id)" class="video-options-button">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                          </svg>
                        </button>
                        <div v-if="showVideoOptions[message.id]" class="video-options-menu">
                          <div class="video-option-item" @click.stop="toggleFullscreen($event.target.closest('.video-container').querySelector('video'))">
                            Teljes képernyő
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                            </svg>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Szöveges üzenetek -->
                    <span v-else-if="message.type !== 'audio' && message.type !== 'file'">{{ message.text }}</span>
                    
                    <!-- Hover action tálca közvetlenül a buborékon belül -->
                    <div class="discord-hover-actions">
                      <button class="hover-action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <circle cx="12" cy="12" r="1"></circle>
                          <circle cx="19" cy="12" r="1"></circle>
                          <circle cx="5" cy="12" r="1"></circle>
                        </svg>
                      </button>
                      <button class="hover-action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                      </button>
                      <button class="hover-action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <polyline points="3 6 5 6 21 6"/>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Szöveges üzenetek -->
                <span v-else-if="message.type !== 'audio' && message.type !== 'file'">{{ message.text }}</span>
              </div>
            </div>
          </div>
        </template>
        
        <!-- Regular message layout for other themes -->
       <!-- A template részben javítom a normál üzenet megjelenítést -->
<template v-else>
  <div 
    v-for="(message, index) in messages" 
    :key="message.id" 
    :class="[
      'message', 
      message.sender === 'me' ? 'message-sent' : 'message-received',
      {
        'first-message': index === 0 || messages[index - 1]?.sender !== message.sender
      }
    ]"
  >
    <!-- Hover akciógombok hozzáadása -->
    <div class="message-hover-actions">
      <button class="hover-action-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="12" cy="12" r="1"></circle>
          <circle cx="19" cy="12" r="1"></circle>
          <circle cx="5" cy="12" r="1"></circle>
        </svg>
      </button>
      <button class="hover-action-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
      </button>
      <button class="hover-action-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <polyline points="3 6 5 6 21 6"/>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        </svg>
      </button>
    </div>
    
    <div v-if="index === 0 || messages[index - 1]?.sender !== message.sender" class="message-avatar">
      <div class="avatar-circle">
        <span>{{ currentChat.name.charAt(0) }}</span>
      </div>
    </div>
    <div class="message-bubble">
      <!-- Kép és GIF kezelés -->
      <img v-if="message.type === 'gif' || message.type === 'image'" 
           :src="message.text" 
           class="message-gif"
           @load="console.log('Image loaded successfully:', message.fileName)"
           @error="(e) => console.error('Image load error:', message.fileName, e)" />
      
      <!-- Videó kezelés -->
      <div v-else-if="message.type === 'video'" class="video-container">
        <video 
          :src="message.text" 
          class="message-video" 
          controls
          @click.stop
          ref="videoRef"
          preload="metadata"
        ></video>
        <div class="video-options">
          <button @click.stop="toggleVideoOptions(message.id)" class="video-options-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="1"></circle>
              <circle cx="12" cy="5" r="1"></circle>
              <circle cx="12" cy="19" r="1"></circle>
            </svg>
          </button>
          <div v-if="showVideoOptions[message.id]" class="video-options-menu">
            <div class="video-option-item" @click.stop="toggleFullscreen($event.target.closest('.video-container').querySelector('video'))">
              Teljes képernyő
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
              </svg>
            </div>
            <!-- Szöveges üzenetek -->
            <span v-else-if="message.type !== 'audio' && message.type !== 'file'">{{ message.text }}</span>
          </div>
        </div>
      </template>
    </div>


    <div class="modern-message-box">
      <div class="message-actions left-actions">
        <div class="gif-button-container" style="position: relative;">
          <button class="action-button gif-button" @click.stop="toggleGifPicker">
            <span>GIF</span>
          </button>
          <GifPicker v-if="showGifPicker" :is-open="showGifPicker" @select-gif="handleGifSelect"
            @close="showGifPicker = false" />
        </div>
        <div class="image-upload-container">
          <input type="file" ref="fileInput" @change="handleImageUpload" accept="image/*,video/*"
            style="display: none;" />
          <button class="action-button image-button" @click="$refs.fileInput.click()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
              <circle cx="8.5" cy="8.5" r="1.5" />
              <polyline points="21 15 16 10 5 21" />
            </svg>
          </button>
        </div>
      </div>
      <input v-model="newMessage" type="text" placeholder="Írj üzenetet..." @keyup.enter="submitMessage" />
      <div class="message-actions">
        <button class="action-button send-button" @click="submitMessage">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
        </button>
      </div>
    </div>
    <ChatProfile v-if="showProfile" :currentChat="currentChat" :messages="messages" :currentTheme="currentTheme"
      @change-theme="handleThemeChange" class="chat-profile-sidebar" @update:showProfile="showProfile = $event" />
  </div> <!-- Added closing div for chat-window -->
</template>

<style lang="scss" scoped>
@import '../styles/ChatWindow.scss';
</style>