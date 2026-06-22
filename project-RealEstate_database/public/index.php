<?php

// =============================================================================
// public/index.php — البوابة الحقيقية لكل طلب HTTP يصل إلى Laravel
// =============================================================================
// عندما يفتح المستخدم أو التطبيق رابطاً مثل http://localhost:8000/api/v1/estates
// خادم الويب (Apache/Nginx أو `php artisan serve`) يوجّه الطلب إلى هذا الملف أولاً.
// هذا الملف لا يحتوي منطق أعمال؛ وظيفته: تحميل Laravel وتسليم الطلب للإطار.

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// LARAVEL_START: وقت بداية التنفيذ — يُستخدم لاحقاً لقياس أداء الاستجابة (debugbar/profiling)
define('LARAVEL_START', microtime(true));

// وضع الصيانة: إذا وُجد ملف maintenance.php (مثلاً بعد php artisan down)
// يُعرض للزوار صفحة صيانة بدلاً من تشغيل التطبيق الكامل
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// autoload: تحميل كل كلاسات Composer (Laravel، موديلاتك، controllers...)
require __DIR__.'/../vendor/autoload.php';

// bootstrap/app.php يُرجع كائن Application مُهيّأ (مسارات، middleware، إلخ)
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Request::capture(): يقرأ Method, URL, Headers, Body من الطلب الحالي
// handleRequest: يمرّ عبر الـ Kernel → Middleware → Router → Controller → Response
$app->handleRequest(Request::capture());
