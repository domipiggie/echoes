<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import DiscordMessage from './messages/DiscordMessage.vue';
import StandardMessage from './messages/StandardMessage.vue';
import { useMessageStore } from '../../store/MessageStore';

const props = defineProps({
  messages: Array,
  currentTheme: String,
  userID: String,
  currentChannelName: String
});

const emit = defineEmits(['start-reply', 'start-editing', 'delete-message']);
const messageStore = useMessageStore();
const showVideoOptions = ref({});
const messagesContainer = ref(null);
const isLoadingMore = ref(false);

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

const startReply = (message) => {
    emit('start-reply', message);
};

const handleScroll = async () => {
  if (!messagesContainer.value || isLoadingMore.value) return;
  
  if (messagesContainer.value.dataset.noMoreMessages === 'true') return;
  
  if (messagesContainer.value.scrollTop < 50) {
    isLoadingMore.value = true;
    
    const scrollHeight = messagesContainer.value.scrollHeight;
    
    const hasMoreMessages = await messageStore.loadMoreMessages();
    
    setTimeout(() => {
      if (messagesContainer.value) {
        const newScrollHeight = messagesContainer.value.scrollHeight;
        messagesContainer.value.scrollTop = newScrollHeight - scrollHeight;
      }
      isLoadingMore.value = false;
      
      if (!hasMoreMessages) {
        messagesContainer.value.dataset.noMoreMessages = 'true';
      }
    }, 100);
  }
};

const resetNoMoreMessagesFlag = () => {
  if (messagesContainer.value) {
    delete messagesContainer.value.dataset.noMoreMessages;
  }
};

onMounted(() => {
  if (messagesContainer.value) {
    messagesContainer.value.addEventListener('scroll', handleScroll);
    resetNoMoreMessagesFlag();
  }
});

watch(() => messageStore.getCurrentChannelId, () => {
  resetNoMoreMessagesFlag();
});

onUnmounted(() => {
  if (messagesContainer.value) {
    messagesContainer.value.removeEventListener('scroll', handleScroll);
  }
});
</script>

<template>
  <div class="messages-container" ref="messagesContainer">
    <div v-if="isLoadingMore" class="loading-indicator">
      További üzenetek betöltése...
    </div>
    
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
        @start-reply="startReply(message)"
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
        :currentChannelName="currentChannelName"
        :showVideoOptions="showVideoOptions"
        @toggle-video-options="toggleVideoOptions"
        @toggle-fullscreen="toggleFullscreen"
        @toggle-mute="toggleMute"
        @start-reply="startReply(message)"
        @start-editing="emit('start-editing', $event)"
        @delete-message="emit('delete-message', $event)"
      />
    </template>
  </div>
</template>

<style lang="scss" scoped>
@import '../../styles/chat/MessageList.scss';
</style>