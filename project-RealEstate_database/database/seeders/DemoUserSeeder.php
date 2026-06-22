<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoPassword;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    use SeedsDemoPassword;

    public function run(): void
    {
        $this->upsertUser(
            email: 'owner@realestate.test',
            username: 'property_owner',
            fname: 'Omar',
            lname: 'Haddad',
            type: 'owner',
        );

        $this->upsertUser(
            email: 'buyer@realestate.test',
            username: 'investor_buyer',
            fname: 'Layla',
            lname: 'Khalil',
            type: 'buyer',
        );

        $this->upsertUser(
            email: 'company@realestate.test',
            username: 'acme_realty',
            fname: 'Samir',
            lname: 'Nasser',
            type: 'company',
        );

        $this->upsertUser(
            email: 'agent@realestate.test',
            username: 'field_agent',
            fname: 'Rami',
            lname: 'Saleh',
            type: 'agent',
        );
    }

    private function upsertUser(
        string $email,
        string $username,
        string $fname,
        string $lname,
        string $type,
    ): User {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'username' => $username,
                'fname' => $fname,
                'lname' => $lname,
                'status' => 'active',
                'type' => $type,
                'is_verified' => false,
                'password' => $this->demoPassword(),
                'phone' => fake()->numerify('##########'),
                'countre_code_phone' => '+963',
                'gender' => fake()->randomElement(['male', 'female']),
            ],
        );
    }
}
