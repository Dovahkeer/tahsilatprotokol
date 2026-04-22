{{-- Yetki ve Prim Ayarları Modalı (Sadece Yönetici) --}}
<template x-teleport="body">
<div x-data="yetkiYonetimiModal()"
     x-show="acik"
     @tahsilat-yetki-modal-ac.window="acik = true; yukle()"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-cloak>

    <div class="fixed inset-0 bg-slate-950/65 backdrop-blur-sm"></div>
    <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
    <div class="relative w-full max-w-6xl max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3rem)] flex flex-col rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl" @click.stop>
        
        {{-- ÜST BİLGİ --}}
        <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tahsilat Yetki ve Prim Yönetimi</h3>
            <button @click="acik = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div x-show="yukleniyor" class="flex justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            <div x-show="!yukleniyor" class="space-y-4">
                {{-- SEKME MENÜSÜ --}}
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex flex-wrap gap-2">
                        <button type="button" @click="aktifSekme='kullanicilar'" :class="aktifSekme === 'kullanicilar' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">Kullanıcı Yetkileri</button>
                        <button type="button" @click="aktifSekme='hacizci-kademe'" :class="aktifSekme === 'hacizci-kademe' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">Hacizci Kademeleri</button>
                        <button type="button" @click="aktifSekme='kademe-pay'" :class="aktifSekme === 'kademe-pay' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">Kademe Arası Pay</button>
                        <button type="button" @click="aktifSekme='kademe-asama'" :class="aktifSekme === 'kademe-asama' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">Kademe Prim Eşikleri</button>
                        <button type="button" @click="aktifSekme='muvekkil'" :class="aktifSekme === 'muvekkil' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">Müvekkil Bazlı Prim</button>
                        <button type="button" @click="aktifSekme='portfoy'" :class="aktifSekme === 'portfoy' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">Portföy Yönetimi</button>
                        <button type="button" @click="aktifSekme='audit'" :class="aktifSekme === 'audit' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">Audit Geçmişi</button>
                    </nav>
                </div>

                {{-- 1. KULLANICI YETKİLERİ SEKMESİ (ESTETİK MASTER-DETAIL TASARIM) --}}
                <div x-show="aktifSekme === 'kullanicilar'" class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Sistem Kullanıcıları</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Personelin aktiflik durumlarını ve sistem yetkilerini yönetin.</p>
                        </div>
                        <button type="button" @click="yeniKullaniciModalAcik = true" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 border border-blue-200 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            Yeni Kullanıcı Ekle
                        </button>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300">Kullanıcı Bilgileri</th>
                                        <th class="py-3 px-5 text-center font-semibold text-gray-600 dark:text-gray-300 w-32">Durum</th>
                                        <th class="py-3 px-5 text-right font-semibold text-gray-600 dark:text-gray-300">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    <template x-for="kul in kullanicilar" :key="kul.id">
                                        <tr class="transition-all hover:bg-gray-50/80 dark:hover:bg-gray-800/80" :class="!kul.aktif ? 'bg-gray-50/50 grayscale-[20%] opacity-70' : ''">
                                            <td class="py-3 px-5">
                                                <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                    <span x-text="kul.ad"></span>
                                                    <span x-show="kul.yonetici" class="px-1.5 py-0.5 rounded text-[9px] bg-purple-100 text-purple-700 border border-purple-200">ADMİN</span>
                                                </div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5" x-text="kul.email"></div>
                                            </td>
                                            
                                            <td class="py-3 px-5 flex justify-center">
                                                <button type="button" @click="kul.aktif = !kul.aktif; hizliDurumGuncelle(kul)"
                                                    :class="kul.aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'"
                                                    class="relative inline-flex items-center justify-center px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full border transition-all w-20">
                                                    <span class="relative flex h-2 w-2 mr-1.5">
                                                        <span :class="kul.aktif ? 'bg-emerald-500 shadow-[0_0_4px_#10b981]' : 'bg-red-500'" class="relative inline-flex rounded-full h-full w-full"></span>
                                                    </span>
                                                    <span x-text="kul.aktif ? 'AKTİF' : 'PASİF'"></span>
                                                </button>
                                            </td>
                                            
                                            <td class="py-3 px-5 text-right space-x-2">
                                                <button type="button" @click="sifreDegistirModalAc(kul)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors" title="Şifre Değiştir">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                                    Şifre
                                                </button>
                                                <button type="button" @click="kullaniciYetkiModalAc(kul)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/30 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-lg text-xs font-bold transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    Yetkileri Yönet
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                {{-- 2. HACİZCİ KADEMELERİ (HATASI ÇÖZÜLDÜ) --}}
                <div x-show="aktifSekme === 'hacizci-kademe'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Hacizci Durum Yönetimi</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Personelin sistemdeki aktiflik durumlarını ve kademelerini yönetin.</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="yeniHacizciModalAc()" class="px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 border border-emerald-200 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Yeni Hacizci Ekle
                            </button>
                            <button @click="kaydetHacizciKademeleri()" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm disabled:opacity-50 flex items-center gap-2" :disabled="kaydediliyor">
                                <span x-text="kaydediliyor ? 'Kaydediliyor...' : 'Değişiklikleri Kaydet'"></span>
                            </button>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300">Hacizci Adı Soyadı</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300">Sicil No</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300">Kademe</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300 text-center">Durum</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    <template x-for="kayit in hacizciKademeleri" :key="kayit.hacizci_id">
                                        <tr class="transition-all hover:bg-gray-50/80 dark:hover:bg-gray-800/80" :class="!kayit.aktif ? 'bg-gray-50/50 grayscale-[20%] opacity-80' : ''">
                                            <td class="py-3 px-5 text-gray-900 dark:text-white font-medium" x-text="kayit.ad_soyad"></td>
                                            <td class="py-3 px-5 text-gray-500 dark:text-gray-400" x-text="kayit.sicil_no || '-'"></td>
                                            <td class="py-3 px-5">
                                                {{-- Alpine.js Sıfırlanma Hatası Çözümü (:selected) --}}
                                                <select x-model="kayit.kademe" class="w-48 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-shadow shadow-sm cursor-pointer hover:border-gray-400">
                                                    <template x-for="kademe in kademeler" :key="kademe.kademe">
                                                        <option :value="kademe.kademe" x-text="kademe.kademe_adi + ' (' + kademe.kademe + ')'" :selected="kayit.kademe === kademe.kademe"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            <td class="py-3 px-5 flex justify-center">
                                                <button type="button" @click="kayit.aktif = !kayit.aktif" :class="kayit.aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'" class="relative inline-flex items-center justify-center px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-full border transition-all w-24">
                                                    <span class="relative flex h-2 w-2 mr-2"><span :class="kayit.aktif ? 'bg-emerald-500' : 'bg-red-500'" class="relative inline-flex rounded-full h-full w-full"></span></span>
                                                    <span x-text="kayit.aktif ? 'AKTİF' : 'PASİF'"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- 3. KADEME ARASI PAY --}}
                <div x-show="aktifSekme === 'kademe-pay'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Kademe ve Pay Yönetimi</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Sisteme yeni kademeler ekleyin ve kademeler arası ortak işlerdeki prim oranlarını belirleyin.</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="yeniKademeModalAcik = true" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 border border-blue-200 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center gap-2">Yeni Kademe Ekle</button>
                            <button @click="kaydetKademePayOranlari()" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm disabled:opacity-50" :disabled="kaydediliyor"><span x-text="kaydediliyor ? 'Kaydediliyor...' : 'Değişiklikleri Kaydet'"></span></button>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300">Üst Kademe</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300">Alt Kademe</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300 w-32">Üst Pay (%)</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300 w-32">Alt Pay (%)</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300 text-center">Toplam</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300 text-center">Durum</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    <template x-for="pay in kademePayOranlari" :key="pay.ust_kademe + '_' + pay.alt_kademe">
                                        <tr class="transition-all hover:bg-gray-50/80 dark:hover:bg-gray-800/80" :class="!pay.aktif ? 'bg-gray-50/50 grayscale-[20%] opacity-80' : ''">
                                            <td class="py-3 px-5 text-gray-900 dark:text-white font-medium" x-text="kademeEtiketi(pay.ust_kademe)"></td>
                                            <td class="py-3 px-5 text-gray-900 dark:text-white font-medium" x-text="kademeEtiketi(pay.alt_kademe)"></td>
                                            <td class="py-3 px-5"><input type="number" min="0" max="100" step="0.01" x-model="pay.ust_kademe_orani" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500 outline-none"></td>
                                            <td class="py-3 px-5"><input type="number" min="0" max="100" step="0.01" x-model="pay.alt_kademe_orani" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500 outline-none"></td>
                                            <td class="py-3 px-5 text-center"><span :class="Math.abs(oranToplami(pay) - 100) < 0.01 ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'" class="font-bold px-2 py-1 rounded-md" x-text="oranToplami(pay).toFixed(2)"></span></td>
                                            <td class="py-3 px-5 flex justify-center"><button type="button" @click="pay.aktif = !pay.aktif" :class="pay.aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-500 border-red-200'" class="relative inline-flex items-center justify-center px-4 py-1.5 text-[11px] font-bold uppercase rounded-full border transition-all w-24"><span class="relative flex h-2 w-2 mr-2"><span :class="pay.aktif ? 'bg-emerald-500' : 'bg-red-400'" class="relative inline-flex rounded-full h-full w-full"></span></span><span x-text="pay.aktif ? 'AKTİF' : 'PASİF'"></span></button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- 4. KADEME PRİM EŞİKLERİ --}}
                <div x-show="aktifSekme === 'kademe-asama'" class="space-y-3">
                    <div class="flex justify-between items-center gap-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Kademeler için prim hakediş aşamalarını ve eşik tutarlarını yönetin. Aşamalar sıralı gitmelidir.</p>
                        <div class="flex gap-2">
                            <button type="button" @click="yeniAsamaModalAc()" class="px-4 py-2 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-lg text-sm font-bold">Yeni Aşama Ekle</button>
                            <button @click="kaydetKademePrimAsamalari()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg disabled:opacity-50" :disabled="kaydediliyor">Kaydet</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Kademe</th>
                                    <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Aşama</th>
                                    <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Eşik Tutarı</th>
                                    <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Prim Oranı (%)</th>
                                    <th class="py-3 px-5 text-center font-semibold text-gray-600 dark:text-gray-400">Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="asama in kademePrimAsamalari" :key="asama.kademe + '_' + asama.asama_no">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/80">
                                        <td class="py-3 px-5 text-gray-900 dark:text-white font-medium" x-text="kademeEtiketi(asama.kademe)"></td>
                                        <td class="py-3 px-5 text-gray-700 dark:text-gray-300" x-text="'Asama ' + asama.asama_no"></td>
                                        <td class="py-3 px-5"><input type="number" min="0" step="0.01" x-model="asama.esik_tutari" class="w-40 px-2 py-1.5 rounded-lg border border-gray-300 bg-white text-sm outline-none focus:ring-2 focus:ring-amber-500"></td>
                                        <td class="py-3 px-5"><input type="number" min="0" max="100" step="0.01" x-model="asama.prim_orani" class="w-32 px-2 py-1.5 rounded-lg border border-gray-300 bg-white text-sm outline-none focus:ring-2 focus:ring-amber-500"></td>
                                        <td class="py-3 px-5 text-center"><input type="checkbox" x-model="asama.aktif" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 5. MÜVEKKİL BAZLI PRİM --}}
                <div x-show="aktifSekme === 'muvekkil'" class="space-y-3">
                    <div class="flex justify-end">
                        <button @click="kaydetMuvekkilOranlari()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg disabled:opacity-50" :disabled="kaydediliyor">Kaydet</button>
                    </div>
                    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Müvekkil</th>
                                    <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Prim Orani (%)</th>
                                    <th class="py-3 px-5 text-center font-semibold text-gray-600 dark:text-gray-400">Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="kayit in muvekkilOranlari" :key="kayit.muvekkil_id">
                                    <tr :class="!kayit.aktif ? 'opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800/80'">
                                        <td class="py-3 px-5 text-gray-900 dark:text-white font-medium" x-text="kayit.muvekkil_ad"></td>
                                        <td class="py-3 px-5"><input type="number" min="0" max="100" step="0.01" x-model="kayit.prim_orani" placeholder="Boş bırak: kaldır" class="w-40 px-2 py-1.5 rounded-lg border border-gray-300 bg-white text-sm outline-none focus:ring-2 focus:ring-amber-500"></td>
                                        <td class="py-3 px-5 text-center"><input type="checkbox" x-model="kayit.aktif" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 6. PORTFÖY YÖNETİMİ --}}
                <div x-show="aktifSekme === 'portfoy'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Portföy Yönetimi</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Müvekkillere ait portföyleri (GSD, Bireysel vb.) yönetin. İsimlerini standardize edin.</p>
                        </div>
                        <button type="button" @click="yeniPortfoyModalAc()" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 border border-blue-200 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Yeni Portföy Ekle
                        </button>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300">Müvekkil</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300">Portföy Adı</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300">Portföy Kodu</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300 text-center w-32">Durum</th>
                                        <th class="py-3 px-5 font-semibold text-gray-600 dark:text-gray-300 text-center w-24">Kaydet</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    <template x-for="p in portfoyler" :key="p.id">
                                        <tr class="transition-all hover:bg-gray-50/80 dark:hover:bg-gray-800/80" :class="!p.aktif ? 'bg-gray-50/50 grayscale-[20%] opacity-80' : ''">
                                            <td class="py-3 px-5 font-medium text-gray-700 dark:text-gray-300" x-text="p.muvekkil_ad"></td>
                                            <td class="py-3 px-5">
                                                <input type="text" x-model="p.ad" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                                            </td>
                                            <td class="py-3 px-5">
                                                <input type="text" x-model="p.kod" placeholder="Kod" class="w-full px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                                            </td>
                                            <td class="py-3 px-5 flex justify-center">
                                                <button type="button" @click="p.aktif = !p.aktif"
                                                    :class="p.aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'"
                                                    class="relative inline-flex items-center justify-center px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-full border transition-all w-24">
                                                    <span class="relative flex h-2 w-2 mr-2">
                                                        <span :class="p.aktif ? 'bg-emerald-500' : 'bg-red-500'" class="relative inline-flex rounded-full h-full w-full"></span>
                                                    </span>
                                                    <span x-text="p.aktif ? 'AKTİF' : 'PASİF'"></span>
                                                </button>
                                            </td>
                                            <td class="py-3 px-5 text-center">
                                                <button @click="portfoyGuncelle(p)" class="text-amber-600 hover:text-amber-800 p-1.5 rounded-md hover:bg-amber-50 dark:hover:bg-gray-700 transition-colors border border-transparent hover:border-amber-200" title="Kaydet">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- 7. AUDİT GEÇMİŞİ --}}
                <div x-show="aktifSekme === 'audit'" class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Tarih</th>
                                <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Alan</th>
                                <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">İşlem</th>
                                <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Değiştiren</th>
                                <th class="py-3 px-5 text-left font-semibold text-gray-600 dark:text-gray-400">Detay</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="kayit in auditKayitlari" :key="kayit.id">
                                <tr class="align-top hover:bg-gray-50 dark:hover:bg-gray-800/80">
                                    <td class="py-3 px-5 text-gray-900 dark:text-white whitespace-nowrap" x-text="new Date(kayit.created_at).toLocaleString('tr-TR')"></td>
                                    <td class="py-3 px-5 text-gray-700 dark:text-gray-300 font-medium" x-text="auditAlanEtiketi(kayit.alan_tipi)"></td>
                                    <td class="py-3 px-5"><span class="px-2 py-1 text-xs rounded-md bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-semibold" x-text="auditIslemEtiketi(kayit.islem_tipi)"></span></td>
                                    <td class="py-3 px-5 text-gray-700 dark:text-gray-300" x-text="kayit.degistiren"></td>
                                    <td class="py-3 px-5 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                        <div x-show="kayit.hedef_anahtar" x-text="'Anahtar: ' + jsonOzet(kayit.hedef_anahtar)"></div>
                                        <div x-show="kayit.eski_deger" x-text="'Eski: ' + jsonOzet(kayit.eski_deger)"></div>
                                        <div x-show="kayit.yeni_deger" x-text="'Yeni: ' + jsonOzet(kayit.yeni_deger)"></div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end bg-gray-50 dark:bg-gray-800 rounded-b-2xl">
            <button @click="acik = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors">Kapat</button>
        </div>
    </div>
    
    {{-- A. KULLANICI DETAYLI YETKİ DÜZENLEME MODALI (MASTER-DETAIL) VE SORUMLU MÜVEKKİLLER FİLTRESİ --}}
    <div x-show="kullaniciDuzenlemeModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/80">
                <div>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white" x-text="'Yetki Yönetimi: ' + (duzenlenenKullanici.ad || '')"></h4>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="duzenlenenKullanici.email"></p>
                </div>
                <button @click="kullaniciDuzenlemeModalAcik = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto space-y-6 custom-scrollbar">
                
                {{-- Yönetici Uyarısı --}}
                <div x-show="duzenlenenKullanici.yonetici" class="bg-purple-50 dark:bg-purple-900/20 text-purple-800 dark:text-purple-300 p-4 rounded-lg border border-purple-200 dark:border-purple-800/50 flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-sm">
                        <strong class="block mb-1">Bu kullanıcı bir Sistem Yöneticisi (Admin).</strong>
                        Yöneticiler sistemdeki tüm ekranlara, tüm işlemlere ve tüm müvekkillerin verilerine otomatik olarak tam erişim sağlarlar. Aşağıdaki yetki kısıtlamaları yöneticiler üzerinde etkili değildir.
                    </div>
                </div>

                {{-- Genel İşlem Yetkileri --}}
                <div>
                    <h5 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Temel İşlem Yetkileri
                    </h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" :class="duzenlenenKullanici.yonetici ? 'opacity-60 cursor-not-allowed' : ''">
                            <input type="checkbox" x-model="duzenlenenKullanici.tahsilat_olusturabilir" :disabled="duzenlenenKullanici.yonetici" class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <div>
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tahsilat Oluşturabilir</div>
                                <div class="text-[11px] text-gray-500">Sisteme yeni manuel tahsilat kaydı girebilir.</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" :class="duzenlenenKullanici.yonetici ? 'opacity-60 cursor-not-allowed' : ''">
                            <input type="checkbox" x-model="duzenlenenKullanici.protokol_olusturabilir" :disabled="duzenlenenKullanici.yonetici" class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <div>
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Protokol Oluşturabilir</div>
                                <div class="text-[11px] text-gray-500">Sisteme yeni ödeme protokolü girebilir.</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" :class="duzenlenenKullanici.yonetici ? 'opacity-60 cursor-not-allowed' : ''">
                            <input type="checkbox" x-model="duzenlenenKullanici.protokol_duzenleyebilir" :disabled="duzenlenenKullanici.yonetici" class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <div>
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Protokol Düzenleyebilir</div>
                                <div class="text-[11px] text-gray-500">Başkalarının girdiği protokolleri düzenleyebilir.</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors md:col-span-2" :class="duzenlenenKullanici.yonetici ? 'opacity-60 cursor-not-allowed' : ''">
                            <input type="checkbox" x-model="duzenlenenKullanici.tahsilat_takip_sorumlusu" :disabled="duzenlenenKullanici.yonetici" class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <div>
                                <div class="text-sm font-bold text-green-700 dark:text-green-500">Tahsilat Takip Sorumlusu (Onay Makamı)</div>
                                <div class="text-[11px] text-gray-500">Girilmiş olan "Beklemede" durumundaki tahsilatları onaylama/reddetme yetkisi verir.</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Ekran Görüntüleme Yetkileri --}}
                <div>
                    <h5 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Sekme (Sayfa) Görüntüleme Yetkileri
                    </h5>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <template x-for="tab in tabTanimlari" :key="'detay_tab_' + tab.key">
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" :class="duzenlenenKullanici.yonetici ? 'opacity-60 cursor-not-allowed' : ''">
                                <input type="checkbox" x-model="duzenlenenKullanici.tab_permissions[tab.key]" :disabled="duzenlenenKullanici.yonetici" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="tab.label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- Sorumlu Olduğu Müvekkiller (Veri Filtreleme Güvenlik Duvarı) --}}
                <div x-show="!duzenlenenKullanici.yonetici">
                    <h5 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Sorumlu Olduğu Müvekkiller (Veri Filtreleme Yetkisi)
                    </h5>
                    <p class="text-[11px] text-gray-500 mb-3">Bu personel "Tüm Tahsilatlar" gibi genel listelerde sadece aşağıda seçtiğiniz müvekkillerin verilerini görebilir. Hiçbir seçim yapılmazsa, güvenlik gereği <strong>hiçbir veri</strong> göremez.</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800/50 max-h-56 overflow-y-auto custom-scrollbar">
                        <template x-for="m in muvekkilOranlari" :key="'sorumlu_' + m.muvekkil_id">
                            <label class="flex items-center gap-2 p-2 rounded hover:bg-white dark:hover:bg-gray-800 border border-transparent hover:border-gray-200 dark:hover:border-gray-700 cursor-pointer transition-all shadow-sm">
                                <input type="checkbox" :value="m.muvekkil_id" x-model="duzenlenenKullanici.sorumlu_muvekkiller" 
                                       class="w-4 h-4 text-purple-600 bg-white border-gray-300 rounded focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600">
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate" x-text="m.muvekkil_ad"></span>
                            </label>
                        </template>
                        <div x-show="muvekkilOranlari.length === 0" class="text-xs text-amber-600 col-span-full">Sistemde kayıtlı müvekkil bulunamadı.</div>
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80 flex justify-end gap-3">
                <button @click="kullaniciDuzenlemeModalAcik = false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">İptal</button>
                <button @click="kullaniciDetayYetkiKaydet()" :disabled="kaydediliyor" class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm disabled:opacity-50 flex items-center gap-2">
                    <svg x-show="!kaydediliyor" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="kaydediliyor ? 'Kaydediliyor...' : 'Yetkileri Kaydet'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- B. YENİ HACİZCİ EKLE MODALI --}}
    <div x-show="yeniHacizciModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Hacizci Ekle</h4>
                <button @click="yeniHacizciModalAcik = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ad Soyad *</label>
                    <input type="text" x-model="yeniHacizciForm.ad_soyad" placeholder="Örn: Ahmet Yılmaz" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sicil No</label>
                    <input type="text" x-model="yeniHacizciForm.sicil_no" placeholder="İsteğe bağlı" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Başlangıç Kademesi *</label>
                    <select x-model="yeniHacizciForm.kademe" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none">
                        <template x-for="kademe in kademeler" :key="kademe.kademe">
                            <option :value="kademe.kademe" x-text="kademe.kademe_adi + ' (' + kademe.kademe + ')'"></option>
                        </template>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 flex justify-end gap-2">
                <button @click="yeniHacizciModalAcik = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">İptal</button>
                <button @click="yeniHacizciKaydet()" :disabled="kaydediliyor || !yeniHacizciForm.ad_soyad" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg disabled:opacity-50">Ekle</button>
            </div>
        </div>
    </div>

    {{-- C. YENİ KULLANICI EKLE MODALI --}}
    <div x-show="yeniKullaniciModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Sistem Kullanıcısı</h4>
                <button @click="yeniKullaniciModalAcik = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6 space-y-4">
                <div><label class="block text-sm font-medium mb-1">Ad Soyad *</label><input type="text" x-model="yeniKullaniciForm.name" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
                <div><label class="block text-sm font-medium mb-1">E-Posta *</label><input type="email" x-model="yeniKullaniciForm.email" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
                <div><label class="block text-sm font-medium mb-1">Şifre *</label><input type="password" x-model="yeniKullaniciForm.password" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" id="is_admin_check" x-model="yeniKullaniciForm.is_admin" class="w-4 h-4 rounded border-gray-300 text-amber-600">
                    <label for="is_admin_check" class="text-sm font-medium cursor-pointer">Sistem Yöneticisi (Admin) olsun mu?</label>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 flex justify-end gap-2">
                <button @click="yeniKullaniciModalAcik = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-200 rounded-lg">İptal</button>
                <button @click="yeniKullaniciKaydet()" :disabled="kaydediliyor || !yeniKullaniciForm.name || !yeniKullaniciForm.email || !yeniKullaniciForm.password" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg disabled:opacity-50">Ekle</button>
            </div>
        </div>
    </div>

    {{-- D. ŞİFRE DEĞİŞTİR MODALI --}}
    <div x-show="sifreDegistirModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Şifre Değiştir</h4>
                <button @click="sifreDegistirModalAcik = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-amber-50 text-amber-800 p-3 rounded-lg text-sm border border-amber-100"><span class="font-semibold" x-text="sifreDegisecekKullanici?.ad"></span> adlı kullanıcının şifresini değiştiriyorsunuz.</div>
                <div><label class="block text-sm font-medium mb-1">Yeni Şifre *</label><input type="password" x-model="sifreDegistirForm.password" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 flex justify-end gap-2">
                <button @click="sifreDegistirModalAcik = false" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded-lg">İptal</button>
                <button @click="sifreDegistirKaydet()" :disabled="kaydediliyor || !sifreDegistirForm.password" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg disabled:opacity-50">Güncelle</button>
            </div>
        </div>
    </div>

    {{-- E. YENİ KADEME SİSTEMİ EKLE MODALI --}}
    <div x-show="yeniKademeModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-sm border border-gray-200 dark:border-gray-700" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Kademe Ekle</h4>
                <button @click="yeniKademeModalAcik = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6 space-y-4">
                <div><label class="block text-sm font-medium mb-1">Kademe Numarası *</label><input type="number" min="1" x-model="yeniKademeForm.kademe_no" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
                <div><label class="block text-sm font-medium mb-1">Varsayılan Prim Oranı (%)</label><input type="number" min="0" step="0.01" x-model="yeniKademeForm.varsayilan_prim_orani" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 flex justify-end gap-2">
                <button @click="yeniKademeModalAcik = false" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded-lg">İptal</button>
                <button @click="yeniKademeKaydet()" :disabled="kaydediliyor || !yeniKademeForm.kademe_no" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg disabled:opacity-50">Oluştur</button>
            </div>
        </div>
    </div>

    {{-- F. YENİ AŞAMA EKLE MODALI --}}
    <div x-show="yeniAsamaModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-sm border border-gray-200 dark:border-gray-700" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Aşama Ekle</h4>
                <button @click="yeniAsamaModalAcik = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Kademe *</label>
                    <select x-model="yeniAsamaForm.kademe" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none">
                        <option value="">Seçiniz</option>
                        <template x-for="kademe in kademeler" :key="'asama_ekle_'+kademe.kademe">
                            <option :value="kademe.kademe" x-text="kademe.kademe_adi"></option>
                        </template>
                    </select>
                </div>
                <div><label class="block text-sm font-medium mb-1">Eşik Tutarı *</label><input type="number" min="0" step="0.01" x-model="yeniAsamaForm.esik_tutari" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
                <div><label class="block text-sm font-medium mb-1">Prim Oranı (%) *</label><input type="number" min="0" max="100" step="0.01" x-model="yeniAsamaForm.prim_orani" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 flex justify-end gap-2">
                <button @click="yeniAsamaModalAcik = false" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded-lg">İptal</button>
                <button @click="yeniAsamaEkle()" :disabled="!yeniAsamaForm.kademe" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg disabled:opacity-50">Ekle</button>
            </div>
        </div>
    </div>

    {{-- G. YENİ PORTFÖY EKLE MODALI --}}
    <div x-show="yeniPortfoyModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-sm border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Portföy Ekle</h4>
                <button @click="yeniPortfoyModalAcik = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Müvekkil *</label>
                    <select x-model="yeniPortfoyForm.muvekkil_id" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none">
                        <option value="">Seçiniz</option>
                        <template x-for="m in muvekkilOranlari" :key="m.muvekkil_id">
                            <option :value="m.muvekkil_id" x-text="m.muvekkil_ad"></option>
                        </template>
                    </select>
                </div>
                <div><label class="block text-sm font-medium mb-1">Portföy Adı *</label><input type="text" x-model="yeniPortfoyForm.ad" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
                <div><label class="block text-sm font-medium mb-1">Portföy Kodu</label><input type="text" x-model="yeniPortfoyForm.kod" placeholder="İsteğe bağlı" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none"></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 flex justify-end gap-2">
                <button @click="yeniPortfoyModalAcik = false" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 rounded-lg">İptal</button>
                <button @click="yeniPortfoyKaydet()" :disabled="kaydediliyor || !yeniPortfoyForm.ad || !yeniPortfoyForm.muvekkil_id" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg disabled:opacity-50">Ekle</button>
            </div>
        </div>
    </div>

