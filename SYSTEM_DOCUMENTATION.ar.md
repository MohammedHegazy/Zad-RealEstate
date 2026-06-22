# التوثيق الشامل لمنصة زاد العقارية

## 1. نظرة عامة

منصة زاد العقارية نظام Full Stack لإدارة وتجربة سوق عقاري رقمي. يجمع النظام بين عرض العقارات، إدارة الملاك والشركات والوسطاء، أدوات استثمارية، محفظة عقارية، خرائط، دردشة، تقييمات، توثيق حسابات، توصيات ذكية، وتوقع سعر العقار عبر نموذج تعلم آلي منفصل.

النظام مقسوم إلى ثلاث طبقات:

| الطبقة | التقنية | المسار |
| --- | --- | --- |
| الواجهة | Vue 3 + Vite + Pinia + Vue Router | `project-RealEstate/` |
| الخلفية | Laravel 12 + Sanctum + Eloquent | `project-RealEstate_database/` |
| خدمة الذكاء الاصطناعي | Flask + scikit-learn + joblib | `project-RealEstate_database/ml/pricing/` |

تعمل الواجهة كتطبيق SPA عربي RTL، وتتواصل مع الخلفية عبر JSON REST API. الخلفية تحفظ البيانات وتطبق الصلاحيات والمنطق التجاري. خدمة Flask مسؤولة عن توقع أسعار العقارات فقط، وLaravel يتعامل معها كخدمة خارجية عبر HTTP.

## 2. الهدف الوظيفي

المنصة تخدم عدة أنواع من المستخدمين:

- الزائر: يتصفح العقارات والمدن والمناطق والوسطاء والشركات.
- المشتري أو المستثمر: يحفظ المفضلة، يستقبل توصيات، ينشئ محافظ استثمارية، يحلل فرص العقارات.
- المالك: ينشر عقاراته ويراقب أدائها وتحليلاتها.
- الشركة: تدير ملف الشركة، الوسطاء، العقارات، روابط التواصل، وتقييمات الشركة.
- الوسيط: يدير ملفه وعقاراته وروابطه وتحليلاته.
- المدير: يدير المستخدمين والعقارات والشركات والوسطاء والمدن والمناطق والثقة والتقييمات.

## 3. بنية المستودع

```text
Real-estat-ziad/
├── README.ar.md
├── REFERENCE.md
├── SYSTEM_DOCUMENTATION.ar.md
├── assets/
├── Documentation Chapters/
├── project-RealEstate/
└── project-RealEstate_database/
```

### `assets/`

يحتوي على مواد داعمة للتوثيق مثل:

- ERD.
- Use Case Diagrams.
- Sequence Diagrams.
- Mind Maps.
- صور ومرفقات وثائقية.

### `project-RealEstate/`

واجهة المستخدم العربية. أهم مجلداتها:

| المسار | المعنى |
| --- | --- |
| `src/api/` | خدمات الاتصال مع API |
| `src/components/` | مكونات عامة ومكونات حسب المجال |
| `src/composables/` | منطق صفحات قابل لإعادة الاستخدام |
| `src/config/` | ثوابت وقوائم وخيارات |
| `src/layouts/` | تخطيطات الأدوار |
| `src/router/` | المسارات والحراس |
| `src/stores/` | Pinia stores |
| `src/styles/` | الألوان والأنماط |
| `src/views/` | الصفحات |

### `project-RealEstate_database/`

خلفية Laravel. أهم مجلداتها:

| المسار | المعنى |
| --- | --- |
| `app/Http/Controllers/Api/` | متحكمات REST API |
| `app/Http/Requests/` | التحقق من الطلبات |
| `app/Models/` | نماذج Eloquent |
| `app/Policies/` | سياسات الصلاحيات |
| `app/Services/` | منطق الأعمال |
| `app/Traits/` | تنسيق استجابات المجالات |
| `config/realestate.php` | قواعد النظام |
| `config/ml.php` | إعدادات خدمة توقع السعر |
| `database/migrations/` | مخطط قاعدة البيانات |
| `database/seeders/` | بيانات التجربة |
| `routes/api/` | مسارات API المقسمة حسب المجال |
| `ml/pricing/` | خدمة Flask |

