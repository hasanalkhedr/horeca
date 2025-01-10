<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventManager = Role::create(['name' => 'eventManager']);
        $salesMan = Role::create(['name' => 'salesMan']);

        Permission::create(['name' => 'Add Events']);
        Permission::create(['name' => 'View Events']);

        $eventManager->givePermissionTo(['Add Events', 'View Events']);
        $salesMan->givePermissionTo('View Events');
    }
}
