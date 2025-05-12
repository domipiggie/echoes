<script setup>
</script>

<template>
  <div class="overlay-container">
    <div class="overlay">
      <div class="overlay-panel overlay-left">
        <h1> "A new era!"</h1>
        <p>Ha még nem regisztráltál, itt megteheted!</p>
        <button class="ghost" @click="$emit('deactivate-right-panel')">
          Regisztráció
        </button>
      </div>
      <div class="overlay-panel overlay-right">
        <h1>"Welcome back, Frank"</h1>
        <p>Van már fiókod? Itt bejelentkezhetsz!</p>
        <button class="ghost" @click="$emit('activate-right-panel')">
          Bejelentkezés
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@media (max-width: 768px) {
  .overlay-container {
    position: static !important;
    width: 100% !important;
    height: auto !important;
    left: auto !important;
    top: auto !important;
    overflow: visible !important;
    z-index: 1 !important;
    transform: none !important;
    transition: none !important;
  }

  .overlay {
    position: static !important;
    width: 100% !important;
    height: auto !important;
    left: 0 !important;
    transform: none !important;
    transition: none !important;
    border-radius: 15px;
    MAX-WIDTH: 400PX;
    MARGIN-LEFT: AUTO;
    MARGIN-RIGHT: AUTO;
  }

  .overlay-panel {
    position: static !important;
    width: 100% !important;
    height: auto !important;
    padding: 30px 20px !important;
    transform: none !important;
    transition: none !important;
    display: block !important;
  }

  .overlay-left {
    display: none !important;
  }

  .overlay-right {
    display: block !important;
    right: auto !important;
  }

  .container.right-panel-active .overlay-right {
    display: none !important;
  }

  .container.right-panel-active .overlay-left {
    display: block !important;
  }

  .ghost {
    margin: 15px 0 5px;
    min-width: 120px;
  }
}
.overlay-container {
  position: absolute;
  top: 0;
  left: 50%;
  width: 50%;
  height: 100%;
  overflow: hidden;
  transition: transform 0.6s ease-in-out;
  z-index: 100;
}

.container.right-panel-active .overlay-container {
  transform: translateX(-100%);
}

.overlay {
  background: #7078e6;
  background: -webkit-linear-gradient(to right, #5a62d3, #7078e6);
  background: linear-gradient(to right, #5a62d3, #7078e6);
  background-repeat: no-repeat;
  background-size: cover;
  background-position: 0 0;
  color: #FFFFFF;
  position: relative;
  left: -100%;
  height: 100%;
  width: 200%;
  transform: translateX(0);
  transition: transform 0.6s ease-in-out;
}

.container.right-panel-active .overlay {
  transform: translateX(50%);
}

.overlay-panel {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 0 40px;
  text-align: center;
  top: 0;
  height: 100%;
  width: 50%;
  transform: translateX(0);
  transition: transform 0.6s ease-in-out;
}

.overlay-left {
  transform: translateX(-20%);
}

.container.right-panel-active .overlay-left {
  transform: translateX(0);
}

.overlay-right {
  right: 0;
  transform: translateX(0);
}

.container.right-panel-active .overlay-right {
  transform: translateX(20%);
}
</style>