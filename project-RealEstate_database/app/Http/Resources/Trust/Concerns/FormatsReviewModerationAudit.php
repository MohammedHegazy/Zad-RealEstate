<?php

namespace App\Http\Resources\Trust\Concerns;

use Illuminate\Http\Request;

trait FormatsReviewModerationAudit
{
    /**
     * @return array<string, mixed>
     */
    protected function moderationAuditFields(Request $request): array
    {
        return [
            'admin_notes' => $this->when(
                $request->user()?->isAdmin(),
                $this->admin_notes,
            ),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewer' => $this->when(
                $request->user()?->isAdmin() && $this->relationLoaded('reviewer') && $this->reviewer,
                fn () => [
                    'id' => $this->reviewer->id,
                    'username' => $this->reviewer->username,
                    'fname' => $this->reviewer->fname,
                    'lname' => $this->reviewer->lname,
                ],
            ),
        ];
    }
}
