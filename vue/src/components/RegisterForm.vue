<script setup>
</script>

<template>
    <div class="form-container sign-in-container">
      <form action="#">
        <h1>Mielőtt chatelnél...</h1>
        <span>Regisztrálj egy fiókot pár kattintással!</span>
        <input type="text" placeholder="Felhasználónév" />
        <input type="date" placeholder="Születési dátum" />
        <input type="email" placeholder="email cím" />
        <input type="password" placeholder="jelszó" />
        <input type="password" placeholder="jelszó újra" />
        <a href="#">Elfelejtetted a jelszavad?</a>
        <button>Regisztráció</button>
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
    background-color: #eee;
    border: none;
    padding: 12px 15px;
    margin: 8px 0;
    width: 100%;
  }

  </style>

