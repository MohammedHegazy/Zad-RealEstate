# دليل ملفات مشروع زاد العقاري

هذا الملف يشرح بنية الواجهة الأمامية والباكند في المشروع بطريقة تعليمية. الهدف منه أن يفهم الطالب أين يبدأ، لماذا يوجد كل نوع من الملفات، وكيف تنتقل البيانات من الشاشة إلى قاعدة البيانات ثم تعود إلى المستخدم.

> ملاحظة مهمة: المشروع مقسوم إلى تطبيقين رئيسيين:
>
> - `project-RealEstate`: الواجهة الأمامية Frontend مبنية بـ Vue و Vite.
> - `project-RealEstate_database`: الباكند Backend مبني بـ Laravel، وفيه قاعدة البيانات، الـ API، الاختبارات، وخدمة الذكاء الاصطناعي للأسعار.

## 1. الصورة الكبيرة للنظام

النظام عبارة عن منصة عقارية فيها عدة أنواع مستخدمين:

- زائر: يتصفح العقارات والمدن والمناطق والشركات والوسطاء.
- مشتري / مستثمر: يحفظ المفضلة، يرى التوصيات، يحلل الاستثمار، ويدير المحافظ.
- مالك: يضيف ويدير عقاراته ويتابع التحليلات.
- وسيط: يدير عقاراته وملفه وروابطه.
- شركة: تدير الوسطاء والعقارات والتقييمات وروابط التواصل.
- مدير: يدير المستخدمين والعقارات والشركات والوسطاء والمدن والمناطق والمراجعات.

تدفق العمل المعتاد:

1. المستخدم يفتح صفحة Vue مثل `EstateDetailPage.vue`.
2. الصفحة تستخدم composable مثل `useEstateDetail.js` لجلب وتنظيم الحالة.
3. الـ composable يستدعي ملف API مثل `estates.js`.
4. ملف API يستخدم العميل العام `client.js` لإرسال HTTP request.
5. Laravel يستقبل الطلب من ملف route مثل `routes/api/v1/public.php`.
6. route يوجه الطلب إلى Controller مثل `EstateController.php`.
7. Controller يستخدم Request للتحقق من البيانات، Model للتعامل مع قاعدة البيانات، Service للحسابات أو المنطق، ثم يعيد JSON.
8. الواجهة تستقبل JSON وتعرضه داخل components.

## 2. تشغيل المشروع

### الواجهة الأمامية

المجلد: `project-RealEstate`

```bash
npm install
npm run dev
```

أهم الأوامر:

- `npm run dev`: تشغيل Vite أثناء التطوير.
- `npm run build`: بناء نسخة production.
- `npm run preview`: معاينة نسخة البناء.
- `npm run lint`: تشغيل أدوات فحص الكود.
- `npm run format`: تنسيق ملفات `src`.

### الباكند

المجلد: `project-RealEstate_database`

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

أهم الأوامر:

- `composer run dev`: يشغل Laravel server والـ queue والـ logs و Vite الخاص بملفات Laravel.
- `composer test` أو `php artisan test`: تشغيل الاختبارات.
- `php artisan migrate`: إنشاء جداول قاعدة البيانات.
- `php artisan db:seed`: إضافة بيانات تجريبية.
- `php artisan route:list`: عرض كل endpoints.

### خدمة توقع الأسعار بالذكاء الاصطناعي

المجلد: `project-RealEstate_database/ml/pricing`

```bash
pip install -r requirements.txt
python server.py
```

هذه الخدمة Flask بسيطة تستخدم ملفات model محفوظة لتوقع سعر العقار. Laravel يتواصل معها من خلال `PricePredictionClient.php`.

## 3. مكتبات الواجهة الأمامية

ملف الحزم: `project-RealEstate/package.json`

- `vue`: إطار بناء الواجهة. كل صفحة أو مكون غالباً ملف `.vue`.
- `vite`: أداة التطوير والبناء السريعة.
- `vue-router`: إدارة الصفحات والمسارات داخل SPA.
- `pinia`: إدارة الحالة المشتركة مثل المستخدم الحالي والتنبيهات.
- `bootstrap`: نظام جاهز للتخطيط والمكونات.
- `bootstrap-icons`: أيقونات Bootstrap.
- `leaflet`: خرائط تفاعلية لعرض مواقع العقارات.
- `eslint`, `oxlint`, `prettier`: جودة وتنسيق الكود.

## 4. مكتبات الباكند

ملف الحزم: `project-RealEstate_database/composer.json`

- `laravel/framework`: إطار Laravel الأساسي.
- `laravel/sanctum`: تسجيل دخول API باستخدام token.
- `laravel/tinker`: تجربة أوامر PHP و Eloquent من الطرفية.
- `phpunit`: اختبارات.
- `fakerphp/faker`: بيانات وهمية للـ factories والـ seeders.
- `laravel/pint`: تنسيق كود PHP.
- `laravel/pail`: متابعة logs أثناء التطوير.

## 5. شرح الواجهة الأمامية

### 5.1 ملفات البداية

- `src/main.js`: نقطة تشغيل Vue. ينشئ التطبيق، يركب Pinia و Router، يسترجع جلسة المستخدم من `localStorage`، يربط نظام التنبيهات، ثم يعمل mount على `#app`.
- `src/App.vue`: الغلاف الأعلى للتطبيق. يختار layout المناسب حسب `route.meta.layout` مثل admin أو company أو main، ويعرض `RouterView`، ويضع `ToastNotification` و `ConfirmDialog` مرة واحدة على مستوى التطبيق.
- `src/router/index.js`: تعريف كل صفحات التطبيق ومساراتها وحراسها وعناوينها. هذا الملف هو خريطة التنقل الرئيسية.
- `src/router/guards.js`: حراس الوصول. يمنع غير المسجل من الصفحات المحمية، ويمنع المستخدم العادي من دخول لوحة المدير أو الشركة أو الوسيط.

### 5.2 الإعدادات

- `src/config/api.js`: عنوان API الافتراضي `http://localhost:8000/api/v1`، headers الافتراضية، وأسماء مفاتيح تخزين token والمستخدم.
- `src/config/auth.js`: إعدادات متعلقة بالمصادقة والنماذج أو أدوار المستخدم.
- `src/config/admin.js`: إعدادات تستخدمها صفحات الإدارة.
- `src/config/agents.js`: ثوابت وتنسيقات خاصة بالوسطاء.
- `src/config/estates.js`: خيارات وأنواع وحقول مرتبطة بالعقارات.
- `src/config/investments.js`: ثوابت التحليل الاستثماري والمحافظ.
- `src/config/journey.js`: إعدادات رحلة المستخدم أو خطوات العرض في الواجهة.

