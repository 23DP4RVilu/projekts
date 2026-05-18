import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia } from 'pinia'
import App from './App.vue'
import HomeView from './views/HomeView.vue'
import PostView from './views/PostView.vue'
import LoginView from './views/LoginView.vue'
import RegisterView from './views/RegisterView.vue'
import CreatePostView from './views/CreatePostView.vue'
import './style.css'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: HomeView },
    { path: '/post/:id', component: PostView },
    { path: '/login', component: LoginView },
    { path: '/register', component: RegisterView },
    { path: '/create', component: CreatePostView },
  ]
})

const pinia = createPinia()
const app = createApp(App)
app.use(router)
app.use(pinia)
app.mount('#app')