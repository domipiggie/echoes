<script setup>
import { ref, defineProps, defineEmits } from 'vue';
import AppearanceSelector from './AppearanceSelector.vue';
import EmojiPicker from './EmojiPicker.vue';

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

const emit = defineEmits(['change-theme', 'update:showProfile', 'change-emoji']);
const showAppearanceSelector = ref(false);
const showEmojiPicker = ref(false);

const closeProfile = () => {
  emit('update:showProfile', false);
};

const handleThemeSelect = (theme) => {
  emit('change-theme', theme);
};

const handleEmojiSelect = (emoji) => {
  emit('change-emoji', emoji);
  showEmojiPicker.value = false;
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
          <span class="button-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="12" cy="12" r="5"/>
              <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
          </span>
          Téma megváltoztatása
        </button>
        <button class="profile-button" @click="showEmojiPicker = true">
          <span class="button-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="12" cy="12" r="10"/>
              <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
              <line x1="9" y1="9" x2="9.01" y2="9"/>
              <line x1="15" y1="9" x2="15.01" y2="9"/>
            </svg>
          </span>
          Hangulatjel megváltoztatása
        </button>
        <button class="profile-button">
          <span class="button-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
          </span>
          Becenevek módosítása
        </button>
      </div>

      <div class="profile-section">
        <h3>Nézet</h3>
        <button class="profile-button" @click="showAppearanceSelector = true">
          <span class="button-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M12 2l3 3m-3-3l-3 3m3-3v6m0 13l3-3m-3 3l-3-3m3 3v-6m-8-5h6m13 0h-6"/>
            </svg>
          </span>
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
    
    <EmojiPicker 
      v-if="showEmojiPicker"
      @select="handleEmojiSelect"
      @close="showEmojiPicker = false"
    />
  </div>
</template>

<style lang="scss" scoped>
@import '../styles/ChatProfile.scss';

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
  
  svg {
    stroke: #7078e6;
    stroke-width: 2;
  }
}
</style>