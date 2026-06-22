<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import { messagesService } from '@/api/messages.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import AppButton from '@/components/ui/AppButton.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import { getUserName, getUserInitials, getAvatarColor } from '@/utils/user.js'

const POLL_MSGS_MS = 4000
const POLL_ONLINE_MS = 15000

const props = defineProps({
  userId: { type: [Number, String], required: true },
  userName: { type: String, default: '' },
})

const emit = defineEmits(['loaded'])

const auth = useAuthStore()
const messages = ref([])
const newText = ref('')
const loading = ref(false)
const sending = ref(false)
const error = ref('')
const messagesEnd = ref(null)
const messagesContainer = ref(null)
const online = ref(null) // null = unknown, true/false
const lastSeenAgo = ref('')

let pollMsgsTimer = null
let pollOnlineTimer = null

const isSelf = computed(() => String(props.userId) === String(auth.user?.id))

const partnerUser = computed(() => {
  if (!messages.value.length) return null
  const first = messages.value[0]
  return first.sender_id === auth.user?.id ? first.receiver : first.sender
})

const currentUserName = computed(() => {
  if (props.userName) return props.userName
  return getUserName(partnerUser.value)
})

const statusText = computed(() => {
  if (online.value === true) return 'متصل الآن'
  if (online.value === false && lastSeenAgo.value) return `آخر ظهور ${lastSeenAgo.value}`
  if (online.value === false) return 'غير متصل'
  return 'جاري التحقق...'
})

const statusClass = computed(() => online.value ? 'online' : 'offline')

function messagePartner(msg) {
  return msg.sender_id === auth.user?.id ? msg.receiver : msg.sender
}

function shouldShowAvatar(msg, index) {
  if (msg.sender_id === auth.user?.id) return false
  if (index === 0) return true
  const prev = messages.value[index - 1]
  return prev.sender_id !== msg.sender_id
}

function shouldShowName(msg, index) {
  if (msg.sender_id === auth.user?.id) return false
  return shouldShowAvatar(msg, index)
}

function formatTime(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' })
}

function formatDateSeparator(dateStr, index) {
  if (index === 0) return true
  const curr = new Date(dateStr).toDateString()
  const prev = new Date(messages.value[index - 1].created_at).toDateString()
  return curr !== prev
}

