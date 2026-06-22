<?php

namespace App\Http\Middleware;

// =============================================================================
// Middleware (وسيط): يُنفَّذ قبل الوصول إلى Controller
// =============================================================================
// EnsureUserIsAdmin يتحقق أن المستخدم مسجّل دخوله ونوعه "مدير" (admin).
// يُسجَّل في bootstrap/app.php باسم alias: 'admin'

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * handle: نقطة الدخول لكل Middleware.
     *
     * المدخلات:
     * - Request $request: الطلب الحالي (يحمل التوكن، المستخدم، البيانات)
     * - Closure $next: الدالة التالية في السلسلة (Controller أو middleware آخر)
     *
     * المخرجات: Response — إما JSON 403 أو تمرير الطلب للأمام عبر $next($request)
     */
    public function handle(Request $request, Closure $next): Response
    {
        // user() يعيد المستخدم من توكن Sanctum إن وُجد (بعد auth:sanctum)
        $user = $request->user();

        // الخطوة 1: هل يوجد مستخدم؟ الخطوة 2: هل isAdmin() true؟
        if (! $user || ! $user->isAdmin()) {
            // إيقاف السلسلة وإرجاع 403 Forbidden دون تنفيذ Controller
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        // المستخدم مدير — متابعة التنفيذ إلى Controller
        return $next($request);
    }
}
