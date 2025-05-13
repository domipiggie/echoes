<script setup>
import { ref, watch } from 'vue';
import GifPicker from '../GifPicker.vue';

const props = defineProps({
  replyingTo: Object,
  editingMessage: Object
});

const emit = defineEmits(['send-message', 'cancel-reply', 'cancel-editing', 'send-file', 'save-edit']);

const newMessage = ref('');
const showGifPicker = ref(false);
const fileInput = ref(null);

// Ha szerkesztési módban vagyunk, akkor az üzenet tartalmát betöltjük az input mezőbe
watch(() => props.editingMessage, (newVal, oldVal) => {
  console.log('Szerkesztési állapot változás:', { newVal, oldVal });
  if (newVal) {
    newMessage.value = newVal.text || '';
  } else if (!newVal && oldVal) {
    newMessage.value = '';
  }
}, { immediate: true });

const submitMessage = () => {
  if (newMessage.value.trim()) {
    if (props.editingMessage) {
      emit('save-edit', newMessage.value);
      newMessage.value = '';
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
  const messageToSend = {
      id: Date.now(),
      text: gifUrl,
      type: 'gif',
      sender: 'me',
      timestamp: new Date().toISOString()
    };
  emit('send-message', messageToSend);
  showGifPicker.value = false;
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  let type = "";
  if (file.type.startsWith('image/')) {
    type = 'image';
  } else if (file.type.startsWith('video/')) {
    type = 'video';
  } else {
    type = 'file';
  }
  
  emit('send-file', {
    file: file,
    channelID: props.currentChat?.channelID,
    type: type
  });
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
      <div class="left-actions">
        <button class="attachment-button" @click="fileInput.click()">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
          </svg>
        </button>
        <button class="gif-button" @click="toggleGifPicker">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
          </svg>
        </button>
      </div>
      <input type="file" ref="fileInput" @change="handleImageUpload" accept="image/*,video/*" style="display: none;" />
      
      <input 
        type="text" 
        v-model="newMessage" 
        :placeholder="editingMessage ? 'Üzenet szerkesztése...' : 'Írj egy üzenetet...'" 
        @keyup.enter="submitMessage"
        @keyup.esc="$emit('cancel-editing')"
      />
      
      <div class="message-actions">
        <button class="send-button" @click="submitMessage">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- GIF picker -->
    <GifPicker 
      v-if="showGifPicker" 
      @select-gif="handleGifSelect" 
      @close="showGifPicker = false"
    />
  </div>
</template>

<style lang="scss" scoped>
@import '../../styles/chat/MessageInput.scss';

.edit-box {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 8px 12px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
}

.edit-box-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.edit-info {
  display: flex;
  flex-direction: column;
}

.edit-label {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 2px;
}

.cancel-edit-btn {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
  
  &:hover {
    color: #ffffff;
  }
}

.reply-box {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 8px 12px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
}

.reply-box-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.reply-info {
  display: flex;
  flex-direction: column;
}

</style>