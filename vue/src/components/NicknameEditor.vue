<script setup>
import { ref, defineEmits, defineProps } from 'vue';

const props = defineProps({
  currentName: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['close', 'save']);
const newNickname = ref(props.currentName);

const handleSave = () => {
  emit('save', newNickname.value);
  emit('close');
};
</script>

<template>
  <div class="nickname-overlay" @click.self="$emit('close')">
    <div class="nickname-modal">
      <h2>Becenév módosítása</h2>
      <div class="nickname-input-container">
        <input 
          type="text" 
          v-model="newNickname" 
          placeholder="Adj meg egy új becenevet"
          class="nickname-input"
        >
      </div>
      <div class="modal-actions">
        <button class="action-button cancel" @click="$emit('close')">Mégse</button>
        <button class="action-button save" @click="handleSave">Kész</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.nickname-overlay {
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

.nickname-modal {
  background: rgb(255, 255, 255);
  border-radius: 15px;
  padding: 24px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.nickname-modal h2 {
  color: #7078e6;
  margin-bottom: 20px;
}

.nickname-input-container {
  margin-bottom: 24px;
}

.nickname-input {
  width: 100%;
  padding: 12px;
  border: 2px solid #e6e8f0;
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.2s ease;
}

.nickname-input:focus {
  border-color: #7078e6;
  outline: none;
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

.action-button.save {
  background: #7078e6;
  color: white;
}

.action-button:hover {
  transform: translateY(-1px);
}

/* Dark mode styles - updated and enhanced */
:global(body.dark-mode) .nickname-overlay {
  background: rgba(0, 0, 0, 0.7); /* Sötétebb, átlátszó háttér dark módban */
}

:global(body.dark-mode) .nickname-modal {
  background: #2d2d2d;
  color: #ffffff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

:global(body.dark-mode) .nickname-modal h2 {
  color: #7078e6;
}

:global(body.dark-mode) .nickname-input {
  background: #3d3d3d;
  border-color: #3d3d3d;
  color: #ffffff;
}

:global(body.dark-mode) .nickname-input:focus {
  border-color: #7078e6;
  box-shadow: 0 0 0 2px rgba(112, 120, 230, 0.2);
}

:global(body.dark-mode) .nickname-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

:global(body.dark-mode) .action-button.cancel {
  background: #3d3d3d;
  color: #ffffff;
}

:global(body.dark-mode) .action-button.cancel:hover {
  background: #4d4d4d;
}

:global(body.dark-mode) .action-button.save {
  background: #7078e6;
  color: #ffffff;
}

:global(body.dark-mode) .action-button.save:hover {
  background: #5961c9;
}
</style>