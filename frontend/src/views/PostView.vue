<template>
  <div v-if="post" class="post-view">
    <div class="post-full">
      <div class="pf-top">
        <div class="vote-col">
          <button class="vote-btn up" :class="{ active: post.user_vote === 1 }" @click="votePost(1)">▲</button>
          <span class="vote-score" :class="scoreClass(post.rating_score)">{{ post.rating_score }}</span>
          <button class="vote-btn down" :class="{ active: post.user_vote === -1 }" @click="votePost(-1)">▼</button>
        </div>
        <div class="pf-content">
          <div class="post-meta">
            <span class="cat-badge" :class="`cat-${post.kategorija?.toLowerCase()}`">{{ post.kategorija }}</span>
            <span class="meta-sep">•</span>
            <span class="meta-author">{{ post.lietotajs?.lietotajvards }}</span>
            <span class="meta-sep">•</span>
            <span class="meta-date">{{ timeAgo(post.datums) }}</span>
            <template v-if="canEdit(post.id_lietotajs)">
              <span class="meta-sep">•</span>
              <button class="action-link" @click="startEditPost">Rediģēt</button>
              <button class="action-link danger" @click="deletePost">Dzēst</button>
            </template>
          </div>
 
          <h1 class="pf-title">{{ post.virsraksts }}</h1>
 
          <div v-if="!editingPost" class="pf-body">{{ post.teksts }}</div>
          <div v-else class="edit-form">
            <textarea v-model="editPostText" rows="5"></textarea>
            <div class="edit-actions">
              <button class="btn-primary" @click="saveEditPost">Saglabāt</button>
              <button class="btn-ghost" @click="editingPost = false">Atcelt</button>
            </div>
          </div>
        </div>
      </div>
    </div>
 

    <div class="comment-section">
      <h2 class="section-title">Komentāri ({{ flatCount }})</h2>
 
      <div v-if="auth.isLoggedIn" class="new-comment-form">
        <div class="form-avatar">{{ auth.user.lietotajvards[0].toUpperCase() }}</div>
        <div class="form-body">
          <textarea
            v-model="newComment"
            :placeholder="`Komentē kā ${auth.user.lietotajvards}...`"
            rows="3"
          ></textarea>
          <div class="form-actions">
            <button class="btn-primary" :disabled="!newComment.trim()" @click="submitComment(null)">
              Pievienot
            </button>
          </div>
        </div>
      </div>
      <div v-else class="login-prompt">
        <router-link to="/login">Ienāc</router-link> vai
        <router-link to="/register">reģistrējies</router-link>, lai komentētu.
      </div>
 
      <div class="comments-tree">
        <CommentNode
          v-for="c in comments"
          :key="c.id_komentars"
          :comment="c"
          :postId="postId"
          :depth="0"
          @vote="handleCommentVote"
          @reply="handleReplyAdded"
          @deleted="handleCommentDeleted"
          @edited="handleCommentEdited"
        />
      </div>
    </div>
  </div>
 
  <div v-else-if="loading" class="loading-center">
    <div class="spinner"></div>
  </div>
</template>
 
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/axios'
import { useAuthStore } from '../stores/auth'
import CommentNode from '../components/CommentNode.vue'
 
const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const postId = +route.params.id
 
const post        = ref(null)
const comments    = ref([])
const loading     = ref(true)
const newComment  = ref('')
const editingPost = ref(false)
const editPostText= ref('')
 
const flatCount = computed(() => countComments(comments.value))
function countComments(arr) {
  return arr.reduce((n, c) => n + 1 + countComments(c.replies || []), 0)
}
 
async function fetchPost() {
  try {
    const res = await api.get(`/posts/${postId}`)
    post.value = res.data
  } catch { router.push('/') }
}
async function fetchComments() {
  const res = await api.get(`/posts/${postId}/comments`)
  comments.value = res.data
}
 
async function votePost(tips) {
  if (!auth.isLoggedIn) { router.push('/login'); return }
  const res = await api.post(`/posts/${postId}/vote`, { tips })
  post.value.rating_score = res.data.rating_score
  post.value.user_vote    = res.data.user_vote
}
 
function canEdit(ownerId) {
  return auth.isLoggedIn && (auth.user.id_lietotajs === ownerId || auth.isAdmin)
}
 
function startEditPost() {
  editPostText.value = post.value.teksts
  editingPost.value  = true
}
async function saveEditPost() {
  await api.put(`/posts/${postId}`, { teksts: editPostText.value })
  post.value.teksts = editPostText.value
  editingPost.value = false
}
async function deletePost() {
  if (!confirm('Dzēst šo rakstu?')) return
  await api.delete(`/posts/${postId}`)
  router.push('/')
}
 
async function submitComment(parentId) {
  if (!newComment.value.trim()) return
  const res = await api.post(`/posts/${postId}/comments`, {
    teksts: newComment.value,
    parent_id: parentId,
  })
  newComment.value = ''
  const c = { ...res.data, replies: [], user_vote: 0 }
  if (!parentId) {
    comments.value.push(c)
  }
}
 
function handleCommentVote({ id, rating_score, user_vote }) {
  updateCommentInTree(comments.value, id, { rating_score, user_vote })
}
function updateCommentInTree(arr, id, patch) {
  for (const c of arr) {
    if (c.id_komentars === id) { Object.assign(c, patch); return }
    if (c.replies) updateCommentInTree(c.replies, id, patch)
  }
}
 
