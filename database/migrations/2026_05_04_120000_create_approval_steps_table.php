<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');                   // approvable_type, approvable_id
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('level');           // 1 = direct_manager, 2 = management
            $table->string('action');                       // approved | rejected
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
    }
};
