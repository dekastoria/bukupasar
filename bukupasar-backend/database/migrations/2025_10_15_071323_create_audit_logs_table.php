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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')
                ->constrained('markets')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
            $table->string('action', 100);
            $table->string('entity', 100);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['market_id', 'created_at']);
            $table->index(['entity', 'entity_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
