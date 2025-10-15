<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('market_id')
                ->after('id')
                ->constrained('markets')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->string('username', 100)->after('name');
            $table->string('phone', 50)->nullable()->after('email');

            $table->unique(['market_id', 'username']);
            $table->index('market_id');
        });

        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
            $table->dropUnique('users_market_id_username_unique');
            $table->dropIndex('users_market_id_index');

            $table->dropColumn(['market_id', 'username', 'phone']);
        });

        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
    }
};
