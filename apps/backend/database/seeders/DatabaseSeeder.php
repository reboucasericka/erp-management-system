<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ContactFunctionSeeder::class);
        $this->call(DemoDataSeeder::class);
        $this->call(AccessSeeder::class);
        $this->call(CleanupViewPermissionsSeeder::class);
        $this->call(RolePermissionSeeder::class);
    }
}
