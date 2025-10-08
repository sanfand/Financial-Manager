import { createRouter, createWebHistory } from 'vue-router';
import { getToken } from '../utils/auth';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: () => import('../views/login.vue') },
  { path: '/register', component: () => import('../views/register.vue') },
  { path: '/dashboard', component: () => import('../views/dashboard.vue') },
  { path: '/categories', component: () => import('../views/categories.vue') },
  { path: '/transactions', component: () => import('../views/transactions.vue') },
  { path: '/profile', component: () => import('../views/profile.vue') },
  {
    path: '/logout',
    beforeEnter: async (to, from, next) => {
      await axios.post('/auth/logout');
      next('/login');
    }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Simplified guard: Only check token presence
router.beforeEach((to, from, next) => {
  const protectedRoutes = ['/dashboard', '/categories', '/transactions', '/profile'];
  if (protectedRoutes.includes(to.path) && !getToken()) {
    next('/login');
  } else {
    next();
  }
});

export default router;