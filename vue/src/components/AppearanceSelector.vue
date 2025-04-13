<script setup>
import { defineEmits, ref } from 'vue';

const emit = defineEmits(['close', 'select']);
const selectedTheme = ref('messenger');

const selectTheme = () => {
  emit('select', selectedTheme.value);
  emit('close');
};
</script>

<template>
  <div class="appearance-overlay">
    <div class="appearance-modal">
      <h2>Válassz megjelenést</h2>
      <div class="theme-options">
        <button class="theme-button" :class="{ active: selectedTheme === 'messenger' }"
          @click="selectedTheme = 'messenger'">
          <div class="theme-preview messenger-preview">
            <div class="preview-header"></div>
            <div class="preview-chat messenger">
              <div class="messenger-message-group">
                <div class="messenger-avatar"></div>
                <div class="preview-message received">Keresett a Feri.</div>
              </div>
              <div class="preview-message sent">Hali! Milyen Feri?</div>
              <div class="messenger-message-group">
                <div class="messenger-avatar"></div>
                <div class="preview-message received">...</div>
              </div>
            </div>
          </div>
          <span>Messenger</span>
        </button>
        <button class="theme-button" :class="{ active: selectedTheme === 'discord' }"
          @click="selectedTheme = 'discord'">
          <div class="theme-preview discord-preview">
            <div class="preview-chat discord">
              <div class="message-group">
                <div class="preview-avatar"></div>
                <div class="message-content">
                  <div class="message-author">User1</div>
                  <div class="preview-message received">Keresett a Feri.</div>
                </div>
              </div>
              <div class="message-group">
                <div class="preview-avatar"></div>
                <div class="message-content">
                  <div class="message-author">User2</div>
                  <div class="preview-message sent">Szia! Milyen Feri?</div>
                </div>
              </div>
              <div class="message-group">
                <div class="preview-avatar"></div>
                <div class="message-content">
                  <div class="message-author">User1</div>
                  <div class="preview-message received">...</div>
                </div>
              </div>
            </div>
          </div>
          <span>Discord</span>
        </button>
      </div>
      <div class="modal-actions">
        <button class="action-button select" @click="selectTheme">Kiválasztás</button>
        <button class="action-button cancel" @click="$emit('close')">Bezárás</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* General styles */
.theme-preview {
  width: 100%;
  height: 120px;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Messenger styles */
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
  z-index: 10000;
}

.appearance-modal {
  background: white;
  border-radius: 16px;
  padding: 24px;
  width: 400px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

h2 {
  color: #484a6a;
  margin: 0 0 20px 0;
  text-align: center;
}

.theme-options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

.theme-button {
  border: 2px solid #e6e8f0;
  border-radius: 12px;
  padding: 12px;
  background: none;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.theme-button.active {
  border-color: #7078e6;
  background: rgba(112, 120, 230, 0.05);
}

.theme-button span {
  color: #000000;
  font-weight: 500;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.action-button {
  padding: 8px 16px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.action-button.select {
  background: #7078e6;
  color: white;
}

.action-button.select:hover {
  background: #5a61d2;
}

.action-button.cancel {
  background: #e6e8f0;
  color: #484a6a;
}

.action-button.cancel:hover {
  background: #d8dae5;
}
</style>