### 5.3 طبقة الاتصال مع API

هذه الملفات موجودة في `src/api`. فكرتها أن الصفحة لا تكتب `fetch` مباشرة، بل تستدعي دالة واضحة مثل `estateService.list()`.

- `src/api/client.js`: العميل المركزي لكل طلبات HTTP. يضيف `Authorization: Bearer token`، يبني query params، يعالج JSON، ويحول أخطاء Laravel إلى Error مفهوم للواجهة.
- `src/api/errors.js`: شكل خطأ موحد للواجهة.
- `src/api/errorHandler.js`: وظائف مساعدة لمعالجة الأخطاء وعرض رسائل مناسبة.
- `src/api/formData.js`: يساعد في تحويل البيانات إلى `FormData` عند رفع صور أو ملفات.
- `src/api/index.js`: ملف تجميع exports ليسهل الاستيراد من مكان واحد.
- `src/api/auth.js`: login/register/logout/me.
- `src/api/profile.js`: قراءة وتعديل الملف الشخصي.
- `src/api/users.js`: عمليات المستخدمين العامة أو الإدارية حسب الاستخدام.
- `src/api/estates.js`: عرض وتفاصيل وإنشاء وتعديل العقارات العامة.
- `src/api/myEstates.js`: عقارات المستخدم الحالي، خصوصاً المالك أو الوسيط.
- `src/api/cities.js`: المدن.
- `src/api/places.js`: المناطق.
- `src/api/agents.js`: قائمة وتفاصيل الوسطاء للعرض العام.
- `src/api/agent.js`: عمليات خاصة بحساب الوسيط.
- `src/api/companies.js`: قائمة وتفاصيل الشركات للعرض العام.
- `src/api/company.js`: عمليات خاصة بحساب الشركة.
- `src/api/buyer.js`: عمليات لوحة المشتري أو المستثمر.
- `src/api/owner.js`: عمليات لوحة المالك.
- `src/api/favorites.js`: إضافة وحذف وقراءة المفضلة.
- `src/api/recommendations.js`: جلب التوصيات وتحديثها.
- `src/api/reviews.js`: التقييمات والمراجعات.
- `src/api/messages.js`: المحادثات والرسائل.
- `src/api/investments.js`: التحليلات الاستثمارية.
- `src/api/portfolios.js`: المحافظ الاستثمارية وعناصرها.
- `src/api/pricePredictions.js`: توقع أسعار العقارات.

ملفات الإدارة داخل `src/api/admin`:

- `client.js`: نسخة API مخصصة لمسارات المدير.
- `index.js`: تجميع خدمات الإدارة.
- `dashboard.js`: إحصائيات لوحة التحكم.
- `users.js`: إدارة المستخدمين.
- `agents.js`: إدارة الوسطاء.
- `companies.js`: إدارة الشركات.
- `estates.js`: إدارة العقارات.
- `locations.js`: المدن والمناطق.
- `socialLinks.js`: روابط التواصل.
- `trust.js`: مراجعات الثقة والتوثيق.

### 5.4 الحالة Stores

- `src/stores/auth.js`: أهم store. يحفظ token والمستخدم، يسجل الدخول والخروج، يستدعي `/me` عند فتح التطبيق، ويحدد نوع المستخدم: admin/company/agent/buyer/owner.
- `src/stores/toast.js`: رسائل نجاح أو خطأ تظهر للمستخدم.
- `src/stores/confirm.js`: نافذة تأكيد عامة قبل الحذف أو العمليات الخطرة.
- `src/stores/counter.js`: store تجريبي غالباً من قالب Vue، يمكن اعتباره مثالاً على Pinia.

### 5.5 Layouts

الـ layout هو شكل الصفحة العام: navbar/sidebar/footer. الصفحة نفسها تعرض المحتوى فقط.

- `src/layouts/MainLayout.vue`: صفحات الزائر والصفحات العامة.
- `src/layouts/AuthLayout.vue`: صفحات تسجيل الدخول والتسجيل.
- `src/layouts/AdminLayout.vue`: لوحة المدير مع sidebar.
- `src/layouts/CompanyLayout.vue`: لوحة الشركة.
- `src/layouts/AgentLayout.vue`: لوحة الوسيط.
- `src/layouts/BuyerLayout.vue`: لوحة المشتري/المستثمر.
- `src/layouts/OwnerLayout.vue`: لوحة المالك.

### 5.6 Composables

الـ composable في Vue يشبه خدمة صغيرة للواجهة. يضع منطق الجلب والحالة والتحميل والأخطاء بعيداً عن ملف الصفحة.

- `usePaginatedList.js`: منطق القوائم مع pagination.
- `useDebouncedAsyncSearch.js`: بحث مؤجل حتى لا يرسل request مع كل حرف فوراً.
- `useFormErrors.js`: تنظيم أخطاء validation القادمة من Laravel.
- `useFormatters.js`: تنسيق أسعار، تواريخ، أرقام، ومساحات.
- `useTheme.js`: تفعيل أو إدارة الثيم.
- `useShareFeedback.js`: مشاركة أو نسخ روابط مع feedback للمستخدم.
- `useHomePage.js`: بيانات الصفحة الرئيسية.
- `useEstatesList.js`, `useEstateDetail.js`, `useEstateMapData.js`: قوائم العقارات، التفاصيل، وبيانات الخريطة.
- `useAgentsList.js`, `useAgentDetail.js`: قوائم وتفاصيل الوسطاء.
- `useCompanyDashboard.js`, `useCompanyEstatesList.js`: بيانات لوحة الشركة وعقاراتها.
- `useAdminDashboard.js`: إحصائيات لوحة المدير.
- `useAdminUsersList.js`, `useAdminUserDetail.js`: إدارة المستخدمين.
- `useAdminAgentsList.js`, `useAdminAgentDetail.js`: إدارة الوسطاء.
- `useAdminCompaniesList.js`, `useAdminCompanyDetail.js`: إدارة الشركات.
- `useAdminEstatesList.js`, `useAdminEstateDetail.js`: إدارة العقارات.
- `useAdminCitiesList.js`, `useAdminCityDetail.js`: إدارة المدن.
- `useAdminPlacesList.js`, `useAdminPlaceDetail.js`: إدارة المناطق.
- `useAdminTrustManagement.js`: مراجعة التقييمات وطلبات التوثيق.