</div>
</template>

<script>
function yetkiYonetimiModal() {
    return {
        acik: false,
        yukleniyor: false,
        kaydediliyor: false,
        aktifSekme: 'kullanicilar',
        
        // --- MASTER-DETAIL KULLANICI YETKİ MODALI ---
        kullaniciDuzenlemeModalAcik: false,
        duzenlenenKullanici: {},

        // --- DİĞER MODALLAR VE FORMLAR ---
        yeniHacizciModalAcik: false,
        yeniHacizciForm: { ad_soyad: '', sicil_no: '', kademe: 'kademe_1' },
        
        yeniKullaniciModalAcik: false,
        yeniKullaniciForm: { name: '', email: '', password: '', is_admin: false },
        
        sifreDegistirModalAcik: false,
        seciliKullanici: null,
        sifreDegistirForm: { password: '' },

        yeniKademeModalAcik: false,
        yeniKademeForm: { kademe_no: '', varsayilan_prim_orani: '' },

        yeniKademePayModalAcik: false,
        yeniKademePayForm: { ust_kademe: '', alt_kademe: '', ust_kademe_orani: 60, alt_kademe_orani: 40, aktif: true },

        yeniAsamaModalAcik: false,
        yeniAsamaForm: { kademe: '', esik_tutari: '', prim_orani: '' },

        yeniPortfoyModalAcik: false,
        yeniPortfoyForm: { muvekkil_id: '', ad: '', kod: '' },

        // --- VERİ LİSTELERİ ---
        kullanicilar: [],
        tabTanimlari: [],
        kademeler: [],
        hacizciKademeleri: [],
        kademePayOranlari: [],
        kademePrimAsamalari: [],
        muvekkilOranlari: [],
        auditKayitlari: [],
        portfoyler: [],

        // 1. ANA VERİ YÜKLEME FONKSİYONU
        async yukle() {
            this.yukleniyor = true;
            try {
                const [kullaniciRes, primRes] = await Promise.all([
                    fetch('/tahsilat/yetki', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
                    fetch('/tahsilat/yetki/prim-ayarlar', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
                ]);

                if (!kullaniciRes.ok) throw new Error(await this.hataMesaji(kullaniciRes));
                if (!primRes.ok) throw new Error(await this.hataMesaji(primRes));

                const kullaniciData = await kullaniciRes.json();
                this.kullanicilar = Array.isArray(kullaniciData) ? kullaniciData : (kullaniciData.kullanicilar ?? []);
                this.tabTanimlari = Array.isArray(kullaniciData.tab_tanimlari) ? kullaniciData.tab_tanimlari : [];
                
                // Kullanıcıları formatla ve Sorumlu Müvekkilleri hazırla
                this.kullanicilar = this.kullanicilar.map((kul) => ({
                    ...kul,
                    yonetici: Boolean(kul.yonetici),
                    protokol_duzenleyebilir: Boolean(kul.protokol_duzenleyebilir),
                    toplu_protokol_ekleyebilir: Boolean(kul.toplu_protokol_ekleyebilir),
                    tab_permissions: kul.tab_permissions ?? {},
                    sorumlu_muvekkiller: kul.sorumlu_muvekkiller ? [...kul.sorumlu_muvekkiller] : [],
                }));

                const primData = await primRes.json();
                this.kademeler = (primData.kademeler ?? []).map((k) => ({
                    ...k,
                    varsayilan_prim_orani: this.toNumber(k.varsayilan_prim_orani),
                    aktif: Boolean(k.aktif),
                }));

                this.hacizciKademeleri = (primData.hacizci_kademeleri ?? []).map((h) => ({
                    ...h,
                    aktif: Boolean(h.aktif),
                }));

                this.kademePayOranlari = (primData.kademe_pay_oranlari ?? []).map((p) => ({
                    ...p,
                    ust_kademe_orani: this.toNumber(p.ust_kademe_orani),
                    alt_kademe_orani: this.toNumber(p.alt_kademe_orani),
                    aktif: Boolean(p.aktif),
                }));

                this.kademePrimAsamalari = (primData.kademe_prim_asamalari ?? []).map((a) => ({
                    ...a,
                    asama_no: parseInt(a.asama_no, 10) || 1,
                    esik_tutari: this.toNumber(a.esik_tutari),
                    prim_orani: this.toNumber(a.prim_orani),
                    aktif: Boolean(a.aktif),
                })).sort((sol, sag) => {
                    const kademeFark = this.kademeEtiketi(sol.kademe).localeCompare(this.kademeEtiketi(sag.kademe), 'tr');
                    if (kademeFark !== 0) return kademeFark;
                    return this.toNumber(sol.asama_no) - this.toNumber(sag.asama_no);
                });

                this.muvekkilOranlari = (primData.muvekkil_oranlari ?? []).map((m) => ({
                    ...m,
                    prim_orani: m.prim_orani === null ? '' : this.toNumber(m.prim_orani),
                    aktif: Boolean(m.aktif),
                }));

                this.portfoyler = (primData.portfoyler ?? []).map((p) => ({
                    ...p,
                    aktif: Boolean(p.aktif),
                }));

                this.auditKayitlari = primData.audit_kayitlari ?? [];
            } catch (error) {
                alert(error.message || 'Veriler yüklenemedi.');
            } finally {
                this.yukleniyor = false;
            }
        },

        // 2. MASTER-DETAIL DETAYLI YETKİ YÖNETİMİ
        kullaniciYetkiModalAc(kul) {
            // Referansı kopararak kopyasını alıyoruz, iptal derse liste bozulmasın.
            this.duzenlenenKullanici = JSON.parse(JSON.stringify(kul));
            if (!this.duzenlenenKullanici.tab_permissions) this.duzenlenenKullanici.tab_permissions = {};
            if (!this.duzenlenenKullanici.sorumlu_muvekkiller) this.duzenlenenKullanici.sorumlu_muvekkiller = [];
            
            // Tüm sekmeleri varsayılan olarak tanımla
            this.tabTanimlari.forEach(tab => {
                if (this.duzenlenenKullanici.tab_permissions[tab.key] === undefined) {
                    this.duzenlenenKullanici.tab_permissions[tab.key] = false;
                }
            });

            this.kullaniciDuzenlemeModalAcik = true;
        },

        async kullaniciDetayYetkiKaydet() {
            this.kaydediliyor = true;
            try {
                // Sorumlu müvekkiller dizisindeki string ID'leri integer'a çevir
                const sorumlu = Array.isArray(this.duzenlenenKullanici.sorumlu_muvekkiller) 
                    ? this.duzenlenenKullanici.sorumlu_muvekkiller.map(id => parseInt(id, 10)).filter(id => !isNaN(id))
                    : [];

                const res = await fetch('/tahsilat/yetki/' + this.duzenlenenKullanici.id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        tahsilat_olusturabilir: Boolean(this.duzenlenenKullanici.tahsilat_olusturabilir),
                        protokol_olusturabilir: Boolean(this.duzenlenenKullanici.protokol_olusturabilir),
                        protokol_duzenleyebilir: Boolean(this.duzenlenenKullanici.protokol_duzenleyebilir),
                        toplu_protokol_ekleyebilir: Boolean(this.duzenlenenKullanici.toplu_protokol_ekleyebilir),
                        tahsilat_takip_sorumlusu: Boolean(this.duzenlenenKullanici.tahsilat_takip_sorumlusu),
                        aktif: Boolean(this.duzenlenenKullanici.aktif),
                        tab_permissions: this.duzenlenenKullanici.tab_permissions ?? {},
                        sorumlu_muvekkiller: sorumlu, // <-- GÜVENLİK DUVARI
                    }),
                });

                if (!res.ok) throw new Error(await this.hataMesaji(res));

                alert('Kullanıcı yetkileri başarıyla güncellendi.');
                this.kullaniciDuzenlemeModalAcik = false;
                await this.yukle(); // Listeyi yenile
            } catch (error) {
                alert(error.message || 'Yetki güncellenemedi.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        // Ana listedeki hızlı (inline) aktif/pasif vs. güncellemeleri
        async hizliDurumGuncelle(kul) {
            try {
                await this.kullaniciYetkiKaydet(kul);
            } catch (error) {
                kul.aktif = !kul.aktif; // Hata alırsak UI'ı eski haline getir
                alert(error.message || 'Durum güncellenemedi.');
            }
        },

        async yetkiGuncelle(kul, alan, deger) {
            const onceki = kul[alan];
            kul[alan] = deger;
            try {
                await this.kullaniciYetkiKaydet(kul);
            } catch (error) {
                kul[alan] = onceki;
                alert(error.message || 'Yetki güncellenemedi.');
            }
        },

        tabYetkisiVarMi(kul, tabAnahtari) {
            if (!kul || !tabAnahtari) return false;
            if (kul.yonetici) return true;
            return Boolean(kul.tab_permissions?.[tabAnahtari]);
        },

        async tabYetkiGuncelle(kul, tabAnahtari, goruntuleyebilir) {
            if (kul?.yonetici) return;
            const oncekiMap = { ...(kul.tab_permissions ?? {}) };
            kul.tab_permissions = { ...oncekiMap, [tabAnahtari]: Boolean(goruntuleyebilir) };
            try {
                await this.kullaniciYetkiKaydet(kul);
            } catch (error) {
                kul.tab_permissions = oncekiMap;
                alert(error.message || 'Tab yetkisi güncellenemedi.');
            }
        },

        async kullaniciYetkiKaydet(kul) {
            const res = await fetch('/tahsilat/yetki/' + kul.id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    tahsilat_olusturabilir: Boolean(kul.tahsilat_olusturabilir),
                    protokol_olusturabilir: Boolean(kul.protokol_olusturabilir),
                    protokol_duzenleyebilir: Boolean(kul.protokol_duzenleyebilir),
                    toplu_protokol_ekleyebilir: Boolean(kul.toplu_protokol_ekleyebilir),
                    tahsilat_takip_sorumlusu: Boolean(kul.tahsilat_takip_sorumlusu),
                    aktif: Boolean(kul.aktif ?? true),
                    tab_permissions: kul.tab_permissions ?? {},
                    sorumlu_muvekkiller: kul.sorumlu_muvekkiller ?? [],
                }),
            });

            if (!res.ok) throw new Error(await this.hataMesaji(res));
        },

        // 3. PRİM VE KADEME AYARLARI KAYIT İŞLEMLERİ
        async kaydetKademePayOranlari() {
            for (const pay of this.kademePayOranlari) {
                const toplam = this.oranToplami(pay);
                if (Math.abs(toplam - 100) > 0.01) {
                    alert(this.kademeEtiketi(pay.ust_kademe) + ' / ' + this.kademeEtiketi(pay.alt_kademe) + ' için oran toplamı 100 olmalıdır.');
                    return;
                }
            }

            this.kaydediliyor = true;
            try {
                const payload = {
                    pay_oranlari: this.kademePayOranlari.map((p) => ({
                        ust_kademe: p.ust_kademe,
                        alt_kademe: p.alt_kademe,
                        ust_kademe_orani: this.toNumber(p.ust_kademe_orani),
                        alt_kademe_orani: this.toNumber(p.alt_kademe_orani),
                        aktif: Boolean(p.aktif),
                    })),
                };

                const res = await fetch('/tahsilat/yetki/prim-ayarlar/kademe-pay', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) throw new Error(await this.hataMesaji(res));
                alert('Kademe arası pay oranları kaydedildi.');
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        async kaydetKademePrimAsamalari() {
            const grupMap = {};
            for (const asama of this.kademePrimAsamalari) {
                const kademe = String(asama.kademe ?? '');
                if (!grupMap[kademe]) grupMap[kademe] = [];
                grupMap[kademe].push(asama);
            }

            for (const [kademe, satirlar] of Object.entries(grupMap)) {
                const sirali = [...satirlar].sort((a, b) => this.toNumber(a.asama_no) - this.toNumber(b.asama_no));
                const asamaListesi = sirali.map((s) => this.toNumber(s.asama_no));
                const beklenen = Array.from({length: satirlar.length}, (_, i) => i + 1).join(',');

                if (asamaListesi.join(',') !== beklenen) {
                    alert(this.kademeEtiketi(kademe) + ' için aşama numaraları sırayla gitmelidir.');
                    return;
                }

                let oncekiEsik = -1;
                for (const satir of sirali) {
                    const esik = this.toNumber(satir.esik_tutari);
                    if (esik <= oncekiEsik) {
                        alert(this.kademeEtiketi(kademe) + ' için eşik tutarları giderek artmalıdır.');
                        return;
                    }
                    oncekiEsik = esik;
                }
            }

            this.kaydediliyor = true;
            try {
                const payload = {
                    asamalar: this.kademePrimAsamalari.map((a) => ({
                        kademe: a.kademe,
                        asama_no: parseInt(a.asama_no, 10) || 1,
                        esik_tutari: this.toNumber(a.esik_tutari),
                        prim_orani: this.toNumber(a.prim_orani),
                        aktif: Boolean(a.aktif),
                    })),
                };

                const res = await fetch('/tahsilat/yetki/prim-ayarlar/kademe-asama', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) throw new Error(await this.hataMesaji(res));
                alert('Kademe prim aşama ayarları kaydedildi.');
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        async kaydetHacizciKademeleri() {
            this.kaydediliyor = true;
            try {
                const payload = {
                    hacizciler: this.hacizciKademeleri.map((h) => ({
                        hacizci_id: h.hacizci_id,
                        kademe: h.kademe,
                        aktif: Boolean(h.aktif),
                    })),
                };

                const res = await fetch('/tahsilat/yetki/prim-ayarlar/hacizci-kademe', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) throw new Error(await this.hataMesaji(res));
                alert('Hacizci kademeleri güncellendi.');
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        async kaydetMuvekkilOranlari() {
            this.kaydediliyor = true;
            try {
                const payload = {
                    muvekkil_oranlari: this.muvekkilOranlari.map((m) => ({
                        muvekkil_id: m.muvekkil_id,
                        prim_orani: m.prim_orani === '' || m.prim_orani === null ? null : this.toNumber(m.prim_orani),
                        aktif: Boolean(m.aktif),
                    })),
                };

                const res = await fetch('/tahsilat/yetki/prim-ayarlar/muvekkil-oranlari', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) throw new Error(await this.hataMesaji(res));
                alert('Müvekkil bazlı prim oranları kaydedildi.');
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        // 4. EKLEME MODALLARI (HACİZCİ, KULLANICI, PORTFÖY VS.)
        yeniHacizciModalAc() {
            this.yeniHacizciForm = { ad_soyad: '', sicil_no: '', kademe: 'kademe_1' };
            this.yeniHacizciModalAcik = true;
        },

        async yeniHacizciKaydet() {
            this.kaydediliyor = true;
            try {
                const res = await fetch('/tahsilat/yetki/prim-ayarlar/hacizci-ekle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(this.yeniHacizciForm),
                });
                if (!res.ok) throw new Error(await this.hataMesaji(res));

                alert('Yeni hacizci başarıyla eklendi!');
                this.yeniHacizciModalAcik = false;
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Hacizci eklenirken hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        async yeniKullaniciKaydet() {
            this.kaydediliyor = true;
            try {
                const res = await fetch('/tahsilat/yetki/kullanici-ekle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(this.yeniKullaniciForm),
                });
                if (!res.ok) throw new Error(await this.hataMesaji(res));
                
                alert('Kullanıcı başarıyla eklendi!');
                this.yeniKullaniciModalAcik = false;
                this.yeniKullaniciForm = { name: '', email: '', password: '', is_admin: false };
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        sifreDegistirModalAc(kul) {
            this.seciliKullanici = kul;
            this.sifreDegistirForm = { password: '' };
            this.sifreDegistirModalAcik = true;
        },

        async sifreDegistirKaydet() {
            this.kaydediliyor = true;
            try {
                const res = await fetch(`/tahsilat/yetki/${this.seciliKullanici.id}/sifre-degistir`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(this.sifreDegistirForm),
                });
                if (!res.ok) throw new Error(await this.hataMesaji(res));
                
                alert('Şifre başarıyla güncellendi!');
                this.sifreDegistirModalAcik = false;
            } catch (error) {
                alert(error.message || 'Hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        async yeniKademeKaydet() {
            this.kaydediliyor = true;
            try {
                const res = await fetch('/tahsilat/yetki/prim-ayarlar/kademe-ekle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(this.yeniKademeForm),
                });
                if (!res.ok) throw new Error(await this.hataMesaji(res));
                
                alert('Yeni kademe ve sistem bağlantıları başarıyla oluşturuldu!');
                this.yeniKademeModalAcik = false;
                this.yeniKademeForm = { kademe_no: '', varsayilan_prim_orani: '' };
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        yeniAsamaModalAc() {
            this.yeniAsamaForm = { kademe: '', esik_tutari: '', prim_orani: '' };
            this.yeniAsamaModalAcik = true;
        },

        yeniAsamaEkle() {
            if (!this.yeniAsamaForm.kademe) return;
            const mevcutAsamalar = this.kademePrimAsamalari.filter(a => a.kademe === this.yeniAsamaForm.kademe);
            const maxAsama = mevcutAsamalar.reduce((max, asama) => Math.max(max, this.toNumber(asama.asama_no)), 0);
            const yeniAsamaNo = maxAsama + 1;

            this.kademePrimAsamalari.push({
                kademe: this.yeniAsamaForm.kademe,
                asama_no: yeniAsamaNo,
                esik_tutari: this.toNumber(this.yeniAsamaForm.esik_tutari),
                prim_orani: this.toNumber(this.yeniAsamaForm.prim_orani),
                aktif: true
            });

            this.kademePrimAsamalari.sort((sol, sag) => {
                const kademeFark = this.kademeEtiketi(sol.kademe).localeCompare(this.kademeEtiketi(sag.kademe), 'tr');
                if (kademeFark !== 0) return kademeFark;
                return this.toNumber(sol.asama_no) - this.toNumber(sag.asama_no);
            });

            this.yeniAsamaModalAcik = false;
        },

        yeniKademePayModalAc() {
            this.yeniKademePayForm = { ust_kademe: '', alt_kademe: '', ust_kademe_orani: 60, alt_kademe_orani: 40, aktif: true };
            this.yeniKademePayModalAcik = true;
        },

        yeniKademePayEkle() {
            const index = this.kademePayOranlari.findIndex(p => p.ust_kademe === this.yeniKademePayForm.ust_kademe && p.alt_kademe === this.yeniKademePayForm.alt_kademe);
            if (index !== -1) {
                alert("Bu kademe çifti için zaten bir kural var! Listeden yüzdesini düzenleyebilirsiniz.");
                return;
            }

            this.kademePayOranlari.push({
                ust_kademe: this.yeniKademePayForm.ust_kademe,
                alt_kademe: this.yeniKademePayForm.alt_kademe,
                ust_kademe_orani: this.toNumber(this.yeniKademePayForm.ust_kademe_orani),
                alt_kademe_orani: this.toNumber(this.yeniKademePayForm.alt_kademe_orani),
                aktif: true
            });

            this.yeniKademePayModalAcik = false;
        },

        // PORTFÖY İŞLEMLERİ
        yeniPortfoyModalAc() {
            this.yeniPortfoyForm = { muvekkil_id: '', ad: '', kod: '' };
            this.yeniPortfoyModalAcik = true;
        },

        async yeniPortfoyKaydet() {
            this.kaydediliyor = true;
            try {
                const res = await fetch('/tahsilat/yetki/prim-ayarlar/portfoy-ekle', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify(this.yeniPortfoyForm),
                });
                if (!res.ok) throw new Error(await this.hataMesaji(res));
                
                alert('Portföy başarıyla eklendi!');
                this.yeniPortfoyModalAcik = false;
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        async portfoyGuncelle(p) {
            this.kaydediliyor = true;
            try {
                const res = await fetch('/tahsilat/yetki/prim-ayarlar/portfoy/' + p.id, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ ad: p.ad, kod: p.kod, aktif: p.aktif }),
                });
                if (!res.ok) throw new Error(await this.hataMesaji(res));
                
                alert('Portföy güncellendi!');
            } catch (error) {
                alert(error.message || 'Hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        // 5. YARDIMCI METOTLAR
        oranToplami(pay) { return this.toNumber(pay.ust_kademe_orani) + this.toNumber(pay.alt_kademe_orani); },
        kademeEtiketi(kademe) { return kademe ? kademe.replace('kademe_', 'Kademe ') : '-'; },
        auditAlanEtiketi(alan) {
            const map = { kademe_varsayilan_orani: 'Kademe Varsayılan Prim', hacizci_kademe: 'Hacizci Kademe', kademe_pay_orani: 'Kademe Pay Oranı', kademe_prim_asama_orani: 'Kademe Prim Aşama', muvekkil_genel_prim_orani: 'Müvekkil Genel Prim' };
            return map[alan] ?? alan;
        },
        auditIslemEtiketi(islem) {
            const map = { create: 'Oluşturma', update: 'Güncelleme', delete: 'Silme' };
            return map[islem] ?? islem;
        },
        jsonOzet(veri) {
            if (!veri) return '-';
            try { return JSON.stringify(veri); } catch (_) { return String(veri); }
        },
        toNumber(value) {
            const parsed = parseFloat(value);
            return Number.isFinite(parsed) ? parsed : 0;
        },
        async hataMesaji(res) {
            try {
                const data = await res.json();
                if (data.message) return data.message;
                if (data.error) return data.error;
                if (data.errors) {
                    const first = Object.values(data.errors)[0];
                    if (Array.isArray(first) && first.length > 0) return first[0];
                }
            } catch (_) {}
            return 'Sunucu hatası oluştu.';
        }
    };
}
</script>