<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'إجازة سنوية',       'max_days_per_year' => 21,  'is_paid' => true,  'color' => '#0EA5A4'],
            ['name' => 'إجازة مرضية',        'max_days_per_year' => 10,  'is_paid' => true,  'color' => '#22C55E'],
            ['name' => 'إجازة طارئة',        'max_days_per_year' => 5,   'is_paid' => true,  'color' => '#F59E0B'],
            ['name' => 'إجازة أمومة',        'max_days_per_year' => 90,  'is_paid' => true,  'color' => '#60A5FA'],
            ['name' => 'إجازة بدون راتب',   'max_days_per_year' => 30,  'is_paid' => false, 'color' => '#94A3B8'],
        ];

        foreach ($types as $type) {
            DB::table('leave_types')->insert(array_merge($type, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
