<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bobot_kriteria', function (Blueprint $table) {
            $table->foreignId('jenis_usaha_id')->nullable()->constrained('jenis_usaha')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('bobot_kriteria', function (Blueprint $table) {
            $table->dropForeign(['jenis_usaha_id']);
            $table->dropColumn('jenis_usaha_id');
        });
    }
};