### 5.7 المكونات Components

#### مكونات UI عامة

هذه ملفات صغيرة يعاد استخدامها في كل المشروع:

- `AppButton.vue`, `AppInput.vue`, `AppSelect.vue`, `AppTextarea.vue`, `AppCheckbox.vue`, `AppFileUpload.vue`, `AppAutocomplete.vue`, `AppSearchInput.vue`: عناصر form موحدة.
- `AppBadge.vue`, `TrustBadge.vue`, `StatusBadge.vue`: شارات حالة أو ثقة.
- `AppFormGroup.vue`, `FormAlert.vue`, `ErrorAlert.vue`: تنظيم النماذج والأخطاء.
- `LoadingSpinner.vue`, `EmptyState.vue`: حالات التحميل وعدم وجود بيانات.
- `Pagination.vue`: أزرار التنقل بين الصفحات.
- `PageHeader.vue`, `PageIntro.vue`, `SectionHeader.vue`: عناوين الصفحات والأقسام.
- `Breadcrumbs.vue`: مسار تنقل داخل الصفحة.
- `ConfirmDialog.vue`: نافذة تأكيد عامة.
- `ToastNotification.vue`: عرض التنبيهات.
- `StarRating.vue`, `StarRatingInput.vue`: عرض وإدخال التقييم بالنجوم.
- `TableAction.vue`, `TableActionGroup.vue`: أزرار عمليات الجداول.
- `DirectoryHero.vue`, `DirectoryToolbar.vue`, `CtaBanner.vue`: عناصر صفحات القوائم العامة.
- `TrustScorePanel.vue`: لوحة توضح درجة الثقة.

#### مكونات البطاقات

- `PropertyCard.vue`: بطاقة عقار.
- `AgentCard.vue`: بطاقة وسيط.
- `CompanyCard.vue`: بطاقة شركة.
- `CityCard.vue`: بطاقة مدينة.
- `RecommendationCard.vue`: بطاقة توصية.

#### مكونات العقار

- `EstateFilters.vue`: فلترة العقارات.
- `EstateGallery.vue`: صور العقار.
- `EstateSpecs.vue`: مواصفات العقار.
- `EstateSidebar.vue`: معلومات جانبية مثل السعر والتواصل.
- `EstateReviews.vue`: تقييمات العقار.
- `EstateInvestment.vue`: تحليل الاستثمار.
- `EstatePricePrediction.vue`: توقع السعر.
- `EstateLocationMap.vue`: موقع العقار على الخريطة.
- `FavoriteButton.vue`: إضافة/حذف من المفضلة.

#### مكونات الخرائط

- `LeafletMap.vue`: خريطة Leaflet للعرض.
- `MapLocationPicker.vue`: اختيار موقع عند إنشاء أو تعديل عقار.

#### مكونات الإدارة

- `AdminSidebar.vue`: قائمة لوحة المدير.
- `AdminPageHeader.vue`: عنوان صفحات الإدارة.
- `AdminDataTable.vue`: جدول إداري عام.
- `AdminStatCard.vue`, `AdminStatsSection.vue`, `AdminTrendChart.vue`, `AdminBarChart.vue`, `AdminRecentList.vue`: إحصائيات ورسوم ولوحات مختصرة.
- `AdminUserForm.vue`, `AdminAgentForm.vue`, `AdminCompanyForm.vue`, `AdminEstateForm.vue`, `AdminCityForm.vue`, `AdminPlaceForm.vue`: نماذج إنشاء وتعديل.
- `AdminEstateMediaPanel.vue`: إدارة صور وفيديوهات العقار.
- `AdminAgentSocialPanel.vue`, `AdminCompanySocialPanel.vue`: روابط التواصل.
- `AdminModerationDialog.vue`: نافذة قبول/رفض المراجعات أو التوثيق.

#### مكونات حسب نوع المستخدم

- `BuyerSidebar.vue`: قائمة المشتري.
- `OwnerSidebar.vue`: قائمة المالك.
- `AgentSidebar.vue` داخل `components/agent`: قائمة لوحة الوسيط.
- `AgentSidebar.vue` داخل `components/agents`: عنصر خاص بعرض تفاصيل وسيط عام.
- `CompanySidebar.vue`: قائمة الشركة.
- `AgentForm.vue`: نموذج وسيط داخل لوحة الشركة.
- `EstateForm.vue`: نموذج عقار داخل لوحة الشركة.
- `SocialLinksManager.vue`: إدارة روابط التواصل.
- `AgentProfileHero.vue`: واجهة عليا لملف الوسيط.
- `ChatWindow.vue`: نافذة المحادثة.
- `ReviewForm.vue`, `ReviewSection.vue`: إضافة وعرض المراجعات.
- `HeroSearch.vue`: بحث الصفحة الرئيسية.
- `AppNavbar.vue`, `AppFooter.vue`: شريط علوي وتذييل عام.

### 5.8 الصفحات Views

كل ملف داخل `src/views` يمثل شاشة كاملة مرتبطة بمسار في router.

#### صفحات عامة

- `HomePage.vue`: الصفحة الرئيسية.
- `auth/LoginPage.vue`, `auth/RegisterPage.vue`: الدخول والتسجيل.
- `estates/EstatesListPage.vue`, `estates/EstateDetailPage.vue`, `estates/EstatesMapPage.vue`: قائمة العقارات، التفاصيل، والخريطة.
- `cities/CitiesListPage.vue`, `cities/CityDetailPage.vue`: المدن.
- `places/PlacesListPage.vue`, `places/PlaceDetailPage.vue`: المناطق.
- `agents/AgentsListPage.vue`, `agents/AgentDetailPage.vue`: الوسطاء.
- `companies/CompaniesListPage.vue`, `companies/CompanyDetailPage.vue`: الشركات.
- `favorites/FavoritesPage.vue`: مفضلة عامة للمستخدم.
- `recommendations/RecommendationsPage.vue`: توصيات عامة.
- `chat/InboxPage.vue`, `chat/ChatPage.vue`: صندوق الرسائل والمحادثة.
- `profile/ProfilePage.vue`: الملف الشخصي.

#### صفحات المشتري / المستثمر

