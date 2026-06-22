# واجهة منصة زاد العقارية

هذه الواجهة هي تطبيق Vue 3 أحادي الصفحة يعمل عبر Vite ويتصل بخلفية Laravel من خلال REST API على `/api/v1`. الواجهة عربية وRTL وتستخدم Pinia لإدارة الحالة، Vue Router للتنقل، Bootstrap RTL للهيكل البصري، وLeaflet لخرائط العقارات.

## التشغيل

```sh
npm install
npm run dev
```

الرابط الافتراضي:

```text
http://localhost:5173
```

## الإعدادات

ملف البيئة:

```text
.env
```

المتغير الأساسي:

```env
VITE_API_BASE_URL=http://localhost:8000/api/v1
```

عند الاختبار من هاتف أو جهاز آخر على نفس الشبكة، استخدم IP الجهاز الذي يشغل Laravel:

```env
VITE_API_BASE_URL=http://192.168.1.10:8000/api/v1
```

## أوامر التطوير

| الأمر | الغرض |
| --- | --- |
| `npm run dev` | تشغيل Vite محليًا |
| `npm run build` | بناء نسخة الإنتاج |
| `npm run preview` | معاينة البناء |
| `npx eslint src/ --no-error-on-unmatched-pattern` | فحص ESLint المطلوب في تعليمات المشروع |
| `npm run lint` | فحص وإصلاح عبر Oxlint وESLint |
| `npm run format` | تنسيق ملفات `src/` عبر Prettier |

## بنية `src`

```text
src/
├── api/           # خدمات الاتصال مع الخلفية
├── components/    # مكونات UI ومكونات المجالات
├── composables/   # منطق مشترك قابل لإعادة الاستخدام
├── config/        # ثوابت وخيارات النظام
├── layouts/       # تخطيطات الأدوار
├── router/        # المسارات والحراس
├── stores/        # Pinia stores
├── styles/        # نظام الألوان والأنماط
├── utils/         # دوال مساعدة
└── views/         # صفحات التطبيق
```

## أهم المسارات

| مجموعة | أمثلة |
| --- | --- |
| عام | `/`, `/estates`, `/estates/map`, `/cities`, `/places`, `/agents`, `/companies` |
| مصادقة | `/login`, `/register`, `/profile` |
| شركة | `/company/dashboard`, `/company/estates`, `/company/agents`, `/company/social-links` |
| وسيط | `/agent/dashboard`, `/agent/estates`, `/agent/profile` |
| مالك | `/owner/dashboard`, `/owner/estates`, `/owner/investment-analytics` |
| مستثمر | `/buyer/dashboard`, `/buyer/favorites`, `/buyer/recommendations`, `/buyer/portfolios` |
| إدارة | `/admin/dashboard`, `/admin/users`, `/admin/estates`, `/admin/companies`, `/admin/trust` |

## طبقة API

- `src/api/client.js`: العميل العام لكل طلبات المستخدم.
- `src/api/admin/client.js`: عميل الإدارة ويضيف `/admin`.
- `src/api/errors.js`: كلاس `ApiError`.
- `src/api/errorHandler.js`: توحيد قراءة أخطاء API.
- `src/api/formData.js`: تجهيز طلبات الملفات.

كل الخدمات في `src/api/*.js` تتبع نمطًا واضحًا: `list`, `getById`, `create`, `update`, `remove` حسب المجال.

## الحالة العامة

| Store | الغرض |
| --- | --- |
| `auth.js` | التوكن، المستخدم، الدخول، التسجيل، الخروج، `fetchMe` |
| `toast.js` | رسائل نجاح/خطأ/تنبيه عائمة |
| `confirm.js` | نافذة تأكيد موحدة بدل `confirm()` |

## قواعد الواجهة

- استخدم مكونات المشروع مثل `AppInput`, `AppSelect`, `AppButton`, `AdminDataTable`, `MapLocationPicker`.
- لا تستدع توست نجاح/خطأ يدويًا لعمليات API؛ العميل العام يعرضها تلقائيًا.
- النماذج المركبة تكشف `handleSubmitError` عبر `defineExpose` حتى يمرر الأب أخطاء 422 إليها.
- كل التأكيدات يجب أن تمر عبر `useConfirmStore().show(...)`.
- حافظ على النصوص العربية في الواجهة، واترك رسائل الخلفية الإنجليزية تظهر في التوست عند رجوعها من API.

## أهم مكونات المجال

| المجال | مكونات |
| --- | --- |
| العقارات | `EstateForm`, `AdminEstateForm`, `EstateGallery`, `EstateInvestment`, `EstateLocationMap`, `EstatePricePrediction` |
| الخرائط | `LeafletMap`, `MapLocationPicker` |
| الشركة | `CompanySidebar`, `AgentForm`, `SocialLinksManager` |
| الإدارة | `AdminDataTable`, `AdminPageHeader`, `AdminStatsSection`, `AdminEstateMediaPanel`, `AdminModerationDialog` |
| الدردشة | `ChatWindow` |
| التقييمات | `ReviewSection`, `ReviewForm`, `TrustBadge`, `TrustScorePanel` |
