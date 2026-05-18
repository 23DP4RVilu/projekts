<template>
  <div class="home">
    <div class="home-header">
      <h1 class="home-title">Mūzikas Kopiena</h1>
      <p class="home-sub">Latvijas mūzikas diskusiju platforma</p>
    </div>
 
    <div class="toolbar">
      <div class="filter-tabs">
        <button
          v-for="cat in categories"
          :key="cat"
          class="filter-tab"
          :class="{ active: activeCategory === cat }"
          @click="setCategory(cat)"
        >{{ cat }}</button>
      </div>
      <div class="sort-tabs">
        <button class="sort-tab" :class="{ active: sort === 'new' }" @click="setSort('new')">Jauns</button>
        <button class="sort-tab" :class="{ active: sort === 'top' }" @click="setSort('top')">Top</button>
      </div>
    </div>
 
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
    </div>
 
    <div v-else-if="posts.length === 0" class="empty-state">
      <p>Nav neviena raksta. Esi pirmais!</p>
    </div>
 
    <transition-group name="post-list" tag="div" class="posts-list">
      <PostCard
        v-for="post in posts"
        :key="post.id_raksts"
        :post="post"
        @vote="handleVote"
      />
    </transition-group>
 
    <div v-if="pagination && pagination.last_page > 1" class="pagination">
      <button
        v-for="page in pagination.last_page"
        :key="page"
        class="page-btn"
        :class="{ active: page === currentPage }"
        @click="loadPage(page)"
      >{{ page }}</button>
    </div>
  </div>
</template>
 
<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/axios'
import PostCard from '../components/PostCard.vue'
 
const categories    = ['Visi', 'Dziesma', 'Albums', 'Recenzija', 'Ziņas']
const activeCategory = ref('Visi')
const sort          = ref('new')
const posts         = ref([])
const pagination    = ref(null)
const currentPage   = ref(1)
const loading       = ref(false)
 
async function fetchPosts() {
  loading.value = true
  try {
    const params = { page: currentPage.value, sort: sort.value }
    if (activeCategory.value !== 'Visi') params.kategorija = activeCategory.value
    const res = await api.get('/posts', { params })
    posts.value      = res.data.data
    pagination.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}
 
function setCategory(cat) {
  activeCategory.value = cat
  currentPage.value    = 1
  fetchPosts()
}
function setSort(s) {
  sort.value        = s
  currentPage.value = 1
  fetchPosts()
}
function loadPage(p) {
  currentPage.value = p
  fetchPosts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
function handleVote({ id, rating_score, user_vote }) {
  const post = posts.value.find(p => p.id_raksts === id)
  if (post) {
    post.rating_score = rating_score
    post.user_vote    = user_vote
  }
}
 
onMounted(fetchPosts)
</script>
 
<style scoped>
.home-header {
  margin-bottom: 28px;
}
.home-title {
  font-family: var(--font-display);
  font-size: 32px;
  font-weight: 800;
  letter-spacing: -0.02em;
  background: var(--accent);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.home-sub {
  color: var(--text-muted);
  margin-top: 4px;
  font-size: 13px;
}
.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.filter-tabs, .sort-tabs {
  display: flex;
  gap: 6px;
}
.filter-tab, .sort-tab {
  background: var(--bg-card);
  border: 1px solid var(--border-soft);
  color: var(--text-muted);
  padding: 6px 14px;
  border-radius: 99px;
  font-size: 12px;
  transition: all 0.2s;
}
.filter-tab:hover, .sort-tab:hover {
  border-color: var(--border);
  color: var(--text);
}
.filter-tab.active, .sort-tab.active {
  background: var(--accent);
  border-color: var(--accent);
  color: #fff;
}
.posts-list { display: flex; flex-direction: column; gap: 10px; }
.post-list-enter-active { transition: all 0.3s ease; }
.post-list-enter-from   { opacity: 0; transform: translateY(-8px); }
.loading-state {
  display: flex;
  justify-content: center;
  padding: 60px 0;
}
.spinner {
  width: 36px; height: 36px;
  border: 3px solid var(--border);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state {
  text-align: center;
  padding: 60px;
  color: var(--text-muted);
}
.pagination {
  display: flex;
  gap: 6px;
  justify-content: center;
  margin-top: 32px;
}
.page-btn {
  background: var(--bg-card);
  border: 1px solid var(--border-soft);
  color: var(--text-muted);
  width: 36px; height: 36px;
  border-radius: var(--radius);
  font-size: 13px;
  transition: all 0.2s;
}
.page-btn:hover { border-color: var(--border); color: var(--text); }
.page-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }
</style>
 