<script setup>
  import { ref, watch, nextTick, onMounted } from 'vue';
  import ChatHeader from './chat/ChatHeader.vue';
  import MessageList from './chat/MessageList.vue';
  import MessageInput from './chat/MessageInput.vue';
  import ChatProfile from './ChatProfile.vue';
  import { userdataStore } from '../store/UserdataStore';
  import { useMessageStore } from '../store/MessageStore';
  import { useWebSocketStore } from '../store/WebSocketStore';
  
  const userStore = userdataStore();
  const messageStore = useMessageStore();
  const webSocketStore = useWebSocketStore();
  
  const emit = defineEmits(['send-message', 'go-back', 'update-message', 'send-file', 'delete-message']);
  
  const messagesContainer = ref(null);
  const isMobile = ref(false);
  const showProfile = ref(false);
  const replyingTo = ref(null);
  const editingMessage = ref(null);

  

  const startReply = (message) => {
    replyingTo.value = {
      id: message.getMessageID(),
      text: message.getContent(),
    };

    nextTick(() => {
      document.querySelector('.modern-message-box input').focus();
    });
  };
  
  const cancelReply = () => {
    replyingTo.value = null;
  };
  
  const startEditing = (message) => {
    console.log('Szerkesztés indítása:', message);
    if (message.getUser().getUserID() == userStore.getUserID()) {
      console.log('Szerkesztés engedélyezve');
      editingMessage.value = {
        id: message.getMessageID(),
        text: message.getContent()
      };
      
      nextTick(() => {
        document.querySelector('.modern-message-box input').focus();
      });
    }
  };
  
  const cancelEditing = () => {
    editingMessage.value = null;
  };
  
  const saveEdit = (newText) => {
    try {
      if (webSocketStore.isConnected) {
        webSocketStore.send({
          type: 'chatmessage_edit',
          messageId: editingMessage.value.id,
          content: newText
        });
      }
      cancelEditing();
    } catch (error) {
      console.error('Hiba történt a szerkesztés mentése közben:', error);
    }
  };
  
  const submitMessage = (messageData) => {
    emit('send-message', messageData);
  };
  
  const handleSendFile = (fileData) => {
    emit('send-file', fileData);
  };
  
  onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
    scrollToBottom();
  });
  
  const checkScreenSize = () => {
    isMobile.value = window.innerWidth <= 768;
  };
  
  const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  };
  
  watch(() => messageStore.getMessages, scrollToBottom);
  
  const deleteMessage = (messageId) => {
    if (confirm("Biztos vissza akarja vonni ezt az üzenetet?")) {
      emit('delete-message', messageId);
    }
  };
  
  const toggleProfile = (value) => {
    showProfile.value = value;
  };
</script>

<template>
  <div class="chat-window container-fluid p-0" :class="userStore.getCurrentTheme()">
    <ChatHeader 
      :currentChannelName="messageStore.getCurrentChannelName"
      :isMobile="isMobile"
      @go-back="$emit('go-back')"
      @toggle-profile="toggleProfile"
    />
    
    <MessageList 
      ref="messagesContainer"
      :messages="messageStore.getMessages"
      :currentTheme="userStore.getCurrentTheme()"
      :userID="userStore.getUserID()"
      :currentChannelName="messageStore.getCurrentChannelName"
      @start-reply="startReply"
      @start-editing="startEditing"
      @delete-message="deleteMessage"
    />
    
    <MessageInput 
      :replyingTo="replyingTo"
      :editingMessage="editingMessage"
      @send-message="submitMessage"
      @cancel-reply="cancelReply"
      @cancel-editing="cancelEditing"
      @save-edit="saveEdit"
      @send-file="handleSendFile"
    />
    
    <ChatProfile 
      v-if="showProfile" 
      :currentChat="messageStore.getCurrentChannel"
      @close="showProfile = false"
    />
  </div>
</template>

<style lang="scss">
@import '../styles/chat/index.scss';
</style>