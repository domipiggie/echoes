<script>
export default {
  name: 'App'
}
</script>

<template>
  <div id="app">
    <router-view/>
  </div>
  <footer>
    <p>Copyright - All rights reserved! &copy;</p>
  </footer>
</template>

<style scoped>
footer {
  background-color: #222;
  color: #fff;
  font-size: 14px;
  bottom: 0;
  position: fixed;
  left: 0;
  right: 0;
  text-align: center;
  z-index: 999;
}

footer p {
  margin: 10px 0;
}

footer a , i{
  color: #3c97bf;
  text-decoration: none;
}
</style>


