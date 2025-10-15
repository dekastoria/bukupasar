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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                ->constrained('markets')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->date('tanggal');
            $table->bigInteger('jumlah');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['market_id', 'tanggal']);
            $table->index(['market_id', 'tenant_id']);
            $table->index(['market_id', 'created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
