<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $depts = DB::table('departments')->pluck('id', 'name');
        $roles = DB::table('roles')->pluck('id', 'slug');

        $employees = [
            'taha' => [
                'name' => 'طه المؤسس', 'email' => 'taha@rushdai.com', 'phone' => '0501111111',
                'department_id' => $depts['الإدارة العليا'], 'job_title' => 'Founder & CEO',
                'manager_key' => null, 'contract_type' => 'permanent',
                'base_salary' => 35000, 'housing_allowance' => 10500, 'transport_allowance' => 800,
                'hire_date' => '2023-01-01', 'status' => 'active', 'role_slug' => 'ceo',
            ],
            'mohammed' => [
                'name' => 'محمد ناصر', 'email' => 'm.nasser@rushdai.com', 'phone' => '0501234567',
                'department_id' => $depts['تطوير البرمجيات'], 'job_title' => 'Senior Backend',
                'manager_key' => null, 'contract_type' => 'permanent',
                'base_salary' => 18000, 'housing_allowance' => 5400, 'transport_allowance' => 800,
                'hire_date' => '2024-01-12', 'status' => 'active', 'role_slug' => 'director',
            ],
            'sara' => [
                'name' => 'سارة القحطاني', 'email' => 's.qahtani@rushdai.com', 'phone' => '0507654321',
                'department_id' => $depts['إدارة المشاريع'], 'job_title' => 'Project Manager',
                'manager_key' => 'mohammed', 'contract_type' => 'permanent',
                'base_salary' => 15000, 'housing_allowance' => 4500, 'transport_allowance' => 800,
                'hire_date' => '2024-02-05', 'status' => 'active', 'role_slug' => 'manager',
            ],
            'ahmed' => [
                'name' => 'أحمد الزهراني', 'email' => 'a.zahrani@rushdai.com', 'phone' => '0509876543',
                'department_id' => $depts['تطوير البرمجيات'], 'job_title' => 'Frontend Developer',
                'manager_key' => 'mohammed', 'contract_type' => 'permanent',
                'base_salary' => 12000, 'housing_allowance' => 3600, 'transport_allowance' => 800,
                'hire_date' => '2024-03-15', 'status' => 'active', 'role_slug' => 'employee',
            ],
            'fatima' => [
                'name' => 'فاطمة العمري', 'email' => 'f.omari@rushdai.com', 'phone' => '0505551234',
                'department_id' => $depts['الهندسة الإنشائية'], 'job_title' => 'Civil Engineer',
                'manager_key' => 'sara', 'contract_type' => 'temporary',
                'base_salary' => 11000, 'housing_allowance' => 3300, 'transport_allowance' => 800,
                'hire_date' => '2024-04-01', 'status' => 'on_leave', 'role_slug' => 'employee',
            ],
            'omar' => [
                'name' => 'عمر الشمري', 'email' => 'o.shamri@rushdai.com', 'phone' => '0506667777',
                'department_id' => $depts['الهندسة الإنشائية'], 'job_title' => 'Structural Engineer',
                'manager_key' => 'sara', 'contract_type' => 'permanent',
                'base_salary' => 13000, 'housing_allowance' => 3900, 'transport_allowance' => 800,
                'hire_date' => '2024-05-20', 'status' => 'active', 'role_slug' => 'employee',
            ],
            'noura' => [
                'name' => 'نورة السلمان', 'email' => 'n.salman@rushdai.com', 'phone' => '0508889999',
                'department_id' => $depts['الموارد البشرية'], 'job_title' => 'HR Specialist',
                'manager_key' => 'mohammed', 'contract_type' => 'permanent',
                'base_salary' => 10000, 'housing_allowance' => 3000, 'transport_allowance' => 800,
                'hire_date' => '2024-06-10', 'status' => 'active', 'role_slug' => 'hr',
            ],
        ];

        $insertedIds = [];

        foreach ($employees as $key => $emp) {
            $roleSlug  = $emp['role_slug'];
            $managerKey = $emp['manager_key'];

            unset($emp['role_slug'], $emp['manager_key']);

            $emp['manager_id'] = $managerKey ? $insertedIds[$managerKey] : null;

            $empId = DB::table('employees')->insertGetId(array_merge($emp, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $insertedIds[$key] = $empId;

            $userId = DB::table('users')->insertGetId([
                'employee_id' => $empId,
                'name'        => $emp['name'],
                'email'       => $emp['email'],
                'password'    => Hash::make('password'),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roles[$roleSlug],
            ]);
        }
    }
}
