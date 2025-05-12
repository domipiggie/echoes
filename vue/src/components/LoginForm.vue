<script setup>
import { ref } from 'vue';

const emits = defineEmits(['login']);

const email = ref("");
const password = ref("");
const errorMessageEmail = ref("");
const errorMessagePassw = ref("");
const errorMessage = ref(false);

const login = () => {
  errorMessage.value = false;
  errorMessageEmail.value = "";
  errorMessagePassw.value = "";

  if (!email.value) {
    errorMessageEmail.value = "Hibás e-mail cím!";
    errorMessage.value = true;
  }
  if (!password.value) {
    errorMessagePassw.value = "Hibás jelszó";
    errorMessage.value = true;
  }

  if (email.value && password.value) {
    emits('login', email.value, password.value);
  }
};
</script>


<template>
  <div class="form-container sign-up-container">
    <form @submit.prevent="login">
      <h1>Üdv újra itt!</h1>
      <span class="alcim">Jelentkezz be gyorsan!</span>
      <input type="text" v-model="email" placeholder="Email cím" :class="{ 'error': errorMessage }"
        style="font-style: italic;" />
      <span v-if="errorMessage" class="error-text">{{ errorMessageEmail }}</span>
      <input type="password" v-model="password" placeholder="Jelszó" :class="{ 'error': errorMessage }"
        style="font-style: italic;" />
      <span v-if="errorMessage" class="error-text">{{ errorMessagePassw }}</span>
      <button type="submit" class="registration">Bejelentkezés</button>
    </form>
  </div>
</template>

<style scoped>
.sign-up-container {
  left: 0;
  width: 50%;
  opacity: 0;
  z-index: 1;
}

.container.right-panel-active .sign-up-container {
  transform: translateX(100%);
  opacity: 1;
  z-index: 5;
  animation: show 0.6s;
}

@keyframes show {

  0%,
  49.99% {
    opacity: 0;
    z-index: 1;
  }

  50%,
  100% {
    opacity: 1;
    z-index: 5;
  }
}

form {
  background-color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 0 50px;
  height: 100%;
  text-align: center;
}

input {
  background-color: #ffffff;
  border: 1px solid #dcdef5;
  border-radius: 5px;
  padding: 8px 12px;
  margin: 8px 0;
  width: 100%;
  height: 36px;
}

input.error {
  border-color: red;
}

.error-text {
  color: red;
  font-size: 14px;
  margin-bottom: 8px;
}

.registration {
  background-color: #7078e6;
}

h1 {
  color: #7078e6;
}

.alcim {
  font-weight: bold;
}

@media (max-width: 768px) {
  .sign-up-container {
    position: static;
    width: 100%;
    height: auto;
    transform: none !important;
    opacity: 1;
    z-index: 2;
    padding: 10px;
  }

  form {
    padding: 25px;
    width: 100%;
    max-width: 400px;
    height: auto;
    min-height: auto;
    margin: 0 auto;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(112, 120, 230, 0.1);
  }

  input {
    max-width: 100%;
  }

  button {
    width: 100%;
    max-width: 200px;
  }
}
</style>
