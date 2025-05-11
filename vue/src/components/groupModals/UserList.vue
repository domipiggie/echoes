<script setup>
import { useMessageStore } from '../../store/messageStore.js';
import { useChannelStore } from '../../store/channelStore.js';
import { userdataStore } from '../../store/UserdataStore.js';
import { useWebSocketStore } from '../../store/WebSocketStore.js';
import { API_CONFIG } from '../../config/api.js';

const messageStore = useMessageStore();
const channelStore = useChannelStore();
const webSocketStore = useWebSocketStore();
const userStore = userdataStore();

const emit = defineEmits(['close']);

const removeUserFromGroup = (userId) => {
    if (webSocketStore.isConnected) {
        console.log("Removing user from group");
        webSocketStore.send({
            type: 'group_remove_user',
            channelId: messageStore.getCurrentChannelId,
            userId: userId
        });
        channelStore.fetchGroupChannels();
    }
}
</script>

<template>
    <div class="new-group-overlay" @click.self="emit('close')">
        <div class="new-group-dialog">
            <div class="dialog-header">
                <h2>Csoporttagok</h2>
                <button class="close-button" @click="emit('close')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="friends-list-container">
                <div class="friends-list">
                    <div v-for="user in channelStore.getGroupChannelById(messageStore.getCurrentChannelId).getUsers()"
                        class="friend-item">
                        <div class="friend-avatar">
                            <img v-if="user.getProfilePicture()" :src="API_CONFIG.BASE_URL + user.getProfilePicture()" alt="Profilkép" class="avatar-circle" />
                            <span v-else>{{ user.getUserName().charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="friend-info">
                            <div class="friend-name">{{ user.getUserName() }}</div>
                        </div>
                        <div class="selection-indicator" @click="removeUserFromGroup(user.getUserID())" v-if="user.getUserID() != userStore.getUserID() && userStore.getUserID() == channelStore.getGroupChannelById(messageStore.getCurrentChannelId).getOwnerID()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="#FF0000" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
@import '../../styles/chat/Avatar.scss';

.new-group-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.new-group-dialog {
    background: white;
    border-radius: 12px;
    padding: 24px;
    width: 450px;
    max-width: 90%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    max-height: 80vh;
    display: flex;
    flex-direction: column;

    .dark-mode & {
        background: #1e1e2d;
        color: #e0e0e0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }
}

.dialog-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

h2 {
    color: #484a6a;
    margin: 0;

    .dark-mode & {
        color: #e0e0e0;
    }
}

.close-button {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f0f2f5;
    color: #484a6a;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;

    .dark-mode & {
        background: #2a2a3d;
        color: #e0e0e0;
    }
}

.close-button:hover {
    background: #e4e6eb;
    transform: scale(1.05);
}

.input-container {
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 12px;
    border: 1px solid #e6e8f0;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s ease;
    background: #f8f9fc;

    .dark-mode & {
        background: #2a2a3d;
        border-color: #3a3a4f;
        color: #e0e0e0;

        &::placeholder {
            color: #8a8a9a;
        }
    }
}

input:focus {
    border-color: #7078e6;
    box-shadow: 0 0 0 3px rgba(112, 120, 230, 0.2);

    .dark-mode & {
        border-color: #7078e6;
        box-shadow: 0 0 0 3px rgba(112, 120, 230, 0.3);
    }
}

.friends-list-container {
    margin-bottom: 20px;
    overflow-y: auto;
    flex-grow: 1;
}

h3 {
    color: #484a6a;
    font-size: 16px;
    margin: 0 0 12px 0;

    .dark-mode & {
        color: #e0e0e0;
    }
}

.friends-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e6e8f0;
    border-radius: 8px;

    .dark-mode & {
        border-color: #3a3a4f;
    }
}

.friend-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid #e6e8f0;
    cursor: pointer;
    transition: background-color 0.2s ease;

    .dark-mode & {
        border-bottom-color: #3a3a4f;
    }
}

.friend-item:last-child {
    border-bottom: none;
}

.friend-item:hover {
    background-color: #f8f9fc;

    .dark-mode & {
        background-color: #333345;
    }
}

.friend-item.selected {
    background-color: rgba(112, 120, 230, 0.1);

    .dark-mode & {
        background-color: rgba(112, 120, 230, 0.2);
    }
}

.friend-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #7078e6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;

    .dark-mode & {
        background-color: #5a62d3;
    }
}

.friend-info {
    flex-grow: 1;
}

.friend-name {
    font-weight: 500;
    color: #484a6a;

    .dark-mode & {
        color: #e0e0e0;
    }
}

.selection-indicator {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid #e6e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;

    .dark-mode & {
        border-color: #3a3a4f;
    }
}

.friend-item.selected .selection-indicator {
    background-color: #7078e6;
    border-color: #7078e6;
}

.no-friends-message {
    padding: 20px;
    text-align: center;
    color: #6b7280;
    font-style: italic;

    .dark-mode & {
        color: #8a8a9a;
    }
}

.error-message {
    color: #e53e3e;
    font-size: 14px;
    margin-bottom: 20px;

    .dark-mode & {
        color: #ff6b6b;
    }
}

.button-container {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.cancel-button,
.create-button {
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    position: relative;
    overflow: hidden;
}

.cancel-button {
    background: #f0f2f5;
    color: #484a6a;

    .dark-mode & {
        background: #2a2a3d;
        color: #e0e0e0;
    }
}

.cancel-button:hover {
    background: #e4e6eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

    .dark-mode & {
        background: #333345;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }
}

.create-button {
    background: #7078e6;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;

    .dark-mode & {
        background: #5a62d3;
    }
}

.create-button:hover {
    background: #5a62d3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(112, 120, 230, 0.4);

    .dark-mode & {
        background: #4e55df;
        box-shadow: 0 4px 12px rgba(112, 120, 230, 0.5);
    }
}

.create-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.loading-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s linear infinite;

    .dark-mode & {
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-top-color: white;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>