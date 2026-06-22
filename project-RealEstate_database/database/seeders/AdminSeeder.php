<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoPassword;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    use SeedsDemoPassword;

    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@realestate.test')],
            [
                'username' => env('ADMIN_USERNAME', 'admin'),
                'fname' => 'System',
                'lname' => 'Administrator',
                'status' => 'active',
                'type' => 'admin',
                'is_verified' => true,
                'password' => $this->demoPassword(),
                'phone' => '0000000000',
                'countre_code_phone' => '+963',
                'gender' => 'other',
            ],
        );

        $user->socialLinks()->updateOrCreate(
            ['platform' => 'facebook'],
            ['url' => 'https://facebook.com/realestate-admin'],
        );

        $user->socialLinks()->updateOrCreate(
            ['platform' => 'instagram'],
            ['url' => 'https://instagram.com/realestate-admin'],
        );
    }
}
