<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_result_shares', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('screening_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bk_teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('consent_version', 30)->default('2026-11-02-v1');
            $table->timestamp('consented_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['bk_teacher_id', 'revoked_at']);
            $table->index(['student_id', 'screening_result_id', 'revoked_at'], 'screening_shares_student_result_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_result_shares');
    }
};
