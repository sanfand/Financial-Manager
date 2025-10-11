<template>
  <div>
    <Header />
    <router-view />
    <Footer />
  </div>
  
</template>

<script>
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
export default {
  components: { Header, Footer },
}
</script>



<style>
body {
  background-color: #f4f6f9;
}
</style>