- `buyer/DashboardPage.vue`: لوحة المستثمر.
- `buyer/FavoritesPage.vue`: المفضلة داخل لوحة المستثمر.
- `buyer/RecommendationsPage.vue`: التوصيات.
- `buyer/ProfilePage.vue`: الملف الشخصي.
- `buyer/SocialLinksPage.vue`: روابط التواصل.
- `buyer/PortfoliosPage.vue`: المحافظ.
- `buyer/CreatePortfolioPage.vue`: إنشاء محفظة.
- `buyer/PortfolioDetailPage.vue`: تفاصيل محفظة.
- `buyer/InvestmentAnalysesPage.vue`: التحليلات.
- `buyer/AnalysisDetailPage.vue`: تفاصيل تحليل.
- `buyer/EstateAnalysisPage.vue`: تحليل عقار محدد.

#### صفحات المالك

- `owner/DashboardPage.vue`: لوحة المالك.
- `owner/EstatesPage.vue`: عقارات المالك.
- `owner/CreateEstatePage.vue`: إضافة عقار.
- `owner/EditEstatePage.vue`: تعديل عقار.
- `owner/InvestmentAnalyticsPage.vue`: تحليلات الاستثمار لعقارات المالك.

#### صفحات الوسيط

- `agent/DashboardPage.vue`: لوحة الوسيط.
- `agent/EstatesPage.vue`: عقارات الوسيط.
- `agent/CreateEstatePage.vue`: إضافة عقار.
- `agent/EditEstatePage.vue`: تعديل عقار.
- `agent/ProfilePage.vue`: ملف الوسيط.
- `agent/SocialLinksPage.vue`: روابط التواصل.
- `agent/InvestmentsPage.vue`: التحليلات.

#### صفحات الشركة

- `company/DashboardPage.vue`: لوحة الشركة.
- `company/AgentsPage.vue`: وسطاء الشركة.
- `company/CreateAgentPage.vue`, `company/EditAgentPage.vue`: إنشاء وتعديل وسيط.
- `company/EstatesPage.vue`: عقارات الشركة.
- `company/CreateEstatePage.vue`, `company/EditEstatePage.vue`: إنشاء وتعديل عقار.
- `company/ReviewsPage.vue`: مراجعات الشركة.
- `company/ProfilePage.vue`: ملف الشركة.
- `company/UserProfilePage.vue`: ملف مستخدم الشركة.
- `company/SocialLinksPage.vue`: روابط التواصل.
- `company/InvestmentsPage.vue`: التحليلات.

#### صفحات المدير

- `admin/DashboardPage.vue`: إحصائيات النظام.
- `admin/ProfilePage.vue`: ملف المدير.
- `admin/users/UsersListPage.vue`, `admin/users/UserDetailPage.vue`: إدارة المستخدمين.
- `admin/agents/AgentsListPage.vue`, `admin/agents/AgentDetailPage.vue`: إدارة الوسطاء.
- `admin/companies/CompaniesListPage.vue`, `admin/companies/CompanyDetailPage.vue`: إدارة الشركات.
- `admin/estates/EstatesListPage.vue`, `admin/estates/EstateDetailPage.vue`: إدارة العقارات.
- `admin/cities/CitiesListPage.vue`, `admin/cities/CityDetailPage.vue`: إدارة المدن.
- `admin/places/PlacesListPage.vue`, `admin/places/PlaceDetailPage.vue`: إدارة المناطق.
- `admin/trust/TrustModerationPage.vue`: مراجعة التقييمات وطلبات التوثيق.

### 5.9 CSS والستايل

- `src/styles/index.css`: نقطة تجميع ملفات CSS.
- `src/styles/fonts.css`: الخطوط.
- `src/styles/colors.css`: ألوان المشروع.
- `src/styles/components/*.css`: ستايل المكونات العامة مثل buttons, cards, forms, navbar, footer, badges, table-actions, ui.
- `src/styles/pages/admin.css`: صفحات الإدارة.
- `src/styles/pages/agents.css`: صفحات الوسطاء.
- `src/styles/pages/auth.css`: الدخول والتسجيل.
- `src/styles/pages/directories.css`: صفحات القوائم مثل المدن والشركات.
- `src/styles/pages/estates.css`: صفحات العقارات.
- `src/styles/pages/homepage.css`: الصفحة الرئيسية.

### 5.10 Utils

- `utils/authRedirect.js`: يحدد أين يذهب المستخدم بعد تسجيل الدخول حسب نوعه.
- `utils/agent.js`: دوال تنسيق أو تطبيع بيانات الوسيط.
- `utils/company.js`: دوال خاصة بالشركة.
- `utils/estate.js`: دوال خاصة بالعقار.
- `utils/location.js`: دوال المدن والمناطق.
- `utils/map.js`: دوال إحداثيات وخرائط.
- `utils/share.js`: مشاركة الروابط أو النصوص.
- `utils/user.js`: دوال نوع المستخدم وبياناته.

## 6. شرح الباكند Laravel

### 6.1 ملفات الدخول والإعداد

- `artisan`: أداة Laravel من الطرفية.
- `composer.json`: مكتبات PHP وأوامر Laravel.
- `package.json`: مكتبات Vite/Tailwind الخاصة بواجهة Laravel الافتراضية إن وجدت.
- `vite.config.js`: إعداد Vite داخل مشروع Laravel.
- `phpunit.xml`: إعداد الاختبارات.
- `README.md`: وصف المشروع أو تعليمات تشغيل.
- `schema.dbml`, `database.dbml`, `ERD2.dbml`: تمثيل قاعدة البيانات كـ DBML.

### 6.2 Routes

المجلد: `routes`

- `api.php`: نقطة دخول API، يستدعي `api/v1.php` و `api/admin.php`.
- `web.php`: صفحات ويب عادية من Laravel، غالباً `welcome`.
- `console.php`: أوامر Console.
- `API.md`: توثيق endpoints.
- `api/v1.php`: يجمع مسارات API العامة ومسارات المستخدمين.
- `api/v1/public.php`: المسارات المتاحة بدون تسجيل دخول مثل عرض العقارات والمدن والشركات.
- `api/v1/auth.php`: login/register/logout/me.
- `api/v1/authenticated/*.php`: مسارات تحتاج token مثل profile, favorites, messages, recommendations, portfolios, trust.
- `api/admin.php`: نقطة دخول مسارات المدير.
- `api/admin/auth.php`: مصادقة المدير.
- `api/admin/authenticated/*.php`: إدارة المستخدمين والعقارات والشركات والوسطاء والمواقع والثقة.
- `routes/_extracted`: نسخة مستخرجة/أرشيفية من المسارات ووثائقها، مفيدة للمراجعة وليست غالباً المسار الفعلي الذي يشغله Laravel.

