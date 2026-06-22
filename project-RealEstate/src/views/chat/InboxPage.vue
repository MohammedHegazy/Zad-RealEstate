<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { messagesService } from '@/api/messages.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import { getUserName, getUserInitials, getAvatarColor } from '@/utils/user.js'

const router = useRouter()
const auth = useAuthStore()
const loading = ref(false)
const error = ref(null)
const allMessages = ref([])
const searchQuery = ref('')
const onlineStatuses = ref({})
let pollTimer = null

function isToday(d) {
  return d.toDateString() === new Date().toDateString()
}

function isYesterday(d) {
  const y = new Date()
  y.setDate(y.getDate() - 1)
  return d.toDateString() === y.toDateString()
}

function isThisWeek(d) {
  const now = new Date()
  const weekStart = new Date(now)
  weekStart.setDate(now.getDate() - now.getDay())
  weekStart.setHours(0, 0, 0, 0)
  return d >= weekStart
}

const groups = computed(() => {
  const grouped = {}
  allMessages.value.forEach((msg) => {
    const otherId = msg.sender_id === auth.user?.id ? msg.receiver_id : msg.sender_id
    const otherUser = msg.sender_id === auth.user?.id ? msg.receiver : msg.sender
    if (!grouped[otherId] || new Date(msg.created_at) > new Date(grouped[otherId].created_at)) {
      grouped[otherId] = {
        ...msg,
        other_user: otherUser,
        unread_count: 0,
        yourself: msg.sender_id === auth.user?.id,
      }
    }
  })
  allMessages.value.forEach((msg) => {
    const otherId = msg.sender_id === auth.user?.id ? msg.receiver_id : msg.sender_id
    if (msg.sender_id !== auth.user?.id && !msg.is_read) {
      if (grouped[otherId]) grouped[otherId].unread_count++
    }
  })

  let entries = Object.values(grouped)

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    entries = entries.filter((e) => getUserName(e.other_user).toLowerCase().includes(q))
  }

  const sections = { today: [], yesterday: [], week: [], older: [] }

  entries.forEach((e) => {
    const d = new Date(e.created_at)
    if (isToday(d)) sections.today.push(e)
    else if (isYesterday(d)) sections.yesterday.push(e)
    else if (isThisWeek(d)) sections.week.push(e)
    else sections.older.push(e)
  })

  const sorted = (arr) =>
    arr.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))

  const result = []
  if (sections.today.length) result.push({ label: 'اليوم', items: sorted(sections.today) })
  if (sections.yesterday.length) result.push({ label: 'أمس', items: sorted(sections.yesterday) })
  if (sections.week.length) result.push({ label: 'هذا الأسبوع', items: sorted(sections.week) })
  if (sections.older.length) result.push({ label: 'سابقاً', items: sorted(sections.older) })

  return result
})

function getUniquePartners() {
  const ids = new Set()
  allMessages.value.forEach((msg) => {
    const otherId = msg.sender_id === auth.user?.id ? msg.receiver_id : msg.sender_id
    if (otherId) ids.add(otherId)
  })
  return [...ids]
}

async function fetchOnlineStatuses() {
  const ids = getUniquePartners()
  if (!ids.length) return
  const results = await Promise.allSettled(
    ids.map((id) => messagesService.getOnlineStatus(id).then((r) => ({ id, ...r.data }))),
  )
  const map = { ...onlineStatuses.value }
  results.forEach((r) => {
    if (r.status === 'fulfilled' && r.value) {
      map[r.value.id] = { online: r.value.online, last_seen_ago: r.value.last_seen_ago ?? '' }
    }
  })
  onlineStatuses.value = map
}

async function fetchConversations() {
  loading.value = true
  error.value = null
  try {
    const { data } = await messagesService.listConversations({ per_page: 100 })
    allMessages.value = data ?? []
    await fetchOnlineStatuses()
    if (pollTimer) clearInterval(pollTimer)
    pollTimer = setInterval(fetchConversationsSilent, 15000)
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل المحادثات.')
  } finally {
    loading.value = false
  }
}

async function fetchConversationsSilent() {
  try {
    const { data } = await messagesService.listConversations({ per_page: 100 })
    allMessages.value = data ?? []
    fetchOnlineStatuses()
  } catch {
    // silent
  }
}

function otherUser(item) {
  return item.other_user
}

function formatTime(dateStr) {
  const d = new Date(dateStr)
  const now = new Date()
  const diffMs = now - d
  const diffMin = Math.floor(diffMs / 60000)
  if (diffMin < 1) return 'الآن'
  if (diffMin < 60) return `${diffMin}د`
  if (isToday(d)) return d.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' })
  if (isYesterday(d)) return 'أمس'
  return d.toLocaleDateString('ar-SA', { day: 'numeric', month: 'short' })
}

function previewText(item) {
  if (item.yourself) return `أنت: ${item.text}`
  return item.text
}

function openChat(item) {
  const otherId = item.sender_id === auth.user?.id ? item.receiver_id : item.sender_id
  router.push(`/chat/${otherId}`)
}

