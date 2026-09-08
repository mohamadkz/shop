import axios from 'axios'
import { unwrap, unwrapList, validationMessage } from '../utils/format'

const TOKEN_KEY = 'shop_token'

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function setToken(token) {
  if (token) localStorage.setItem(TOKEN_KEY, token)
  else localStorage.removeItem(TOKEN_KEY)
}

export const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = getToken()
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export function extractError(error) {
  const err = new Error(validationMessage(error))
  err.status = error?.response?.status
  err.payload = error?.response?.data
  err.original = error
  return err
}

async function request(fn) {
  try {
    const res = await fn()
    return res.data
  } catch (error) {
    throw extractError(error)
  }
}

export const authApi = {
  login: (body) => request(() => api.post('/v1/login', body)),
  register: (body) => request(() => api.post('/v1/register', body)),
  logout: () => request(() => api.post('/v1/logout')),
  me: () => request(() => api.get('/v1/user')),
  sendOtp: (phone) => request(() => api.post('/v2/send-otp', { phone })),
  verifyOtp: (body) => request(() => api.post('/v2/verify-otp', body)),
}

export const catalogApi = {
  items: (params) => request(() => api.get('/v1/items', { params })),
  item: (id) => request(() => api.get(`/v1/items/${id}`)),
  createItem: (formData) => request(() => api.post('/v1/items', formData)),
  updateItem: (id, formData) => request(() => api.post(`/v1/items/${id}`, formData)),
  deleteItem: (id) => request(() => api.delete(`/v1/items/${id}`)),
  comments: (id, params) => request(() => api.get(`/v1/items/${id}/comments`, { params })),
  addComment: (id, body) => request(() => api.post(`/v1/items/${id}/comments`, body)),
  toggleFavorite: (id) => request(() => api.post(`/v1/items/${id}/favorite`)),
  categories: () => request(() => api.get('/v1/categories')),
}

export const cartApi = {
  get: () => request(() => api.get('/v1/cart')),
  add: (body) => request(() => api.post('/v1/cart/items', body)),
  update: (itemId, quantity) => request(() => api.patch(`/v1/cart/items/${itemId}`, { quantity })),
  remove: (itemId) => request(() => api.delete(`/v1/cart/items/${itemId}`)),
  applyDiscount: (code) => request(() => api.post('/v1/cart/discount', { code })),
  removeDiscount: () => request(() => api.delete('/v1/cart/discount')),
}

export const orderApi = {
  checkout: (body) => request(() => api.post('/v1/checkout', body)),
  list: (params) => request(() => api.get('/v1/orders', { params })),
  show: (uuid) => request(() => api.get(`/v1/orders/${uuid}`)),
  initiatePayment: (uuid) => request(() => api.post(`/v1/orders/${uuid}/payment/initiate`)),
}

export { unwrap, unwrapList }
