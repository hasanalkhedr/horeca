<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Settings\Category;
use App\Models\Settings\Currency;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create(['name' => 'admin', 'email' => 'admin@horeca.com', 'password' => 'password']);
        $this->call([
            RoleSeeder::class,
        ]);

        Currency::create([
            'CODE' => 'USD',
            'name' => 'US Dollar',
            'rate_to_usd' => '1.00',
            'country' => 'USA'
        ]);
        Currency::create([
            'CODE' => 'EURO',
            'name' => 'Euro',
            'rate_to_usd' => '0.9',
            'country' => 'Europe'
        ]);

        $this->call(CategorySeeder::class);
    }
}

