<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'عرض الموظفين',              'slug' => 'view_employees'],
            ['name' => 'تعديل الموظفين',             'slug' => 'edit_employees'],
            ['name' => 'حذف الموظفين',               'slug' => 'delete_employees'],
            ['name' => 'عرض الرواتب',                'slug' => 'view_payroll'],
            ['name' => 'تعديل الرواتب',              'slug' => 'edit_payroll'],
            ['name' => 'تشغيل مسير الرواتب',         'slug' => 'run_payroll'],
            ['name' => 'موافقة الإجازات',            'slug' => 'approve_leaves'],
            ['name' => 'موافقة المشتريات',           'slug' => 'approve_purchases'],
            ['name' => 'إدارة الأدوار',              'slug' => 'manage_roles'],
            ['name' => 'إدارة القواعد',              'slug' => 'manage_rules'],
            ['name' => 'إدارة الأقسام',              'slug' => 'manage_departments'],
            ['name' => 'عرض التقارير',               'slug' => 'view_reports'],
            ['name' => 'عرض سجل التدقيق',           'slug' => 'view_audit_logs'],
            ['name' => 'إدارة الورديات',             'slug' => 'manage_shifts'],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->insert(array_merge($perm, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
