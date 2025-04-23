<script setup>
import { ref } from 'vue';
import DiscordMessage from './messages/DiscordMessage.vue';
import StandardMessage from './messages/StandardMessage.vue';

const props = defineProps({
  messages: Array,
  currentTheme: String,
  userID: String,
  currentChannelName: String
});

const emit = defineEmits(['start-reply', 'start-editing', 'delete-message']);

const showVideoOptions = ref({});

const toggleVideoOptions = (messageId) => {
  showVideoOptions.value[messageId] = !showVideoOptions.value[messageId];
};

const toggleFullscreen = (video) => {
  if (!document.fullscreenElement) {
    video.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
};

const videoStates = ref({});

const toggleMute = (video, messageId) => {
  video.muted = !video.muted;
  videoStates.value[messageId] = {
    ...videoStates.value[messageId],
    muted: video.muted
  };
};
</script>

<template>
  <div class="messages-container">
    <!-- Discord theme messages -->
    <template v-if="currentTheme === 'discord'">
      <DiscordMessage 
        v-for="(message, index) in messages" 
        :key="message.getMessageID()"
        :message="message"
        :index="index"
        :messages="messages"
        :userID="userID"
        :currentChannelName="currentChannelName"
        :showVideoOptions="showVideoOptions"
        @toggle-video-options="toggleVideoOptions"
        @toggle-fullscreen="toggleFullscreen"
        @toggle-mute="toggleMute"
        @start-reply="emit('start-reply', $event)"
        @start-editing="emit('start-editing', $event)"
        @delete-message="emit('delete-message', $event)"
      />
    </template>
    
    <!-- Standard theme messages -->
    <template v-else>
      <StandardMessage 
        v-for="(message, index) in messages" 
        :key="message.getMessageID()"
        :message="message"
        :index="index"
        :messages="messages"
        :userID="userID"
        :showVideoOptions="showVideoOptions"
        @toggle-video-options="toggleVideoOptions"
        @toggle-fullscreen="toggleFullscreen"
        @toggle-mute="toggleMute"
        @start-reply="emit('start-reply', $event)"
        @start-editing="emit('start-editing', $event)"
        @delete-message="emit('delete-message', $event)"
      />
    </template>
  </div>
</template>

<style lang="scss" scoped>
@import '../../styles/chat/MessageList.scss';
</style>