onMounted(fetchConversations)

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader title="الرسائل" />

    <div class="inbox">
      <div class="inbox__search">
        <i class="bi bi-search inbox__search-icon"></i>
        <input
          v-model="searchQuery"
          type="text"
          class="inbox__search-input"
          placeholder="ابحث عن محادثة..."
        />
      </div>

      <LoadingSpinner v-if="loading" />
      <ErrorAlert v-else-if="error" :message="error" @retry="fetchConversations" />

      <template v-else-if="groups.length">
        <div v-for="group in groups" :key="group.label" class="inbox__group">
          <span class="inbox__group-label">{{ group.label }}</span>
          <div class="inbox__list">
            <button
              v-for="item in group.items"
              :key="item.other_user?.id ?? item.id"
              type="button"
              class="inbox__item"
              :class="{ 'inbox__item--unread': item.unread_count > 0 }"
              @click="openChat(item)"
            >
              <div class="inbox__item-avatar-wrap">
                <span
                  class="inbox__item-avatar"
                  :style="{ background: getAvatarColor(otherUser(item)?.id) }"
                >{{ getUserInitials(otherUser(item)) }}</span>
                <span
                  v-if="onlineStatuses[otherUser(item)?.id]?.online"
                  class="inbox__item-online-dot"
                />
              </div>

              <div class="inbox__item-body">
                <div class="inbox__item-top">
                  <span
                    class="inbox__item-name"
                    :class="{ 'inbox__item-name--unread': item.unread_count > 0 }"
                  >{{ getUserName(otherUser(item)) }}</span>
                  <span class="inbox__item-time">{{ formatTime(item.created_at) }}</span>
                </div>
                <div class="inbox__item-bottom">
                  <span
                    class="inbox__item-preview"
                    :class="{ 'inbox__item-preview--unread': item.unread_count > 0 }"
                  >{{ previewText(item) }}</span>
                  <span
                    v-if="item.unread_count > 0"
                    class="inbox__item-badge"
                  >{{ item.unread_count > 99 ? '99+' : item.unread_count }}</span>
                </div>
              </div>
            </button>
          </div>
        </div>
      </template>

      <EmptyState
        v-else-if="!loading && !error && !searchQuery"
        icon="bi-chat-dots"
        title="لا توجد محادثات"
        message="تصفّح العقارات وتواصل مع المالكين."
      >
        <AppButton to="/estates" variant="primary">تصفح العقارات</AppButton>
      </EmptyState>

      <div
        v-else-if="!loading && !error && searchQuery"
        class="inbox__empty-search"
      >
        <i class="bi bi-search inbox__empty-search-icon"></i>
        <p>لا توجد نتائج لـ "{{ searchQuery }}"</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.inbox {
  max-width: 640px;
  margin: 0 auto;
}

.inbox__search {
  position: relative;
  margin-bottom: 1.25rem;
}

.inbox__search-icon {
  position: absolute;
  top: 50%;
  inset-inline-start: 1rem;
  transform: translateY(-50%);
  color: var(--color-text-muted);
  font-size: 0.9rem;
  pointer-events: none;
}

.inbox__search-input {
  width: 100%;
  padding: 0.7rem 1rem 0.7rem 2.5rem;
  border: 1px solid var(--color-border);
  border-radius: 0.75rem;
  background: var(--color-surface);
  color: var(--color-text-primary);
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.inbox__search-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb, 79, 70, 229), 0.15);
}

.inbox__search-input::placeholder {
  color: var(--color-text-muted);
}

.inbox__group {
  margin-bottom: 1.25rem;
}

.inbox__group-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.4rem;
  padding-inline-start: 0.25rem;
}

.inbox__list {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.inbox__item {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  width: 100%;
  padding: 0.85rem 1rem;
  border: none;
  border-bottom: 1px solid var(--color-border-subtle);
  background: transparent;
  color: var(--color-text-primary);
  cursor: pointer;
  text-align: start;
  transition: background 0.15s;
}

.inbox__item:last-child {
  border-bottom: none;
}

.inbox__item:hover {
  background: var(--color-surface-hover, var(--color-surface-sunken));
}

.inbox__item--unread {
  background: var(--color-surface-sunken);
}

.inbox__item--unread:hover {
  background: var(--color-surface-hover, var(--color-surface-sunken));
}

.inbox__item-avatar-wrap {
  position: relative;
  flex-shrink: 0;
}

.inbox__item-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
}

.inbox__item-online-dot {
  position: absolute;
  bottom: 1px;
  inset-inline-end: 1px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: var(--color-success, #16a34a);
  border: 2px solid var(--color-surface);
}

.inbox__item-body {
  flex: 1;
  min-width: 0;
}

.inbox__item-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.2rem;
}

.inbox__item-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--color-text-primary);
}

.inbox__item-name--unread {
  font-weight: 700;
}

.inbox__item-time {
  font-size: 0.7rem;
  color: var(--color-text-muted);
  white-space: nowrap;
  flex-shrink: 0;
}

.inbox__item-bottom {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.inbox__item-preview {
  flex: 1;
  font-size: 0.8rem;
  color: var(--color-text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.inbox__item-preview--unread {
  color: var(--color-text-primary);
  font-weight: 500;
}

.inbox__item-badge {
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: var(--color-primary);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  line-height: 22px;
  text-align: center;
  flex-shrink: 0;
}

.inbox__empty-search {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--color-text-muted);
}

.inbox__empty-search-icon {
  font-size: 2rem;
  opacity: 0.4;
  margin-bottom: 0.5rem;
  display: block;
}

.inbox__empty-search p {
  margin: 0;
  font-size: 0.9rem;
}
</style>
