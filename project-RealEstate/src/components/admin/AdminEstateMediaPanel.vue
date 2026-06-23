<script setup>
import { computed, ref, watch } from 'vue'

import AppFileUpload from '@/components/ui/AppFileUpload.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import VideoPlayerModal from '@/components/ui/VideoPlayerModal.vue'
import { adminEstatesService } from '@/api/admin/estates.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useConfirmStore } from '@/stores/confirm.js'

const props = defineProps({
  estate: {
    type: Object,
    required: true,
  },
  service: {
    type: Object,
    default: () => adminEstatesService,
  },
})

const emit = defineEmits(['updated'])
const confirmStore = useConfirmStore()

const loading = ref(null)
const error = ref('')

const localImages = computed(() => props.estate.images ?? [])
const localAds = computed(() => props.estate.ads ?? [])

const dragIndex = ref(null)
const dragOverIndex = ref(-1)

const selectedVideoUrl = ref('')
const pendingImages = ref([])
const pendingVideos = ref([])
const pendingAds = ref([])

watch(pendingImages, async (files) => {
  if (!files?.length || loading.value) return
  loading.value = 'upload-images'
  error.value = ''
  try {
    const noExisting = !(props.estate.images?.length ?? 0)
    for (let i = 0; i < files.length; i++) {
      const formData = new FormData()
      formData.append('image', files[i])
      formData.append('is_primary', (noExisting && i === 0) ? '1' : '0')
      await props.service.uploadImage(props.estate.id, formData)
    }
    emit('updated')
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر رفع الصور.')
  } finally {
    loading.value = null
    pendingImages.value = []
  }
})

watch(pendingVideos, async (files) => {
  if (!files?.length || loading.value) return
  loading.value = 'upload-videos'
  error.value = ''
  try {
    for (const file of files) {
      const formData = new FormData()
      formData.append('video', file)
      await props.service.uploadVideo(props.estate.id, formData)
    }
    emit('updated')
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر رفع الفيديوهات.')
  } finally {
    loading.value = null
    pendingVideos.value = []
  }
})

watch(pendingAds, async (files) => {
  if (!files?.length || loading.value) return
  loading.value = 'upload-ads'
  error.value = ''
  try {
    const noExisting = !(props.estate.ads?.length ?? 0)
    for (let i = 0; i < files.length; i++) {
      const formData = new FormData()
      formData.append('image', files[i])
      formData.append('is_main', (noExisting && i === 0) ? '1' : '0')
      await props.service.uploadAd(props.estate.id, formData)
    }
    emit('updated')
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر رفع صور الإعلانات.')
  } finally {
    loading.value = null
    pendingAds.value = []
  }
})

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

function setPrimary(imageId) {
  return runAction(`primary-${imageId}`, () =>
    props.service.setPrimaryImage(props.estate.id, imageId),
  )
}

async function removeImage(imageId) {
  if (!(await confirmStore.show({ message: 'حذف هذه الصورة؟' }))) return
  return runAction(`delete-image-${imageId}`, () =>
    props.service.removeImage(props.estate.id, imageId),
  )
}

async function removeVideo(videoId) {
  if (!(await confirmStore.show({ message: 'حذف هذا الفيديو؟' }))) return
  return runAction(`delete-video-${videoId}`, () =>
    props.service.removeVideo(props.estate.id, videoId),
  )
}

function setMainAd(adId) {
  return runAction(`main-ad-${adId}`, () =>
    props.service.setMainAd(props.estate.id, adId),
  )
}

async function removeAd(adId) {
  if (!(await confirmStore.show({ message: 'حذف صورة الإعلان؟' }))) return
  return runAction(`delete-ad-${adId}`, () =>
    props.service.removeAd(props.estate.id, adId),
  )
}

function onDragStart(index) {
  dragIndex.value = index
}

function onDragOver(e, index) {
  e.preventDefault()
  dragOverIndex.value = index
}

function onDragLeave() {
  dragOverIndex.value = -1
}

function onDropImages(index) {
  if (!props.service.reorderImages) return
  if (dragIndex.value === null || dragIndex.value === index) {
    clearDragState()
    return
  }
  const images = [...localImages.value]
  const from = dragIndex.value
  const to = index
  const item = images.splice(from, 1)[0]
  images.splice(to, 0, item)
  clearDragState()
  runAction('reorder-images', () =>
    props.service.reorderImages(props.estate.id, images.map((img) => img.id)),
  )
}

function onDropAds(index) {
  if (!props.service.reorderAds) return
  if (dragIndex.value === null || dragIndex.value === index) {
    clearDragState()
    return
  }
  const ads = [...localAds.value]
  const from = dragIndex.value
  const to = index
  const item = ads.splice(from, 1)[0]
  ads.splice(to, 0, item)
  clearDragState()
  runAction('reorder-ads', () =>
    props.service.reorderAds(props.estate.id, ads.map((ad) => ad.id)),
  )
}

