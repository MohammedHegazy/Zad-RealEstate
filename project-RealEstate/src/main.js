import { createApp } from 'vue'
import { createPinia } from 'pinia'

import '@/styles/index.css'

import App from './App.vue'
import router from './router'
import { useAuthStore } from '@/stores/auth.js'
import { useToastStore } from '@/stores/toast.js'
import { setToastHandler } from '@/api/client.js'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Restore session from localStorage token on app load
useAuthStore(pinia).fetchMe()

// Wire global toast notifications
const toastStore = useToastStore(pinia)
setToastHandler({
  success: (msg) => toastStore.success(msg),
  error: (msg) => toastStore.error(msg),
})

app.mount('#app')

// Bootstrap JS — navbar collapse toggle
import('bootstrap/dist/js/bootstrap.bundle.min.js')
