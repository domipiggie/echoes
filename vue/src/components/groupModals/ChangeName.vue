<script setup>
import { useWebSocketStore } from '../../store/WebSocketStore';
import { useMessageStore } from '../../store/messageStore';

import { ref } from 'vue';

const emit = defineEmits(['close']);
const webSocketStore = useWebSocketStore();
const messageStore = useMessageStore();

const groupName = ref('');

const changeName = () => {
    if (webSocketStore.isConnected) {
        webSocketStore.send({
            type: 'group_update_info',
            channelId: messageStore.getCurrentChannelId,
            groupName: groupName.value
        });
        emit('close');
    }
};
</script>

<template>
  <div class="new-group-overlay" @click.self="emit('close')">
    <div class="new-group-dialog">
      <div class="dialog-header">
        <h2>Csoport név módosítása</h2>
        <button class="close-button" @click="emit('close')">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <div class="input-container">
        <input type="text" v-model="groupName" :placeholder="messageStore.getCurrentChannelName" />
      </div>

      <div class="button-container">
        <button class="cancel-button" @click="emit('close')">Mégse</button>
        <button class="create-button" @click="changeName" :disabled="groupName.length === 0">
          <span>Kész</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../styles/modals/ChangeName.scss';
</style>