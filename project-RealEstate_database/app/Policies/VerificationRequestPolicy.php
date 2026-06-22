<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VerificationRequest;

class VerificationRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, VerificationRequest $request): bool
    {
        return $user->id === $request->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
