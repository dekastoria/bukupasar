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
        Schema::create('settings', function (Blueprint $table) {
            $table->foreignId('market_id')
                ->constrained('markets')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->string('key_name', 100);
            $table->text('value')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->primary(['market_id', 'key_name']);
            $table->index('market_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
