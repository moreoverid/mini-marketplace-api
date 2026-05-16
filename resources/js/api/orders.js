import { http } from './http';

export async function fetchOrders(params = {}) {
    const response = await http.get('/orders', {
        params,
    });

    return response.data;
}

export async function createOrder(payload) {
    const response = await http.post('/orders', payload);

    return response.data.data;
}

export async function payOrder(orderId) {
    const response = await http.patch(`/orders/${orderId}/pay`);

    return response.data.data;
}
