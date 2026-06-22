<?php

// =============================================================================
// bootstrap/app.php — نقطة تهيئة تطبيق Laravel 11+
// =============================================================================
// هذا الملف يُنفَّذ عند كل طلب HTTP: يبني كائن Application ويربط المسارات والـ Middleware.
// في Laravel القديم كان bootstrap/app.php أبسط؛ هنا الإعدادات المركزية (Routing, Middleware, Exceptions).

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Application::configure() يبدأ سلسلة إعدادات Fluent (سلسلة استدعاءات متتابعة)
// basePath: جذر المشروع (المجلد الأب لمجلد bootstrap)
return Application::configure(basePath: dirname(__DIR__))
    // withRouting: تسجيل ملفات المسارات — كل طلب API يمر عبر routes/api.php
    ->withRouting(
        web: __DIR__.'/../routes/web.php',       // مسارات الويب (صفحات، جلسات)
        api: __DIR__.'/../routes/api.php',       // مسارات JSON للتطبيق/الموبايل
        commands: __DIR__.'/../routes/console.php', // أوامر Artisan
        health: '/up',                           // فحص صحة السيرفر (مثلاً للنشر)
    )
    // withMiddleware: تسجيل Middleware مخصص بأسماء مختصرة (alias)
    ->withMiddleware(function (Middleware $middleware): void {
        // alias يربط الاسم 'admin' بالكلاس EnsureUserIsAdmin
        // الاستخدام في routes: ->middleware(['auth:sanctum', 'admin'])
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'activity' => \App\Http\Middleware\UpdateLastActivity::class,
        ]);
    })
    // withExceptions: معالجة الأخطاء العامة (يمكن تخصيص الردود لاحقاً)
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Unauthenticated.',
                    'errors' => [],
                ], 401);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'errors' => [],
                ], 404);
            }
        });
    })->create();
