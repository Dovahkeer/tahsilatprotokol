{{-- Günlük Tahsilat Tab --}}
<div class="p-6" x-data="tahsilatTakipTab()" x-init="init()">

    {{-- Filtre Paneli --}}
    <div class="mb-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20 p-3">
        <div class="flex flex-col gap-4">
            
            {{-- Üst Kısım: Uyarı ve Sağdaki İşlem Butonları --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="h-9 px-3 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 inline-flex items-center text-xs font-medium text-amber-700 dark:text-amber-300">
                    Sadece bugünün tahsilatları listelenir
                </div>

                @php
                    $yetkiVar = auth()->id() && \App\Models\TahsilatYetkiliKullanici
                        ::where('user_id', auth()->id())
                        ->where('tahsilat_olusturabilir', true)
                        ->where('aktif', true)
                        ->exists();
                @endphp

                <div class="flex items-center gap-2">
                    <button type="button" @click="mailOrderModalAc()"
                        class="inline-flex items-center gap-2 h-9 px-3.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l4-4m-4 4l-4-4M4 17v1a2 2 0 002 2h12a2 2 0 002-2v-1"/>
                        </svg>
                        Mail Order PDF
                    </button>

                    @if(auth()->user()->isYonetici())
                    <button @click="topluTahsilatModalAc()"
                        class="inline-flex items-center gap-2 h-9 px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Toplu Tahsilat Ekle
                    </button>
                    @endif

                    @if($yetkiVar || auth()->user()->isYonetici())
                    <button @click="yeniTahsilatAc()"
                        class="inline-flex items-center gap-2 h-9 px-4 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Yeni Tahsilat
                    </button>
                    @endif
                </div>
            </div>

            {{-- Alt Kısım: Detaylı Arama ve Filtreler (Taşmayan Orantılı Esnek Yapı) --}}
            <div class="flex flex-col md:flex-row gap-3 items-end w-full">
                
                {{-- Arama Çubuğu (Geniş alan alır, 2 pay) --}}
                <div class="w-full md:flex-[2] min-w-0">
                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Arama</label>
                    <input x-model="filtre.q" @keydown.enter.prevent="yukle()" type="text" placeholder="Borçlu adı, TCKN/VKN, Protokol No..."
                        class="h-9 w-full px-3 rounded-lg text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 outline-none focus:ring-2 focus:ring-amber-500 transition-shadow">
                </div>

                {{-- Müvekkil (Eşit alan alır, 1 pay) --}}
                <div class="w-full md:flex-1 min-w-0">
                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Müvekkil</label>
                    <select x-model="filtre.muvekkil_id" @change="muvekkilDegisti(); yukle()" 
                        class="h-9 w-full px-3 rounded-lg text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 outline-none focus:ring-2 focus:ring-amber-500 transition-shadow">
                        <option value="">Tüm Müvekkiller</option>
                        <template x-for="m in muvekkiller" :key="m.id">
                            <option :value="m.id" x-text="m.ad"></option>
                        </template>
                    </select>
                </div>

                {{-- Portföy (Eşit alan alır, 1 pay) --}}
                <div class="w-full md:flex-1 min-w-0">
                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Portföy</label>
                    <select x-model="filtre.portfoy_id" @change="yukle()" :disabled="!filtre.muvekkil_id || portfoyYukleniyor" 
                        class="h-9 w-full px-3 rounded-lg text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 disabled:opacity-50 outline-none focus:ring-2 focus:ring-amber-500 transition-shadow">
                        <option value="">Tüm Portföyler</option>
                        <template x-for="p in portfoyler" :key="p.id">
                            <option :value="p.id" x-text="p.ad"></option>
                        </template>
                    </select>
                </div>

                {{-- Durum (Eşit alan alır, 1 pay) --}}
                <div class="w-full md:flex-1 min-w-0">
                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-1">Durum</label>
                    <select x-model="filtre.onay_durumu" @change="yukle()"
                        class="h-9 w-full px-3 rounded-lg text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 outline-none focus:ring-2 focus:ring-amber-500 transition-shadow">
                        <option value="">Tüm Durumlar</option>
                        <option value="beklemede">Beklemede</option>
                        <option value="onaylandi">Onaylandı</option>
                        <option value="reddedildi">Reddedildi</option>
                    </select>
                </div>

                {{-- Temizle Butonu (Sadece içeriği kadar yer kaplar) --}}
                <div class="w-full md:w-auto md:flex-none">
                    <button @click="filtreTemizle()" title="Filtreleri Temizle"
                        class="h-9 w-full px-4 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:text-gray-800 hover:bg-white dark:hover:bg-gray-700 transition-colors text-sm font-medium shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Temizle
                    </button>
                </div>
            </div>

        </div>

        <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-2">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Kayıt</div>
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="toplamKayitSayisi"></div>
            </div>
            <div class="rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-900/20 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-emerald-700/80 dark:text-emerald-300/80">Toplam Tutar</div>
                <div class="text-sm font-semibold text-emerald-700 dark:text-emerald-300" x-text="formatPara(toplamTahsilatTutari)"></div>
            </div>
            <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50/70 dark:bg-amber-900/20 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Beklemede</div>
                <div class="text-sm font-semibold text-amber-700 dark:text-amber-300" x-text="bekleyenSayisi"></div>
            </div>
            <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-900/20 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-blue-700/80 dark:text-blue-300/80">Onaylandı</div>
                <div class="text-sm font-semibold text-blue-700 dark:text-blue-300" x-text="onaylananSayisi"></div>
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
    <div x-show="!yukleniyor && tahsilatlar.length === 0" class="text-center py-12 text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-sm">Bugün için tahsilat kaydı bulunamadı veya arama kriterlerine uyan kayıt yok.</p>
    </div>

    {{-- Tahsilat Kartlari --}}
    <div x-show="!yukleniyor" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <template x-for="tahsilat in tahsilatlar" :key="tahsilat.id">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-3 shadow-sm flex flex-col gap-2.5">

                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white text-sm" x-text="tahsilat.muvekkil?.ad ?? '-'"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="tahsilat.borclu_adi ?? '-'"></div>
                    </div>
                    <span :class="durumSinif(tahsilat.onay_durumu)"
                        class="flex-shrink-0 inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium"
                        x-text="durumEtiketi(tahsilat.onay_durumu)"></span>
                </div>

                <div class="flex items-end justify-between">
                    <span class="text-lg font-bold text-gray-900 dark:text-white" x-text="formatPara(tahsilat.tutar)"></span>
                    <div class="text-right">
                        <div class="text-[11px] text-gray-500" x-text="'Tarih: ' + formatTarih(tahsilat.tahsilat_tarihi)"></div>
                        <div class="text-[11px] text-gray-400" x-text="'Kayıt: ' + formatTarihSaat(tahsilat.created_at)"></div>
                    </div>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <template x-if="tahsilat.protokolsuz">
                        <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">Protokolsuz</span>
                    </template>
                    <template x-if="!tahsilat.protokolsuz && tahsilat.protokol">
                        <span x-text="'Protokol: ' + (tahsilat.protokol?.protokol_no ?? '-')"></span>
                    </template>
                </div>

                <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex gap-2">
                        <template x-if="tahsilat.dekontlar && tahsilat.dekontlar.length > 0">
                            <button type="button" @click="dekontGoruntule(tahsilat.dekontlar[0], tahsilat)"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded text-xs font-medium hover:bg-blue-100 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Dekont
                            </button>
                        </template>
                    </div>

                    <div class="flex items-center gap-2">
                        <button x-show="duzenleyebilirMi(tahsilat) && tahsilat.onay_durumu !== 'onaylandi' && tahsilat.onay_durumu !== 'iptal'"
                            @click="tahsilatDuzenleAc(tahsilat)"
                            class="px-2 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded text-[11px] font-medium hover:bg-amber-200 dark:hover:bg-amber-900/50 transition-colors">
                            Düzenle
                        </button>

                        @php
                            $sorumluyum = auth()->id() && \App\Models\TahsilatYetkiliKullanici
                                ::where('user_id', auth()->id())
                                ->where('tahsilat_takip_sorumlusu', true)
                                ->where('aktif', true)
                                ->exists();
                        @endphp

                        @if($sorumluyum || auth()->user()->isYonetici())
                        <div x-show="tahsilat.onay_durumu === 'beklemede'" class="flex gap-2">
                            <button @click="tahsilatOnayla(tahsilat)"
                                class="px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-[11px] font-medium transition-colors">
                                Onayla
                            </button>
                            <button @click="redModalAc(tahsilat)"
                                class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-[11px] font-medium transition-colors">
                                Reddet
                            </button>
                        </div>
                        @endif

                        @if(auth()->user()->isYonetici())
                        <div x-show="tahsilat.onay_durumu === 'onaylandi'" class="flex gap-2">
                            <button @click="tahsilatIptalEt(tahsilat)"
                                class="px-2 py-1 bg-slate-600 hover:bg-slate-700 text-white rounded text-[11px] font-medium transition-colors">
                                İptal Et
                            </button>
                        </div>
                        @endif

                        <button x-show="tahsilat.onay_durumu === 'reddedildi' || tahsilat.onay_durumu === 'iptal'"
                            @click="nedenModalAc(tahsilat)"
                            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded text-[11px] font-medium transition-colors">
                            Nedeni Gör
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Yeni Tahsilat Modalı --}}
    @include('components.private.tahsilat.TahsilatFormModal')

    {{-- Dekont Görüntüleme Modalı --}}
    <template x-teleport="body">
    <div x-show="dekontModal.acik" class="fixed inset-0 z-[60] overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-slate-950/75 backdrop-blur-sm" @click="dekontModal.acik = false"></div>
        <div class="relative flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-4xl h-[85vh] flex flex-col rounded-xl bg-white dark:bg-gray-900 shadow-2xl overflow-hidden" @click.stop>
                
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white truncate pr-4" x-text="dekontModal.baslik"></h3>
                    <button @click="dekontModal.acik = false" class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-200 dark:hover:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="flex-1 w-full h-full bg-gray-100 dark:bg-gray-950">
                    <iframe x-show="dekontModal.acik" :src="dekontModal.url" class="w-full h-full border-0"></iframe>
                </div>
                
            </div>
        </div>
    </div>
    </template>

    {{-- Mail Order PDF Rapor Modalı --}}
    <template x-teleport="body">
    <div x-show="mailOrderModal.acik" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-slate-950/65 backdrop-blur-sm" @click="mailOrderModal.acik = false"></div>
        <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-sm rounded-2xl border border-gray-200 dark:border-gray-700 bg-white p-6 shadow-2xl dark:bg-gray-800" @click.stop>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Mail Order Raporu</h3>
            <p class="text-xs text-gray-500 mb-4">Hangi güne ait muhasebe raporunu almak istiyorsunuz?</p>
            
            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Rapor Tarihi</label>
            <input type="date" x-model="mailOrderModal.tarih" 
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
            
            <div class="flex justify-end gap-3 mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
                <button @click="mailOrderModal.acik = false"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    İptal
                </button>
                <a :href="'/tahsilat/export/mail-order-pdf?tarih=' + mailOrderModal.tarih" target="_blank" @click="mailOrderModal.acik = false"
                    class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF Oluştur
                </a>
            </div>
        </div>
        </div>
    </div>
    </template>

    {{-- Red Nedeni Modalı --}}
    <template x-teleport="body">
    <div x-show="redModal.acik" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-slate-950/65 backdrop-blur-sm"></div>
        <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-md rounded-2xl border border-gray-200 dark:border-gray-700 bg-white p-6 shadow-2xl dark:bg-gray-800" @click.stop>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Red Nedeni</h3>
            <textarea x-model="redModal.neden" rows="3" placeholder="Reddetme nedenini yazın..."
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white resize-none focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
            <div class="flex justify-end gap-3 mt-4">
                <button @click="redModal.acik = false"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    İptal
                </button>
                <button @click="tahsilatReddet()"
                    class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                    Reddet
                </button>
            </div>
        </div>
        </div>
    </div>
    </template>

    {{-- Bilgi (Neden) Modalı --}}
    <template x-teleport="body">
    <div x-show="bilgiModal.acik" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-slate-950/65 backdrop-blur-sm" @click="bilgiModal.acik = false"></div>
        <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-md rounded-2xl border border-gray-200 dark:border-gray-700 bg-white p-6 shadow-2xl dark:bg-gray-800" @click.stop>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2" x-text="bilgiModal.baslik"></h3>
            <div class="p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap" x-text="bilgiModal.icerik || 'Neden belirtilmemiş.'"></div>
            <div class="flex justify-end mt-4">
                <button @click="bilgiModal.acik = false" class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg font-medium transition-colors">Kapat</button>
            </div>
        </div>
        </div>
    </div>
    </template>
