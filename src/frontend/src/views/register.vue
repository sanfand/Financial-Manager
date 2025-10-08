<template>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark text-center">Register</h6>
          </div>
          <div class="card-body">
            <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ errorMessage }}
              <button type="button" class="btn-close" @click="errorMessage = ''"></button>
            </div>
            <form @submit.prevent="register">
              <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" v-model.trim="name" id="name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" v-model.trim="email" id="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" v-model.trim="password" id="password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirm Password</label>
                <input type="password" v-model.trim="passwordConfirm" id="password_confirm" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary btn-block" :disabled="isLoading">
                {{ isLoading ? 'Registering...' : 'Register' }}
              </button>
            </form>
            <div class="mt-3 text-center">
              <small>Already have an account? <router-link to="/login">Login here</router-link></small>
            </div>
          </div>
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

      // Validation
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

      console.log('Form data being sent:', {
        name: this.name,
        email: this.email,
        password: this.password,
        password_confirm: this.passwordConfirm
      });

      try {
        const response = await axios.post('/auth/register', {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirm: this.passwordConfirm
        }, {
          headers: { 
            'Content-Type': 'application/json'
          }
        });

        console.log('Response received:', response);

        if (response.data.status === 'success') {
          this.$router.push('/login');
        } else {
          this.errorMessage = response.data.message || 'Registration failed. Please try again.';
          setTimeout(() => { this.errorMessage = ''; }, 5000);
        }
      } catch (e) {
        console.error('Registration error details:', e.response || e.message);
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
.text-dark {
  color: #343a40;
}
.card {
  border: none;
  border-radius: 10px;
}
.card-header {
  background: #f8f9fa;
}
</style>