{{-- Tüm Tahsilatlar Tab --}}
<div class="p-6" x-data="tumTahsilatlarTab()" x-init="init()">
    @php
        $onayYetkisiVar = auth()->id() && \App\Models\TahsilatYetkiliKullanici
            ::where('user_id', auth()->id())
            ->where('tahsilat_takip_sorumlusu', true)
            ->where('aktif', true)
            ->exists();
    @endphp

    <div class="mb-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20 p-3">
        <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-3">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-2 flex-1">
                <div class="xl:col-span-2">
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Arama</label>
                    <input x-model="filtre.q" @keydown.enter.prevent="filtreUygula()" type="text" placeholder="Müvekkil, borçlu, protokol no, TCKN/VKN"
                        class="h-9 w-full px-3 rounded-lg text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Durum</label>
                    <select x-model="filtre.onay_durumu" class="h-9 w-full px-3 rounded-lg text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                        <option value="">Tüm Durumlar</option>
                        <option value="beklemede">Beklemede</option>
                        <option value="onaylandi">Onaylandı</option>
                        <option value="reddedildi">Reddedildi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Başlangıç</label>
                    <input x-model="filtre.tarih_baslangic" type="date"
                        class="h-9 w-full px-3 rounded-lg text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-gray-500 mb-1">Bitiş</label>
                    <input x-model="filtre.tarih_bitis" type="date"
                        class="h-9 w-full px-3 rounded-lg text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button @click="filtreUygula()"
                    class="h-9 px-4 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition-colors">
                    Uygula
                </button>
                <button @click="filtreTemizle()"
                    class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-colors">
                    Temizle
                </button>
                <a href="/tahsilat/export/excel"
                    class="inline-flex items-center h-9 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium transition-colors">
                    Excel
                </a>
            </div>
        </div>

        <div class="mt-3 grid grid-cols-2 xl:grid-cols-4 gap-2">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-gray-500">Toplam Kayıt</div>
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="sayfalama.total"></div>
            </div>
            <div class="rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-900/20 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-emerald-700/80 dark:text-emerald-300/80">Sayfa Toplamı</div>
                <div class="text-sm font-semibold text-emerald-700 dark:text-emerald-300" x-text="formatPara(sayfaToplami())"></div>
            </div>
            <div class="rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50/70 dark:bg-amber-900/20 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Bekleyen</div>
                <div class="text-sm font-semibold text-amber-700 dark:text-amber-300" x-text="bekleyenSayisi()"></div>
            </div>
            <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-900/20 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-blue-700/80 dark:text-blue-300/80">Onaylı</div>
                <div class="text-sm font-semibold text-blue-700 dark:text-blue-300" x-text="onayliSayisi()"></div>
            </div>
        </div>
    </div>

    <div x-show="yukleniyor" class="flex justify-center py-12">
        <svg class="animate-spin w-8 h-8 text-amber-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    <div x-show="!yukleniyor" class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Tarih</th>
                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Müvekkil / Portföy</th>
                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Protokol</th>
                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Borçlu</th>
                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 uppercase">Yöntem</th>
                    <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 uppercase">Tutar</th>
                    <th class="px-3 py-2 text-center text-[11px] font-semibold text-gray-500 uppercase">Durum</th>
                    <th class="px-3 py-2 text-center text-[11px] font-semibold text-gray-500 uppercase">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                <template x-if="tahsilatlar.length === 0">
                    <tr>
                        <td colspan="8" class="px-3 py-8 text-center text-gray-400">Filtreye uygun tahsilat kaydı bulunamadı.</td>
                    </tr>
                </template>

                <template x-for="tahsilat in tahsilatlar" :key="tahsilat.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-3 py-2 align-top">
                            <div class="text-gray-800 dark:text-gray-200" x-text="formatTarih(tahsilat.tahsilat_tarihi)"></div>
                            <div class="text-[11px] text-gray-500" x-text="formatTarihSaat(tahsilat.created_at)"></div>
                        </td>
                        <td class="px-3 py-2 align-top">
                            <div class="font-medium text-gray-800 dark:text-gray-200" x-text="tahsilat.muvekkil?.ad ?? '-'" ></div>
                            <div class="text-[11px] text-gray-500" x-text="tahsilat.portfoy?.ad ?? '-'" ></div>
                        </td>
                        <td class="px-3 py-2 align-top text-gray-700 dark:text-gray-300" x-text="tahsilat.protokol?.protokol_no ?? (tahsilat.protokolsuz ? 'Protokolsuz' : '-')"></td>
                        <td class="px-3 py-2 align-top">
                            <div class="text-gray-800 dark:text-gray-200" x-text="tahsilat.borclu_adi ?? '-'" ></div>
                            <div class="text-[11px] text-gray-500" x-text="tahsilat.borclu_tckn_vkn ?? '-'" ></div>
                        </td>
                        <td class="px-3 py-2 align-top text-gray-700 dark:text-gray-300" x-text="formatYontem(tahsilat.tahsilat_yontemi)"></td>
                        <td class="px-3 py-2 align-top text-right font-semibold text-gray-900 dark:text-white" x-text="formatPara(tahsilat.tutar)"></td>
                        <td class="px-3 py-2 align-top text-center">
                            <span :class="durumSinif(tahsilat.onay_durumu)" class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium" x-text="formatDurum(tahsilat.onay_durumu)"></span>
                        </td>
                        <td class="px-3 py-2 align-top text-center">
                            <div class="inline-flex items-center justify-center gap-1">
                                <button x-show="onayYetkisiVar && tahsilat.onay_durumu === 'beklemede'"
                                    @click="tahsilatOnayla(tahsilat)"
                                    class="px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-[11px] font-medium transition-colors">
                                    Onayla
                                </button>
                                <button x-show="onayYetkisiVar && tahsilat.onay_durumu === 'beklemede'"
                                    @click="redModalAc(tahsilat)"
                                    class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-[11px] font-medium transition-colors">
                                    Reddet
                                </button>
                                <button x-show="yoneticiMi && tahsilat.onay_durumu === 'onaylandi'"
                                    @click="tahsilatIptalEt(tahsilat)"
                                    class="px-2 py-1 bg-slate-600 hover:bg-slate-700 text-white rounded text-[11px] font-medium transition-colors">
                                    İptal Et
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2" x-show="!yukleniyor && sayfalama.last_page > 1">
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

    <div x-show="redModal.acik" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Red Nedeni</h3>
            <textarea x-model="redModal.neden" rows="3" placeholder="Reddetme nedenini yazin..."
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white resize-none focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
            <div class="flex justify-end gap-3 mt-4">
                <button @click="redModal.acik = false"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Iptal
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
function tumTahsilatlarTab() {
    return {
        yukleniyor: true,
        tahsilatlar: [],
        onayYetkisiVar: @js((bool) ($onayYetkisiVar || auth()->user()->isYonetici())),
        yoneticiMi: @js((bool) auth()->user()->isYonetici()),
        redModal: { acik: false, tahsilat: null, neden: '' },
        sayfalama: {
            current_page: 1,
            last_page: 1,
            per_page: 20,
            total: 0,
            from: 0,
            to: 0,
        },
        filtre: {
            q: '',
            onay_durumu: '',
            tarih_baslangic: '',
            tarih_bitis: '',
        },

        init() {
            this.yukle(1);
            window.addEventListener('tahsilat-listesi-yenile', () => this.yukle(this.sayfalama.current_page || 1));
        },

        buildParams(page = 1) {
            const params = new URLSearchParams();
            params.set('page', String(page));
            params.set('per_page', String(this.sayfalama.per_page || 20));

            if (this.filtre.q) params.set('q', this.filtre.q);
            if (this.filtre.onay_durumu) params.set('onay_durumu', this.filtre.onay_durumu);
            if (this.filtre.tarih_baslangic && this.filtre.tarih_bitis) {
                params.set('tarih_baslangic', this.filtre.tarih_baslangic);
                params.set('tarih_bitis', this.filtre.tarih_bitis);
            }

            return params;
        },

        async yukle(page = 1) {
            this.yukleniyor = true;
            try {
                const params = this.buildParams(page);
                const res = await fetch('/tahsilat/list?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (res.ok) {
                    const data = await res.json();
                    this.tahsilatlar = data.data ?? [];
                    this.sayfalama.current_page = Number(data.current_page ?? 1);
                    this.sayfalama.last_page = Number(data.last_page ?? 1);
                    this.sayfalama.per_page = Number(data.per_page ?? 20);
                    this.sayfalama.total = Number(data.total ?? 0);
                    this.sayfalama.from = Number(data.from ?? 0);
                    this.sayfalama.to = Number(data.to ?? 0);
                }
            } finally {
                this.yukleniyor = false;
            }
        },

        filtreUygula() {
            this.yukle(1);
        },

        filtreTemizle() {
            this.filtre = { q: '', onay_durumu: '', tarih_baslangic: '', tarih_bitis: '' };
            this.yukle(1);
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

        sayfaToplami() {
            return this.tahsilatlar.reduce((toplam, item) => {
                if ((item?.onay_durumu ?? '') === 'iptal') {
                    return toplam;
                }

                return toplam + Number(item?.tutar ?? 0);
            }, 0);
        },

        bekleyenSayisi() {
            return this.tahsilatlar.filter((x) => x.onay_durumu === 'beklemede').length;
        },

        onayliSayisi() {
            return this.tahsilatlar.filter((x) => x.onay_durumu === 'onaylandi').length;
        },

        sayfalamaBilgisi() {
            const from = this.sayfalama.from ?? 0;
            const to = this.sayfalama.to ?? 0;
            const total = this.sayfalama.total ?? 0;
            return from + ' - ' + to + ' / ' + total + ' kayıt';
        },

        async tahsilatOnayla(tahsilat) {
            if (!confirm('Bu tahsilati onaylamak istiyor musunuz?')) return;
            const res = await fetch('/tahsilat/' + tahsilat.id + '/onayla', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (res.ok) {
                this.yukle(this.sayfalama.current_page || 1);
                window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
                window.dispatchEvent(new CustomEvent('tahsilat-dashboard-yenile'));
                window.dispatchEvent(new CustomEvent('tahsilat-listesi-yenile'));
            } else {
                const err = await res.json();
                alert(err.error ?? 'Bir hata olustu.');
            }
        },

        redModalAc(tahsilat) {
            this.redModal = { acik: true, tahsilat, neden: '' };
        },

        async tahsilatReddet() {
            if (!this.redModal?.tahsilat?.id) return;
            if (!this.redModal.neden.trim()) {
                alert('Lutfen red nedeni girin.');
                return;
            }
            const res = await fetch('/tahsilat/' + this.redModal.tahsilat.id + '/reddet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ red_nedeni: this.redModal.neden.trim() }),
            });

            if (res.ok) {
                this.redModal.acik = false;
                this.yukle(this.sayfalama.current_page || 1);
                window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
                window.dispatchEvent(new CustomEvent('tahsilat-dashboard-yenile'));
                window.dispatchEvent(new CustomEvent('tahsilat-listesi-yenile'));
            } else {
                const err = await res.json();
                alert(err.error ?? 'Bir hata olustu.');
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
                this.yukle(this.sayfalama.current_page || 1);
                window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
                window.dispatchEvent(new CustomEvent('tahsilat-dashboard-yenile'));
                window.dispatchEvent(new CustomEvent('tahsilat-listesi-yenile'));
            } else {
                const err = await res.json();
                alert(err.error ?? 'Bir hata oluştu.');
            }
        },

        formatDurum(durum) {
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

        formatYontem(yontem) {
            if (!yontem || typeof yontem !== 'string') return '-';

            const map = {
                muvekkil: 'Müvekkil',
                vekil: 'Vekil',
                hesabina: 'Hesabına',
                mail: 'Mail',
                order: 'Order',
                eft: 'EFT',
                havale: 'Havale',
                reddiyat: 'Reddiyat',
                elden: 'Elden',
                alindi: 'Alındı',
                tahsilat: 'Tahsilat',
                yontemi: 'Yöntemi',
            };

            return yontem
                .replaceAll('_', ' ')
                .split(' ')
                .map((parca) => {
                    if (!parca) return '';
                    const lower = parca.toLowerCase();
                    if (map[lower]) return map[lower];
                    return parca[0].toUpperCase() + parca.slice(1);
                })
                .join(' ');
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
            const sayi = Number(deger ?? 0);
            if (!Number.isFinite(sayi)) return '0,00 TL';
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(sayi);
        },
    };
}
</script>