function handleReplyAdded({ parentId, comment }) {
  addReplyInTree(comments.value, parentId, comment)
}
function addReplyInTree(arr, parentId, comment) {
  for (const c of arr) {
    if (c.id_komentars === parentId) { c.replies.push({ ...comment, replies: [], user_vote: 0 }); return }
    if (c.replies) addReplyInTree(c.replies, parentId, comment)
  }
}
 
function handleCommentDeleted(id) {
  comments.value = deleteFromTree(comments.value, id)
}
function deleteFromTree(arr, id) {
  return arr.filter(c => {
    if (c.id_komentars === id) return false
    if (c.replies) c.replies = deleteFromTree(c.replies, id)
    return true
  })
}
 
function handleCommentEdited({ id, teksts }) {
  updateCommentInTree(comments.value, id, { teksts })
}
 
function timeAgo(dateStr) {
  const diff = Date.now() - new Date(dateStr).getTime()
  const m = Math.floor(diff / 60000)
  if (m < 1) return 'tikko'
  if (m < 60) return `${m}min atpakaļ`
  const h = Math.floor(m / 60)
  if (h < 24) return `${h}h atpakaļ`
  return `${Math.floor(h/24)}d atpakaļ`
}
function scoreClass(score) {
  if (score > 0) return 'positive'
  if (score < 0) return 'negative'
  return 'neutral'
}
 
onMounted(async () => {
  await Promise.all([fetchPost(), fetchComments()])
  loading.value = false
})
</script>
 
<style scoped>
.post-full {
  background: var(--bg-card);
  border: 1px solid var(--border-soft);
  border-radius: var(--radius-lg);
  padding: 24px;
  margin-bottom: 24px;
}
.pf-top { display: flex; gap: 16px; }
.vote-col {
  display: flex; flex-direction: column;
  align-items: center; gap: 6px; min-width: 40px;
}
.vote-btn {
  background: transparent; border: none;
  color: var(--text-dim); font-size: 14px;
  padding: 5px 7px; border-radius: 4px;
  transition: all 0.15s; cursor: pointer;
}
.vote-btn:hover { color: var(--text); }
.vote-btn.up.active  { color: var(--up);   background: var(--up-bg); }
.vote-btn.down.active{ color: var(--down); background: var(--down-bg); }
.vote-score { font-family: var(--font-display); font-weight: 700; font-size: 16px; }
.vote-score.positive { color: var(--up); }
.vote-score.negative { color: var(--down); }
.vote-score.neutral  { color: var(--text-muted); }
.pf-content { flex: 1; }
.post-meta {
  display: flex; align-items: center; gap: 6px;
  font-size: 12px; margin-bottom: 12px; flex-wrap: wrap;
}
.cat-badge {
  padding: 2px 8px; border-radius: 4px;
  font-size: 11px; font-weight: 700;
  font-family: var(--font-display); letter-spacing: 0.05em; text-transform: uppercase;
}
.cat-dziesma   { background: rgba(167,139,250,0.15); color: var(--tag-dziesma); }
.cat-albums    { background: rgba(56,189,248,0.15);  color: var(--tag-albums); }
.cat-recenzija { background: rgba(251,146,60,0.15);  color: var(--tag-recenzija); }
.cat-zinas     { background: rgba(74,222,128,0.15);  color: var(--tag-zinas); }
.meta-sep   { color: var(--text-dim); }
.meta-author{ color: var(--text-muted); }
.meta-date  { color: var(--text-dim); }
.action-link {
  background: none; border: none; font-size: 12px;
  color: var(--text-muted); cursor: pointer;
  transition: color 0.15s; font-family: var(--font-body);
}
.action-link:hover { color: var(--text); }
.action-link.danger:hover { color: var(--down); }
.pf-title {
  font-family: var(--font-display); font-size: 24px;
  font-weight: 800; margin-bottom: 16px; line-height: 1.25;
}
.pf-body { color: var(--text-muted); line-height: 1.7; white-space: pre-wrap; }
.edit-form textarea {
  width: 100%; margin-bottom: 10px;
}
.edit-actions { display: flex; gap: 8px; }
.comment-section { margin-top: 8px; }
.section-title {
  font-family: var(--font-display); font-size: 16px;
  font-weight: 700; margin-bottom: 20px; color: var(--text-muted);
}
.new-comment-form {
  display: flex; gap: 12px; margin-bottom: 24px;
}
.form-avatar {
  width: 36px; height: 36px; min-width: 36px;
  background: var(--accent); border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--font-display); font-weight: 700;
  font-size: 15px; color: #fff;
}
.form-body { flex: 1; }
.form-actions { display: flex; justify-content: flex-end; margin-top: 8px; }
.login-prompt {
  margin-bottom: 24px; color: var(--text-muted); font-size: 13px;
}
.login-prompt a { color: var(--accent); text-decoration: underline; }
.comments-tree { display: flex; flex-direction: column; gap: 2px; }
.btn-primary {
  background: var(--accent); color: #fff; border: none;
  padding: 8px 18px; border-radius: var(--radius);
  font-family: var(--font-display); font-weight: 600;
  font-size: 13px; transition: opacity 0.2s; cursor: pointer;
}
.btn-primary:hover:not(:disabled) { opacity: 0.85; }
.btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-ghost {
  background: transparent; border: 1px solid var(--border);
  color: var(--text-muted); padding: 8px 18px;
  border-radius: var(--radius); font-size: 13px;
  transition: all 0.2s; cursor: pointer;
}
.btn-ghost:hover { border-color: var(--text-muted); color: var(--text); }
.loading-center { display: flex; justify-content: center; padding: 80px; }
.spinner {
  width: 36px; height: 36px;
  border: 3px solid var(--border);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>