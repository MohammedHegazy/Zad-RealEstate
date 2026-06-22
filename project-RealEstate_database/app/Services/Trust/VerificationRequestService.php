<?php

namespace App\Services\Trust;

use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VerificationRequestService
{
    public function __construct(
        private readonly TrustScoreService $trustScore,
    ) {}

    public function submit(User $user, array $data, ?UploadedFile $document = null): VerificationRequest
    {
        $path = $document
            ? $document->store('verification-documents/'.$user->id, 'local')
            : $data['document_path'];

        return VerificationRequest::create([
            'user_id' => $user->id,
            'document_type' => $data['document_type'],
            'document_path' => $path,
            'status' => 'pending',
        ]);
    }

    public function listForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return VerificationRequest::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function pending(int $perPage = 15): LengthAwarePaginator
    {
        return $this->adminList(['status' => 'pending'], $perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function adminList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = VerificationRequest::query()
            ->with([
                'user:id,username,fname,lname,email,type,is_verified',
                'reviewer:id,username,fname,lname',
            ]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('document_type', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('fname', 'like', "%{$search}%")
                            ->orWhere('lname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function adminShow(int $id): VerificationRequest
    {
        return VerificationRequest::query()
            ->with(['user', 'reviewer:id,username,fname,lname'])
            ->findOrFail($id);
    }

    public function approve(VerificationRequest $request, User $reviewer, ?string $adminNotes = null): VerificationRequest
    {
        $request->update([
            'status' => 'approved',
            'admin_notes' => $adminNotes,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
        ]);

        $user = $request->user;
        $user->update(['is_verified' => true]);
        $this->trustScore->recalculateForUser($user->fresh());

        return $request->fresh();
    }

    public function reject(VerificationRequest $request, User $reviewer, ?string $adminNotes = null): VerificationRequest
    {
        $request->update([
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
        ]);

        return $request->fresh();
    }

    public function documentExists(VerificationRequest $request): bool
    {
        return Storage::disk('local')->exists($request->document_path);
    }

    public function documentAbsolutePath(VerificationRequest $request): ?string
    {
        if (! $this->documentExists($request)) {
            return null;
        }

        return Storage::disk('local')->path($request->document_path);
    }

    public function documentMimeType(VerificationRequest $request): string
    {
        $path = $this->documentAbsolutePath($request);

        if (! $path) {
            return 'application/octet-stream';
        }

        return mime_content_type($path) ?: 'application/octet-stream';
    }

    public function documentFilename(VerificationRequest $request): string
    {
        return basename($request->document_path);
    }
}

