<template>
  <div class="create-page">
    <div class="create-card">
      <h1 class="create-title">Izveidot Rakstu</h1>
 
      <div v-if="error" class="alert-error">{{ error }}</div>
 
      <div class="field">
        <label>Kategorija</label>
        <div class="cat-picker">
          <button
            v-for="cat in categories"
            :key="cat"
            class="cat-opt"
            :class="[`cat-${cat.toLowerCase()}`, { active: form.kategorija === cat }]"
            @click="form.kategorija = cat"
          >{{ cat }}</button>
        </div>
      </div>
 
      <div class="field">
        <label>Virsraksts</label>
        <input v-model="form.virsraksts" type="text" placeholder="Raksta nosaukums..." maxlength="300" />
        <span class="char-count">{{ form.virsraksts.length }}/300</span>
      </div>
 
      <div class="field">
        <label>Saturs</label>
        <textarea v-model="form.teksts" rows="8" placeholder="Raksti šeit..."></textarea>
      </div>
 
      <div class="create-actions">
        <button class="btn-primary" :disabled="!isValid || submitting" @click="submit">
          <span v-if="submitting">Publicē...</span>
          <span v-else">Publicēt</span>
        </button>
        <router-link to="/" class="btn-ghost">Atcelt</router-link>
      </div>
    </div>
  </div>
</template>
 
<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/axios'
import { useAuthStore } from '../stores/auth'
 
const router    = useRouter()
const auth      = useAuthStore()
const categories = ['Dziesma', 'Albums', 'Recenzija', 'Zinas']
 
if (!auth.isLoggedIn) router.push('/login')
 
const form = ref({ virsraksts: '', teksts: '', kategorija: '' })
const error     = ref('')
const submitting = ref(false)
 
const isValid = computed(() =>
  form.value.virsraksts.trim() &&
  form.value.teksts.trim() &&
  form.value.kategorija
)
 
async function submit() {
  error.value    = ''
  submitting.value = true
  try {
    const res = await api.post('/posts', form.value)
    router.push(`/post/${res.data.id_raksts}`)
  } catch (e) {
    error.value = e.response?.data?.message || 'Kļūda publicējot rakstu.'
  } finally {
    submitting.value = false
  }
}
</script>
 
<style scoped>
.create-page {
  max-width: 640px; margin: 0 auto;
}
.create-card {
  background: var(--bg-card);
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-lg);
  padding: 32px;
}
.create-title {
  font-family: var(--font-display); font-size: 24px;
  font-weight: 800; margin-bottom: 28px;
}
.alert-error {
  background: rgba(244,63,94,0.1); border: 1px solid rgba(244,63,94,0.3);
  color: var(--down); border-radius: var(--radius);
  padding: 10px 14px; font-size: 13px; margin-bottom: 20px;
}
.field { margin-bottom: 20px; }
.field label {
  display: block; font-size: 12px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.07em;
  color: var(--text-muted); margin-bottom: 8px;
  font-family: var(--font-display);
}
.char-count { font-size: 11px; color: var(--text-dim); float: right; }
.cat-picker { display: flex; gap: 8px; flex-wrap: wrap; }
.cat-opt {
  padding: 6px 16px; border-radius: 99px; font-size: 12px;
  font-weight: 700; font-family: var(--font-display);
  letter-spacing: 0.05em; text-transform: uppercase;
  border: 2px solid transparent; cursor: pointer;
  transition: all 0.2s;
  background: var(--bg-elevated); color: var(--text-muted);
}
.cat-opt:hover { color: var(--text); }
.cat-opt.cat-dziesma.active   { background: rgba(167,139,250,0.15); color: var(--tag-dziesma); border-color: var(--tag-dziesma); }
.cat-opt.cat-albums.active    { background: rgba(56,189,248,0.15);  color: var(--tag-albums);  border-color: var(--tag-albums); }
.cat-opt.cat-recenzija.active { background: rgba(251,146,60,0.15);  color: var(--tag-recenzija); border-color: var(--tag-recenzija); }
.cat-opt.cat-zinas.active     { background: rgba(74,222,128,0.15);  color: var(--tag-zinas);   border-color: var(--tag-zinas); }
.create-actions { display: flex; gap: 10px; margin-top: 28px; align-items: center; }
.btn-primary {
  background: var(--accent); color: #fff; border: none;
  padding: 10px 24px; border-radius: var(--radius);
  font-family: var(--font-display); font-weight: 700;
  font-size: 14px; transition: opacity 0.2s; cursor: pointer;
}
.btn-primary:hover:not(:disabled) { opacity: 0.85; }
.btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-ghost {
  background: transparent; border: 1px solid var(--border);
  color: var(--text-muted); padding: 10px 24px;
  border-radius: var(--radius); font-size: 14px;
  transition: all 0.2s; cursor: pointer; display: inline-block;
}
.btn-ghost:hover { border-color: var(--text-muted); color: var(--text); }
</style>