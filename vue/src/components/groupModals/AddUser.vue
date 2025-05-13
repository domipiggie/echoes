<script setup>
import { ref, computed } from 'vue';
import { useFriendshipStore } from '../../store/FriendshipStore';
import { useWebSocketStore } from '../../store/WebSocketStore';
import { userdataStore } from '../../store/UserdataStore';
import { useMessageStore } from '../../store/MessageStore';
import { useChannelStore } from '../../store/ChannelStore';
import { API_CONFIG } from '../../config/api.js';

const emit = defineEmits(['close']);
const friendshipStore = useFriendshipStore();
const webSocketStore = useWebSocketStore();
const userStore = userdataStore();
const messageStore = useMessageStore();
const channelStore = useChannelStore();

const availableUsers = computed(() => {
  const currentChannelId = messageStore.getCurrentChannelId;
  const currentChannel = channelStore.getGroupChannelById(currentChannelId);
  const channelUsers = currentChannel.getUsers();
  const friendList = friendshipStore.getAcceptedFriendships;

  return friendList.filter(friendship => {
    const friendUser = friendship.getTargetUser();
    return !channelUsers.some(channelUser =>
      channelUser.getUserID() === friendUser.getUserID()
    );
  });
});
const selectedFriends = ref([]);

const toggleFriendSelection = (friend) => {
  const index = selectedFriends.value.findIndex(f => f.getUserID() === friend.getUserID());
  if (index === -1) {
    selectedFriends.value.push(friend);
  } else {
    selectedFriends.value.splice(index, 1);
  }
};

const isFriendSelected = (friend) => {
  return selectedFriends.value.some(f => f.getUserID() === friend.getUserID());
};

const addUsers = () => {
  const currentChannelId = messageStore.getCurrentChannelId;
  const selectedUserIds = selectedFriends.value.map(friend => friend.getUserID());

  if (webSocketStore.isConnected) {
    webSocketStore.send({
      type: 'group_add_user',
      channelId: currentChannelId,
      userIds: selectedUserIds
    });
    emit('close');
  }

  channelStore.fetchGroupChannels();
}

</script>

<template>
  <div class="new-group-overlay" @click.self="emit('close')">
    <div class="new-group-dialog">
      <div class="dialog-header">
        <h2>Csoporttag felvétele</h2>
        <button class="close-button" @click="emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="friends-list-container">
        <h3>Válassz barátokat a csoporthoz</h3>
        <div class="friends-list" v-if="availableUsers.length > 0">
          <div v-for="friendship in availableUsers" class="friend-item"
            :class="{ 'selected': isFriendSelected(friendship.getTargetUser()) }"
            @click="toggleFriendSelection(friendship.getTargetUser())">
            <div class="friend-avatar">
              <img v-if="friendship.getTargetUser().getProfilePicture()" :src="API_CONFIG.BASE_URL + friendship.getTargetUser().getProfilePicture()" alt="Profilkép"
                class="avatar-circle" />
              <span v-else>{{ friendship.getTargetUser().getUserName().charAt(0).toUpperCase() }}</span>
            </div>
            <div class="friend-info">
              <div class="friend-name">{{ friendship.getTargetUser().getUserName() }}</div>
            </div>
            <div class="selection-indicator">
              <svg v-if="isFriendSelected(friendship.getTargetUser())" xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </div>
          </div>
        </div>
        <div v-else class="no-friends-message">
          Nincsenek barátaid akik nem tagja a csoportnak.
        </div>
      </div>

      <div class="button-container">
        <button class="cancel-button" @click="emit('close')">Mégse</button>
        <button class="create-button" @click="addUsers">
          <span>Tagok felvétele</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../styles/modals/AddUser.scss';
</style>