import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: () => import('../views/login.vue') },
  { path: '/register', component: () => import('../views/register.vue') },
  { path: '/dashboard', component: () => import('../views/dashboard.vue') },
  { path: '/categories', component: () => import('../views/categories.vue') },
  { path: '/transactions', component: () => import('../views/transactions.vue') },
  { path: '/profile', component: () => import('../views/profile.vue') },
  {
    path: '/logout',
    beforeEnter: async (to, from, next) => {
      try {
        const res = await axios.post('/api/auth/logout', {}, { withCredentials: true });
        if (res.data.status === 'success') {
          next('/login');
        } else {
          next('/login');
        }
      } catch {
        next('/login');
      }
    }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Global auth guard
router.beforeEach(async (to, from, next) => {
  const protectedRoutes = ['/dashboard', '/categories', '/transactions', '/profile'];
  if (protectedRoutes.includes(to.path)) {
    try {
      const res = await axios.get('/api/auth/check', { withCredentials: true });

        const loggedIn = res.data.is_logged_in || (res.data.message === 'Already logged in');

      if (res.data.status === 'success' && loggedIn) {
        next();
      } else {
        next('/login');
      }
    } catch {
      next('/login');
    }
  } else {
    next();
  }
});

export default router;
