<?php

namespace App\Http\Resources\Trust;

use App\Http\Resources\Trust\Concerns\FormatsReviewModerationAudit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\AgentReview */
class AgentReviewResource extends JsonResource
{
    use FormatsReviewModerationAudit;
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'agent_id' => $this->agent_id,
            'rating' => $this->rating,
            'review' => $this->review,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'fname' => $this->user->fname,
                'lname' => $this->user->lname,
            ]),
            'agent' => $this->whenLoaded('agent', fn () => [
                'id' => $this->agent->id,
                'user' => $this->agent->relationLoaded('user') && $this->agent->user
                    ? $this->agent->user->only(['id', 'username', 'fname', 'lname'])
                    : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            ...$this->moderationAuditFields($request),
        ];
    }
}
