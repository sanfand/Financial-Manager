<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="card-header">
        <h2>Welcome Back</h2>
        <p>Sign in to your account</p>
      </div>
      <div class="card-body">
        <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ errorMessage }}
          <button type="button" class="btn-close" @click="errorMessage = ''"></button>
        </div>
        <form @submit.prevent="login">
          <div class="form-group">
            <label for="usernameEmail">Email or Username</label>
            <input type="text" v-model.trim="usernameEmail" class="form-control" id="usernameEmail" required>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" v-model.trim="password" class="form-control" id="password" required>
          </div>
          <button type="submit" class="auth-btn" :disabled="isLoading">
            {{ isLoading ? 'Signing In...' : 'Sign In' }}
          </button>
        </form>
        <div class="auth-footer">
          <p>Don't have an account? <router-link to="/register" class="auth-link">Create account</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { setToken } from '../utils/auth';

export default {
  data() {
    return {
      usernameEmail: '',
      password: '',
      errorMessage: '',
      isLoading: false
    };
  },
  methods: {
    async login() {
      this.errorMessage = '';
      this.isLoading = true;

      try {
        const response = await axios.post('api/auth/login', {
          username_email: this.usernameEmail,
          password: this.password
        });

        if (response.data.status === 'success') {
          const token = response.data.token;
          setToken(token);
          localStorage.setItem('user', JSON.stringify(response.data.user));
          
          // Force page reload to update navbar and state
          window.location.href = '/dashboard';
        } else {
          this.errorMessage = response.data.message || 'Login failed';
        }
      } catch (e) {
        console.error('Login error:', e);
        this.errorMessage = e.response?.data?.message || 'Server error';
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #8496e9 0%, #b483e6 100%);
  padding: 20px;
}

.auth-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 450px;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.auth-card:hover {
  transform: translateY(-5px);
}

.card-header {
  background: linear-gradient(135deg, #7d9aa5, #b5ead7);
  padding: 40px 40px 30px;
  text-align: center;
  color: #2c3e50;
}

.card-header h2 {
  margin: 0 0 10px 0;
  font-size: 2rem;
  font-weight: 700;
}

.card-header p {
  margin: 0;
  opacity: 0.8;
  font-size: 1.1rem;
}

.card-body {
  padding: 40px;
}

.form-group {
  margin-bottom: 25px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
}

.form-control {
  width: 100%;
  padding: 15px;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #a8d8ea;
  box-shadow: 0 0 0 3px rgba(168, 216, 234, 0.2);
}

.auth-btn {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.auth-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.auth-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.auth-footer {
  text-align: center;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #e9ecef;
}

.auth-footer p {
  margin: 0;
  color: #6c757d;
}

.auth-link {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.auth-link:hover {
  text-decoration: underline;
}

.alert {
  border-radius: 10px;
  border: none;
  padding: 15px;
  margin-bottom: 25px;
}

.alert-danger {
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}
</style>