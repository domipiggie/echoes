<script setup>
import { ref, defineEmits } from 'vue';

const emits = defineEmits(['register']);

const username = ref("");
const birthdate = ref("");
const email = ref("");
const password1 = ref("");
const password2 = ref("");

const errors = ref({
  username: false,
  birthdate: false,
  email: false,
  password1: false,
  password2: false,
});

const validate = () => {
  errors.value.username = !username.value.trim();
  errors.value.birthdate = !birthdate.value;
  errors.value.email = !email.value.includes("@");
  errors.value.password1 = password1.value.length < 6;
  errors.value.password2 = password2.value !== password1.value;
  
  return !Object.values(errors.value).includes(true);
};

const register = () => {
  if (validate()) {
    emits('register', username.value, birthdate.value, email.value, password1.value);
  }
};
</script>

<template>
  <div class="form-container sign-in-container">
    <form @submit.prevent="register">
      <h1>Mielőtt chatelnél...</h1>
      <span class="alcim">Regisztrálj egy fiókot pár kattintással!</span>
      
      <input type="text" v-model="username" placeholder="Felhasználónév" :class="{ 'error': errors.username }" style="font-style: italic;"/>
      <span v-if="errors.username" class="error-text">Hibás!</span>

      <input type="date" v-model="birthdate" placeholder="Születési dátum" :class="{ 'error': errors.birthdate }" style="font-style: italic;"/>
      <span v-if="errors.birthdate" class="error-text">Érvénytelen dátum!</span>

      <input type="email" v-model="email" placeholder="Email cím" :class="{ 'error': errors.email }" style="font-style: italic;"/>
      <span v-if="errors.email" class="error-text">Érvénytelen email!</span>

      <input type="password" v-model="password1" placeholder="Jelszó" :class="{ 'error': errors.password1 }" style="font-style: italic;"/>
      <span v-if="errors.password1" class="error-text">Hiba! (Minimum 6 karakter szükséges)</span>

      <input type="password" v-model="password2" placeholder="Jelszó újra" :class="{ 'error': errors.password2 }" style="font-style: italic;"/>
      <span v-if="errors.password2" class="error-text">Hiba! (Nem egyezik az első jelszóval)</span>

      <a href="#">Elfelejtetted a jelszavad?</a>
      <button type="submit" class="registration">Regisztráció</button>
    </form>
  </div>
</template>

<style scoped>
.sign-in-container {
  left: 0;
  width: 50%;
  z-index: 2;
}

.container.right-panel-active .sign-in-container {
  transform: translateX(100%);
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
  font-size: 12px;
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


