import axios from 'axios'

const api = axios.create({
  baseURL: 'https://projekts-production.up.railway.app',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true,
})

export default api