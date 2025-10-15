<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                ->constrained('markets')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->string('nama', 100);
            $table->boolean('wajib_keterangan')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['market_id', 'jenis', 'nama']);
            $table->index('market_id');
            $table->index(['market_id', 'jenis', 'aktif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
