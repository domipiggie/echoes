<script setup>
defineProps({
  currentChat: {
    type: Object,
    required: true
  }
});
</script>

<template>
  <div class="profile-sidebar">
    <div class="profile-header">
      <div class="profile-image">
        <img src="" alt="" />
      </div>
      <h2>{{ currentChat.name }}</h2>
      <div class="last-seen">Elérhető volt: {{ currentChat.lastSeen }}</div>
    </div>

    <div class="profile-section">
      <h3>Chat testreszabása</h3>
      <button class="profile-button">
        <span class="button-icon">🎨</span>
        Téma megváltoztatása
      </button>
      <button class="profile-button">
        <span class="button-icon">😊</span>
        Hangulatjel megváltoztatása
      </button>
      <button class="profile-button">
        <span class="button-icon">Aa</span>
        Becenevek módosítása
      </button>
    </div>

    <div class="profile-section">
      <h3>Médiatartalom és fájlok</h3>
      <div class="media-grid">
        <div class="media-placeholder"></div>
        <div class="media-placeholder"></div>
        <div class="media-placeholder"></div>
      </div>
    </div>

    <div class="profile-section">
      <h3>Személyes adatok védelme és támogatás</h3>
    </div>
  </div>
</template>

<style scoped>
.profile-sidebar {
  width: 360px;
  background-color: #242526;
  border-left: 1px solid #393a3b;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.profile-header {
  padding: 24px;
  text-align: center;
  border-bottom: 1px solid #393a3b;
}

.profile-image {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background-color: #3a3b3c;
  margin: 0 auto 16px;
}

.profile-header h2 {
  margin: 0;
  color: #e4e6eb;
  font-size: 20px;
}

.last-seen {
  color: #b0b3b8;
  font-size: 13px;
  margin-top: 4px;
}

.profile-section {
  padding: 16px;
  border-bottom: 1px solid #393a3b;
}

.profile-section h3 {
  color: #e4e6eb;
  font-size: 16px;
  margin: 0 0 12px 0;
}

.profile-button {
  width: 100%;
  padding: 8px 12px;
  background: none;
  border: none;
  color: #e4e6eb;
  font-size: 15px;
  text-align: left;
  cursor: pointer;
  display: flex;
  align-items: center;
  border-radius: 8px;
  margin-bottom: 4px;
}

.profile-button:hover {
  background-color: #3a3b3c;
}

.button-icon {
  margin-right: 12px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}

.media-placeholder {
  aspect-ratio: 1;
  background-color: #3a3b3c;
  border-radius: 8px;
}
</style>