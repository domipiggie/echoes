<script setup>
import { defineEmits } from 'vue';

const emit = defineEmits(['select', 'close']);

const emojis = ['😀', '😂', '🥰', '😎', '🤔', '😴', '🥳', '😇', '🤩', '😊', '😍', '🤗', '😋', '😉', '🥺', 
                '😈', '🥵', '🥶', '😱', '🤯', '🐵', '🤠', '🤡', '👻', '👽', '🤖', '🎃', '💀', '👾', '🤪'];

const selectEmoji = (emoji) => {
  emit('select', emoji);
  emit('close');
};
</script>

<template>
  <div class="emoji-picker-overlay" @click.self="$emit('close')">
    <div class="emoji-picker">
      <div class="emoji-grid">
        <button 
          v-for="emoji in emojis" 
          :key="emoji"
          class="emoji-button"
          @click="selectEmoji(emoji)"
        >
          {{ emoji }}
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.emoji-picker-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.emoji-picker {
  background: #232020;;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  min-width: 320px;
}

:global(body.dark-mode) .emoji-picker {
  background: #e6e5e5;
  color: rgb(32, 31, 31);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.emoji-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 10px;
  justify-items: center;
  align-items: center;
}

.emoji-button {
  width: 40px;
  height: 40px;
  border: none;
  background: none;
  font-size: 24px;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;

  &:hover {
    background: rgba(112, 120, 230, 0.1);
    transform: scale(1.1);
  }
}

// Add dark mode hover effect for emoji buttons
:global(body.dark-mode) .emoji-button:hover {
  background: rgba(112, 120, 230, 0.2);
}
</style>