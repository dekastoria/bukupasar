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
        Schema::table('tenants', function (Blueprint $table) {
            $table->date('tanggal_mulai_sewa')->nullable()->after('outstanding');
            $table->date('tanggal_akhir_sewa')->nullable()->after('tanggal_mulai_sewa');
            $table->bigInteger('tarif_sewa')->default(0)->after('tanggal_akhir_sewa')->comment('Tarif sewa per periode dalam Rupiah');
            $table->enum('periode_sewa', ['harian', 'mingguan', 'bulanan', 'tahunan'])->default('bulanan')->after('tarif_sewa');
            $table->text('catatan_sewa')->nullable()->after('periode_sewa')->comment('Catatan tambahan tentang sewa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_mulai_sewa',
                'tanggal_akhir_sewa',
                'tarif_sewa',
                'periode_sewa',
                'catatan_sewa',
            ]);
        });
    }
};
