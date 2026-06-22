<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { citiesService } from '@/api/cities.js'
import AppButton from '@/components/ui/AppButton.vue'
import AppSearchInput from '@/components/ui/AppSearchInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'

const router = useRouter()

const cities = ref([])
const search = ref('')
const cityId = ref('')
const typeText = ref('')
const listingType = ref('')

const propertyTypes = [
  { value: '', label: 'كل الأنواع' },
  { value: 'residential', label: 'سكني' },
  { value: 'commercial', label: 'تجاري' },
]

const listingTypes = [
  { value: '', label: 'بيع أو إيجار' },
  { value: 'sale', label: 'للبيع' },
  { value: 'rent', label: 'للإيجار' },
]

const cityOptions = computed(() => [
  { value: '', label: 'كل المدن' },
  ...cities.value.map((city) => ({
    value: city.id,
    label: city.name,
  })),
])

onMounted(async () => {
  try {
    const { data } = await citiesService.list({ per_page: 20 })
    cities.value = data ?? []
  } catch {
    cities.value = []
  }
})

function handleSearch() {
  const query = {}

  if (search.value.trim()) query.search = search.value.trim()
  if (cityId.value) query.cities_id = cityId.value
  if (typeText.value) query.type_text = typeText.value
  if (listingType.value) query.listing_type = listingType.value

  router.push({ path: '/estates', query })
}
</script>

<template>
  <form class="hero-search" @submit.prevent="handleSearch">
    <div class="hero-search__field hero-search__field--wide">
      <label class="hero-search__label" for="hero-search">ابحث عن عقار</label>
      <AppSearchInput
        id="hero-search"
        v-model="search"
        placeholder="شقة، فيلا، مكتب…"
        size="lg"
      />
    </div>

    <div class="hero-search__field">
      <label class="hero-search__label" for="hero-city">المدينة</label>
      <div class="hero-search__input-wrap">
        <i class="bi bi-geo-alt hero-search__input-icon"></i>
        <AppSelect id="hero-city" v-model="cityId" size="lg" :options="cityOptions" />
      </div>
    </div>

    <div class="hero-search__field">
      <label class="hero-search__label" for="hero-type">النوع</label>
      <div class="hero-search__input-wrap">
        <i class="bi bi-building hero-search__input-icon"></i>
        <AppSelect id="hero-type" v-model="typeText" size="lg" :options="propertyTypes" />
      </div>
    </div>

    <div class="hero-search__field">
      <label class="hero-search__label" for="hero-listing">الغرض</label>
      <div class="hero-search__input-wrap">
        <i class="bi bi-tag hero-search__input-icon"></i>
        <AppSelect id="hero-listing" v-model="listingType" size="lg" :options="listingTypes" />
      </div>
    </div>

    <AppButton type="submit" variant="primary" size="lg" class="hero-search__submit">
      <i class="bi bi-search"></i>
      بحث
    </AppButton>
  </form>
</template>
