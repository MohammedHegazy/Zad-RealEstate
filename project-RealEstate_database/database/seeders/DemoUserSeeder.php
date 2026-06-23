<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoPassword;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    use SeedsDemoPassword;

    private array $created = [];

    public function run(): void
    {
        $this->created['owners'] = [];
        $this->created['buyers'] = [];
        $this->created['companies'] = [];
        $this->created['agents'] = [];

        // 7 owners
        $this->created['owners'][] = $this->upsertUser(
            email: 'owner@realestate.test',
            username: 'property_owner',
            fname: 'Omar',
            lname: 'Haddad',
            type: 'owner',
        );
        $this->created['owners'][] = $this->upsertUser(
            email: 'owner2@realestate.test',
            username: 'noor_estates',
            fname: 'Noor',
            lname: 'Al-Din',
            type: 'owner',
        );
        $this->created['owners'][] = $this->upsertUser(
            email: 'owner3@realestate.test',
            username: 'ward_buildings',
            fname: 'Ward',
            lname: 'Jaber',
            type: 'owner',
        );
        $this->created['owners'][] = $this->upsertUser(
            email: 'owner4@realestate.test',
            username: 'basel_invest',
            fname: 'Basel',
            lname: 'Khalaf',
            type: 'owner',
        );
        $this->created['owners'][] = $this->upsertUser(
            email: 'owner5@realestate.test',
            username: 'diala_props',
            fname: 'Diala',
            lname: 'Mousa',
            type: 'owner',
        );
        $this->created['owners'][] = $this->upsertUser(
            email: 'owner6@realestate.test',
            username: 'samer_realty',
            fname: 'Samer',
            lname: 'Ayoub',
            type: 'owner',
        );
        $this->created['owners'][] = $this->upsertUser(
            email: 'owner7@realestate.test',
            username: 'lina_holdings',
            fname: 'Lina',
            lname: 'Sulaiman',
            type: 'owner',
        );

        // 6 buyers/investors
        $buyerEmails = [
            ['buyer@realestate.test', 'investor_buyer', 'Layla', 'Khalil'],
            ['buyer2@realestate.test', 'hisham_invest', 'Hisham', 'Qassem'],
            ['buyer3@realestate.test', 'ranim_capital', 'Ranim', 'Fares'],
            ['buyer4@realestate.test', 'khaled_vc', 'Khaled', 'Shahin'],
            ['buyer5@realestate.test', 'mariam_trust', 'Mariam', 'Akkad'],
            ['buyer6@realestate.test', 'tamer_group', 'Tamer', 'Rashid'],
        ];
        foreach ($buyerEmails as [$email, $username, $fname, $lname]) {
            $this->created['buyers'][] = $this->upsertUser(
                email: $email,
                username: $username,
                fname: $fname,
                lname: $lname,
                type: 'buyer',
            );
        }

        // 4 company-owners
        $this->created['companies'][] = $this->upsertUser(
            email: 'company@realestate.test',
            username: 'acme_realty',
            fname: 'Samir',
            lname: 'Nasser',
            type: 'company',
        );
        $this->created['companies'][] = $this->upsertUser(
            email: 'company2@realestate.test',
            username: 'sham_holdings',
            fname: 'Fadi',
            lname: 'Mardini',
            type: 'company',
        );
        $this->created['companies'][] = $this->upsertUser(
            email: 'company3@realestate.test',
            username: 'levant_props',
            fname: 'Rana',
            lname: 'Kattan',
            type: 'company',
        );
        $this->created['companies'][] = $this->upsertUser(
            email: 'company4@realestate.test',
            username: 'orient_group',
            fname: 'Maher',
            lname: 'Barakat',
            type: 'company',
        );

        // 20 agents (keep existing Rami Saleh + 19 new)
        $agentUsers = [
            ['agent@realestate.test', 'field_agent', 'Rami', 'Saleh'],
            ['agent2@realestate.test', 'sara_realty', 'Sara', 'Al-Ali'],
            ['agent3@realestate.test', 'khaled_agent', 'Khaled', 'Suleiman'],
            ['agent4@realestate.test', 'nadia_homes', 'Nadia', 'Youssef'],
            ['agent5@realestate.test', 'wassim_estate', 'Wassim', 'Hajjar'],
            ['agent6@realestate.test', 'amani_props', 'Amani', 'Saad'],
            ['agent7@realestate.test', 'mohsen_group', 'Mohsen', 'Khatib'],
            ['agent8@realestate.test', 'rasha_realty', 'Rasha', 'Diab'],
            ['agent9@realestate.test', 'husam_estate', 'Husam', 'Nassif'],
            ['agent10@realestate.test', 'lama_homes', 'Lama', 'Rifai'],
            ['agent11@realestate.test', 'yamen_group', 'Yamen', 'Ahmad'],
            ['agent12@realestate.test', 'dina_allies', 'Dina', 'Kassab'],
            ['agent13@realestate.test', 'gassan_realty', 'Gassan', 'Mourad'],
            ['agent14@realestate.test', 'hala_props', 'Hala', 'Shawky'],
            ['agent15@realestate.test', 'nadim_estate', 'Nadim', 'Fakhoury'],
            ['agent16@realestate.test', 'farah_holdings', 'Farah', 'Ibrahim'],
            ['agent17@realestate.test', 'ziad_agent', 'Ziad', 'Anwar'],
            ['agent18@realestate.test', 'nour_realty', 'Nour', 'Hassan'],
            ['agent19@realestate.test', 'bassam_homes', 'Bassam', 'Karam'],
            ['agent20@realestate.test', 'manal_group', 'Manal', 'Azar'],
        ];
        foreach ($agentUsers as [$email, $username, $fname, $lname]) {
            $this->created['agents'][] = $this->upsertUser(
                email: $email,
                username: $username,
                fname: $fname,
                lname: $lname,
                type: 'agent',
            );
        }
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
                'country_code_phone' => '+963',
                'gender' => fake()->randomElement(['male', 'female']),
            ],
        );
    }
}
