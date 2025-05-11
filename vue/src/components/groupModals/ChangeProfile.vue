<script setup>
import { ref, onMounted } from 'vue';
import { userdataStore } from '../../store/UserdataStore';
import { useMessageStore } from '../../store/MessageStore';
import { useChannelStore } from '../../store/ChannelStore';
import axios from 'axios';
import { API_CONFIG } from '../../config/api.js';

const emit = defineEmits(['close']);
const userStore = userdataStore();
const messageStore = useMessageStore();
const channelStore = useChannelStore();

const profileImage = ref(null);
const profileImageUrl = ref(null);

// Hibaüzenetek és állapotok
const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// Profilkép feltöltése
const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    profileImage.value = file;
    profileImageUrl.value = URL.createObjectURL(file);
  }
};

// Felhasználói adatok mentése
const saveProfilePicture = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';
    
    // Check if there's a profile image to upload
    if (!profileImage.value) {
      successMessage.value = 'Nincs új kép kiválasztva.';
      setTimeout(() => {
        successMessage.value = '';
      }, 3000);
      isLoading.value = false;
      return;
    }
    
    const formData = new FormData();
    formData.append('file', profileImage.value);
    
    console.log('Uploading profile picture...');
    
    const response = await axios.post(`${API_CONFIG.BASE_URL}/pfp/group/${messageStore.getCurrentChannelId}`, formData, {
      headers: { 
        'Authorization': `Bearer ${userStore.getAccessToken()}`,
      }
    });
    
    console.log('Upload response:', response);
    
    // More flexible success condition
    if (response.status >= 200 && response.status < 300) {
      // If the response includes a profile image URL, use it
      if (response.data && response.data.profilePicture) {
        profileImageUrl.value = API_CONFIG.BASE_URL + response.data.profilePicture;
      } else if (response.data && response.data.data && response.data.data.profilePicture) {
        profileImageUrl.value = API_CONFIG.BASE_URL + response.data.data.profilePicture;
      } else {
        // Refresh the profile image by forcing a reload from the server
        await loadUserData();
      }
      
      // Display success message
      successMessage.value = 'A profil adatok sikeresen frissítve!';
      setTimeout(() => {
        successMessage.value = '';
      }, 3000);
    } else {
      throw new Error('Server returned an unsuccessful status code');
    }
  } catch (error) {
    console.error('Hiba a felhasználói adatok mentésekor:', error);
    errorMessage.value = 'Nem sikerült menteni a felhasználói adatokat.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(async () => {
  isLoading.value = true;

  const channel = channelStore.getGroupChannelById(messageStore.getCurrentChannelId);
  if (channel && channel.getPicture()) {
    profileImageUrl.value = API_CONFIG.BASE_URL + channel.getPicture();
  }

  isLoading.value = false;
});
</script>

<template>
  <div class="profile-settings-overlay" @click.self="emit('close')">
    <div class="profile-settings-dialog">
      <div class="profile-settings-header">
        <h2 style="width: 100%; text-align: center;">Csoport profilkép módosítása</h2>
        <button class="close-button" @click="emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="profile-settings-content">
        <div v-if="isLoading" class="loading-spinner">
          <div class="spinner"></div>
        </div>

        <div v-else>
          <div class="error-message" v-if="errorMessage">{{ errorMessage }}</div>
          <div class="success-message" v-if="successMessage">{{ successMessage }}</div>

          <div class="profile-image-section">
            <div class="profile-image-container">
              <img v-if="profileImageUrl" :src="profileImageUrl" alt="Profilkép" class="profile-image" />
              <div v-else class="profile-image-placeholder">
                {{ messageStore.getCurrentChannelName.charAt(0).toUpperCase() }}
              </div>
              <div class="profile-image-overlay">
                <label for="profile-image-upload" class="profile-image-upload-label">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                    <circle cx="12" cy="13" r="4"></circle>
                  </svg>
                </label>
                <input type="file" id="profile-image-upload" accept="image/*" @change="handleImageUpload" class="profile-image-upload" />
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button class="cancel-button" @click="emit('close')">Mégse</button>
            <button class="save-button" @click="saveProfilePicture" :disabled="isLoading">
              <span v-if="isLoading">Mentés...</span>
              <span v-else>Mentés</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.profile-settings-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.3);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(3px);
  border: none;
}

