import { http } from './http';

export async function fetchProducts(params = {}) {
  const response = await http.get('/products', {
    params,
  });

  return response.data;
}

export async function searchProducts(params = {}) {
  const response = await http.get('/products/search', {
    params,
  });

  return response.data;
}

export async function createProduct(payload) {
  const response = await http.post('/products', payload);

  return response.data.data;
}
