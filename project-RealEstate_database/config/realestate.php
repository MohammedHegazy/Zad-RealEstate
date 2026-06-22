<?php

// =============================================================================
// config/realestate.php — إعدادات خاصة بمنصة العقارات (ليست إعدادات Laravel العامة)
// =============================================================================
// تُقرأ عبر config('realestate.xxx') من Controllers و Middleware و Requests.
// الفصل هنا يجعل تغيير قواعد العمل (حالات العقار، أنواع المستخدمين) بدون تعديل الكود.

return [

    // أنواع المستخدمين الذين يُعتبرون "مديراً" — يُستخدم في EnsureUserIsAdmin
    // env('ADMIN_USER_TYPES'): من .env مثل "admin,super" → مصفوفة بعد explode
    'admin_types' => array_filter(explode(',', env('ADMIN_USER_TYPES', 'admin'))),

    // حالات سجل العقار في جدول estates — دورة الموافقة: pending → active أو rejected
    'estate_statuses' => ['pending', 'active', 'rejected'],

    // حالات الشركة — دورة الموافقة: pending → approved | rejected | suspended
    'company_statuses' => ['pending', 'approved', 'rejected', 'suspended'],

    // أيام الأسبوع المسموحة في work_days (JSON array على companies)
    'work_days' => [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
    ],

    // حالات عنصر المحفظة الاستثمارية (تتبع → استثمار → بيع)
    'portfolio_item_statuses' => ['tracking', 'invested', 'sold'],

    // حالات محفظة الاستثمار
    'portfolio_statuses' => ['active', 'archived', 'closed'],

    // مستويات المخاطرة للمحفظة
    'portfolio_risk_levels' => ['low', 'moderate', 'high'],

    // اسم المحفظة الافتراضية عند أول استخدام لـ PortfolioService
    'default_portfolio_name' => env('DEFAULT_PORTFOLIO_NAME', 'My Portfolio'),

    // حالات حساب المستخدم (تفعيل/تعليق)
    'user_statuses' => ['active', 'inactive', 'suspended'],

    // أدوار المنصة: admin, agent, owner, buyer, company — تُخزَّن في users.user_type
    'user_types' => ['admin', 'agent', 'owner', 'buyer', 'company'],

    // حدود رفع الوسائط — FileUploadService يقرأ max_image_kb و allowed_mimes
    'upload' => [
        'max_image_kb' => (int) env('UPLOAD_MAX_IMAGE_KB', 5120),   // افتراضي 5 ميجا للصور
        'max_video_kb' => (int) env('UPLOAD_MAX_VIDEO_KB', 51200), // افتراضي ~50 ميجا للفيديو
        'allowed_mimes' => ['jpeg', 'jpg', 'png', 'webp'],
    ],

    // منصات التواصل المسموحة في جدول social_links (علاقة polymorphic)
    // إعدادات افتراضية لـ Leaflet + OpenStreetMap (الواجهة Vue تقرأها من API)
    'map' => [
        'default_lat' => (float) env('MAP_DEFAULT_LAT', 33.5138),
        'default_lng' => (float) env('MAP_DEFAULT_LNG', 36.2765),
        'default_zoom' => (int) env('MAP_DEFAULT_ZOOM', 12),
        'tile_url' => env('MAP_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'attribution' => env(
            'MAP_TILE_ATTRIBUTION',
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        ),
    ],

    'social_platforms' => [
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'youtube',
        'tiktok',
        'whatsapp',
        'website',
    ],

    // Smart recommendation engine
    'recommendation_limit' => (int) env('RECOMMENDATION_LIMIT', 30),
    'recommendation_min_score' => (float) env('RECOMMENDATION_MIN_SCORE', 40),
    'recommendation_candidate_pool' => (int) env('RECOMMENDATION_CANDIDATE_POOL', 150),
    'recommendation_stale_hours' => (int) env('RECOMMENDATION_STALE_HOURS', 24),
    'similar_estates_pool' => (int) env('SIMILAR_ESTATES_POOL', 50),

    // Geolocation & map search
    'geo' => [
        'default_nearby_radius_km' => (float) env('GEO_DEFAULT_NEARBY_RADIUS_KM', 25),
        'max_radius_km' => (float) env('GEO_MAX_RADIUS_KM', 100),
        'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
        'osm_tile_url' => env('GEO_OSM_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
        'osm_attribution' => env(
            'GEO_OSM_ATTRIBUTION',
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        ),
    ],

    // Trust & credibility
    'review_statuses' => ['pending', 'approved', 'rejected'],
    'verification_statuses' => ['pending', 'approved', 'rejected'],
    'verification_document_types' => ['national_id', 'passport', 'business_license', 'other'],
    'trust_score' => [
        'weights' => [
            'verified_account' => 25,
            'approved_properties' => 25,
            'average_rating' => 25,
            'review_count' => 15,
            'platform_activity' => 10,
        ],
        'max_approved_properties' => 20,
        'max_review_count' => 50,
        'max_activity_score' => 1000,
    ],

];
