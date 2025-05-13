<script setup>
import { ref, computed } from 'vue';
// Hiányzó AppearanceSelector komponens importja
import AppearanceSelector from './AppearanceSelector.vue';
import UserList from './groupModals/UserList.vue';
import AddUser from './groupModals/AddUser.vue';
import ChangeName from './groupModals/ChangeName.vue';
import ChangeProfile from './groupModals/ChangeProfile.vue';
import { useMessageStore } from '../store/MessageStore';
import { useChannelStore } from '../store/ChannelStore';
import { userdataStore } from '../store/UserdataStore';
import { useWebSocketStore } from '../store/WebSocketStore';
import { API_CONFIG } from '../config/api';
import { useAlertStore } from '../store/AlertStore.js';
import Alert from '../classes/Alert';

const emit = defineEmits(['close', 'update:showProfile']);

const messageStore = useMessageStore();
const channelStore = useChannelStore();
const webSocketStore = useWebSocketStore();
const userStore = userdataStore();
const alertStore = useAlertStore();
const activeTab = ref('appearance'); // Alapértelmezetten a megjelenés fül legyen aktív
const showAppearanceSelector = ref(false); // Hiányzó ref
const showUserList = ref(false);
const showAddUser = ref(false);
const showChangeName = ref(false);
const showChangeProfile = ref(false);

// Javított closeProfile metódus
const closeProfile = () => {
  emit('close');
  emit('update:showProfile', false);
};

const leaveGroup = () => {
  alertStore.addAlert(new Alert(
    'Megerősítés',
    'Biztosan el szeretnéd hagyni ezt a csoportot?',
    'confirm',
    () => leaveGroupCallback()
  ))
}
const leaveGroupCallback = () => {
  if (webSocketStore.isConnected) {
    webSocketStore.send({
      type: 'group_leave',
      channelId: messageStore.getCurrentChannelId,
    });
  }
};

const deleteGroup = () => {
  alertStore.addAlert(new Alert(
    'Megerősítés',
    'Biztosan törölni szeretnéd ezt a csoportot? Ez a művelet nem visszavonható.',
    'confirm',
    () => deleteGroupCallback()
  ))
}
const deleteGroupCallback = () => {
    if (webSocketStore.isConnected) {
        console.log("Deleting group");
        webSocketStore.send({
            type: 'group_delete',
            channelId: messageStore.getCurrentChannelId
        });
    }
}

const deleteFriend = () => {
  alertStore.addAlert(new Alert(
    'Megerősítés',
    'Biztosan törölni szeretnéd ezt a barátot?',
    'confirm',
    () => deleteFriendCallback()
  ))
}
const deleteFriendCallback = () => {
  if (webSocketStore.isConnected) {
    const channel = channelStore.getFriendChannelById(messageStore.getCurrentChannelId);
    const recipient_id = channel.getUser1().getUserID() == userStore.getUserID()? channel.getUser2().getUserID() : channel.getUser1().getUserID();
    webSocketStore.send({
      type: 'friend_remove',
      recipient_id: recipient_id
    });
  }
};

const profilePicture = computed(() => {
  if (messageStore.getCurrentChannelId == null) return null;
  if (channelStore.getFriendChannelById(messageStore.getCurrentChannelId)) {
    const channel = channelStore.getFriendChannelById(messageStore.getCurrentChannelId);
    const pfp = channel.getUser1().getUserID() == userStore.getUserID() ? channel.getUser2().getProfilePicture() : channel.getUser1().getProfilePicture();
    return pfp;
  } else {
    const channel = channelStore.getGroupChannelById(messageStore.getCurrentChannelId);
    return channel.getPicture();
  }
})
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
          <img v-if="profilePicture" :src="API_CONFIG.BASE_URL + profilePicture" alt="Profilkép"
            class="avatar-circle" />
          <span v-else class="avatar-circle">{{ messageStore.getCurrentChannelName.charAt(0).toUpperCase() }}</span>
        </div>
        <h2>{{ messageStore.getCurrentChannelName }}</h2>
      </div>

      <div class="profile-section" v-if="channelStore.getGroupChannelById(messageStore.getCurrentChannelId)">
        <h3>Chat testreszabása</h3>
        <div
          v-if="channelStore.getGroupChannelById(messageStore.getCurrentChannelId)?.getOwnerID() == userStore.getUserID()">
          <button class="profile-button" @click="showChangeName = true">
            <span class="button-icon">Aa</span>
            Csoport név módosítása
          </button>

          <button class="profile-button" @click="showChangeProfile = true">
            <span class="button-icon">Aa</span>
            Csoport profilkép módosítása
          </button>

          <button class="profile-button" @click="showAddUser = true">
            <span class="button-icon">Aa</span>
            Új csoporttag felvétele
          </button>
        </div>
        <button class="profile-button" @click="showUserList = true">
          <span class="button-icon">Aa</span>
          Csoporttagok
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
          <div v-for="message in messageStore.getNotTextMessages" :key="message.getMessageID() || Math.random()">
            <div class="media-item">
              <img v-if="message.getType() === 'image' || message.getType() === 'gif'" :src="message.getContent()"
                :alt="message.getContent() || 'Media content'" @error="$event.target.src = 'fallback-image-url'" />
              <div v-else-if="message.getType() === 'video'" class="video-preview"
                :style="{ backgroundImage: `url(${message.getContent()})` }">
                <div class="video-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white">
                    <path d="M8 5v14l11-7z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>


      <div class="profile-section" v-if="channelStore.getGroupChannelById(messageStore.getCurrentChannelId)">
        <button class="profile-button"
          v-if="channelStore.getGroupChannelById(messageStore.getCurrentChannelId)?.getOwnerID() == userStore.getUserID()"
          @click="deleteGroup">
          <span class="button-icon">Aa</span>
          Csoport törlése
        </button>
        <button class="profile-button" @click="leaveGroup">
          <span class="button-icon">Aa</span>
          Csoport elhagyása
        </button>
      </div>
      <div class="profile-section" v-else>
        <button class="profile-button" @click="deleteFriend">
          <span class="button-icon">Aa</span>
          Barát törlése
        </button>
      </div>

    </div>

    <AppearanceSelector v-if="showAppearanceSelector" @close="showAppearanceSelector = false"
      @select="userStore.handleThemeChange" />

    <UserList v-if="showUserList" @close="showUserList = false" />

    <AddUser v-if="showAddUser" @close="showAddUser = false" />

    <ChangeName v-if="showChangeName" @close="showChangeName = false" />

    <ChangeProfile v-if="showChangeProfile" @close="showChangeProfile = false" />
  </div>
</template>

<style lang="scss">
@import '../styles/ChatProfile.scss';
</style>