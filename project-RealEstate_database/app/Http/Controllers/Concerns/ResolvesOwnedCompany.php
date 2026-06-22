<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Companies;
use Illuminate\Http\Request;

trait ResolvesOwnedCompany
{
    protected function ownedCompany(Request $request): ?Companies
    {
        return $request->user()->company;
    }
}
