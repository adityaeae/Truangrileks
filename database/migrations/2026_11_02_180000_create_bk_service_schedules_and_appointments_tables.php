<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bk_service_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bk_teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 120)->default('Layanan Bimbingan dan Konseling');
            $table->text('description')->nullable();
            $table->string('service_mode', 20)->default('tatap_muka');
            $table->string('location', 255);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedTinyInteger('capacity')->default(1);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['bk_teacher_id', 'starts_at']);
            $table->index(['is_published', 'starts_at']);
        });

        Schema::create('bk_appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_schedule_id')->constrained('bk_service_schedules')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $table->string('student_note', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['service_schedule_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_appointments');
        Schema::dropIfExists('bk_service_schedules');
    }
};
