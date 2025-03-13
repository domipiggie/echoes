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
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

#app {
  width: 100%;
  height: 100%;
  overflow: hidden;
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
  width: 100vw;
  height: 100vh;
  background-color: #18191a;  /* Changed to match body background */
  overflow: hidden;
  display: flex;
}

@media (min-width: 1600px) {
  .floating-container {
    width: 95%;
    max-width: 1450px;
    height: 95vh;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  }
}


@media (max-width: 768px) {
  .floating-container {
    width: 100%;
    height: 100vh;
    border-radius: 0;
  }
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
