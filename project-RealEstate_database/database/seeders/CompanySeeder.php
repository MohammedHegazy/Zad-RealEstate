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
        $companyUsers = User::query()->where('type', 'company')->orderBy('id')->get();

        if ($companyUsers->isEmpty()) {
            $this->command?->warn('CompanySeeder skipped: no company users found. Run DemoUserSeeder first.');
            return;
        }

        $companies = [
            [
                'user_email' => 'company@realestate.test',
                'company_name' => 'أكاديمية العقارية',
                'website' => 'https://acme-realty.example',
                'place_name' => 'المالكي',
                'employees_num' => 12,
                'description' => 'شركة وساطة عقارية متكاملة تقدم خدمات البيع والشراء والتأجير والاستشارات الاستثمارية.',
            ],
            [
                'user_email' => 'company2@realestate.test',
                'company_name' => 'شام القابضة',
                'website' => 'https://sham-holdings.example',
                'place_name' => 'المزة',
                'employees_num' => 25,
                'description' => 'مجموعة استثمارية عقارية رائدة في دمشق وحلب، متخصصة في المشاريع السكنية والتجارية.',
            ],
            [
                'user_email' => 'company3@realestate.test',
                'company_name' => 'المشرق للعقارات',
                'website' => 'https://levant-props.example',
                'place_name' => 'الرمل الجنوبي',
                'employees_num' => 18,
                'description' => 'شركة متخصصة في التطوير العقاري وإدارة الأملاك في المنطقة الساحلية.',
            ],
            [
                'user_email' => 'company4@realestate.test',
                'company_name' => 'المجموعة الشرقية',
                'website' => 'https://orient-group.example',
                'place_name' => 'العزيزية',
                'employees_num' => 30,
                'description' => 'شركة عقارية تعمل في عدة محافظات سورية مع تركيز على المشاريع الاستثمارية الكبرى.',
            ],
        ];

        foreach ($companies as $data) {
            $user = $companyUsers->firstWhere('email', $data['user_email']);
            if (!$user) {
                continue;
            }

            $place = Places::query()->where('name', $data['place_name'])->first()
                ?? Places::query()->inRandomOrder()->first();

            if (!$place) {
                $this->command?->warn('CompanySeeder skipped: no places found. Run LocationSeeder first.');
                return;
            }

            $company = Companies::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'places_id' => $place->id,
                    'company_name' => $data['company_name'],
                    'website' => $data['website'],
                    'employees_num' => $data['employees_num'],
                    'description' => $data['description'],
                    'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                    'status' => 'approved',
                    'profile_image' => null,
                    'banner_image' => null,
                    'trust_score' => 0,
                ],
            );

            if ($data['user_email'] === 'company@realestate.test') {
                $company->socialLinks()->updateOrCreate(
                    ['platform' => 'facebook'],
                    ['url' => 'https://facebook.com/acme-realty'],
                );
                $company->socialLinks()->updateOrCreate(
                    ['platform' => 'website'],
                    ['url' => $data['website']],
                );
            }
        }
    }
}
