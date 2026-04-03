{{-- Protokol Tab --}}
<div class="p-6" x-data="protokolTab()" x-init="init()">

    {{-- Hizli Filtreler --}}
    <div class="mb-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20 p-3">
        @php
            $protokolYetkiVar = auth()->id() && \App\Models\TahsilatYetkiliKullanici
                ::where('user_id', auth()->id())
                ->where('protokol_olusturabilir', true)
                ->where('aktif', true)
                ->exists();
            $topluProtokolYetkiVar = (bool) auth()->user()->isYonetici();
            if ($topluProtokolYetkiVar
                && \Illuminate\Support\Facades\Schema::hasTable('tahsilat_yetkili_kullanicilar')
                && \Illuminate\Support\Facades\Schema::hasColumn('tahsilat_yetkili_kullanicilar', 'toplu_protokol_ekleyebilir')
            ) {
                $topluProtokolKaydi = \App\Models\TahsilatYetkiliKullanici::where('user_id', auth()->id())
                    ->where('aktif', true)
                    ->first();
                if ($topluProtokolKaydi) {
                    $topluProtokolYetkiVar = (bool) $topluProtokolKaydi->toplu_protokol_ekleyebilir;
                }
            }
        @endphp

        <div class="pb-1 relative">
            <div class="w-full flex items-center gap-1.5">
                <div class="relative w-[130px]">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.55a1 1 0 010 1.4L15 16m-6 0l-4.55-4.6a1 1 0 010-1.4L9 10"/>
                    </svg>
                    <input type="text"
                        x-model="filtre.protokol_no"
                        @input.debounce.400ms="yukle(1)"
                        placeholder="Protokol No"
                        class="w-full h-9 pl-8 pr-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                </div>

                <div class="relative w-[150px]" @click.outside="muvekkilDropdownAcik = false">
                    <button type="button"
                        @click="muvekkilDropdownAcik = !muvekkilDropdownAcik"
                        class="w-full h-9 pl-8 pr-7 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs text-left text-gray-700 dark:text-gray-200 flex items-center justify-between">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6m10 0H7"/>
                        </svg>
                        <span class="truncate" x-text="seciliMuvekkilEtiketi()"></span>
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="muvekkilDropdownAcik"
                        x-transition.opacity
                        x-cloak
                        class="absolute z-40 mt-1 w-[240px] rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg">
                        <div class="flex items-center justify-between px-2.5 py-2 border-b border-gray-100 dark:border-gray-700">
                            <button type="button" @click="tumMuvekkilSec()" class="text-[10px] font-medium text-amber-600 hover:text-amber-700">Tümünü Seç</button>
                            <button type="button" @click="muvekkilTemizle()" class="text-[10px] font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Temizle</button>
                        </div>
                        <div class="max-h-40 overflow-y-auto p-1.5 space-y-0.5">
                            <template x-for="m in muvekkiller" :key="m.id">
                                <label class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700/60 cursor-pointer">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                        :checked="filtre.muvekkil_ids.includes(m.id)"
                                        @change="muvekkilSecimToggle(m.id)">
                                    <span class="text-[11px] text-gray-700 dark:text-gray-200" x-text="m.ad"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="relative w-[150px]">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <select x-model="filtre.portfoy_id"
                        :disabled="portfoyYukleniyor"
                        @change="yukle(1)"
                        class="w-full h-9 pl-8 pr-7 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200 disabled:opacity-50">
                        <option value="">Tüm Portföyler</option>
                        <template x-for="p in portfoyler" :key="p.id">
                            <option :value="p.id" x-text="p.ad"></option>
                        </template>
                    </select>
                </div>

                <div class="relative w-[130px]">
                    <input type="month"
                        x-model="filtre.ay"
                        @change="yukle(1)"
                        class="w-full h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs text-gray-700 dark:text-gray-200">
                </div>

                <div class="inline-flex h-9 rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden bg-white dark:bg-gray-700">
                    <button type="button" @click="aktifDurumuSec('aktif')"
                        :class="filtre.aktif_durumu === 'aktif' ? 'bg-amber-500 text-white' : 'text-gray-600 dark:text-gray-300'"
                        class="px-2.5 text-xs font-medium inline-flex items-center gap-1 border-r border-gray-200 dark:border-gray-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Aktif
                    </button>
                    <button type="button" @click="aktifDurumuSec('pasif')"
                        :class="filtre.aktif_durumu === 'pasif' ? 'bg-amber-500 text-white' : 'text-gray-600 dark:text-gray-300'"
                        class="px-2.5 text-xs font-medium inline-flex items-center gap-1 border-r border-gray-200 dark:border-gray-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Pasif
                    </button>
                    <button type="button" @click="aktifDurumuSec('tum')"
                        :class="filtre.aktif_durumu === 'tum' ? 'bg-amber-500 text-white' : 'text-gray-600 dark:text-gray-300'"
                        class="px-2.5 text-xs font-medium inline-flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        Tümü
                    </button>
                </div>

                <div class="inline-flex h-9 rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden bg-white dark:bg-gray-700">
                    <button type="button" @click="siralamaSec('protokol_tarihi_desc')"
                        :class="filtre.siralama === 'protokol_tarihi_desc' ? 'bg-amber-500 text-white' : 'text-gray-600 dark:text-gray-300'"
                        class="px-2.5 text-xs font-medium inline-flex items-center gap-1 border-r border-gray-200 dark:border-gray-600 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10"/>
                        </svg>
                        Tarih
                    </button>
                    <button type="button" @click="siralamaSec('aylik_tutar_desc')"
                        :class="filtre.siralama === 'aylik_tutar_desc' ? 'bg-amber-500 text-white' : 'text-gray-600 dark:text-gray-300'"
                        class="px-2.5 text-xs font-medium inline-flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l4-4 4 4m0-10v10"/>
                        </svg>
                        Miktar
                    </button>
                </div>

                <div class="ml-auto flex items-center gap-1.5">
                    @if($topluProtokolYetkiVar)
                    <button @click="topluProtokolModalAc()"
                        class="h-9 px-3 rounded-lg border border-blue-200 dark:border-blue-700 text-xs font-medium text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors whitespace-nowrap inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Toplu Protokol Ekle
                    </button>
                    @endif

                    <button @click="filtreTemizle()"
                        class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-colors whitespace-nowrap inline-flex items-center">
                        Filtreleri Temizle
                    </button>

                    @if($protokolYetkiVar || auth()->user()->isYonetici())
                    <button @click="yeniProtokolAc()"
                        class="inline-flex items-center gap-2 h-9 px-4 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-sm ring-1 ring-amber-500/30 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Yeni Protokol
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-2 flex items-center justify-between gap-2">
            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                Seçili müvekkil: <span class="font-medium text-gray-700 dark:text-gray-300" x-text="seciliMuvekkilEtiketi()"></span>
            </div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                Sonuç: <span class="font-medium text-gray-700 dark:text-gray-300" x-text="sayfalama.total"></span> protokol
            </div>
        </div>
    </div>

    {{-- Yükleniyor --}}
    <div x-show="yukleniyor" class="flex justify-center py-12">
        <svg class="animate-spin w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    {{-- Bos Durum --}}
    <div x-show="!yukleniyor && protokoller.length === 0" class="text-center py-10 text-gray-400">
        <svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-sm">Bu kriterlerde protokol bulunamadı.</p>
    </div>

    {{-- Tablo --}}
    <div x-show="!yukleniyor && protokoller.length > 0" class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/60">
                <tr>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Protokol No</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Müvekkil</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Portföy</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Borçlu</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">TCKN/VKN</th>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Hacizciler</th>
                    <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Peşinat</th>
                    <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Kalan Taksit</th>
                    <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Bu Ay Vadesi Geçmiş</th>
                    <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Toplam Protokol</th>
                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">PDF</th>
                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">İşlem</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                <template x-for="p in protokoller" :key="p.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-3 py-2.5 text-sm font-mono font-medium text-amber-600 dark:text-amber-400" x-text="p.protokol_no"></td>
                        <td class="px-3 py-2.5 text-sm text-gray-800 dark:text-gray-200" x-text="p.muvekkil?.ad ?? '-'"></td>
                        <td class="px-3 py-2.5 text-sm text-gray-600 dark:text-gray-400" x-text="p.portfoy?.ad ?? '-'"></td>
                        <td class="px-3 py-2.5 text-sm text-gray-600 dark:text-gray-400" x-text="p.borclu_adi"></td>
                        <td class="px-3 py-2.5 text-xs text-gray-500 dark:text-gray-400 font-mono" x-text="p.borclu_tckn_vkn ?? '-'"></td>
                        {{-- YENİ EKLENEN SATIR BURASI --}}
                        <td class="px-3 py-2.5 text-xs text-gray-600 dark:text-gray-400 whitespace-normal min-w-[200px] max-w-[250px] leading-relaxed" 
                            x-text="p.hacizciler && p.hacizciler.length > 0 ? p.hacizciler.map(h => h.ad_soyad).join(', ') : '-'">
                        </td>
                        <td class="px-3 py-2.5 text-sm text-right" x-text="formatPara(p.pesinat)"></td>
                        <td class="px-3 py-2.5 text-sm font-medium text-right">
                            <button @click="taksitDetayAc(p)"
                                class="text-blue-600 dark:text-blue-400 hover:underline"
                                x-text="formatPara(p.kalan_taksit_toplami)">
                            </button>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5"
                                x-text="'Bu Ay Beklenen: ' + formatPara(p.bu_ay_kalan_taksit_toplami ?? 0)"></div>
                        </td>
                        <td class="px-3 py-2.5 text-sm text-right">
                            <div class="font-medium"
                                :class="Number(p.bu_ay_vadesi_gecmis_taksit_sayisi ?? 0) > 0 ? 'text-red-700 dark:text-red-300' : 'text-gray-600 dark:text-gray-300'"
                                x-text="(p.bu_ay_vadesi_gecmis_taksit_sayisi ?? 0) + ' adet'"></div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5"
                                x-text="formatPara(p.bu_ay_vadesi_gecmis_taksit_toplami ?? 0)"></div>
                        </td>
                        <td class="px-3 py-2.5 text-sm text-right font-medium" x-text="formatPara(p.toplam_protokol_tutari)"></td>
                        <td class="px-3 py-2.5 text-center">
                            <template x-if="p.protokol_pdf_dosya_yolu">
                                <button @click="pdfGoster(p)"
                                    class="inline-flex items-center gap-1 px-2 py-1 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded text-xs hover:bg-red-100 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    PDF
                                </button>
                            </template>
                            <span x-show="!p.protokol_pdf_dosya_yolu" class="text-xs text-gray-400">-</span>
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            <button x-show="duzenleyebilirMi(p)"
                                @click="protokolDuzenleAc(p)"
                                class="inline-flex items-center px-2 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 rounded text-xs hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                                Düzenle
                            </button>
                            <span x-show="!duzenleyebilirMi(p)" class="text-xs text-gray-400">-</span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
        x-show="!yukleniyor && sayfalama.last_page > 1">
        <div class="text-xs text-gray-500" x-text="sayfalamaBilgisi()"></div>
        <div class="flex items-center gap-2">
            <button @click="oncekiSayfa()" :disabled="sayfalama.current_page <= 1"
                class="h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-medium text-gray-600 dark:text-gray-300 disabled:opacity-40">
                Önceki
            </button>
            <span class="text-xs text-gray-600 dark:text-gray-300" x-text="sayfalama.current_page + ' / ' + sayfalama.last_page"></span>
            <button @click="sonrakiSayfa()" :disabled="sayfalama.current_page >= sayfalama.last_page"
                class="h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-medium text-gray-600 dark:text-gray-300 disabled:opacity-40">
                Sonraki
            </button>
        </div>
    </div>

    {{-- Alt Özet Satırı: Taksit Beklenti Durumu --}}
    <div class="mt-3 rounded-xl border border-amber-200 dark:border-amber-700/60 bg-amber-50 dark:bg-amber-900/20 px-3 py-2">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 text-xs">
            <div class="rounded-lg bg-white/70 dark:bg-amber-950/25 border border-amber-100 dark:border-amber-900 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Özet Ay</div>
                <div class="mt-0.5 text-sm font-semibold text-amber-700 dark:text-amber-300" x-text="formatAyYil(filtreTaksitOzeti.ozet_ay)"></div>
            </div>
            <div class="rounded-lg bg-white/70 dark:bg-amber-950/25 border border-amber-100 dark:border-amber-900 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Beklenen Taksit</div>
                <div class="mt-0.5 text-sm font-semibold text-amber-700 dark:text-amber-300" x-text="formatPara(filtreTaksitOzeti.beklenen_taksit_toplami)"></div>
            </div>
            <div class="rounded-lg bg-white/70 dark:bg-amber-950/25 border border-amber-100 dark:border-amber-900 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Bekleyen Protokol</div>
                <div class="mt-0.5 text-sm font-semibold text-amber-700 dark:text-amber-300" x-text="filtreTaksitOzeti.beklenen_protokol_sayisi"></div>
            </div>
            <div class="rounded-lg bg-white/70 dark:bg-red-950/20 border border-red-100 dark:border-red-900 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-red-700/80 dark:text-red-300/80">Vadesi Geçmiş</div>
                <div class="mt-0.5 text-sm font-semibold text-red-700 dark:text-red-300"
                    x-text="(filtreTaksitOzeti.vadesi_gecmis_taksit_sayisi ?? 0) + ' adet / ' + formatPara(filtreTaksitOzeti.vadesi_gecmis_taksit_toplami ?? 0)"></div>
            </div>
        </div>
    </div>

    {{-- Taksit Detay Modalı --}}
    <template x-teleport="body">
    <div x-show="taksitModal.acik" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-slate-950/65 backdrop-blur-sm"></div>
        <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-md rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl" @click.stop @click.outside="taksitModal.acik = false">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h3 class="text-base font-bold text-gray-900 dark:text-white"
                    x-text="'Taksit Planı - ' + (taksitModal.protokol?.protokol_no ?? '')"></h3>
                <button @click="taksitModal.acik = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="space-y-2 max-h-80 overflow-y-auto p-6">
                <template x-for="t in (taksitModal.protokol?.taksitler ?? [])" :key="t.id">
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg"
                        :class="t.odendi ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-700/40'">
                        <div>
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400" x-text="'Taksit ' + t.taksit_no"></span>
                            <div class="text-xs text-gray-500" x-text="t.taksit_tarihi"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium" :class="t.odendi ? 'text-green-700 dark:text-green-400' : 'text-gray-800 dark:text-gray-200'"
                                x-text="formatPara(t.taksit_tutari)"></div>
                            <div class="text-xs text-gray-500" x-show="Number(t.odenen_tutar ?? 0) > 0"
                                x-text="'Ödenen: ' + formatPara(t.odenen_tutar ?? 0)"></div>
                            <div class="text-xs text-gray-500" x-show="!t.odendi" x-text="'Kalan: ' + formatPara(t.kalan_tutar ?? t.taksit_tutari)"></div>
                            <div x-show="t.odendi" class="text-xs text-green-600">Ödendi</div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        </div>
    </div>
    </template>

    {{-- PDF Görüntüleme Modalı --}}
    <template x-teleport="body">
    <div x-show="pdfModal.acik" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-slate-950/65 backdrop-blur-sm"></div>
        <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="relative flex h-[85vh] max-h-[calc(100vh-2rem)] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800 sm:max-h-[calc(100vh-3rem)]" @click.stop @click.outside="pdfModal.acik = false">
            <div class="flex items-center justify-between border-b border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-base font-bold text-gray-900 dark:text-white"
                    x-text="'Protokol PDF - ' + (pdfModal.protokol?.protokol_no ?? '')"></h3>
                <button @click="pdfModal.acik = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 p-2">
                <iframe x-show="pdfModal.acik" :src="pdfModal.url" class="w-full h-full rounded border border-gray-200 dark:border-gray-700"></iframe>
            </div>
        </div>
        </div>
    </div>
    </template>

    {{-- Yeni Protokol Form Modalı --}}
    @include('components.private.tahsilat.ProtokolFormModal')
    @if($topluProtokolYetkiVar)
    <template x-teleport="body">
    @include('components.private.tahsilat.TopluProtokolModal')
    </template>
    @endif

