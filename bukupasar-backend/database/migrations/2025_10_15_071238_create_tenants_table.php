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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                ->constrained('markets')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->string('nama', 200);
            $table->string('nomor_lapak', 50);
            $table->string('hp', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto_profile')->nullable();
            $table->string('foto_ktp')->nullable();
            $table->bigInteger('outstanding')->default(0);
            $table->timestamps();

            $table->unique(['market_id', 'nomor_lapak']);
            $table->index(['market_id', 'created_at']);
            $table->index('outstanding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
