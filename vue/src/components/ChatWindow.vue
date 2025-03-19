<script setup>
  import { defineProps, defineEmits, ref, watch, nextTick, onMounted } from 'vue';
  import ChatProfile from './ChatProfile.vue';
  import GifPicker from './GifPicker.vue'; // Győződj meg róla, hogy ez az import létezik
  
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
  
  onMounted(() => {
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
    <div class="chat-window container-fluid p-0">
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
        <div 
          v-for="message in messages" 
          :key="message.id" 
          :class="['message', message.sender === 'me' ? 'message-sent' : 'message-received']"
        >
          <div class="message-bubble">
            <img v-if="message.type === 'gif' || message.type === 'image'" 
                 :src="message.text" 
                 class="message-gif"
                 @load="console.log('Image loaded successfully:', message.fileName)"
                 @error="(e) => console.error('Image load error:', message.fileName, e)" />
            <video v-else-if="message.type === 'video'" 
                   :src="message.text" 
                   :ref="el => { if (el) el.id = `video-${message.id}` }"
                   controls 
                   class="message-video">
              <div class="video-controls">
                <button class="video-control-btn play-btn" @click="$refs[`video-${message.id}`].play()">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </button>
                <button class="video-control-btn options-btn" @click.stop="toggleVideoOptions(message.id)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white">
                    <circle cx="12" cy="12" r="2"/>
                    <circle cx="19" cy="12" r="2"/>
                    <circle cx="5" cy="12" r="2"/>
                  </svg>
                </button>
              </div>
              <div v-if="showVideoOptions[message.id]" class="video-options-menu">
                <div class="video-option-item" @click="toggleMute($refs[`video-${message.id}`], message.id)">
                  <span>{{ videoStates[message.id]?.muted ? 'Némítás feloldása' : 'Némítás' }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 5L6 9H2v6h4l5 4V5z"/>
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
                  </svg>
                </div>
                <div class="video-option-item">
                  <span>Letöltés</span>
                </div>
                <div class="video-option-item">
                  <span>Lejátszási sebesség</span>
                </div>
                <div class="video-option-item">
                  <span>Kép a képben</span>
                </div>
              </div>
            </video>
            <audio v-else-if="message.type === 'audio'"
                   :src="message.text"
                   controls
                   class="message-audio"></audio>
            <div v-else-if="message.type === 'file'" class="file-message">
              <a :href="message.text" :download="message.fileName" class="file-download">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                  <polyline points="13 2 13 9 20 9"></polyline>
                </svg>
                <div class="file-info">
                  <span class="file-name">{{ message.fileName }}</span>
                  <span class="file-size">{{ (message.fileSize / 1024).toFixed(1) }} KB</span>
                </div>
              </a>
            </div>
            <span v-else>{{ message.text }}</span>
          </div>
          <div class="message-hover-actions">
            <button class="hover-action-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
              </svg>
            </button>
            <button class="hover-action-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 17 4 12 9 7"></polyline>
                <path d="M20 18v-2a4 4 0 0 0-4-4H4"></path>
              </svg>
            </button>
            <button class="hover-action-btn">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="1"></circle>
                <circle cx="12" cy="5" r="1"></circle>
                <circle cx="12" cy="19" r="1"></circle>
              </svg>
            </button>
          </div>
        </div>
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
        class="chat-profile-sidebar"
        @update:showProfile="showProfile = $event"
      />
    </div>  <!-- Added closing div for chat-window -->
</template>
      
    <style scoped>
    .message-gif {
      max-width: 300px;
      max-height: 300px;
      border-radius: 8px;
      object-fit: contain;
      margin: 4px 0;
    }
    
    .chat-window {
      overflow: visible !important;
    }
    
    .modern-message-box {
      display: flex;
      align-items: center;
      background: #ffffff;
      border-radius: 24px;
      padding: 4px 8px 4px 8px;
      border: 1px solid rgba(112, 120, 230, 0.1);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      margin-bottom: 16px;  /* Add margin to move it up */
      margin-left: 16px;    /* Add some side margins */
      margin-right: 16px;
    }
    
    .messages-container {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      padding-bottom: 8px;  /* Reduce bottom padding */
      display: flex;
      flex-direction: column;
      background-color: #ffffff;
      scrollbar-width: thin;
      scrollbar-color: #7078e6 #f0f0f0;
    }
    
    .left-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-right: 8px;
      margin-left: 4px;
    }
    
    .gif-button {
      background-color: #7078e6;
      color: white;
      font-weight: 600;
      font-size: 13px;
      border-radius: 16px;
      padding: 6px 12px;
      border: none;
      transition: all 0.2s ease;
    }
    
    .gif-button:hover {
      background-color: #5a61d2;
      transform: translateY(-1px);
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
    
    .message-actions {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-right: 4px;
    }
    
    .action-button {
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      background-color: #7078e6;
      color: white;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      padding: 0;
    }
    
    .action-button:hover {
      background-color: #5a61d2;
      transform: translateY(-1px);
    }
    
    /* Alapvető stílusok */
    .chat-window {
      flex: 1;
      display: flex;
      flex-direction: column;
      background-color: #f8fafc;
      position: relative;
      border-radius: 12px;
      overflow: hidden;
      color: #333;
      box-shadow: 0 4px 20px rgba(112, 120, 230, 0.15);
      height: 100%;
    }
    
    .chat-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
      background-color: #ffffff;
      border-bottom: 1px solid rgba(112, 120, 230, 0.2);
      height: 70px;
      box-shadow: 0 2px 10px rgba(112, 120, 230, 0.1);
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
    }
    
    .user-status {
      font-size: 12px;
      color: #7078e6;
    }
    
    .back-button {
      background: none;
      border: none;
      cursor: pointer;
      color: #7078e6;
      margin-right: 12px;
      padding: 4px;
      transition: transform 0.2s ease;
    }
    
    .back-button:hover {
      transform: translateX(-2px);
    }
    
    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #e6eaee;
      margin-right: 16px;
      overflow: hidden;
      border: 2px solid #7078e6;
      box-shadow: 0 0 0 2px rgba(112, 120, 230, 0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .avatar:hover {
      transform: scale(1.05);
      box-shadow: 0 0 0 3px rgba(112, 120, 230, 0.3);
    }
    
    .avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .user-name {
      font-size: 16px;
      font-weight: 600;
      color: #484a6a;
      transition: color 0.2s ease;
    }
    
    .user-name:hover {
      color: #7078e6;
    }
    
    .more-button {
      background: none;
      border: none;
      cursor: pointer;
      color: #7078e6;
      transition: transform 0.3s ease;
      padding: 6px;
      border-radius: 50%;
    }
    
    .more-button:hover {
      transform: rotate(90deg);
      background-color: rgba(112, 120, 230, 0.1);
    }
    
    .messages-container {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
      display: flex;
      flex-direction: column;
      background-color: #ffffff;
      scrollbar-width: thin;
      scrollbar-color: #7078e6 #f0f0f0;
    }
    
    .message {
      max-width: 75%;
      margin-bottom: 3px;
      display: flex;
      animation: fadeIn 0.3s ease-out;
      position: relative;
    }
    
    /* Remove all other margin styles */
    .message-sent + .message-sent,
    .message-received + .message-received,
    .message-sent + .message-received,
    .message-received + .message-sent {
      margin-bottom: 3px;
    }
    
    /* Remove this style as we want consistent 3px spacing */
    /* .message-sent + .message-received,
    .message-received + .message-sent {
      margin-bottom: 12px;
    } */
    
    .message-bubble {
      padding: 12px 16px;
      border-radius: 18px;
      word-break: break-word;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      font-size: 14px;
      line-height: 1.5;
      transition: transform 0.2s ease;
      background-color: #7078e6;
    }
    
    .message-hover-actions {
      position: absolute;
      display: flex;
      gap: 8px;
      opacity: 0;
      transition: all 0.2s ease;
      z-index: 100;
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
      transition: all 0.2s ease;
      padding: 0;
      box-shadow: 0 2px 6px rgba(112, 120, 230, 0.2);
    }
    
    .hover-action-btn svg {
      stroke: white;
      transition: all 0.2s ease;
    }
    
    .hover-action-btn:hover {
      transform: scale(1.1);
      background: #5a61d2;
      box-shadow: 0 4px 12px rgba(112, 120, 230, 0.3);
    }
    
    .hover-action-btn:hover svg {
      stroke-width: 2.2;
    }
    
    .message:hover .message-hover-actions {
      opacity: 1;
    }
    
    .message-sent .message-hover-actions {
      right: calc(100% + 16px);
      top: 50%;
      transform: translateY(-50%);
    }
    
    .message-received .message-hover-actions {
      left: calc(100% + 16px);
      top: 50%;
      transform: translateY(-50%);
    }
    
    .message-options-dropdown {
      position: absolute;
      top: -40px;
      right: 0;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
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
      transition: background-color 0.2s;
    }
    
    .message-option:hover {
      background-color: #f0f4ff;
    }
    
    .message-option.delete {
      color: #ff4757;
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
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      font-size: 14px;
      line-height: 1.5;
      transition: transform 0.2s ease;
      background-color: #7078e6;
    }
    
    .message-sent .message-bubble {
      background-color: #7078e6;
      color: white;
      border-bottom-right-radius: 4px;
    }
    
    .message-video {
      max-width: 300px;
      max-height: 300px;
      border-radius: 12px;
      object-fit: cover;
      margin: 2px 0;
      background: #000;
      box-shadow: 0 2px 8px rgba(112, 120, 230, 0.15);
      border: 1px solid rgba(112, 120, 230, 0.3);
    }
    
    /* Custom video controls */
    .message-video::-webkit-media-controls-panel {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
      padding: 8px;
    }
    
    .message-video::-webkit-media-controls-play-button {
      display: block;
    }
    
    .message-video::-webkit-media-controls-timeline,
    .message-video::-webkit-media-controls-current-time-display,
    .message-video::-webkit-media-controls-time-remaining-display,
    .message-video::-webkit-media-controls-mute-button,
    .message-video::-webkit-media-controls-volume-slider,
    .message-video::-webkit-media-controls-fullscreen-button {
      display: none;
    }
    
    /* Video options menu */
    .video-options-menu {
      position: absolute;
      right: 8px;
      bottom: 40px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      z-index: 1000;
    }
    
    .video-option-item {
      padding: 8px 16px;
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      color: #333;
      transition: background-color 0.2s;
    }
    
    .video-option-item:hover {
      background-color: #f0f4ff;
    }
    
    .video-option-item svg {
      width: 16px;
      height: 16px;
      margin-left: 8px;
    }
    
    .video-option-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 16px;
      cursor: pointer;
      color: #333;
      transition: background-color 0.2s;
    }
    </style>