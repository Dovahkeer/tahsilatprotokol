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

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/muvekkil/list', [MuvekkilController::class, 'list']);

    Route::prefix('tahsilat')->group(function () {
        Route::get('/dashboard-data', [TahsilatDashboardController::class, 'dashboardData']);
        Route::get('/dashboard-beklenti-protokoller', [TahsilatDashboardController::class, 'beklentiProtokoller']);
        Route::get('/prim/pivot-table', [TahsilatDashboardController::class, 'primPivot']);

        Route::get('/protokol/list', [ProtokolController::class, 'list']);
        Route::post('/protokol/store', [ProtokolController::class, 'store']);
        Route::get('/protokol/hacizciler', [ProtokolController::class, 'hacizciler']);
        Route::get('/protokol/portfoyler', [ProtokolController::class, 'portfoyler']);
        Route::get('/protokol/portfoyler/{muvekkil_id}', [ProtokolController::class, 'portfoylerByMuvekkil'])
            ->whereNumber('muvekkil_id');
        Route::get('/protokol/borclu-ara', [ProtokolController::class, 'borcluAra']);
        Route::get('/protokol/{protokol}/pdf', [ProtokolController::class, 'pdf'])->whereNumber('protokol');
        Route::post('/protokol/{protokol}/pdf', [ProtokolController::class, 'uploadPdf'])->whereNumber('protokol');
        Route::get('/protokol/{protokol}', [ProtokolController::class, 'show'])->whereNumber('protokol');
        Route::put('/protokol/{protokol}', [ProtokolController::class, 'update'])->whereNumber('protokol');

        Route::get('/list', [TahsilatController::class, 'list']);
        Route::post('/store', [TahsilatController::class, 'store']);
        Route::get('/dekont/{dekont}/view', [TahsilatController::class, 'dekontView'])->whereNumber('dekont');

        Route::middleware('admin')->group(function () {
            Route::get('/yetki', [YetkiController::class, 'index']);
            Route::get('/yetki/prim-ayarlar', [YetkiController::class, 'primAyarlar']);
            Route::post('/yetki/prim-ayarlar/kademe-pay', [YetkiController::class, 'kademePay']);
            Route::post('/yetki/prim-ayarlar/kademe-asama', [YetkiController::class, 'kademeAsama']);
            Route::post('/yetki/prim-ayarlar/hacizci-kademe', [YetkiController::class, 'hacizciKademe']);
            Route::post('/yetki/prim-ayarlar/muvekkil-oranlari', [YetkiController::class, 'muvekkilOranlari']);
            Route::post('/yetki/{user}', [YetkiController::class, 'update'])->whereNumber('user');

            Route::get('/protokol-toplu/sablon', [ImportController::class, 'protokolTemplate']);
            Route::post('/protokol-toplu/import', [ImportController::class, 'protokolImport']);
            Route::get('/izlence-gecmis/sablon', [ImportController::class, 'izlenceTemplate']);
            Route::post('/izlence-gecmis/import', [ImportController::class, 'izlenceImport']);
        });

        Route::get('/export/excel', [ExportController::class, 'excel'])->name('tahsilat.export.excel');
        Route::get('/export/mail-order-pdf', [ExportController::class, 'mailOrderPdf'])->name('tahsilat.export.mail-order-pdf');

        Route::get('/{tahsilat}', [TahsilatController::class, 'show'])->whereNumber('tahsilat');
        Route::put('/{tahsilat}', [TahsilatController::class, 'update'])->whereNumber('tahsilat');
        Route::post('/{tahsilat}/dekont', [TahsilatController::class, 'uploadDekont'])->whereNumber('tahsilat');
        Route::post('/{tahsilat}/onayla', [TahsilatController::class, 'onayla'])->whereNumber('tahsilat');
        Route::post('/{tahsilat}/reddet', [TahsilatController::class, 'reddet'])->whereNumber('tahsilat');
        Route::post('/{tahsilat}/iptal', [TahsilatController::class, 'iptal'])->whereNumber('tahsilat');
    });
});
