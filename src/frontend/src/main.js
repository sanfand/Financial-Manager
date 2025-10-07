import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import 'bootstrap/dist/css/bootstrap.min.css'; // Bootstrap CSS
import 'bootstrap'; // Bootstrap JS





const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#app');