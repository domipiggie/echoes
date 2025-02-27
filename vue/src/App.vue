<script setup>
import Chat from './components/Chat.vue'
</script>

<template>
  <div class="app-container">
    <div class="floating-container">
      <Chat />
    </div>
  </div>
</template>

<style>
body {
  margin: 0;
  padding: 0;
  background-color: #18191a;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.app-container {
  width: 100%;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  box-sizing: border-box;
}

.floating-container {
  width: 1450px;  /* Increased from 1200px */
  height: 95vh;
  background-color: #242526;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  display: flex;
}

/* Custom Scrollbar Styles */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #242526;
}

::-webkit-scrollbar-thumb {
  background: #3a3b3c;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #4a4b4c;
}
</style>
