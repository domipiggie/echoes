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
              <img src=".src/images/test.jpg" alt="Profilkép">
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
        </div>
      </div>
      
      <div class="messages-container" ref="messagesContainer">
        <!-- A template részben javítsuk a Discord-specifikus részt -->
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
                <img :src="message.sender === 'me' ? './src/assets/default-avatar.png' : './src/assets/other-avatar.png'" alt="Profile" />
              </div>
              <div v-else class="discord-avatar-placeholder"></div>
              
              <div class="discord-message-content">
                <div v-if="index === 0 || messages[index - 1]?.sender !== message.sender" class="discord-username">
                  {{ message.sender === 'me' ? 'You' : currentChat.name }}
                </div>
                
                <!-- Javított üzenet buborék -->
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
                </div>
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
    <div v-if="index === 0 || messages[index - 1]?.sender !== message.sender" class="message-avatar">
      <img :src="message.sender === 'me' ? './src/assets/default-avatar.png' : './src/assets/other-avatar.png'" alt="Profile" />
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
          </div>
        </div>
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
            <button 
              class="action-button gif-button"
              @click.stop="toggleGifPicker"
            >
              <span>GIF</span>
            </button>
            <GifPicker
              v-if="showGifPicker"
              :is-open="showGifPicker"
              @select-gif="handleGifSelect"
              @close="showGifPicker = false"
            />
          </div>
          <div class="image-upload-container">
            <input
              type="file"
              ref="fileInput"
              @change="handleImageUpload"
              accept="image/*,video/*"
              style="display: none;"
            />
            <button 
              class="action-button image-button"
              @click="$refs.fileInput.click()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
            </button>
          </div>
        </div>
        <input 
          v-model="newMessage" 
          type="text" 
          placeholder="Írj üzenetet..."
          @keyup.enter="submitMessage"
        />
        <div class="message-actions">
          <button class="action-button send-button" @click="submitMessage">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </div>
      </div>
      <ChatProfile
        v-if="showProfile" 
        :currentChat="currentChat" 
        :messages="messages"  
        :currentTheme="currentTheme"
        @change-theme="handleThemeChange"
        class="chat-profile-sidebar"
        @update:showProfile="showProfile = $event"
      />
    </div>  <!-- Added closing div for chat-window -->
</template>
      
<style scoped>
.chat-window {
	flex: 1;
	display: flex;
	flex-direction: column;
	background-color: #f8fafc;
	position: relative;
	border-radius: 12px;
	overflow: hidden;
	color: #333;
	box-shadow: 0 4px 20px #7078e626;
	height: 100%;
	overflow: visible !important;
}

.chat-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px 16px;
	background-color: #fff;
	border-bottom: 1px solid #7078e633;
	height: 70px;
	box-shadow: 0 2px 10px #7078e61a;
}

.user-info {
	display: flex;
	align-items: center;
	gap: 8px;
}

.user-details {
	display: flex;
	flex-direction: column;
	gap: 2px;
}

.user-name {
	font-size: 16px;
	font-weight: 600;
	color: #484a6a;
	transition: color .2s ease;
}

.user-name:hover {
	color: #7078e6;
}

.user-status {
	font-size: 12px;
	color: #7078e6;
}

.avatar {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	background-color: #e6eaee;
	margin-right: 16px;
	overflow: hidden;
	border: 2px solid #7078e6;
	box-shadow: 0 0 0 2px #7078e633;
	transition: transform .3s ease, box-shadow .3s ease;
}

.avatar:hover {
	transform: scale(1.05);
	box-shadow: 0 0 0 3px #7078e64d;
}

.avatar img, .message-avatar img, .discord-avatar img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.back-button, .more-button {
	background: none;
	border: none;
	cursor: pointer;
	color: #7078e6;
	padding: 4px;
	transition: all .2s ease;
}

.back-button {
	margin-right: 12px;
}

.back-button:hover {
	transform: translateX(-2px);
}

.more-button {
	transition: transform .3s ease;
	padding: 6px;
	border-radius: 50%;
}

.more-button:hover {
	transform: rotate(90deg);
	background-color: #7078e61a;
}

.messages-container {
	flex: 1;
	overflow-y: auto;
	padding: 20px;
	display: flex;
	flex-direction: column;
	background-color: #fff;
	scrollbar-width: thin;
	scrollbar-color: #7078e6 #f0f0f0;
}

.chat-window.discord .messages-container {
	padding: 16px;
}

.message {
	max-width: 75%;
	margin-bottom: 8px;
	display: flex;
	align-items: flex-start;
	position: relative;
}

.message:not(.first-message) {
	margin-bottom: 4px;
}