.profile-settings-dialog {
  background-color: #ffffff;
  border-radius: 16px;
  width: 500px;
  max-width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
  animation: slide-up 0.3s ease-out;
  border: none;
}

.profile-settings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  background: #ffffff;
  z-index: 10;
  border-radius: 16px 16px 0 0;
  color: #333333;
}

.profile-settings-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #6366f1;
}

.close-button {
  background: none;
  border: none;
  cursor: pointer;
  color: #6366f1;
  padding: 8px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.close-button:hover {
  background-color: rgba(99, 102, 241, 0.1);
}

.profile-settings-content {
  padding: 24px;
  background-color: #f8f9fa;
}

.profile-image-section {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.profile-image-container {
  position: relative;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border: 2px solid #e5e7eb;
}

.profile-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #6366f1;
  color: white;
  font-size: 48px;
  font-weight: 600;
}

.profile-image-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(0, 0, 0, 0.4);
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.profile-image-container:hover .profile-image-overlay {
  opacity: 1;
}

.profile-image-upload-label {
  cursor: pointer;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.profile-image-upload {
  display: none;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333333;
}

.form-control {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 16px;
  background-color: #ffffff;
  color: #333333;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

textarea.form-control {
  min-height: 100px;
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.cancel-button {
  padding: 10px 20px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background-color: #f3f4f6;
  color: #4b5563;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.cancel-button:hover {
  background-color: #e5e7eb;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.save-button {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  background-color: #6366f1;
  color: white;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.save-button:hover {
  background-color: #4f46e5;
  box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
}

.save-button:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.error-message {
  background-color: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 16px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.success-message {
  background-color: rgba(34, 197, 94, 0.1);
  color: #22c55e;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 16px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(99, 102, 241, 0.1);
  border-radius: 50%;
  border-top-color: #6366f1;
  animation: spin 1s linear infinite;
  box-shadow: 0 2px 10px rgba(99, 102, 241, 0.1);
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes slide-up {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* Dark mode támogatás */
:global(.dark-mode) {
  .profile-settings-overlay {
    background-color: rgba(0, 0, 0, 0.7);
    border: none;
  }

  .profile-settings-dialog {
    background-color: #1a1a27;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    border: none;
  }
  
  .profile-settings-header {
    background: #1a1a27;
    border-bottom: 1px solid rgba(0, 0, 0, 0.2);
    color: #e0e0e0;
  }
  
  .profile-settings-header h2 {
    color: #6366f1;
  }
  
  .close-button {
    color: #6366f1;
  }
  
  .close-button:hover {
    background-color: rgba(99, 102, 241, 0.1);
  }
  
  .profile-settings-content {
    background-color: #121220;
  }

  .profile-image-placeholder {
    background-color: #6366f1;
  }
  
  label {
    color: #e0e0e0;
  }
  
  .form-control {
    background-color: #27293d;
    border-color: #2d2d44;
    color: #e0e0e0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }
  
  .form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
  }
  
  .cancel-button {
    background-color: #27293d;
    border-color: #2d2d44;
    color: #e0e0e0;
  }
  
  .cancel-button:hover {
    background-color: #2d2d44;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  }

  .save-button {
    background-color: #6366f1;
  }

  .save-button:hover {
    background-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.5);
  }

  .save-button:disabled {
    background-color: rgba(99, 102, 241, 0.5);
  }
  
  .error-message {
    background-color: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
  }
  
  .success-message {
    background-color: rgba(34, 197, 94, 0.2);
    color: #86efac;
  }

  .spinner {
    border: 4px solid rgba(99, 102, 241, 0.2);
    border-top-color: #6366f1;
  }
}
</style>