## 4. الأدوار والصلاحيات

| الدور | القيمة | واجهة المستخدم | أهم الصلاحيات |
| --- | --- | --- | --- |
| المدير | `admin` | `/admin/*` | إدارة كل الموارد، المراجعة، الثقة، المستخدمين |
| الشركة | `company` | `/company/*` | ملف الشركة، الوسطاء، العقارات، روابط التواصل |
| الوسيط | `agent` | `/agent/*` | ملف الوسيط، عقاراته، روابطه |
| المستثمر/المشتري | `buyer` | `/buyer/*` | المفضلة، التوصيات، المحافظ، التحليلات |
| المالك | `owner` | `/owner/*` | عقارات المالك، لوحة المالك، التحليلات |
| الزائر | بدون توكن | صفحات عامة | تصفح الفهارس والتفاصيل |

الحماية في الواجهة تتم عبر `src/router/guards.js`. الحماية في الخلفية تتم عبر `auth:sanctum` وميدلوير الإدارة وسياسات Laravel.

## 5. الواجهة الأمامية

### 5.1 التقنيات

- Vue 3.
- Vite.
- Pinia.
- Vue Router.
- Bootstrap RTL وBootstrap Icons.
- Leaflet وOpenStreetMap.
- CSS variables ونظام ألوان في `src/styles/colors.css`.

### 5.2 التخطيطات

| Layout | الاستخدام |
| --- | --- |
| `MainLayout` | الصفحات العامة |
| `AuthLayout` | الدخول والتسجيل |
| `AdminLayout` | المدير |
| `CompanyLayout` | الشركة |
| `AgentLayout` | الوسيط |
| `BuyerLayout` | المستثمر |
| `OwnerLayout` | المالك |

### 5.3 الصفحات العامة

- الصفحة الرئيسية.
- قائمة العقارات.
- تفاصيل العقار.
- خريطة العقارات.
- المدن وتفاصيل المدينة.
- المناطق وتفاصيل المنطقة.
- الوسطاء وتفاصيل الوسيط.
- الشركات وتفاصيل الشركة.
- الدردشة والرسائل للمستخدمين المسجلين.
- الملف الشخصي والمفضلة والتوصيات.

### 5.4 لوحات الأدوار

#### لوحة المدير

تغطي:

- الإحصائيات العامة.
- إدارة المستخدمين.
- إدارة العقارات وحالتها ووسائطها.
- إدارة الشركات وحالتها.
- إدارة الوسطاء.
- إدارة المدن والمناطق.
- مراجعة التقييمات وطلبات التوثيق.
- الملف الشخصي للمدير.

#### لوحة الشركة

تغطي:

- ملخص الشركة.
- إدارة عقارات الشركة.
- إضافة وتعديل الوسطاء.
- قبول أو رفض الوسطاء المرتبطين بالشركة.
- إدارة روابط التواصل.
- مراجعة تقييمات الشركة.
- ملف الشركة والملف الشخصي.

#### لوحة الوسيط

تغطي:

- ملخص الوسيط.
- عقارات الوسيط.
- إضافة وتعديل العقارات.
- الملف الشخصي.
- روابط التواصل.
- تحليلات الاستثمار.

#### لوحة المستثمر

تغطي:

- لوحة المستثمر.
- المفضلة.
- التوصيات.
- المحافظ الاستثمارية.
- تفاصيل المحفظة.
- التحليلات الاستثمارية.
- تحليل عقار محدد.
- الملف الشخصي وروابط التواصل.

#### لوحة المالك

تغطي:

- لوحة المالك.
- عقارات المالك.
- إضافة وتعديل العقارات.
- التحليلات الاستثمارية.

### 5.5 طبقة API في الواجهة

كل طلبات المستخدم تمر عبر:

```text
src/api/client.js
```

ومسارات الإدارة عبر:

```text
src/api/admin/client.js
```

خصائص مهمة:

- حقن Bearer token تلقائيًا.
- قراءة `VITE_API_BASE_URL`.
- توحيد أخطاء API عبر `ApiError`.
- نجاح طلبات non-GET يظهر toast عند وجود رسالة من الخلفية.
- كل الأخطاء تظهر toast تلقائيًا.

### 5.6 نظام النماذج والأخطاء

النمط المعتمد:

- النموذج يستخدم `useFormErrors`.
- النموذج يعرّف `defineExpose({ handleSubmitError })`.
- الصفحة الأب تضع `ref` على النموذج.
- عند catch تمرر الخطأ إلى `formRef.value?.handleSubmitError(err)`.
- لا تستخدم `FormAlert` لأخطاء الحفظ لأن toast العام يغطيها.

### 5.7 نظام التأكيد والتوست

- `ToastNotification` مثبت في `App.vue`.
- `ConfirmDialog` مثبت في `App.vue`.
- الحذف أو القرارات الخطرة تستخدم `useConfirmStore().show(...)`.
- لا تستخدم `alert()` أو `confirm()` الأصليين.

## 6. الخلفية

### 6.1 المصادقة

المصادقة عبر Laravel Sanctum Bearer Token.

المسارات الأساسية:

- `POST /api/v1/register`
- `POST /api/v1/login`
- `POST /api/v1/logout`
- `GET /api/v1/me`
- `PUT /api/v1/profile`

يوجد مسار دخول إداري:

- `POST /api/v1/admin/login`

### 6.2 شكل الاستجابة

النجاح:

```json
{
  "success": true,
  "message": "Operation completed.",
  "data": {},
  "pagination": {}
}
```