.message-sent, .message-received {
	align-self: flex-end;
	flex-direction: row-reverse;
}

.message-received {
	align-self: flex-start;
	flex-direction: row;
}

.message-sent {
	margin-right: 36px;
}

.message-received {
	margin-left: 36px;
}

.first-message {
	margin-top: 8px;
}

.first-message.message-sent {
	margin-right: 36px;
}

.first-message.message-received {
	margin-left: 36px;
}

.message-avatar {
	width: 28px;
	height: 28px;
	border-radius: 50%;
	overflow: hidden;
	flex-shrink: 0;
	border: 1px solid #7078e6;
	box-shadow: 0 1px 3px #7078e633;
	position: absolute;
	left: -36px;
}

.message-sent .message-avatar {
	left: auto;
	right: -36px;
}

.message-bubble {
	padding: 14px 18px;
	border-radius: 18px;
	word-break: break-word;
	box-shadow: 0 2px 5px #0000000d;
	font-size: 14px;
	line-height: 1.5;
	transition: transform .2s ease;
	background-color: #f0f2ff;
	border-radius: 18px;
	word-break: break-word;
	box-shadow: 0 2px 5px #0000000d;
	font-size: 15px;
	color: #484a6a;
	min-width: 40px;
}

.message-sent .message-bubble {
	background-color: #7078e6;
	color: #fff;
	border-bottom-right-radius: 4px;
}

.message-received .message-bubble {
	background-color: #f0f2ff;
	color: #484a6a;
	border-bottom-left-radius: 4px;
}

.message-hover-actions {
	position: absolute;
	display: flex;
	gap: 8px;
	opacity: 0;
	transition: all .2s ease;
	z-index: 100;
	right: calc(100% + 16px);
	top: 50%;
	transform: translateY(-50%);
}

.message-received .message-hover-actions {
	left: calc(100% + 16px);
	right: auto;
}

.message:hover .message-hover-actions {
	opacity: 1;
}

.hover-action-btn {
	width: 32px;
	height: 32px;
	border-radius: 50%;
	border: none;
	background: #7078e6;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	transition: all .2s ease;
	padding: 0;
	box-shadow: 0 2px 6px #7078e633;
}

.hover-action-btn svg {
	stroke: #fff;
	transition: all .2s ease;
}

.hover-action-btn:hover {
	transform: scale(1.1);
	background: #5a61d2;
	box-shadow: 0 4px 12px #7078e64d;
}

.hover-action-btn:hover svg {
	stroke-width: 2.2;
}

.message-options-dropdown {
	position: absolute;
	top: -40px;
	right: 0;
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 8px #00000026;
	padding: 8px 0;
	z-index: 1000;
}

.message-option {
	padding: 8px 16px;
	color: #484a6a;
	cursor: pointer;
	display: flex;
	align-items: center;
	gap: 8px;
	transition: background-color .2s;
}

.message-option:hover {
	background-color: #f0f4ff;
}

.message-option.delete {
	color: #ff4757;
}

.message-gif {
	max-width: 300px;
	max-height: 300px;
	border-radius: 8px;
	object-fit: contain;
	margin: 4px 0;
	display: block;
}

.message-video {
	max-width: 300px;
	max-height: 400px;
	border-radius: 12px;
	object-fit: contain;
	margin: 2px 0;
	background: #000;
	box-shadow: 0 2px 8px #7078e626;
	border: 1px solid #7078e64d;
	display: block;
}

