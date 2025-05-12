<script setup>
import { onMounted, ref } from 'vue';
import { useFileStore } from '../store/FileStore';
import { API_CONFIG } from '../config/api';
import { fileService } from '../services/fileService';
import { useAlertStore } from '../../store/AlertStore.js';
import Alert from '../../classes/Alert';

const fileStore = useFileStore();
const alertStore = useAlertStore();
const emit = defineEmits(['close']);
const isDeleting = ref(false);

onMounted(() => {
    fileStore.fetchFiles();
});

const deleteFile = (fileId) => {
    alertStore.addAlert(new Alert(
        'Megerősítés',
        'Biztosan el szeretnéd távolítani ezt a fájlt?',
        'confirm',
        () => deleteFileCallback(fileId)
    ));
}
const deleteFileCallback = async (fileId) => {
    try {
        isDeleting.value = true;
        await fileService.deleteFile(fileId);
        fileStore.removeFileByID(fileId);
    } catch (error) {
        console.error('Error deleting file:', error);
        alert('Failed to delete file. Please try again.');
    } finally {
        isDeleting.value = false;
    }
};
</script>

<template>
    <div class="new-group-overlay" @click.self="emit('close')">
        <div class="new-group-dialog">
            <div class="dialog-header">
                <h2>Feltöltött fileok</h2>
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
                    <div v-for="file in fileStore.getFiles"
                        class="friend-item">
                        <div class="friend-avatar">
                            <a :href="API_CONFIG.BASE_URL + '/files/' + file.getUniqueName()" target="_blank">
                                <img :src="API_CONFIG.BASE_URL + '/files/' + file.getUniqueName()"/>
                            </a>
                        </div>
                        <div class="friend-info">
                            <div class="friend-name">{{ file.getFileName() }}</div>
                            <span>{{ fileStore.formatFileSize(file.getSize()) }}</span>
                        </div>
                        <div class="selection-indicator" @click="deleteFile(file.getFileID())" :class="{ 'disabled': isDeleting }">
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
@import '../styles/chat/Avatar.scss';

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
    width: 65vw;
    min-width: 350px;
    max-width: 600px;
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
    border-radius: 10%;
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
    max-height: 65vh;
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
    min-width: 40px;
    border-radius: 10%;
    background-color: #7078e6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;
    overflow: hidden;

    .dark-mode & {
        background-color: #5a62d3;
    }

    a {
        display: block;
        width: 100%;
        height: 100%;
    }

    img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
}

.friend-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.friend-info {
    flex-grow: 1;
    min-width: 0;
    overflow: hidden;
}

.friend-name {
    font-weight: 500;
    color: #484a6a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: calc(100% - 10px);

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
    flex-shrink: 0;
    margin-left: 8px;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
        background-color: rgba(255, 0, 0, 0.1);
        transform: scale(1.1);
    }

    &.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .dark-mode & {
        border-color: #3a3a4f;
    }
}

.friend-avatar {
    width: 40px;
    height: 40px;
    min-width: 40px;
    background-color: #7078e6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 12px;
    overflow: hidden;

    .dark-mode & {
        background-color: #5a62d3;
    }

    a {
        display: block;
        width: 100%;
        height: 100%;
    }

    img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
}
</style>