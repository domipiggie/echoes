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





 