</div>

<script>
function protokolTab() {
    return {
        yukleniyor: true,
        protokoller: [],
        filtreTaksitOzeti: {
            ozet_ay: '',
            beklenen_taksit_toplami: 0,
            beklenen_protokol_sayisi: 0,
            vadesi_gecmis_taksit_sayisi: 0,
            vadesi_gecmis_taksit_toplami: 0,
        },
        sayfalama: {
            current_page: 1,
            last_page: 1,
            per_page: 20,
            total: 0,
            from: 0,
            to: 0,
        },
        muvekkiller: [],
        allPortfoyler: [],
        portfoyler: [],
        portfoyYukleniyor: false,
        muvekkilDropdownAcik: false,
        currentUserId: @js((string) auth()->id()),
        yoneticiMi: @js((bool) auth()->user()->isYonetici()),
        filtre: {
            protokol_no: '',
            muvekkil_ids: [],
            portfoy_id: '',
            ay: '',
            aktif_durumu: 'aktif',
            siralama: 'protokol_tarihi_desc',
        },
        taksitModal: { acik: false, protokol: null },
        pdfModal: { acik: false, protokol: null, url: '' },

        buildParams(page = 1) {
            const params = new URLSearchParams();
            if (this.filtre.protokol_no) params.set('protokol_no', this.filtre.protokol_no);
            if (Array.isArray(this.filtre.muvekkil_ids)) {
                const seciliMuvekkiller = this.filtre.muvekkil_ids
                    .filter((id) => typeof id === 'string' && id !== '')
                    .map((id) => id.trim())
                    .filter((id) => id !== '');
                if (seciliMuvekkiller.length > 0) {
                    params.set('muvekkil_ids', seciliMuvekkiller.join(','));
                }
            }
            if (this.filtre.portfoy_id) params.set('portfoy_id', this.filtre.portfoy_id);
            if (this.filtre.ay) params.set('ay', this.filtre.ay);
            if (this.filtre.aktif_durumu) params.set('aktif_durumu', this.filtre.aktif_durumu);
            if (this.filtre.siralama) params.set('siralama', this.filtre.siralama);

            params.set('page', String(page));
            params.set('per_page', String(this.sayfalama.per_page || 20));
            return params;
        },

        async yukle(page = 1) {
            this.yukleniyor = true;
            try {
                const params = this.buildParams(page);

                const res = await fetch('/tahsilat/protokol/list?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.protokoller = data.data ?? [];
                    this.sayfalama.current_page = Number(data.current_page ?? 1);
                    this.sayfalama.last_page = Number(data.last_page ?? 1);
                    this.sayfalama.per_page = Number(data.per_page ?? 20);
                    this.sayfalama.total = Number(data.total ?? 0);
                    this.sayfalama.from = Number(data.from ?? 0);
                    this.sayfalama.to = Number(data.to ?? 0);

                    const ozet = data.filtre_taksit_ozeti ?? {};
                    const beklenenTaksitToplami = parseFloat(ozet.beklenen_taksit_toplami ?? '');
                    const beklenenProtokolSayisi = parseInt(ozet.beklenen_protokol_sayisi ?? '0', 10);
                    const vadesiGecmisTaksitSayisi = parseInt(ozet.vadesi_gecmis_taksit_sayisi ?? '0', 10);
                    const vadesiGecmisTaksitToplami = parseFloat(ozet.vadesi_gecmis_taksit_toplami ?? '');
                    const varsayilanAy = this.filtre.ay || new Date().toISOString().slice(0, 7);

                    this.filtreTaksitOzeti = {
                        ozet_ay: (typeof ozet.ozet_ay === 'string' && ozet.ozet_ay !== '') ? ozet.ozet_ay : varsayilanAy,
                        beklenen_taksit_toplami: Number.isFinite(beklenenTaksitToplami) ? beklenenTaksitToplami : 0,
                        beklenen_protokol_sayisi: Number.isFinite(beklenenProtokolSayisi) ? beklenenProtokolSayisi : 0,
                        vadesi_gecmis_taksit_sayisi: Number.isFinite(vadesiGecmisTaksitSayisi) ? vadesiGecmisTaksitSayisi : 0,
                        vadesi_gecmis_taksit_toplami: Number.isFinite(vadesiGecmisTaksitToplami) ? vadesiGecmisTaksitToplami : 0,
                    };
                }
            } finally {
                this.yukleniyor = false;
            }
        },

        async muvekkillerYukle() {
            try {
                const res = await fetch('/muvekkil/list', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    this.muvekkiller = await res.json();
                }
            } catch (e) {
                this.muvekkiller = [];
            }
        },

        seciliMuvekkilEtiketi() {
            const secili = Array.isArray(this.filtre.muvekkil_ids) ? this.filtre.muvekkil_ids : [];
            if (secili.length === 0) return 'Tüm Müvekkiller';
            if (secili.length === 1) {
                const kayit = this.muvekkiller.find((m) => m.id === secili[0]);
                return kayit?.ad ?? '1 Müvekkil';
            }

            return secili.length + ' Müvekkil';
        },

        async portfoylerYukle() {
            this.portfoyYukleniyor = true;
            try {
                const res = await fetch('/tahsilat/protokol/portfoyler', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    this.allPortfoyler = await res.json();
                    this.applyPortfoySecenekleri();
                }
            } catch (e) {
                this.allPortfoyler = [];
                this.portfoyler = [];
            } finally {
                this.portfoyYukleniyor = false;
            }
        },

        applyPortfoySecenekleri() {
            const seciliMuvekkiller = Array.isArray(this.filtre.muvekkil_ids) ? this.filtre.muvekkil_ids : [];
            if (seciliMuvekkiller.length === 0) {
                this.portfoyler = [...this.allPortfoyler];
            } else {
                const seciliSet = new Set(seciliMuvekkiller);
                this.portfoyler = this.allPortfoyler.filter((p) => seciliSet.has(p.muvekkil_id));
            }

            if (this.filtre.portfoy_id && !this.portfoyler.some((p) => p.id === this.filtre.portfoy_id)) {
                this.filtre.portfoy_id = '';
            }
        },

        async muvekkilSecimToggle(muvekkilId) {
            if (typeof muvekkilId !== 'string' || muvekkilId === '') return;
            const secili = new Set(Array.isArray(this.filtre.muvekkil_ids) ? this.filtre.muvekkil_ids : []);
            if (secili.has(muvekkilId)) {
                secili.delete(muvekkilId);
            } else {
                secili.add(muvekkilId);
            }

            this.filtre.muvekkil_ids = Array.from(secili);
            this.applyPortfoySecenekleri();
            await this.yukle(1);
        },

        async tumMuvekkilSec() {
            this.filtre.muvekkil_ids = this.muvekkiller.map((m) => m.id);
            this.applyPortfoySecenekleri();
            await this.yukle(1);
        },

        async muvekkilTemizle() {
            this.filtre.muvekkil_ids = [];
            this.applyPortfoySecenekleri();
            await this.yukle(1);
        },

        async muvekkilDegisti() {
            this.applyPortfoySecenekleri();
            await this.yukle(1);
        },

        aktifDurumuSec(deger) {
            if (!deger || this.filtre.aktif_durumu === deger) return;
            this.filtre.aktif_durumu = deger;
            this.yukle(1);
        },

        siralamaSec(deger) {
            if (!deger || this.filtre.siralama === deger) return;
            this.filtre.siralama = deger;
            this.yukle(1);
        },

        async filtreTemizle() {
            this.filtre = {
                protokol_no: '',
                muvekkil_ids: [],
                portfoy_id: '',
                ay: '',
                aktif_durumu: 'aktif',
                siralama: 'protokol_tarihi_desc',
            };
            this.applyPortfoySecenekleri();
            await this.yukle(1);
        },

        oncekiSayfa() {
            if (this.sayfalama.current_page > 1) {
                this.yukle(this.sayfalama.current_page - 1);
            }
        },

        sonrakiSayfa() {
            if (this.sayfalama.current_page < this.sayfalama.last_page) {
                this.yukle(this.sayfalama.current_page + 1);
            }
        },

        sayfalamaBilgisi() {
            const from = this.sayfalama.from ?? 0;
            const to = this.sayfalama.to ?? 0;
            const total = this.sayfalama.total ?? 0;
            return from + ' - ' + to + ' / ' + total + ' kayıt';
        },

        taksitDetayAc(protokol) {
            this.taksitModal = { acik: true, protokol };
        },

        pdfGoster(protokol) {
            this.pdfModal = {
                acik: true,
                protokol,
                url: '/tahsilat/protokol/' + protokol.id + '/pdf'
            };
        },

        yeniProtokolAc() {
            window.dispatchEvent(new CustomEvent('protokol-form-ac'));
        },

        topluProtokolModalAc() {
            window.dispatchEvent(new CustomEvent('protokol-toplu-modal-ac'));
        },

        protokolDuzenleAc(protokol) {
            window.dispatchEvent(new CustomEvent('protokol-form-duzenle', { detail: { id: protokol.id } }));
        },

        duzenleyebilirMi(protokol) {
            return Boolean(protokol?.duzenlenebilir);
        },

        formatPara(deger) {
            if (!deger) return '0,00 TL';
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(deger);
        },

        formatAyYil(ay) {
            if (!ay || !/^\d{4}-\d{2}$/.test(ay)) return '-';
            const [yil, ayNo] = ay.split('-').map((v) => parseInt(v, 10));
            const tarih = new Date(yil, ayNo - 1, 1);
            return new Intl.DateTimeFormat('tr-TR', { month: 'long', year: 'numeric' }).format(tarih);
        },

        async init() {
            await this.muvekkillerYukle();
            await this.portfoylerYukle();
            await this.yukle(1);
            window.addEventListener('protokol-listesi-yenile', () => this.yukle(this.sayfalama.current_page || 1));
        }
    };
}
</script>




