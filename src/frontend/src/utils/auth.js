import axios from 'axios';
import router from '../router';

const API_BASE = '/api';

const api = axios.create({
  baseURL: API_BASE,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json'
  }
});

// --- Token management ---
export function setToken(token) {
  localStorage.setItem('token', token);
  api.defaults.headers.common['Authorization'] = 'Bearer ' + token;
}

export function getToken() {
  return localStorage.getItem('token');
}

export function clearToken() {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  delete api.defaults.headers.common['Authorization'];
}

export async function logout() {
  try {
    await api.post('/auth/logout');
  } catch (e) {
    console.warn('Logout API failed', e);
  }
  clearToken();
  router.push('/login');
}

// Set token from localStorage on initial load
const savedToken = getToken();
if (savedToken) {
  setToken(savedToken);
}

// --- API calls ---
export async function login(payload) {
  try {
    const response = await api.post('/auth/login', payload);
    if (response.data.status === 'success') {
      setToken(response.data.token);
    }
    return response.data;
  } catch (error) {
    console.error('Login error:', error);
    throw error;
  }
}

export async function register(payload) {
  const response = await api.post('/auth/register', payload);
  return response.data;
}

export async function resetPassword(payload) {
  const response = await api.post('/auth/reset_password', payload);
  return response.data;
}

export default api;