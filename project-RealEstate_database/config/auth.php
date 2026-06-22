<?php

// =============================================================================
// config/auth.php — المصادقة (Guards و Providers)
// =============================================================================
// Guard = طريقة التحقق (session للويب). Provider = من أين يُجلب المستخدم (Eloquent).

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | الإعدادات الافتراضية للمصادقة
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        // guard الافتراضي عند استدعاء auth() بدون تحديد
        'guard' => env('AUTH_GUARD', 'web'),
        // broker لاستعادة كلمة المرور
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards — طبقات المصادقة
    |--------------------------------------------------------------------------
    | session = كوكيز + جلسة (لوحة ويب). API يستخدم Sanctum منفصل.
    */

    'guards' => [
        'web' => [
            'driver' => 'session', // يعتمد على Session
            'provider' => 'users', // يجلب User من providers.users
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers — مصدر بيانات المستخدم
    |--------------------------------------------------------------------------
    | eloquent = Model User | database = جدول users مباشرة
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class), // موديل المستخدم
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | استعادة كلمة المرور
    |--------------------------------------------------------------------------
    | expire = دقائق صلاحية الرابط | throttle = ثوانٍ بين طلبات جديدة
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60, // الرمز صالح 60 دقيقة
            'throttle' => 60, // انتظار 60 ثانية بين المحاولات
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
