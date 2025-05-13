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
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  position: relative;
  max-width: 90%;
  max-height: 90vh;
  border-radius: 8px;
  overflow: hidden;
}

.modal-image {
  display: block;
  max-width: 100%;
  max-height: 90vh;
  object-fit: contain;
}

.close-button {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(0, 0, 0, 0.5);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.2s;
  
  &:hover {
    background: rgba(0, 0, 0, 0.7);
  }
  
  svg {
    width: 24px;
    height: 24px;
    stroke: white;
    stroke-width: 2;
  }
}
</style>