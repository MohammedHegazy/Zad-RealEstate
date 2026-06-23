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

        // 60 owners (7 known + 53 generated)
        $knownOwners = [
            ['owner@realestate.test', 'property_owner', 'Omar', 'Haddad'],
            ['owner2@realestate.test', 'noor_estates', 'Noor', 'Al-Din'],
            ['owner3@realestate.test', 'ward_buildings', 'Ward', 'Jaber'],
            ['owner4@realestate.test', 'basel_invest', 'Basel', 'Khalaf'],
            ['owner5@realestate.test', 'diala_props', 'Diala', 'Mousa'],
            ['owner6@realestate.test', 'samer_realty', 'Samer', 'Ayoub'],
            ['owner7@realestate.test', 'lina_holdings', 'Lina', 'Sulaiman'],
        ];
        foreach ($knownOwners as [$email, $username, $fname, $lname]) {
            $this->created['owners'][] = $this->upsertUser(
                email: $email, username: $username, fname: $fname, lname: $lname, type: 'owner',
            );
        }
        for ($i = 8; $i <= 60; $i++) {
            $this->created['owners'][] = $this->upsertUser(
                email: "owner{$i}@realestate.test", username: fake()->unique()->userName(),
                fname: fake()->firstName(), lname: fake()->lastName(), type: 'owner',
            );
        }

        // 120 buyers/investors (6 known + 114 generated)
        $knownBuyers = [
            ['buyer@realestate.test', 'investor_buyer', 'Layla', 'Khalil'],
            ['buyer2@realestate.test', 'hisham_invest', 'Hisham', 'Qassem'],
            ['buyer3@realestate.test', 'ranim_capital', 'Ranim', 'Fares'],
            ['buyer4@realestate.test', 'khaled_vc', 'Khaled', 'Shahin'],
            ['buyer5@realestate.test', 'mariam_trust', 'Mariam', 'Akkad'],
            ['buyer6@realestate.test', 'tamer_group', 'Tamer', 'Rashid'],
        ];
        foreach ($knownBuyers as [$email, $username, $fname, $lname]) {
            $this->created['buyers'][] = $this->upsertUser(
                email: $email, username: $username, fname: $fname, lname: $lname, type: 'buyer',
            );
        }
        for ($i = 7; $i <= 120; $i++) {
            $this->created['buyers'][] = $this->upsertUser(
                email: "buyer{$i}@realestate.test", username: fake()->unique()->userName(),
                fname: fake()->firstName(), lname: fake()->lastName(), type: 'buyer',
            );
        }

        // 8 company-owners (4 known + 4 generated)
        $knownCompanies = [
            ['company@realestate.test', 'acme_realty', 'Samir', 'Nasser'],
            ['company2@realestate.test', 'sham_holdings', 'Fadi', 'Mardini'],
            ['company3@realestate.test', 'levant_props', 'Rana', 'Kattan'],
            ['company4@realestate.test', 'orient_group', 'Maher', 'Barakat'],
        ];
        foreach ($knownCompanies as [$email, $username, $fname, $lname]) {
            $this->created['companies'][] = $this->upsertUser(
                email: $email, username: $username, fname: $fname, lname: $lname, type: 'company',
            );
        }
        for ($i = 5; $i <= 8; $i++) {
            $this->created['companies'][] = $this->upsertUser(
                email: "company{$i}@realestate.test", username: fake()->unique()->userName(),
                fname: fake()->firstName(), lname: fake()->lastName(), type: 'company',
            );
        }

        // 112 agents (~14 per company, keeping existing Rami Saleh as agent 1)
        $knownAgents = [
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
        foreach ($knownAgents as [$email, $username, $fname, $lname]) {
            $this->created['agents'][] = $this->upsertUser(
                email: $email, username: $username, fname: $fname, lname: $lname, type: 'agent',
            );
        }

        // 92 more agents (21-112)
        for ($i = 21; $i <= 112; $i++) {
            $this->created['agents'][] = $this->upsertUser(
                email: "agent{$i}@realestate.test", username: fake()->unique()->userName(),
                fname: fake()->firstName(), lname: fake()->lastName(), type: 'agent',
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
