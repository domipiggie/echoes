<script setup>
import { ref } from 'vue';
import GifPicker from '../GifPicker.vue';

const props = defineProps({
  replyingTo: Object,
  editingMessage: Object
});

const emit = defineEmits(['send-message', 'cancel-reply', 'cancel-editing', 'send-file']);

const newMessage = ref('');
const showGifPicker = ref(false);
const fileInput = ref(null);

const submitMessage = () => {
  if (newMessage.value.trim()) {
    if (props.editingMessage) {
      emit('save-edit', newMessage.value);
      return;
    }
    
    const messageToSend = {
      id: Date.now(),
      text: newMessage.value,
      type: 'text',
      sender: 'me',
      timestamp: new Date().toISOString()
    };
    
    if (props.replyingTo) {
      messageToSend.replyTo = props.replyingTo.id;
      messageToSend.replyToText = props.replyingTo.text;
      messageToSend.replyToSender = props.replyingTo.sender;
      messageToSend.replyToType = props.replyingTo.type || 'text';
    }
    
    emit('send-message', messageToSend);
    newMessage.value = '';
  }
};

const toggleGifPicker = (event) => {
  event.stopPropagation();
  showGifPicker.value = !showGifPicker.value;
};

const handleGifSelect = (gifUrl) => {
  emit('send-message', { text: gifUrl, type: 'gif' });
  showGifPicker.value = false;
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (!file || !(file.type.startsWith('image/') || file.type.startsWith('video/'))) return;
  
  if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
    emit('send-file', {
      file: file,
      channelID: props.currentChat?.channelID
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

    emit('send-message', message);
  };

  reader.onerror = (error) => {
    console.error('Error reading file:', error);
  };

  reader.readAsDataURL(file);
  event.target.value = '';
};
</script>

<template>
  <div class="input-area">
    <!-- Reply box -->
    <div class="reply-box" v-if="replyingTo">
      <div class="reply-box-content">
        <div class="reply-info">
          <span class="reply-to">Válasz erre:</span>
          <span class="reply-text">{{ replyingTo.text }}</span>
        </div>
        <button class="cancel-reply-btn" @click="$emit('cancel-reply')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Edit box -->
    <div class="edit-box" v-if="editingMessage">
      <div class="edit-box-content">
        <div class="edit-info">
          <span class="edit-label">Üzenet szerkesztése:</span>
        </div>
        <button class="cancel-edit-btn" @click="$emit('cancel-editing')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Message input -->
    <div class="modern-message-box">
      <button class="attachment-button" @click="fileInput.click()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
        </svg>
      </button>
      <input type="file" ref="fileInput" @change="handleImageUpload" accept="image/*,video/*" style="display: none;" />
      
      <input 
        type="text" 
        v-model="newMessage" 
        placeholder="Írj egy üzenetet..." 
        @keyup.enter="submitMessage"
      />
      
      <button class="gif-button" @click="toggleGifPicker">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
          <line x1="8" y1="21" x2="16" y2="21"></line>
          <line x1="12" y1="17" x2="12" y2="21"></line>
        </svg>
      </button>
      
      <button class="send-button" @click="submitMessage">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </button>
    </div>
    
    <!-- GIF picker -->
    <GifPicker 
      v-if="showGifPicker" 
      @select="handleGifSelect" 
      @close="showGifPicker = false"
    />
  </div>
</template>

<style lang="scss" scoped>
@import '../../styles/chat/MessageInput.scss';
</style>