</div>

<script>
function tahsilatTakipTab() {
    return {
        yukleniyor: true,
        tahsilatlar: [],
        
        // YENİ EKLENEN FİLTRE VERİLERİ
        muvekkiller: [],
        portfoyler: [],
        portfoyYukleniyor: false,

        currentUserId: @js((string) auth()->id()),
        yoneticiMi: @js((bool) auth()->user()->isYonetici()),
        
        // GENİŞLETİLMİŞ FİLTRE NESNESİ
        filtre: { onay_durumu: '', q: '', muvekkil_id: '', portfoy_id: '' },
        
        redModal: { acik: false, tahsilat: null, neden: '' },
        dekontModal: { acik: false, url: '', baslik: '' },
        bilgiModal: { acik: false, baslik: '', icerik: '' },
        mailOrderModal: { acik: false, tarih: '' },
        tahsilatModal: false,

        mailOrderModalAc() {
            const dun = new Date();
            dun.setDate(dun.getDate() - 1);
            this.mailOrderModal.tarih = dun.toISOString().split('T')[0];
            this.mailOrderModal.acik = true;
        },

        get toplamKayitSayisi() {
            return (this.tahsilatlar ?? []).length;
        },

        get toplamTahsilatTutari() {
            return (this.tahsilatlar ?? []).reduce((toplam, item) => {
                if ((item?.onay_durumu ?? '') === 'iptal') {
                    return toplam;
                }
                const tutar = parseFloat(item?.tutar ?? 0);
                return toplam + (Number.isFinite(tutar) ? tutar : 0);
            }, 0);
        },

        get bekleyenSayisi() {
            return (this.tahsilatlar ?? []).filter((t) => t.onay_durumu === 'beklemede').length;
        },

        get onaylananSayisi() {
            return (this.tahsilatlar ?? []).filter((t) => t.onay_durumu === 'onaylandi').length;
        },

        init() {
            this.muvekkillerYukle(); // SAYFA AÇILIRKEN MÜVEKKİLLERİ ÇEK
            this.yukle();
            window.addEventListener('tahsilat-listesi-yenile', () => this.yukle());
        },

        // YENİ EKLENEN: Müvekkil Listesini Çek
        async muvekkillerYukle() {
            try {
                const res = await fetch('/muvekkil/list', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) this.muvekkiller = await res.json();
            } catch (e) {}
        },

        // YENİ EKLENEN: Müvekkil seçilince Portföyleri Çek
        async muvekkilDegisti() {
            this.filtre.portfoy_id = '';
            this.portfoyler = [];

            if (!this.filtre.muvekkil_id) return;

            this.portfoyYukleniyor = true;
            try {
                const res = await fetch('/tahsilat/protokol/portfoyler/' + this.filtre.muvekkil_id, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) this.portfoyler = await res.json();
            } catch (e) {}
            this.portfoyYukleniyor = false;
        },

        async yukle() {
            this.yukleniyor = true;
            try {
                const params = new URLSearchParams();
                params.set('bugun', '1');
                params.set('include', 'dekontlar');
                
                // BACKEND'E TÜM FİLTRELERİ GÖNDERİYORUZ
                if (this.filtre.q) params.set('q', this.filtre.q);
                if (this.filtre.muvekkil_id) params.set('muvekkil_id', this.filtre.muvekkil_id);
                if (this.filtre.portfoy_id) params.set('portfoy_id', this.filtre.portfoy_id);
                if (this.filtre.onay_durumu) params.set('onay_durumu', this.filtre.onay_durumu);
                
                params.set('per_page', '100');

                const res = await fetch('/tahsilat/list?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.tahsilatlar = data.data ?? [];
                }
            } finally {
                this.yukleniyor = false;
            }
        },

        filtreTemizle() {
            this.filtre = { onay_durumu: '', q: '', muvekkil_id: '', portfoy_id: '' };
            this.portfoyler = [];
            this.yukle();
        },

        async tahsilatOnayla(tahsilat) {
            if (!confirm('Bu tahsilatı onaylamak istiyor musunuz?')) return;
            const res = await fetch('/tahsilat/' + tahsilat.id + '/onayla', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (res.ok) {
                this.yukle();
                window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
            } else {
                const err = await res.json();
                alert(err.error ?? 'Bir hata oluştu.');
            }
        },

        redModalAc(tahsilat) {
            this.redModal = { acik: true, tahsilat, neden: '' };
        },

        nedenModalAc(tahsilat) {
            this.bilgiModal.baslik = tahsilat.onay_durumu === 'reddedildi' ? 'Red Nedeni' : 'İptal Nedeni';
            this.bilgiModal.icerik = tahsilat.onay_durumu === 'reddedildi' ? tahsilat.red_nedeni : tahsilat.iptal_nedeni;
            this.bilgiModal.acik = true;
        },

        dekontGoruntule(dekont, tahsilat) {
            this.dekontModal.url = '/tahsilat/dekont/' + dekont.id + '/view';
            this.dekontModal.baslik = 'Dekont - ' + (tahsilat.borclu_adi || 'Bilinmiyor');
            this.dekontModal.acik = true;
        },

        async tahsilatReddet() {
            if (!this.redModal.neden.trim()) {
                alert('Lütfen red nedeni girin.');
                return;
            }
            const res = await fetch('/tahsilat/' + this.redModal.tahsilat.id + '/reddet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ red_nedeni: this.redModal.neden }),
            });
            if (res.ok) {
                this.redModal.acik = false;
                this.yukle();
                window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
            } else {
                const err = await res.json();
                alert(err.error ?? 'Bir hata oluştu.');
            }
        },

        async tahsilatIptalEt(tahsilat) {
            if (!this.yoneticiMi) {
                alert('Bu işlem sadece yönetici tarafından yapılabilir.');
                return;
            }

            if (!confirm('Bu onaylı tahsilatı iptal etmek istiyor musunuz?')) {
                return;
            }

            const neden = prompt('İptal nedeni (opsiyonel):', '') ?? '';
            const res = await fetch('/tahsilat/' + tahsilat.id + '/iptal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ iptal_nedeni: neden.trim() }),
            });

            if (res.ok) {
                this.yukle();
                window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
                window.dispatchEvent(new CustomEvent('tahsilat-dashboard-yenile'));
                window.dispatchEvent(new CustomEvent('tahsilat-listesi-yenile'));
            } else {
                const err = await res.json();
                alert(err.error ?? 'Bir hata oluştu.');
            }
        },

        yeniTahsilatAc() {
            window.dispatchEvent(new CustomEvent('tahsilat-form-ac'));
        },

        topluTahsilatModalAc() {
            window.dispatchEvent(new CustomEvent('tahsilat-toplu-modal-ac'));
        },

        tahsilatDuzenleAc(tahsilat) {
            window.dispatchEvent(new CustomEvent('tahsilat-form-duzenle', { detail: { id: tahsilat.id } }));
        },

        duzenleyebilirMi(tahsilat) {
            if (this.yoneticiMi) {
                return true;
            }

            return !!tahsilat?.created_by && String(tahsilat.created_by) === String(this.currentUserId);
        },

        parseDate(deger) {
            if (!deger) return null;
            if (deger instanceof Date) return deger;
            if (typeof deger === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(deger.trim())) {
                return new Date(deger + 'T00:00:00');
            }
            const parsed = new Date(deger);
            return Number.isNaN(parsed.getTime()) ? null : parsed;
        },

        formatTarih(deger) {
            const tarih = this.parseDate(deger);
            if (!tarih) return '-';
            return new Intl.DateTimeFormat('tr-TR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                timeZone: 'Europe/Istanbul',
            }).format(tarih);
        },

        formatTarihSaat(deger) {
            const tarih = this.parseDate(deger);
            if (!tarih) return '-';
            return new Intl.DateTimeFormat('tr-TR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
                timeZone: 'Europe/Istanbul',
            }).format(tarih);
        },

        formatPara(deger) {
            if (!deger) return '0,00 TL';
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(deger);
        },

        durumEtiketi(durum) {
            if (durum === 'onaylandi') return 'Onaylandı';
            if (durum === 'beklemede') return 'Beklemede';
            if (durum === 'reddedildi') return 'Reddedildi';
            if (durum === 'iptal') return 'İptal';
            return '-';
        },

        durumSinif(durum) {
            if (durum === 'onaylandi') {
                return 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400';
            }
            if (durum === 'beklemede') {
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400';
            }
            if (durum === 'reddedildi') {
                return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-400';
            }
            if (durum === 'iptal') {
                return 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200';
            }
            return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
        },
    };
}
</script>