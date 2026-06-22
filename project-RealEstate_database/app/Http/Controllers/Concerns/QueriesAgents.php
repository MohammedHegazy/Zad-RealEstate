<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait QueriesAgents
{
    protected function agentsQuery(Request $request): Builder
    {
        $query = Agent::query()
            ->with(['user:id,username,fname,lname,email,phone,type', 'company:id,company_name,places_id'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews');

        if ($request->filled('companies_id')) {
            $query->where('companies_id', $request->integer('companies_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('user', function (Builder $userQuery) use ($search) {
                    $userQuery->where('username', 'like', '%'.$search.'%')
                        ->orWhere('fname', 'like', '%'.$search.'%')
                        ->orWhere('lname', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })->orWhereHas('company', function (Builder $companyQuery) use ($search) {
                    $companyQuery->where('company_name', 'like', '%'.$search.'%');
                });
            });
        }

        return $query->latest();
    }
}
