<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            LeaveTypeSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            ApprovalRuleSeeder::class,
            ShiftSeeder::class,
            EmployeeSeeder::class,
            ScenarioSeeder::class,
        ]);
    }
}
