<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taksit_evrak_detaylari', function (Blueprint $table) {
            $table->id();
            
            // Kilit Nokta: Hangi takside ait olduğunu bağlayan kolon (Taksit silinirse evrak da silinir)
            $table->foreignId('protokol_taksit_id')->constrained('protokol_taksitler')->cascadeOnDelete();
            
            $table->string('evrak_tipi'); // 'cek' veya 'senet' olacak
            $table->string('seri_no')->nullable();
            $table->string('banka_adi')->nullable();
            $table->string('sube_adi')->nullable();
            $table->string('kesideci')->nullable(); // Çeki yazan asıl kişi/kurum
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taksit_evrak_detaylari');
    }
};