### 6.3 Controllers

الـ Controller يستقبل الطلب من route، يقرأ Request، يستدعي Model أو Service، ثم يعيد response.

- `BaseApiController.php`: يوحد شكل JSON: `success`, `message`, `data`, و `pagination`.
- `AuthController.php`: تسجيل، دخول، خروج، وجلب المستخدم الحالي.
- `UserController.php`: بيانات المستخدم.
- `AgentController.php`: الوسطاء.
- `CompanyController.php`: الشركات.
- `CityController.php`, `PlaceController.php`: المدن والمناطق.
- `EstateController.php`: العقارات.
- `EstateImageController.php`, `EstateVideoController.php`, `EstateAdController.php`: وسائط وإعلانات العقار.
- `EstateGeoController.php`: البحث الجغرافي والخريطة.
- `FavoriteEstateController.php`, `FavoriteAgentController.php`: المفضلة.
- `InvestmentAnalysisController.php`: تحليلات الاستثمار.
- `PortfolioController.php`: محافظ استثمارية عامة/قديمة.
- `Investor/InvestmentPortfolioController.php`, `Investor/MyPortfolioController.php`, `Investor/MyPortfolioItemController.php`, `Investor/InvestorDashboardController.php`: محافظ ولوحة المستثمر.
- `MarketAnalyticsController.php`: تحليلات السوق.
- `MessageController.php`: الرسائل.
- `NotificationController.php`: الإشعارات.
- `Owner/OwnerDashboardController.php`: لوحة المالك.
- `PricePredictionController.php`: توقع السعر.
- `PropertyInteractionController.php`: تفاعلات المستخدم مع العقار.
- `RecommendationController.php`: التوصيات.
- `SocialLinkController.php`, `CompanySocialLinkController.php`: روابط التواصل.
- `UserPreferenceController.php`: تفضيلات المستخدم.
- `Trust/*ReviewController.php`: مراجعات العقار والوسيط والشركة.
- `Trust/TrustScoreController.php`: درجة الثقة.
- `Trust/VerificationRequestController.php`: طلبات التوثيق.

Controllers الإدارة داخل `app/Http/Controllers/Api/Admin`:

- `AuthController.php`: مصادقة المدير.
- `DashboardController.php`: إحصائيات لوحة المدير.
- `UserController.php`: CRUD المستخدمين.
- `AgentController.php`: CRUD الوسطاء.
- `CompanyController.php`: CRUD الشركات.
- `EstateController.php`: CRUD العقارات والموافقة/الرفض.
- `CityController.php`, `PlaceController.php`: إدارة المواقع.
- `MessageController.php`, `NotificationController.php`: مراقبة الرسائل والإشعارات.
- `EstateImageController.php`, `EstateVideoController.php`, `EstateAdController.php`: وسائط العقارات.
- `CompanySocialLinkController.php`, `SocialLinkController.php`: روابط التواصل.
- `TrustModerationController.php`: قبول/رفض المراجعات وطلبات التوثيق.

### 6.4 Controller Concerns

المجلد: `app/Http/Controllers/Concerns`

هذه traits صغيرة تقلل تكرار الكود داخل controllers:

- `ManagesAgentProfiles.php`: منطق إدارة ملفات الوسطاء.
- `ManagesCityImages.php`: صور المدن.
- `ManagesEstateAds.php`: إعلانات العقار.
- `ManagesEstateImages.php`: صور العقار.
- `ManagesEstateVideos.php`: فيديوهات العقار.
- `MapsPortfolioDomainExceptions.php`: تحويل أخطاء المحافظ إلى responses.
- `PersistsEstateFields.php`: حفظ حقول العقار المشتركة.
- `QueriesAdminUsers.php`, `QueriesAgents.php`, `QueriesCities.php`, `QueriesPlaces.php`: queries مشتركة للفلاتر والبحث.
- `ResolvesOwnedCompany.php`: معرفة الشركة التي يملكها أو يمثلها المستخدم.

### 6.5 Requests

المجلد: `app/Http/Requests`

Request في Laravel مسؤول عن validation. بدلاً من كتابة القواعد داخل Controller، يتم فصلها هنا.

- `Auth/LoginRequest.php`, `Auth/RegisterUserRequest.php`, `Auth/UpdateProfileRequest.php`: دخول، تسجيل، تعديل الملف.
- `Admin/*Request.php`: قواعد إنشاء وتعديل وإدارة موارد المدير مثل المستخدمين والعقارات والشركات والمدن والمناطق.
- `Concerns/EstateValidationRules.php`: قواعد مشتركة للعقار.
- `Concerns/WorkDaysValidationRules.php`: قواعد أيام العمل.
- `Geo/*Request.php`: قواعد البحث الجغرافي.
- `Portfolio/*Request.php`: قواعد المحافظ وعناصرها.
- `Trust/*ReviewRequest.php`, `Trust/StoreVerificationRequestRequest.php`: التقييمات والتوثيق.
- `StoreEstateRequest.php`, `UpdateEstateRequest.php`: إنشاء وتعديل عقار.
- `StoreAgentRequest.php`, `UpdateAgentRequest.php`, `UpdateAgentRateRequest.php`: الوسطاء.
- `StoreMyCompanyRequest.php`, `UpdateMyCompanyRequest.php`: بيانات الشركة الخاصة.
- `StoreMessageRequest.php`: إرسال رسالة.
- `StoreInvestmentAnalysisRequest.php`, `UpdateInvestmentAnalysisRequest.php`: التحليلات.
- `StoreFavoriteEstateRequest.php`, `StoreFavoriteAgentRequest.php`: المفضلة.
- `StoreSocialLinkRequest.php`, `UpdateSocialLinkRequest.php`, `SyncSocialLinksRequest.php`: روابط التواصل.
- `StorePricePredictionPreviewRequest.php`: توقع سعر بدون حفظ.
- `StorePropertyInteractionRequest.php`: تسجيل التفاعل.
- `StoreUserPreferenceRequest.php`: حفظ تفضيلات المستخدم.
- `StoreEstateImageRequest.php`, `StoreEstateVideoRequest.php`, `StoreEstateAdRequest.php`: وسائط العقار.

### 6.6 Models

المجلد: `app/Models`

Model يمثل جدولاً في قاعدة البيانات وعلاقاته.

