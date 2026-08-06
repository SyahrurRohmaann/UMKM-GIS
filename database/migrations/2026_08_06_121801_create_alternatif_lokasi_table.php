<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alternatif_lokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_usaha_id')->constrained('jenis_usaha')->onDelete('cascade');
            $table->foreignId('kelurahan_id')->constrained('kelurahan')->onDelete('cascade');
            $table->string('nama_lokasi');
            $table->double('latitude', 10, 8);
            $table->double('longitude', 11, 8);
            $table->decimal('harga_sewa_per_tahun', 15, 2);
            $table->tinyInteger('skor_keamanan');
            $table->boolean('adalah_kompetitor')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternatif_lokasi');
    }
};