<script setup>
const props = defineProps({
  imageUrl: {
    type: String,
    required: true
  },
  onClose: {
    type: Function,
    required: true
  }
});
</script>

<template>
  <div class="modal-overlay" @click="onClose">
    <div class="modal-content" @click.stop>
      <img :src="imageUrl" class="modal-image" />
      <button class="close-button" @click="onClose">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M18 6L6 18M6 6l12 12"></path>
        </svg>
      </button>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../../styles/chat/messages/ImageModal.scss';
</style>