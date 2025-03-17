<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  currentChat: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(['update:showProfile']);

const closeProfile = () => {
  emit('update:showProfile', false);
};
</script>

<template>
  <div class="profile-overlay">
    <div class="profile-sidebar">
      <button class="close-button" @click="closeProfile">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
      </button>
      <div class="profile-header">
        <div class="profile-image">
          <img src="" alt="" />
        </div>
        <h2>{{ currentChat.name }}</h2>
        <div class="last-seen">Elérhető volt: {{ currentChat.lastSeen }}</div>
      </div>

      <div class="profile-section">
        <h3>Chat testreszabása</h3>
        <button class="profile-button">
          <span class="button-icon"></span>
          Téma megváltoztatása
        </button>
        <button class="profile-button">
          <span class="button-icon">Aa</span>
          Hangulatjel megváltoztatása
        </button>
        <button class="profile-button">
          <span class="button-icon">Aa</span>
          Becenevek módosítása
        </button>
      </div>

      <div class="profile-section">
        <h3>Médiatartalom és fájlok</h3>
        <div class="media-grid">
          <div class="media-placeholder"></div>
          <div class="media-placeholder"></div>
          <div class="media-placeholder"></div>
        </div>
      </div>

      <div class="profile-section">
        <h3>Személyes adatok védelme és támogatás</h3>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-overlay {
  position: fixed;
  top: 2.5vh;
  right: 2.5vw;
  bottom: 2.5vh;
  width: 360px;
  background: white;
  z-index: 9998;
  border-radius: 16px;
  box-shadow: -4px 0 20px rgba(112, 120, 230, 0.15);
}

.profile-sidebar {
  width: 100%;
  height: 100%;
  background-color: #ffffff;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  position: relative;
  animation: slideIn 0.3s ease-out;
  border-radius: 16px;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.profile-header {
  padding: 24px;
  text-align: center;
  border-bottom: 1px solid rgba(238, 230, 230, 0.1);
  background: linear-gradient(135deg, #7078e6, #4e55df);
  color: white;
  border-radius: 16px 16px 0 0;
}

.close-button {
  position: absolute;
  top: 16px;
  left: 16px;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.close-button:hover {
  background-color: rgba(255, 255, 255, 0.2);
  transform: scale(1.1);
}

.profile-header {
  padding: 24px;
  text-align: center;
  border-bottom: 1px solid rgba(112, 120, 230, 0.1);
  background: linear-gradient(135deg, #7078e6, #4e55df);
  color: white;
}

.profile-image {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.1);
  margin: 0 auto 16px;
  border: 3px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-header h2 {
  margin: 0;
  color: #ffffff;
  font-size: 20px;
}

.last-seen {
  color: rgba(255, 255, 255, 0.8);
  font-size: 13px;
  margin-top: 4px;
}

.profile-section {
  padding: 20px;
  border-bottom: 1px solid rgba(112, 120, 230, 0.1);
  background: white;
}

.profile-section h3 {
  color: #484a6a;
  font-size: 16px;
  margin: 0 0 16px 0;
  font-weight: 600;
}

.profile-button {
  width: 100%;
  padding: 12px 16px;
  background: none;
  border: none;
  color: #484a6a;
  font-size: 15px;
  text-align: left;
  cursor: pointer;
  display: flex;
  align-items: center;
  border-radius: 12px;
  margin-bottom: 8px;
  transition: all 0.2s ease;
}

.profile-button:hover {
  background-color: rgba(112, 120, 230, 0.08);
  transform: translateX(4px);
}

.button-icon {
  margin-right: 12px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(112, 120, 230, 0.1);
  border-radius: 8px;
  color: #7078e6;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-top: 16px;
}

.media-placeholder {
  aspect-ratio: 1;
  background: linear-gradient(135deg, rgba(112, 120, 230, 0.1), rgba(112, 120, 230, 0.05));
  border-radius: 12px;
  transition: all 0.2s ease;
}

.media-placeholder:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(112, 120, 230, 0.1);
}
</style>