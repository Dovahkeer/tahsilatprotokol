<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Güvenlik: Tablo var mı ve sütun zaten eklenmemiş mi?
        if (Schema::hasTable('tahsilat_yetkili_kullanicilar') && !Schema::hasColumn('tahsilat_yetkili_kullanicilar', 'sorumlu_muvekkiller')) {
            Schema::table('tahsilat_yetkili_kullanicilar', function (Blueprint $table) {
                // JSON formatında ve boş bırakılabilir (nullable) olarak ekliyoruz
                $table->json('sorumlu_muvekkiller')->nullable()->after('aktif');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tahsilat_yetkili_kullanicilar') && Schema::hasColumn('tahsilat_yetkili_kullanicilar', 'sorumlu_muvekkiller')) {
            Schema::table('tahsilat_yetkili_kullanicilar', function (Blueprint $table) {
                $table->dropColumn('sorumlu_muvekkiller');
            });
        }
    }
};