<template>
  <div class="post-card" @click="goToPost">
    <div class="vote-col">
      <button
        class="vote-btn up"
        :class="{ active: post.user_vote === 1 }"
        @click.stop="vote(1)"
      >▲</button>
      <span class="vote-score" :class="scoreClass">{{ post.rating_score }}</span>
      <button
        class="vote-btn down"
        :class="{ active: post.user_vote === -1 }"
        @click.stop="vote(-1)"
      >▼</button>
    </div>
 
    <div class="post-body">
      <div class="post-meta">
        <span class="cat-badge" :class="`cat-${post.kategorija?.toLowerCase()}`">
          {{ post.kategorija }}
        </span>
        <span class="meta-sep">•</span>
        <span class="meta-author">{{ post.lietotajs?.lietotajvards }}</span>
        <span class="meta-sep">•</span>
        <span class="meta-date">{{ timeAgo(post.datums) }}</span>
      </div>
 
      <h2 class="post-title">{{ post.virsraksts }}</h2>
      <p class="post-excerpt">{{ excerpt(post.teksts) }}</p>
 
      <div class="post-footer">
        <span class="comment-count">
           {{ post.komentari_count ?? 0 }} komentāri
        </span>
      </div>
    </div>
  </div>
</template>
 
<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../services/axios'
 
const props  = defineProps({ post: Object })
const emit   = defineEmits(['vote'])
const router = useRouter()
const auth   = useAuthStore()
 
function goToPost() {
  router.push(`/post/${props.post.id_raksts}`)
}
 
async function vote(tips) {
  if (!auth.isLoggedIn) { router.push('/login'); return }
  try {
    const res = await api.post(`/posts/${props.post.id_raksts}/vote`, { tips })
    emit('vote', { id: props.post.id_raksts, ...res.data })
  } catch (e) { console.error(e) }
}
 
function timeAgo(dateStr) {
  const diff = Date.now() - new Date(dateStr).getTime()
  const m = Math.floor(diff / 60000)
  if (m < 1)   return 'tikko'
  if (m < 60)  return `${m}min atpakaļ`
  const h = Math.floor(m / 60)
  if (h < 24)  return `${h}h atpakaļ`
  return `${Math.floor(h/24)}d atpakaļ`
}
 
function excerpt(text) {
  return text?.length > 140 ? text.slice(0, 140) + '…' : text
}
 
const scoreClass = computed(() => {
  if (props.post.rating_score > 0) return 'positive'
  if (props.post.rating_score < 0) return 'negative'
  return 'neutral'
})
</script>
 
<style scoped>
.post-card {
  display: flex;
  gap: 14px;
  background: var(--bg-card);
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-lg);
  padding: 16px;
  cursor: pointer;
  transition: border-color 0.2s, transform 0.15s;
  position: relative;
  overflow: hidden;
}
.post-card::before {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 3px;
  background: transparent;
  transition: background 0.2s;
}
.post-card:hover {
  border-color: var(--border);
  transform: translateX(2px);
}
.post-card:hover::before { background: var(--accent); }
.vote-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  min-width: 36px;
}
.vote-btn {
  background: transparent;
  border: none;
  color: var(--text-dim);
  font-size: 13px;
  padding: 4px 6px;
  border-radius: 4px;
  transition: all 0.15s;
  line-height: 1;
}
.vote-btn:hover { color: var(--text); }
.vote-btn.up.active  { color: var(--up);   background: var(--up-bg); }
.vote-btn.down.active{ color: var(--down); background: var(--down-bg); }
.vote-score {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 15px;
}
.vote-score.positive { color: var(--up); }
.vote-score.negative { color: var(--down); }
.vote-score.neutral  { color: var(--text-muted); }
.post-body { flex: 1; min-width: 0; }
.post-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 8px;
  font-size: 12px;
  flex-wrap: wrap;
}
.cat-badge {
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
  font-family: var(--font-display);
  letter-spacing: 0.05em;
  text-transform: uppercase;
}
.cat-dziesma   { background: rgba(167,139,250,0.15); color: var(--tag-dziesma); }
.cat-albums    { background: rgba(56,189,248,0.15);  color: var(--tag-albums); }
.cat-recenzija { background: rgba(251,146,60,0.15);  color: var(--tag-recenzija); }
.cat-zinas     { background: rgba(74,222,128,0.15);  color: var(--tag-zinas); }
.meta-sep   { color: var(--text-dim); }
.meta-author{ color: var(--text-muted); }
.meta-date  { color: var(--text-dim); }
.post-title {
  font-family: var(--font-display);
  font-size: 16px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 6px;
  line-height: 1.3;
}
.post-excerpt {
  color: var(--text-muted);
  font-size: 13px;
  line-height: 1.5;
  margin-bottom: 12px;
}
.post-footer {
  display: flex;
  gap: 16px;
}
.comment-count {
  font-size: 12px;
  color: var(--text-dim);
}
</style>
 