- `User.php`: المستخدمون وأنواعهم.
- `Agent.php`: الوسيط.
- `Companies.php`: الشركة.
- `Cities.php`: المدينة.
- `Places.php`: المنطقة.
- `Estate.php`: العقار، وهو مركز النظام.
- `EstateImage.php`, `EstateVideo.php`, `EstateAd.php`: وسائط وإعلانات العقار.
- `Favorit_estate.php`, `Favorit_agent.php`: مفضلة العقارات والوسطاء.
- `InvestmentAnalysis.php`: تحليل استثماري.
- `InvestmentPortfolio.php`, `Portfolio.php`, `PortfolioItem.php`, `PortfolioProperty.php`: المحافظ الاستثمارية وعناصرها.
- `Message.php`: الرسائل.
- `Notifications.php`: الإشعارات.
- `PricePrediction.php`: نتيجة توقع السعر.
- `PropertyInteraction.php`: تفاعل المستخدم مع عقار.
- `Recommendation.php`: توصية.
- `UserPreference.php`: تفضيلات المستخدم.
- `SocialLink.php`: روابط التواصل.
- `PropertyReview.php`, `AgentReview.php`, `CompanyReview.php`: التقييمات.
- `VerificationRequest.php`: طلب التوثيق.
- `PasswordResetToken.php`: رموز إعادة كلمة المرور.
- `FailedJob.php`: jobs فاشلة.

Model Concerns:

- `HasSocialLinks.php`: علاقة روابط التواصل.
- `HasModerationStatus.php`: حالة المراجعة/القبول.
- `HasReviewModerationAudit.php`: معلومات من راجع ومتى.

### 6.7 Services

Service يحتوي منطق الأعمال الذي لا يفضل وضعه داخل Controller أو Model.

- `Admin/StatisticsService.php`: حساب إحصائيات لوحة المدير.
- `Ai/PricePredictionClient.php`: الاتصال بخدمة Flask.
- `Ai/EstatePricePredictionService.php`: منطق توقع سعر عقار.
- `Ai/MarketTrendsService.php`: اتجاهات السوق من قاعدة البيانات.
- `EstateImageService.php`: معالجة صور العقار.
- `FileUploadService.php`: رفع الملفات.
- `GeoSearchService.php`: بحث جغرافي حسب الإحداثيات والمسافة.
- `Investment/InvestmentCalculator.php`: حسابات الاستثمار الأساسية.
- `Investment/InvestmentCalculatorService.php`: طبقة خدمة حول حسابات الاستثمار.
- `Investment/InvestmentMetrics.php`: قيم ومقاييس الاستثمار.
- `Investment/InvestorDashboardService.php`: بيانات لوحة المستثمر.
- `NotificationService.php`: إنشاء وإدارة الإشعارات.
- `Owner/OwnerDashboardService.php`: بيانات لوحة المالك.
- `Portfolio/PortfolioService.php`: منطق المحافظ.
- `PropertyInteractionService.php`: تسجيل وتحليل تفاعلات المستخدم.
- `RecommendationService.php`: إدارة التوصيات.
- `RecommendationGeneratorService.php`: توليد التوصيات.
- `RecommendationScoringService.php`: حساب score لكل عقار.
- `SocialLinkService.php`: منطق روابط التواصل.
- `Trust/ReviewService.php`: منطق التقييمات.
- `Trust/TrustScoreService.php`: حساب درجة الثقة.
- `Trust/VerificationRequestService.php`: طلبات التوثيق.

### 6.8 Resources و Traits

Resources تحول Model إلى JSON نظيف للواجهة.

- `Http/Resources/Portfolio/*`: شكل المحافظ وعناصرها وملخصات اللوحة.
- `Http/Resources/Trust/*`: شكل التقييمات والثقة والتوثيق.
- `Http/Resources/Trust/Concerns/FormatsReviewModerationAudit.php`: تنسيق بيانات مراجعة المدير.

Traits في `app/Traits`:

- `FormatsAdminUserResponse.php`: تنسيق مستخدم الإدارة.
- `FormatsAgentResponse.php`: تنسيق الوسيط.
- `FormatsCityResponse.php`: تنسيق المدينة.
- `FormatsCompanyResponse.php`: تنسيق الشركة.
- `FormatsEstateResponse.php`: تنسيق العقار.
- `FormatsInvestmentAnalysisResponse.php`: تنسيق التحليل.
- `FormatsPlaceResponse.php`: تنسيق المنطقة.
- `FormatsPropertyInteractionResponse.php`: تنسيق التفاعل.
- `FormatsRecommendationResponse.php`: تنسيق التوصية.
- `FormatsUserPreferenceResponse.php`: تنسيق التفضيلات.

### 6.9 Policies و Middleware و Enums و Exceptions

Policies:

- `PortfolioPolicy.php`, `PortfolioItemPolicy.php`: صلاحيات المحافظ.
- `PropertyReviewPolicy.php`, `AgentReviewPolicy.php`, `CompanyReviewPolicy.php`: صلاحيات التقييم.
- `VerificationRequestPolicy.php`: صلاحيات التوثيق.

Middleware:

- `EnsureUserIsAdmin.php`: يتأكد أن المستخدم مدير.
- `UpdateLastActivity.php`: يحدث آخر نشاط للمستخدم.

Enums:

- `InteractionType.php`: أنواع التفاعل.
- `InvestmentGoal.php`: هدف الاستثمار.
- `InvestmentType.php`: نوع الاستثمار.
- `PropertyFunction.php`: وظيفة العقار.

Exceptions المحافظ:

- `PortfolioDomainException.php`: الخطأ الأساسي.
- `DuplicatePortfolioItemException.php`: العنصر مكرر.
- `EstateNotPublishedException.php`: العقار غير منشور.
- `InvalidPortfolioStatusTransitionException.php`: انتقال حالة غير مسموح.
- `PortfolioItemNotFoundException.php`: عنصر غير موجود.
- `UnauthorizedPortfolioAccessException.php`: وصول غير مصرح.

### 6.10 قاعدة البيانات

Migrations:

