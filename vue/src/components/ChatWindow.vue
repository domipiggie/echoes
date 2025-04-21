<script setup>
  import { ref, watch, nextTick, onMounted } from 'vue';
  import ChatProfile from './ChatProfile.vue';
  import GifPicker from './GifPicker.vue';
  import { userdataStore } from '../store/UserdataStore';
  import { useMessageStore } from '../store/MessageStore';
  
  const userStore = userdataStore();
  const messageStore = useMessageStore();
  
  const emit = defineEmits(['send-message', 'go-back', 'update-message', 'send-file']);
  
  const newMessage = ref('');
  const messagesContainer = ref(null);
  const isMobile = ref(false);
  const showProfile = ref(false);
  const showGifPicker = ref(false);
  const gifButtonRef = ref(null);
  
  const replyingTo = ref(null);
  const editingMessage = ref(null);
  
  const startReply = (message) => {
    replyingTo.value = {
      id: message.id,
      text: message.text,
      sender: message.sender,
      type: message.type
    };
    
    nextTick(() => {
      document.querySelector('.modern-message-box input').focus();
    });
  };
  
  const cancelReply = () => {
    replyingTo.value = null;
  };
  
  const startEditing = (message) => {
    console.log('Szerkesztés indítása:', message);
    if (message.sender === 'me' && !message.isRevoked) {
      editingMessage.value = message;
      newMessage.value = message.text;
      
      nextTick(() => {
        document.querySelector('.modern-message-box input').focus();
      });
    }
  };
  
  const cancelEditing = () => {
    editingMessage.value = null;
    newMessage.value = '';
  };
  
  const saveEdit = () => {
    if (editingMessage.value && newMessage.value.trim()) {
      const messageIndex = props.messages.findIndex(msg => msg.id === editingMessage.value.id);
      if (messageIndex !== -1) {
        props.messages[messageIndex].text = newMessage.value;
        
        emit('update-message', props.messages[messageIndex]);
        
        console.log('Üzenet szerkesztve:', props.messages[messageIndex]);
      }
      
      cancelEditing();
    }
  };
  
  const submitMessage = () => {
    if (newMessage.value.trim()) {
      if (editingMessage.value) {
        saveEdit();
        return;
      }
      
      const messageToSend = {
        id: Date.now(),
        text: newMessage.value,
        type: 'text',
        sender: 'me',
        timestamp: new Date().toISOString()
      };
      
      if (replyingTo.value) {
        messageToSend.replyTo = replyingTo.value.id;
        messageToSend.replyToText = replyingTo.value.text;
        messageToSend.replyToSender = replyingTo.value.sender;
        messageToSend.replyToType = replyingTo.value.type || 'text';
        
        replyingTo.value = null;
      }
      
      emit('send-message', messageToSend);
      newMessage.value = '';
    }
  };
  
  onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
    scrollToBottom();
  });
  
  const checkScreenSize = () => {
    isMobile.value = window.innerWidth <= 768;
  };
  
  const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  };
  
  watch(() => messageStore.getMessages, scrollToBottom);
  
  const toggleGifPicker = (event) => {
    event.stopPropagation();
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
    
    if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
      emit('send-file', {
        file: file,
        channelID: props.currentChat.channelID
      });
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
      const message = {
        id: Date.now(),
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

  const deleteMessage = (messageId) => {
    if (confirm("Biztos vissza akarja vonni ezt az üzenetet?")) {
      const messageIndex = props.messages.findIndex(msg => msg.id === messageId);
      if (messageIndex !== -1) {
        props.messages[messageIndex].text = "visszavont üzenet";
        props.messages[messageIndex].isRevoked = true;
        
        emit('update-message', props.messages[messageIndex]);
        
        console.log('Üzenet visszavonva:', props.messages[messageIndex]);
      }
    }
  };
</script>

<template>
  <div class="chat-window container-fluid p-0" :class="userStore.getCurrentTheme()">
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
              <span>{{ messageStore.getCurrentChannelName.charAt(0) }}</span>
            </div>
          </div>
          <div class="user-details">
            <div class="user-name">{{ messageStore.getCurrentChannelName }}</div>
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
      <!-- Discord üzenetek esetén -->
      <template v-if="userStore.getCurrentTheme() === 'discord'">
        <div 
          v-for="(message, index) in messageStore.getMessages" 
          :key="message.getMessageID()" 
          class="message discord-style"
          :class="[
            message.getUser().getUserID() == userStore.getUserID() ? 'message-sent' : 'message-received',
            {
              'first-message': index === 0 || messageStore.getMessages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()
            }
          ]"
        >
          <div class="discord-message-container">
            <div v-if="index === 0 || messageStore.getMessages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()" class="discord-avatar">
              <div class="avatar-circle">
                <span>{{ message.getUser().getUserID() == userStore.getUserID() ? 'Y' : messageStore.getCurrentChannelName.charAt(0) }}</span>
              </div>
            </div>
            <div v-else class="discord-avatar-placeholder"></div>
            
            <div class="discord-message-content">
              <div v-if="index === 0 || messageStore.getMessages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()" class="discord-username">
                {{ message.getUser().getUserID() == userStore.getUserID() ? 'You' : messageStore.getCurrentChannelName.charAt(0) }}
              </div>
              
              <div class="discord-message-wrapper">
                <div class="message-bubble discord-bubble">
                  <!-- Kép és GIF kezelés -->
                  <img v-if="message.getType() === 'gif' || message.getType() === 'image'" 
                       :src="message.getContent()" 
                       class="message-gif"
                       @load="console.log('Image loaded successfully:', message.fileName)"
                       @error="(e) => console.error('Image load error:', message.fileName, e)" />
                  
                  <!-- Javított videó kezelés -->
                  <div v-else-if="message.getType() === 'video'" class="video-container">
                    <video 
                      :src="message.getContent()" 
                      class="message-video" 
                      controls
                      @click.stop
                      ref="videoRef"
                      preload="metadata"
                    ></video>
                    <div class="video-options">
                      <button @click.stop="toggleVideoOptions(message.getMessageID())" class="video-options-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <circle cx="12" cy="12" r="1"></circle>
                          <circle cx="12" cy="5" r="1"></circle>
                          <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                      </button>
                      <div v-if="showVideoOptions[message.getMessageID()]" class="video-options-menu">
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
                  <span v-else-if="message.getType() !== 'audio' && message.getType() !== 'file'" 
                        :class="{ 'revoked-message': message.isRevoked }">
                      {{ message.getContent() }}
                    </span>
                  
                  <!-- Hover action tálca közvetlenül a buborékon belül -->
                  <!-- Discord hover actions módosítása -->
                  <div class="discord-hover-actions">
                    <!-- Ceruza ikon a három pont helyett, csak saját üzeneteknél -->
                    <button v-if="message.getUser().getUserID() == userStore.getUserID() && !message.isRevoked" class="hover-action-btn" @click="startEditing(message)">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                    </button>
                    <button class="hover-action-btn" @click="startReply(message)">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                      </svg>
                    </button>
                    <button class="hover-action-btn" @click="deleteMessage(message.getMessageID())">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
      
      <!-- Regular message layout for other themes -->
      <template v-else>
        <div 
          v-for="(message, index) in messageStore.getMessages" 
          :key="message.getMessageID()" 
          :class="[
            'message', 
            message.getUser().getUserID() == userStore.getUserID() ? 'message-sent' : 'message-received',
            {
              'first-message': index === 0 || messageStore.getMessages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()
            }
          ]"
        >
          <!-- Hover akciógombok hozzáadása -->
          <!-- Normál üzenetek hover actions módosítása -->
          <div class="message-hover-actions">
            <!-- Három pont gomb csak akkor, ha nem a saját üzenetünk -->
            <button v-if="message.getUser().getUserID() != userStore.getUserID()" class="hover-action-btn">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="1"></circle>
                <circle cx="19" cy="12" r="1"></circle>
                <circle cx="5" cy="12" r="1"></circle>
              </svg>
            </button>
            <!-- Ceruza ikon csak a saját üzeneteknél -->
            <button v-if="message.getUser().getUserID() == userStore.getUserID() && !message.isRevoked" class="hover-action-btn" @click="startEditing(message)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
              </svg>
            </button>
            <button class="hover-action-btn" @click="startReply(message)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
              </svg>
            </button>
            <button class="hover-action-btn" @click="deleteMessage(message.getMessageID())">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
              </svg>
            </button>
          </div>
          
          <div v-if="index === 0 || messageStore.getMessages[index - 1]?.getUser().getUserID() !== message.getUser().getUserID()" class="message-avatar">
            <div class="avatar-circle">
              <span>{{ messageStore.getCurrentChannelName.charAt(0) }}</span>
            </div>
          </div>
          <div class="message-bubble" :class="{ 'has-reply': message.replyTo }">
            <!-- Válasz megjelenítése, ha van -->
            <div v-if="message.replyTo" class="messenger-reply">
              <div class="reply-indicator-content">
                <div class="reply-indicator-name">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="reply-icon">
                    <path d="M7 17l-4-4 4-4"/>
                  </svg>
                  {{ message.replyToSender === 'me' ? 'Saját üzeneted' : messageStore.getCurrentChannelName }}
                </div>
                <div class="reply-indicator-text">{{ message.replyToText }}</div>
              </div>
            </div>
            
            <!-- Üzenet tartalma -->
            <img v-if="message.getType() === 'image'" :src="message.getContent()" class="message-image" />
            <img v-else-if="message.getType() === 'gif'" :src="message.getContent()" class="message-gif" />
            <div v-else-if="message.getType() === 'video'" class="video-container">
              <video 
                :src="message.getContent()" 
                class="message-video" 
                controls
                @click.stop
                ref="videoRef"
                preload="metadata"
              ></video>
              <div class="video-options">
                <button @click.stop="toggleVideoOptions(message.getMessageID())" class="video-options-button">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="12" cy="5" r="1"></circle>
                    <circle cx="12" cy="19" r="1"></circle>
                  </svg>
                </button>
                <div v-if="showVideoOptions[message.getMessageID()]" class="video-options-menu">
                  <div class="video-option-item" @click.stop="toggleFullscreen($event.target.closest('.video-container').querySelector('video'))">
                    Teljes képernyő
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                    </svg>
                  </div>
                </div>
              </div>
            </div>
            <!-- Normál üzeneteknél is frissítsük a visszavont üzenet megjelenítését -->
            <span v-else-if="message.getType() !== 'audio' && message.getType() !== 'file'" 
                  :class="{ 'revoked-message': message.isRevoked }">
                {{ message.getContent() }}
            </span>
          </div>
        </div>
      </template>
    </div>
    
    <!-- Input area for sending new messages -->
    <div class="input-area">
      <!-- Add reply container above the input field -->
      <div class="reply-box" v-if="replyingTo">
        <div class="reply-box-content">
          <div class="reply-box-header">
            <div class="reply-box-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="reply-icon">
                <path d="M7 17l-4-4 4-4"/>
              </svg>
              Válasz {{ replyingTo.sender === 'me' ? 'saját üzenetedre' : messageStore.getCurrentChannelName + ' üzenetére' }}
            </div>
          </div>
          <div class="reply-box-text">{{ replyingTo.text }}</div>
        </div>
        <button class="reply-box-close" @click="cancelReply">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
    
      <!-- Add edit container above the input field -->
      <div class="reply-box edit-box" v-if="editingMessage && !replyingTo">
        <div class="reply-box-content">
          <div class="reply-box-header">
            <div class="reply-box-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="edit-icon">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
              </svg>
              Üzenet módosítása
            </div>
          </div>
          <div class="reply-box-text">{{ editingMessage.text }}</div>
        </div>
        <button class="reply-box-close" @click="cancelEditing">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
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
    </div>
    
    <ChatProfile
      v-if="showProfile"
      class="chat-profile-sidebar"
      @update:showProfile="showProfile = $event"
    />
  </div>
