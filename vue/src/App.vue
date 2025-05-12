<script setup>
import AuthContainer from './components/AuthContainer.vue'
import Chat from './components/Chat.vue'
import AlertBox from './components/AlertBox.vue';
import { useAlertStore } from './store/AlertStore';

const alertStore = useAlertStore();
</script>

<template>
  <AlertBox v-for="alert in alertStore.getAlerts" :alert="alert" @close="alertStore.removeAlert(alert)"/>
  <router-view />
</template>

<style>
body {
  margin: 0;
  padding: 0;
  background-color: #ffffff;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  transition: background-color 0.3s ease, color 0.3s ease;
}

body.dark-mode {
  background-color: #1e1e2e;
  color: #e0e0e0;
}

#app {
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.auth-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  width: 100%;
}
</style>
