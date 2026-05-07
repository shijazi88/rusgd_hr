<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            ['name' => 'الوردية الصباحية', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'color' => '#0EA5A4', 'grace_minutes' => 10],
            ['name' => 'الوردية المسائية', 'start_time' => '14:00:00', 'end_time' => '23:00:00', 'color' => '#F59E0B', 'grace_minutes' => 10],
            ['name' => 'الوردية الليلية',  'start_time' => '22:00:00', 'end_time' => '06:00:00', 'color' => '#60A5FA', 'grace_minutes' => 10],
        ];

        foreach ($shifts as $shift) {
            DB::table('shifts')->insert(array_merge($shift, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
