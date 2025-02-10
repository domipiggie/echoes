<script setup>
  import { ref } from 'vue'
  import LoginForm from './LoginForm.vue'
  import RegisterForm from './RegisterForm.vue'
  import OverlayContainer from './OverlayContainer.vue'
  
  const isRightPanelActive = ref(false)
  
  const activateRightPanel = () => {
    isRightPanelActive.value = true
  }
  
  const deactivateRightPanel = () => {
    isRightPanelActive.value = false
  }
  </script>

<template>
    <div class="container" :class="{ 'right-panel-active': isRightPanelActive }">
      <LoginForm />
      <RegisterForm />
      <OverlayContainer 
        @activate-right-panel="activateRightPanel"
        @deactivate-right-panel="deactivateRightPanel"
      />
    </div>
  </template>
  
  