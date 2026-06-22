<script setup>
import { ref, onMounted } from 'vue'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { buyerService } from '@/api/buyer.js'
import { SOCIAL_PLATFORM_OPTIONS, getPlatformStyle } from '@/config/admin.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useConfirmStore } from '@/stores/confirm.js'
const confirmStore = useConfirmStore()

const loading = ref(false)
const error = ref('')
const links = ref([])
const saving = ref(false)

const newLink = ref({ platform: '', url: '' })

async function fetchLinks() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await buyerService.socialLinks()
    links.value = data ?? []
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل روابط التواصل.')
  } finally {
    loading.value = false
  }
}

async function syncLinks() {
  saving.value = true
  error.value = ''
  try {
    const payload = {
      links: links.value.map((l) => ({
        platform: l.platform,
        url: l.url,
      })),
    }
    const { data } = await buyerService.updateSocialLinks(payload)
    links.value = data ?? []
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر حفظ الروابط.')
  } finally {
    saving.value = false
  }
}

async function addLink() {
  if (!newLink.value.platform || !newLink.value.url) return
  links.value.push({ platform: newLink.value.platform, url: newLink.value.url })
  newLink.value = { platform: '', url: '' }
  await syncLinks()
}

async function removeLink(index) {
  if (!(await confirmStore.show({ message: 'هل أنت متأكد من حذف هذا الرابط؟' }))) return
  links.value.splice(index, 1)
  await syncLinks()
}

function platformLabel(platform) {
  return SOCIAL_PLATFORM_OPTIONS.find((o) => o.value === platform)?.label ?? platform
}

onMounted(fetchLinks)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="روابط التواصل الاجتماعي"
      description="إدارة روابطك على منصات التواصل."
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchLinks" />

    <template v-else>
      <div v-if="links.length" class="d-flex flex-column gap-2 mb-3">
        <div
          v-for="(link, index) in links"
          :key="index"
          class="d-flex align-items-center gap-2 border rounded p-2"
        >
          <span
            class="d-inline-flex align-items-center justify-content-center rounded"
            style="width:32px;height:32px;"
            :style="{ backgroundColor: getPlatformStyle(link.platform).color }"
          >
            <i
              :class="getPlatformStyle(link.platform).icon"
              class="text-white"
            ></i>
          </span>
          <span class="fw-semibold small" style="min-width:60px;">{{ platformLabel(link.platform) }}</span>
          <a
            :href="link.url"
            target="_blank"
            rel="noopener noreferrer"
            class="text-truncate flex-grow-1 small text-decoration-none"
          >{{ link.url }}</a>
          <AppButton variant="outline" size="sm" @click="removeLink(index)">
            <i class="bi bi-trash"></i>
          </AppButton>
        </div>
      </div>

      <p v-else class="text-muted mb-3">لا توجد روابط تواصل بعد. أضف رابطاً أدناه.</p>

      <div class="d-flex gap-2 align-items-end">
        <div style="min-width:140px;">
          <AppSelect
            v-model="newLink.platform"
            :options="SOCIAL_PLATFORM_OPTIONS"
            placeholder="اختر المنصة"
          />
        </div>
        <AppInput v-model="newLink.url" type="url" placeholder="https://..." class="flex-grow-1" />
        <AppButton
          variant="primary"
          :disabled="!newLink.platform || !newLink.url || saving"
          @click="addLink"
        >
          <i class="bi bi-plus-lg"></i>
          إضافة
        </AppButton>
      </div>
    </template>
  </div>
</template>
