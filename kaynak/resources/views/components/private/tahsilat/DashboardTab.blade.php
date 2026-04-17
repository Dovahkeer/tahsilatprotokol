{{-- İzlence Tab --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="p-6 bg-slate-50/50 dark:bg-gray-900 min-h-screen" x-data="izlenceTab()" x-init="yukle()" @tahsilat-dashboard-yenile.window="yukle()">

    {{-- Yükleniyor Göstergesi --}}
    <div x-show="yukleniyor" class="flex justify-center py-12" x-cloak>
        <svg class="animate-spin w-10 h-10 text-emerald-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    <div x-show="!yukleniyor" class="space-y-6" x-cloak>
        
        {{-- Başlık ve Aksiyon Butonları --}}
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Komuta Merkezi</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Tahsilat istatistikleri, büyüme ivmesi ve iş günü bazlı takip paneli</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button @click="$dispatch('tahsilat-tab-degistir', { tab: 'tahsilat' })"
                    class="inline-flex items-center gap-2 h-9 px-4 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                    Günlük Tahsilat
                </button>
                <button @click="$dispatch('tahsilat-tab-degistir', { tab: 'tum_tahsilatlar' })"
                    class="inline-flex items-center gap-2 h-9 px-4 rounded-lg border border-blue-200 dark:border-blue-700 text-xs font-semibold text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors shadow-sm">
                    Tüm Tahsilatlar
                </button>
                <a href="/tahsilat/export/excel"
                    class="inline-flex items-center gap-2 h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors shadow-sm">
                    Tüm Tahsilatları Excel İndir
                </a>
            </div>
        </div>

        {{-- 1. SATIR: ÜST ÖZET KARTLARI (KURUMSAL TASARIM) --}}
        <div class="grid grid-cols-2 xl:grid-cols-6 gap-4">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bugün Tutar</div>
                <div class="mt-1 text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="formatPara(istatistikler.bugun_tahsilat_tutari)"></div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bu Ay Tutar</div>
                <div class="mt-1 text-lg font-black text-blue-600 dark:text-blue-400" x-text="formatPara(istatistikler.aylik_tahsilat_tutari)"></div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Yıllık Aylık Ortalama</div>
                <div class="mt-1 text-lg font-black text-indigo-600 dark:text-indigo-400" x-text="formatPara(istatistikler.yillik_aylik_ortalama_tahsilat_tutari)"></div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ay / Yıl Ortalaması</div>
                <div class="mt-1 text-lg font-black" :class="yuzdeSinif(istatistikler.yil_ortalamasina_gore_aylik_fark_yuzde)" x-text="formatYuzde(istatistikler.yil_ortalamasina_gore_aylik_fark_yuzde)"></div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm relative overflow-hidden">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Onay Bekleyen</div>
                <div class="mt-1 text-lg font-black text-amber-600 dark:text-amber-400" x-text="istatistikler.bekleyen_tahsilat_sayisi ?? 0"></div>
                <div x-show="(istatistikler.bekleyen_tahsilat_sayisi ?? 0) > 0" class="absolute top-0 right-0 w-12 h-12 bg-amber-500/10 rounded-bl-full -mr-6 -mt-6"></div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Toplam Aktif</div>
                <div class="mt-1 text-lg font-black text-gray-800 dark:text-gray-100" x-text="istatistikler.toplam_tahsilat_sayisi ?? 0"></div>
            </div>
        </div>

        {{-- 2. SATIR: MOMENTUM GRAFİĞİ --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-800 dark:text-gray-200">Tahsilat İvmesi (Günlük Kümülatif)</h3>
                    <div class="text-xs text-gray-500 mt-0.5">Ayın 1'inden 31'ine kadar olan büyüme hızı</div>
                </div>
                <div class="flex gap-4 text-[11px] font-bold uppercase tracking-wider bg-gray-50 dark:bg-gray-900/50 px-3 py-1.5 rounded-lg border border-gray-100 dark:border-gray-700">
                    <span class="flex items-center gap-1.5 text-emerald-600"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm"></span> Bu Ay</span>
                    <span class="flex items-center gap-1.5 text-blue-500"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm"></span> Geçen Ay</span>
                    <span class="flex items-center gap-1.5 text-red-500"><span class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-sm"></span> 2 Ay Önce</span>
                </div>
            </div>
            <div class="relative w-full h-[320px]">
                <canvas id="momentumChart"></canvas>
            </div>
        </div>

        {{-- 3. SATIR: MÜVEKKİL BAZLI BU AY BEKLENTİ --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm bg-white dark:bg-gray-800">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                <div class="text-base font-bold text-gray-800 dark:text-gray-200">Müvekkil Bazlı Bu Ay Beklenti (Risk Hesaplamalı)</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="beklentiAltBaslik()"></div>
            </div>

            <div class="p-5 space-y-4">
                {{-- YENİ İSİMLENDİRİLMİŞ BEKLENTİ KARTLARI --}}
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="rounded-xl border border-rose-100 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-900/20 p-4 relative overflow-hidden">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-rose-600/80 dark:text-rose-400">Bu Ay Vadesi Geçmiş (>7 Gün)</div>
                        <div class="mt-1 text-lg font-black text-rose-600 dark:text-rose-400" x-text="formatPara(muvekkilBeklenti().toplam_bu_ay_vadesi_gecmis_tutari)"></div>
                        <div class="text-[10px] font-medium text-rose-600/70 mt-1">Beklentiden silinmiştir (Riskli)</div>
                    </div>
                    <div class="rounded-xl border border-blue-100 dark:border-blue-900/50 bg-blue-50/50 dark:bg-blue-900/20 p-4">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-blue-600/80 dark:text-blue-400">Bu Ay Vadesi Gelecek</div>
                        <div class="mt-1 text-lg font-black text-blue-600 dark:text-blue-400" x-text="formatPara(muvekkilBeklenti().toplam_bu_ay_vadesi_gelecek_tutari)"></div>
                    </div>
                    <div class="rounded-xl border border-amber-100 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-900/20 p-4">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-amber-600/80 dark:text-amber-400">Vadesi Son 7 Gün Geçmiş</div>
                        <div class="mt-1 text-lg font-black text-amber-600 dark:text-amber-400" x-text="formatPara(muvekkilBeklenti().toplam_son_7_gun_vadesi_gecmis_tutari)"></div>
                    </div>
                    <div class="rounded-xl border border-emerald-100 dark:border-emerald-900/50 bg-emerald-50/50 dark:bg-emerald-900/20 p-4">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-emerald-600/80 dark:text-emerald-400">Toplam Beklenti (Gelecek + Son 7 Gün)</div>
                        <div class="mt-1 text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="formatPara(muvekkilBeklenti().toplam_beklenti_tutari)"></div>
                    </div>
                </div>

                {{-- YENİ İNTERAKTİF TABLO --}}
                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Müvekkil</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold text-rose-500 uppercase tracking-wider">Bu Ay Vadesi Geçmiş (>7 Gün)</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold text-blue-500 uppercase tracking-wider">Bu Ay Vadesi Gelecek</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold text-amber-500 uppercase tracking-wider">Vadesi Son 7 Gün Geçmiş</th>
                                <th class="px-4 py-3 text-right text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Toplam Beklenti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900/20">
                            <template x-if="muvekkilBeklentiSatirlari().length === 0">
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-400">Beklenti verisi bulunamadı.</td>
                                </tr>
                            </template>
                            <template x-for="satir in muvekkilBeklentiSatirlari()" :key="satir.muvekkil_id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200" x-text="satir.muvekkil_ad"></td>
                                    
                                    {{-- Ölü Geçmiş (> 7 Gün) Butonu --}}
                                    <td class="px-4 py-3 text-right">
                                        <template x-if="(satir.bu_ay_vadesi_gecmis_tutari ?? 0) > 0">
                                            <button type="button"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 font-bold rounded-lg hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors"
                                                @click="beklentiDetayModalAc(satir, 'gecmis', 'Bu Ay Vadesi Geçmiş (>7 Gün)')">
                                                <span x-text="formatPara(satir.bu_ay_vadesi_gecmis_tutari)"></span>
                                            </button>
                                        </template>
                                        <template x-if="(satir.bu_ay_vadesi_gecmis_tutari ?? 0) <= 0">
                                            <span class="text-gray-400 font-normal" x-text="formatPara(0)"></span>
                                        </template>
                                    </td>

                                    {{-- Gelecek Butonu --}}
                                    <td class="px-4 py-3 text-right">
                                        <template x-if="(satir.bu_ay_vadesi_gelecek_tutari ?? 0) > 0">
                                            <button type="button"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors"
                                                @click="beklentiDetayModalAc(satir, 'gelecek', 'Bu Ay Vadesi Gelecek')">
                                                <span x-text="formatPara(satir.bu_ay_vadesi_gelecek_tutari)"></span>
                                            </button>
                                        </template>
                                        <template x-if="(satir.bu_ay_vadesi_gelecek_tutari ?? 0) <= 0">
                                            <span class="text-gray-400 font-normal" x-text="formatPara(0)"></span>
                                        </template>
                                    </td>

                                    {{-- Son 7 Gün Butonu --}}
                                    <td class="px-4 py-3 text-right">
                                        <template x-if="(satir.son_7_gun_vadesi_gecmis_tutari ?? 0) > 0">
                                            <button type="button"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 font-bold rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors"
                                                @click="beklentiDetayModalAc(satir, 'son_7', 'Vadesi Son 7 Gün Geçmiş')">
                                                <span x-text="formatPara(satir.son_7_gun_vadesi_gecmis_tutari)"></span>
                                            </button>
                                        </template>
                                        <template x-if="(satir.son_7_gun_vadesi_gecmis_tutari ?? 0) <= 0">
                                            <span class="text-gray-400 font-normal" x-text="formatPara(0)"></span>
                                        </template>
                                    </td>

                                    <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 font-bold" x-text="formatPara(satir.toplam_beklenti_tutari)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 4. SATIR: İŞ GÜNÜ BAZLI TAKİP VE SEGMENTLER --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm bg-white dark:bg-gray-800">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                <div class="text-base font-bold text-gray-800 dark:text-gray-200">İş Günü Bazlı Takip</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Bu ay, geçen ay ve iki ay önceye göre iş günü performansı ve segmentler</div>
            </div>

            <div class="p-5 space-y-5">
                {{-- Segment Kartları --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <template x-for="kart in segmentKartlari()" :key="kart.id">
                        <div class="rounded-xl border p-4 shadow-sm" :class="kart.kapsayici_sinif">
                            <div class="text-xs font-bold uppercase tracking-wider" :class="kart.baslik_sinif" x-text="kart.baslik"></div>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div class="rounded-lg border border-gray-100 dark:border-gray-600/50 bg-gray-50/50 dark:bg-gray-900/50 p-3">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Geçen Ay</div>
                                    <div class="mt-1 text-sm font-black text-gray-800 dark:text-gray-200" x-text="formatPara(kart.gecen_ay_tutar)"></div>
                                </div>
                                <div class="rounded-lg border border-gray-100 dark:border-gray-600/50 bg-gray-50/50 dark:bg-gray-900/50 p-3">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bu Ay</div>
                                    <div class="mt-1 text-sm font-black text-emerald-600 dark:text-emerald-400" x-text="formatPara(kart.bu_ay_tutar)"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Tablolar Bölümü --}}
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
                    
                    {{-- İş Günü Tablosu --}}
                    <div class="xl:col-span-2 xl:row-span-2 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Dönem</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Ay</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wider">İş Günü</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wider">Toplam Tahsilat</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-bold text-gray-500 uppercase tracking-wider">İş Günü Başı Ort.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900/20">
                                <template x-for="satir in isGunuSatirlari()" :key="satir.id">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200" x-text="satir.baslik"></td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400" x-text="ayEtiketiUzun(satir.ay)"></td>
                                        <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-gray-100" x-text="satir.is_gunu ?? 0"></td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400" x-text="formatPara(satir.toplam_tutar)"></td>
                                        <td class="px-4 py-3 text-right font-semibold text-blue-600 dark:text-blue-400" x-text="formatPara(satir.is_gunu_basi_ortalama)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Kümülatif Karşılaştırma Kartları --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 xl:grid-cols-1 gap-4">
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 p-4">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">Geçen Ay vs Bu Ay (Aynı İş Günü)</div>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">Geçen Ay:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200" x-text="formatPara(donemToplam('gecen_ay'))"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">Bu Ay:</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="formatPara(donemToplam('bu_ay'))"></span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="font-bold" :class="yuzdeSinif(aylikFarkYuzde('bu_ay', 'gecen_ay'))" x-text="formatYuzde(aylikFarkYuzde('bu_ay', 'gecen_ay'))"></span>
                                    <span class="font-bold" :class="yuzdeSinif(aylikFarkTutar('bu_ay', 'gecen_ay'))" x-text="formatParaSigned(aylikFarkTutar('bu_ay', 'gecen_ay'))"></span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 p-4">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">2 Ay Önce vs Bu Ay (Aynı İş Günü)</div>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">İki Ay Önce:</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200" x-text="formatPara(donemToplam('iki_ay_once'))"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">Bu Ay:</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="formatPara(donemToplam('bu_ay'))"></span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="font-bold" :class="yuzdeSinif(aylikFarkYuzde('bu_ay', 'iki_ay_once'))" x-text="formatYuzde(aylikFarkYuzde('bu_ay', 'iki_ay_once'))"></span>
                                    <span class="font-bold" :class="yuzdeSinif(aylikFarkTutar('bu_ay', 'iki_ay_once'))" x-text="formatParaSigned(aylikFarkTutar('bu_ay', 'iki_ay_once'))"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Müvekkil Bazlı Aylık Tahsilat Toplamı (Kaydırma Çubuğu Kaldırıldı) --}}
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex flex-col h-full">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">Müvekkil Aylık Toplam</div>
                            <div class="text-[10px] text-gray-500 mt-1">
                                Dönem: <span class="font-bold text-gray-700 dark:text-gray-300" x-text="ayEtiketiUzun(muvekkilAylikTahsilat().ay)"></span> | 
                                Toplam: <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="formatPara(muvekkilAylikTahsilat().toplam_tutar)"></span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                <thead class="bg-white dark:bg-gray-800 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Müvekkil</th>
                                        <th class="px-4 py-2 text-right text-[10px] font-bold text-gray-500 uppercase">Tahsilat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50 bg-white dark:bg-gray-900/20">
                                    <template x-if="muvekkilAylikTahsilatSatirlari().length === 0">
                                        <tr><td colspan="2" class="px-4 py-4 text-center text-xs text-gray-400">Kayıt bulunamadı.</td></tr>
                                    </template>
                                    <template x-for="satir in muvekkilAylikTahsilatSatirlari()" :key="satir.muvekkil_id ?? satir.muvekkil_ad">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td class="px-4 py-2 font-medium text-gray-700 dark:text-gray-300" x-text="satir.muvekkil_ad"></td>
                                            <td class="px-4 py-2 text-right font-bold text-emerald-600 dark:text-emerald-400" x-text="formatPara(satir.aylik_tahsilat_tutari)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BEKLENTİ DETAY MODALI (İçerisi seçilen butona göre filtrelenir) --}}
        <template x-teleport="body">
        <div x-show="beklentiDetayModal.acik"
             @keydown.escape.window="if (beklentiDetayModal.acik) beklentiDetayModalKapat()"
             class="fixed inset-0 z-50 overflow-y-auto"
             x-cloak>
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="beklentiDetayModalKapat()"></div>
            <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
                <div class="relative w-full max-w-6xl max-h-[calc(100vh-2rem)] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800 sm:max-h-[calc(100vh-3rem)]" @click.stop>
                    <div class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/50">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="beklentiDetayModal.kategoriBaslik"></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span class="font-bold text-gray-700 dark:text-gray-300" x-text="beklentiDetayModal.muvekkil_ad || '-'"></span>
                                <span x-show="beklentiDetayModal.vade_ay">- <span x-text="ayEtiketiUzun(beklentiDetayModal.vade_ay)"></span></span>
                            </p>
                        </div>
                        <button type="button"
                            @click="beklentiDetayModalKapat()"
                            class="h-8 w-8 inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-600 shadow-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                        <template x-if="beklentiDetayModal.yukleniyor">
                            <div class="py-16 flex flex-col items-center justify-center gap-3 text-sm text-gray-500 dark:text-gray-300">
                                <svg class="animate-spin w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V4a8 8 0 00-8 8z"></path></svg>
                                Veri yükleniyor, lütfen bekleyin...
                            </div>
                        </template>

                        <template x-if="!beklentiDetayModal.yukleniyor && !!beklentiDetayModal.hata">
                            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-text="beklentiDetayModal.hata"></div>
                        </template>

                        <template x-if="!beklentiDetayModal.yukleniyor && !beklentiDetayModal.hata">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 p-4 shadow-sm">
                                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Muvekkil</div>
                                        <div class="mt-1 text-base font-bold text-gray-900 dark:text-white" x-text="beklentiDetayModal.muvekkil_ad || '-'"></div>
                                    </div>
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 p-4 shadow-sm">
                                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Protokol Sayisi</div>
                                        <div class="mt-1 text-base font-bold text-indigo-600 dark:text-indigo-400" x-text="computedProtokoller().length"></div>
                                    </div>
                                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 p-4 shadow-sm">
                                        <div class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Kategori Toplamı</div>
                                        <div class="mt-1 text-base font-black" :class="beklentiDetayModal.kategori === 'gecmis' ? 'text-rose-600 dark:text-rose-400' : (beklentiDetayModal.kategori === 'gelecek' ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400')" x-text="formatPara(computedProtokoller().reduce((toplam, p) => toplam + p.protokol_kalan_tutar, 0))"></div>
                                    </div>
                                </div>

                                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Protokol No</th>
                                                <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Borclu</th>
                                                <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Muhatap</th>
                                                <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Vade Durumu</th>
                                                <th class="px-4 py-3 text-right text-[11px] font-bold text-gray-500 uppercase">Kalan Tutar</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900/20">
                                            <template x-if="computedProtokoller().length === 0">
                                                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Bu kategoriye ait protokol bulunamadı.</td></tr>
                                            </template>
                                            <template x-for="p in computedProtokoller()" :key="p.protokol_id">
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 align-top transition-colors">
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-gray-800 dark:text-gray-200" x-text="p.protokol_no || '-'"></div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-gray-800 dark:text-gray-200" x-text="p.borclu_adi || '-'"></div>
                                                        <div class="text-[11px] text-gray-500 mt-0.5" x-show="p.borclu_tckn_vkn" x-text="'TC/VKN: ' + p.borclu_tckn_vkn"></div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="font-medium text-gray-700 dark:text-gray-300" x-text="p.muhatap_adi || '-'"></div>
                                                        <div class="text-[11px] text-gray-500 mt-0.5" x-show="p.muhatap_telefon" x-text="p.muhatap_telefon"></div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="text-[11px] space-y-1">
                                                            <template x-for="t in (p.taksitler ?? [])" :key="t.taksit_id">
                                                                <div class="flex items-center gap-2" :class="beklentiDetayModal.kategori === 'gecmis' ? 'text-rose-600 dark:text-rose-400' : (beklentiDetayModal.kategori === 'gelecek' ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400')">
                                                                    <span class="w-1.5 h-1.5 rounded-full" :class="beklentiDetayModal.kategori === 'gecmis' ? 'bg-rose-500' : (beklentiDetayModal.kategori === 'gelecek' ? 'bg-blue-500' : 'bg-amber-500')"></span>
                                                                    <span x-text="'Taksit ' + (t.taksit_no ?? '-') + ':'"></span>
                                                                    <span class="font-bold" x-text="tarihEtiketiKisa(t.taksit_tarihi)"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-right">
                                                        <div class="font-black text-base" :class="beklentiDetayModal.kategori === 'gecmis' ? 'text-rose-600 dark:text-rose-400' : (beklentiDetayModal.kategori === 'gelecek' ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400')" x-text="formatPara(p.protokol_kalan_tutar)"></div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        </template>

    </div>
</div>

<script>
function izlenceTab() {
    return {
        yukleniyor: true,
        istatistikler: {},
        chartInstance: null,

        beklentiDetayModal: {
            acik: false,
            yukleniyor: false,
            hata: '',
            kategori: '', // 'gecmis', 'gelecek', 'son_7'
            kategoriBaslik: '',
            muvekkil_id: '',
            muvekkil_ad: '',
            hesap_tarihi: '',
            vade_ay: '',
            protokoller: [],
        },

        async yukle() {
            this.yukleniyor = true;
            try {
                const res = await fetch('/tahsilat/dashboard-data', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    this.istatistikler = await res.json();
                    
                    this.$nextTick(() => {
                        this.cizgiGrafikOlustur();
                    });
                }
            } finally {
                this.yukleniyor = false;
            }
        },

        cizgiGrafikOlustur() {
            const grafikVerisi = this.istatistikler?.grafik_verisi;
            if (!grafikVerisi) return;
            
            const ctx = document.getElementById('momentumChart');
            if (!ctx) return;

            if (this.chartInstance) {
                this.chartInstance.destroy();
            }

            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: grafikVerisi.labels,
                    datasets: [
                        {
                            label: 'Bu Ay',
                            data: grafikVerisi.bu_ay,
                            borderColor: '#10B981', // Emerald 500
                            backgroundColor: 'rgba(16, 185, 129, 0.05)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 0,
                            pointHitRadius: 10,
                        },
                        {
                            label: 'Geçen Ay',
                            data: grafikVerisi.gecen_ay,
                            borderColor: '#3B82F6', // Blue 500
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.4,
                            fill: false,
                            pointRadius: 0,
                            pointHitRadius: 10,
                        },
                        {
                            label: '2 Ay Önce',
                            data: grafikVerisi.iki_ay_once,
                            borderColor: '#EF4444', // Red 500
                            borderWidth: 1.5,
                            borderDash: [2, 4],
                            tension: 0.4,
                            fill: false,
                            pointRadius: 0,
                            pointHitRadius: 10,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                        y: {
                            border: { display: false },
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: {
                                font: { size: 10 },
                                callback: function(value) {
                                    if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                    if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                    return value;
                                }
                            }
                        }
                    }
                }
            });
        },

        isGunuAnalizi() { return this.istatistikler?.is_gunu_analizi ?? {}; },
        muvekkilBeklenti() { return this.istatistikler?.muvekkil_bazli_bu_ay_beklenti ?? {}; },
        muvekkilBeklentiSatirlari() {
            const satirlar = this.muvekkilBeklenti()?.satirlar ?? [];
            return Array.isArray(satirlar) ? satirlar : [];
        },
        beklentiGelecekTutari(satir) {
            const tutar = Number(satir?.bu_ay_vadesi_gelecek_tutari ?? satir?.bu_ay_vade_tutari ?? 0);
            return Number.isFinite(tutar) ? tutar : 0;
        },

        // YENİ: Kategori bazlı modal açma fonksiyonu
        async beklentiDetayModalAc(satir, kategori, kategoriBaslik) {
            const muvekkilId = String(satir?.muvekkil_id ?? '').trim();
            if (muvekkilId === '') return;

            this.beklentiDetayModal = {
                acik: true, yukleniyor: true, hata: '',
                kategori: kategori, kategoriBaslik: kategoriBaslik,
                muvekkil_id: muvekkilId, muvekkil_ad: String(satir?.muvekkil_ad ?? ''),
                hesap_tarihi: '', vade_ay: '', protokoller: [],
            };

            try {
                const params = new URLSearchParams();
                params.set('muvekkil_id', muvekkilId);
                const res = await fetch('/tahsilat/dashboard-beklenti-protokoller?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data?.message ?? data?.error ?? 'Beklenti detayları yüklenemedi.');

                const protokoller = Array.isArray(data?.protokoller) ? data.protokoller : [];
                this.beklentiDetayModal = {
                    ...this.beklentiDetayModal,
                    yukleniyor: false, hata: '',
                    muvekkil_ad: String(data?.muvekkil_ad ?? this.beklentiDetayModal.muvekkil_ad ?? ''),
                    hesap_tarihi: String(data?.hesap_tarihi ?? ''),
                    vade_ay: String(data?.vade_ay ?? ''),
                    protokoller: protokoller.map((protokol) => ({
                        ...protokol,
                        taksitler: Array.isArray(protokol?.taksitler)
                            ? protokol.taksitler.map((taksit) => ({
                                ...taksit,
                                kalan_tutar: Number(taksit?.kalan_tutar ?? 0),
                            })) : [],
                    })),
                };
            } catch (error) {
                this.beklentiDetayModal = {
                    ...this.beklentiDetayModal,
                    yukleniyor: false, hata: error?.message ?? 'Beklenti detayları yüklenemedi.',
                    protokoller: [],
                };
            }
        },

        // YENİ: Tıklanan kategoriye göre taksitleri filtreleyen fonksiyon
        computedProtokoller() {
            if (!this.beklentiDetayModal.protokoller) return [];
            
            const bugun = new Date();
            bugun.setHours(0, 0, 0, 0);
            
            const yediGunOnce = new Date();
            yediGunOnce.setDate(bugun.getDate() - 7);
            yediGunOnce.setHours(0, 0, 0, 0);

            const kategori = this.beklentiDetayModal.kategori;

            return this.beklentiDetayModal.protokoller.map(p => {
                const filtrelenmisTaksitler = (p.taksitler || []).filter(t => {
                    const tTarih = new Date(t.taksit_tarihi + 'T00:00:00');
                    if (kategori === 'gelecek') return tTarih >= bugun;
                    if (kategori === 'son_7') return tTarih < bugun && tTarih >= yediGunOnce;
                    if (kategori === 'gecmis') return tTarih < yediGunOnce;
                    return true;
                });

                if (filtrelenmisTaksitler.length === 0) return null;

                const yeniKalan = filtrelenmisTaksitler.reduce((acc, t) => acc + (Number(t.kalan_tutar) || 0), 0);

                return {
                    ...p,
                    taksitler: filtrelenmisTaksitler,
                    protokol_kalan_tutar: yeniKalan
                };
            }).filter(p => p !== null);
        },

        beklentiDetayModalKapat() { this.beklentiDetayModal.acik = false; },

        beklentiAltBaslik() {
            const beklenti = this.muvekkilBeklenti();
            const vadeAy = String(beklenti?.vade_ay ?? '');
            if (vadeAy === '') return 'Bu ay içinde vadesi gelen, vadesi gelecek ve son 7 gün vadesi geçmiş taksitlerin müvekkil bazlı toplamı';
            return this.ayEtiketiUzun(vadeAy) + ' için vadesi gelen, vadesi gelecek ve son 7 gün gecikme toplamları';
        },

        kumulatifIsGunuAnalizi() { return this.istatistikler?.is_gunu_kumulatif_analizi ?? {}; },

        isGunuSatirlari() {
            const analiz = this.isGunuAnalizi();
            return [
                { id: 'bu_ay', baslik: 'Bu Ay', ...(analiz.bu_ay ?? {}) },
                { id: 'gecen_ay', baslik: 'Geçen Ay', ...(analiz.gecen_ay ?? {}) },
                { id: 'iki_ay_once', baslik: 'İki Ay Önce', ...(analiz.iki_ay_once ?? {}) },
            ];
        },

        donemToplam(donemKey) {
            const kumulatif = Number(this.kumulatifIsGunuAnalizi()?.[donemKey]?.kumulatif_tutar ?? NaN);
            if (Number.isFinite(kumulatif)) return kumulatif;
            return Number(this.isGunuAnalizi()?.[donemKey]?.toplam_tutar ?? 0);
        },

        aylikFarkTutar(guncelDonemKey, bazDonemKey) { return this.donemToplam(guncelDonemKey) - this.donemToplam(bazDonemKey); },

        aylikFarkYuzde(guncelDonemKey, bazDonemKey) {
            const guncel = this.donemToplam(guncelDonemKey);
            const baz = this.donemToplam(bazDonemKey);
            if (Math.abs(baz) < 0.0001) return null;
            return Number((((guncel - baz) / baz) * 100).toFixed(2));
        },

        segmentKartlari() {
            const segmentler = this.istatistikler?.segment_izlence ?? {};
            return [
                {
                    id: 'gsd', baslik: 'GSD Durumu',
                    kapsayici_sinif: 'border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800',
                    baslik_sinif: 'text-gray-500 dark:text-gray-400',
                    veri: segmentler.gsd ?? {},
                },
                {
                    id: 'tum_varlik_yonetim', baslik: 'Tüm Varlık Yönetimi',
                    kapsayici_sinif: 'border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800',
                    baslik_sinif: 'text-gray-500 dark:text-gray-400',
                    veri: segmentler.tum_varlik_yonetim ?? {},
                },
                {
                    id: 'tum_faktoringler', baslik: 'Tüm Faktoringler',
                    kapsayici_sinif: 'border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800',
                    baslik_sinif: 'text-gray-500 dark:text-gray-400',
                    veri: segmentler.tum_faktoringler ?? {},
                },
            ].map((kart) => ({
                ...kart,
                bu_ay_tutar: Number(kart.veri?.bu_ay_tutar ?? 0),
                gecen_ay_tutar: Number(kart.veri?.gecen_ay_tutar ?? 0),
            }));
        },

        muvekkilAylikTahsilat() { return this.istatistikler?.muvekkil_bazli_aylik_tahsilat ?? {}; },
        muvekkilAylikTahsilatSatirlari() {
            const satirlar = this.muvekkilAylikTahsilat()?.satirlar ?? [];
            return Array.isArray(satirlar) ? satirlar : [];
        },

        ayEtiketiKisa(ayKey) {
            if (!ayKey || typeof ayKey !== 'string') return '-';
            const d = new Date(ayKey + '-01T00:00:00');
            if (Number.isNaN(d.getTime())) return ayKey;
            return new Intl.DateTimeFormat('tr-TR', { month: 'short' }).format(d);
        },

        ayEtiketiUzun(ayKey) {
            if (!ayKey || typeof ayKey !== 'string') return '-';
            const d = new Date(ayKey + '-01T00:00:00');
            if (Number.isNaN(d.getTime())) return ayKey;
            return new Intl.DateTimeFormat('tr-TR', { month: 'long', year: 'numeric' }).format(d);
        },

        tarihEtiketiKisa(tarih) {
            if (!tarih || typeof tarih !== 'string') return '-';
            const d = new Date(tarih + 'T00:00:00');
            if (Number.isNaN(d.getTime())) return '-';
            return new Intl.DateTimeFormat('tr-TR', { day: '2-digit', month: '2-digit' }).format(d);
        },

        yuzdeSinif(value) {
            if (value === null || value === undefined || Number.isNaN(Number(value))) return 'text-gray-500';
            if (Number(value) > 0) return 'text-emerald-600 dark:text-emerald-400';
            if (Number(value) < 0) return 'text-rose-600 dark:text-rose-400';
            return 'text-gray-600 dark:text-gray-300';
        },

        formatYuzde(value) {
            if (value === null || value === undefined || Number.isNaN(Number(value))) return 'Veri yok';
            const sayi = Number(value);
            const prefix = sayi > 0 ? '+' : '';
            return prefix + sayi.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
        },

        trendMetni(value) {
            if (value === null || value === undefined || Number.isNaN(Number(value))) return 'Karşılaştırma için yeterli veri yok.';
            if (Number(value) > 0) return 'Yükseliş trendi';
            if (Number(value) < 0) return 'Düşüş trendi';
            return 'Dengeli trend';
        },

        formatPara(deger) {
            const sayi = Number(deger ?? 0);
            if (!Number.isFinite(sayi)) return '0,00 TL';
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(sayi);
        },

        formatParaSigned(deger) {
            const sayi = Number(deger ?? 0);
            if (!Number.isFinite(sayi)) return '0,00 TL';
            const formatted = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(Math.abs(sayi));
            if (sayi > 0) return '+' + formatted;
            if (sayi < 0) return '-' + formatted;
            return formatted;
        },
    };
}
</script>