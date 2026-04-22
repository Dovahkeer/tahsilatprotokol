<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('protokol_hacizci', function (Blueprint $table) {
            // 1. Eski ikili kilidi (Primary Key) kaldır
            $table->dropPrimary(['protokol_id', 'hacizci_id']);
            
            // 2. Yeni üçlü kilidi (Protokol + Hacizci + Tür) ekle
            $table->primary(['protokol_id', 'hacizci_id', 'haciz_turu']);
        });
    }

    public function down(): void
    {
        Schema::table('protokol_hacizci', function (Blueprint $table) {
            $table->dropPrimary(['protokol_id', 'hacizci_id', 'haciz_turu']);
            $table->primary(['protokol_id', 'hacizci_id']);
        });
    }
};