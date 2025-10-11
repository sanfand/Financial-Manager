<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="card-header">
        <h2>Create Account</h2>
        <p>Join us today</p>
      </div>
      <div class="card-body">
        <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ errorMessage }}
          <button type="button" class="btn-close" @click="errorMessage = ''"></button>
        </div>
        <form @submit.prevent="register">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" v-model.trim="name" id="name" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" v-model.trim="email" id="email" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" v-model.trim="password" id="password" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" v-model.trim="passwordConfirm" id="password_confirm" class="form-control" required>
          </div>
          <button type="submit" class="auth-btn" :disabled="isLoading">
            {{ isLoading ? 'Creating Account...' : 'Create Account' }}
          </button>
        </form>
        <div class="auth-footer">
          <p>Already have an account? <router-link to="/login" class="auth-link">Sign in</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../utils/auth';

export default {
  data() {
    return {
      name: '',
      email: '',
      password: '',
      passwordConfirm: '',
      errorMessage: '',
      isLoading: false
    };
  },
  methods: {
    async register() {
      this.errorMessage = '';
      this.isLoading = true;

      if (this.password !== this.passwordConfirm) {
        this.errorMessage = 'Passwords do not match';
        this.isLoading = false;
        return;
      }

      if (this.password.length < 6) {
        this.errorMessage = 'Password must be at least 6 characters';
        this.isLoading = false;
        return;
      }

      try {
        const response = await axios.post('/auth/register', {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirm: this.passwordConfirm
        });

        if (response.data.status === 'success') {
          this.$router.push('/login');
        } else {
          this.errorMessage = response.data.message || 'Registration failed. Please try again.';
        }
      } catch (e) {
        console.error('Registration error:', e);
        this.errorMessage = e.response?.data?.message || 'Server error. Please try again.';
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
  max-width: 480px;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.auth-card:hover {
  transform: translateY(-5px);
}

.card-header {
  background: linear-gradient(135deg,  #7d9aa5, #b5ead7);
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
  background: linear-gradient(135deg, #7689dd, #b281e4);
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