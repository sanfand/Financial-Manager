<template>
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
      <router-link class="navbar-brand" to="/">Financial Manager</router-link>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navmenu">
        <ul class="navbar-nav ms-auto">
          <li v-if="isLoggedIn" class="nav-item"><router-link class="nav-link" to="/dashboard">Dashboard</router-link></li>
          <li v-if="isLoggedIn" class="nav-item"><router-link class="nav-link" to="/transactions">Transactions</router-link></li>
          <li v-if="isLoggedIn" class="nav-item"><router-link class="nav-link" to="/categories">Categories</router-link></li>
          <li v-if="isLoggedIn" class="nav-item"><router-link class="nav-link" to="/profile">Profile</router-link></li>
          <li v-if="isLoggedIn" class="nav-item"><a class="nav-link" href="#" @click.prevent="logout">Logout</a></li>
          <template v-else>
            <li class="nav-item"><router-link class="nav-link" to="/login">Login</router-link></li>
            <li class="nav-item"><router-link class="nav-link" to="/register">Register</router-link></li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      isLoggedIn: false
    };
  },
  async created() {
    await this.checkAuth();
  },
  watch: {
    $route: {
      handler: async function() {
        await this.checkAuth();
      },
      immediate: true
    }
  },
  methods: {
    async checkAuth() {
      try {
        const res = await axios.get('/api/auth/check', {
          withCredentials: true  // Ensure cookies are sent
        });
        this.isLoggedIn = res.data.status === 'success' && res.data.is_logged_in;
      } catch {
        this.isLoggedIn = false;
      }
    },
    async logout() {
      try {
        const res = await axios.post('/api/auth/logout', {}, {
          withCredentials: true
        });
        if (res.data.status === 'success') {
          this.isLoggedIn = false;
          this.$router.push('/login');
        }
      } catch (e) {
        console.error('Logout error:', e);
        this.isLoggedIn = false;
        this.$router.push('/login');
      }
    }
  }
};
</script>

<style scoped>
.navbar {
  background-color: #343a40;
}
.navbar-brand, .nav-link {
  color: #fff;
}
.navbar-brand:hover, .nav-link:hover {
  color: #a8d8ea;
}
</style>