- `0001_01_01_000000_create_users_table.php`: جدول المستخدمين.
- `0001_01_01_000001_create_cache_table.php`: cache.
- `0001_01_01_000002_create_jobs_table.php`: jobs والصفوف.
- `2019_12_14_000001_create_personal_access_tokens_table.php`: tokens الخاصة بـ Sanctum.
- `2026_05_09_212502_create_messages_table.php`: الرسائل.
- `2026_05_10_149000_create_cities_table.php`: المدن.
- `2026_05_10_151000_create_places_table.php`: المناطق.
- `2026_05_10_152000_create_estates_table.php`: العقارات.
- `2026_05_10_153000_create_favorit_estates_table.php`: مفضلة العقارات.
- `2026_05_10_205027_create_estate_ads_table.php`: إعلانات العقارات.
- `2026_05_11_095822_create_estates_videos_table.php`: فيديوهات العقارات.
- `2026_05_11_102523_create_estates_images_table.php`: صور العقارات.
- `2026_05_11_105530_create_companies_table.php`: الشركات.
- `2026_05_11_115833_create_agents_table.php`: الوسطاء.
- `2026_05_11_120000_create_favorit_agents_table.php`: مفضلة الوسطاء.
- `2026_05_12_105148_create_password_rest_tokens_table.php`: رموز استعادة كلمة المرور.
- `2026_05_12_180000_create_investment_analyses_table.php`: التحليلات.
- `2026_05_21_140000_create_user_preferences_table.php`: تفضيلات المستخدم.
- `2026_05_21_150000_create_property_interactions_table.php`: التفاعلات.
- `2026_05_21_160000_create_recommendations_table.php`: التوصيات.
- `2026_05_22_100000_create_price_predictions_table.php`: توقعات الأسعار.
- `2026_05_24_100000_create_investment_portfolios_table.php`: المحافظ.
- `2026_05_28_120000_create_social_links_table.php`: روابط التواصل.
- `2026_06_01_100000_create_property_reviews_table.php`: تقييمات العقارات.
- `2026_06_01_100001_create_agent_reviews_table.php`: تقييمات الوسطاء.
- `2026_06_01_100002_create_company_reviews_table.php`: تقييمات الشركات.
- `2026_06_01_100003_create_verification_requests_table.php`: طلبات التوثيق.
- `2026_06_02_100000_create_notifications_table.php`: الإشعارات.
- `2026_06_08_120000_add_moderation_audit_to_reviews_tables.php`: بيانات مراجعة المدير للتقييمات.
- `2026_06_20_000000_add_agent_approval_fields.php`: حقول الموافقة على الوسيط.
- `2026_06_20_024532_add_last_activity_at_to_users_table.php`: آخر نشاط للمستخدم.

Factories:

- `UserFactory.php`, `AgentFactory.php`, `CompanyFactory.php`, `CityFactory.php`, `PlaceFactory.php`, `EstateFactory.php`: إنشاء بيانات وهمية للكيانات الأساسية.
- `AgentReviewFactory.php`, `CompanyReviewFactory.php`, `PropertyReviewFactory.php`, `VerificationRequestFactory.php`: بيانات وهمية للثقة والمراجعات.
- `NotificationsFactory.php`, `SocialLinkFactory.php`, `UserPreferenceFactory.php`: بيانات وهمية للمساندات.
- `PortfolioFactory.php`, `PortfolioItemFactory.php`: بيانات المحافظ.

Seeders:

- `DatabaseSeeder.php`: نقطة تشغيل seeders.
- `AdminSeeder.php`: مدير تجريبي.
- `DemoUserSeeder.php`: مستخدمون تجريبيون.
- `LocationSeeder.php`: مدن ومناطق.
- `CompanySeeder.php`: شركات.
- `AgentSeeder.php`: وسطاء.
- `EstateSeeder.php`: عقارات.
- `PortfolioSeeder.php`: محافظ.
- `ReviewSeeder.php`: مراجعات.
- `Concerns/SeedsDemoPassword.php`: كلمة مرور موحدة للبيانات التجريبية.

### 6.11 config

- `config/app.php`: اسم التطبيق، اللغة، timezone، ومزودي الخدمات.
- `config/auth.php`: guards و providers للمصادقة.
- `config/sanctum.php`: إعدادات tokens.
- `config/database.php`: اتصال قاعدة البيانات.
- `config/cache.php`: cache.
- `config/filesystems.php`: التخزين والملفات العامة.
- `config/logging.php`: logs.
- `config/mail.php`: البريد.
- `config/ml.php`: إعداد خدمة الذكاء الاصطناعي.
- `config/queue.php`: queues.
- `config/realestate.php`: إعدادات خاصة بالمجال العقاري.
- `config/services.php`: خدمات خارجية.
- `config/session.php`: الجلسات.

### 6.12 resources و public و storage

- `resources/views/welcome.blade.php`: صفحة Blade الافتراضية.
- `resources/js/app.js`, `resources/js/bootstrap.js`: JavaScript الخاص بواجهة Laravel الافتراضية إن استخدمت.
- `resources/css/app.css`: CSS خاص بواجهة Laravel.
- `public/index.php`: نقطة دخول HTTP في Laravel.
- `public/favicon.ico`, `public/robots.txt`: ملفات عامة للمتصفح ومحركات البحث.
- `storage/app`, `storage/framework`, `storage/logs`: ملفات مرفوعة، cache، sessions، logs. ملفات `.gitignore` داخلها تحافظ على المجلدات بدون رفع المحتوى المتغير.

### 6.13 ML Pricing

- `ml/pricing/server.py`: خادم Flask يستقبل بيانات العقار ويرجع توقع السعر.
- `ml/pricing/requirements.txt`: مكتبات Python المطلوبة.
- `ml/pricing/real_estate_model.pkl`: نموذج التعلم الآلي المدرب.
- `ml/pricing/label_encoder.pkl`: محول التصنيفات.
- `ml/pricing/README.md`: شرح خدمة ML.

### 6.14 scripts

- `extract-routes-from-transcript.php`: استخراج routes من نص أو transcript.
- `fix-api-refactor.php`: إصلاحات بعد refactor للـ API.
- `fix-paginator-data.php`, `fix-paginator-items-only.php`: سكربتات تصحيح pagination.
- `refactor-api-responses.php`: توحيد responses.

### 6.15 tests

الاختبارات تقسم إلى Feature و Unit.

