<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { userdataStore } from '../store/UserdataStore';
import { useChannelStore } from '../store/ChannelStore';
import { useFriendshipStore } from '../store/FriendshipStore';
import { useWebSocketStore } from '../store/WebSocketStore';
import { useRouter } from 'vue-router';
import NewChatDialog from './NewChatDialog.vue';
import NewGroupDialog from './NewGroupDialog.vue';
import ProfileSettingsDialog from './ProfileSettingsDialog.vue';
import friendService from '../services/friendService';

const router = useRouter();

const userStore = userdataStore();
const channelStore = useChannelStore();
const friendStore = useFriendshipStore();
const webSocketStore = useWebSocketStore();

const emit = defineEmits(['select-chat']);

// Beállítások modal kezelése
const showSettingsModal = ref(false);
const showProfileSettingsModal = ref(false);
const darkMode = ref(false);
const storageUsed = ref(45); // MB-ban, ezt később API-ból kérheted le

// Dark mode kapcsoló
const toggleDarkMode = () => {
  darkMode.value = !darkMode.value;
  if (darkMode.value) {
    document.body.classList.add('dark-mode');
  } else {
    document.body.classList.remove('dark-mode');
  }
  localStorage.setItem('darkMode', darkMode.value ? 'true' : 'false');
};

// Profil beállítások megnyitása
const openProfileSettings = () => {
  showProfileSettingsModal.value = true;
  showSettingsModal.value = false;
};

// Kijelentkezés függvény
const logout = () => {
  // Sötét mód eltávolítása a kijelentkezés előtt, hogy a login oldal világos maradjon
  document.body.classList.remove('dark-mode');
  // Töröljük a sötét mód beállítást a localStorage-ból
  localStorage.removeItem('darkMode');
  // Töröljük a felhasználói adatokat
  userStore.clearAuth();
  // Átirányítás a bejelentkezési oldalra a router segítségével
  router.push('/');
};

const selectChat = (chat) => {
  emit('select-chat', chat);
};

// Aktív nézet kezelése
const activeView = ref('chats'); // 'chats' vagy 'requests'
const requestType = ref('incoming'); // 'incoming' vagy 'sent'
const chatType = ref('personal'); // 'personal' vagy 'groups'

// Dialog visibility states
const showNewChatDialog = ref(false);
const showNewGroupDialog = ref(false);

const handleNewChat = () => {
  showNewChatDialog.value = true;
};

const handleChatCreated = (newChat) => {
  console.log('New chat created:', newChat);
};

const handleGroupCreated = (newGroup) => {
  console.log('New group created:', newGroup);
  showNewGroupDialog.value = false;
};

const acceptFriendRequest = async (request) => {
  try {
    if (webSocketStore.getIsConnected) {
      console.log(request.getFriendshipID())
      webSocketStore.send({
        type: 'friend_accept',
        recipient_id: request.getInitiator()
      });
    }
  } catch (error) {
    console.error('Failed to accept friend request:', error);
  }
};

const rejectFriendRequest = async (request) => {
  try {
    if (webSocketStore.getIsConnected) {
      console.log(request.getFriendshipID())
      webSocketStore.send({
        type: 'friend_deny',
        recipient_id: request.getInitiator() == userStore.getUserID() ? request.getTargetUser().getUserID() : request.getInitiator()
      });
    }
  } catch (error) {
    console.error('Failed to accept friend request:', error);
  }
};

const reloadFriendships = async () => {
  try {
    await friendStore.clearFriendships();
    await friendStore.fetchFriendships();
  } catch (error) {
    console.error('Failed to reload friendships:', error);
  }
};

onMounted(() => {
  const savedDarkMode = localStorage.getItem('darkMode');
  if (savedDarkMode === 'true') {
    darkMode.value = true;
    document.body.classList.add('dark-mode');
  } else {
    darkMode.value = false;
    document.body.classList.remove('dark-mode');
  }

  webSocketStore.registerHandler("friend_request_denied", reloadFriendships);
  webSocketStore.registerHandler("friend_deny", reloadFriendships);
  webSocketStore.registerHandler("friend_request_recieved", reloadFriendships);
  
});

onUnmounted(() => {
  webSocketStore.unregisterHandler("friend_request_denied");
  webSocketStore.unregisterHandler("friend_deny");
  webSocketStore.unregisterHandler("friend_request_recieved");
});
</script>

