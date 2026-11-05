<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screening_consents', function (Blueprint $table) {
            $table->enum('consent_type', ['participation', 'storage', 'followup'])->default('participation')->after('consent_given');
        });
    }

    public function down(): void
    {
        Schema::table('screening_consents', function (Blueprint $table) {
            $table->dropColumn('consent_type');
        });
    }
};