- `tests/TestCase.php`: أساس الاختبارات.
- `tests/Feature/Admin/*`: اختبار إدارة المدير للمستخدمين والعقارات والشركات والوسطاء والمواقع والثقة.
- `tests/Feature/Agents/*`: الوسطاء ومراجعاتهم.
- `tests/Feature/Companies/*`: الشركات وحالاتها.
- `tests/Feature/Favorites/*`: سلامة المفضلة.
- `tests/Feature/Geo/EstateGeoTest.php`: البحث الجغرافي.
- `tests/Feature/Investment/*`: التحليلات والمحافظ ولوحة المستثمر.
- `tests/Feature/Notifications/NotificationTest.php`: الإشعارات.
- `tests/Feature/Recommendations/*`: التفضيلات والتوصيات.
- `tests/Feature/SocialLinks/*`: روابط التواصل لكل نوع كيان.
- `tests/Feature/Trust/*`: المراجعات والتوثيق وإدارة الثقة.
- `tests/Unit/Services/*`: اختبار الخدمات الحسابية والمنطقية مثل الاستثمار والتوصيات والثقة والخرائط.
- `tests/Concerns/*`: helpers مشتركة لتسهيل استدعاء API في الاختبارات.

## 7. ملفات الجذر والمجلدات المولدة

### 7.1 ملفات جذر الواجهة `project-RealEstate`

- `.editorconfig`: يوحد إعدادات المحرر مثل المسافات ونهاية السطر.
- `.env.example`: مثال متغيرات البيئة، مثل `VITE_API_BASE_URL`.
- `.gitattributes`: إعدادات Git للملفات.
- `.gitignore`: ملفات لا ترفع إلى Git مثل `node_modules` و `dist`.
- `.oxlintrc.json`: إعدادات Oxlint.
- `.prettierrc.json`: إعدادات Prettier.
- `eslint.config.js`: قواعد ESLint.
- `index.html`: صفحة HTML التي يحقن فيها Vite تطبيق Vue داخل `#app`.
- `jsconfig.json`: aliases مثل `@/` حتى تشير إلى `src`.
- `package.json`: أوامر ومكتبات المشروع.
- `package-lock.json`: نسخة مقفلة من شجرة npm dependencies.
- `README.md`: تعليمات أو وصف الواجهة.
- `vite.config.js`: إعداد Vite و Vue Devtools و alias.
- `.vscode`: إعدادات خاصة بـ VS Code.
- `public`: ملفات ثابتة تنسخ كما هي أثناء build.
- `dist`: ناتج `npm run build`. هذا مجلد مولد وليس كوداً تعدله عادة.
- `node_modules`: مكتبات npm. هذا مجلد مولد بعد `npm install` ولا يشرح ملفاً ملفاً لأنه ليس من كود المشروع.

### 7.2 ملفات جذر الباكند `project-RealEstate_database`

- `.editorconfig`: توحيد تنسيق الملفات.
- `.env.example`: مثال إعدادات البيئة: قاعدة البيانات، app key، البريد، Sanctum، ML.
- `.gitattributes`: إعدادات Git.
- `.gitignore`: استبعاد ملفات متغيرة مثل `.env`, `vendor`, logs.
- `artisan`: CLI Laravel.
- `composer.json`: مكتبات PHP وأوامر Composer.
- `composer.lock`: النسخ الدقيقة للمكتبات المثبتة.
- `package.json`: أدوات frontend داخل Laravel مثل Vite و Tailwind.
- `phpunit.xml`: إعداد بيئة الاختبار.
- `vite.config.js`: إعداد Vite الخاص بموارد Laravel.
- `README.md`: وصف وتعليمات.
- `database.dbml`, `schema.dbml`, `ERD2.dbml`: نماذج قاعدة البيانات.
- `bootstrap`: ملفات إقلاع Laravel و cache. غالباً لا تعدلها إلا عند إعداد framework.
- `vendor`: مكتبات Composer. مجلد مولد بعد `composer install` ولا يعد من كود التطبيق.
- `_transcript_extract`: مجلد مساعد لاستخراج أو تحليل نصوص/مسارات، وليس طبقة runtime أساسية.
- `docs`: توثيق API وشرح عربي وملفات diagrams.
- `activity-diagrams`, `sequence-diagrams`, `use-case-diagrams`: مخططات تحليلية تساعد في التقرير والفهم.

### 7.3 لماذا لا نشرح `node_modules` و `vendor` ملفاً ملفاً؟

هذه المجلدات تحتوي آلاف ملفات مكتبات خارجية. عملياً لا يقرأها الطالب لتعلم المشروع، ولا يغيرها المطور. المهم أن يعرف:

- `node_modules` يأتي من `npm install`.
- `vendor` يأتي من `composer install`.
- `dist` يأتي من `npm run build`.
- أي تعديل حقيقي يجب أن يكون في `src` للواجهة أو `app/routes/database/config/tests` للباكند.

## 7. كيف تقرأ الكود كمبتدئ

أفضل مسار تعلم داخل هذا المشروع:

1. ابدأ من `src/router/index.js` لتعرف الصفحات.
2. افتح صفحة بسيطة مثل `src/views/estates/EstatesListPage.vue`.
3. ابحث عن composable أو API service الذي تستخدمه الصفحة.
4. افتح ملف API المقابل مثل `src/api/estates.js`.
5. افتح route في Laravel مثل `routes/api/v1/public.php`.
6. افتح Controller المقابل.
7. افتح Request إن وجد لتفهم validation.
8. افتح Model لتفهم العلاقات.
9. افتح migration لتفهم شكل الجدول.
10. افتح test مشابه لتفهم السلوك المتوقع.

مثال عملي: عرض تفاصيل عقار

- Frontend route: `src/router/index.js` فيه `/estates/:id`.
- الصفحة: `src/views/estates/EstateDetailPage.vue`.
- مكونات العرض: `EstateGallery.vue`, `EstateSpecs.vue`, `EstateLocationMap.vue`, `EstateReviews.vue`.
- API: `src/api/estates.js`.
- Backend route: غالباً داخل `routes/api/v1/public.php`.
- Controller: `app/Http/Controllers/Api/EstateController.php`.
- Model: `app/Models/Estate.php`.
- Database: `database/migrations/2026_05_10_152000_create_estates_table.php`.

## 8. قاعدة ذهبية لفهم الطبقات

- View يعرض.
- Component يعاد استخدامه.
- Composable ينظم منطق الواجهة.
- API file يرسل الطلب.
- Route يحدد العنوان.
- Request يتحقق من البيانات.
- Controller ينسق العملية.
- Service ينفذ منطق الأعمال.
- Model يمثل الجدول والعلاقات.
- Migration يبني الجدول.
- Resource/Trait ينسق JSON.
- Test يثبت أن السلوك يعمل.

إذا ضعت في المشروع، اسأل نفسك: هل أبحث عن شاشة، طلب API، قاعدة validation، منطق حساب، أم جدول قاعدة بيانات؟ الجواب سيحدد المجلد الصحيح.
