# مرجع نظام زاد العقاري

هذا الملف مرجع عملي سريع للمطورين. التوثيق الكامل موجود في `SYSTEM_DOCUMENTATION.ar.md`.

## المكدس التقني

| الطبقة | التقنية | المسار |
| --- | --- | --- |
| الواجهة | Vue 3, Vite, Pinia, Vue Router, Bootstrap RTL, Leaflet | `project-RealEstate/` |
| الخلفية | Laravel 12, Sanctum, Eloquent, Policies, Services | `project-RealEstate_database/` |
| الذكاء الاصطناعي | Flask, scikit-learn, joblib | `project-RealEstate_database/ml/pricing/` |
| قاعدة البيانات | SQLite للتطوير أو MySQL للإنتاج | `project-RealEstate_database/database/` |

## أوامر التشغيل والفحص

### الواجهة

```sh
cd project-RealEstate
npm install
npm run dev
npx eslint src/ --no-error-on-unmatched-pattern
npm run build
```

### الخلفية

```sh
cd project-RealEstate_database
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
php artisan test
```

### خدمة توقع السعر

```sh
cd project-RealEstate_database/ml/pricing
pip install -r requirements.txt
python server.py
```

## بنية الواجهة

```text
project-RealEstate/src/
├── api/           # طبقة الاتصال مع Laravel API
├── components/    # مكونات عامة ومكونات حسب المجال
├── composables/   # منطق قابل لإعادة الاستخدام
├── config/        # ثوابت وقوائم وخيارات النظام
├── layouts/       # تخطيطات الأدوار
├── router/        # تعريف المسارات والحراس
├── stores/        # Pinia: auth, toast, confirm
├── styles/        # نظام الألوان والأنماط
├── utils/         # دوال مساعدة
└── views/         # صفحات التطبيق
```

### التخطيطات

| التخطيط | الدور |
| --- | --- |
| `MainLayout` | الصفحات العامة |
| `AuthLayout` | تسجيل الدخول والتسجيل |
| `AdminLayout` | المدير |
| `CompanyLayout` | الشركة |
| `AgentLayout` | الوسيط |
| `BuyerLayout` | المستثمر/المشتري |
| `OwnerLayout` | المالك |

### حراس المسارات

| الحارس | الغرض |
| --- | --- |
| `guestGuard` | منع المستخدم المسجل من صفحات الدخول والتسجيل |
| `authGuard` | حماية صفحات المستخدم العام |
| `adminGuard` | حصر لوحة الإدارة على `admin` |
| `companyGuard` | حصر لوحة الشركة على `company` |
| `agentGuard` | حصر لوحة الوسيط على `agent` |
| `buyerGuard` | حصر لوحة المستثمر على `buyer` |
| `ownerGuard` | حصر لوحة المالك على `owner` |

## مكونات الواجهة المفضلة

استخدم هذه المكونات قبل كتابة HTML خام:

- نماذج: `AppInput`, `AppSelect`, `AppFormGroup`, `AppTextarea`, `AppCheckbox`, `AppButton`, `AppFileUpload`, `AppAutocomplete`
- قوائم وجداول: `DirectoryToolbar`, `Pagination`, `TableAction`, `TableActionGroup`, `AdminDataTable`
- حالات واجهة: `EmptyState`, `FormAlert`, `ErrorAlert`, `LoadingSpinner`
- إدارة: `StatusBadge`, `AdminPageHeader`, `AdminStatCard`, `AdminStatsSection`, `AdminSidebar`
- خرائط: `MapLocationPicker`, `LeafletMap`
- عامة: `ToastNotification`, `ConfirmDialog`

## قواعد API في الواجهة

- العميل الأساسي: `src/api/client.js`.
- عميل الإدارة: `src/api/admin/client.js` ويضيف بادئة `/admin`.
- التوكن يحقن تلقائيًا من Pinia auth store.
- نجاح أي طلب غير GET يظهر toast تلقائيًا عند وجود `message`.
- أخطاء API، بما فيها 422، تظهر toast تلقائيًا.
- لا تستدع `toast.success()` أو `toast.error()` يدويًا لعمليات API العادية.

