<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import AppearanceSelector from './AppearanceSelector.vue';

const props = defineProps({
  currentChat: {
    type: Object,
    required: true
  },
  messages: {
    type: Array,
    required: true
  },
  currentTheme: {
    type: String,
    default: 'messenger'
  },
  showProfile: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['change-theme', 'update:showProfile']);
const showAppearanceSelector = ref(false);

const closeProfile = () => {
  emit('update:showProfile', false);
};

const handleThemeSelect = (theme) => {
  emit('change-theme', theme);
};
</script>

<template>
  <div class="profile-overlay">
    <div class="profile-sidebar">
      <button class="close-button" @click="closeProfile">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 18l-6-6 6-6"></path>
        </svg>
      </button>
      <div class="profile-header">
        <div class="profile-image">
          <img src="" alt="" />
        </div>
        <h2>{{ currentChat.name }}</h2>
        <div class="last-seen"><b>Elérhető volt:</b> {{ currentChat.lastSeen }}</div>
      </div>

      <div class="profile-section">
        <h3>Chat testreszabása</h3>
        <button class="profile-button" @click="showAppearanceSelector = true">
          <span class="button-icon">🌓</span>
          Téma megváltoztatása
        </button>
        <button class="profile-button">
          <span class="button-icon">😀</span>
          Hangulatjel megváltoztatása
        </button>
        <button class="profile-button">
          <span class="button-icon">✒️</span>
          Becenevek módosítása
        </button>
      </div>

      <div class="profile-section">
        <h3>Nézet</h3>
        <button class="profile-button" @click="showAppearanceSelector = true">
          <span class="button-icon">🎨</span>
          Megjelenés
        </button>
      </div>

      <div class="profile-section">
        <h3>Médiatartalom és fájlok</h3>
        <div class="media-grid">
          <div 
            v-for="media in mediaMessagesData" 
            :key="media.id || Math.random()" 
            class="media-item"
          >
            <img 
              v-if="media.type === 'image' || media.type === 'gif'"
              :src="media.text"
              :alt="media.fileName || 'Media content'"
              @error="$event.target.src = 'fallback-image-url'"
            />
            <div 
              v-else-if="media.type === 'video'"
              class="video-preview"
              :style="{ backgroundImage: `url(${media.thumbnail || media.text})` }"
            >
              <div class="video-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white">
                  <path d="M8 5v14l11-7z"/>
                </svg>
              </div>
            </div>
          </div>
          <div 
            v-for="n in Math.max(0, 9 - (mediaMessages?.length || 0))" 
            :key="'placeholder-' + n" 
            class="media-placeholder"
          ></div>
        </div>
      </div>

      <div class="profile-section">
        <h3>Személyes adatok védelme és támogatás</h3>
      </div>
    </div>
    
    <AppearanceSelector 
      v-if="showAppearanceSelector" 
      @close="showAppearanceSelector = false"
      @select="handleThemeSelect"
    />
  </div>
</template>

<style lang="scss" scoped>
@import '../styles/ChatProfile.scss';
</style>