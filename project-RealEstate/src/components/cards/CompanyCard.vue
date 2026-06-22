<script setup>
const PLACEHOLDER =
  'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&q=80'

defineProps({
  company: {
    type: Object,
    required: true,
  },
})

function getBanner(company) {
  return company.banner_image_url ?? company.profile_image_url ?? PLACEHOLDER
}
</script>

<template>
  <article class="company-card">
    <RouterLink :to="`/companies/${company.id}`" class="company-card__media">
      <img
        :src="getBanner(company)"
        :alt="company.company_name"
        class="company-card__image"
        loading="lazy"
      />
      <div class="company-card__overlay"></div>
    </RouterLink>

    <div class="company-card__body">
      <h3 class="company-card__name">
        <RouterLink :to="`/companies/${company.id}`">{{ company.company_name }}</RouterLink>
      </h3>

      <p v-if="company.place?.name" class="company-card__location">
        <i class="bi bi-geo-alt"></i>
        {{ company.place.name }}، {{ company.place.city?.name }}
      </p>

      <p v-if="company.description" class="company-card__desc">
        {{ company.description }}
      </p>

      <div class="company-card__footer">
        <span v-if="company.trust_score" class="company-card__trust">
          <i class="bi bi-shield-check"></i>
          درجة الثقة {{ company.trust_score }}%
        </span>
        <span v-if="company.employees_num" class="company-card__employees">
          <i class="bi bi-people"></i>
          {{ company.employees_num }} موظف
        </span>
      </div>
    </div>
  </article>
</template>
