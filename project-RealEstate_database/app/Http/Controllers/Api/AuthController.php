<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use App\Services\SocialLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    public function __construct(//اجراء التهيئة للتحكم في الروابط الاجتماعية
        private readonly SocialLinkService $socialLinks,//لجلب بيانات الروابط الاجتماعية
    ) {}

    public function register(RegisterUserRequest $request): JsonResponse//تسجيل مستخدم جديد  POST/REGISTER
    {
        $user = User::create([//انشاء المستخدم في قاعدة البيانات
            'username' => $request->username,//اسم المستخدم
            'fname' => $request->fname,//الاسم الاول
            'lname' => $request->lname,//الاسم الثاني
            'email' => $request->email,//البريد الالكتروني
            'password' => $request->password,//كلمة المرور
            'phone' => $request->phone,//الهاتف
            'countre_code_phone' => $request->countre_code_phone,//رمز الدولة للهاتف
            'gender' => $request->gender,//الجنس
            'type' => $request->type,//النوع
            'status' => 'active',//الحالة
        ]);

        $this->socialLinks->syncLegacyFields($user, $request->only(['facebook', 'instagram']));//مزامنة الروابط الاجتماعية

        $token = $user->createToken($request->input('device_name', 'mobile-app'))->plainTextToken;//إنشاء رمز الوصول

        return $this->createdResponse([
            'user' => $user->load('socialLinks'),//عرض البيانات بنجاح
            'token' => $token,//رمز الوصول  
            'token_type' => 'Bearer',//نوع رمز الوصول
        ], 'Account created successfully.');
    }

    public function login(LoginRequest $request): JsonResponse//تسجيل الدخول  POST/LOGIN
    {
        $user = User::where('email', $request->email)->first();//البحث عن المستخدم في قاعدة البيانات

        if (! $user || ! Hash::check($request->password, $user->password)) {//التحقق من البريد الالكتروني وكلمة المرور  
            return $this->errorResponse('Invalid credentials.', 401);//رسالة الخطأ
        }

        if ($user->status !== 'active') {
            return $this->errorResponse('Account is not active.', 403);//رسالة الخطأ    
        }

        $token = $user->createToken(
            $request->input('device_name', 'mobile-app')
        )->plainTextToken;//إنشاء رمز الوصول

        return $this->successResponse([
            'user' => $user->load('socialLinks'),//عرض البيانات بنجاح
            'token' => $token,//رمز الوصول
            'token_type' => 'Bearer',//نوع رمز الوصول
        ], 'Logged in successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        $request->user()->updateQuietly(['last_activity_at' => null]);

        return $this->successResponse(null, 'Logged out successfully.');
    }
}
