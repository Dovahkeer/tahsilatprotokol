{{-- İzlence Tab --}}
<div class="p-6" x-data="izlenceTab()" x-init="yukle()" @tahsilat-dashboard-yenile.window="yukle()">

    <div x-show="yukleniyor" class="flex justify-center py-12">
        <svg class="animate-spin w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    <div x-show="!yukleniyor" class="space-y-5">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">İzlence</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Tahsilat istatistikleri, trendler ve iş günü bazlı takip paneli</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button @click="$dispatch('tahsilat-tab-degistir', { tab: 'tahsilat' })"
                    class="inline-flex items-center gap-2 h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-colors">
                    Günlük Tahsilat
                </button>
                <button @click="$dispatch('tahsilat-tab-degistir', { tab: 'tum_tahsilatlar' })"
                    class="inline-flex items-center gap-2 h-9 px-3 rounded-lg border border-blue-200 dark:border-blue-700 text-xs font-medium text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                    Tüm Tahsilatlar
                </button>
                <a href="/tahsilat/export/excel"
                    class="inline-flex items-center gap-2 h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Tüm Tahsilatları Excel İndir
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 xl:grid-cols-6 gap-2">
            <div class="rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-900/20 px-3 py-2.5">
                <div class="text-[10px] uppercase tracking-wide text-emerald-700/80 dark:text-emerald-300/80">Bugün Tutar</div>
                <div class="text-sm font-semibold text-emerald-700 dark:text-emerald-300" x-text="formatPara(istatistikler.bugun_tahsilat_tutari)"></div>
            </div>

            <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-900/20 px-3 py-2.5">
                <div class="text-[10px] uppercase tracking-wide text-blue-700/80 dark:text-blue-300/80">Bu Ay Tutar</div>
                <div class="text-sm font-semibold text-blue-700 dark:text-blue-300" x-text="formatPara(istatistikler.aylik_tahsilat_tutari)"></div>
            </div>

            <div class="rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50/70 dark:bg-indigo-900/20 px-3 py-2.5">
                <div class="text-[10px] uppercase tracking-wide text-indigo-700/80 dark:text-indigo-300/80">Yıllık Aylık Ortalama</div>
                <div class="text-sm font-semibold text-indigo-700 dark:text-indigo-300" x-text="formatPara(istatistikler.yillik_aylik_ortalama_tahsilat_tutari)"></div>
            </div>

            <div class="rounded-lg border border-fuchsia-200 dark:border-fuchsia-800 bg-fuchsia-50/70 dark:bg-fuchsia-900/20 px-3 py-2.5">
                <div class="text-[10px] uppercase tracking-wide text-fuchsia-700/80 dark:text-fuchsia-300/80">Ay / Yıl Ortalaması</div>
                <div class="text-sm font-semibold" :class="yuzdeSinif(istatistikler.yil_ortalamasina_gore_aylik_fark_yuzde)" x-text="formatYuzde(istatistikler.yil_ortalamasina_gore_aylik_fark_yuzde)"></div>
            </div>

            <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50/70 dark:bg-amber-900/20 px-3 py-2.5">
                <div class="text-[10px] uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Onay Bekleyen</div>
                <div class="text-sm font-semibold text-amber-700 dark:text-amber-300" x-text="istatistikler.bekleyen_tahsilat_sayisi ?? 0"></div>
            </div>

            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5">
                <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Toplam Aktif</div>
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="istatistikler.toplam_tahsilat_sayisi ?? 0"></div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20">
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Müvekkil Bazlı Bu Ay Beklenti</div>
                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="beklentiAltBaslik()"></div>
            </div>

            <div class="p-4 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                    <div class="rounded-lg border border-rose-200 dark:border-rose-800 bg-rose-50/60 dark:bg-rose-900/20 p-3">
                        <div class="text-[10px] uppercase tracking-wide text-rose-700/80 dark:text-rose-300/80">Bu Ay Vadesi Gelen</div>
                        <div class="mt-1 text-sm font-semibold text-rose-700 dark:text-rose-300" x-text="formatPara(muvekkilBeklenti().toplam_bu_ay_vadesi_gelen_tutari ?? muvekkilBeklenti().toplam_son_7_gun_vadesi_gecmis_tutari)"></div>
                    </div>
                    <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50/60 dark:bg-blue-900/20 p-3">
                        <div class="text-[10px] uppercase tracking-wide text-blue-700/80 dark:text-blue-300/80">Bu Ay Vadesi Gelecek</div>
                        <div class="mt-1 text-sm font-semibold text-blue-700 dark:text-blue-300" x-text="formatPara(muvekkilBeklenti().toplam_bu_ay_vadesi_gelecek_tutari ?? muvekkilBeklenti().toplam_bu_ay_vade_tutari)"></div>
                    </div>
                    <div class="rounded-lg border border-violet-200 dark:border-violet-800 bg-violet-50/60 dark:bg-violet-900/20 p-3">
                        <div class="text-[10px] uppercase tracking-wide text-violet-700/80 dark:text-violet-300/80">Son 7 Gün Vadesi Geçmiş</div>
                        <div class="mt-1 text-sm font-semibold text-amber-700 dark:text-amber-300" x-text="formatPara(muvekkilBeklenti().toplam_son_7_gun_vadesi_gecmis_tutari)"></div>
                    </div>
                    <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/20 p-3">
                        <div class="text-[10px] uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Toplam Beklenti</div>
                        <div class="mt-1 text-sm font-semibold text-amber-700 dark:text-amber-300" x-text="formatPara(muvekkilBeklenti().toplam_beklenti_tutari)"></div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Müvekkil</th>
                                <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">Bu Ay Vadesi Gelen</th>
                                <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">Bu Ay Vadesi Gelecek</th>
                                <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">Son 7 Gün Vadesi Geçmiş</th>
                                <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">Toplam Beklenti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-if="muvekkilBeklentiSatirlari().length === 0">
                                <tr>
                                    <td colspan="5" class="px-3 py-3 text-center text-xs text-gray-400">Beklenti verisi bulunamadı.</td>
                                </tr>
                            </template>
                            <template x-for="satir in muvekkilBeklentiSatirlari()" :key="satir.muvekkil_id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300" x-text="satir.muvekkil_ad"></td>
                                    <td class="px-3 py-2 text-right text-rose-700 dark:text-rose-300 font-medium" x-text="formatPara(satir.bu_ay_vadesi_gelen_tutari ?? satir.son_7_gun_vadesi_gecmis_tutari)"></td>
                                    <td class="px-3 py-2 text-right text-blue-700 dark:text-blue-300 font-medium">
                                        <template x-if="beklentiGelecekTutari(satir) > 0">
                                            <button type="button"
                                                class="inline-flex items-center gap-1 underline decoration-dotted underline-offset-2 hover:text-blue-800 dark:hover:text-blue-200"
                                                @click="beklentiDetayModalAc(satir)"
                                                x-text="formatPara(beklentiGelecekTutari(satir))"></button>
                                        </template>
                                        <template x-if="beklentiGelecekTutari(satir) <= 0">
                                            <span x-text="formatPara(0)"></span>
                                        </template>
                                    </td>
                                    <td class="px-3 py-2 text-right text-amber-700 dark:text-amber-300 font-medium" x-text="formatPara(satir.son_7_gun_vadesi_gecmis_tutari)"></td>
                                    <td class="px-3 py-2 text-right text-amber-700 dark:text-amber-300 font-semibold" x-text="formatPara(satir.toplam_beklenti_tutari)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20">
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">İş Günü Bazlı Takip</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Bu ay, geçen ay ve iki ay önceye göre iş günü ve tahsilat performansı</div>
            </div>

            <div class="p-4 space-y-3">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-2.5">
                    <template x-for="kart in segmentKartlari()" :key="kart.id">
                        <div class="rounded-lg border p-2.5" :class="kart.kapsayici_sinif">
                            <div class="text-[11px] uppercase tracking-wide" :class="kart.baslik_sinif" x-text="kart.baslik"></div>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <div class="rounded-md border border-white/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-900/30 p-2">
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Geçen Ay</div>
                                    <div class="mt-1 text-xs font-semibold text-gray-800 dark:text-gray-100" x-text="formatPara(kart.gecen_ay_tutar)"></div>
                                </div>
                                <div class="rounded-md border border-white/60 dark:border-gray-700/60 bg-white/70 dark:bg-gray-900/30 p-2">
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Bu Ay</div>
                                    <div class="mt-1 text-xs font-semibold text-gray-800 dark:text-gray-100" x-text="formatPara(kart.bu_ay_tutar)"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                    <div class="xl:col-span-2 xl:row-span-2 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Dönem</th>
                                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Ay</th>
                                    <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">İş Günü</th>
                                    <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">Toplam Tahsilat</th>
                                    <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">İş Günü Başı Ortalama</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="satir in isGunuSatirlari()" :key="satir.id">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300" x-text="satir.baslik"></td>
                                        <td class="px-3 py-2 text-gray-600 dark:text-gray-400" x-text="ayEtiketiUzun(satir.ay)"></td>
                                        <td class="px-3 py-2 text-right font-medium text-gray-800 dark:text-gray-100" x-text="satir.is_gunu ?? 0"></td>
                                        <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300" x-text="formatPara(satir.toplam_tutar)"></td>
                                        <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300" x-text="formatPara(satir.is_gunu_basi_ortalama)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 xl:grid-cols-1 gap-2.5">
                        <div class="rounded-lg border border-cyan-200 dark:border-cyan-800 bg-cyan-50/60 dark:bg-cyan-900/20 p-3">
                            <div class="text-[11px] uppercase tracking-wide text-cyan-700/80 dark:text-cyan-300/80">Geçen Ay / Bu Ay Aynı İş Günü Kümülatif Tahsilat</div>
                            <div class="mt-2 space-y-2 text-xs">
                                <div>
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Geçen Ay</div>
                                    <div class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="formatPara(donemToplam('gecen_ay'))"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Bu Ay</div>
                                    <div class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="formatPara(donemToplam('bu_ay'))"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Aylık Değişim</div>
                                    <div class="mt-0.5 text-sm font-semibold" :class="yuzdeSinif(aylikFarkTutar('bu_ay', 'gecen_ay'))" x-text="formatParaSigned(aylikFarkTutar('bu_ay', 'gecen_ay'))"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Aylık Yüzde Fark</div>
                                    <div class="mt-0.5 text-sm font-semibold" :class="yuzdeSinif(aylikFarkYuzde('bu_ay', 'gecen_ay'))" x-text="formatYuzde(aylikFarkYuzde('bu_ay', 'gecen_ay'))"></div>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border border-violet-200 dark:border-violet-800 bg-violet-50/60 dark:bg-violet-900/20 p-3">
                            <div class="text-[11px] uppercase tracking-wide text-violet-700/80 dark:text-violet-300/80">İki Ay Önce / Bu Ay Aynı İş Günü Kümülatif Tahsilat</div>
                            <div class="mt-2 space-y-2 text-xs">
                                <div>
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">İki Ay Önce</div>
                                    <div class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="formatPara(donemToplam('iki_ay_once'))"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Bu Ay</div>
                                    <div class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="formatPara(donemToplam('bu_ay'))"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Aylık Değişim</div>
                                    <div class="mt-0.5 text-sm font-semibold" :class="yuzdeSinif(aylikFarkTutar('bu_ay', 'iki_ay_once'))" x-text="formatParaSigned(aylikFarkTutar('bu_ay', 'iki_ay_once'))"></div>
                                </div>
                                <div>
                                    <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Aylık Yüzde Fark</div>
                                    <div class="mt-0.5 text-sm font-semibold" :class="yuzdeSinif(aylikFarkYuzde('bu_ay', 'iki_ay_once'))" x-text="formatYuzde(aylikFarkYuzde('bu_ay', 'iki_ay_once'))"></div>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border border-rose-200 dark:border-rose-800 bg-rose-50/60 dark:bg-rose-900/20 p-2.5">
                            <div class="text-[11px] uppercase tracking-wide text-rose-700/80 dark:text-rose-300/80">İş Günü Bazlı Tahsilat Trendi</div>
                            <div class="mt-1 text-base font-semibold" :class="yuzdeSinif(isGunuAnalizi().is_gunu_bazli_tahsilat_trend_yuzde)" x-text="formatYuzde(isGunuAnalizi().is_gunu_bazli_tahsilat_trend_yuzde)"></div>
                            <div class="text-xs text-rose-700/80 dark:text-rose-300/80 mt-1" x-text="trendMetni(isGunuAnalizi().is_gunu_bazli_tahsilat_trend_yuzde)"></div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-900/30 p-3">
                        <div class="flex flex-col gap-0.5">
                            <div class="text-[11px] uppercase tracking-wide text-slate-600 dark:text-slate-300">Müvekkil Bazlı Aylık Tahsilat Toplamı</div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400">
                                Dönem:
                                <span class="font-medium" x-text="ayEtiketiUzun(muvekkilAylikTahsilat().ay)"></span>
                                - Toplam:
                                <span class="font-medium" x-text="formatPara(muvekkilAylikTahsilat().toplam_tutar)"></span>
                            </div>
                        </div>
                        <div class="mt-2 max-h-52 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700/50 sticky top-0">
                                    <tr>
                                        <th class="px-2 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Müvekkil</th>
                                        <th class="px-2 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">Aylık Tahsilat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <template x-if="muvekkilAylikTahsilatSatirlari().length === 0">
                                        <tr>
                                            <td colspan="2" class="px-2 py-3 text-center text-xs text-gray-400">Kayıt bulunamadı.</td>
                                        </tr>
                                    </template>
                                    <template x-for="satir in muvekkilAylikTahsilatSatirlari()" :key="satir.muvekkil_id ?? satir.muvekkil_ad">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                            <td class="px-2 py-2 text-gray-700 dark:text-gray-300" x-text="satir.muvekkil_ad"></td>
                                            <td class="px-2 py-2 text-right font-medium text-gray-800 dark:text-gray-100" x-text="formatPara(satir.aylik_tahsilat_tutari)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="beklentiDetayModal.acik"
             @keydown.escape.window="if (beklentiDetayModal.acik) beklentiDetayModalKapat()"
             class="fixed inset-0 z-50"
             x-cloak>
            <div class="absolute inset-0 bg-black/60" @click="beklentiDetayModalKapat()"></div>
            <div class="relative min-h-full flex items-center justify-center p-4">
                <div class="relative w-full max-w-6xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Bu Ay Vadesi Gelecek Protokoller</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                <span class="font-medium" x-text="beklentiDetayModal.muvekkil_ad || '-'"></span>
                                <span x-show="beklentiDetayModal.vade_ay">- <span x-text="ayEtiketiUzun(beklentiDetayModal.vade_ay)"></span></span>
                            </p>
                        </div>
                        <button type="button"
                            @click="beklentiDetayModalKapat()"
                            class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/60">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-5 space-y-3 max-h-[80vh] overflow-y-auto">
                        <template x-if="beklentiDetayModal.yukleniyor">
                            <div class="py-12 flex items-center justify-center gap-2 text-sm text-gray-500 dark:text-gray-300">
                                <svg class="animate-spin w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V4a8 8 0 00-8 8z"></path>
                                </svg>
                                Veri yukleniyor...
                            </div>
                        </template>

                        <template x-if="!beklentiDetayModal.yukleniyor && !!beklentiDetayModal.hata">
                            <div class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-3 text-sm text-red-700 dark:text-red-300" x-text="beklentiDetayModal.hata"></div>
                        </template>

                        <template x-if="!beklentiDetayModal.yukleniyor && !beklentiDetayModal.hata">
                            <div class="space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50/60 dark:bg-blue-900/20 p-2.5">
                                        <div class="text-[10px] uppercase tracking-wide text-blue-700/80 dark:text-blue-300/80">Muvekkil</div>
                                        <div class="mt-0.5 text-sm font-semibold text-blue-700 dark:text-blue-300" x-text="beklentiDetayModal.muvekkil_ad || '-'"></div>
                                    </div>
                                    <div class="rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50/60 dark:bg-indigo-900/20 p-2.5">
                                        <div class="text-[10px] uppercase tracking-wide text-indigo-700/80 dark:text-indigo-300/80">Protokol Sayisi</div>
                                        <div class="mt-0.5 text-sm font-semibold text-indigo-700 dark:text-indigo-300" x-text="beklentiDetayModal.protokol_sayisi ?? 0"></div>
                                    </div>
                                    <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/20 p-2.5">
                                        <div class="text-[10px] uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Toplam Kalan Tutar</div>
                                        <div class="mt-0.5 text-sm font-semibold text-amber-700 dark:text-amber-300" x-text="formatPara(beklentiDetayModal.toplam_kalan_tutar)"></div>
                                    </div>
                                </div>

                                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Protokol No</th>
                                                <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Borclu</th>
                                                <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Muhatap</th>
                                                <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Vade</th>
                                                <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">Kalan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            <template x-if="(beklentiDetayModal.protokoller ?? []).length === 0">
                                                <tr>
                                                    <td colspan="5" class="px-3 py-3 text-center text-xs text-gray-400">Bu muvekkil icin bu ay vadesi gelecek protokol bulunamadi.</td>
                                                </tr>
                                            </template>
                                            <template x-for="p in (beklentiDetayModal.protokoller ?? [])" :key="p.protokol_id">
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 align-top">
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                                        <div class="font-medium" x-text="p.protokol_no || '-'"></div>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                                        <div class="font-medium" x-text="p.borclu_adi || '-'"></div>
                                                        <div class="text-[11px] text-gray-500 dark:text-gray-400" x-show="p.borclu_tckn_vkn" x-text="p.borclu_tckn_vkn"></div>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                                        <div class="font-medium" x-text="p.muhatap_adi || '-'"></div>
                                                        <div class="text-[11px] text-gray-500 dark:text-gray-400" x-show="p.muhatap_telefon" x-text="p.muhatap_telefon"></div>
                                                    </td>
                                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                                                        <div class="font-medium text-gray-700 dark:text-gray-200" x-text="tarihEtiketiKisa(p.en_yakin_vade_tarihi)"></div>
                                                        <div class="text-[11px] space-y-0.5 mt-1">
                                                            <template x-for="t in (p.taksitler ?? [])" :key="t.taksit_id">
                                                                <div class="text-gray-500 dark:text-gray-400">
                                                                    <span x-text="'Taksit ' + (t.taksit_no ?? '-')"></span>:
                                                                    <span x-text="tarihEtiketiKisa(t.taksit_tarihi)"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-2 text-right text-blue-700 dark:text-blue-300 font-semibold" x-text="formatPara(p.protokol_kalan_tutar)"></td>
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

    </div>
