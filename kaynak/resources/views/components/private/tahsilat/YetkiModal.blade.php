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
        <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tahsilat Yetki ve Prim Yönetimi</h3>
            <button @click="acik = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <div x-show="yukleniyor" class="flex justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            <div x-show="!yukleniyor" class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex flex-wrap gap-2">
                        <button type="button" @click="aktifSekme='kullanicilar'"
                            :class="aktifSekme === 'kullanicilar' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Kullanıcı Yetkileri
                        </button>
                        <button type="button" @click="aktifSekme='hacizci-kademe'"
                            :class="aktifSekme === 'hacizci-kademe' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Hacizci Kademeleri
                        </button>
                        <button type="button" @click="aktifSekme='kademe-pay'"
                            :class="aktifSekme === 'kademe-pay' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Kademe Arası Pay
                        </button>
                        <button type="button" @click="aktifSekme='kademe-asama'"
                            :class="aktifSekme === 'kademe-asama' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Kademe Prim Eşikleri
                        </button>
                        <button type="button" @click="aktifSekme='muvekkil'"
                            :class="aktifSekme === 'muvekkil' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Müvekkil Bazlı Prim
                        </button>
                        <button type="button" @click="aktifSekme='audit'"
                            :class="aktifSekme === 'audit' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Audit Geçmişi
                        </button>
                        <button type="button" @click="aktifSekme='portfoy'"
                            :class="aktifSekme === 'portfoy' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Portföy Yönetimi
                        </button>
                    </nav>
                </div>

                <div x-show="aktifSekme === 'kullanicilar'" class="space-y-4">
                    {{-- Üst Bilgi ve Aksiyon Butonları --}}
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Sistem Kullanıcıları ve Yetkiler</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Sisteme giriş yapabilen personeli, ekran yetkilerini ve aktiflik durumlarını yönetin.</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="yeniKullaniciModalAcik = true" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 border border-blue-200 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                Yeni Kullanıcı Ekle
                            </button>
                        </div>
                    </div>

                    {{-- Kullanıcılar Tablosu --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Kullanıcı</th>
                                        <th class="py-3 px-2 text-center font-semibold text-gray-600 dark:text-gray-300 text-[11px] leading-tight">Tahsilat<br>Oluşturabilir</th>
                                        <th class="py-3 px-2 text-center font-semibold text-gray-600 dark:text-gray-300 text-[11px] leading-tight">Protokol<br>Oluşturabilir</th>
                                        <th class="py-3 px-2 text-center font-semibold text-gray-600 dark:text-gray-300 text-[11px] leading-tight">Protokol<br>Düzenleyebilir</th>
                                        <th class="py-3 px-2 text-center font-semibold text-gray-600 dark:text-gray-300 text-[11px] leading-tight">Toplu Protokol<br>Ekleyebilir</th>
                                        <th class="py-3 px-2 text-center font-semibold text-gray-600 dark:text-gray-300 text-[11px] leading-tight">Tahsilat Takip<br>Sorumlusu</th>
                                        <template x-for="tab in tabTanimlari" :key="'head-' + tab.key">
                                            <th class="py-3 px-2 text-center font-semibold text-gray-600 dark:text-gray-300 text-[11px] leading-tight" x-text="tab.label"></th>
                                        </template>
                                        {{-- YENİ SÜTUNLAR: Durum ve Ayarlar --}}
                                        <th class="py-3 px-2 text-center font-semibold text-gray-600 dark:text-gray-300">Durum</th>
                                        <th class="py-3 px-2 text-center font-semibold text-gray-600 dark:text-gray-300">Ayarlar</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    <template x-for="kul in kullanicilar" :key="kul.id">
                                        <tr class="transition-all hover:bg-gray-50/80 dark:hover:bg-gray-800/80"
                                            :class="!kul.aktif ? 'bg-gray-50/50 grayscale-[20%] opacity-70' : ''">
                                            
                                            <td class="py-3 px-4">
                                                <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                    <span x-text="kul.ad"></span>
                                                    <span x-show="kul.yonetici" class="px-1.5 py-0.5 rounded text-[9px] bg-purple-100 text-purple-700 border border-purple-200">ADMİN</span>
                                                </div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5" x-text="kul.email"></div>
                                            </td>
                                            
                                            <td class="py-3 px-2 text-center">
                                                <input type="checkbox" :checked="kul.tahsilat_olusturabilir" @change="yetkiGuncelle(kul, 'tahsilat_olusturabilir', $event.target.checked)" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                            </td>
                                            <td class="py-3 px-2 text-center">
                                                <input type="checkbox" :checked="kul.protokol_olusturabilir" @change="yetkiGuncelle(kul, 'protokol_olusturabilir', $event.target.checked)" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                            </td>
                                            <td class="py-3 px-2 text-center">
                                                <input type="checkbox" :checked="kul.protokol_duzenleyebilir" @change="yetkiGuncelle(kul, 'protokol_duzenleyebilir', $event.target.checked)" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                            </td>
                                            <td class="py-3 px-2 text-center">
                                                <input type="checkbox" :checked="kul.toplu_protokol_ekleyebilir" :disabled="!kul.yonetici" @change="yetkiGuncelle(kul, 'toplu_protokol_ekleyebilir', $event.target.checked)" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer disabled:opacity-50">
                                            </td>
                                            <td class="py-3 px-2 text-center">
                                                <input type="checkbox" :checked="kul.tahsilat_takip_sorumlusu" @change="yetkiGuncelle(kul, 'tahsilat_takip_sorumlusu', $event.target.checked)" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                            </td>
                                            
                                            <template x-for="tab in tabTanimlari" :key="kul.id + '-' + tab.key">
                                                <td class="py-3 px-2 text-center">
                                                    <input type="checkbox" :checked="tabYetkisiVarMi(kul, tab.key)" :disabled="kul.yonetici" @change="tabYetkiGuncelle(kul, tab.key, $event.target.checked)" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer disabled:opacity-50">
                                                </td>
                                            </template>
                                            
                                            {{-- YENİ: AKTİF/PASİF BUTONU --}}
                                            <td class="py-3 px-2 flex justify-center">
                                                <button type="button" @click="kul.aktif = !kul.aktif; yetkiGuncelle(kul, 'aktif', kul.aktif)"
                                                    :class="kul.aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'"
                                                    class="relative inline-flex items-center justify-center px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full border transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-emerald-500 w-20">
                                                    <span class="relative flex h-2 w-2 mr-1.5">
                                                        <span :class="kul.aktif ? 'bg-emerald-500 shadow-[0_0_4px_#10b981]' : 'bg-red-500'" class="relative inline-flex rounded-full h-full w-full transition-colors"></span>
                                                    </span>
                                                    <span x-text="kul.aktif ? 'AKTİF' : 'PASİF'"></span>
                                                </button>
                                            </td>
                                            
                                            {{-- YENİ: ŞİFRE DEĞİŞTİR BUTONU --}}
                                            <td class="py-3 px-2 text-center border-l border-gray-100 dark:border-gray-700">
                                                <button type="button" @click="sifreDegistirModalAc(kul)" class="text-amber-600 hover:text-amber-800 p-1 rounded-md hover:bg-amber-50 dark:hover:bg-gray-700 transition-colors" title="Şifre Değiştir">
                                                    <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                    <div x-show="aktifSekme === 'hacizci-kademe'" class="space-y-4">
                    {{-- Üst Bilgi ve Aksiyon Butonları --}}
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
                            <button @click="kaydetHacizciKademeleri()"
                                class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm disabled:opacity-50 flex items-center gap-2"
                                :disabled="kaydediliyor">
                                <svg x-show="!kaydediliyor" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="kaydediliyor" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-text="kaydediliyor ? 'Kaydediliyor...' : 'Değişiklikleri Kaydet'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Tablo Konteyneri (Şık Kart Görünümü) --}}
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
                                        <tr class="transition-all hover:bg-gray-50/80 dark:hover:bg-gray-800/80" 
                                            :class="!kayit.aktif ? 'bg-gray-50/50 grayscale-[20%] opacity-80' : ''">
                                            
                                            <td class="py-3 px-5 text-gray-900 dark:text-white font-medium" x-text="kayit.ad_soyad"></td>
                                            
                                            <td class="py-3 px-5 text-gray-500 dark:text-gray-400" x-text="kayit.sicil_no || '-'"></td>
                                            
                                            <td class="py-3 px-5">
                                                <select x-model="kayit.kademe"
                                                    class="w-48 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-shadow shadow-sm cursor-pointer hover:border-gray-400">
                                                    <template x-for="kademe in kademeler" :key="kademe.kademe">
                                                        <option :value="kademe.kademe" x-text="kademe.kademe_adi + ' (' + kademe.kademe + ')'" :selected="kayit.kademe === kademe.kademe"></option>
                                                    </template>
                                                </select>
                                            </td>
                                            
                                            <td class="py-3 px-5 flex justify-center">
                                                {{-- YENİ: Modern Badge Tipi Aktif/Pasif Butonu --}}
                                                <button type="button" @click="kayit.aktif = !kayit.aktif"
                                                    :class="kayit.aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'"
                                                    class="relative inline-flex items-center justify-center px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-full border transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-emerald-500 w-24">
                                                    <span class="relative flex h-2 w-2 mr-2">
                                                        <span :class="kayit.aktif ? 'bg-emerald-500 shadow-[0_0_4px_#10b981]' : 'bg-red-500'" 
                                                              class="relative inline-flex rounded-full h-full w-full transition-colors"></span>
                                                    </span>
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

                {{-- KADEME ARASI PAY SEKMESİ --}}
                <div x-show="aktifSekme === 'kademe-pay'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Kademe ve Pay Yönetimi</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Sisteme yeni kademeler ekleyin ve kademeler arası ortak işlerdeki prim oranlarını belirleyin.</p>
                        </div>
                        <div class="flex gap-2">
                            {{-- YENİ KADEME EKLE BUTONU BURAYA GELDİ --}}
                            <button type="button" @click="yeniKademeModalAcik = true" class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 border border-blue-200 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Yeni Kademe Sistemi Ekle
                            </button>
                            
                            <button @click="kaydetKademePayOranlari()"
                                class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm disabled:opacity-50 flex items-center gap-2"
                                :disabled="kaydediliyor">
                                <svg x-show="!kaydediliyor" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="kaydediliyor" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-text="kaydediliyor ? 'Kaydediliyor...' : 'Değişiklikleri Kaydet'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Tablo Konteyneri --}}
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
                                        <tr class="transition-all hover:bg-gray-50/80 dark:hover:bg-gray-800/80" 
                                            :class="!pay.aktif ? 'bg-gray-50/50 grayscale-[20%] opacity-80' : ''">
                                            
                                            <td class="py-3 px-5 text-gray-900 dark:text-white font-medium" x-text="kademeEtiketi(pay.ust_kademe)"></td>
                                            <td class="py-3 px-5 text-gray-900 dark:text-white font-medium" x-text="kademeEtiketi(pay.alt_kademe)"></td>
                                            
                                            <td class="py-3 px-5">
                                                <input type="number" min="0" max="100" step="0.01" x-model="pay.ust_kademe_orani" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-shadow shadow-sm">
                                            </td>
                                            <td class="py-3 px-5">
                                                <input type="number" min="0" max="100" step="0.01" x-model="pay.alt_kademe_orani" class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition-shadow shadow-sm">
                                            </td>
                                            
                                            <td class="py-3 px-5 text-center">
                                                <span :class="Math.abs(oranToplami(pay) - 100) < 0.01 ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30' : 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30'" 
                                                      class="font-bold px-2 py-1 rounded-md" x-text="oranToplami(pay).toFixed(2)"></span>
                                            </td>
                                            
                                            <td class="py-3 px-5 flex justify-center">
                                                {{-- YENİ: Modern Badge Tipi Aktif/Pasif Butonu --}}
                                                <button type="button" @click="pay.aktif = !pay.aktif"
                                                    :class="pay.aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-500 border-red-200 hover:bg-red-100'"
                                                    class="relative inline-flex items-center justify-center px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-full border transition-all focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-emerald-500 w-24">
                                                    <span class="relative flex h-2 w-2 mr-2">
                                                        <span :class="pay.aktif ? 'bg-emerald-500 shadow-[0_0_4px_#10b981]' : 'bg-red-400'" class="relative inline-flex rounded-full h-full w-full transition-colors"></span>
                                                    </span>
                                                    <span x-text="pay.aktif ? 'AKTİF' : 'PASİF'"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- KADEME PRİM EŞİKLERİ SEKMESİ --}}
                <div x-show="aktifSekme === 'kademe-asama'" class="space-y-3">
                    <div class="flex justify-between items-center gap-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Kademeler için prim hakediş aşamalarını ve eşik tutarlarını yönetin. Aşamalar sıralı gitmelidir.
                        </p>
                        <div class="flex gap-2">
                            {{-- YENİ AŞAMA EKLE BUTONU BURAYA GELDİ --}}
                            <button type="button" @click="yeniAsamaModalAc()" class="px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 border border-emerald-200 rounded-lg text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Yeni Aşama Ekle
                            </button>
                            
                            <button @click="kaydetKademePrimAsamalari()"
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                                :disabled="kaydediliyor">
                                Kaydet
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Kademe</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Aşama</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Eşik Tutarı</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Prim Oranı (%)</th>
                                    <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400">Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="asama in kademePrimAsamalari" :key="asama.kademe + '_' + asama.asama_no">
                                    <tr>
                                        <td class="py-3 text-gray-900 dark:text-white" x-text="kademeEtiketi(asama.kademe)"></td>
                                        <td class="py-3 text-gray-700 dark:text-gray-300" x-text="'Asama ' + asama.asama_no"></td>
                                        <td class="py-3">
                                            <input type="number" min="0" step="0.01" x-model="asama.esik_tutari"
                                                class="w-40 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-3">
                                            <input type="number" min="0" max="100" step="0.01" x-model="asama.prim_orani"
                                                class="w-32 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-3 text-center">
                                            <input type="checkbox" x-model="asama.aktif"
                                                class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div x-show="aktifSekme === 'muvekkil'" class="space-y-3">
                    <div class="flex justify-end">
                        <button @click="kaydetMuvekkilOranlari()"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                            :disabled="kaydediliyor">
                            Kaydet
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Müvekkil</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Prim Orani (%)</th>
                                    <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400">Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="kayit in muvekkilOranlari" :key="kayit.muvekkil_id">
                                    <tr :class="!kayit.muvekkil_aktif ? 'opacity-50' : ''">
                                        <td class="py-3 text-gray-900 dark:text-white" x-text="kayit.muvekkil_ad"></td>
                                        <td class="py-3">
                                            <input type="number" min="0" max="100" step="0.01" x-model="kayit.prim_orani"
                                                placeholder="Boş bırak: kaldır"
                                                class="w-40 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-3 text-center">
                                            <input type="checkbox" x-model="kayit.aktif"
                                                class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="aktifSekme === 'audit'" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Tarih</th>
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Alan</th>
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">İşlem</th>
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Değiştiren</th>
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Detay</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-if="auditKayitlari.length === 0">
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-500 dark:text-gray-400">Audit kaydı bulunamadı.</td>
                                </tr>
                            </template>
                            <template x-for="kayit in auditKayitlari" :key="kayit.id">
                                <tr class="align-top">
                                    <td class="py-3 text-gray-900 dark:text-white whitespace-nowrap" x-text="new Date(kayit.created_at).toLocaleString('tr-TR')"></td>
                                    <td class="py-3 text-gray-700 dark:text-gray-300" x-text="auditAlanEtiketi(kayit.alan_tipi)"></td>
                                    <td class="py-3">
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                                            x-text="auditIslemEtiketi(kayit.islem_tipi)"></span>
                                    </td>
                                    <td class="py-3 text-gray-700 dark:text-gray-300" x-text="kayit.degistiren"></td>
                                    <td class="py-3 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                        <div x-show="kayit.hedef_anahtar" x-text="'Anahtar: ' + jsonOzet(kayit.hedef_anahtar)"></div>
                                        <div x-show="kayit.eski_deger" x-text="'Eski: ' + jsonOzet(kayit.eski_deger)"></div>
                                        <div x-show="kayit.yeni_deger" x-text="'Yeni: ' + jsonOzet(kayit.yeni_deger)"></div>
                                        <div x-show="kayit.aciklama" class="text-gray-500 dark:text-gray-500" x-text="kayit.aciklama"></div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- PORTFÖY YÖNETİMİ SEKMESİ --}}
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
                                            <td class="py-2 px-5 text-gray-600 dark:text-gray-400" x-text="p.muvekkil_ad"></td>
                                            <td class="py-2 px-5">
                                                <input type="text" x-model="p.ad" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                                            </td>
                                            <td class="py-2 px-5">
                                                <input type="text" x-model="p.kod" placeholder="Kod" class="w-full px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                                            </td>
                                            <td class="py-2 px-5 flex justify-center">
                                                <button type="button" @click="p.aktif = !p.aktif"
                                                    :class="p.aktif ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'"
                                                    class="relative inline-flex items-center justify-center px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-full border transition-all w-24">
                                                    <span class="relative flex h-2 w-2 mr-2">
                                                        <span :class="p.aktif ? 'bg-emerald-500' : 'bg-red-500'" class="relative inline-flex rounded-full h-full w-full"></span>
                                                    </span>
                                                    <span x-text="p.aktif ? 'AKTİF' : 'PASİF'"></span>
                                                </button>
                                            </td>
                                            <td class="py-2 px-5 text-center">
                                                <button @click="portfoyGuncelle(p)" class="text-amber-600 hover:text-amber-800 p-1.5 rounded-md hover:bg-amber-50 dark:hover:bg-gray-700 transition-colors" title="Kaydet">
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
            </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <button @click="acik = false"
                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                Kapat
            </button>
        </div>
    </div>
    {{-- YENİ HACİZCİ EKLE MODALI --}}
    <div x-show="yeniHacizciModalAcik" 
         class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
         x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Hacizci Ekle</h4>
                <button @click="yeniHacizciModalAcik = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ad Soyad *</label>
                    <input type="text" x-model="yeniHacizciForm.ad_soyad" placeholder="Örn: Ahmet Yılmaz" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sicil No</label>
                    <input type="text" x-model="yeniHacizciForm.sicil_no" placeholder="İsteğe bağlı" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Başlangıç Kademesi *</label>
                    <select x-model="yeniHacizciForm.kademe" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:ring-amber-500 focus:border-amber-500 outline-none transition-all">
                        <template x-for="kademe in kademeler" :key="kademe.kademe">
                            <option :value="kademe.kademe" x-text="kademe.kademe_adi + ' (' + kademe.kademe + ')'"></option>
                        </template>
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                <button @click="yeniHacizciModalAcik = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">İptal</button>
                <button @click="yeniHacizciKaydet()" :disabled="kaydediliyor || !yeniHacizciForm.ad_soyad" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">Ekle</button>
            </div>
        </div>
    </div>
    {{-- YENİ KULLANICI EKLE MODALI --}}
    <div x-show="yeniKullaniciModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Sistem Kullanıcısı</h4>
                <button @click="yeniKullaniciModalAcik = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ad Soyad *</label>
                    <input type="text" x-model="yeniKullaniciForm.name" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 focus:border-amber-500 outline-none">
                    </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">E-Posta *</label>
                    <input type="email" x-model="yeniKullaniciForm.email" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Şifre *</label>
                    <input type="password" x-model="yeniKullaniciForm.password" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" id="is_admin_check" x-model="yeniKullaniciForm.is_admin" class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                    <label for="is_admin_check" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Bu kullanıcı Sistem Yöneticisi (Admin) olsun mu?</label>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                <button @click="yeniKullaniciModalAcik = false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">İptal</button>
                <button @click="yeniKullaniciKaydet()" :disabled="kaydediliyor || !yeniKullaniciForm.name || !yeniKullaniciForm.email || !yeniKullaniciForm.password" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg disabled:opacity-50">Ekle</button>
            </div>
        </div>
    </div>

    {{-- ŞİFRE DEĞİŞTİR MODALI --}}
    <div x-show="sifreDegistirModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Şifre Değiştir</h4>
                <button @click="sifreDegistirModalAcik = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
                
            <div class="p-6 space-y-4">
                {{-- Bilgi Kutusu --}}
                <div class="bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200 p-3 rounded-lg text-sm border border-amber-100 dark:border-amber-800/30">
                    <span class="font-semibold" x-text="seciliKullanici?.ad"></span> adlı kullanıcının sistem giriş şifresini değiştiriyorsunuz.
                </div>
                    
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Yeni Şifre *</label>
                    <input type="password" x-model="sifreDegistirForm.password" placeholder="En az 6 karakter" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 focus:border-amber-500 outline-none transition-all shadow-sm">
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end gap-2">
                <button @click="sifreDegistirModalAcik = false" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 font-medium rounded-lg transition-colors">İptal</button>
                <button @click="sifreDegistirKaydet()" :disabled="kaydediliyor || !sifreDegistirForm.password" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm disabled:opacity-50">Güncelle</button>
            </div>
        </div>
    </div>

    {{-- YENİ KADEME SİSTEMİ EKLE MODALI --}}
    <div x-show="yeniKademeModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-sm border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Kademe Ekle</h4>
                <button @click="yeniKademeModalAcik = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200 p-3 rounded-lg text-xs border border-blue-100 dark:border-blue-800/30">
                    Yeni bir kademe eklediğinizde sistem otomatik olarak diğer kademelerle arasındaki %50-%50 pay oranlarını ve 3 adet prim eşiğini oluşturacaktır.
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kademe Numarası *</label>
                    <input type="number" min="1" x-model="yeniKademeForm.kademe_no" placeholder="Örn: 4" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 focus:border-amber-500 outline-none transition-all shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Varsayılan Prim Oranı (%)</label>
                    <input type="number" min="0" step="0.01" x-model="yeniKademeForm.varsayilan_prim_orani" placeholder="Örn: 10" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 focus:border-amber-500 outline-none transition-all shadow-sm">
                </div>
            </div>
                
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end gap-2">
                <button @click="yeniKademeModalAcik = false" class="px-4 py-2 text-sm text-gray-700 font-medium rounded-lg hover:bg-gray-200">İptal</button>
                <button @click="yeniKademeKaydet()" :disabled="kaydediliyor || !yeniKademeForm.kademe_no" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg disabled:opacity-50">Kademeyi Oluştur</button>
            </div>
        </div>
    </div>

    {{-- YENİ AŞAMA EKLE MODALI --}}
    <div x-show="yeniAsamaModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-sm border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Aşama Ekle</h4>
                <button @click="yeniAsamaModalAcik = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hangi Kademeye Eklenecek? *</label>
                    <select x-model="yeniAsamaForm.kademe" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 focus:border-amber-500 outline-none transition-all shadow-sm">
                        <option value="">Seçiniz</option>
                        <template x-for="kademe in kademeler" :key="'asama_ekle_'+kademe.kademe">
                            <option :value="kademe.kademe" x-text="kademe.kademe_adi"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Eşik Tutarı (TL) *</label>
                    <input type="number" min="0" step="0.01" x-model="yeniAsamaForm.esik_tutari" placeholder="Örn: 250000" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 focus:border-amber-500 outline-none transition-all shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prim Oranı (%) *</label>
                    <input type="number" min="0" max="100" step="0.01" x-model="yeniAsamaForm.prim_orani" placeholder="Örn: 15" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 focus:border-amber-500 outline-none transition-all shadow-sm">
                </div>
            </div>
                
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-end gap-2">
                <button @click="yeniAsamaModalAcik = false" class="px-4 py-2 text-sm text-gray-700 font-medium rounded-lg hover:bg-gray-200">İptal</button>
                <button @click="yeniAsamaEkle()" :disabled="!yeniAsamaForm.kademe" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg disabled:opacity-50">Listeye Ekle</button>
            </div>
        </div>
    </div>

    {{-- YENİ PORTFÖY EKLE MODALI --}}
    <div x-show="yeniPortfoyModalAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-sm border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Yeni Portföy Ekle</h4>
                <button @click="yeniPortfoyModalAcik = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
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
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Portföy Adı *</label>
                    <input type="text" x-model="yeniPortfoyForm.ad" placeholder="Örn: Garanti - GSD" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Portföy Kodu</label>
                    <input type="text" x-model="yeniPortfoyForm.kod" placeholder="İsteğe bağlı" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-amber-500 outline-none">
                </div>
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
        // YENİ EKLENEN DEĞİŞKENLER:
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

        kullanicilar: [],
        tabTanimlari: [],
        kademeler: [],
        hacizciKademeleri: [],
        kademePayOranlari: [],
        kademePrimAsamalari: [],
        muvekkilOranlari: [],
        auditKayitlari: [],
        portfoyler: [], // YENİ EKLENDİ

        async yukle() {
            this.yukleniyor = true;
            try {
                const [kullaniciRes, primRes] = await Promise.all([
                    fetch('/tahsilat/yetki', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
                    fetch('/tahsilat/yetki/prim-ayarlar', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
                ]);

                if (!kullaniciRes.ok) {
                    throw new Error(await this.hataMesaji(kullaniciRes));
                }
                if (!primRes.ok) {
                    throw new Error(await this.hataMesaji(primRes));
                }

                const kullaniciData = await kullaniciRes.json();
                this.kullanicilar = Array.isArray(kullaniciData)
                    ? kullaniciData
                    : (kullaniciData.kullanicilar ?? []);
                this.tabTanimlari = Array.isArray(kullaniciData.tab_tanimlari)
                    ? kullaniciData.tab_tanimlari
                    : [];
                this.kullanicilar = this.kullanicilar.map((kul) => ({
                    ...kul,
                    yonetici: Boolean(kul.yonetici),
                    protokol_duzenleyebilir: Boolean(kul.protokol_duzenleyebilir),
                    toplu_protokol_ekleyebilir: Boolean(kul.toplu_protokol_ekleyebilir),
                    tab_permissions: kul.tab_permissions ?? {},
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

                // YENİ EKLENEN SATIR:
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
            if (!kul || !tabAnahtari) {
                return false;
            }

            if (kul.yonetici) {
                return true;
            }

            return Boolean(kul.tab_permissions?.[tabAnahtari]);
        },

        async tabYetkiGuncelle(kul, tabAnahtari, goruntuleyebilir) {
            if (kul?.yonetici) {
                return;
            }

            const oncekiMap = { ...(kul.tab_permissions ?? {}) };
            kul.tab_permissions = {
                ...oncekiMap,
                [tabAnahtari]: Boolean(goruntuleyebilir),
            };

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
                    aktif: Boolean(kul.aktif ?? true), // <-- Artık dinamik!
                    tab_permissions: kul.tab_permissions ?? {},
                }),
            });

            if (!res.ok) {
                throw new Error(await this.hataMesaji(res));
            }
        },

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
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    throw new Error(await this.hataMesaji(res));
                }

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
                if (!grupMap[kademe]) {
                    grupMap[kademe] = [];
                }
                grupMap[kademe].push(asama);
            }

            for (const [kademe, satirlar] of Object.entries(grupMap)) {
                // Aşamaları numarasına göre sırala
                const sirali = [...satirlar].sort((a, b) => this.toNumber(a.asama_no) - this.toNumber(b.asama_no));
                const asamaListesi = sirali.map((s) => this.toNumber(s.asama_no));
                
                // Dinamik kontrol: Kaç satır varsa, 1'den o sayıya kadar ardışık olmalı (Örn 4 satır varsa: 1,2,3,4)
                const beklenen = Array.from({length: satirlar.length}, (_, i) => i + 1).join(',');

                if (asamaListesi.join(',') !== beklenen) {
                    alert(this.kademeEtiketi(kademe) + ' için aşama numaraları sırayla (1, 2, 3, 4...) gitmelidir. Arada silinmiş veya atlanmış aşama var.');
                    return;
                }

                let oncekiEsik = -1;
                for (const satir of sirali) {
                    const esik = this.toNumber(satir.esik_tutari);
                    if (esik <= oncekiEsik) {
                        alert(this.kademeEtiketi(kademe) + ' için eşik tutarları giderek artmalıdır (Aynı veya daha düşük olamaz).');
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
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    throw new Error(await this.hataMesaji(res));
                }

                alert('Kademe prim aşama ayarları kaydedildi.');
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        yeniKademePayModalAc() {
            this.yeniKademePayForm = { ust_kademe: '', alt_kademe: '', ust_kademe_orani: 60, alt_kademe_orani: 40, aktif: true };
            this.yeniKademePayModalAcik = true;
        },

        yeniKademePayGecerli() {
            const ust = this.yeniKademePayForm.ust_kademe;
            const alt = this.yeniKademePayForm.alt_kademe;
            const toplam = this.toNumber(this.yeniKademePayForm.ust_kademe_orani) + this.toNumber(this.yeniKademePayForm.alt_kademe_orani);
            
            // Seçimler dolu mu? Aynı kademeyi mi seçmiş? Toplamı 100 mü?
            return ust && alt && ust !== alt && Math.abs(toplam - 100) < 0.01;
        },

        yeniKademePayEkle() {
            // Listedeki var olan kayıtları kontrol et (Aynı kademe çifti 2 defa eklenmesin)
            const index = this.kademePayOranlari.findIndex(p => p.ust_kademe === this.yeniKademePayForm.ust_kademe && p.alt_kademe === this.yeniKademePayForm.alt_kademe);
            
            if (index !== -1) {
                alert("Bu kademe çifti için zaten bir kural var! Listeden yüzdesini düzenleyebilirsiniz.");
                return;
            }

            // Doğrudan Frontend listemizin sonuna ekliyoruz (Henüz DB'ye gitmedi)
            this.kademePayOranlari.push({
                ust_kademe: this.yeniKademePayForm.ust_kademe,
                alt_kademe: this.yeniKademePayForm.alt_kademe,
                ust_kademe_orani: this.toNumber(this.yeniKademePayForm.ust_kademe_orani),
                alt_kademe_orani: this.toNumber(this.yeniKademePayForm.alt_kademe_orani),
                aktif: true
            });

            this.yeniKademePayModalAcik = false;
        },

        async yeniKademeKaydet() {
            this.kaydediliyor = true;
            try {
                const res = await fetch('/tahsilat/yetki/prim-ayarlar/kademe-ekle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.yeniKademeForm),
                });
                
                if (!res.ok) throw new Error(await this.hataMesaji(res));
                
                alert('Yeni kademe ve sistem bağlantıları başarıyla oluşturuldu!');
                this.yeniKademeModalAcik = false;
                this.yeniKademeForm = { kademe_no: '', varsayilan_prim_orani: '' };
                await this.yukle(); // Matrisi yenileyip ekrana basar
            } catch (error) {
                alert(error.message || 'Hata oluştu.');
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
                        aktif: Boolean(h.aktif), // <-- BU SATIR EKLENDİ    
                    })),
                };

                const res = await fetch('/tahsilat/yetki/prim-ayarlar/hacizci-kademe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    throw new Error(await this.hataMesaji(res));
                }

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
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    throw new Error(await this.hataMesaji(res));
                }

                alert('Müvekkil bazlı prim oranları kaydedildi.');
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        oranToplami(pay) {
            return this.toNumber(pay.ust_kademe_orani) + this.toNumber(pay.alt_kademe_orani);
        },

        kademeEtiketi(kademe) {
            return kademe ? kademe.replace('kademe_', 'Kademe ') : '-';
        },

        auditAlanEtiketi(alan) {
            const map = {
                kademe_varsayilan_orani: 'Kademe Varsayılan Prim',
                hacizci_kademe: 'Hacizci Kademe',
                kademe_pay_orani: 'Kademe Pay Oranı',
                kademe_prim_asama_orani: 'Kademe Prim Aşama',
                muvekkil_genel_prim_orani: 'Müvekkil Genel Prim',
            };
            return map[alan] ?? alan;
        },

        auditIslemEtiketi(islem) {
            const map = {
                create: 'Oluşturma',
                update: 'Güncelleme',
                delete: 'Silme',
            };
            return map[islem] ?? islem;
        },

        jsonOzet(veri) {
            if (!veri) return '-';
            try {
                return JSON.stringify(veri);
            } catch (_) {
                return String(veri);
            }
        },

        yeniHacizciModalAc() {
            this.yeniHacizciForm = { ad_soyad: '', sicil_no: '', kademe: 'kademe_1' };
            this.yeniHacizciModalAcik = true;
        },

        async yeniHacizciKaydet() {
            this.kaydediliyor = true;
            try {
                const res = await fetch('/tahsilat/yetki/prim-ayarlar/hacizci-ekle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.yeniHacizciForm),
                });

                if (!res.ok) throw new Error(await this.hataMesaji(res));

                alert('Yeni hacizci başarıyla eklendi!');
                this.yeniHacizciModalAcik = false;
                await this.yukle(); // Listeyi otomatik yeniler
            } catch (error) {
                alert(error.message || 'Hacizci eklenirken hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        // Diğer metodlar
        async yeniKullaniciKaydet() {
            this.kaydediliyor = true;
            try {
                const res = await fetch('/tahsilat/yetki/kullanici-ekle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
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
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
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
            } catch (_) {
                // noop
            }

            return 'Sunucu hatası oluştu.';
        },

        yeniAsamaModalAc() {
            this.yeniAsamaForm = { kademe: '', esik_tutari: '', prim_orani: '' };
            this.yeniAsamaModalAcik = true;
        },

        yeniAsamaEkle() {
            if (!this.yeniAsamaForm.kademe) return;

            // Seçilen kademedeki mevcut aşamaları bul ve en büyük aşama numarasını al
            const mevcutAsamalar = this.kademePrimAsamalari.filter(a => a.kademe === this.yeniAsamaForm.kademe);
            const maxAsama = mevcutAsamalar.reduce((max, asama) => Math.max(max, this.toNumber(asama.asama_no)), 0);
            
            // Yeni eklenecek olanın numarası (Örn: En son 3 varsa bu 4 olacak)
            const yeniAsamaNo = maxAsama + 1;

            this.kademePrimAsamalari.push({
                kademe: this.yeniAsamaForm.kademe,
                asama_no: yeniAsamaNo,
                esik_tutari: this.toNumber(this.yeniAsamaForm.esik_tutari),
                prim_orani: this.toNumber(this.yeniAsamaForm.prim_orani),
                aktif: true
            });

            // Listeyi ekranda düzgün görünmesi için tekrar sırala
            this.kademePrimAsamalari.sort((sol, sag) => {
                const kademeFark = this.kademeEtiketi(sol.kademe).localeCompare(this.kademeEtiketi(sag.kademe), 'tr');
                if (kademeFark !== 0) return kademeFark;
                return this.toNumber(sol.asama_no) - this.toNumber(sag.asama_no);
            });

            this.yeniAsamaModalAcik = false;
        },

        yeniPortfoyModalAc() {
            this.yeniPortfoyForm = { muvekkil_id: '', ad: '', kod: '' };
            this.yeniPortfoyModalAcik = true;
        },

        async yeniPortfoyKaydet() {
            this.kaydediliyor = true;
            try {
                const res = await fetch('/tahsilat/yetki/prim-ayarlar/portfoy-ekle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.yeniPortfoyForm),
                });
                if (!res.ok) throw new Error(await this.hataMesaji(res));
                
                alert('Portföy başarıyla eklendi!');
                this.yeniPortfoyModalAcik = false;
                await this.yukle(); // Listeyi yeniler
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
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
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
    };
}
</script>











