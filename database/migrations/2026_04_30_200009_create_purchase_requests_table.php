<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('employee_id')->constrained()->onDelete('restrict');
            $table->string('item_name');
            $table->string('vendor')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->string('approval_level')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
