<template>
  <div class="comment-node" :class="{ nested: depth > 0 }">
    <div class="comment-card">
      <div class="comment-vote">
        <button class="vote-btn up" :class="{ active: comment.user_vote === 1 }" @click="vote(1)">▲</button>
        <span class="vote-score" :class="scoreClass">{{ comment.rating_score }}</span>
        <button class="vote-btn down" :class="{ active: comment.user_vote === -1 }" @click="vote(-1)">▼</button>
      </div>
 
      <div class="comment-body">
        <div class="comment-meta">
          <span class="c-avatar">{{ comment.lietotajs?.lietotajvards?.[0]?.toUpperCase() }}</span>
          <span class="c-author">{{ comment.lietotajs?.lietotajvards }}</span>
          <span class="c-sep">•</span>
          <span class="c-date">{{ timeAgo(comment.datums) }}</span>
          <template v-if="canEdit">
            <span class="c-sep">•</span>
            <button class="action-link" @click="startEdit">Rediģēt</button>
            <button class="action-link danger" @click="deleteComment">Dzēst</button>
          </template>
        </div>
 
        <div v-if="!editing" class="comment-text">{{ comment.teksts }}</div>
        <div v-else class="edit-form">
          <textarea v-model="editText" rows="3"></textarea>
          <div class="edit-actions">
            <button class="btn-primary sm" @click="saveEdit">Saglabāt</button>
            <button class="btn-ghost sm" @click="editing = false">Atcelt</button>
          </div>
        </div>
 
        <div class="comment-actions">
          <button
            v-if="auth.isLoggedIn"
            class="reply-btn"
            :class="{ active: replying }"
            @click="replying = !replying"
          >
            ↩ Atbildēt
          </button>
          <button
            v-if="comment.replies?.length"
            class="toggle-btn"
            @click="collapsed = !collapsed"
          >
            {{ collapsed ? '▶' : '▼' }} {{ comment.replies.length }} atbilde{{ comment.replies.length !== 1 ? 's' : '' }}
          </button>
        </div>
 
        <div v-if="replying" class="reply-form">
          <textarea
            v-model="replyText"
            :placeholder="`Atbildi ${comment.lietotajs?.lietotajvards}...`"
            rows="3"
            autofocus
          ></textarea>
          <div class="reply-actions">
            <button class="btn-primary sm" :disabled="!replyText.trim()" @click="submitReply">Nosūtīt</button>
            <button class="btn-ghost sm" @click="replying = false; replyText = ''">Atcelt</button>
          </div>
        </div>
      </div>
    </div>
 
    <transition name="collapse">
      <div v-if="!collapsed && comment.replies?.length" class="replies">
        <CommentNode
          v-for="reply in comment.replies"
          :key="reply.id_komentars"
          :comment="reply"
          :postId="postId"
          :depth="depth + 1"
          @vote="$emit('vote', $event)"
          @reply="$emit('reply', $event)"
          @deleted="$emit('deleted', $event)"
          @edited="$emit('edited', $event)"
        />
      </div>
    </transition>
  </div>
</template>
 
<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../services/axios'
 
const props = defineProps({
  comment: Object,
  postId:  Number,
  depth:   { type: Number, default: 0 }
})
const emit   = defineEmits(['vote', 'reply', 'deleted', 'edited'])
const auth   = useAuthStore()
const router = useRouter()
 
const replying  = ref(false)
const replyText = ref('')
const editing   = ref(false)
const editText  = ref('')
const collapsed = ref(false)
 
const canEdit = computed(() =>
  auth.isLoggedIn &&
  (auth.user.id_lietotajs === props.comment.id_lietotajs || auth.isAdmin)
)
 
const scoreClass = computed(() => {
  if (props.comment.rating_score > 0) return 'positive'
  if (props.comment.rating_score < 0) return 'negative'
  return 'neutral'
})
 
async function vote(tips) {
  if (!auth.isLoggedIn) { router.push('/login'); return }
  try {
    const res = await api.post(`/comments/${props.comment.id_komentars}/vote`, { tips })
    emit('vote', { id: props.comment.id_komentars, ...res.data })
  } catch (e) { console.error(e) }
}
 
