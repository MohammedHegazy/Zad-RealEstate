# خلفية منصة زاد العقارية

هذه الخلفية هي Laravel 12 API لمنصة زاد العقارية. توفر مصادقة Sanctum، إدارة العقارات والوسائط، الشركات والوسطاء، المدن والمناطق، المفضلة، الرسائل، الإشعارات، المحافظ والتحليلات الاستثمارية، التوصيات الذكية، نظام الثقة والتقييمات، وجسر اتصال مع خدمة Flask لتوقع أسعار العقارات.

## التشغيل المحلي

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

الرابط الافتراضي:

```text
http://localhost:8000/api/v1
```

## أوامر مهمة

| الأمر | الغرض |
| --- | --- |
| `php artisan serve` | تشغيل API |
| `php artisan migrate --seed` | إنشاء الجداول وبيانات التجربة |
| `php artisan migrate:fresh --seed` | إعادة بناء قاعدة التطوير |
| `php artisan route:list --path=api/v1` | عرض مسارات API |
| `php artisan test` | تشغيل الاختبارات |
| `php artisan config:clear` | تنظيف كاش الإعدادات |
| `composer run dev` | تشغيل Laravel وqueue وpail وVite الخاص بسقالة Laravel |

## البيئة

ملف `.env.example` يستخدم SQLite افتراضيًا للتطوير:

```env
DB_CONNECTION=sqlite
```

لـ MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=realestate
DB_USERNAME=root
DB_PASSWORD=
```

إعدادات خدمة توقع السعر:

```env
ML_PRICE_PREDICTION_URL=http://127.0.0.1:5000
ML_PRICE_PREDICTION_TIMEOUT=10
ML_PRICE_PREDICTION_CONNECT_TIMEOUT=3
ML_PRICE_PREDICTION_LOCATION_FIELD=city
ML_PRICE_PREDICTION_LOG=true
```

إعدادات الخريطة:

```env
MAP_DEFAULT_LAT=33.5138
MAP_DEFAULT_LNG=36.2765
MAP_DEFAULT_ZOOM=12
MAP_TILE_URL=https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
```

## خدمة توقع السعر

الخدمة موجودة في:

```text
ml/pricing/
```

التشغيل:

```sh
cd ml/pricing
pip install -r requirements.txt
python server.py
```

Laravel لا يحمل ملفات `pkl` مباشرة، بل يرسل طلب HTTP إلى Flask عبر `PricePredictionClient` ثم يحفظ النتائج في `price_predictions` عند تفعيل `ML_PRICE_PREDICTION_LOG`.

## بنية الخلفية

```text
app/
├── Http/Controllers/Api/        # API للمستخدم والمدير
├── Http/Requests/               # قواعد التحقق
├── Models/                      # Eloquent models
├── Policies/                    # صلاحيات المجالات
├── Services/                    # منطق الأعمال
└── Traits/                      # تنسيق استجابات المجالات

routes/api/
├── v1.php                       # API المستخدم
├── admin.php                    # API المدير
├── v1/public.php                # فهارس عامة
├── v1/authenticated/            # مسارات المستخدم بعد المصادقة
└── admin/authenticated/         # مسارات الإدارة
```

## الاستجابات

كل المتحكمات يجب أن تستخدم `BaseApiController`:

```json
{
  "success": true,
  "message": "Created successfully.",
  "data": {},
  "pagination": {}
}
```

أخطاء التحقق:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {}
}
```

## أهم الجداول

| المجال | الجداول |
| --- | --- |
| المستخدمون | `users`, `personal_access_tokens`, `password_reset_tokens` |
| الموقع | `cities`, `places` |
| العقارات | `estates`, `estate_images`, `estate_videos`, `estate_ads` |
| الشركات والوسطاء | `companies`, `agents`, `social_links` |
| التفاعل | `favorite_estates`, `favorite_agents`, `messages`, `notifications` |
| الاستثمار | `investment_analyses`, `investment_portfolios`, `portfolio_properties`, `portfolio_items` |
| التوصيات والذكاء | `user_preferences`, `recommendations`, `property_interactions`, `price_predictions` |
| الثقة | `property_reviews`, `agent_reviews`, `company_reviews`, `verification_requests` |

## أهم الخدمات

| الخدمة | الغرض |
| --- | --- |
| `FileUploadService` | رفع الصور والفيديوهات |
| `EstateImageService` | إدارة صور العقار والصورة الرئيسية |
| `GeoSearchService` | بحث الخريطة والنطاق الجغرافي |
| `RecommendationService` | إرجاع توصيات المستخدم |
| `RecommendationGeneratorService` | توليد التوصيات |
| `RecommendationScoringService` | حساب درجة التوصية |
| `PropertyInteractionService` | تسجيل المشاهدات والتواصل والمشاركة |
| `InvestmentCalculatorService` | حساب ROI ومؤشرات الاستثمار |
| `InvestorDashboardService` | ملخص المستثمر |
| `OwnerDashboardService` | ملخص المالك |
| `PortfolioService` | المحافظ الاستثمارية |
| `PricePredictionClient` | الاتصال بخدمة Flask |
| `EstatePricePredictionService` | توقع سعر عقار موجود |
| `MarketTrendsService` | اتجاهات السوق |
| `TrustScoreService` | حساب درجة الثقة |
| `ReviewService` | منطق التقييمات |
| `VerificationRequestService` | طلبات التوثيق |
| `SocialLinkService` | مزامنة روابط التواصل |

## مسارات API

قاعدة API:

```text
/api/v1
```

قاعدة الإدارة:

```text
/api/v1/admin
```

أهم المجالات:

- `/register`, `/login`, `/logout`, `/me`
- `/companies`, `/agents`, `/cities`, `/places`, `/estates`
- `/estates/map`, `/estates/nearby`, `/estates/in-radius`
- `/my/estates` مع nested images/videos/ads
- `/favorites`, `/messages`, `/notifications`
- `/my/investment-analyses`, `/my/portfolios`, `/my/portfolio-items`
- `/price-predictions/preview`, `/estates/{estate}/price-prediction`
- `/recommendations`, `/preferences`
- `/verification-requests`, reviews وtrust-score
- `/admin/users`, `/admin/companies`, `/admin/agents`, `/admin/estates`, `/admin/trust`

## بيانات التجربة

`DatabaseSeeder` يشغل:

- `AdminSeeder`
- `LocationSeeder`
- `DemoUserSeeder`
- `CompanySeeder`
- `AgentSeeder`
- `EstateSeeder`
- `ReviewSeeder`
- `PortfolioSeeder`

كلمة المرور الافتراضية من `SEED_PASSWORD`، أو `password`.

| الدور | البريد |
| --- | --- |
| مدير | `admin@realestate.test` |
| مالك | `owner@realestate.test` |
| مستثمر/مشتري | `buyer@realestate.test` |
| شركة | `company@realestate.test` |
| وسيط | `agent@realestate.test` |

## اختبارات موجودة

توجد اختبارات وحدة وميزات لمجالات مثل:

- الروابط الاجتماعية.
- البحث الجغرافي.
- حسابات الاستثمار.
- لوحة المستثمر.
- المحافظ.
- نظام الثقة والتقييمات.
- طلبات التوثيق.

شغلها عبر:

```sh
php artisan test
```
