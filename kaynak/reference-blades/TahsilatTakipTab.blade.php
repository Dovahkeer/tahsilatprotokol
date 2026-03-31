{{-- Günlük Tahsilat Tab --}}
<div class="p-6" x-data="tahsilatTakipTab()" x-init="init()">

    {{-- Filtre Paneli --}}
    <div class="mb-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20 p-3">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3">
            <div class="flex flex-wrap items-end gap-2">
                <div class="h-9 px-3 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 inline-flex items-center text-xs font-medium text-amber-700 dark:text-amber-300">
                    Sadece bugünün tahsilatları listelenir
                </div>

                <select x-model="filtre.onay_durumu" @change="yukle()"
                    class="h-9 px-3 rounded-lg text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <option value="">Tüm Durumlar</option>
                    <option value="beklemede">Beklemede</option>
                    <option value="onaylandi">Onaylandı</option>
                    <option value="reddedildi">Reddedildi</option>
                </select>

                <button @click="filtreTemizle()"
                    class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-colors">
                    Filtreyi Temizle
                </button>
            </div>

            @php
                $yetkiVar = auth()->id() && \App\Models\TahsilatYetkiliKullanici
                    ::where('user_id', auth()->id())
                    ->where('tahsilat_olusturabilir', true)
                    ->where('aktif', true)
                    ->exists();
            @endphp

            <div class="flex items-center gap-2">
                <a href="{{ route('tahsilat.export.mail-order-pdf') }}"
                    class="inline-flex items-center gap-2 h-9 px-3.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l4-4m-4 4l-4-4M4 17v1a2 2 0 002 2h12a2 2 0 002-2v-1"/>
                    </svg>
                    Mail Order PDF
                </a>

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

        <div class="mt-2 grid grid-cols-2 lg:grid-cols-4 gap-2">
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
        <p class="text-sm">Bugün için tahsilat kaydı bulunamadı.</p>
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
                        <template x-for="dekont in (tahsilat.dekontlar ?? [])" :key="dekont.id">
                            <a :href="'/tahsilat/dekont/' + dekont.id + '/view'" target="_blank"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded text-xs hover:bg-blue-100 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Dekont
                            </a>
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
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Yeni Tahsilat Modalı --}}
    @include('components.private.tahsilat.TahsilatFormModal')

    {{-- Red Nedeni Modalı --}}
    <div x-show="redModal.acik" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
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

<script>
function tahsilatTakipTab() {
    return {
        yukleniyor: true,
        tahsilatlar: [],
        currentUserId: @js((string) auth()->id()),
        yoneticiMi: @js((bool) auth()->user()->isYonetici()),
        filtre: { onay_durumu: '' },
        redModal: { acik: false, tahsilat: null, neden: '' },
        tahsilatModal: false,

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
            this.yukle();
            window.addEventListener('tahsilat-listesi-yenile', () => this.yukle());
        },

        async yukle() {
            this.yukleniyor = true;
            try {
                const params = new URLSearchParams();
                params.set('bugun', '1');
                params.set('include', 'dekontlar');
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
            window.dispatchEvent(new CustomEvent('open-toplu-tahsilat-modal'));
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

        filtreTemizle() {
            this.filtre = { onay_durumu: '' };
            this.yukle();
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

