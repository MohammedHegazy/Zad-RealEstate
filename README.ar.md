# منصة زاد للعقارات

منصة زاد للعقارات هي نظام عقاري كامل مبني كواجهة Vue 3 مستقلة وخلفية Laravel 12 تعمل كـ REST API، مع خدمة Flask منفصلة لتوقع أسعار العقارات بالذكاء الاصطناعي. يضم النظام تصفح العقارات والمدن والمناطق، لوحات خاصة للشركة والوسيط والمالك والمستثمر، لوحة إدارة، دردشة، مفضلة، توصيات ذكية، محافظ وتحليلات استثمارية، نظام تقييمات وثقة، وإدارة وسائط العقارات.

## أين أبدأ؟

| الملف | الغرض |
| --- | --- |
| `SYSTEM_DOCUMENTATION.ar.md` | التوثيق العربي الشامل للنظام بالكامل |
| `REFERENCE.md` | مرجع تقني مختصر للبنية، المسارات، API، وقواعد التطوير |
| `project-RealEstate/README.md` | تشغيل وتطوير الواجهة الأمامية |
| `project-RealEstate_database/README.md` | تشغيل وتطوير الخلفية وخدمة الذكاء الاصطناعي |

## بنية المستودع

```text
Real-estat-ziad/
├── project-RealEstate/             # واجهة Vue 3 + Vite + Pinia
├── project-RealEstate_database/    # Laravel 12 API + Sanctum + ML bridge
├── assets/                         # صور ومخططات ومواد توثيقية
├── Documentation Chapters/         # فصول ووثائق داعمة
├── README.ar.md                    # هذا الملف
├── REFERENCE.md                    # مرجع تقني سريع
└── SYSTEM_DOCUMENTATION.ar.md      # التوثيق العربي الشامل
```

## التشغيل السريع

شغل الخلفية:

```sh
cd project-RealEstate_database
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

شغل الواجهة:

```sh
cd project-RealEstate
npm install
npm run dev
```

اختياريًا، شغل خدمة توقع السعر:

```sh
cd project-RealEstate_database/ml/pricing
pip install -r requirements.txt
python server.py
```

## روابط التشغيل الافتراضية

| الخدمة | الرابط |
| --- | --- |
| الواجهة | `http://localhost:5173` |
| Laravel API | `http://localhost:8000/api/v1` |
| خدمة توقع السعر | `http://127.0.0.1:5000` |

## حسابات التجربة

كلمة المرور الافتراضية تأتي من `SEED_PASSWORD`، أو `password` عند عدم ضبطها.

| الدور | البريد |
| --- | --- |
| مدير | `admin@realestate.test` |
| مالك | `owner@realestate.test` |
| مستثمر/مشتري | `buyer@realestate.test` |
| شركة | `company@realestate.test` |
| وسيط | `agent@realestate.test` |

## أوامر مهمة

| المكان | الأمر | الغرض |
| --- | --- | --- |
| `project-RealEstate/` | `npm run dev` | تشغيل الواجهة |
| `project-RealEstate/` | `npx eslint src/ --no-error-on-unmatched-pattern` | فحص الواجهة حسب تعليمات المشروع |
| `project-RealEstate/` | `npm run build` | بناء الواجهة |
| `project-RealEstate_database/` | `php artisan serve` | تشغيل API |
| `project-RealEstate_database/` | `php artisan migrate --seed` | إنشاء قاعدة البيانات وبيانات التجربة |
| `project-RealEstate_database/` | `php artisan test` | تشغيل اختبارات Laravel |

## ملاحظات تطوير

- الواجهة عربية وRTL، وتعتمد مكونات UI مشتركة مثل `AppInput`, `AppButton`, `AdminDataTable`, `MapLocationPicker`.
- كل طلبات API في الواجهة تمر عبر `src/api/client.js`، والتوستات تظهر تلقائيًا من النظام العام.
- المصادقة عبر Laravel Sanctum Bearer Tokens.
- استجابات الخلفية موحدة عبر `BaseApiController`.
- خدمة ML ليست جزءًا من Laravel مباشرة؛ Laravel يتواصل معها عبر HTTP حسب إعدادات `config/ml.php`.
