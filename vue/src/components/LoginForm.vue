<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const email = ref("");
const password = ref("");
const errorMessage = ref("");

const login = () => {
  if (!email.value || !password.value) {
    errorMessage.value = "Hibás adatok!";
    return;
  }
  router.push('/chat');
};
</script>


<template>
  <div class="form-container sign-up-container">
    <form @submit.prevent="login">
      <h1>Üdv újra itt!</h1>
      <span class="alcim">Jelentkezz be gyorsan!</span>
      <input type="text" v-model="email" placeholder="Felhasználónév vagy email" :class="{ 'error': errorMessage }" style="font-style: italic;"/>
      <input type="password" v-model="password" placeholder="Jelszó" :class="{ 'error': errorMessage }" style="font-style: italic;"/>
      <span v-if="errorMessage" class="error-text">{{ errorMessage }}</span>
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
  0%, 49.99% {
    opacity: 0;
    z-index: 1;
  }
  50%, 100% {
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
  border: none;
  padding: 12px 15px;
  margin: 8px 0;
  width: 100%;
  border: 1px solid #dcdef5;
  border-radius: 5px;
}

input.error {
  border-color: red;
}

.error-text {
  color: red;
  font-size: 14px;
  margin-bottom: 8px;
}
.registration{
  background-color: #7078e6;
}
h1{
  color: #7078e6;
}
.alcim{
  font-weight: bold;
}
</style>

