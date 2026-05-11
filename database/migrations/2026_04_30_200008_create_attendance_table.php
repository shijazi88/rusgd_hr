<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained()->onDelete('restrict');

            // Which period within the shift applied to this attendance row
            // (a day can have a first AND a second period for split shifts —
            // we record the resolved period that matched the check-in time).
            $table->foreignId('period_id')->nullable()->constrained('periods')->onDelete('set null');

            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->unsignedInteger('late_minutes')->default(0);

            // Day multiplier captured at the time of attendance (Fri/Sat may
            // pay 1.5x or 2x; we snapshot this so payroll is reproducible
            // even if shift_days are edited later).
            $table->decimal('multiplier', 4, 2)->default(1.00);

            // Auto-computed deduction from the matched period late tier.
            // payroll_run reads `deduction_amount` and applies it.
            $table->decimal('deduction_amount', 8, 2)->default(0);
            $table->string('deduction_type')->nullable();   // hour | day | fixed | absence
            $table->string('deduction_reason')->nullable(); // human label, e.g. "تأخير 09:01-09:20"

            $table->string('status')->default('absent');    // present | late | absent | on_leave
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