</div>

<script>
function izlenceTab() {
    return {
        yukleniyor: true,
        istatistikler: {},
        beklentiDetayModal: {
            acik: false,
            yukleniyor: false,
            hata: '',
            muvekkil_id: '',
            muvekkil_ad: '',
            hesap_tarihi: '',
            vade_ay: '',
            toplam_kalan_tutar: 0,
            protokol_sayisi: 0,
            protokoller: [],
        },

        async yukle() {
            try {
                const res = await fetch('/tahsilat/dashboard-data', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    this.istatistikler = await res.json();
                }
            } finally {
                this.yukleniyor = false;
            }
        },

        isGunuAnalizi() {
            return this.istatistikler?.is_gunu_analizi ?? {};
        },

        muvekkilBeklenti() {
            return this.istatistikler?.muvekkil_bazli_bu_ay_beklenti ?? {};
        },

        muvekkilBeklentiSatirlari() {
            const satirlar = this.muvekkilBeklenti()?.satirlar ?? [];
            return Array.isArray(satirlar) ? satirlar : [];
        },

        beklentiGelecekTutari(satir) {
            const tutar = Number(satir?.bu_ay_vadesi_gelecek_tutari ?? satir?.bu_ay_vade_tutari ?? 0);
            return Number.isFinite(tutar) ? tutar : 0;
        },

        async beklentiDetayModalAc(satir) {
            const muvekkilId = String(satir?.muvekkil_id ?? '').trim();
            if (muvekkilId === '') {
                return;
            }

            this.beklentiDetayModal = {
                acik: true,
                yukleniyor: true,
                hata: '',
                muvekkil_id: muvekkilId,
                muvekkil_ad: String(satir?.muvekkil_ad ?? ''),
                hesap_tarihi: '',
                vade_ay: '',
                toplam_kalan_tutar: 0,
                protokol_sayisi: 0,
                protokoller: [],
            };

            try {
                const params = new URLSearchParams();
                params.set('muvekkil_id', muvekkilId);

                const res = await fetch('/tahsilat/dashboard-beklenti-protokoller?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data?.message ?? data?.error ?? 'Beklenti detaylari yuklenemedi.');
                }

                const protokoller = Array.isArray(data?.protokoller) ? data.protokoller : [];
                this.beklentiDetayModal = {
                    ...this.beklentiDetayModal,
                    yukleniyor: false,
                    hata: '',
                    muvekkil_ad: String(data?.muvekkil_ad ?? this.beklentiDetayModal.muvekkil_ad ?? ''),
                    hesap_tarihi: String(data?.hesap_tarihi ?? ''),
                    vade_ay: String(data?.vade_ay ?? ''),
                    toplam_kalan_tutar: Number(data?.toplam_kalan_tutar ?? 0),
                    protokol_sayisi: Number(data?.protokol_sayisi ?? protokoller.length),
                    protokoller: protokoller.map((protokol) => ({
                        ...protokol,
                        protokol_kalan_tutar: Number(protokol?.protokol_kalan_tutar ?? 0),
                        taksitler: Array.isArray(protokol?.taksitler)
                            ? protokol.taksitler.map((taksit) => ({
                                ...taksit,
                                taksit_tutari: Number(taksit?.taksit_tutari ?? 0),
                                odenen_tutar: Number(taksit?.odenen_tutar ?? 0),
                                kalan_tutar: Number(taksit?.kalan_tutar ?? 0),
                            }))
                            : [],
                    })),
                };
            } catch (error) {
                this.beklentiDetayModal = {
                    ...this.beklentiDetayModal,
                    yukleniyor: false,
                    hata: error?.message ?? 'Beklenti detaylari yuklenemedi.',
                    toplam_kalan_tutar: 0,
                    protokol_sayisi: 0,
                    protokoller: [],
                };
            }
        },

        beklentiDetayModalKapat() {
            this.beklentiDetayModal.acik = false;
        },

        beklentiAltBaslik() {
            const beklenti = this.muvekkilBeklenti();
            const vadeAy = String(beklenti?.vade_ay ?? '');
            if (vadeAy === '') {
                return 'Bu ay içinde vadesi gelen, vadesi gelecek ve son 7 gün vadesi geçmiş taksitlerin müvekkil bazlı toplamı';
            }

            return this.ayEtiketiUzun(vadeAy) + ' için vadesi gelen, vadesi gelecek ve son 7 gün gecikme toplamları';
        },

        kumulatifIsGunuAnalizi() {
            return this.istatistikler?.is_gunu_kumulatif_analizi ?? {};
        },

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
            if (Number.isFinite(kumulatif)) {
                return kumulatif;
            }

            return Number(this.isGunuAnalizi()?.[donemKey]?.toplam_tutar ?? 0);
        },

        aylikFarkTutar(guncelDonemKey, bazDonemKey) {
            return this.donemToplam(guncelDonemKey) - this.donemToplam(bazDonemKey);
        },

        aylikFarkYuzde(guncelDonemKey, bazDonemKey) {
            const guncel = this.donemToplam(guncelDonemKey);
            const baz = this.donemToplam(bazDonemKey);

            if (Math.abs(baz) < 0.0001) {
                return null;
            }

            return Number((((guncel - baz) / baz) * 100).toFixed(2));
        },

        segmentKartlari() {
            const segmentler = this.istatistikler?.segment_izlence ?? {};

            return [
                {
                    id: 'gsd',
                    baslik: 'GSD Durumu',
                    kapsayici_sinif: 'border-sky-200 dark:border-sky-800 bg-sky-50/60 dark:bg-sky-900/20',
                    baslik_sinif: 'text-sky-700/80 dark:text-sky-300/80',
                    veri: segmentler.gsd ?? {},
                },
                {
                    id: 'tum_varlik_yonetim',
                    baslik: 'Tüm Varlık Yönetim Durumu',
                    kapsayici_sinif: 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/60 dark:bg-indigo-900/20',
                    baslik_sinif: 'text-indigo-700/80 dark:text-indigo-300/80',
                    veri: segmentler.tum_varlik_yonetim ?? {},
                },
                {
                    id: 'tum_faktoringler',
                    baslik: 'Tüm Faktoringler',
                    kapsayici_sinif: 'border-emerald-200 dark:border-emerald-800 bg-emerald-50/60 dark:bg-emerald-900/20',
                    baslik_sinif: 'text-emerald-700/80 dark:text-emerald-300/80',
                    veri: segmentler.tum_faktoringler ?? {},
                },
            ].map((kart) => ({
                ...kart,
                bu_ay_tutar: Number(kart.veri?.bu_ay_tutar ?? 0),
                gecen_ay_tutar: Number(kart.veri?.gecen_ay_tutar ?? 0),
            }));
        },

        muvekkilAylikTahsilat() {
            return this.istatistikler?.muvekkil_bazli_aylik_tahsilat ?? {};
        },

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
            if (value === null || value === undefined || Number.isNaN(Number(value))) {
                return 'text-gray-500 dark:text-gray-400';
            }

            if (Number(value) > 0) {
                return 'text-emerald-700 dark:text-emerald-300';
            }

            if (Number(value) < 0) {
                return 'text-red-700 dark:text-red-300';
            }

            return 'text-gray-700 dark:text-gray-200';
        },

        formatYuzde(value) {
            if (value === null || value === undefined || Number.isNaN(Number(value))) {
                return 'Veri yok';
            }

            const sayi = Number(value);
            const prefix = sayi > 0 ? '+' : '';
            return prefix + sayi.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
        },

        trendMetni(value) {
            if (value === null || value === undefined || Number.isNaN(Number(value))) {
                return 'Karşılaştırma için yeterli veri yok.';
            }

            if (Number(value) > 0) {
                return 'Yükseliş trendi';
            }

            if (Number(value) < 0) {
                return 'Düşüş trendi';
            }

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



