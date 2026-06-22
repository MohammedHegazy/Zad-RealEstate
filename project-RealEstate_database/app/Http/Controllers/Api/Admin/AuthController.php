<?php
/*
|--------------------------------------------------------------------------
| AuthController — تسجيل دخول وخروج المدير (لوحة التحكم)
|--------------------------------------------------------------------------
| المسار في API: عادةً /api/admin/login و /api/admin/me و /api/admin/logout
|
| ماذا يفعل هذا الملف؟
| - يتحقق من بريد المدير وكلمة المرور
| - يصدر Token (Sanctum) للوحة الإدارة
| - يعرض بيانات المدير الحالي ويسمح بتسجيل الخروج
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials.', 401);
        }

        if (! $user->isAdmin()) {
            return $this->errorResponse('Access denied. Admin account required.', 403);
        }

        if ($user->status !== 'active') {
            return $this->errorResponse('Account is not active.', 403);
        }

        $token = $user->createToken(
            $request->input('device_name', 'admin-panel')
        )->plainTextToken;

        return $this->successResponse(
            [
                'user' => $user->load('socialLinks'),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
            'Logged in successfully.',
        );
    }

    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(
            $request->user()->load('socialLinks'),
            'Authenticated admin profile.',
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        $request->user()->updateQuietly(['last_activity_at' => null]);

        return $this->deletedResponse('Logged out successfully.');
    }
}