الخطأ:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {}
}
```

### 6.3 الخدمات الأساسية

| الخدمة | المسؤولية |
| --- | --- |
| `FileUploadService` | رفع الملفات والتحقق من الامتدادات والأحجام |
| `EstateImageService` | إدارة صور العقارات والصورة الرئيسية |
| `GeoSearchService` | حساب المسافات والبحث على الخريطة |
| `NotificationService` | إنشاء وإدارة الإشعارات |
| `SocialLinkService` | مزامنة روابط التواصل polymorphic |
| `PropertyInteractionService` | تسجيل مشاهدة وتواصل ومشاركة العقار |
| `RecommendationService` | قراءة توصيات المستخدم |
| `RecommendationGeneratorService` | توليد التوصيات من المرشحين |
| `RecommendationScoringService` | حساب درجة ملاءمة العقار |
| `InvestmentCalculatorService` | حساب العائد والمؤشرات الاستثمارية |
| `InvestorDashboardService` | ملخص المستثمر والمحفظة |
| `OwnerDashboardService` | ملخص المالك وعقاراته |
| `PortfolioService` | إدارة المحافظ وعناصرها |
| `PricePredictionClient` | الاتصال بخدمة Flask |
| `EstatePricePredictionService` | توقع سعر عقار محفوظ |
| `MarketTrendsService` | اتجاهات السوق |
| `ReviewService` | عمليات التقييم |
| `TrustScoreService` | حساب درجة الثقة |
| `VerificationRequestService` | طلبات التوثيق |
| `StatisticsService` | إحصائيات لوحة الإدارة |

## 7. قاعدة البيانات

### 7.1 المستخدمون والمصادقة

- `users`: الحسابات والأدوار والحالة وآخر نشاط.
- `personal_access_tokens`: توكنات Sanctum.
- `password_reset_tokens`: إعادة تعيين كلمة المرور.

### 7.2 الموقع

- `cities`: المدن.
- `places`: المناطق، وترتبط بمدينة.

### 7.3 العقارات والوسائط

- `estates`: العقارات، الأسعار، النوع، الحالة، الموقع، بيانات الاستثمار.
- `estate_images`: صور العقارات.
- `estate_videos`: فيديوهات العقارات.
- `estate_ads`: صور/إعلانات العقارات.

### 7.4 الشركات والوسطاء

- `companies`: ملف الشركة وحالتها وساعات العمل.
- `agents`: ملف الوسيط وربطه بالمستخدم/الشركة وحالة الموافقة.
- `social_links`: روابط تواصل متعددة الأشكال للمستخدم أو الشركة أو الوسيط أو العقار.

### 7.5 التفاعل

- `favorite_estates`: مفضلة العقارات.
- `favorite_agents`: مفضلة الوسطاء.
- `messages`: المحادثات.
- `notifications`: الإشعارات.
- `property_interactions`: مشاهدة، مشاركة، تواصل، وتحليل تفاعل العقار.

### 7.6 الاستثمار

- `investment_analyses`: نتائج تحليل الاستثمار لعقار.
- `investment_portfolios`: محافظ استثمارية.
- `portfolio_properties`: ربط محافظ بعقارات.
- `portfolio_items`: عناصر محفظة بحالة tracking/invested/sold.

### 7.7 الذكاء والتوصيات

- `price_predictions`: سجل توقعات الأسعار.
- `user_preferences`: تفضيلات المستخدم.
- `recommendations`: توصيات محفوظة للمستخدم.

### 7.8 الثقة والمراجعات

- `property_reviews`: تقييمات العقارات.
- `agent_reviews`: تقييمات الوسطاء.
- `company_reviews`: تقييمات الشركات.
- `verification_requests`: طلبات توثيق الحسابات.

## 8. API حسب المجال

### 8.1 الفهارس العامة

بدون مصادقة:

- `GET /api/v1/companies`
- `GET /api/v1/companies/{company}`
- `GET /api/v1/companies/{company}/agents`
- `GET /api/v1/agents`
- `GET /api/v1/agents/{agent}`
- `GET /api/v1/cities`
- `GET /api/v1/cities/{city}`
- `GET /api/v1/cities/{city}/places`
- `GET /api/v1/places`
- `GET /api/v1/places/{place}`
- `GET /api/v1/estates`
- `GET /api/v1/estates/{estate}`

### 8.2 الخرائط

- `GET /api/v1/estates/map`
- `GET /api/v1/estates/nearby`
- `GET /api/v1/estates/in-radius`

تعتمد على latitude/longitude وLeaflet/OpenStreetMap.

### 8.3 عقارات المستخدم

بعد المصادقة:

- `GET /api/v1/my/estates`
- `POST /api/v1/my/estates`
- `GET /api/v1/my/estates/{estate}`
- `PUT /api/v1/my/estates/{estate}`
- `DELETE /api/v1/my/estates/{estate}`
- صور وفيديوهات وإعلانات داخل `/my/estates/{estate}/...`

### 8.4 المفضلة

- `GET /api/v1/favorites`
- `POST /api/v1/favorites`
- `GET /api/v1/favorites/check/{estate}`
- `DELETE /api/v1/favorites/{favorite_estate}`
- `POST /api/v1/estates/{estate}/favorite`
- `DELETE /api/v1/estates/{estate}/favorite`

### 8.5 الرسائل والإشعارات

- `GET /api/v1/messages`
- `POST /api/v1/messages`
- `GET /api/v1/messages/conversation/{user}`
- `PATCH /api/v1/messages/{message}/read`
- `GET /api/v1/notifications`
- `PATCH /api/v1/notifications/read-all`
- `PATCH /api/v1/notifications/{notification}/read`

### 8.6 الاستثمار والمحافظ

- `GET /api/v1/my/investment-analyses`
- `POST /api/v1/my/investment-analyses`
- `GET /api/v1/my/investment-analyses/{id}`
- `PUT /api/v1/my/investment-analyses/{id}`
- `DELETE /api/v1/my/investment-analyses/{id}`
- `POST /api/v1/estates/{estate}/investment-analyses`
- `GET /api/v1/my/portfolios`
- `POST /api/v1/my/portfolios`
- `GET /api/v1/my/portfolio-items`
- `POST /api/v1/my/portfolio-items`
- `PUT /api/v1/my/portfolio-items/{id}`
- `DELETE /api/v1/my/portfolio-items/{id}`
- `GET /api/v1/investor-dashboard`
- `GET /api/v1/my/investor-dashboard`

### 8.7 التوصيات والتفضيلات

- `GET /api/v1/preferences`
- `POST /api/v1/preferences`
- `PUT /api/v1/preferences`
- `DELETE /api/v1/preferences`
- `GET /api/v1/recommendations`
- `GET /api/v1/recommendations/top`
- `GET /api/v1/recommendations/similar-estates/{estate}`
- `POST /api/v1/recommendations/refresh`

### 8.8 توقع السعر والتحليلات السوقية

- `POST /api/v1/price-predictions/preview`
- `POST /api/v1/estates/{estate}/price-prediction`
- `GET /api/v1/market-analytics/trends`

### 8.9 الثقة والتقييمات

عام:

- `GET /api/v1/estates/{estate}/reviews`
- `GET /api/v1/agents/{agent}/reviews`
- `GET /api/v1/companies/{company}/reviews`
- `GET /api/v1/agents/{agent}/trust-score`
- `GET /api/v1/companies/{company}/trust-score`

بعد المصادقة:

- `POST /api/v1/estates/{estate}/reviews`
- `POST /api/v1/agents/{agent}/reviews`
- `POST /api/v1/companies/{company}/reviews`
- `GET /api/v1/verification-requests`
- `POST /api/v1/verification-requests`

### 8.10 API الإدارة

قاعدة الإدارة:

```text
/api/v1/admin
```

مجالات الإدارة:

- `users`
- `companies`
- `agents`
- `cities`
- `places`
- `estates`
- `notifications`
- `messages`
- `social-links`
- `trust`
- `statistics`

## 9. خدمة توقع السعر

الخدمة موجودة في `project-RealEstate_database/ml/pricing/`.

مكوناتها:

- `server.py`: خادم Flask.
- `requirements.txt`: حزم Python.
- `real_estate_model.pkl`: النموذج.
- `label_encoder.pkl`: ترميز القيم النصية.

Laravel يتواصل معها عبر:

- `config/ml.php`
- `App\Services\Ai\PricePredictionClient`
- `App\Services\Ai\EstatePricePredictionService`

تدفق العمل:

1. المستخدم يطلب توقع سعر لعقار أو بيانات preview.
2. Laravel يجهز الحقول المطلوبة.
3. Laravel يرسل HTTP إلى Flask.
4. Flask يرجع السعر المتوقع.
5. Laravel يحفظ النتيجة في `price_predictions` عند تفعيل التسجيل.
6. الواجهة تعرض السعر والفرق عن السعر المعروض.

## 10. نظام التوصيات

يعتمد النظام على:

- تفضيلات المستخدم في `user_preferences`.
- تفاعلات المستخدم في `property_interactions`.
- المفضلة.
- خصائص العقار مثل المدينة، النوع، السعر، الاستخدام، ومؤشرات الاستثمار.

الخدمات المسؤولة:

- `RecommendationService`
- `RecommendationGeneratorService`
- `RecommendationScoringService`

الإعدادات المهمة في `config/realestate.php`:

- `recommendation_limit`
- `recommendation_min_score`
- `recommendation_candidate_pool`
- `recommendation_stale_hours`
- `similar_estates_pool`

## 11. نظام الاستثمار

يدعم النظام:

- تحليل ROI.
- الدخل السنوي المتوقع.
- فترة الاسترداد.
- محافظ متعددة.
- حالة عنصر المحفظة: `tracking`, `invested`, `sold`.
- لوحة مستثمر بملخص مالي.
- لوحة مالك تلخص عقاراته وتحليلاتها.

الخدمات المسؤولة:

- `InvestmentCalculator`
- `InvestmentCalculatorService`
- `InvestmentMetrics`
- `InvestorDashboardService`
- `OwnerDashboardService`
- `PortfolioService`

## 12. نظام الثقة

نظام الثقة يبني مصداقية الشركات والوسطاء من خلال:

- حالة توثيق الحساب.
- عدد العقارات المقبولة.
- متوسط التقييم.
- عدد التقييمات.
- نشاط الحساب على المنصة.

الأوزان موجودة في:

```text
config/realestate.php -> trust_score.weights
```

التقييمات لها حالات:

- `pending`
- `approved`
- `rejected`

طلبات التوثيق لها الحالات نفسها، ويمكن للمدير قبولها أو رفضها وتنزيل المستندات.

## 13. إدارة الوسائط

العقار يدعم:

- صور متعددة.
- صورة رئيسية.
- فيديوهات.
- إعلانات أو صور ترويجية.

الرفع يعتمد على:

- `FileUploadService`
- `EstateImageService`
- إعدادات `realestate.upload`

الواجهة تستخدم:

- `AppFileUpload`
- `EstateForm`
- `AdminEstateForm`
- `AdminEstateMediaPanel`

## 14. بيانات التجربة

يشغل `DatabaseSeeder` عدة seeders:

- `AdminSeeder`
- `LocationSeeder`
- `DemoUserSeeder`
- `CompanySeeder`
- `AgentSeeder`
- `EstateSeeder`
- `ReviewSeeder`
- `PortfolioSeeder`

الحسابات الافتراضية:

| الدور | البريد | كلمة المرور |
| --- | --- | --- |
| مدير | `admin@realestate.test` | `password` |
| مالك | `owner@realestate.test` | `password` |
| مستثمر | `buyer@realestate.test` | `password` |
| شركة | `company@realestate.test` | `password` |
| وسيط | `agent@realestate.test` | `password` |

يمكن تغيير كلمة المرور عبر:

```env
SEED_PASSWORD=your-password
```

## 15. التشغيل المحلي خطوة بخطوة

### 15.1 الخلفية

```sh
cd project-RealEstate_database
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 15.2 الواجهة

