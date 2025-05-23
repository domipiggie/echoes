<script setup>
import { ref,computed } from 'vue';
import { useMessageStore } from '../../store/MessageStore';
import { useChannelStore } from '../../store/ChannelStore';
import { userdataStore } from '../../store/UserdataStore';
import { API_CONFIG } from '../../config/api';

const messageStore = useMessageStore();
const channelStore = useChannelStore();
const userStore = userdataStore();

const props = defineProps({
  currentChannelName: String,
  isMobile: Boolean
});

const emit = defineEmits(['go-back', 'toggle-profile']);

const showProfile = ref(false);

const toggleProfile = () => {
  showProfile.value = !showProfile.value;
  emit('toggle-profile', showProfile.value);
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
  <div class="chat-header row m-0 align-items-center">
    <div class="col d-flex align-items-center">
      <div class="user-info">
        <button 
          class="back-button btn"
          @click="$emit('go-back')"
          v-if="isMobile"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path fill="currentColor" d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z"/>
          </svg>
        </button>
        <div class="avatar">
          <div class="avatar-circle">
            <img v-if="profilePicture" :src="API_CONFIG.BASE_URL + profilePicture" alt="Profilkép" />
            <span v-else>{{ currentChannelName.charAt(0).toUpperCase() }}</span>
          </div>
        </div>
        <div class="user-details">
          <div class="user-name">{{ currentChannelName }}</div>
        </div>
      </div>
    </div>
    <div class="col-auto">
      <button class="more-button btn" @click="toggleProfile">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="1"></circle>
          <circle cx="19" cy="12" r="1"></circle>
          <circle cx="5" cy="12" r="1"></circle>
        </svg>
      </button>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../styles/chat/ChatHeader.scss';
@import '../../styles/chat/Avatar.scss';
</style>