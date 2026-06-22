<?php

namespace App\Http\Resources\Trust;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\VerificationRequest */
class VerificationRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'document_path' => $this->when($request->user()?->isAdmin(), $this->document_path),
            'has_document' => $this->when(
                $request->user()?->isAdmin(),
                fn () => app(\App\Services\Trust\VerificationRequestService::class)->documentExists($this->resource),
            ),
            'status' => $this->status,
            'admin_notes' => $this->when(
                $request->user()?->isAdmin() || $request->user()?->id === $this->user_id,
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
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'fname' => $this->user->fname,
                'lname' => $this->user->lname,
                'is_verified' => (bool) $this->user->is_verified,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