```sh
cd project-RealEstate
npm install
npm run dev
```

### 15.3 خدمة ML

```sh
cd project-RealEstate_database/ml/pricing
pip install -r requirements.txt
python server.py
```

## 16. الفحص والاختبارات

### الواجهة

```sh
cd project-RealEstate
npx eslint src/ --no-error-on-unmatched-pattern
npm run build
```

### الخلفية

```sh
cd project-RealEstate_database
php artisan test
php artisan route:list --path=api/v1
```

## 17. قواعد التطوير

- لا تضف HTML خام عندما يوجد مكون UI مناسب.
- حافظ على الواجهة العربية وRTL.
- استخدم `useConfirmStore` لأي تأكيد.
- دع نظام toast العام يتعامل مع نجاح وأخطاء API.
- لا تكسر شكل الاستجابة الموحد.
- عند تعديل نموذج في الواجهة، راجع التحقق المقابل في Laravel.
- عند إضافة حقل لعقار، راجع المكونات، requests، model casts/fillable، format traits، seeders، والاختبارات.
- عند إضافة endpoint، أضف خدمة مناسبة في `src/api/` بدل استدعاء fetch مباشرة من الصفحة.
- عند تعديل ملفات DBML، تحقق من عدم تكرار العلاقات inline و`Ref` لأن أدوات DBML قد تفشل عند التكرار.

## 18. ملفات مرجعية داخل المشروع

| الملف | الفائدة |
| --- | --- |
| `README.ar.md` | مدخل سريع للمشروع |
| `REFERENCE.md` | مرجع تقني مختصر |
| `project-RealEstate/README.md` | تشغيل وفهم الواجهة |
| `project-RealEstate_database/README.md` | تشغيل وفهم الخلفية |
| `project-RealEstate_database/routes/API.md` | مرجع مسارات API |
| `project-RealEstate_database/docs/api/*.md` | وثائق API تفصيلية لبعض المجالات |
| `project-RealEstate_database/docs/ar/` | وثائق عربية داعمة |
| `assets/` | مخططات وصور توثيقية |
