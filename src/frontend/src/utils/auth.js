import axios from 'axios';
import router from '../router';

const API_BASE = 'http://localhost:8000/index.php/'; // Added /index.php/ to bypass rewrite issues

// Create axios instance with proper configuration
const instance = axios.create({
  baseURL: API_BASE,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  withCredentials: true // Important for CORS with credentials
});

export const getToken = () => localStorage.getItem('token');

export const setToken = (token) => {
  if (token) {
    localStorage.setItem('token', token);
    // Set for ALL future requests
    instance.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    console.log('Token set globally:', token);
  }
};

export const clearToken = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  delete instance.defaults.headers.common['Authorization'];
  console.log('Token cleared');
};

// Request interceptor
instance.interceptors.request.use((config) => {
  // Don't override Content-Type for FormData (file uploads)
  if (!(config.data instanceof FormData)) {
    config.headers['Content-Type'] = 'application/json';
  }
  
  config.headers['Accept'] = 'application/json';
  config.headers['X-Requested-With'] = 'XMLHttpRequest';
  
  const token = getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
    console.log('Request with token:', config.url);
  } else {
    console.log('Request without token:', config.url);
    // Remove Authorization header if no token
    delete config.headers.Authorization;
  }
  return config;
}, (error) => {
  console.error('Request error:', error);
  return Promise.reject(error);
});

// Response interceptor
instance.interceptors.response.use(
  (response) => {
    console.log('Response success:', response.config.url, response.status);
    return response;
  },
  (error) => {
    console.error('Response error:', {
      url: error.config?.url,
      status: error.response?.status,
      data: error.response?.data,
      message: error.message
    });
    
    if (error.response?.status === 401 || error.response?.status === 403) {
      console.log('Authentication error, clearing token');
      clearToken();
      router.push('/login');
    }
    return Promise.reject(error);
  }
);

export const logout = async () => {
  try {
    const token = getToken();
    if (token) {
      await instance.post('/auth/logout', {}, {
        headers: { 
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
      console.log('Logout request sent successfully');
    }
  } catch (e) {
    console.error('Logout error:', e.response?.data || e.message);
  } finally {
    clearToken();
  }
};

// Initialize token if exists
const existingToken = getToken();
if (existingToken) {
  setToken(existingToken);
}

export default instance;