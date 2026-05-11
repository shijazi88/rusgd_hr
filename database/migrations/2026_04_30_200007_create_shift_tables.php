<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Three-tier shift model (mirrors traditional Arabic HR systems):
 *
 *   periods          — atomic check-in/out time windows + late tiers + deductions
 *   shifts           — the weekly composition (each shift is a name + 7-day map)
 *   shift_days       — for each shift, per-day: which period(s) apply + multiplier
 *   shift_assignments — who works which shift over which date range
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── periods ───────────────────────────────────────────────────────────
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#0EA5A4');

            // Behaviour flags
            $table->boolean('is_open_period')->default(false);        // فترة مفتوحة
            $table->boolean('allow_no_fingerprint')->default(false);  // حضور بدون بصمات
            $table->boolean('is_stopped')->default(false);            // إيقاف

            // Check-in section
            $table->boolean('checkin_required')->default(true);
            $table->time('checkin_earliest_at')->nullable();   // أدنى وقت مسموح
            $table->time('checkin_start_at')->nullable();      // بداية الدخول (on-time start)
            $table->time('checkin_end_at')->nullable();        // نهاية الدخول (on-time end → lateness begins)
            $table->time('checkin_latest_at')->nullable();     // أعلى وقت مسموح (after this = absent)
            $table->string('checkin_after_grace_action')->default('entry_only');
            $table->string('checkin_after_end_action')->default('late_attendance');
            $table->boolean('checkin_absence_without_perm')->default(false);
            $table->decimal('checkin_absence_deduction', 8, 2)->default(0);
            $table->string('checkin_absence_deduction_type')->default('day'); // day | hour | fixed

            // Check-out section
            $table->boolean('checkout_required')->default(true);
            $table->time('checkout_earliest_at')->nullable();
            $table->time('checkout_start_at')->nullable();
            $table->time('checkout_end_at')->nullable();
            $table->time('checkout_latest_at')->nullable();
            $table->string('checkout_after_grace_action')->default('exit_only');
            $table->boolean('checkout_next_day')->default(false);  // الخروج في اليوم التالي
            $table->boolean('checkout_absence_without_perm')->default(false);
            $table->decimal('checkout_absence_deduction', 8, 2)->default(0);
            $table->string('checkout_absence_deduction_type')->default('day');

            // Total working minutes (admin can set explicitly; UI computes a hint)
            $table->unsignedInteger('total_work_minutes')->default(420);

            $table->timestamps();
        });

        // ── period_late_tiers ─────────────────────────────────────────────────
        // Each tier defines a time range AFTER on-time end where a specific
        // deduction kicks in. Multiple tiers per period for graduated penalties.
        Schema::create('period_late_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->time('from_time');
            $table->time('to_time');
            $table->decimal('deduction_amount', 8, 2);
            $table->string('deduction_type')->default('hour'); // hour | day | fixed | absence
            $table->unsignedInteger('min_occurrences')->default(0); // عدد الأيام لقبول الخصم
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['period_id', 'sort_order']);
        });

        // ── shifts (weekly composition) ───────────────────────────────────────
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#0EA5A4');
            $table->boolean('show_additional_periods')->default(false); // إظهار الفترات الإضافية
            $table->boolean('is_stopped')->default(false);
            $table->timestamps();
        });

        // ── shift_days (per-day mapping inside a shift) ───────────────────────
        // day_of_week: 'sat','sun','mon','tue','wed','thu','fri'
        // first_period_id + second_period_id allow split shifts.
        // multiplier: 1.0 default, admin can set 1.5 / 2.0 etc. for weighted days.
        Schema::create('shift_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->string('day_of_week', 3); // sat|sun|mon|tue|wed|thu|fri
            $table->foreignId('first_period_id')->nullable()->constrained('periods')->onDelete('restrict');
            $table->foreignId('second_period_id')->nullable()->constrained('periods')->onDelete('restrict');
            $table->decimal('multiplier', 4, 2)->default(1.00);
            $table->timestamps();

            $table->unique(['shift_id', 'day_of_week']);
        });

        // ── shift_assignments ─────────────────────────────────────────────────
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('restrict');
            $table->date('from_date');
            $table->date('to_date');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'from_date', 'to_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('shift_days');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('period_late_tiers');
        Schema::dropIfExists('periods');
    }
};