</template>

<style lang="scss" scoped>
@import '../styles/ChatWindow.scss';

// Visszavont üzenet stílusa
.revoked-message {
  font-style: italic;
  color: #dbd9d9;
  text-decoration: line-through;
  position: relative;
 
  &::before {
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
  }
}

// Discord stílusú visszavont üzenet
.discord-bubble .revoked-message {
  color: #a0a0a0;
  background-color: rgba(255, 0, 0, 0.05);
  padding: 2px 5px;
  border-radius: 3px;
  border-left: 3px solid #ff6b6b;
}

// Szerkesztési box stílusa
.edit-box {
  background-color: rgba(0, 123, 255, 0.1);
  border-left: 3px solid #007bff;
  
  .edit-icon {
    color: #007bff;
  }
  
  .reply-box-title {
    color: #007bff;
  }
}


.edit-message-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.edit-message-container {
  background-color: white;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.edit-message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #e0e0e0;
  
  h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
  }
  
  .close-button {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #666;
    
    &:hover {
      color: #333;
    }
  }
}

.edit-message-body {
  padding: 16px;
  
  .edit-message-textarea {
    width: 100%;
    min-height: 100px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
    font-family: inherit;
    font-size: 14px;
    
    &:focus {
      outline: none;
      border-color: #007bff;
    }
  }
}

.edit-message-footer {
  display: flex;
  justify-content: flex-end;
  padding: 12px 16px;
  border-top: 1px solid #e0e0e0;
  gap: 8px;
  
  button {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
  }
  
  .cancel-button {
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    color: #333;
    
    &:hover {
      background-color: #e0e0e0;
    }
  }
  
  .save-button {
    background-color: #007bff;
    border: 1px solid #007bff;
    color: white;
    
    &:hover {
      background-color: #0069d9;
    }
  }
}

</style>