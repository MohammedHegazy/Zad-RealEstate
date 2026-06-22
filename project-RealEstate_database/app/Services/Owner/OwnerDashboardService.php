<?php

namespace App\Services\Owner;

use App\Models\Estate;
use App\Models\PropertyInteraction;
use App\Models\User;

class OwnerDashboardService
{
    public function getSummary(User $user): array
    {
        $estates = Estate::query()
            ->where('user_id', $user->id)
            ->select('id', 'name', 'status', 'price', 'created_at')
            ->latest()
            ->get();

        $totalEstates = $estates->count();
        $estatesByStatus = $estates->groupBy('status')->map->count();
        $recentEstates = $estates->take(5)->values()->toArray();

        $interactions = PropertyInteraction::query()
            ->whereIn('estate_id', $estates->pluck('id'))
            ->with(['estate:id,name', 'user:id,fname,lname'])
            ->latest()
            ->take(10)
            ->get()
            ->toArray();

        return [
            'total_estates' => $totalEstates,
            'estates_by_status' => $estatesByStatus,
            'active_estates' => $estatesByStatus['active'] ?? 0,
            'pending_estates' => $estatesByStatus['pending'] ?? 0,
            'rejected_estates' => $estatesByStatus['rejected'] ?? 0,
            'recent_estates' => $recentEstates,
            'recent_interactions' => $interactions,
        ];
    }
}
