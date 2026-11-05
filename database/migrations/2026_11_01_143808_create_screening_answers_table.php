<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('screening_result_id')->constrained()->cascadeOnDelete();
            $table->string('question_code');
            $table->string('answer')->nullable();
            $table->timestamps();

            $table->unique(['screening_result_id', 'question_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_answers');
    }
};
