<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi_pilihan_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_perhitungan')->onDelete('cascade');
            $table->double('latitude', 10, 8)->nullable();
            $table->double('longitude', 11, 8)->nullable();
            $table->foreignId('alternatif_lokasi_id')->nullable()->constrained('alternatif_lokasi')->onDelete('cascade');
            $table->decimal('skor_dihitung', 6, 4)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi_pilihan_user');
    }
};
