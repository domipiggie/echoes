<script setup>
import { defineEmits, ref } from 'vue';

const emit = defineEmits(['close', 'select']);
const isDarkMode = ref(document.body.classList.contains('dark-mode'));

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  document.body.classList.toggle('dark-mode');
  emit('select', isDarkMode.value ? 'dark' : 'light');
};
</script>

<template>
  <div class="appearance-overlay">
    <div class="appearance-modal">
      <h2>Téma beállítása</h2>
      <div class="theme-options">
        <button 
          class="theme-button" 
          :class="{ active: isDarkMode }"
          @click="toggleDarkMode"
        >
          <div class="theme-preview">
            <div class="mode-preview" :class="{ 'dark': isDarkMode }">
              <span class="mode-icon">
                <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7078e6">
                  <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7078e6">
                  <circle cx="12" cy="12" r="5"/>
                  <line x1="12" y1="1" x2="12" y2="3"/>
                  <line x1="12" y1="21" x2="12" y2="23"/>
                  <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                  <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                  <line x1="1" y1="12" x2="3" y2="12"/>
                  <line x1="21" y1="12" x2="23" y2="12"/>
                  <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                  <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
              </span>
            </div>
          </div>
          <span>{{ isDarkMode ? 'Sötét mód' : 'Világos mód' }}</span>
        </button>
      </div>
      <div class="modal-actions">
        <button class="action-button cancel" @click="$emit('close')">Bezárás</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.theme-preview {
  width: 100%;
  height: 120px;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
.messenger-preview {
  background: #f0f2f5;
  padding: 0;
}

.preview-chat.messenger {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 8px;
  height: 100%;
}

.messenger-message-group {
  display: flex;
  align-items: flex-end;
  gap: 4px;
}

.messenger-avatar {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #7078e6;
  flex-shrink: 0;
}

.messenger-preview .preview-message {
  padding: 6px 8px;
  font-size: 8px;
  max-width: 60%;
  border-radius: 14px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  margin: 0;
  text-transform: none;
  font-weight: normal;
  line-height: 1.2;
}

.messenger-preview .preview-message.received {
  background: white;
  color: #050505;
  align-self: flex-start;
  border-bottom-left-radius: 4px;
}

.messenger-preview .preview-message.sent {
  background: #7078e6;
  color: white;
  align-self: flex-end;
  margin-left: auto;
  /* This pushes the message to the right */
  border-bottom-right-radius: 4px;
}

/* Discord styles */
.preview-avatar {
  width: 18px;
  /* Slightly larger avatar */
  height: 18px;
  border-radius: 50%;
  background: #7078e6;
  flex-shrink: 0;
}

.message-author {
  font-size: 7px;
  /* Slightly larger font */
  color: #2d3748;
  font-weight: 600;
  text-align: left;
  text-transform: none;
}

.discord-preview .preview-message {
  font-size: 7px;
  /* Slightly larger font */
  color: #2d3748;
  max-width: 75px;
  /* Adjusted width */
  margin: 0;
  text-transform: none;
  font-weight: normal;
}

.discord-preview .preview-message.sent,
.discord-preview .preview-message.received {
  padding: 4px 6px;
  /* Slightly larger padding */
}

.message-author {
  font-size: 6px;
  /* Smaller font */
  color: #2d3748;
  font-weight: 600;
  text-align: left;
  text-transform: none;
  /* Ensures text is not all uppercase */
}

.messenger-preview .preview-message.received {
  background: white;
  color: #050505;
  align-self: flex-start;
  border-bottom-left-radius: 4px;
}

.messenger-preview .preview-message.sent {
  background: #7078e6;
  color: white;
  align-self: flex-end;
  margin-left: auto;
  /* This pushes the message to the right */
  border-bottom-right-radius: 4px;
}

/* Discord styles */
.preview-chat.discord {
  display: flex;
  flex-direction: column;
  gap: 6px;
  /* Reduced gap */
  padding: 8px;
  height: 100%;
  background: #ffffff;
  overflow-y: auto;
}

.message-group {
  display: flex;
  gap: 4px;
  /* Smaller gap */
  margin-bottom: 0;
}

.preview-avatar {
  width: 16px;
  /* Smaller avatar */
  height: 16px;
  border-radius: 50%;
  background: #7078e6;
  flex-shrink: 0;
}

.message-content {
  display: flex;
  flex-direction: column;
  gap: 1px;
  /* Smaller gap */
  align-items: flex-start;
}

.message-author {
  font-size: 6px;
  /* Smaller font */
  color: #2d3748;
  font-weight: 600;
  text-align: left;
}

.discord-preview .preview-message {
  font-size: 6px;
  /* Smaller font */
  color: #2d3748;
  max-width: 70px;
  /* Reduced width */
  margin: 0;
}

.discord-preview .preview-message.sent,
.discord-preview .preview-message.received {
  padding: 3px 5px;
  /* Smaller padding */
}

.discord-preview .preview-message.sent {
  color: #ffffff;
  background: #7078e6;
  padding: 4px 8px;
  border-radius: 4px;
}

.discord-preview .preview-message.received {
  color: #2d3748;
  background: #f0f2f5;
  padding: 4px 8px;
  border-radius: 4px;
}

/* Other styles remain unchanged */
.appearance-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.appearance-modal {
  background: white;
  border-radius: 15px;
  padding: 24px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.theme-preview {
  width: 100px;
  height: 60px;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.mode-preview {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  transition: all 0.3s ease;
}

.mode-preview.dark {
  background: #1a1a1a;
}

.mode-icon {
  font-size: 24px;
}

.theme-options {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 24px;
}

.theme-button {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 12px;
  border: 2px solid #e6e8f0;
  border-radius: 12px;
  background: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-button:hover {
  border-color: #7078e6;
  background: rgba(112, 120, 230, 0.05);
}

.theme-button.active {
  border-color: #7078e6;
  background: rgba(112, 120, 230, 0.1);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.action-button {
  padding: 8px 16px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.action-button.cancel {
  background: #e6e8f0;
  color: #484a6a;
}

.action-button.cancel:hover {
  background: #d8dae6;
}
</style>