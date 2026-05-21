import axios from 'axios'

const api = axios.create({
  baseURL: 'https://projekts-production.up.railway.app/api',
  headers: {
    'Content-Type': 'application/json', 'Accept': 'application/json'
  },
  withCredentials: false,
})

export default api