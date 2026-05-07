<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // Financial rules
            ['type' => 'financial', 'description' => 'أقل من 2,000 ر.س',          'min_value' => 0,     'max_value' => 2000,  'approver_role' => 'manager',  'approver_label' => 'المدير المباشر',            'priority' => 'low'],
            ['type' => 'financial', 'description' => 'من 2,000 إلى 10,000 ر.س',   'min_value' => 2000,  'max_value' => 10000, 'approver_role' => 'director', 'approver_label' => 'المدير العام',              'priority' => 'medium'],
            ['type' => 'financial', 'description' => 'من 10,000 إلى 50,000 ر.س',  'min_value' => 10000, 'max_value' => 50000, 'approver_role' => 'director', 'approver_label' => 'المدير العام + المالية',    'priority' => 'high'],
            ['type' => 'financial', 'description' => 'أكثر من 50,000 ر.س',        'min_value' => 50000, 'max_value' => 0,     'approver_role' => 'ceo',      'approver_label' => 'CEO',                       'priority' => 'critical'],
            // Leave rules
            ['type' => 'leave', 'description' => '1 إلى 3 أيام',    'min_value' => 1,  'max_value' => 3,  'approver_role' => 'manager',  'approver_label' => 'المدير المباشر',          'priority' => 'low'],
            ['type' => 'leave', 'description' => '4 إلى 7 أيام',    'min_value' => 4,  'max_value' => 7,  'approver_role' => 'manager',  'approver_label' => 'المدير المباشر + HR',     'priority' => 'medium'],
            ['type' => 'leave', 'description' => '8 إلى 14 يوم',    'min_value' => 8,  'max_value' => 14, 'approver_role' => 'director', 'approver_label' => 'المدير العام',            'priority' => 'high'],
            ['type' => 'leave', 'description' => 'أكثر من 14 يوم',  'min_value' => 14, 'max_value' => 0,  'approver_role' => 'ceo',      'approver_label' => 'CEO',                     'priority' => 'critical'],
        ];

        foreach ($rules as $rule) {
            DB::table('approval_rules')->insert(array_merge($rule, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
