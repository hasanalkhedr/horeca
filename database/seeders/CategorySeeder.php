<?php

namespace Database\Seeders;

use App\Models\Settings\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'BakeryPastry',]);
        Category::create(['name' => 'Beverage',]);
        Category::create(['name' => 'Catering equipment',]);
        Category::create(['name' => 'Coffee & Tea Pavilion',]);
        Category::create(['name' => 'Consultancy, Recruitment & Franchise',]);
        Category::create(['name' => 'Education',]);
        Category::create(['name' => 'Food',]);
        Category::create(['name' => 'Hygiene',]);
        Category::create(['name' => 'Interiors',]);
        Category::create(['name' => 'International Pavilion',]);
        Category::create(['name' => 'Packaging/Labeling',]);
        Category::create(['name' => 'Techzone',]);
    }
}
