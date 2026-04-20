<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Güvenlik: Tablo var mı? Ve sütun henüz EKLENMEMİŞ mi?
        if (Schema::hasTable('tahsilatlar') && !Schema::hasColumn('tahsilatlar', 'pos_cihazi')) {
            Schema::table('tahsilatlar', function (Blueprint $table) {
                $table->string('pos_cihazi')->nullable()->after('tahsilat_yontemi');
            });
        }
    }

    public function down(): void
    {
        // Geri alma işlemi için güvenlik: Tablo var mı? Ve sütun GERÇEKTEN var mı?
        if (Schema::hasTable('tahsilatlar') && Schema::hasColumn('tahsilatlar', 'pos_cihazi')) {
            Schema::table('tahsilatlar', function (Blueprint $table) {
                $table->dropColumn('pos_cihazi');
            });
        }
    }
};