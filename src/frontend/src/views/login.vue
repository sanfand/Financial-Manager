<template>
  <div class="container py-5">
    <h2 class="mb-4 text-gray-800">Login</h2>
    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ errorMessage }}
      <button type="button" class="btn-close" @click="errorMessage=''"></button>
    </div>
    <form @submit.prevent="login">
      <div class="mb-3">
        <label for="usernameEmail" class="form-label">Username or Email</label>
        <input type="text" v-model.trim="usernameEmail" class="form-control" id="usernameEmail" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" v-model.trim="password" class="form-control" id="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-sm" :disabled="isLoading">
        {{ isLoading ? 'Logging in...' : 'Login' }}
      </button>
      <router-link to="/register" class="btn btn-link">Register</router-link>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';


axios.defaults.withCredentials = true; // 👈 add this line here




export default {
  data() {
    return {
      usernameEmail: '',
      password: '',
      errorMessage: '',
      isLoading: false
    };
  },
    
  setup() {
    const router = useRouter();
    return { router };
  },
  methods: {
    
    async login() {
      this.errorMessage = '';
      this.isLoading = true;

      // Debug: Log form data before sending
      console.log('Form data being sent:', {
        username_email: this.usernameEmail,
        password: this.password
      });

      try {
        // Use FormData for application/x-www-form-urlencoded
        const formData = new FormData();
        formData.append('username_email', this.usernameEmail);
        formData.append('password', this.password);

        const response = await axios.post('/api/auth/login', formData, {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        console.log('Response received:', response); // Debug: Log full response

        if (response.data.status === 'success') {
          this.router.push('/dashboard');
        } else {
          this.errorMessage = response.data.message || 'Login failed. Please try again.';
          setTimeout(() => { this.errorMessage = ''; }, 5000);
        }
      } catch (e) {
        console.error('Login error details:', e.response || e.message); // Debug: Log full error
        this.errorMessage = e.response?.data?.message || 'Server error or unexpected response. Please try again.';
        setTimeout(() => { this.errorMessage = ''; }, 5000);
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
.container {
  width: 100%;
  max-width: 600px;
  margin: auto;
}
.text-gray-800 {
  color: #343a40;
}
</style>