function clearDragState() {
  dragIndex.value = null
  dragOverIndex.value = -1
}
</script>

<template>
  <div class="admin-estate-media">
    <p v-if="error" class="admin-estate-media__error">{{ error }}</p>

    <section class="admin-estate-media__block">
      <div class="admin-estate-media__header">
        <h4>معرض الصور</h4>
      </div>
      <AppFileUpload
        accept="image/*"
        :multiple="true"
        hint="اسحب الصور أو اضغط لاختيارها — يمكنك اختيار عدة صور"
        v-model="pendingImages"
      />

      <div v-if="estate.images?.length" class="admin-estate-media__grid">
        <article
          v-for="(image, index) in estate.images"
          :key="image.id"
          class="admin-estate-media__card"
          :class="{
            'admin-estate-media__card--primary': image.is_primary,
            'admin-estate-media__card--drag-over': dragOverIndex === index,
          }"
          :draggable="Boolean(props.service.reorderImages)"
          @dragstart="onDragStart(index)"
          @dragover="(e) => onDragOver(e, index)"
          @dragleave="onDragLeave"
          @drop="onDropImages(index)"
          @dragend="clearDragState"
        >
          <img :src="image.image_url" :alt="estate.name" />
          <div class="admin-estate-media__card-actions">
            <TableActionGroup>
              <TableAction
                v-if="!image.is_primary"
                tone="success"
                label="رئيسية"
                :disabled="Boolean(loading)"
                @click="setPrimary(image.id)"
              />
              <TableAction
                tone="danger"
                label="حذف"
                :disabled="Boolean(loading)"
                @click="removeImage(image.id)"
              />
            </TableActionGroup>
          </div>
        </article>
      </div>
      <p v-else class="admin-estate-media__empty">لا توجد صور بعد.</p>
    </section>

    <section class="admin-estate-media__block">
      <div class="admin-estate-media__header">
        <h4>الفيديوهات</h4>
      </div>
      <AppFileUpload
        accept="video/*"
        :multiple="true"
        hint="اسحب الفيديوهات أو اضغط لاختيارها — يمكنك اختيار عدة فيديوهات"
        v-model="pendingVideos"
      />

      <div v-if="estate.videos?.length" class="admin-estate-media__grid">
        <article v-for="video in estate.videos" :key="video.id" class="admin-estate-media__card admin-estate-media__card--video" role="button" tabindex="0" @click="selectedVideoUrl = video.video_url" @keydown.enter="selectedVideoUrl = video.video_url">
          <span class="admin-estate-media__video-thumb">
            <i class="bi bi-play-circle-fill admin-estate-media__play-icon"></i>
          </span>
          <div class="admin-estate-media__card-actions">
            <TableAction
              tone="danger"
              label="حذف"
              :disabled="Boolean(loading)"
              @click="removeVideo(video.id)"
            />
          </div>
        </article>
      </div>
      <p v-else class="admin-estate-media__empty">لا توجد فيديوهات.</p>
    </section>

    <section class="admin-estate-media__block">
      <div class="admin-estate-media__header">
        <h4>صور الإعلانات</h4>
      </div>
      <AppFileUpload
        accept="image/*"
        :multiple="true"
        hint="اسحب صور الإعلانات أو اضغط لاختيارها — يمكنك اختيار عدة صور"
        v-model="pendingAds"
      />

      <div v-if="estate.ads?.length" class="admin-estate-media__grid">
        <article
          v-for="(ad, index) in estate.ads"
          :key="ad.id"
          class="admin-estate-media__card"
          :class="{
            'admin-estate-media__card--primary': ad.is_main,
            'admin-estate-media__card--drag-over': dragOverIndex === index,
          }"
          :draggable="Boolean(props.service.reorderAds)"
          @dragstart="onDragStart(index)"
          @dragover="(e) => onDragOver(e, index)"
          @dragleave="onDragLeave"
          @drop="onDropAds(index)"
          @dragend="clearDragState"
        >
          <img :src="ad.image_url" :alt="`إعلان ${ad.id}`" />
          <div class="admin-estate-media__card-actions">
            <TableActionGroup>
              <TableAction
                v-if="!ad.is_main"
                tone="success"
                label="رئيسي"
                :disabled="Boolean(loading)"
                @click="setMainAd(ad.id)"
              />
              <TableAction
                tone="danger"
                label="حذف"
                :disabled="Boolean(loading)"
                @click="removeAd(ad.id)"
              />
            </TableActionGroup>
          </div>
        </article>
      </div>
      <p v-else class="admin-estate-media__empty">لا توجد صور إعلانات.</p>
    </section>

    <VideoPlayerModal
      :show="Boolean(selectedVideoUrl)"
      :video-url="selectedVideoUrl"
      @close="selectedVideoUrl = ''"
    />
  </div>
</template>
