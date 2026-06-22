<?php

namespace App\Traits;

use App\Models\User;

trait FormatsAdminUserResponse
{
    /**
     * @return array<string, mixed>
     */
    protected function formatAdminUser(User $user, bool $detailed = false): array
    {
        $data = $user->makeHidden(['password', 'remember_token'])->toArray();

        if (isset($user->estates_count)) {
            $data['estates_count'] = (int) $user->estates_count;
        }

        if ($detailed) {
            $data['counts'] = [
                'estates' => (int) ($user->estates_count ?? $user->estates()->count()),
                'favorite_estates' => $user->favoriteEstates()->count(),
                'favorite_agents' => $user->favoriteAgents()->count(),
                'property_reviews' => $user->propertyReviews()->count(),
                'notifications' => $user->inAppNotifications()->count(),
                'sent_messages' => $user->sentMessages()->count(),
                'received_messages' => $user->receivedMessages()->count(),
            ];

            if ($user->relationLoaded('agent') && $user->agent) {
                $data['agent'] = [
                    'id' => $user->agent->id,
                    'companies_id' => $user->agent->companies_id,
                    'trust_score' => $user->agent->trust_score,
                ];
            }

            if ($user->relationLoaded('company') && $user->company) {
                $data['company'] = [
                    'id' => $user->company->id,
                    'company_name' => $user->company->company_name,
                    'status' => $user->company->status,
                ];
            }
        }

        return $data;
    }
}
