<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'الإدارة العليا'],
            ['name' => 'تطوير البرمجيات'],
            ['name' => 'الهندسة الإنشائية'],
            ['name' => 'إدارة المشاريع'],
            ['name' => 'الموارد البشرية'],
            ['name' => 'المالية'],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->insert(array_merge($dept, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
