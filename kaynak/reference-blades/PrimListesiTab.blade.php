{{-- Prim Listesi Tab --}}
<div class="p-6" x-data="primListesiTab()" x-init="yukle()">

    {{-- Filtre Paneli --}}
    <div class="mb-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/20 p-3">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">Ay</label>
                    <select x-model="ay" @change="yukle()"
                        class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-200">
                        <template x-for="(ad, i) in aylar" :key="i">
                            <option :value="i + 1" x-text="ad"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] text-gray-500 dark:text-gray-400 mb-1">Yıl</label>
                    <select x-model="yil" @change="yukle()"
                        class="h-9 px-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-200">
                        <template x-for="y in yillar" :key="y">
                            <option :value="y" x-text="y"></option>
                        </template>
                    </select>
                </div>
                <div class="h-9 px-3 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 inline-flex items-center text-xs font-medium text-blue-700 dark:text-blue-300">
                    <span x-text="secilenDonem"></span>
                </div>
            </div>
        </div>

        <div class="mt-2 grid grid-cols-2 lg:grid-cols-3 gap-2">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Hacizci</div>
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="toplamHacizciSayisi"></div>
            </div>
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Müvekkil</div>
                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="toplamMuvekkilSayisi"></div>
            </div>
            <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-900/20 px-2.5 py-2">
                <div class="text-[10px] uppercase tracking-wide text-blue-700/80 dark:text-blue-300/80">Prime Esas Tahsilat</div>
                <div class="text-sm font-semibold text-blue-700 dark:text-blue-300" x-text="formatPara(toplamPrimeEsasTahsilatTutari)"></div>
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
    <div x-show="!yukleniyor && satirlar().length === 0" class="text-center py-12 text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-sm">Bu dönemde prime esas tahsilat kaydı bulunmamaktadır.</p>
    </div>

    {{-- Prime Esas Tahsilat Tablosu --}}
    <div x-show="!yukleniyor && satirlar().length > 0" class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-blue-50 dark:bg-blue-900/20">
                <tr>
                    <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">Hacizci</th>
                    <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">Kademe</th>
                    <template x-for="m in (pivotData.muvekkiller ?? [])" :key="m.id">
                        <th class="px-3 py-2.5 text-right text-[11px] font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap" x-text="m.ad"></th>
                    </template>
                    <th class="px-3 py-2.5 text-right text-[11px] font-bold text-blue-700 dark:text-blue-400 whitespace-nowrap">PRIME ESAS TOPLAM</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                <template x-for="row in satirlar()" :key="row.hacizci_id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-white" x-text="row.hacizci_ad"></td>
                        <td class="px-3 py-2.5 text-center text-gray-500 dark:text-gray-400" x-text="row.kademe"></td>
                        <template x-for="m in (pivotData.muvekkiller ?? [])" :key="m.id">
                            <td class="px-3 py-2.5 text-right text-gray-700 dark:text-gray-300" x-text="formatPara(row[m.id] ?? 0)"></td>
                        </template>
                        <td class="px-3 py-2.5 text-right font-bold text-blue-700 dark:text-blue-400" x-text="formatPara(row.toplam_prime_esas_tahsilat ?? row.toplam_prim ?? 0)"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

</div>

<script>
function primListesiTab() {
    return {
        yukleniyor: true,
        ay: new Date().getMonth() + 1,
        yil: new Date().getFullYear(),
        pivotData: {},
        aylar: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
        yillar: Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i),

        get secilenDonem() {
            return this.aylar[this.ay - 1] + ' ' + this.yil;
        },

        satirlar() {
            const rows = this.pivotData?.prime_esas_pivot_data ?? this.pivotData?.pivot_data ?? [];
            return Array.isArray(rows) ? rows : [];
        },

        get toplamHacizciSayisi() {
            return this.satirlar().length;
        },

        get toplamMuvekkilSayisi() {
            return (this.pivotData?.muvekkiller ?? []).length;
        },

        get toplamPrimeEsasTahsilatTutari() {
            return this.satirlar().reduce((toplam, row) => {
                const deger = parseFloat(row?.toplam_prime_esas_tahsilat ?? row?.toplam_prim ?? 0);
                return toplam + (Number.isFinite(deger) ? deger : 0);
            }, 0);
        },

        async yukle() {
            this.yukleniyor = true;
            try {
                const res = await fetch(`/tahsilat/prim/pivot-table?ay=${this.ay}&yil=${this.yil}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (res.ok) {
                    this.pivotData = await res.json();
                } else {
                    this.pivotData = {};
                }
            } catch (e) {
                this.pivotData = {};
            } finally {
                this.yukleniyor = false;
            }
        },

        formatPara(deger) {
            const sayi = Number(deger ?? 0);
            if (!Number.isFinite(sayi)) return '0,00 TL';
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(sayi);
        },
    };
}
</script>
