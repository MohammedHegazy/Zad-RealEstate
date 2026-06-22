<script setup>
import { onMounted } from 'vue'
import AgentCard from '@/components/cards/AgentCard.vue'
import CityCard from '@/components/cards/CityCard.vue'
import CompanyCard from '@/components/cards/CompanyCard.vue'
import PropertyCard from '@/components/cards/PropertyCard.vue'
import HeroSearch from '@/components/home/HeroSearch.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import SectionHeader from '@/components/ui/SectionHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { useHomePage } from '@/composables/useHomePage.js'
import { formatPrice, formatPropertyType } from '@/composables/useFormatters.js'
import { getEstateImage, getEstateLocation } from '@/utils/estate.js'

const {
  loading,
  error,
  featuredEstate,
  latestEstates,
  cities,
  places,
  agents,
  companies,
  mapStats,
  fetchHomeData,
} = useHomePage()

onMounted(fetchHomeData)
</script>

<template>
  <div class="homepage">
    <!-- Hero -->
    <section class="homepage-hero">
      <div class="homepage-hero__bg"></div>
      <div class="container homepage-hero__content">
        <div class="row align-items-center g-5">
          <div class="col-lg-7">
            <span class="homepage-hero__overline text-overline">منصة عقارية ذكية</span>
            <h1 class="homepage-hero__title">اعثر على عقارك المثالي اين ما تريد</h1>
            <p class="homepage-hero__subtitle">
              آلاف العقارات السكنية والتجارية، خرائط تفاعلية، وكلاء معتمدون، وتحليلات
              استثمارية — كل ذلك في مكان واحد.
            </p>

            <div class="homepage-hero__stats">
              <div class="homepage-hero__stat">
                <strong>{{ mapStats.total || '—' }}</strong>
                <span>عقار نشط</span>
              </div>
              <div class="homepage-hero__stat">
                <strong>{{ cities.length || '—' }}</strong>
                <span>مدينة</span>
              </div>
              <div class="homepage-hero__stat">
                <strong>{{ agents.length || '—' }}</strong>
                <span>وسيط معتمد</span>
              </div>
            </div>
          </div>

          <div v-if="featuredEstate" class="col-lg-5">
            <article class="homepage-hero__featured">
              <img
                :src="getEstateImage(featuredEstate)"
                :alt="featuredEstate.name"
                class="homepage-hero__featured-img"
              />
              <div class="homepage-hero__featured-body">
                <span class="homepage-hero__featured-badge">عقار مميز</span>
                <h3>{{ featuredEstate.name }}</h3>
                <p>
                  <i class="bi bi-geo-alt"></i>
                  {{ getEstateLocation(featuredEstate) }}
                </p>
                <div class="homepage-hero__featured-meta">
                  <span>{{ formatPropertyType(featuredEstate.kind_text) }}</span>
                  <strong>{{ formatPrice(featuredEstate.price) }}</strong>
                </div>
                <AppButton :to="`/estates/${featuredEstate.id}`" variant="primary" size="sm">
                  عرض التفاصيل
                </AppButton>
              </div>
            </article>
          </div>
        </div>

        <HeroSearch />
      </div>
    </section>

    <LoadingSpinner v-if="loading" class="homepage__loader" />

    <ErrorAlert
      v-else-if="error"
      :message="error"
      class="container homepage__error"
      @retry="fetchHomeData"
    />

    <template v-else>
      <!-- Latest listings -->
      <section class="homepage-section">
        <div class="container">
          <SectionHeader
            title="أحدث العقارات"
            subtitle="إضافات جديدة"
            to="/estates"
          />

          <div v-if="latestEstates.length" class="row g-4">
            <div
              v-for="estate in latestEstates"
              :key="estate.id"
              class="col-sm-6 col-lg-3"
            >
              <PropertyCard :estate="estate" />
            </div>
          </div>

          <p v-else class="homepage__empty">لا توجد عقارات متاحة حالياً.</p>
        </div>
      </section>

      <!-- Cities -->
      <section class="homepage-section homepage-section--muted">
        <div class="container">
          <SectionHeader
            title="استكشف حسب المدينة"
            subtitle="وجهات عقارية"
            to="/cities"
          />

          <div v-if="cities.length" class="row g-4">
            <div v-for="city in cities" :key="city.id" class="col-sm-6 col-lg-3">
              <CityCard :city="city" />
            </div>
          </div>
        </div>
      </section>

      <!-- Popular neighborhoods -->
      <section class="homepage-section">
        <div class="container">
          <SectionHeader
            title="أشهر المناطق"
            subtitle="أحياء مميزة"
            to="/places"
          />

          <div v-if="places.length" class="homepage-places">
            <RouterLink
              v-for="place in places"
              :key="place.id"
              :to="`/places/${place.id}`"
              class="homepage-places__chip"
            >
              <span class="homepage-places__name">{{ place.name }}</span>
              <span class="homepage-places__city">{{ place.city?.name }}</span>
              <span v-if="place.active_estates_count" class="homepage-places__count">
                {{ place.active_estates_count }} عقار
              </span>
            </RouterLink>
          </div>
        </div>
      </section>

      <!-- Agents -->
      <section class="homepage-section homepage-section--muted">
        <div class="container">
          <SectionHeader
            title="أفضل الوكلاء"
            subtitle="خبراء معتمدون"
            to="/agents"
          />

          <div v-if="agents.length" class="row g-4">
            <div v-for="agent in agents" :key="agent.id" class="col-sm-6 col-lg-3">
              <AgentCard :agent="agent" />
            </div>
          </div>
        </div>
      </section>

      <!-- Companies -->
      <section class="homepage-section">
        <div class="container">
          <SectionHeader
            title="شركات عقارية موثوقة"
            subtitle="وكلاء معتمدون"
            to="/companies"
          />

          <div v-if="companies.length" class="row g-4">
            <div v-for="company in companies" :key="company.id" class="col-md-4">
              <CompanyCard :company="company" />
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="homepage-cta">
        <div class="container homepage-cta__inner">
          <div>
            <h2>هل تبحث عن استثمار عقاري ذكي؟</h2>
            <p>
              أنشئ حساباً مجانياً للوصول إلى تحليلات العائد على الاستثمار، المحفظة
              الاستثمارية، والتوصيات المخصصة.
            </p>
          </div>
          <div class="homepage-cta__actions">
            <AppButton to="/register" variant="primary" size="lg">ابدأ الآن</AppButton>
            <AppButton to="/estates" variant="outline" size="lg">تصفح العقارات</AppButton>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>
