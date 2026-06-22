<?php

namespace Database\Seeders;

use App\Models\Companies;
use App\Models\Places;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->where('email', 'company@realestate.test')->first();

        if (! $owner) {
            $this->command?->warn('CompanySeeder skipped: demo company user not found. Run DemoUserSeeder first.');

            return;
        }

        $place = Places::query()->where('name', 'Malki')->first()
            ?? Places::query()->first();

        if (! $place) {
            $this->command?->warn('CompanySeeder skipped: no places found. Run LocationSeeder first.');

            return;
        }

        $company = Companies::query()->updateOrCreate(
            ['user_id' => $owner->id],
            [
                'places_id' => $place->id,
                'company_name' => 'Acme Realty',
                'website' => 'https://acme-realty.example',
                'employees_num' => 12,
                'description' => 'Full-service brokerage covering sales, rentals, and investment advisory.',
                'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                'status' => 'approved',
                'profile_image' => null,
                'banner_image' => null,
                'trust_score' => 0,
            ],
        );

        $company->socialLinks()->updateOrCreate(
            ['platform' => 'facebook'],
            ['url' => 'https://facebook.com/acme-realty'],
        );

        $company->socialLinks()->updateOrCreate(
            ['platform' => 'website'],
            ['url' => 'https://acme-realty.example'],
        );
    }
}