function formatDateLabel(dateStr) {
  const d = new Date(dateStr)
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  if (d.toDateString() === today.toDateString()) return 'اليوم'
  if (d.toDateString() === yesterday.toDateString()) return 'أمس'
  return d.toLocaleDateString('ar-SA', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

async function fetchOnlineStatus() {
  if (isSelf.value) return
  try {
    const { data } = await messagesService.getOnlineStatus(props.userId)
    online.value = data?.online ?? false
    lastSeenAgo.value = data?.last_seen_ago ?? ''
  } catch {
    online.value = false
    lastSeenAgo.value = ''
  }
}

async function loadMessages() {
  if (isSelf.value) return
  loading.value = true
  error.value = ''
  try {
    const { data } = await messagesService.getConversation(props.userId, { per_page: 100 })
    messages.value = data ?? []
    emit('loaded', messages.value)
    loading.value = false
    await scrollToBottom()
    initialLoadDone = true
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل الرسائل.')
    loading.value = false
  }
}

async function pollNewMessages() {
  if (isSelf.value || loading.value) return
  try {
    const { data } = await messagesService.getConversation(props.userId, { per_page: 10 })
    if (data?.length) {
      const existingIds = new Set(messages.value.map((m) => m.id))
      const newOnes = data.filter((m) => !existingIds.has(m.id))
      if (newOnes.length) {
        messages.value.push(...newOnes.reverse())
        await scrollToBottom()
      }
    }
  } catch {
    // silent
  }
}

async function sendMessage() {
  const text = newText.value.trim()
  if (!text || sending.value || isSelf.value) return
  sending.value = true
  try {
    const { data } = await messagesService.send(props.userId, text)
    messages.value.push(data)
    newText.value = ''
    await scrollToBottom()
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر إرسال الرسالة.')
  } finally {
    sending.value = false
  }
}

let initialLoadDone = false

async function scrollToBottom() {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

watch(
  () => props.userId,
  () => {
    messages.value = []
    online.value = null
    lastSeenAgo.value = ''
    initialLoadDone = false
    loadMessages()
    fetchOnlineStatus()
  },
)

watch(
  () => messages.value.length,
  () => {
    if (!initialLoadDone) return
    scrollToBottom(true)
  },
)

onMounted(() => {
  if (!isSelf.value) {
    loadMessages()
    fetchOnlineStatus()
    pollMsgsTimer = setInterval(pollNewMessages, POLL_MSGS_MS)
    pollOnlineTimer = setInterval(fetchOnlineStatus, POLL_ONLINE_MS)
  }
})

onUnmounted(() => {
  if (pollMsgsTimer) clearInterval(pollMsgsTimer)
  if (pollOnlineTimer) clearInterval(pollOnlineTimer)
})
</script>

<template>
  <div class="chat-window">
    <div v-if="isSelf" class="chat-window__self-alert">
      لا يمكنك مراسلة نفسك.
    </div>

    <template v-else>
      <div class="chat-window__header">
        <div class="chat-window__header-avatar">
          <span
            class="chat-window__initials"
            :style="{ background: getAvatarColor(partnerUser?.id) }"
          >{{ getUserInitials(partnerUser) }}</span>
          <span
            class="chat-window__online-dot"
            :class="`chat-window__online-dot--${statusClass}`"
          />
        </div>
        <div class="chat-window__header-info">
          <h3>{{ currentUserName }}</h3>
          <span
            class="chat-window__header-status"
            :class="`chat-window__header-status--${statusClass}`"
          >{{ statusText }}</span>
        </div>
      </div>

      <div class="chat-window__messages" ref="messagesContainer">
        <LoadingSpinner v-if="loading" />
        <ErrorAlert v-else-if="error" :message="error" @retry="loadMessages" />

        <template v-else-if="messages.length">
          <template v-for="(msg, i) in messages" :key="msg.id">
            <div class="chat-window__date-sep" v-if="formatDateSeparator(msg.created_at, i)">
              <span>{{ formatDateLabel(msg.created_at) }}</span>
            </div>

            <div
              class="chat-window__row"
              :class="{
                'chat-window__row--sent': msg.sender_id === auth.user?.id,
                'chat-window__row--received': msg.sender_id !== auth.user?.id,
              }"
            >
              <div
                v-if="shouldShowAvatar(msg, i)"
                class="chat-window__avatar"
              >
                <span
                  class="chat-window__initials chat-window__initials--sm"
                  :style="{ background: getAvatarColor(msg.sender_id) }"
                >{{ getUserInitials(messagePartner(msg)) }}</span>
              </div>
              <div v-else class="chat-window__avatar-spacer" />

              <div class="chat-window__group">
                <span
                  v-if="shouldShowName(msg, i)"
                  class="chat-window__name"
                >{{ getUserName(messagePartner(msg)) }}</span>
                <div class="chat-window__bubble">
                  <p>{{ msg.text }}</p>
                  <div class="chat-window__bubble-footer">
                    <small class="chat-window__time">{{ formatTime(msg.created_at) }}</small>
                    <i
                      v-if="msg.sender_id === auth.user?.id"
                      class="chat-window__status"
                      :class="msg.is_read ? 'bi bi-check2-all' : 'bi bi-check2'"
                    />
                  </div>
                </div>
              </div>
            </div>
          </template>
          <div ref="messagesEnd" />
        </template>

        <div v-else-if="!loading && !error" class="chat-window__empty">
          <i class="bi bi-chat-dots chat-window__empty-icon"></i>
          <p>لا توجد رسائل بعد</p>
          <span>ابدأ المحادثة مع {{ currentUserName }}</span>
        </div>
      </div>

      <form class="chat-window__input" @submit.prevent="sendMessage">
        <input
          v-model="newText"
          type="text"
          placeholder="اكتب رسالتك..."
          class="chat-window__field"
          :disabled="sending"
        />
        <AppButton type="submit" variant="primary" :loading="sending" :disabled="!newText.trim()">
          <i class="bi bi-send"></i>
        </AppButton>
      </form>
    </template>
  </div>
</template>

<style scoped>
.chat-window {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 400px;
  border: 1px solid var(--color-border);
  border-radius: 1rem;
  overflow: hidden;
  background: var(--color-surface);
}

.chat-window__self-alert {
  padding: 2rem;
  text-align: center;
  color: var(--color-text-muted);
}

.chat-window__header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--color-border);
  background: var(--color-surface);
}

