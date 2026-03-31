import { createApp } from 'vue';
import App from './App.vue';
import { pinia } from './stores/pinia';
import router from './router';
import './bootstrap';

const app = createApp(App);

app.use(pinia);
app.use(router);
app.mount('#app');
