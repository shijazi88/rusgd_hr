<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Comprehensive scenario seeder — covers every status and approval path.
 *
 * Hierarchy recap:
 *   Taha (CEO)          – no direct manager
 *   Mohammed (Director) – no direct manager
 *   Sara (Manager)      → Mohammed
 *   Ahmed (Employee)    → Mohammed
 *   Fatima (Employee)   → Sara   [status: on_leave]
 *   Omar  (Employee)    → Sara
 *   Noura (HR)          → Mohammed
 *
 * Approval flow:
 *   pending          → awaiting direct manager (level 1)
 *   initial_approval → direct manager approved; awaiting CEO + HR (level 2, both required)
 *   approved         → CEO and HR both approved
 *   rejected         → rejected at level 1 OR level 2
 */
class ScenarioSeeder extends Seeder
{
    private const MORPH_LEAVE    = 'App\Models\LeaveRequest';
    private const MORPH_PURCHASE = 'App\Models\PurchaseRequest';

    public function run(): void
    {
        $employees  = DB::table('employees')->get()->keyBy('email');
        $users      = DB::table('users')->get()->keyBy('email');
        $shifts     = DB::table('shifts')->pluck('id', 'name');
        $leaveTypes = DB::table('leave_types')->pluck('id', 'name');

        $taha     = $employees['taha@rushdai.com'];
        $mohammed = $employees['m.nasser@rushdai.com'];
        $sara     = $employees['s.qahtani@rushdai.com'];
        $ahmed    = $employees['a.zahrani@rushdai.com'];
        $fatima   = $employees['f.omari@rushdai.com'];
        $omar     = $employees['o.shamri@rushdai.com'];
        $noura    = $employees['n.salman@rushdai.com'];

        $tahaU     = $users['taha@rushdai.com'];
        $mohammedU = $users['m.nasser@rushdai.com'];
        $saraU     = $users['s.qahtani@rushdai.com'];
        $nouraU    = $users['n.salman@rushdai.com'];

        $allEmps = [$taha, $mohammed, $sara, $ahmed, $fatima, $omar, $noura];

        // ── Leave Balances (year 2026) ────────────────────────────────────────
        // type => [total_days, [used per employee: taha,mohammed,sara,ahmed,fatima,omar,noura]]
        $balanceMap = [
            'إجازة سنوية'     => [21, [0, 3, 10, 5, 0, 2, 1]],
            'إجازة مرضية'      => [10, [0, 1,  0, 3, 3, 0, 0]],
            'إجازة طارئة'      => [5,  [0, 1,  1, 0, 0, 1, 0]],
            'إجازة بدون راتب' => [30, [0, 0,  0, 0, 0, 0, 0]],
        ];

        foreach ($allEmps as $i => $emp) {
            foreach ($balanceMap as $ltName => [$total, $usedList]) {
                $ltId = $leaveTypes[$ltName] ?? null;
                if (!$ltId) continue;
                DB::table('leave_balances')->insert([
                    'employee_id'   => $emp->id,
                    'leave_type_id' => $ltId,
                    'year'          => 2026,
                    'total_days'    => $total,
                    'used_days'     => $usedList[$i] ?? 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        $annual    = $leaveTypes['إجازة سنوية'];
        $sick      = $leaveTypes['إجازة مرضية'];
        $emergency = $leaveTypes['إجازة طارئة'];

        // ── Leave Requests ────────────────────────────────────────────────────
        // 8 scenarios × 2 categories (pending, initial×2, approved×2, rejected×2)

        // 1. PENDING — Ahmed (→ Mohammed)
        $lr1 = $this->insertLeave([
            'reference'    => 'LR-2026-001',
            'employee_id'  => $ahmed->id,
            'leave_type_id'=> $annual,
            'from_date'    => '2026-07-01',
            'to_date'      => '2026-07-07',
            'days'         => 7,
            'reason'       => 'إجازة سنوية مخططة',
            'status'       => 'pending',
            'created_at'   => now()->subDays(2),
        ]);

        // 2. PENDING — Omar (→ Sara)
        $lr2 = $this->insertLeave([
            'reference'    => 'LR-2026-002',
            'employee_id'  => $omar->id,
            'leave_type_id'=> $annual,
            'from_date'    => '2026-08-15',
            'to_date'      => '2026-08-19',
            'days'         => 5,
            'reason'       => 'رحلة عائلية مخططة',
            'status'       => 'pending',
            'created_at'   => now()->subDays(1),
        ]);

        // 3. INITIAL_APPROVAL — Fatima (→ Sara → level 2 pending, neither CEO nor HR acted)
        $lr3 = $this->insertLeave([
            'reference'    => 'LR-2026-003',
            'employee_id'  => $fatima->id,
            'leave_type_id'=> $sick,
            'from_date'    => '2026-05-20',
            'to_date'      => '2026-05-22',
            'days'         => 3,
            'reason'       => 'مرض – تقرير طبي مرفق',
            'status'       => 'initial_approval',
            'approved_by'  => $saraU->id,
            'approved_at'  => now()->subDays(1)->setTime(10, 0),
            'created_at'   => now()->subDays(1)->setTime(8, 0),
        ]);
        $this->step(self::MORPH_LEAVE, $lr3, $saraU->id, 1, 'approved', null, now()->subDays(1)->setTime(10, 0));

        // 4. INITIAL_APPROVAL (partial L2) — Sara (→ Mohammed); CEO approved, HR hasn't
        $lr4 = $this->insertLeave([
            'reference'    => 'LR-2026-004',
            'employee_id'  => $sara->id,
            'leave_type_id'=> $annual,
            'from_date'    => '2026-09-01',
            'to_date'      => '2026-09-10',
            'days'         => 10,
            'reason'       => 'إجازة سنوية',
            'status'       => 'initial_approval',
            'approved_by'  => $mohammedU->id,
            'approved_at'  => now()->subDays(3)->setTime(11, 0),
            'created_at'   => now()->subDays(4)->setTime(9, 0),
        ]);
        $this->step(self::MORPH_LEAVE, $lr4, $mohammedU->id, 1, 'approved', null,     now()->subDays(3)->setTime(11, 0));
        $this->step(self::MORPH_LEAVE, $lr4, $tahaU->id,     2, 'approved', null,     now()->subDays(2)->setTime(9, 0));
        // Noura (HR) has NOT acted yet — status stays initial_approval

        // 5. APPROVED — Ahmed (→ Mohammed → Taha CEO + Noura HR)
        $lr5 = $this->insertLeave([
            'reference'    => 'LR-2026-005',
            'employee_id'  => $ahmed->id,
            'leave_type_id'=> $sick,
            'from_date'    => '2026-03-10',
            'to_date'      => '2026-03-12',
            'days'         => 3,
            'reason'       => 'توعك صحي – تقرير طبي',
            'status'       => 'approved',
            'approved_by'  => $tahaU->id,
            'approved_at'  => now()->subDays(40)->setTime(14, 0),
            'created_at'   => now()->subDays(43)->setTime(9, 0),
        ]);
        $this->step(self::MORPH_LEAVE, $lr5, $mohammedU->id, 1, 'approved', null, now()->subDays(42)->setTime(10, 0));
        $this->step(self::MORPH_LEAVE, $lr5, $tahaU->id,     2, 'approved', null, now()->subDays(41)->setTime(11, 0));
        $this->step(self::MORPH_LEAVE, $lr5, $nouraU->id,    2, 'approved', null, now()->subDays(40)->setTime(14, 0));

        // 6. APPROVED — Mohammed (no direct manager; management handles L1 & L2)
        $lr6 = $this->insertLeave([
            'reference'    => 'LR-2026-006',
            'employee_id'  => $mohammed->id,
            'leave_type_id'=> $emergency,
            'from_date'    => '2026-04-10',
            'to_date'      => '2026-04-10',
            'days'         => 1,
            'reason'       => 'ظرف طارئ عائلي',
            'status'       => 'approved',
            'approval_level'=> 'الإدارة العليا',
            'approved_by'  => $tahaU->id,
            'approved_at'  => now()->subDays(20)->setTime(12, 0),
            'created_at'   => now()->subDays(20)->setTime(8, 0),
        ]);
        $this->step(self::MORPH_LEAVE, $lr6, $tahaU->id,  1, 'approved', null, now()->subDays(20)->setTime(9, 30));
        $this->step(self::MORPH_LEAVE, $lr6, $tahaU->id,  2, 'approved', null, now()->subDays(20)->setTime(11, 0));
        $this->step(self::MORPH_LEAVE, $lr6, $nouraU->id, 2, 'approved', null, now()->subDays(20)->setTime(12, 0));

        // 7. REJECTED at L1 — Omar (→ Sara rejected)
        $lr7 = $this->insertLeave([
            'reference'    => 'LR-2026-007',
            'employee_id'  => $omar->id,
            'leave_type_id'=> $annual,
            'from_date'    => '2026-06-01',
            'to_date'      => '2026-06-05',
            'days'         => 5,
            'reason'       => 'سفر خارجي ترفيهي',
            'status'       => 'rejected',
            'approved_by'  => $saraU->id,
            'approved_at'  => now()->subDays(10)->setTime(14, 0),
            'rejection_reason' => 'تعارض مع موعد تسليم مشروع حرج',
            'created_at'   => now()->subDays(12)->setTime(10, 0),
        ]);
        $this->step(self::MORPH_LEAVE, $lr7, $saraU->id, 1, 'rejected', 'تعارض مع موعد تسليم مشروع حرج', now()->subDays(10)->setTime(14, 0));

        // 8. REJECTED at L2 — Fatima (Sara approved L1; Taha rejected L2)
        $lr8 = $this->insertLeave([
            'reference'    => 'LR-2026-008',
            'employee_id'  => $fatima->id,
            'leave_type_id'=> $annual,
            'from_date'    => '2026-10-01',
            'to_date'      => '2026-10-05',
            'days'         => 5,
            'reason'       => 'سفر خارجي مع العائلة',
            'status'       => 'rejected',
            'approved_by'  => $tahaU->id,
            'approved_at'  => now()->subDays(5)->setTime(16, 0),
            'rejection_reason' => 'فترة حرجة – متطلبات المشروع لا تسمح بالغياب',
            'created_at'   => now()->subDays(8)->setTime(9, 0),
        ]);
        $this->step(self::MORPH_LEAVE, $lr8, $saraU->id, 1, 'approved',  null,                                              now()->subDays(7)->setTime(10, 0));
        $this->step(self::MORPH_LEAVE, $lr8, $tahaU->id, 2, 'rejected', 'فترة حرجة – متطلبات المشروع لا تسمح بالغياب', now()->subDays(5)->setTime(16, 0));

        // ── Purchase Requests ─────────────────────────────────────────────────

        // 1. PENDING — Ahmed (→ Mohammed)
        $pr1 = $this->insertPurchase([
            'reference'   => 'PR-2026-001',
            'employee_id' => $ahmed->id,
            'item_name'   => 'أجهزة لابتوب للمطورين (3 أجهزة)',
            'vendor'      => null,
            'quantity'    => 3,
            'amount'      => 15000,
            'reason'      => 'دعم فريق التطوير البرمجي وتحسين الكفاءة',
            'status'      => 'pending',
            'created_at'  => now()->subDays(2),
        ]);

        // 2. PENDING — Fatima (→ Sara)
        $pr2 = $this->insertPurchase([
            'reference'   => 'PR-2026-002',
            'employee_id' => $fatima->id,
            'item_name'   => 'أجهزة قياس هندسية دقيقة',
            'vendor'      => 'مؤسسة الهندسة المتقدمة',
            'quantity'    => 2,
            'amount'      => 7500,
            'reason'      => 'متطلبات المشروع الهندسي الجديد',
            'status'      => 'pending',
            'created_at'  => now()->subDays(4),
        ]);

        // 3. INITIAL_APPROVAL — Sara (→ Mohammed → no L2 yet)
        $pr3 = $this->insertPurchase([
            'reference'   => 'PR-2026-003',
            'employee_id' => $sara->id,
            'item_name'   => 'تجديد اشتراك AWS السنوي',
            'vendor'      => 'Amazon Web Services',
            'quantity'    => 1,
            'amount'      => 8500,
            'reason'      => 'استمرارية الخدمات السحابية لجميع مشاريع الشركة',
            'status'      => 'initial_approval',
            'approved_by' => $mohammedU->id,
            'approved_at' => now()->subDays(6)->setTime(11, 0),
            'created_at'  => now()->subDays(8),
        ]);
        $this->step(self::MORPH_PURCHASE, $pr3, $mohammedU->id, 1, 'approved', null, now()->subDays(6)->setTime(11, 0));

        // 4. INITIAL_APPROVAL (partial L2) — Omar (→ Sara); HR approved, CEO hasn't
        $pr4 = $this->insertPurchase([
            'reference'   => 'PR-2026-004',
            'employee_id' => $omar->id,
            'item_name'   => 'أثاث مكتبي وكراسي ارجونومية',
            'vendor'      => 'مكتبات الأثاث الحديث',
            'quantity'    => 5,
            'amount'      => 12000,
            'reason'      => 'تجديد أثاث المكتب وتحسين بيئة العمل',
            'status'      => 'initial_approval',
            'approved_by' => $saraU->id,
            'approved_at' => now()->subDays(10)->setTime(12, 0),
            'created_at'  => now()->subDays(13),
        ]);
        $this->step(self::MORPH_PURCHASE, $pr4, $saraU->id,  1, 'approved', null, now()->subDays(10)->setTime(12, 0));
        $this->step(self::MORPH_PURCHASE, $pr4, $nouraU->id, 2, 'approved', null, now()->subDays(9)->setTime(9, 0));
        // Taha (CEO) has NOT acted yet — status stays initial_approval

        // 5. APPROVED — Noura (→ Mohammed → Taha CEO + Noura HR)
        $pr5 = $this->insertPurchase([
            'reference'   => 'PR-2026-005',
            'employee_id' => $noura->id,
            'item_name'   => 'أدوات مكتبية وقرطاسية متنوعة',
            'vendor'      => null,
            'quantity'    => 1,
            'amount'      => 2000,
            'reason'      => 'توفير احتياجات مكتب الموارد البشرية',
            'status'      => 'approved',
            'approved_by' => $tahaU->id,
            'approved_at' => now()->subDays(20)->setTime(14, 0),
            'created_at'  => now()->subDays(25),
        ]);
        $this->step(self::MORPH_PURCHASE, $pr5, $mohammedU->id, 1, 'approved', null, now()->subDays(24)->setTime(10, 0));
        $this->step(self::MORPH_PURCHASE, $pr5, $tahaU->id,     2, 'approved', null, now()->subDays(22)->setTime(11, 0));
        $this->step(self::MORPH_PURCHASE, $pr5, $nouraU->id,    2, 'approved', null, now()->subDays(20)->setTime(14, 0));

        // 6. APPROVED — Mohammed (no manager; management handles L1+L2)
        $pr6 = $this->insertPurchase([
            'reference'    => 'PR-2026-006',
            'employee_id'  => $mohammed->id,
            'item_name'    => 'ترقية خوادم التطوير',
            'vendor'       => 'Dell Technologies',
            'quantity'     => 2,
            'amount'       => 25000,
            'reason'       => 'تحسين أداء بيئة التطوير والتكامل المستمر',
            'status'       => 'approved',
            'approval_level'=> 'الإدارة العليا',
            'approved_by'  => $tahaU->id,
            'approved_at'  => now()->subDays(30)->setTime(15, 0),
            'created_at'   => now()->subDays(35),
        ]);
        $this->step(self::MORPH_PURCHASE, $pr6, $tahaU->id,  1, 'approved', null, now()->subDays(34)->setTime(9, 0));
        $this->step(self::MORPH_PURCHASE, $pr6, $tahaU->id,  2, 'approved', null, now()->subDays(32)->setTime(11, 0));
        $this->step(self::MORPH_PURCHASE, $pr6, $nouraU->id, 2, 'approved', null, now()->subDays(30)->setTime(15, 0));

        // 7. REJECTED at L1 — Ahmed (Mohammed rejected)
        $pr7 = $this->insertPurchase([
            'reference'        => 'PR-2026-007',
            'employee_id'      => $ahmed->id,
            'item_name'        => 'كراسي مريحة للمبرمجين (3 كراسي)',
            'vendor'           => null,
            'quantity'         => 3,
            'amount'           => 11500,
            'reason'           => 'تحسين راحة فريق التطوير',
            'status'           => 'rejected',
            'approved_by'      => $mohammedU->id,
            'approved_at'      => now()->subDays(15)->setTime(16, 0),
            'rejection_reason' => 'تجاوز الميزانية المخصصة للربع الثاني',
            'created_at'       => now()->subDays(17),
        ]);
        $this->step(self::MORPH_PURCHASE, $pr7, $mohammedU->id, 1, 'rejected', 'تجاوز الميزانية المخصصة للربع الثاني', now()->subDays(15)->setTime(16, 0));

        // 8. REJECTED at L2 — Sara (Mohammed approved L1; Taha rejected L2)
        $pr8 = $this->insertPurchase([
            'reference'        => 'PR-2026-008',
            'employee_id'      => $sara->id,
            'item_name'        => 'تراخيص برامج إدارة المشاريع Jira (10 مستخدمين)',
            'vendor'           => 'Atlassian',
            'quantity'         => 10,
            'amount'           => 5000,
            'reason'           => 'تحسين تتبع المهام وإدارة المشاريع',
            'status'           => 'rejected',
            'approved_by'      => $tahaU->id,
            'approved_at'      => now()->subDays(12)->setTime(14, 0),
            'rejection_reason' => 'البرنامج المطلوب مكرر مع أدوات موجودة بالفعل',
            'created_at'       => now()->subDays(16),
        ]);
        $this->step(self::MORPH_PURCHASE, $pr8, $mohammedU->id, 1, 'approved',  null,                                        now()->subDays(15)->setTime(10, 0));
        $this->step(self::MORPH_PURCHASE, $pr8, $tahaU->id,     2, 'rejected', 'البرنامج المطلوب مكرر مع أدوات موجودة بالفعل', now()->subDays(12)->setTime(14, 0));

        // ── Attendance (last 14 weekdays) ─────────────────────────────────────
        $attRecords = [];
        $dayCount   = 0;
        $today      = Carbon::today();

        for ($offset = 0; $dayCount < 14; $offset++) {
            $date = $today->copy()->subDays($offset);
            if ($date->isWeekend()) continue;
            $dayCount++;
            $d = $date->toDateString();

            // Taha — always early
            $attRecords[] = ['employee_id' => $taha->id,     'date' => $d, 'check_in' => '07:45:00', 'check_out' => '18:00:00', 'work_hours' => 10.25, 'status' => 'present',  'late_minutes' => 0];
            // Mohammed — always on time
            $attRecords[] = ['employee_id' => $mohammed->id, 'date' => $d, 'check_in' => '08:00:00', 'check_out' => '17:15:00', 'work_hours' => 9.25,  'status' => 'present',  'late_minutes' => 0];
            // Sara — late twice (days 2 and 9), present otherwise
            if ($dayCount === 2 || $dayCount === 9) {
                $attRecords[] = ['employee_id' => $sara->id, 'date' => $d, 'check_in' => '08:40:00', 'check_out' => '17:00:00', 'work_hours' => 8.33, 'status' => 'late',    'late_minutes' => 40];
            } else {
                $attRecords[] = ['employee_id' => $sara->id, 'date' => $d, 'check_in' => '08:05:00', 'check_out' => '17:00:00', 'work_hours' => 8.92, 'status' => 'present', 'late_minutes' => 0];
            }
            // Ahmed — absent on day 3, present otherwise
            if ($dayCount === 3) {
                $attRecords[] = ['employee_id' => $ahmed->id, 'date' => $d, 'check_in' => null, 'check_out' => null, 'work_hours' => 0, 'status' => 'absent',  'late_minutes' => 0];
            } else {
                $attRecords[] = ['employee_id' => $ahmed->id, 'date' => $d, 'check_in' => '07:55:00', 'check_out' => '17:00:00', 'work_hours' => 9.08, 'status' => 'present', 'late_minutes' => 0];
            }
            // Fatima — on leave every day (employee status is on_leave)
            $attRecords[] = ['employee_id' => $fatima->id, 'date' => $d, 'check_in' => null, 'check_out' => null, 'work_hours' => 0, 'status' => 'on_leave', 'late_minutes' => 0];
            // Omar — late on day 6, present otherwise
            if ($dayCount === 6) {
                $attRecords[] = ['employee_id' => $omar->id, 'date' => $d, 'check_in' => '09:15:00', 'check_out' => '17:00:00', 'work_hours' => 7.75, 'status' => 'late',    'late_minutes' => 75];
            } else {
                $attRecords[] = ['employee_id' => $omar->id, 'date' => $d, 'check_in' => '08:00:00', 'check_out' => '17:00:00', 'work_hours' => 9.0,  'status' => 'present', 'late_minutes' => 0];
            }
            // Noura — always present
            $attRecords[] = ['employee_id' => $noura->id, 'date' => $d, 'check_in' => '08:02:00', 'check_out' => '17:00:00', 'work_hours' => 8.97, 'status' => 'present', 'late_minutes' => 0];
        }

        foreach ($attRecords as $rec) {
            DB::table('attendance')->insert([...$rec, 'created_at' => now(), 'updated_at' => now()]);
        }

        // ── Payroll Run (April 2026) ──────────────────────────────────────────
        $activeEmps  = DB::table('employees')->where('status', 'active')->whereNull('deleted_at')->get();
        $totalSalary = $activeEmps->sum(fn ($e) => $e->base_salary + $e->housing_allowance + $e->transport_allowance);

        $runId = DB::table('payroll_runs')->insertGetId([
            'month'        => 4,
            'year'         => 2026,
            'total_amount' => round($totalSalary, 2),
            'status'       => 'completed',
            'run_by'       => $tahaU->id,
            'created_at'   => '2026-04-30 22:00:00',
            'updated_at'   => '2026-04-30 22:05:00',
        ]);

        foreach ($activeEmps as $emp) {
            DB::table('payroll_items')->insert([
                'payroll_run_id'      => $runId,
                'employee_id'         => $emp->id,
                'base_salary'         => $emp->base_salary,
                'housing_allowance'   => $emp->housing_allowance,
                'transport_allowance' => $emp->transport_allowance,
                'deductions'          => 0,
                'net_salary'          => $emp->base_salary + $emp->housing_allowance + $emp->transport_allowance,
                'created_at'          => '2026-04-30 22:00:00',
                'updated_at'          => '2026-04-30 22:00:00',
            ]);
        }

        // ── Shift Assignments ─────────────────────────────────────────────────
        $morning = $shifts['الوردية الصباحية'] ?? null;
        $evening = $shifts['الوردية المسائية'] ?? null;
        $night   = $shifts['الوردية الليلية']  ?? null;

        if (!$morning) return;

        $workDays = json_encode(['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس']);

        $assignments = [
            ['employee_id' => $taha->id,     'shift_id' => $morning, 'from_date' => '2026-01-01', 'to_date' => '2026-12-31', 'days_of_week' => $workDays],
            ['employee_id' => $mohammed->id, 'shift_id' => $morning, 'from_date' => '2026-01-01', 'to_date' => '2026-12-31', 'days_of_week' => $workDays],
            ['employee_id' => $sara->id,     'shift_id' => $evening, 'from_date' => '2026-01-01', 'to_date' => '2026-12-31', 'days_of_week' => $workDays],
            ['employee_id' => $ahmed->id,    'shift_id' => $morning, 'from_date' => '2026-01-01', 'to_date' => '2026-12-31', 'days_of_week' => $workDays],
            ['employee_id' => $fatima->id,   'shift_id' => $morning, 'from_date' => '2026-01-01', 'to_date' => '2026-12-31', 'days_of_week' => $workDays],
            ['employee_id' => $omar->id,     'shift_id' => $night,   'from_date' => '2026-01-01', 'to_date' => '2026-12-31', 'days_of_week' => $workDays],
            ['employee_id' => $noura->id,    'shift_id' => $morning, 'from_date' => '2026-01-01', 'to_date' => '2026-12-31', 'days_of_week' => $workDays],
        ];

        foreach ($assignments as $a) {
            DB::table('shift_assignments')->insert([...$a, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function insertLeave(array $data): int
    {
        return DB::table('leave_requests')->insertGetId(array_merge([
            'approval_level'   => 'المدير المباشر',
            'approved_by'      => null,
            'approved_at'      => null,
            'rejection_reason' => null,
        ], $data, ['updated_at' => $data['created_at']]));
    }

    private function insertPurchase(array $data): int
    {
        return DB::table('purchase_requests')->insertGetId(array_merge([
            'vendor'           => null,
            'quantity'         => 1,
            'approval_level'   => 'المدير المباشر',
            'approved_by'      => null,
            'approved_at'      => null,
            'rejection_reason' => null,
        ], $data, ['updated_at' => $data['created_at']]));
    }

    private function step(string $type, int $id, int $userId, int $level, string $action, ?string $notes, Carbon $at): void
    {
        DB::table('approval_steps')->insert([
            'approvable_type' => $type,
            'approvable_id'   => $id,
            'user_id'         => $userId,
            'level'           => $level,
            'action'          => $action,
            'notes'           => $notes,
            'created_at'      => $at,
            'updated_at'      => $at,
        ]);
    }
}
