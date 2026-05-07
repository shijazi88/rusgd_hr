<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('description');
            $table->decimal('min_value', 12, 2)->default(0);
            $table->decimal('max_value', 12, 2)->default(0);
            $table->string('approver_role');
            $table->string('approver_label');
            $table->string('priority')->default('low');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_rules');
    }
};
