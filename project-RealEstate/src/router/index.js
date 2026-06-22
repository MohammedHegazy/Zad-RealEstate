import { createRouter, createWebHistory } from 'vue-router'

import { adminGuard, agentGuard, authGuard, buyerGuard, companyGuard, guestGuard, ownerGuard } from '@/router/guards.js'
import DashboardPage from '@/views/admin/DashboardPage.vue'
import AdminAgentsListPage from '@/views/admin/agents/AgentsListPage.vue'
import AdminAgentDetailPage from '@/views/admin/agents/AgentDetailPage.vue'
import AdminCitiesListPage from '@/views/admin/cities/CitiesListPage.vue'
import AdminCityDetailPage from '@/views/admin/cities/CityDetailPage.vue'
import AdminCompaniesListPage from '@/views/admin/companies/CompaniesListPage.vue'
import AdminCompanyDetailPage from '@/views/admin/companies/CompanyDetailPage.vue'
import AdminEstatesListPage from '@/views/admin/estates/EstatesListPage.vue'
import AdminEstateDetailPage from '@/views/admin/estates/EstateDetailPage.vue'
import AdminPlacesListPage from '@/views/admin/places/PlacesListPage.vue'
import AdminPlaceDetailPage from '@/views/admin/places/PlaceDetailPage.vue'
import TrustModerationPage from '@/views/admin/trust/TrustModerationPage.vue'
import AdminUsersListPage from '@/views/admin/users/UsersListPage.vue'
import AdminProfilePage from '@/views/admin/ProfilePage.vue'
import AgentDashboardPage from '@/views/agent/DashboardPage.vue'
import AgentEstatesPage from '@/views/agent/EstatesPage.vue'
import AgentCreateEstatePage from '@/views/agent/CreateEstatePage.vue'
import AgentEditEstatePage from '@/views/agent/EditEstatePage.vue'
import AgentProfilePage from '@/views/agent/ProfilePage.vue'
import AgentSocialLinksPage from '@/views/agent/SocialLinksPage.vue'
import AdminUserDetailPage from '@/views/admin/users/UserDetailPage.vue'
import CompanyDashboardPage from '@/views/company/DashboardPage.vue'
import CompanyAgentsPage from '@/views/company/AgentsPage.vue'
import CompanyCreateAgentPage from '@/views/company/CreateAgentPage.vue'
import CompanyEditAgentPage from '@/views/company/EditAgentPage.vue'
import CompanyEstatesPage from '@/views/company/EstatesPage.vue'
import CompanyCreateEstatePage from '@/views/company/CreateEstatePage.vue'
import CompanyEditEstatePage from '@/views/company/EditEstatePage.vue'
import CompanyReviewsPage from '@/views/company/ReviewsPage.vue'
import CompanyProfilePage from '@/views/company/ProfilePage.vue'
import CompanySocialLinksPage from '@/views/company/SocialLinksPage.vue'
import CompanyUserProfilePage from '@/views/company/UserProfilePage.vue'
import CompanyInvestmentsPage from '@/views/company/InvestmentsPage.vue'

