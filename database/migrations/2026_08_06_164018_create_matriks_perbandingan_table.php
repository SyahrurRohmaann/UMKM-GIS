<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriks_perbandingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_perhitungan')->onDelete('cascade');
            $table->foreignId('jenis_usaha_id')->constrained('jenis_usaha')->onDelete('cascade');
            $table->foreignId('kriteria_a_id')->constrained('kriteria')->onDelete('cascade');
            $table->foreignId('kriteria_b_id')->constrained('kriteria')->onDelete('cascade');
            $table->decimal('nilai_saaty', 5, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriks_perbandingan');
    }
};
