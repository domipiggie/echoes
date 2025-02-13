<script>

</script>

<template>
    <div class="form-container sign-up-container">
      <form action="#">
        <h1>Üdv újra itt!</h1>
        <span>Jelentkezz be gyorsan!</span>
        <input type="text" placeholder="Felhasználónév vagy email" />
        <input type="password" placeholder="Jelszó" />
        <button>Bejelentkezés</button>
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
  background-color: #eee;
  border: none;
  padding: 12px 15px;
  margin: 8px 0;
  width: 100%;
}

</style>