<template>
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="title-section">
        <h1>Üzenetek</h1>
        <div class="header-buttons">
          <button class="action-btn" @click="handleNewChat">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
              <path fill="currentColor"
                d="M19,3H5C3.89,3,3,3.89,3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M17,13H13V17H11V13H7V11H11V7H13V11H17V13Z" />
            </svg>
          </button>
          <button class="action-btn" @click="showSettingsModal = true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
              <path fill="currentColor"
                d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z" />
            </svg>
          </button>
        </div>
      </div>
      <div class="search-container">
        <div class="search-bar">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" placeholder="Keresés az üzenetek között..." />
        </div>
      </div>
    </div>

    <!-- Új navigációs gombokok a képnek megfelelően -->
    <div class="main-tabs">
      <button class="main-tab-button" :class="{ active: activeView === 'chats' }" @click="activeView = 'chats'">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        ÜZENETEK
      </button>
      <button class="main-tab-button" :class="{ active: activeView === 'requests' }" @click="activeView = 'requests'">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="8.5" cy="7" r="4"></circle>
          <line x1="20" y1="8" x2="20" y2="14"></line>
          <line x1="23" y1="11" x2="17" y2="11"></line>
        </svg>
        BARÁTKÉRELMEK
      </button>
    </div>

    <!-- Üzenetek altabfülek - csak akkor jelenik meg, ha az üzenetek aktívak -->
    <div v-if="activeView === 'chats'" class="chat-tabs">
      <button class="chat-tab-button" :class="{ active: chatType === 'personal' }" @click="chatType = 'personal'">
        SZEMÉLYES
      </button>
      <button class="chat-tab-button" :class="{ active: chatType === 'groups' }" @click="chatType = 'groups'">
        CSOPORTOK
      </button>
    </div>

    <!-- Barátkérelmek altabfülek - csak akkor jelenik meg, ha a barátkérelmek aktívak -->
    <div v-if="activeView === 'requests'" class="request-tabs">
      <button class="request-tab-button" :class="{ active: requestType === 'incoming' }"
        @click="requestType = 'incoming'">
        BEJÖVŐ KÉRELMEK
      </button>
      <button class="request-tab-button" :class="{ active: requestType === 'sent' }" @click="requestType = 'sent'">
        ELKÜLDÖTT KÉRELMEK
      </button>
    </div>

    <!-- Üzenetek lista -->
    <div v-if="activeView === 'chats'" class="chats-list">
      <!-- Személyes üzenetek -->
      <div v-if="chatType === 'personal'">
        <div v-for="chat in channelStore.getFriendChannels" :key="chat.getChannelID()" class="chat-item"
          @click="selectChat(chat)">
          <div class="avatar">
            <div class="avatar-circle"></div>
          </div>
          <div class="chat-info">
            <div class="chat-name">{{ userStore.getUserID() == chat.getUser1().getUserID() ?
              chat.getUser2().getUserName() : chat.getUser1().getUserName() }}</div>
            <div class="last-seen">{{ }}</div>
            <div class="last-message">{{ chat.lastMessage || 'Még nincs üzenet' }}</div>
          </div>
        </div>
      </div>

      <!-- Csoportos üzenetek -->
      <div v-if="chatType === 'groups' && channelStore.getGroupChannels.length == 0" class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="1">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="9" cy="7" r="4"></circle>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
        <p>Nincsenek csoportos beszélgetések</p>
      </div>
      <div v-else-if="chatType === 'groups' && channelStore.getGroupChannels.length > 0">
        <div v-for="chat in channelStore.getGroupChannels" :key="chat.getChannelID()" class="chat-item"
          @click="selectChat(chat)">
          <div class="avatar">
            <div class="avatar-circle"></div>
          </div>
          <div class="chat-info">
            <div class="chat-name">{{ chat.getUsers()[0].getUserName() }}</div>
            <div class="last-message">{{ chat.lastMessage || 'Még nincs üzenet' }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Barátkérelmek lista -->
    <div v-if="activeView === 'requests'" class="chats-list requests-content">
      <!-- Bejövő kérelmek -->
      <div v-if="requestType === 'incoming'">
        <div v-if="friendStore.getIncomingRequests.length === 0" class="empty-state">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="8.5" cy="7" r="4"></circle>
            <line x1="20" y1="8" x2="20" y2="14"></line>
            <line x1="23" y1="11" x2="17" y2="11"></line>
          </svg>
          <p>Nincsenek függőben lévő barátkérelmek</p>
        </div>

        <div v-else>
          <!-- Meglévő barátkérelmek listája -->
          <div v-for="request in friendStore.getIncomingRequests" :key="request.getFriendshipID()"
            class="friend-request-item">
            <div class="avatar">
              <div class="avatar-circle">
                <!-- Display first letter of username if no avatar -->
                <span v-if="!request.avatar">{{ request.getTargetUser().getUserName().charAt(0) }}</span>
              </div>
            </div>
            <div class="request-info">
              <div class="request-name">{{ request.getTargetUser().getUserName() }}</div>
            </div>
            <div class="request-actions">
              <button class="accept-btn round accept" @click="acceptFriendRequest(request)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="#4CAF50" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </button>
              <button class="reject-btn round reject" @click="rejectFriendRequest(request)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="#f44336" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Elküldött kérelmek -->
      <div v-if="requestType === 'sent'">
        <div v-if="friendStore.getOutgoingRequests.length == 0" class="empty-state">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="8.5" cy="7" r="4"></circle>
            <line x1="20" y1="8" x2="20" y2="14"></line>
            <line x1="23" y1="11" x2="17" y2="11"></line>
          </svg>
          <p>Nincsenek elküldött barátkérelmek</p>
        </div>
        <div v-else>
          <!-- Meglévő barátkérelmek listája -->
          <div v-for="request in friendStore.getOutgoingRequests" :key="request.getFriendshipID()" class="friend-request-item">
            <div class="avatar">
              <div class="avatar-circle">
                <!-- Display first letter of username if no avatar -->
                <span v-if="!request.avatar">{{ request.getTargetUser().getUserName().charAt(0) }}</span>
              </div>
            </div>
            <div class="request-info">
              <div class="request-name">{{ request.getTargetUser().getUserName() }}</div>
            </div>
            <div class="request-actions">
              <button class="reject-btn round reject" @click="rejectFriendRequest(request)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="#f44336" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- New Chat Dialog -->
    <NewChatDialog v-if="showNewChatDialog" @close="showNewChatDialog = false" @chat-created="handleChatCreated" @open-group-dialog="showNewGroupDialog = true" />
    <!-- New Group Dialog -->
    <NewGroupDialog v-if="showNewGroupDialog" @close="showNewGroupDialog = false" @group-created="handleGroupCreated" />

    <!-- Beállítások Modal -->
    <div v-if="showSettingsModal" class="settings-modal-overlay" @click="showSettingsModal = false">
      <div class="settings-modal" @click.stop>
        <div class="settings-header">
          <h2>Beállítások</h2>
          <button class="close-button" @click="showSettingsModal = false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <div class="settings-content">
          <!-- Profil beállítások -->
          <div class="settings-item" @click="openProfileSettings">
            <div class="settings-item-label">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <span>Profil beállítások</span>
            </div>
            <div class="settings-item-arrow">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </div>
          </div>
          
          <!-- Dark Mode kapcsoló -->
          <div class="settings-item">
            <div class="settings-item-label">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
              </svg>
              <span>Sötét mód</span>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" :checked="darkMode" @click="toggleDarkMode">
              <span class="toggle-slider"></span>
            </label>
          </div>

          <!-- Tárhely mutató -->
          <div class="settings-item">
            <div class="settings-item-label">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M21 3H3v18h18V3z"></path>
                <path d="M21 12H3"></path>
                <path d="M12 3v18"></path>
              </svg>
              <span>Tárhely használat</span>
            </div>
            <div class="storage-info">
              <div class="storage-bar">
                <div class="storage-used" :style="{ width: `${storageUsed}%` }"></div>
              </div>
              <span>{{ storageUsed }} MB / 100 MB</span>
            </div>
          </div>

          <!-- Kijelentkezés -->
          <div class="settings-item logout-item" @click="logout">
            <div class="settings-item-label">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
              <span>Kijelentkezés</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Profil beállítások dialog -->
    <ProfileSettingsDialog v-if="showProfileSettingsModal" @close="showProfileSettingsModal = false" />
  </div>
</template>

<style lang="scss" scoped>
@import '../styles/SideBar.scss';

/* Új stílusok a beállítások modalhoz */
.settings-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.settings-modal {
  background-color: white;
  border-radius: 16px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  animation: slide-up 0.3s ease-out;
}

.settings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(112, 120, 230, 0.1);
  background: linear-gradient(135deg, #7078e6, #5a63d4);
  color: white;
}

.settings-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: white;
}

.close-button {
  background: none;
  border: none;
  cursor: pointer;
  color: white;
  padding: 8px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.2s;
}

.close-button:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.settings-content {
  padding: 24px;
}

.settings-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 8px;
  border-bottom: 1px solid rgba(112, 120, 230, 0.1);
  transition: background-color 0.2s;
  cursor: pointer;
}

.settings-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.settings-item:hover {
  background-color: rgba(112, 120, 230, 0.05);
}

.settings-item-arrow {
  color: #7078e6;
}

@keyframes slide-up {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.settings-item-label {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #484a6a;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 22px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 2px;
  bottom: 2px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .toggle-slider {
  background-color: #7078e6;
}

input:checked + .toggle-slider:before {
  transform: translateX(22px);
}

.storage-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.storage-bar {
  width: 100px;
  height: 8px;
  background-color: #f0f2ff;
  border-radius: 4px;
  overflow: hidden;
}

.storage-used {
  height: 100%;
  background-color: #7078e6;
  border-radius: 4px;
}

.logout-item {
  cursor: pointer;
  color: #f44336;
}

.logout-item:hover {
  background-color: rgba(244, 67, 54, 0.05);
}
</style>
