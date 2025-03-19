<script setup>
import { defineProps, defineEmits, computed, ref, watchEffect } from 'vue';

const props = defineProps({
  currentChat: {
    type: Object,
    required: true,
  },
  messages: {
    type: Array,
    required: true,
  }
});

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
const mediaMessages = computed(async () => {
  if (!props.messages || !Array.isArray(props.messages)) {
    return [];
  }
  
  const messages = props.messages
    .filter(msg => {
      return msg && msg.type && (
        msg.type === 'image' || 
        msg.type === 'video' || 
        msg.type === 'gif'
      ) && msg.text;
    })
    .slice(-9)
    .reverse();

  // Generate thumbnails for videos
  for (const msg of messages) {
    if (msg.type === 'video') {
      msg.thumbnail = await getVideoThumbnail(msg.text);
    }
  }

  return messages;
});

const emit = defineEmits(['update:showProfile']);

const closeProfile = () => {
  emit('update:showProfile', false);
};

const mediaMessagesData = ref([]);

watchEffect(async () => {
  if (!props.messages || !Array.isArray(props.messages)) {
    mediaMessagesData.value = [];
    return;
  }
  
  const messages = props.messages
    .filter(msg => {
      return msg && msg.type && (
        msg.type === 'image' || 
        msg.type === 'video' || 
        msg.type === 'gif'
      ) && msg.text;
    })
    .slice(-9)
    .reverse();

  // Generate thumbnails for videos
  for (const msg of messages) {
    if (msg.type === 'video') {
      try {
        msg.thumbnail = await getVideoThumbnail(msg.text);
      } catch (error) {
        console.error('Error generating thumbnail:', error);
        msg.thumbnail = null;
      }
    }
  }

  mediaMessagesData.value = messages;
});

// Remove the computed mediaMessages and use mediaMessagesData in template instead
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
  z-index: 9999;  /* Add this line */
}

.close-button svg {
  display: block;  /* Add this block */
  width: 24px;
  height: 24px;
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
  gap: 8px;
  margin-top: 12px;
}

.media-item {
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  background: rgba(112, 120, 230, 0.1);
}

.media-item img,
.media-item video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.media-placeholder {
  aspect-ratio: 1;
  background: rgba(112, 120, 230, 0.05);
  border-radius: 8px;
}

.profile-section {
  padding: 16px;
  border-bottom: 1px solid rgba(112, 120, 230, 0.1);
}
</style>

<style scoped>
.video-preview {
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.video-icon {
  background: rgba(0, 0, 0, 0.5);
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>