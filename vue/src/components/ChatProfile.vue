<script setup>
import { ref, computed } from 'vue';
// Hiányzó AppearanceSelector komponens importja
import AppearanceSelector from './AppearanceSelector.vue';
import { useMessageStore } from '../store/MessageStore';
import { userdataStore } from '../store/UserdataStore';

const emit = defineEmits(['close', 'update:showProfile']);

const messageStore = useMessageStore();
const userStore = userdataStore();
const activeTab = ref('appearance'); // Alapértelmezetten a megjelenés fül legyen aktív
const showAppearanceSelector = ref(false); // Hiányzó ref

// Javított closeProfile metódus
const closeProfile = () => {
  emit('close');
  emit('update:showProfile', false);
};

// Hiányzó handleThemeSelect metódus
const handleThemeSelect = (theme) => {
  showAppearanceSelector.value = false;
};

const getVideoThumbnail = (videoUrl) => {
  const video = document.createElement('video');
  video.src = videoUrl;
  return new Promise((resolve) => {
    video.addEventListener('loadeddata', () => {
      video.currentTime = 0;
      const canvas = document.createElement('canvas');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(video, 0, 0);
      resolve(canvas.toDataURL('image/jpeg'));
    });
  });
};

// Update mediaMessages computed property
const mediaMessages = computed(() => {
  return messageStore.getMessages
    .filter(msg => {
      return msg && msg.type && (
        msg.type === 'image' ||
        msg.type === 'video' ||
        msg.type === 'gif'
      ) && msg.text;
    })
    .slice(-9)
    .reverse();
});

// Hiányzó mediaMessagesData - egyszerűsített verzió a computed property-ből
const mediaMessagesData = computed(() => {
  return mediaMessages.value;
});
</script>

<template>
  <div class="profile-overlay">
    <div class="profile-sidebar">
      <button class="close-button" @click="closeProfile">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6"></path>
        </svg>
      </button>
      <div class="profile-header">
        <div class="profile-image">
          <img src="" alt="" />
        </div>
        <h2>{{ messageStore.getCurrentChannelName }}</h2>
        <div class="last-seen">Elérhető volt: {{  }}soha</div>
      </div>

      <div class="profile-section">
        <h3>Chat testreszabása</h3>
        <button class="profile-button" @click="showAppearanceSelector = true">
          <span class="button-icon"></span>
          Téma megváltoztatása
        </button>
        <button class="profile-button">
          <span class="button-icon">Aa</span>
          Becenevek módosítása
        </button>
      </div>

      <div class="profile-section">
        <h3>Nézet</h3>
        <button class="profile-button" @click="showAppearanceSelector = true">
          <span class="button-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </span>
          Megjelenés
        </button>
      </div>

      <div class="profile-section">
        <h3>Médiatartalom és fájlok</h3>
        <div class="media-grid">
          <div v-for="media in mediaMessagesData" :key="media.id || Math.random()" class="media-item">
            <img v-if="media.type === 'image' || media.type === 'gif'" :src="media.text"
              :alt="media.fileName || 'Media content'" @error="$event.target.src = 'fallback-image-url'" />
            <div v-else-if="media.type === 'video'" class="video-preview"
              :style="{ backgroundImage: `url(${media.thumbnail || media.text})` }">
              <div class="video-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white">
                  <path d="M8 5v14l11-7z" />
                </svg>
              </div>
            </div>
          </div>
          <div v-for="n in Math.max(0, 9 - (mediaMessages?.length || 0))" :key="'placeholder-' + n"
            class="media-placeholder"></div>
        </div>
      </div>

      
    </div>

    <AppearanceSelector v-if="showAppearanceSelector" @close="showAppearanceSelector = false"
      @select="userStore.handleThemeChange" />
  </div>
</template>

<style lang="scss">
@import '../styles/ChatProfile.scss';
</style>