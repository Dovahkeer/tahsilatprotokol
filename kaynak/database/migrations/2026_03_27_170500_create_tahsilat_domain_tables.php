<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muvekkiller', function (Blueprint $table) {
            $table->id();
            $table->string('ad');
            $table->string('kod')->nullable();
            $table->string('normalized_ad')->index();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique('normalized_ad');
        });

        Schema::create('portfoyler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muvekkil_id')->constrained('muvekkiller')->cascadeOnDelete();
            $table->string('ad');
            $table->string('kod')->nullable();
            $table->string('normalized_ad')->index();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['muvekkil_id', 'normalized_ad']);
        });

        Schema::create('hacizciler', function (Blueprint $table) {
            $table->id();
            $table->string('ad_soyad');
            $table->string('sicil_no')->nullable();
            $table->string('kademe')->default('kademe_1');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('protokol_numara_sayaclari', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('yil')->unique();
            $table->unsignedInteger('son_sira')->default(0);
            $table->timestamps();
        });

        Schema::create('protokoller', function (Blueprint $table) {
            $table->id();
            $table->string('protokol_no')->unique();
            $table->foreignId('muvekkil_id')->constrained('muvekkiller')->restrictOnDelete();
            $table->foreignId('portfoy_id')->nullable()->constrained('portfoyler')->nullOnDelete();
            $table->date('protokol_tarihi');
            $table->string('borclu_adi');
            $table->string('borclu_tckn_vkn', 20)->nullable();
            $table->string('muhatap_adi')->nullable();
            $table->string('muhatap_telefon', 30)->nullable();
            $table->decimal('pesinat', 15, 2)->default(0);
            $table->decimal('toplam_protokol_tutari', 15, 2);
            $table->boolean('aktif')->default(true);
            $table->string('protokol_pdf_dosya_yolu')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['muvekkil_id', 'aktif']);
            $table->index('protokol_tarihi');
        });

        Schema::create('protokol_taksitler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protokol_id')->constrained('protokoller')->cascadeOnDelete();
            $table->unsignedInteger('taksit_no');
            $table->date('taksit_tarihi');
            $table->decimal('taksit_tutari', 15, 2);
            $table->decimal('odenen_tutar', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['protokol_id', 'taksit_no']);
            $table->index('taksit_tarihi');
        });

        Schema::create('protokol_hacizci', function (Blueprint $table) {
            $table->foreignId('protokol_id')->constrained('protokoller')->cascadeOnDelete();
            $table->foreignId('hacizci_id')->constrained('hacizciler')->restrictOnDelete();
            $table->string('haciz_turu');
            $table->decimal('pay_orani', 5, 2)->nullable();

            $table->primary(['protokol_id', 'hacizci_id']);
        });

        Schema::create('tahsilatlar', function (Blueprint $table) {
            $table->id();
            $table->boolean('protokolsuz')->default(false);
            $table->foreignId('protokol_id')->nullable()->constrained('protokoller')->nullOnDelete();
            $table->foreignId('protokol_taksit_id')->nullable()->constrained('protokol_taksitler')->nullOnDelete();
            $table->string('odeme_kalemi_tipi');
            $table->foreignId('muvekkil_id')->constrained('muvekkiller')->restrictOnDelete();
            $table->string('borclu_adi');
            $table->string('borclu_tckn_vkn', 20)->nullable();
            $table->date('tahsilat_tarihi');
            $table->decimal('tutar', 15, 2);
            $table->string('tahsilat_yontemi');
            $table->jsonb('tahsilat_birimleri');
            $table->text('notlar')->nullable();
            $table->string('onay_durumu')->default('beklemede');
            $table->text('red_nedeni')->nullable();
            $table->text('iptal_nedeni')->nullable();
            $table->foreignId('onaylayan_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reddeden_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('iptal_eden_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tahsilat_tarihi', 'onay_durumu']);
            $table->index(['muvekkil_id', 'tahsilat_tarihi']);
        });

        Schema::create('tahsilat_dekontlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahsilat_id')->constrained('tahsilatlar')->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('tahsilat_yetkili_kullanicilar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('tahsilat_olusturabilir')->default(false);
            $table->boolean('protokol_olusturabilir')->default(false);
            $table->boolean('protokol_duzenleyebilir')->default(false);
            $table->boolean('toplu_protokol_ekleyebilir')->default(false);
            $table->boolean('tahsilat_takip_sorumlusu')->default(false);
            $table->boolean('aktif')->default(true);
            $table->jsonb('tab_permissions')->default(json_encode([]));
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('prim_kademeler', function (Blueprint $table) {
            $table->id();
            $table->string('kademe')->unique();
            $table->string('kademe_adi');
            $table->decimal('varsayilan_prim_orani', 5, 2)->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('prim_kademe_pay_oranlari', function (Blueprint $table) {
            $table->id();
            $table->string('ust_kademe');
            $table->string('alt_kademe');
            $table->decimal('ust_kademe_orani', 5, 2)->default(0);
            $table->decimal('alt_kademe_orani', 5, 2)->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['ust_kademe', 'alt_kademe']);
        });

        Schema::create('prim_kademe_asamalari', function (Blueprint $table) {
            $table->id();
            $table->string('kademe');
            $table->unsignedInteger('asama_no');
            $table->decimal('esik_tutari', 15, 2)->default(0);
            $table->decimal('prim_orani', 5, 2)->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['kademe', 'asama_no']);
        });

        Schema::create('muvekkil_prim_oranlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muvekkil_id')->constrained('muvekkiller')->cascadeOnDelete();
            $table->decimal('prim_orani', 5, 2)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique('muvekkil_id');
        });

        Schema::create('prim_audit_loglari', function (Blueprint $table) {
            $table->id();
            $table->string('alan_tipi');
            $table->string('islem_tipi');
            $table->jsonb('hedef_anahtar')->nullable();
            $table->jsonb('eski_deger')->nullable();
            $table->jsonb('yeni_deger')->nullable();
            $table->text('aciklama')->nullable();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('izlence_gecmis_tahsilatlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muvekkil_id')->constrained('muvekkiller')->restrictOnDelete();
            $table->string('ham_muvekkil_adi');
            $table->date('tahsilat_tarihi');
            $table->decimal('tutar', 15, 2);
            $table->string('source_fingerprint')->unique();
            $table->jsonb('source_payload');
            $table->timestamps();

            $table->index(['muvekkil_id', 'tahsilat_tarihi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izlence_gecmis_tahsilatlari');
        Schema::dropIfExists('prim_audit_loglari');
        Schema::dropIfExists('muvekkil_prim_oranlari');
        Schema::dropIfExists('prim_kademe_asamalari');
        Schema::dropIfExists('prim_kademe_pay_oranlari');
        Schema::dropIfExists('prim_kademeler');
        Schema::dropIfExists('tahsilat_yetkili_kullanicilar');
        Schema::dropIfExists('tahsilat_dekontlari');
        Schema::dropIfExists('tahsilatlar');
        Schema::dropIfExists('protokol_hacizci');
        Schema::dropIfExists('protokol_taksitler');
        Schema::dropIfExists('protokoller');
        Schema::dropIfExists('protokol_numara_sayaclari');
        Schema::dropIfExists('hacizciler');
        Schema::dropIfExists('portfoyler');
        Schema::dropIfExists('muvekkiller');
    }
};
