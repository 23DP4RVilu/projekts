<template>
  <div id="app-root">
    <nav class="navbar">
      <div class="nav-inner">
        <router-link to="/" class="nav-logo">
          <span class="logo-icon">♪</span>
          <span class="logo-text">MUZIKA</span>
        </router-link>
 
        <div class="nav-links">
          <router-link to="/" class="nav-link">Sākums</router-link>
          <router-link v-if="auth.isLoggedIn" to="/create" class="nav-btn-create">
            + Raksts
          </router-link>
        </div>
 
        <div class="nav-auth">
          <button class="theme-toggle" @click="toggleTheme" :title="theme === 'dark' ? 'Gaišais režīms' : 'Tumšais režīms'">
            {{ theme === 'dark' ? 'Gaišs' : 'Tumšs' }}
          </button>
 
          <template v-if="auth.isLoggedIn">
            <span class="nav-user">
              <span class="user-dot"></span>
              {{ auth.user.lietotajvards }}
            </span>
            <button class="nav-btn-ghost" @click="handleLogout">Iziet</button>
          </template>
          <template v-else>
            <router-link to="/login" class="nav-btn-ghost">Ienākt</router-link>
            <router-link to="/register" class="nav-btn-accent">Reģistrēties</router-link>
          </template>
        </div>
      </div>
    </nav>
 
    <main class="page-wrap">
      <router-view />
    </main>
  </div>
</template>
 
<script setup>
import { ref } from 'vue'
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'
 
const auth   = useAuthStore()
const router = useRouter()
 
const theme = ref(localStorage.getItem('theme') || 'dark')
 
document.documentElement.setAttribute('data-theme', theme.value)
 
function toggleTheme() {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
  localStorage.setItem('theme', theme.value)
  document.documentElement.setAttribute('data-theme', theme.value)
}
 
async function handleLogout() {
  await auth.logout()
  router.push('/')
}
</script>
 
<style scoped>
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: var(--navbar-bg);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border-soft);
}
.nav-inner {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 20px;
  height: 56px;
  display: flex;
  align-items: center;
  gap: 24px;
}
.nav-logo {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 18px;
  letter-spacing: 0.08em;
  color: var(--text);
}
.logo-icon {
  font-size: 22px;
  color: var(--accent);
  animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.5; }
}
.nav-links {
  display: flex;
  align-items: center;
  gap: 16px;
  flex: 1;
}
.nav-link {
  color: var(--text-muted);
  font-size: 13px;
  transition: color 0.2s;
}
.nav-link:hover, .nav-link.router-link-active { color: var(--text); }
.nav-btn-create {
  background: var(--accent);
  color: #fff;
  border-radius: var(--radius);
  padding: 6px 14px;
  font-size: 13px;
  font-weight: 700;
  font-family: var(--font-display);
  letter-spacing: 0.03em;
  transition: opacity 0.2s;
}
.nav-btn-create:hover { opacity: 0.85; }
.nav-auth {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-left: auto;
}
.nav-user {
  display: flex;
  align-items: center;
  gap: 7px;
  color: var(--text-muted);
  font-size: 13px;
}
.user-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--up);
  box-shadow: 0 0 6px var(--up);
}
.nav-btn-ghost {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  border-radius: var(--radius);
  padding: 6px 14px;
  font-size: 13px;
  transition: border-color 0.2s, color 0.2s;
}
.nav-btn-ghost:hover { border-color: var(--text-muted); color: var(--text); }
.nav-btn-accent {
  background: var(--accent);
  color: #fff;
  border-radius: var(--radius);
  padding: 6px 14px;
  font-size: 13px;
  font-family: var(--font-display);
  font-weight: 600;
  transition: opacity 0.2s;
}
.nav-btn-accent:hover { opacity: 0.85; }
.theme-toggle {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  height: 34px;
  padding: 0 12px;
  font-size: 12px;
  font-family: var(--font-display);
  font-weight: 700;
  color: var(--text-muted);
  letter-spacing: 0.04em;
  transition: border-color 0.2s, color 0.2s, background 0.2s;
  cursor: pointer;
  white-space: nowrap;
}
.theme-toggle:hover {
  border-color: var(--accent);
  color: var(--accent);
  background: var(--accent-glow);
}
.page-wrap {
  max-width: 900px;
  margin: 0 auto;
  padding: 32px 20px;
}
</style>