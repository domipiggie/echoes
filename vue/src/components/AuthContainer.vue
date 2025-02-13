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

<style scoped>

.container {
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0 14px 28px rgba(0,0,0,0.25), 
              0 10px 10px rgba(0,0,0,0.22);
  position: relative;
  overflow: hidden;
  width: 768px;
  max-width: 100%;
  min-height: 480px;
}

.form-container {
  position: absolute;
  top: 0;
  height: 100%;
  transition: all 0.6s ease-in-out;
}

</style>

  
  