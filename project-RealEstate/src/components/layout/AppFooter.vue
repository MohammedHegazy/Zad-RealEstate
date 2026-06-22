<script setup>
const currentYear = new Date().getFullYear()

const footerLinks = [
  { label: 'العقارات', to: '/estates' },
  { label: 'المدن', to: '/cities' },
  { label: 'الوسطاء', to: '/agents' },
  { label: 'الشركات', to: '/companies' },
]
</script>

<template>
  <footer class="app-footer">
    <div class="container">
      <div class="row g-4 app-footer__grid">
        <div class="col-md-4">
          <RouterLink to="/" class="app-footer__brand">
            <i class="bi bi-building"></i>
            زاد للعقارات
          </RouterLink>
          <p class="app-footer__desc">
            منصة عقارية متكاملة لبيع وشراء وإيجار العقارات، مع تحليلات استثمارية وخرائط
            تفاعلية ووكلاء معتمدين.
          </p>
        </div>

        <div class="col-md-4">
          <h4 class="app-footer__heading">روابط سريعة</h4>
          <ul class="app-footer__links">
            <li v-for="link in footerLinks" :key="link.to">
              <RouterLink :to="link.to">{{ link.label }}</RouterLink>
            </li>
          </ul>
        </div>

        <div class="col-md-4">
          <h4 class="app-footer__heading">تواصل معنا</h4>
          <ul class="app-footer__contact">
            <li><i class="bi bi-envelope"></i> info@ziad-realestate.com</li>
            <li><i class="bi bi-telephone"></i> +963 11 000 0000</li>
            <li><i class="bi bi-geo-alt"></i> دمشق، سوريا</li>
          </ul>
        </div>
      </div>

      <div class="app-footer__bottom">
        <p>&copy; {{ currentYear }} زاد للعقارات. جميع الحقوق محفوظة.</p>
      </div>
    </div>
  </footer>
</template>
