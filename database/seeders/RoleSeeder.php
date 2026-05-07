<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'CEO',           'slug' => 'ceo',       'financial_limit' => 999999, 'leave_limit_days' => 999, 'color' => '#0EA5A4'],
            ['name' => 'مدير عام',      'slug' => 'director',  'financial_limit' => 50000,  'leave_limit_days' => 30,  'color' => '#60A5FA'],
            ['name' => 'مدير قسم',      'slug' => 'manager',   'financial_limit' => 10000,  'leave_limit_days' => 7,   'color' => '#94A3B8'],
            ['name' => 'موارد بشرية',   'slug' => 'hr',        'financial_limit' => 2000,   'leave_limit_days' => 14,  'color' => '#22C55E'],
            ['name' => 'مالية',         'slug' => 'finance',   'financial_limit' => 25000,  'leave_limit_days' => 0,   'color' => '#F59E0B'],
            ['name' => 'موظف عادي',     'slug' => 'employee',  'financial_limit' => 0,      'leave_limit_days' => 0,   'color' => '#334155'],
        ];

        $allPerms    = DB::table('permissions')->pluck('id', 'slug');
        $ceoPerms    = $allPerms->values()->all();
        $directorPerms = $allPerms->only(['view_employees','edit_employees','approve_leaves','approve_purchases','view_payroll','manage_rules','manage_departments','manage_contract_types','view_reports','manage_shifts'])->values()->all();
        $managerPerms  = $allPerms->only(['view_employees','approve_leaves','approve_purchases','view_payroll','manage_shifts'])->values()->all();
        $hrPerms       = $allPerms->only(['view_employees','edit_employees','approve_leaves','approve_purchases','manage_departments','manage_contract_types','manage_company_settings','view_payroll','view_reports'])->values()->all();
        $financePerms  = $allPerms->only(['view_employees','approve_purchases','view_payroll','edit_payroll','run_payroll'])->values()->all();
        $employeePerms = $allPerms->only(['view_employees'])->values()->all();

        $rolePermMap = [
            'ceo'      => $ceoPerms,
            'director' => $directorPerms,
            'manager'  => $managerPerms,
            'hr'       => $hrPerms,
            'finance'  => $financePerms,
            'employee' => $employeePerms,
        ];

        foreach ($roles as $role) {
            $roleId = DB::table('roles')->insertGetId([
                ...$role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($rolePermMap[$role['slug']] as $permId) {
                DB::table('role_permissions')->insert([
                    'role_id'       => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }
    }
}
