<?php

namespace App\Services\Admin;

use App\Models\Agent;
use App\Models\AgentReview;
use App\Models\Cities;
use App\Models\Companies;
use App\Models\CompanyReview;
use App\Models\Estate;
use App\Models\Message;
use App\Models\Notifications;
use App\Models\Places;
use App\Models\PropertyReview;
use App\Models\User;
use App\Models\VerificationRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function overview(): array
    {
        $estatesByStatus = $this->countGrouped(Estate::query(), 'status');
        $companiesByStatus = $this->countGrouped(Companies::query(), 'status');
        $usersByStatus = $this->countGrouped(User::query(), 'status');
        $usersByType = $this->countGrouped(User::query(), 'type');

        $pendingReviews = [
            'property' => PropertyReview::query()->pending()->count(),
            'agent' => AgentReview::query()->pending()->count(),
            'company' => CompanyReview::query()->pending()->count(),
        ];
        $pendingReviews['total'] = array_sum($pendingReviews);

        return [
            'totals' => [
                'users' => User::query()->count(),
                'agents' => Agent::query()->count(),
                'estates' => Estate::query()->count(),
                'companies' => Companies::query()->count(),
                'cities' => Cities::query()->count(),
                'places' => Places::query()->count(),
                'notifications' => Notifications::query()->count(),
                'unread_notifications' => Notifications::query()->where('is_read', false)->count(),
                'messages' => Message::query()->count(),
                'unread_messages' => Message::query()->where('is_read', false)->count(),
            ],
            'moderation' => [
                'estates_pending' => (int) ($estatesByStatus['pending'] ?? 0),
                'estates_active' => (int) ($estatesByStatus['active'] ?? 0),
                'estates_rejected' => (int) ($estatesByStatus['rejected'] ?? 0),
                'companies_pending' => (int) ($companiesByStatus['pending'] ?? 0),
                'reviews_pending' => $pendingReviews,
                'verifications_pending' => VerificationRequest::query()->pending()->count(),
            ],
            'estates_by_status' => $estatesByStatus,
            'companies_by_status' => $companiesByStatus,
            'users_by_type' => $usersByType,
            'users_by_status' => $usersByStatus,
            'registrations' => [
                'users_last_7_days' => $this->registrationsTrend(User::class),
                'estates_last_7_days' => $this->registrationsTrend(Estate::class),
            ],
            'recent_users' => User::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'username', 'fname', 'lname', 'email', 'type', 'status', 'created_at'])
                ->map(fn (User $user) => $this->formatRecentUser($user))
                ->values()
                ->all(),
            'recent_estates' => Estate::query()
                ->with(['user:id,username,fname,lname', 'place:id,name'])
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'status', 'price', 'user_id', 'places_id', 'created_at'])
                ->map(fn (Estate $estate) => $this->formatRecentEstate($estate))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function countGrouped($query, string $column): array
    {
        return $query
            ->select($column, DB::raw('count(*) as count'))
            ->groupBy($column)
            ->pluck('count', $column)
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array<int, array{date: string, count: int}>
     */
    private function registrationsTrend(string $modelClass, int $days = 7): array
    {
        $since = Carbon::now()->subDays($days - 1)->startOfDay();

        $counts = $modelClass::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->map(fn ($count) => (int) $count)
            ->all();

        $trend = [];

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($days - 1 - $i)->toDateString();
            $trend[] = [
                'date' => $date,
                'count' => $counts[$date] ?? 0,
            ];
        }

        return $trend;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRecentUser(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'fname' => $user->fname,
            'lname' => $user->lname,
            'email' => $user->email,
            'type' => $user->type,
            'status' => $user->status,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatRecentEstate(Estate $estate): array
    {
        return [
            'id' => $estate->id,
            'name' => $estate->name,
            'status' => $estate->status,
            'price' => $estate->price,
            'created_at' => $estate->created_at?->toIso8601String(),
            'user' => $estate->user ? [
                'id' => $estate->user->id,
                'username' => $estate->user->username,
                'fname' => $estate->user->fname,
                'lname' => $estate->user->lname,
            ] : null,
            'place' => $estate->place ? [
                'id' => $estate->place->id,
                'name' => $estate->place->name,
            ] : null,
        ];
    }
}