import AgentInvestmentsPage from '@/views/agent/InvestmentsPage.vue'
import OwnerDashboardPage from '@/views/owner/DashboardPage.vue'
import OwnerEstatesPage from '@/views/owner/EstatesPage.vue'
import OwnerCreateEstatePage from '@/views/owner/CreateEstatePage.vue'
import OwnerEditEstatePage from '@/views/owner/EditEstatePage.vue'
import OwnerInvestmentAnalyticsPage from '@/views/owner/InvestmentAnalyticsPage.vue'
import BuyerDashboardPage from '@/views/buyer/DashboardPage.vue'
import BuyerProfilePage from '@/views/buyer/ProfilePage.vue'
import BuyerSocialLinksPage from '@/views/buyer/SocialLinksPage.vue'
import BuyerFavoritesPage from '@/views/buyer/FavoritesPage.vue'
import BuyerRecommendationsPage from '@/views/buyer/RecommendationsPage.vue'
import PortfoliosPage from '@/views/buyer/PortfoliosPage.vue'
import CreatePortfolioPage from '@/views/buyer/CreatePortfolioPage.vue'
import PortfolioDetailPage from '@/views/buyer/PortfolioDetailPage.vue'
import InvestmentAnalysesPage from '@/views/buyer/InvestmentAnalysesPage.vue'
import AnalysisDetailPage from '@/views/buyer/AnalysisDetailPage.vue'
import EstateAnalysisPage from '@/views/buyer/EstateAnalysisPage.vue'
import HomePage from '@/views/HomePage.vue'
import LoginPage from '@/views/auth/LoginPage.vue'
import RegisterPage from '@/views/auth/RegisterPage.vue'
import AgentsListPage from '@/views/agents/AgentsListPage.vue'
import AgentDetailPage from '@/views/agents/AgentDetailPage.vue'
import CitiesListPage from '@/views/cities/CitiesListPage.vue'
import CityDetailPage from '@/views/cities/CityDetailPage.vue'
import CompaniesListPage from '@/views/companies/CompaniesListPage.vue'
import CompanyDetailPage from '@/views/companies/CompanyDetailPage.vue'
import EstateDetailPage from '@/views/estates/EstateDetailPage.vue'
import EstatesListPage from '@/views/estates/EstatesListPage.vue'
import EstatesMapPage from '@/views/estates/EstatesMapPage.vue'
import FavoritesPage from '@/views/favorites/FavoritesPage.vue'
import RecommendationsPage from '@/views/recommendations/RecommendationsPage.vue'
import InboxPage from '@/views/chat/InboxPage.vue'
import ChatPage from '@/views/chat/ChatPage.vue'
import PlacesListPage from '@/views/places/PlacesListPage.vue'
import PlaceDetailPage from '@/views/places/PlaceDetailPage.vue'
import ProfilePage from '@/views/profile/ProfilePage.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomePage,
      meta: { title: 'الرئيسية', layout: 'main' },
    },
    {
      path: '/login',
      name: 'login',
      component: LoginPage,
      meta: { title: 'تسجيل الدخول', layout: 'auth', guest: true },
      beforeEnter: guestGuard,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterPage,
      meta: { title: 'إنشاء حساب', layout: 'auth', guest: true },
      beforeEnter: guestGuard,
    },
    {
      path: '/estates',
      name: 'estates',
      component: EstatesListPage,
      meta: { title: 'العقارات', layout: 'main' },
    },
    {
      path: '/estates/map',
      name: 'estates-map',
      component: EstatesMapPage,
      meta: { title: 'البحث على الخريطة', layout: 'main' },
    },
    {
      path: '/estates/:id',
      name: 'estate-detail',
      component: EstateDetailPage,
      meta: { title: 'تفاصيل العقار', layout: 'main' },
    },
    {
      path: '/cities',
      name: 'cities',
      component: CitiesListPage,
      meta: { title: 'المدن', layout: 'main' },
    },
    {
      path: '/cities/:id',
      name: 'city-detail',
      component: CityDetailPage,
      meta: { title: 'تفاصيل المدينة', layout: 'main' },
    },
    {
      path: '/places',
      name: 'places',
      component: PlacesListPage,
      meta: { title: 'المناطق', layout: 'main' },
    },
    {
      path: '/places/:id',
      name: 'place-detail',
      component: PlaceDetailPage,
      meta: { title: 'تفاصيل المنطقة', layout: 'main' },
    },
    {
      path: '/agents',
      name: 'agents',
      component: AgentsListPage,
      meta: { title: 'الوسطاء', layout: 'main' },
    },
    {
      path: '/agents/:id',
      name: 'agent-detail',
      component: AgentDetailPage,
      meta: { title: 'ملف الوسيط', layout: 'main' },
    },
    {
      path: '/proxies',
      redirect: '/agents',
    },
    {
      path: '/proxies/:id',
      redirect: (to) => `/agents/${to.params.id}`,
    },
    {
      path: '/companies',
      name: 'companies',
      component: CompaniesListPage,
      meta: { title: 'الشركات', layout: 'main' },
    },
    {
      path: '/companies/:id',
      name: 'company-detail',
      component: CompanyDetailPage,
      meta: { title: 'ملف الشركة', layout: 'main' },
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfilePage,
      meta: { title: 'حسابي', requiresAuth: true, layout: 'main' },
      beforeEnter: authGuard,
    },
    {
      path: '/favorites',
      name: 'favorites',
      component: FavoritesPage,
      meta: { title: 'المفضلة', requiresAuth: true, layout: 'main' },
      beforeEnter: authGuard,
    },
    {
      path: '/recommendations',
      name: 'recommendations',
      component: RecommendationsPage,
      meta: { title: 'التوصيات', requiresAuth: true, layout: 'main' },
      beforeEnter: authGuard,
    },
    {
      path: '/inbox',
      name: 'inbox',
      component: InboxPage,
      meta: { title: 'الرسائل', requiresAuth: true, layout: 'main' },
      beforeEnter: authGuard,
    },
    {
      path: '/chat/:userId',
      name: 'chat',
      component: ChatPage,
      meta: { title: 'محادثة', requiresAuth: true, layout: 'main' },
      beforeEnter: authGuard,
    },
    {
      path: '/company',
      redirect: '/company/dashboard',
    },
    {
      path: '/company/dashboard',
      name: 'company-dashboard',
      component: CompanyDashboardPage,
      meta: { title: 'لوحة الشركة', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/agents',
      name: 'company-agents',
      component: CompanyAgentsPage,
      meta: { title: 'الوسطاء', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/agents/create',
      name: 'company-agent-create',
      component: CompanyCreateAgentPage,
      meta: { title: 'إضافة وسيط', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/agents/edit/:id(\\d+)',
      name: 'company-agent-edit',
      component: CompanyEditAgentPage,
      meta: { title: 'تعديل وسيط', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/estates',
      name: 'company-estates',
      component: CompanyEstatesPage,
      meta: { title: 'العقارات', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/estates/create',
      name: 'company-estate-create',
      component: CompanyCreateEstatePage,
      meta: { title: 'إضافة عقار', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/estates/edit/:id(\\d+)',
      name: 'company-estate-edit',
      component: CompanyEditEstatePage,
      meta: { title: 'تعديل عقار', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/reviews',
      name: 'company-reviews',
      component: CompanyReviewsPage,
      meta: { title: 'التقييمات', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/profile',
      name: 'company-profile',
      component: CompanyProfilePage,
      meta: { title: 'ملف الشركة', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/user-profile',
      name: 'company-user-profile',
      component: CompanyUserProfilePage,
      meta: { title: 'الملف الشخصي', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/social-links',
      name: 'company-social-links',
      component: CompanySocialLinksPage,
      meta: { title: 'روابط التواصل', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/company/investments',
      name: 'company-investments',
      component: CompanyInvestmentsPage,
      meta: { title: 'تحليلات الاستثمار', layout: 'company' },
      beforeEnter: companyGuard,
    },
    {
      path: '/agent',
      redirect: '/agent/dashboard',
    },
    {
      path: '/agent/dashboard',
      name: 'agent-dashboard',
      component: AgentDashboardPage,
      meta: { title: 'لوحة الوسيط', layout: 'agent' },
      beforeEnter: agentGuard,
    },
    {
      path: '/agent/estates',
      name: 'agent-estates',
      component: AgentEstatesPage,
      meta: { title: 'العقارات', layout: 'agent' },
      beforeEnter: agentGuard,
    },
    {
      path: '/agent/estates/create',
      name: 'agent-estate-create',
      component: AgentCreateEstatePage,
      meta: { title: 'إضافة عقار', layout: 'agent' },
      beforeEnter: agentGuard,
    },
    {
      path: '/agent/estates/edit/:id(\\d+)',
      name: 'agent-estate-edit',
      component: AgentEditEstatePage,
      meta: { title: 'تعديل عقار', layout: 'agent' },
      beforeEnter: agentGuard,
    },
    {
      path: '/agent/profile',
      name: 'agent-profile',
      component: AgentProfilePage,
      meta: { title: 'الملف الشخصي', layout: 'agent' },
      beforeEnter: agentGuard,
    },
    {
      path: '/agent/social-links',
      name: 'agent-social-links',
      component: AgentSocialLinksPage,
      meta: { title: 'روابط التواصل', layout: 'agent' },
      beforeEnter: agentGuard,
    },
    {
      path: '/agent/investments',
      name: 'agent-investments',
      component: AgentInvestmentsPage,
      meta: { title: 'تحليلات الاستثمار', layout: 'agent' },
      beforeEnter: agentGuard,
    },
    {
      path: '/buyer',
      redirect: '/buyer/dashboard',
    },
    {
      path: '/buyer/dashboard',
      name: 'buyer-dashboard',
      component: BuyerDashboardPage,
      meta: { title: 'لوحة المستثمر', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/buyer/favorites',
      name: 'buyer-favorites',
      component: BuyerFavoritesPage,
      meta: { title: 'المفضلة', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/buyer/recommendations',
      name: 'buyer-recommendations',
      component: BuyerRecommendationsPage,
      meta: { title: 'التوصيات', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/buyer/profile',
      name: 'buyer-profile',
      component: BuyerProfilePage,
      meta: { title: 'الملف الشخصي', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/buyer/social-links',
      name: 'buyer-social-links',
      component: BuyerSocialLinksPage,
      meta: { title: 'روابط التواصل', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/buyer/portfolios',
      name: 'buyer-portfolios',
      component: PortfoliosPage,
      meta: { title: 'المحافظ', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/buyer/portfolios/create',
      name: 'buyer-portfolios-create',
      component: CreatePortfolioPage,
      meta: { title: 'محفظة جديدة', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/buyer/portfolios/:id(\\d+)',
      name: 'buyer-portfolios-detail',
      component: PortfolioDetailPage,
      meta: { title: 'المحفظة', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/buyer/investment-analyses',
      name: 'buyer-investment-analyses',
      component: InvestmentAnalysesPage,
      meta: { title: 'التحليلات', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/buyer/investment-analyses/:id(\\d+)',
      name: 'buyer-investment-analysis-detail',
      component: AnalysisDetailPage,
      meta: { title: 'التحليل', layout: 'buyer' },
      beforeEnter: buyerGuard,
    },
    {
      path: '/owner',
      redirect: '/owner/dashboard',
    },
    {
      path: '/owner/dashboard',
      name: 'owner-dashboard',
      component: OwnerDashboardPage,
      meta: { title: 'لوحة المالك', layout: 'owner' },
      beforeEnter: ownerGuard,
    },
    {
      path: '/owner/estates',
      name: 'owner-estates',
      component: OwnerEstatesPage,
      meta: { title: 'عقاراتي', layout: 'owner' },
      beforeEnter: ownerGuard,
    },
    {
      path: '/owner/estates/create',
      name: 'owner-estates-create',
      component: OwnerCreateEstatePage,
      meta: { title: 'إضافة عقار', layout: 'owner' },
      beforeEnter: ownerGuard,
    },
    {
      path: '/owner/estates/edit/:id(\\d+)',
      name: 'owner-estates-edit',
      component: OwnerEditEstatePage,
      meta: { title: 'تعديل عقار', layout: 'owner' },
      beforeEnter: ownerGuard,
    },
    {
      path: '/owner/investment-analytics',
      name: 'owner-investment-analytics',
      component: OwnerInvestmentAnalyticsPage,
      meta: { title: 'التحليلات الاستثمارية', layout: 'owner' },
      beforeEnter: ownerGuard,
    },
    {
      path: '/estates/:id(\\d+)/analyze',
      name: 'estate-analyze',
      component: EstateAnalysisPage,
      meta: { title: 'تحليل استثماري', layout: 'main' },
    },
    {
      path: '/admin/login',
      redirect: (to) => ({
        path: '/login',
        query: { ...to.query, redirect: to.query.redirect ?? '/admin/dashboard' },
      }),
    },
    {
      path: '/admin',
      redirect: '/admin/dashboard',
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: DashboardPage,
      meta: { title: 'لوحة التحكم', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: AdminUsersListPage,
      meta: { title: 'المستخدمون', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/users/:id',
      name: 'admin-user-detail',
      component: AdminUserDetailPage,
      meta: { title: 'ملف المستخدم', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/estates',
      name: 'admin-estates',
      component: AdminEstatesListPage,
      meta: { title: 'العقارات', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/estates/create',
      name: 'admin-estate-create',
      component: AdminEstateDetailPage,
      meta: { title: 'إضافة عقار', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/estates/:id',
      name: 'admin-estate-detail',
      component: AdminEstateDetailPage,
      meta: { title: 'تفاصيل العقار', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/companies',
      name: 'admin-companies',
      component: AdminCompaniesListPage,
      meta: { title: 'الشركات', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/companies/create',
      name: 'admin-company-create',
      component: AdminCompanyDetailPage,
      meta: { title: 'إضافة شركة', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/companies/:id',
      name: 'admin-company-detail',
      component: AdminCompanyDetailPage,
      meta: { title: 'تفاصيل الشركة', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/agents',
      name: 'admin-agents',
      component: AdminAgentsListPage,
      meta: { title: 'الوسطاء', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/agents/create',
      name: 'admin-agent-create',
      component: AdminAgentDetailPage,
      meta: { title: 'إضافة وسيط', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/agents/:id',
      name: 'admin-agent-detail',
      component: AdminAgentDetailPage,
      meta: { title: 'تفاصيل الوسيط', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/cities',
      name: 'admin-cities',
      component: AdminCitiesListPage,
      meta: { title: 'المدن', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/cities/create',
      name: 'admin-city-create',
      component: AdminCityDetailPage,
      meta: { title: 'إضافة مدينة', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/cities/:id',
      name: 'admin-city-detail',
      component: AdminCityDetailPage,
      meta: { title: 'تفاصيل المدينة', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/places',
      name: 'admin-places',
      component: AdminPlacesListPage,
      meta: { title: 'المناطق', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/places/create',
      name: 'admin-place-create',
      component: AdminPlaceDetailPage,
      meta: { title: 'إضافة منطقة', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/places/:id',
      name: 'admin-place-detail',
      component: AdminPlaceDetailPage,
      meta: { title: 'تفاصيل المنطقة', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/trust',
      name: 'admin-trust',
      component: TrustModerationPage,
      meta: { title: 'الثقة والمراجعة', layout: 'admin' },
      beforeEnter: adminGuard,
    },
    {
      path: '/admin/profile',
      name: 'admin-profile',
      component: AdminProfilePage,
      meta: { title: 'الملف الشخصي', layout: 'admin' },
      beforeEnter: adminGuard,
    },
  ],
  scrollBehavior(to, _from, savedPosition) {
    if (to.hash) {
      return { el: to.hash, behavior: 'smooth' }
    }

    if (savedPosition) {
      return savedPosition
    }

    if ((to.meta.layout === 'admin' || to.meta.layout === 'company' || to.meta.layout === 'agent' || to.meta.layout === 'buyer' || to.meta.layout === 'owner') && window.matchMedia('(min-width: 992px)').matches) {
      return { el: '.admin-layout__main', top: 0, behavior: 'smooth' }
    }

    return { top: 0, behavior: 'smooth' }
  },
})

router.afterEach((to) => {
  const base = to.meta.layout === 'admin' ? 'زاد — الإدارة' : to.meta.layout === 'company' ? 'زاد — الشركة' : to.meta.layout === 'agent' ? 'زاد — الوسيط' : to.meta.layout === 'buyer' ? 'زاد — المستثمر' : to.meta.layout === 'owner' ? 'زاد — المالك' : 'زاد للعقارات'
  document.title = to.meta.title ? `${to.meta.title} | ${base}` : base
})

export default router
