<template>
  <div class="container py-5">
    <h2 class="mb-4 text-gray-800">Login Debug</h2>
    
    <div v-if="debugResult" class="alert alert-info">
      <pre>{{ JSON.stringify(debugResult, null, 2) }}</pre>
    </div>
    
    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
    </div>
    
    <form @submit.prevent="login">
      <div class="mb-3">
        <label for="usernameEmail" class="form-label">Email or Name</label>
        <input type="text" v-model.trim="usernameEmail" class="form-control" id="usernameEmail" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" v-model.trim="password" class="form-control" id="password" required>
      </div>
      <button type="submit" class="btn btn-primary" :disabled="isLoading">
        {{ isLoading ? 'Logging in...' : 'Login' }}
      </button>
    </form>

    <div class="mt-4">
      <button @click="createTestUser" class="btn btn-success me-2">Create Test User</button>
      <button @click="debugUsers" class="btn btn-warning me-2">Show All Users</button>
      <button @click="debugTokens" class="btn btn-info me-2">Show Tokens</button>
      <button @click="resetPassword" class="btn btn-danger">Reset Password</button>
    </div>

    <div v-if="usersList" class="mt-3">
      <h5>All Users in Database:</h5>
      <pre>{{ JSON.stringify(usersList, null, 2) }}</pre>
    </div>

    <div v-if="tokensList" class="mt-3">
      <h5>All Tokens in Database:</h5>
      <pre>{{ JSON.stringify(tokensList, null, 2) }}</pre>
    </div>
  </div>
</template>

<script>
import axios from '../utils/auth';
import { setToken } from '../utils/auth';

export default {
  data() {
    return {
      usernameEmail: '',
      password: '',
      errorMessage: '',
      isLoading: false,
      debugResult: null,
      usersList: null,
      tokensList: null
    };
  },
  methods: {
    async login() {
      this.debugResult = null;
      this.errorMessage = '';
      this.isLoading = true;

      console.log('Login attempt:', {
        username_email: this.usernameEmail,
        password: this.password
      });

      try {
        const response = await axios.post('/auth/login', {
          username_email: this.usernameEmail,
          password: this.password
        });

        console.log('Login response:', response.data);
        
        if (response.data.status === 'success') {
          const token = response.data.token;
          console.log('Login successful, token:', token);
          setToken(token);
          localStorage.setItem('user', JSON.stringify(response.data.user));
          alert('LOGIN SUCCESS! Token: ' + token);
          this.$router.push('/dashboard');
        } else {
          this.errorMessage = response.data.message || 'Login failed';
          console.error('Login failed:', response.data);
        }
      } catch (e) {
        console.error('Login error:', e);
        this.errorMessage = e.response?.data?.message || 'Server error';
        if (e.response) {
          console.error('Error response:', e.response.data);
        }
      } finally {
        this.isLoading = false;
      }
    },

    async createTestUser() {
      try {
        const response = await axios.post('/auth/create_test_user');
        console.log('Create user response:', response.data);
        alert(response.data.status === 'success' ? 'User created!' : 'Error: ' + response.data.message);
      } catch (e) {
        console.error('Create user error:', e);
        alert('Error creating user');
      }
    },

    async debugUsers() {
      try {
        const response = await axios.get('/auth/debug_users');
        console.log('All users:', response.data);
        this.usersList = response.data.users || [];
      } catch (e) {
        console.error('Debug users error:', e);
      }
    },

    async debugTokens() {
      try {
        const response = await axios.get('/auth/debug_tokens');
        console.log('All tokens:', response.data);
        this.tokensList = response.data.tokens || [];
      } catch (e) {
        console.error('Debug tokens error:', e);
      }
    },

    async resetPassword() {
      const newPassword = prompt('Enter new password for "sana":', '1234567');
      if (!newPassword) return;
      
      try {
        const response = await axios.post('/auth/reset_password', {
          email: 'sana',
          new_password: newPassword
        });
        console.log('Reset password response:', response.data);
        alert(response.data.message);
        this.password = newPassword;
      } catch (e) {
        console.error('Reset password error:', e);
        alert('Error resetting password');
      }
    }
  },

  mounted() {
    localStorage.clear();
    console.log('Storage cleared');
  }
};
</script>

<style scoped>
.container {
  max-width: 800px;
}
pre {
  background: #f8f9fa;
  padding: 10px;
  border-radius: 5px;
  font-size: 12px;
}
</style>