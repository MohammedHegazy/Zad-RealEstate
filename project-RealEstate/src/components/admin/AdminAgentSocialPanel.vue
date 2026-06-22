<script setup>
import { reactive, ref } from 'vue'

import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { SOCIAL_PLATFORM_OPTIONS } from '@/config/admin.js'
import { AGENT_SOCIALABLE_TYPE, adminSocialLinksService } from '@/api/admin/socialLinks.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useConfirmStore } from '@/stores/confirm.js'

const props = defineProps({
  agent: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['updated'])
const confirmStore = useConfirmStore()

const loading = ref(null)
const error = ref('')

const form = reactive({
  platform: 'facebook',
  url: '',
})

function platformLabel(platform) {
  return SOCIAL_PLATFORM_OPTIONS.find((item) => item.value === platform)?.label ?? platform
}

async function runAction(key, action) {
  loading.value = key
  error.value = ''

  try {
    await action()
    emit('updated')
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تنفيذ الإجراء.')
  } finally {
    loading.value = null
  }
}

async function addLink() {
  if (!form.url.trim()) return

  await runAction('add-link', async () => {
    await adminSocialLinksService.create({
      socialable_type: AGENT_SOCIALABLE_TYPE,
      socialable_id: props.agent.id,
      platform: form.platform,
      url: form.url.trim(),
    })
    form.url = ''
  })
}

async function removeLink(linkId) {
  if (!(await confirmStore.show({ message: 'حذف هذا الرابط؟' }))) return

  return runAction(`delete-${linkId}`, () => adminSocialLinksService.remove(linkId))
}
</script>

<template>
  <div class="admin-agent-social">
    <FormAlert v-if="error" :message="error" variant="error" />

    <div class="admin-agent-social__add">
      <AppFormGroup label="منصة" label-for="agent-social-platform">
        <AppSelect
          id="agent-social-platform"
          v-model="form.platform"
          :options="SOCIAL_PLATFORM_OPTIONS"
        />
      </AppFormGroup>
      <AppFormGroup label="الرابط" label-for="agent-social-url">
        <AppInput id="agent-social-url" v-model="form.url" type="url" placeholder="https://" />
      </AppFormGroup>
      <AppButton
        variant="outline"
        size="sm"
        :disabled="loading === 'add-link' || !form.url.trim()"
        @click="addLink"
      >
        {{ loading === 'add-link' ? 'جاري الإضافة...' : 'إضافة رابط' }}
      </AppButton>
    </div>

    <p v-if="!agent.social_links?.length" class="admin-agent-social__empty">
      لا توجد روابط تواصل اجتماعي بعد.
    </p>

    <ul v-else class="admin-agent-social__list">
      <li v-for="link in agent.social_links" :key="link.id" class="admin-agent-social__item">
        <div>
          <strong>{{ platformLabel(link.platform) }}</strong>
          <a :href="link.url" target="_blank" rel="noopener noreferrer">{{ link.url }}</a>
        </div>
        <TableActionGroup>
          <TableAction
            tone="danger"
            label="حذف"
            :disabled="loading === `delete-${link.id}`"
            @click="removeLink(link.id)"
          />
        </TableActionGroup>
      </li>
    </ul>
  </div>
</template>
