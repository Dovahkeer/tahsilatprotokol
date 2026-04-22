<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MuvekkilController;
use App\Http\Controllers\ProtokolController;
use App\Http\Controllers\TahsilatController;
use App\Http\Controllers\TahsilatDashboardController;
use App\Http\Controllers\YetkiController;
use Illuminate\Support\Facades\Route;

// ==========================================
// ANA GİRİŞ & KİMLİK DOĞRULAMA (AUTH)
// ==========================================
Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::controller(AuthenticatedSessionController::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', 'create')->name('login');
        Route::post('/login', 'store')->name('login.store');
    });
    Route::post('/logout', 'destroy')->middleware('auth')->name('logout');
});

// ==========================================
// SİSTEM İÇİ ROTALAR (OTURUM AÇMIŞ KULLANICILAR)
// ==========================================
Route::middleware('auth')->group(function () {
    
    // Genel Dashboard & Müvekkil
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/muvekkil/list', [MuvekkilController::class, 'list']);

    // --- TAHSİLAT MODÜLÜ ---
    Route::prefix('tahsilat')->group(function () {

        // 1. Dashboard (Özet Veriler)
        Route::controller(TahsilatDashboardController::class)->group(function () {
            Route::get('/dashboard-data', 'dashboardData');
            Route::get('/dashboard-beklenti-protokoller', 'beklentiProtokoller');
            Route::get('/prim/pivot-table', 'primPivot');
        });

        // 2. Protokol İşlemleri
        Route::prefix('protokol')->controller(ProtokolController::class)->group(function () {
            Route::get('/list', 'list');
            Route::post('/store', 'store');
            Route::get('/vade-takip', 'vadeTakip'); // Vade Takip Eklendi
            Route::get('/hacizciler', 'hacizciler');
            Route::get('/portfoyler', 'portfoyler');
            Route::get('/portfoyler/{muvekkil_id}', 'portfoylerByMuvekkil')->whereNumber('muvekkil_id');
            Route::get('/borclu-ara', 'borcluAra');
            Route::get('/{protokol}/pdf', 'pdf')->whereNumber('protokol');
            Route::post('/{protokol}/pdf', 'uploadPdf')->whereNumber('protokol');
            Route::get('/{protokol}', 'show')->whereNumber('protokol');
            Route::put('/{protokol}', 'update')->whereNumber('protokol');
        });

        // 3. Dışa Aktarımlar (Export)
        Route::prefix('export')->group(function () {
            Route::get('/excel', [ExportController::class, 'excel'])->name('tahsilat.export.excel');
            Route::get('/vade-takip', [ExportController::class, 'vadeTakip'])->name('tahsilat.export.vade-takip'); // Vade Takip Export
            Route::get('/mail-order-pdf', [TahsilatController::class, 'mailOrderPdf'])->name('tahsilat.export.mail-order-pdf');
        });

        // 4. YÖNETİCİ (ADMIN) İŞLEMLERİ
        Route::middleware('admin')->group(function () {
            
            // Yetki, Prim, Hacizci ve Portföy Ayarları
            Route::prefix('yetki')->controller(YetkiController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/prim-ayarlar', 'primAyarlar');
                
                // Prim ve Kademe Ayarları
                Route::post('/prim-ayarlar/kademe-pay', 'kademePay');
                Route::post('/prim-ayarlar/kademe-asama', 'kademeAsama');
                Route::post('/prim-ayarlar/kademe-ekle', 'kademeEkle');
                Route::post('/prim-ayarlar/muvekkil-oranlari', 'muvekkilOranlari');
                
                // Hacizci İşlemleri
                Route::post('/prim-ayarlar/hacizci-kademe', 'hacizciKademe');
                Route::post('/prim-ayarlar/hacizci-ekle', 'hacizciEkle');

                // Portföy İşlemleri
                Route::post('/prim-ayarlar/portfoy-ekle', 'portfoyEkle');
                Route::put('/prim-ayarlar/portfoy/{id}', 'portfoyGuncelle');

                // Kullanıcı ve Şifre İşlemleri
                Route::post('/kullanici-ekle', 'kullaniciEkle');
                Route::post('/{user}/sifre-degistir', 'sifreDegistir')->whereNumber('user');
                Route::post('/{user}', 'update')->whereNumber('user');
            });

            // Toplu Veri İçe Aktarımı (Import)
            Route::controller(ImportController::class)->group(function () {
                Route::get('/protokol-toplu/sablon', 'protokolTemplate');
                Route::post('/protokol-toplu/import', 'protokolImport');
                Route::get('/izlence-gecmis/sablon', 'izlenceTemplate');
                Route::post('/izlence-gecmis/import', 'izlenceImport');
            });
        });

        // 5. Tahsilat Genel İşlemleri (CRUD)
        Route::controller(TahsilatController::class)->group(function () {
            Route::get('/list', 'list');
            Route::post('/store', 'store');
            Route::get('/dekont/{dekont}/view', 'dekontView')->whereNumber('dekont');
            
            Route::get('/{tahsilat}', 'show')->whereNumber('tahsilat');
            Route::put('/{tahsilat}', 'update')->whereNumber('tahsilat');
            Route::post('/{tahsilat}/dekont', 'uploadDekont')->whereNumber('tahsilat');
            Route::post('/{tahsilat}/onayla', 'onayla')->whereNumber('tahsilat');
            Route::post('/{tahsilat}/reddet', 'reddet')->whereNumber('tahsilat');
            Route::post('/{tahsilat}/iptal', 'iptal')->whereNumber('tahsilat');
        });
    });
});