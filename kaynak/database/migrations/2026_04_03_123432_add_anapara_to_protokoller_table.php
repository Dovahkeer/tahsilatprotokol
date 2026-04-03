<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('protokoller', function (Blueprint $table) {
            // Eğer ana_para sütunu YOKSA ekle
            if (!Schema::hasColumn('protokoller', 'ana_para')) {
                $table->decimal('ana_para', 15, 2)->nullable();
            }
            
            // Eğer kapak_hesabi sütunu YOKSA ekle
            if (!Schema::hasColumn('protokoller', 'kapak_hesabi')) {
                $table->decimal('kapak_hesabi', 15, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protokoller', function (Blueprint $table) {
            //
        });
    }
};
