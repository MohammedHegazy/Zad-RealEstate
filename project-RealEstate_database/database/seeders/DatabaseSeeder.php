<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            LocationSeeder::class,
            DemoUserSeeder::class,
            CompanySeeder::class,
            AgentSeeder::class,
            EstateSeeder::class,
            ReviewSeeder::class,
            PortfolioSeeder::class,
        ]);
    }
}