## بنية الخلفية

```text
project-RealEstate_database/
├── app/Http/Controllers/Api/        # متحكمات المستخدم والمدير
├── app/Http/Requests/               # التحقق من الطلبات
├── app/Models/                      # نماذج Eloquent
├── app/Policies/                    # صلاحيات الثقة والمحافظ والمراجعات
├── app/Services/                    # منطق الأعمال
├── app/Traits/                      # تنسيق استجابات المجالات
├── config/realestate.php            # قواعد النظام
├── config/ml.php                    # اتصال خدمة ML
├── database/migrations/             # الجداول
├── database/seeders/                # بيانات تجربة
├── routes/api/                      # تقسيم REST API
└── ml/pricing/                      # Flask price prediction
```

## الاستجابة القياسية من الخلفية

```json
{
  "success": true,
  "message": "Message text",
  "data": {},
  "pagination": {}
}
```

الأخطاء:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {}
}
```

## مجالات API الرئيسية

| المجال | المسارات |
| --- | --- |
| المصادقة | `/api/v1/register`, `/api/v1/login`, `/api/v1/logout`, `/api/v1/me` |
| الفهارس العامة | `/companies`, `/agents`, `/cities`, `/places`, `/estates` |
| الخرائط | `/estates/map`, `/estates/nearby`, `/estates/in-radius` |
| عقاراتي | `/my/estates` مع صور وفيديوهات وإعلانات |
| المفضلة | `/favorites`, `/estates/{estate}/favorite` |
| الرسائل | `/messages`, `/messages/conversation/{user}` |
| الإشعارات | `/notifications`, `/my/notifications` |
| الاستثمار | `/my/investment-analyses`, `/my/portfolios`, `/my/portfolio-items` |
| المستثمر | `/investor-dashboard`, `/my/investor-dashboard` |
| توقع السعر | `/price-predictions/preview`, `/estates/{estate}/price-prediction` |
| التوصيات | `/recommendations`, `/recommendations/top`, `/recommendations/refresh` |
| الثقة | reviews, trust-score, verification-requests |
| الإدارة | `/api/v1/admin/*` |

## أدوار المستخدم

| الدور | القيمة في قاعدة البيانات | الواجهة |
| --- | --- | --- |
| المدير | `admin` | `/admin/*` |
| الشركة | `company` | `/company/*` |
| الوسيط | `agent` | `/agent/*` |
| المستثمر/المشتري | `buyer` | `/buyer/*` |
| المالك | `owner` | `/owner/*` |

## جداول قاعدة البيانات الأساسية

| مجموعة | جداول |
| --- | --- |
| المستخدم والمصادقة | `users`, `personal_access_tokens`, `password_reset_tokens` |
| الموقع | `cities`, `places` |
| العقارات | `estates`, `estate_images`, `estate_videos`, `estate_ads` |
| الجهات | `companies`, `agents`, `social_links` |
| التفاعل | `favorite_estates`, `favorite_agents`, `messages`, `notifications` |
| الاستثمار | `investment_analyses`, `investment_portfolios`, `portfolio_properties`, `portfolio_items` |
| الذكاء الاصطناعي | `price_predictions`, `user_preferences`, `recommendations`, `property_interactions` |
| الثقة | `property_reviews`, `agent_reviews`, `company_reviews`, `verification_requests` |

## قواعد مهمة عند التطوير

- الواجهة عربية وRTL.
- حافظ على نمط المكونات المشتركة بدل Bootstrap خام.
- النماذج تستخدم `useFormErrors` وتعرض أخطاء 422 على الحقول.
- عمليات الحذف والتأكيد تستخدم `useConfirmStore().show(...)`.
- لا تكسر شكل الاستجابة القياسي في `BaseApiController`.
- عند إضافة حقل لعقار، راجع: Form Vue، Request Laravel، Model fillable/casts، Formatter trait، والاختبارات إن وجدت.
- ملفات الوسائط تدار عبر `FileUploadService` و`EstateImageService`.
- تحديث الروابط الاجتماعية يتم عبر `SocialLinkService@syncFromRequest`.
