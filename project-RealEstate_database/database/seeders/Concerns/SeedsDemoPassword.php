<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\Hash;

trait SeedsDemoPassword
{
    protected function demoPassword(): string
    {
        return Hash::make(env('SEED_PASSWORD', 'password'));
    }
}