.chat-window__header-avatar {
  position: relative;
  flex-shrink: 0;
}

.chat-window__header-info h3 {
  margin: 0;
  font-size: 0.95rem;
  color: var(--color-text-primary);
}

.chat-window__header-status {
  font-size: 0.75rem;
  transition: color 0.3s;
}

.chat-window__header-status--online {
  color: var(--color-success, #16a34a);
}

.chat-window__header-status--offline {
  color: var(--color-text-muted);
}

.chat-window__online-dot {
  position: absolute;
  bottom: 1px;
  inset-inline-end: 1px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 2px solid var(--color-surface);
  transition: background 0.3s;
}

.chat-window__online-dot--online {
  background: var(--color-success, #16a34a);
}

.chat-window__online-dot--offline {
  background: var(--color-text-muted);
}

.chat-window__initials {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  color: #fff;
  font-size: 0.9rem;
  font-weight: 600;
  flex-shrink: 0;
}

.chat-window__initials--sm {
  width: 32px;
  height: 32px;
  font-size: 0.75rem;
}

.chat-window__messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  background: var(--color-background);
}

.chat-window__row {
  display: flex;
  gap: 0.5rem;
  max-width: 85%;
}

.chat-window__row--sent {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.chat-window__row--received {
  align-self: flex-start;
}

.chat-window__avatar {
  flex-shrink: 0;
  align-self: flex-end;
}

.chat-window__avatar-spacer {
  width: 32px;
  flex-shrink: 0;
}

.chat-window__group {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.chat-window__name {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-muted);
  padding-inline-start: 0.5rem;
}

.chat-window__bubble {
  padding: 0.5rem 0.85rem;
  border-radius: 1rem;
  background: var(--color-surface);
  border: 1px solid var(--color-border-subtle);
  position: relative;
}

.chat-window__row--sent .chat-window__bubble {
  background: var(--color-primary);
  color: #fff;
  border-color: var(--color-primary);
}

.chat-window__bubble p {
  margin: 0 0 0.15rem;
  font-size: 0.9rem;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
}

.chat-window__bubble-footer {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  justify-content: flex-end;
}

.chat-window__row--received .chat-window__bubble-footer {
  justify-content: flex-start;
}

.chat-window__time {
  font-size: 0.65rem;
  opacity: 0.7;
}

.chat-window__row--sent .chat-window__time {
  opacity: 0.8;
}

.chat-window__status {
  font-size: 0.7rem;
  opacity: 0.8;
}

.chat-window__date-sep {
  display: flex;
  justify-content: center;
  margin: 0.75rem 0;
}

.chat-window__date-sep span {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  background: var(--color-background);
  padding: 0.15rem 0.75rem;
  border-radius: 999px;
  border: 1px solid var(--color-border-subtle);
}

.chat-window__empty {
  text-align: center;
  color: var(--color-text-muted);
  padding: 3rem 1rem;
  margin: auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.chat-window__empty-icon {
  font-size: 2.5rem;
  opacity: 0.4;
}

.chat-window__empty p {
  margin: 0;
  font-size: 1rem;
}

.chat-window__empty span {
  font-size: 0.85rem;
  opacity: 0.7;
}

.chat-window__input {
  display: flex;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--color-border);
  background: var(--color-surface);
}

.chat-window__field {
  flex: 1;
  padding: 0.65rem 1rem;
  border: 1px solid var(--color-border);
  border-radius: 1.5rem;
  background: var(--color-background);
  color: var(--color-text-primary);
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
}

.chat-window__field:focus {
  border-color: var(--color-primary);
}

.chat-window__field::placeholder {
  color: var(--color-text-muted);
}
</style>
