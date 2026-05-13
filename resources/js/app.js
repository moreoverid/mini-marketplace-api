import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { Quasar, Dialog, Loading, Notify } from 'quasar';

import '@quasar/extras/material-icons/material-icons.css';
import 'quasar/src/css/index.sass';

import App from './App.vue';
import router from './router';

const app = createApp(App);

app.use(createPinia());

app.use(router);

app.use(Quasar, {
    plugins: {
        Dialog,
        Loading,
        Notify,
    },
});

app.mount('#app');