.message-video::-webkit-media-controls-panel {
	display: flex;
	justify-content: space-between;
	align-items: center;
	background: linear-gradient(to top,#000000b3,transparent);
	padding: 8px;
}

.message-video::-webkit-media-controls-play-button {
	display: block;
	opacity: .9;
}

.message-video::-webkit-media-controls-timeline, .message-video::-webkit-media-controls-current-time-display, .message-video::-webkit-media-controls-time-remaining-display, .message-video::-webkit-media-controls-mute-button, .message-video::-webkit-media-controls-volume-slider, .message-video::-webkit-media-controls-fullscreen-button {
	display: none;
}

.video-container {
	position: relative;
	display: inline-block;
	border-radius: 8px;
	overflow: hidden;
	margin: 4px 0;
	background: #000;
	max-width: 300px;
}

.video-options {
	position: absolute;
	top: 8px;
	right: 8px;
	z-index: 10;
}

.video-options-button {
	background: #0009;
	border: none;
	border-radius: 50%;
	width: 28px;
	height: 28px;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	color: #fff;
	transition: background .2s ease;
}

.video-options-button:hover {
	background: #000c;
}

.video-options-button svg {
	width: 16px;
	height: 16px;
	stroke: #fff;
	stroke-width: 2.5;
}

.video-options-menu {
	position: absolute;
	right: 8px;
	bottom: 40px;
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 8px #0003;
	z-index: 1000;
}

.video-option-item {
	padding: 8px 16px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 8px;
	cursor: pointer;
	color: #333;
	transition: background-color .2s;
}

.video-option-item:hover {
	background-color: #f0f4ff;
}

.video-option-item svg {
	width: 16px;
	height: 16px;
	margin-left: 8px;
}

.modern-message-box {
	display: flex;
	align-items: center;
	background: #fff;
	border-radius: 24px;
	padding: 4px 8px;
	border: 1px solid #7078e61a;
	box-shadow: 0 2px 8px #0000000d;
	margin-bottom: 16px;
	margin-left: 16px;
	margin-right: 16px;
}

.left-actions {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-right: 8px;
	margin-left: 4px;
}

.message-actions {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-right: 4px;
}

input {
	flex: 1;
	border: none;
	outline: none;
	padding: 12px 16px;
	font-size: 15px;
	color: #2d3748;
	background: transparent;
}

input::placeholder {
	color: #a0aec0;
}

.action-button {
	border: none;
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all .2s ease;
	background-color: #7078e6;
	color: #fff;
	border-radius: 50%;
	width: 36px;
	height: 36px;
	padding: 0;
}

.action-button:hover {
	background-color: #5a61d2;
	transform: translateY(-1px);
}

.gif-button {
	background-color: #7078e6;
	color: #fff;
	font-weight: 600;
	font-size: 13px;
	border-radius: 16px;
	padding: 6px 12px;
	border: none;
	transition: all .2s ease;
}

.gif-button:hover {
	background-color: #5a61d2;
	transform: translateY(-1px);
}

.discord-style {
	width: 100%;
	max-width: 100% !important;
	margin-left: 0 !important;
	margin-right: 0 !important;
	align-self: flex-start !important;
}

.discord-bubble {
	background-color: #f0f2ff !important;
	color: #484a6a !important;
	border-radius: 8px !important;
	box-shadow: 0 1px 3px #0000001a !important;
	padding: 10px 14px !important;
}

.message-sent.discord-style .discord-bubble {
	background-color: #7078e6 !important;
	color: #fff !important;
}

.discord-message-container {
	display: flex;
	width: 100%;
	margin-bottom: 2px;
	padding: 2px 0;
}

.discord-avatar {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	margin-right: 16px;
	flex-shrink: 0;
}

.discord-avatar-placeholder {
	width: 40px;
	margin-right: 16px;
	flex-shrink: 0;
}

.discord-message-content {
	display: flex;
	flex-direction: column;
	flex: 1;
}

.discord-username {
	font-size: 14px;
	font-weight: 500;
	color: #484a6a;
	margin-bottom: 4px;
}

.discord-style .video-container {
	position: relative;
	display: inline-block;
	border-radius: 8px;
	overflow: hidden;
	margin: 0;
	max-width: 300px;
	background: #000;
	box-shadow: 0 2px 4px #0003;
}

.discord-style .message-video {
	width: 100%;
	max-width: 300px;
	max-height: 400px;
	border-radius: 0;
	object-fit: contain;
	margin: 0;
	background: #000;
	box-shadow: none;
	border: none;
	display: block;
}

.discord-style .video-options {
	position: absolute;
	top: 8px;
	right: 8px;
	z-index: 100;
}

.discord-style .video-options-button {
	background: #000000b3;
	border: none;
	border-radius: 50%;
	width: 32px;
	height: 32px;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	color: #fff;
	transition: background .2s ease;
	box-shadow: 0 2px 4px #0000004d;
}

.discord-style .video-options-button:hover {
	background: #000000e6;
	transform: scale(1.05);
}

.discord-style .video-options-button svg {
	width: 18px;
	height: 18px;
	stroke: #fff;
	stroke-width: 2;
}

.video-options-menu {
	position: absolute;
	right: 8px;
	top: 40px;
	background: #fff;
	border-radius: 8px;
	box-shadow: 0 2px 8px #0003;
	z-index: 1000;
	min-width: 150px;
}

@media (min-resolution: 192dpi) {
	.message-hover-actions {
		flex-direction: column;
	}

	.message-sent .message-hover-actions {
		right: calc(100% + 8px);
	}

	.message-received .message-hover-actions {
		left: calc(100% + 8px);
	}
}

@keyframes fadeIn {
	from {
		opacity: 0;
		transform: translateY(10px);
	}

	to {
		opacity: 1;
		transform: translateY(0);
	}
}
</style>