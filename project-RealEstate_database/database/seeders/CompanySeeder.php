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
        $places = Places::query()->inRandomOrder()->get();

        if ($companyUsers->isEmpty() || $places->isEmpty()) {
            $this->command?->warn('CompanySeeder skipped: company users or places not found.');
            return;
        }

        $arabicNames = [
            'أكاديمية العقارية',
            'شام القابضة',
            'المشرق للعقارات',
            'المجموعة الشرقية',
            'رواد الشام العقارية',
            'بيت الخبرة العقاري',
            'الفرسان للعقارات',
            'Golden Tower العقارية',
        ];

        $descriptions = [
            'شركة وساطة عقارية متكاملة تقدم خدمات البيع والشراء والتأجير والاستشارات الاستثمارية.',
            'مجموعة استثمارية عقارية رائدة في دمشق وحلب، متخصصة في المشاريع السكنية والتجارية.',
            'شركة متخصصة في التطوير العقاري وإدارة الأملاك في المنطقة الساحلية.',
            'شركة عقارية تعمل في عدة محافظات سورية مع تركيز على المشاريع الاستثمارية الكبرى.',
            'وكالة عقارية محترفة تقدم حلولاً مبتكرة في مجال التسويق العقاري وإدارة الممتلكات.',
            'شركة رائدة في مجال الاستشارات العقارية والتقييم وإدارة المشاريع السكنية والتجارية.',
            'مجموعة عقارية متكاملة تغطي جميع المحافظات السورية بخدمات بيع وشراء وتأجير.',
            'شركة استثمار عقاري فاخر متخصصة في المشاريع السكنية الراقية والمجمعات التجارية.',
        ];

        foreach ($companyUsers as $i => $user) {
            $nameIdx = $i % count($arabicNames);
            $place = $places->get($i % $places->count());

            Companies::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'places_id' => $place->id,
                    'company_name' => $arabicNames[$nameIdx],
                    'website' => 'https://company' . ($i + 1) . '.example',
                    'employees_num' => fake()->numberBetween(8, 40),
                    'description' => $descriptions[$nameIdx],
                    'work_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
                    'status' => 'approved',
                    'profile_image' => null,
                    'banner_image' => null,
                    'trust_score' => 0,
                ],
            );
        }
    }
}
