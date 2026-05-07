<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Seeder-side bootstrap of company structure for testing only.
     *
     * Lazily creates the contract types and job titles each test employee
     * needs, then inserts the employees with the resolved FK ids. The
     * resulting rows are normal records — admins can rename/deactivate
     * them post-seed. On a real production deploy (which doesn't run this
     * seeder), the company starts with empty lookup tables and the admin
     * adds their own.
     */
    public function run(): void
    {
        // Default company name (admin can change it from /company)
        DB::table('company_settings')->insert([
            'key'        => 'name',
            'value'      => 'RushdAI',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $depts = DB::table('departments')->pluck('id', 'name');
        $roles = DB::table('roles')->pluck('id', 'slug');

        $contractTypeIds = $this->seedContractTypes();

        $employees = [
            'taha' => [
                'name' => 'طه المؤسس', 'email' => 'taha@rushdai.com', 'phone' => '0501111111',
                'department' => 'الإدارة العليا', 'job_title' => 'Founder & CEO',
                'manager_key' => null, 'contract_slug' => 'permanent',
                'base_salary' => 35000, 'housing_allowance' => 10500, 'transport_allowance' => 800,
                'hire_date' => '2023-01-01', 'status' => 'active', 'role_slug' => 'ceo',
            ],
            'mohammed' => [
                'name' => 'محمد ناصر', 'email' => 'm.nasser@rushdai.com', 'phone' => '0501234567',
                'department' => 'تطوير البرمجيات', 'job_title' => 'Senior Backend',
                'manager_key' => null, 'contract_slug' => 'permanent',
                'base_salary' => 18000, 'housing_allowance' => 5400, 'transport_allowance' => 800,
                'hire_date' => '2024-01-12', 'status' => 'active', 'role_slug' => 'director',
            ],
            'sara' => [
                'name' => 'سارة القحطاني', 'email' => 's.qahtani@rushdai.com', 'phone' => '0507654321',
                'department' => 'إدارة المشاريع', 'job_title' => 'Project Manager',
                'manager_key' => 'mohammed', 'contract_slug' => 'permanent',
                'base_salary' => 15000, 'housing_allowance' => 4500, 'transport_allowance' => 800,
                'hire_date' => '2024-02-05', 'status' => 'active', 'role_slug' => 'manager',
            ],
            'ahmed' => [
                'name' => 'أحمد الزهراني', 'email' => 'a.zahrani@rushdai.com', 'phone' => '0509876543',
                'department' => 'تطوير البرمجيات', 'job_title' => 'Frontend Developer',
                'manager_key' => 'mohammed', 'contract_slug' => 'permanent',
                'base_salary' => 12000, 'housing_allowance' => 3600, 'transport_allowance' => 800,
                'hire_date' => '2024-03-15', 'status' => 'active', 'role_slug' => 'employee',
            ],
            'fatima' => [
                'name' => 'فاطمة العمري', 'email' => 'f.omari@rushdai.com', 'phone' => '0505551234',
                'department' => 'الهندسة الإنشائية', 'job_title' => 'Civil Engineer',
                'manager_key' => 'sara', 'contract_slug' => 'temporary',
                'base_salary' => 11000, 'housing_allowance' => 3300, 'transport_allowance' => 800,
                'hire_date' => '2024-04-01', 'status' => 'on_leave', 'role_slug' => 'employee',
            ],
            'omar' => [
                'name' => 'عمر الشمري', 'email' => 'o.shamri@rushdai.com', 'phone' => '0506667777',
                'department' => 'الهندسة الإنشائية', 'job_title' => 'Structural Engineer',
                'manager_key' => 'sara', 'contract_slug' => 'permanent',
                'base_salary' => 13000, 'housing_allowance' => 3900, 'transport_allowance' => 800,
                'hire_date' => '2024-05-20', 'status' => 'active', 'role_slug' => 'employee',
            ],
            'noura' => [
                'name' => 'نورة السلمان', 'email' => 'n.salman@rushdai.com', 'phone' => '0508889999',
                'department' => 'الموارد البشرية', 'job_title' => 'HR Specialist',
                'manager_key' => 'mohammed', 'contract_slug' => 'permanent',
                'base_salary' => 10000, 'housing_allowance' => 3000, 'transport_allowance' => 800,
                'hire_date' => '2024-06-10', 'status' => 'active', 'role_slug' => 'hr',
            ],
        ];

        $insertedIds = [];

        foreach ($employees as $key => $emp) {
            $deptId     = $depts[$emp['department']];
            $jobTitleId = $this->getOrCreateJobTitle($deptId, $emp['job_title']);
            $contractId = $contractTypeIds[$emp['contract_slug']];
            $managerId  = $emp['manager_key'] ? $insertedIds[$emp['manager_key']] : null;
            $roleSlug   = $emp['role_slug'];

            $empId = DB::table('employees')->insertGetId([
                'name'                => $emp['name'],
                'email'               => $emp['email'],
                'phone'               => $emp['phone'],
                'department_id'       => $deptId,
                'job_title_id'        => $jobTitleId,
                'manager_id'          => $managerId,
                'contract_type_id'    => $contractId,
                'base_salary'         => $emp['base_salary'],
                'housing_allowance'   => $emp['housing_allowance'],
                'transport_allowance' => $emp['transport_allowance'],
                'hire_date'           => $emp['hire_date'],
                'status'              => $emp['status'],
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

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

    /**
     * @return array<string, int> map of slug → id
     */
    private function seedContractTypes(): array
    {
        $types = [
            ['name' => 'دوام كامل',  'slug' => 'permanent',  'description' => 'عقد دائم بدوام كامل'],
            ['name' => 'مؤقت',        'slug' => 'temporary',  'description' => 'عقد محدد المدة'],
            ['name' => 'تدريب',       'slug' => 'internship', 'description' => 'تدريب طلابي أو مهني'],
            ['name' => 'استشارة',     'slug' => 'contract',   'description' => 'متعاون مستقل'],
        ];

        $map = [];
        foreach ($types as $t) {
            $map[$t['slug']] = DB::table('contract_types')->insertGetId([
                ...$t,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return $map;
    }

    private function getOrCreateJobTitle(int $departmentId, string $name): int
    {
        $existing = DB::table('job_titles')
            ->where('department_id', $departmentId)
            ->where('name', $name)
            ->value('id');

        if ($existing) return (int) $existing;

        return (int) DB::table('job_titles')->insertGetId([
            'department_id' => $departmentId,
            'name'          => $name,
            'is_active'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
