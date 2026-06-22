<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait QueriesAdminUsers
{
    protected function adminUsersQuery(Request $request): Builder
    {
        $query = User::query()
            ->withCount('estates');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function (Builder $q) use ($search) {
                $q->where('username', 'like', '%'.$search.'%')
                    ->orWhere('fname', 'like', '%'.$search.'%')
                    ->orWhere('lname', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->boolean('verified')) {
            $query->where('is_verified', true);
        }

        return $query->latest();
    }
}
