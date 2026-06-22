<script setup>
import { ref, onMounted } from 'vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import { companyService } from '@/api/company.js'
import { SOCIAL_PLATFORM_OPTIONS, getPlatformStyle } from '@/config/admin.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useConfirmStore } from '@/stores/confirm.js'
const confirmStore = useConfirmStore()
const loading = ref(false)
const error = ref('')
const links = ref([])
const editing = ref(null)
const saving = ref(false)

const newLink = ref({ platform: '', url: '' })

async function fetchLinks() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await companyService.socialLinks()
    links.value = data ?? []
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل روابط التواصل.')
  } finally {
    loading.value = false
  }
}

async function addLink() {
  if (!newLink.value.platform || !newLink.value.url) return
  saving.value = true
  try {
    const { data } = await companyService.addSocialLink({ ...newLink.value })
    links.value.push(data)
    newLink.value = { platform: '', url: '' }
  } catch {
    /* handled by global toast */
  } finally {
    saving.value = false
  }
}

function startEdit(link) {
  editing.value = { ...link }
}

function cancelEdit() {
  editing.value = null
}

async function saveEdit() {
  if (!editing.value) return
  saving.value = true
  try {
    const payload = {}
    if (editing.value.platform) payload.platform = editing.value.platform
    if (editing.value.url) payload.url = editing.value.url
    const { data } = await companyService.updateSocialLink(editing.value.id, payload)
    const idx = links.value.findIndex((l) => l.id === editing.value.id)
    if (idx !== -1) links.value[idx] = data
    editing.value = null
  } catch {
    /* handled by global toast */
  } finally {
    saving.value = false
  }
}

async function deleteLink(linkId) {
  if (!(await confirmStore.show({ message: 'هل أنت متأكد من حذف هذا الرابط؟' }))) return
  try {
    await companyService.removeSocialLink(linkId)
    links.value = links.value.filter((l) => l.id !== linkId)
  } catch {
    /* handled by global toast */
  }
}

onMounted(fetchLinks)

function platformLabel(platform) {
  return SOCIAL_PLATFORM_OPTIONS.find((o) => o.value === platform)?.label ?? platform
}
</script>

<template>
  <div class="admin-company-form__section">
    <h3 class="admin-company-form__section-title">روابط التواصل الاجتماعي</h3>

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchLinks" />

    <template v-else>
      <div v-if="links.length" class="d-flex flex-column gap-2">
        <div
          v-for="link in links"
          :key="link.id"
          class="d-flex align-items-center gap-2 border rounded p-2"
        >
          <template v-if="editing?.id === link.id">
            <AppSelect
              v-model="editing.platform"
              :options="SOCIAL_PLATFORM_OPTIONS"
              size="sm"
            />
            <AppInput v-model="editing.url" type="url" size="sm" class="flex-grow-1" />
            <AppButton variant="primary" size="sm" :disabled="saving" @click="saveEdit">
              <i class="bi bi-check"></i>
            </AppButton>
            <AppButton variant="outline" size="sm" @click="cancelEdit">
              <i class="bi bi-x"></i>
            </AppButton>
          </template>
          <template v-else>
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
            <div class="d-flex gap-1 flex-shrink-0">
              <AppButton variant="outline" size="sm" @click="startEdit(link)">
                <i class="bi bi-pencil"></i>
              </AppButton>
              <AppButton variant="outline" size="sm" @click="deleteLink(link.id)">
                <i class="bi bi-trash"></i>
              </AppButton>
            </div>
          </template>
        </div>
      </div>

      <p v-else class="text-muted">لا توجد روابط تواصل بعد. أضف رابطاً أدناه.</p>

      <div class="d-flex gap-2 mt-3 align-items-end">
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
