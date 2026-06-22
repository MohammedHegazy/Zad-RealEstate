<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import CompanyCard from '@/components/cards/CompanyCard.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import Pagination from '@/components/ui/Pagination.vue'
import AppSearchInput from '@/components/ui/AppSearchInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import { PAGE_NARRATIVES } from '@/config/journey.js'
import { companiesService } from '@/api/companies.js'
import { placesService } from '@/api/places.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const narrative = PAGE_NARRATIVES.companies
const route = useRoute()
const router = useRouter()

const search = ref(route.query.search || '')
const placesId = ref(route.query.places_id || '')
const sort = ref(route.query.sort || 'newest')
const places = ref([])

const SORT_OPTIONS = [
  { value: 'newest', label: 'الأحدث' },
  { value: 'trust', label: 'الأعلى موثوقية' },
  { value: 'employees', label: 'الأكثر موظفين' },
]

const placeOptions = computed(() => [
  { value: '', label: 'كل المناطق' },
  ...places.value.map((p) => ({
    value: String(p.id),
    label: p.name,
  })),
])

const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
  (params) => companiesService.list(params),
  {
    extraParams: () => ({
      search: route.query.search || undefined,
      places_id: route.query.places_id || undefined,
      sort: route.query.sort || 'newest',
    }),
  },
)

onMounted(async () => {
  try {
    const { data } = await placesService.list({ per_page: 100 })
    places.value = data ?? []
  } catch {
    places.value = []
  }
})

watch(
  () => route.query.search,
  (val) => { search.value = val ?? '' },
)
watch(
  () => route.query.places_id,
  (val) => { placesId.value = val ?? '' },
)
watch(
  () => route.query.sort,
  (val) => { sort.value = val ?? 'newest' },
)

let debounceTimer
watch(search, (val) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.push({ query: { ...route.query, search: val || undefined, page: 1 } })
  }, 400)
})

watch(placesId, (val) => {
  router.push({ query: { ...route.query, places_id: val || undefined, page: 1 } })
})

watch(sort, (val) => {
  router.push({ query: { ...route.query, sort: val || undefined, page: 1 } })
})
</script>

<template>
  <div class="directory-page">
    <div class="container">
      <PageIntro
        :overline="narrative.overline"
        :title="narrative.title"
        :description="narrative.description"
        :step="narrative.step"
        icon="bi-buildings"
      />

      <div class="directory-toolbar">
        <div class="directory-toolbar__search">
          <AppSearchInput
            v-model="search"
            placeholder="ابحث عن شركة…"
          />
        </div>
        <div class="directory-toolbar__filters">
          <div class="filter-wrap">
            <i class="bi bi-pin-map filter-wrap__icon"></i>
            <AppSelect v-model="placesId" :options="placeOptions" />
          </div>
          <div class="filter-wrap">
            <i class="bi bi-sort-down filter-wrap__icon"></i>
            <AppSelect v-model="sort" :options="SORT_OPTIONS" />
          </div>
        </div>
      </div>

      <LoadingSpinner v-if="loading" />
      <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

      <EmptyState
        v-else-if="!items.length"
        icon="bi-briefcase"
        title="لا توجد شركات"
        message="لم يتم تسجيل شركات معتمدة بعد."
      />

      <template v-else>
        <div class="row g-4">
          <div v-for="company in items" :key="company.id" class="col-md-6 col-lg-4">
            <CompanyCard :company="company" />
          </div>
        </div>

        <Pagination
          v-if="pagination"
          :current-page="pagination.current_page"
          :last-page="pagination.last_page"
          :total="pagination.total"
          @page-change="goToPage"
        />
      </template>

      <CtaBanner v-bind="narrative.cta" />
    </div>
  </div>
</template>
