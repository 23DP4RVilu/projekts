<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-logo">♪</div>
      <h1 class="auth-title">Ienākt</h1>
      <p class="auth-sub">Sveiki atpakaļ, mūzikas entuziast!</p>
 
      <div v-if="error" class="alert-error">{{ error }}</div>
 
      <div class="field">
        <label>Lietotājvārds</label>
        <input v-model="form.lietotajvards" type="text" placeholder="tavs_vards" @keydown.enter="submit" />
      </div>
      <div class="field">
        <label>Parole</label>
        <input v-model="form.parole" type="password" placeholder="••••••••" @keydown.enter="submit" />
      </div>
 
      <button class="btn-primary full" :disabled="submitting" @click="submit">
        {{ submitting ? 'Ienāk...' : 'Ienākt' }}
      </button>
 
      <p class="auth-switch">
        Nav konta? <router-link to="/register">Reģistrēties</router-link>
      </p>
    </div>
  </div>
</template>
 
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
 
const router    = useRouter()
const auth      = useAuthStore()
const form      = ref({ lietotajvards: '', parole: '' })
const error     = ref('')
const submitting = ref(false)
 
async function submit() {
  error.value = ''
  submitting.value = true
  try {
    await auth.login(form.value)
    router.push('/')
  } catch (e) {
    const errs = e.response?.data?.errors
    if (errs) error.value = Object.values(errs).flat().join(' ')
    else error.value = e.response?.data?.message || 'Kļūda ienākot.'
  } finally {
    submitting.value = false
  }
}
</script>
 
<style scoped>
.auth-page {
  display: flex; justify-content: center; align-items: flex-start;
  padding-top: 40px;
}
.auth-card {
  background: var(--bg-card);
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-lg);
  padding: 40px; width: 100%; max-width: 420px;
}
.auth-logo {
  font-size: 40px; color: var(--accent);
  text-align: center; margin-bottom: 16px;
}
.auth-title {
  font-family: var(--font-display); font-size: 26px;
  font-weight: 800; text-align: center; margin-bottom: 6px;
}
.auth-sub { text-align: center; color: var(--text-muted); font-size: 13px; margin-bottom: 28px; }
.alert-error {
  background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.3);
  color: var(--down); border-radius: var(--radius);
  padding: 10px 14px; font-size: 13px; margin-bottom: 16px;
}
.field { margin-bottom: 16px; }
.field label {
  display: block; font-size: 12px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.07em;
  color: var(--text-muted); margin-bottom: 6px;
  font-family: var(--font-display);
}
.btn-primary {
  background: var(--accent); color: #fff; border: none;
  padding: 12px 24px; border-radius: var(--radius);
  font-family: var(--font-display); font-weight: 700;
  font-size: 14px; transition: opacity 0.2s; cursor: pointer;
}
.btn-primary.full { width: 100%; margin-top: 8px; }
.btn-primary:hover:not(:disabled) { opacity: 0.85; }
.btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }
.auth-switch {
  text-align: center; margin-top: 20px;
  font-size: 13px; color: var(--text-muted);
}
.auth-switch a { color: var(--accent); text-decoration: underline; }
</style>