import { createRouter, createWebHistory } from 'vue-router';

import DashboardPage from '../pages/DashboardPage.vue';
import ProductsPage from '../pages/ProductsPage.vue';
import OrdersPage from '../pages/OrdersPage.vue';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: DashboardPage,
    },
    {
        path: '/products',
        name: 'products',
        component: ProductsPage,
    },
    {
        path: '/orders',
        name: 'orders',
        component: OrdersPage,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