async function submitReply() {
  if (!replyText.value.trim()) return
  try {
    const res = await api.post(`/posts/${props.postId}/comments`, {
      teksts:    replyText.value,
      parent_id: props.comment.id_komentars,
    })
    emit('reply', { parentId: props.comment.id_komentars, comment: res.data })
    replyText.value = ''
    replying.value  = false
  } catch (e) { console.error(e) }
}
 
function startEdit() {
  editText.value = props.comment.teksts
  editing.value  = true
}
async function saveEdit() {
  await api.put(`/comments/${props.comment.id_komentars}`, { teksts: editText.value })
  emit('edited', { id: props.comment.id_komentars, teksts: editText.value })
  editing.value = false
}
async function deleteComment() {
  if (!confirm('Dzēst komentāru?')) return
  await api.delete(`/comments/${props.comment.id_komentars}`)
  emit('deleted', props.comment.id_komentars)
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
</script>
 
<style scoped>
.comment-node { margin-top: 2px; }
.comment-node.nested {
  margin-left: 28px;
  border-left: 2px solid var(--border-soft);
  padding-left: 12px;
}
.comment-card {
  display: flex; gap: 10px;
  padding: 12px 0;
}
.comment-vote {
  display: flex; flex-direction: column;
  align-items: center; gap: 2px; min-width: 28px;
}
.vote-btn {
  background: transparent; border: none;
  color: var(--text-dim); font-size: 11px;
  padding: 3px 5px; border-radius: 3px;
  transition: all 0.15s; cursor: pointer;
}
.vote-btn:hover { color: var(--text); }
.vote-btn.up.active  { color: var(--up);   background: var(--up-bg); }
.vote-btn.down.active{ color: var(--down); background: var(--down-bg); }
.vote-score {
  font-family: var(--font-display); font-weight: 700; font-size: 12px;
}
.vote-score.positive { color: var(--up); }
.vote-score.negative { color: var(--down); }
.vote-score.neutral  { color: var(--text-dim); }
.comment-body { flex: 1; }
.comment-meta {
  display: flex; align-items: center; gap: 5px;
  font-size: 12px; margin-bottom: 6px; flex-wrap: wrap;
}
.c-avatar {
  width: 22px; height: 22px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 50%;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; font-family: var(--font-display);
  color: var(--text-muted);
}
.c-author { font-weight: 700; color: var(--text); font-size: 12px; }
.c-sep, .c-date { color: var(--text-dim); }
.action-link {
  background: none; border: none; font-size: 11px;
  color: var(--text-dim); cursor: pointer;
  transition: color 0.15s; font-family: var(--font-body);
}
.action-link:hover { color: var(--text-muted); }
.action-link.danger:hover { color: var(--down); }
.comment-text { color: var(--text-muted); font-size: 13px; line-height: 1.6; white-space: pre-wrap; }
.edit-form textarea { margin-bottom: 8px; }
.edit-actions, .reply-actions { display: flex; gap: 6px; }
.comment-actions {
  display: flex; gap: 10px; margin-top: 8px;
}
.reply-btn, .toggle-btn {
  background: none; border: none; font-size: 12px;
  color: var(--text-dim); cursor: pointer;
  transition: color 0.15s; font-family: var(--font-body);
  padding: 0;
}
.reply-btn:hover, .toggle-btn:hover { color: var(--text-muted); }
.reply-btn.active { color: var(--accent); }
.reply-form { margin-top: 10px; }
.reply-form textarea { margin-bottom: 8px; }
.replies { margin-top: 2px; }
.collapse-enter-active, .collapse-leave-active { transition: opacity 0.2s; }
.collapse-enter-from, .collapse-leave-to { opacity: 0; }
.btn-primary {
  background: var(--accent); color: #fff; border: none;
  padding: 8px 18px; border-radius: var(--radius);
  font-family: var(--font-display); font-weight: 600;
  font-size: 13px; transition: opacity 0.2s; cursor: pointer;
}
.btn-primary.sm { padding: 5px 12px; font-size: 12px; }
.btn-primary:hover:not(:disabled) { opacity: 0.85; }
.btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-ghost {
  background: transparent; border: 1px solid var(--border);
  color: var(--text-muted); padding: 8px 18px;
  border-radius: var(--radius); font-size: 13px;
  transition: all 0.2s; cursor: pointer;
}
.btn-ghost.sm { padding: 5px 12px; font-size: 12px; }
.btn-ghost:hover { border-color: var(--text-muted); color: var(--text); }
</style>