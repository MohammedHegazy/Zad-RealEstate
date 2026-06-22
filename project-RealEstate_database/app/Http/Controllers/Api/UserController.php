<?php
/*
|--------------------------------------------------------------------------
| UserController — الملف الشخصي والبيانات المرتبطة بالمستخدم
|--------------------------------------------------------------------------
|
| الغرض:
| - عرض/تحديث حساب المستخدم المسجّل
| - قوائم عقاراته وتحليلات الاستثمار المحفوظة
| - ملف عام محدود لمستخدم آخر
|
| الارتباطات:
| - UpdateProfileRequest
| - علاقات User: estates, investmentAnalyses, socialLinks...
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Models\User;
use App\Services\SocialLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    public function __construct(
        private readonly SocialLinkService $socialLinks,
    ) {}

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();//
        $includes = $this->parseIncludes($request->query('include', ''));//

        if ($includes !== []) { //
            $user->load($includes);//
        } else {
            $user->load('socialLinks');
        }

        return $this->successResponse(//
            $this->formatUser($user),
            'Profile retrieved.',
        );
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['password_confirmation', 'facebook', 'instagram']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        if ($request->has('facebook') || $request->has('instagram') || $request->has('links')) {
            $this->socialLinks->syncFromRequest(
                $user,
                $request->only(['facebook', 'instagram', 'links']),
                replace: true,
            );
        }

        return $this->successResponse(
            $this->formatUser($user->fresh()->load('socialLinks')),
            'Profile updated successfully.',
        );
    }

    public function myEstates(Request $request): JsonResponse
    {
        $estates = $request->user()
            ->estates()
            ->with(['place.city', 'images'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $estates->items(),
            'Your estates retrieved.',
            200,
            $this->paginationMeta($estates),
        );
    }

    public function myInvestmentAnalyses(Request $request): JsonResponse
    {
        $analyses = $request->user()
            ->investmentAnalyses()
            ->with('estate:id,name,price,status')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse(
            $analyses->items(),
            'Investment analyses retrieved.',
            200,
            $this->paginationMeta($analyses),
        );
    }

    public function show(User $user): JsonResponse
    {
        return $this->successResponse(
            $user->only(['id', 'username', 'fname', 'lname', 'type', 'created_at']),
            'User profile retrieved.',
        );
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required_without:phone', 'string', 'max:255'],
            'phone' => ['required_without:email', 'string', 'max:50'],
        ]);

        $query = User::query()->where('status', 'active');

        if ($request->filled('email')) {
            $query->where('email', 'like', '%'.$request->email.'%');
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%'.$request->phone.'%');
        }

        $users = $query->limit(10)->get(['id', 'username', 'fname', 'lname', 'email', 'phone', 'type']);

        return $this->successResponse($users, 'Users retrieved.');
    }

    private function parseIncludes(string $include): array
    {
        $allowed = [
            'socialLinks',
            'estates',
            'companies',
            'agent',
            'inAppNotifications',
            'favoriteEstates',
            'favoriteAgents',
            'investmentAnalyses',
            'sentMessages',
            'receivedMessages',
        ];

        $requested = array_filter(array_map('trim', explode(',', $include)));

        return array_values(array_intersect($requested, $allowed));
    }

    private function formatUser(User $user): array
    {
        $data = $user->toArray();
        unset($data['password'], $data['remember_token']);

        return $data;
    }
}
