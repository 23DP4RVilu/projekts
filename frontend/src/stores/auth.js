import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/axios'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const token = ref(localStorage.getItem('token') || null)

  const isLoggedIn = computed(() => !!token.value)
  const isAdmin    = computed(() => user.value?.loma === 'Admin')

  async function login(credentials) {
    const res = await api.post('/login', credentials)
    user.value  = res.data.user
    token.value = res.data.token
    localStorage.setItem('user', JSON.stringify(res.data.user))
    localStorage.setItem('token', res.data.token)
    api.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`
  }

  async function register(data) {
    const res = await api.post('/register', data)
    user.value  = res.data.user
    token.value = res.data.token
    localStorage.setItem('user', JSON.stringify(res.data.user))
    localStorage.setItem('token', res.data.token)
    api.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`
  }

  async function logout() {
    try { await api.post('/logout') } catch {}
    user.value  = null
    token.value = null
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    delete api.defaults.headers.common['Authorization']
  }

  if (token.value) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  return { user, token, isLoggedIn, isAdmin, login, register, logout }
})