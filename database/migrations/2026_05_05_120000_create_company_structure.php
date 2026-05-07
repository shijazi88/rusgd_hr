<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable company structure.
 *
 * Replaces the hardcoded ContractType enum and the free-text employee.job_title
 * with editable lookup tables, plus a key/value store for company-wide settings
 * (company name today, more later).
 *
 * Runs after the original employees migration — drops the two old columns and
 * adds FK columns pointing at the new lookup tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── company_settings (key/value, single-tenant) ───────────────────────
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // ── contract_types ────────────────────────────────────────────────────
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── job_titles (per-department) ───────────────────────────────────────
        Schema::create('job_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // "Senior Backend" can exist in IT and Marketing — uniqueness scoped per dept.
            $table->unique(['department_id', 'name']);
        });

        // ── employees: drop old string columns, add FK columns ────────────────
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['job_title', 'contract_type']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('job_title_id')
                  ->nullable()
                  ->after('department_id')
                  ->constrained()
                  ->onDelete('restrict');

            $table->foreignId('contract_type_id')
                  ->nullable()
                  ->after('manager_id')
                  ->constrained()
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['job_title_id']);
            $table->dropForeign(['contract_type_id']);
            $table->dropColumn(['job_title_id', 'contract_type_id']);
            $table->string('job_title')->after('department_id');
            $table->string('contract_type')->default('permanent')->after('manager_id');
        });

        Schema::dropIfExists('job_titles');
        Schema::dropIfExists('contract_types');
        Schema::dropIfExists('company_settings');
    }
};
