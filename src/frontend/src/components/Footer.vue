<template>
  <footer class="sticky-footer">
    <div class="container my-auto">
      <div class="copyright text-center my-auto">
        <span>&copy; {{ year }} Financial Manager</span>
        <div class="mt-2 links">
          <a href="#" class="text-primary">Privacy Policy</a>
          <a href="#" class="text-primary">Terms of Service</a>
          <a href="#" class="text-primary">Contact</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  data: () => ({ year: new Date().getFullYear() })
};
</script>

<style scoped>
.sticky-footer {
  background-color: #f8f9fa; /* Adjust to match your CI3 footer.php styles */
  padding: 1rem 0;
}
.links a {
  margin: 0 10px;
}
</style>