<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eğer users tablosunda 'aktif' yoksa ekle
        if (!Schema::hasColumn('users', 'aktif')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('aktif')->default(true);
            });
        }

        // Eğer hacizciler tablosunda 'aktif' yoksa ekle (Burada var olduğu için bu bloğu es geçecek ve çökmeyecek)
        if (!Schema::hasColumn('hacizciler', 'aktif')) {
            Schema::table('hacizciler', function (Blueprint $table) {
                $table->boolean('aktif')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'aktif')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('aktif');
            });
        }

        if (Schema::hasColumn('hacizciler', 'aktif')) {
            Schema::table('hacizciler', function (Blueprint $table) {
                $table->dropColumn('aktif');
            });
        }
    }
};