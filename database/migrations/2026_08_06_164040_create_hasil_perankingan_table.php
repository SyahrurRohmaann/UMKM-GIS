<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_perankingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_perhitungan')->onDelete('cascade');
            $table->foreignId('alternatif_lokasi_id')->constrained('alternatif_lokasi')->onDelete('cascade');
            $table->decimal('skor_akhir', 6, 4);
            $table->integer('ranking');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_perankingan');
    }
};
