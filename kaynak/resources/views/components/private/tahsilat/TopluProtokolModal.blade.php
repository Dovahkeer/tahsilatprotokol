<div x-data="topluProtokolModal()"
     x-show="acik"
     x-cloak
     @protokol-toplu-modal-ac.window="acik = true; reset()"
     @keydown.escape.window="acik = false"
     class="fixed inset-0 z-[70] overflow-y-auto"
     style="display: none;">

    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="acik = false"></div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative w-full max-w-3xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Toplu Protokol Ekle</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Bu işlem sadece protokol üst bilgilerini toplu şekilde sisteme ekler.
                    </p>
                </div>
                <button @click="acik = false"
                        class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-900/20 p-3">
                    <div class="text-sm font-medium text-blue-800 dark:text-blue-300">1. Excel �?ablonunu İndir</div>
                    <div class="text-xs text-blue-700/90 dark:text-blue-300/90 mt-1">
                        Zorunlu sütunlar:
                        <strong>MÜVEKKİL</strong>, <strong>TARİH</strong>, <strong>BORÇLU ADI</strong>,
                        <strong>TCKN VKN</strong>, <strong>PE�?İNAT</strong>, <strong>TOPLAM PROTKOL BEDELİ</strong>.
                    </div>
                    <div class="text-xs text-blue-700/90 dark:text-blue-300/90 mt-1">
                        Opsiyonel sütunlar: PORTFÖY, MUHATAP ADI ve TELEFON. Hacizci veya taksit kolonu beklenmez.
                    </div>
                    <button @click="sablonIndir()"
                            class="mt-3 inline-flex items-center gap-2 h-9 px-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                        </svg>
                        �?ablon Excel İndir
                    </button>
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40 p-3">
                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200">2. Excel Dosyasını Yükle</div>
                    <input type="file"
                           @change="dosyaSec($event)"
                           accept=".xlsx,.xls,.csv"
                           class="mt-2 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-amber-100 file:text-amber-700 file:font-medium hover:file:bg-amber-200">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2" x-show="secilenDosya" x-text="'Seçilen dosya: ' + secilenDosya"></div>
                </div>

                <template x-if="hata">
                    <div class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-3 text-sm text-red-700 dark:text-red-300" x-text="hata"></div>
                </template>

                <template x-if="sonuc">
                    <div class="rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-3">
                        <div class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Import Tamamlandı</div>
                        <div class="mt-1 text-xs text-emerald-700/90 dark:text-emerald-300/90">
                            Eklenen: <strong x-text="sonuc.inserted ?? 0"></strong> |
                            Atlanan: <strong x-text="sonuc.skipped ?? 0"></strong>
                        </div>
                        <template x-if="(sonuc.errors ?? []).length > 0">
                            <div class="mt-2 text-xs text-emerald-700/90 dark:text-emerald-300/90 max-h-24 overflow-auto space-y-1">
                                <template x-for="err in sonuc.errors" :key="err">
                                    <div x-text="err"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                <button @click="acik = false"
                        class="h-9 px-4 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                    Kapat
                </button>
                <button @click="importEt()"
                        :disabled="yukleniyor || !dosya"
                        class="h-9 px-4 rounded-lg bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white text-sm font-medium transition-colors">
                    <span x-show="!yukleniyor">Toplu Protokol Ekle</span>
                    <span x-show="yukleniyor">Yükleniyor...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function topluProtokolModal() {
    return {
        acik: false,
        dosya: null,
        secilenDosya: '',
        yukleniyor: false,
        sonuc: null,
        hata: null,

        reset() {
            this.dosya = null;
            this.secilenDosya = '';
            this.yukleniyor = false;
            this.sonuc = null;
            this.hata = null;
        },

        sablonIndir() {
            window.location.href = '/tahsilat/protokol-toplu/sablon';
        },

        dosyaSec(event) {
            const file = event?.target?.files?.[0] ?? null;
            this.dosya = file;
            this.secilenDosya = file ? file.name : '';
            this.sonuc = null;
            this.hata = null;
        },

        async importEt() {
            if (!this.dosya || this.yukleniyor) {
                return;
            }

            this.yukleniyor = true;
            this.hata = null;
            this.sonuc = null;

            try {
                const formData = new FormData();
                formData.append('file', this.dosya);

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
                const res = await fetch('/tahsilat/protokol-toplu/import', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    this.hata = data.message ?? 'Import sırasında hata oluştu.';
                    return;
                }

                this.sonuc = data;
                window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
                window.dispatchEvent(new CustomEvent('tahsilat-dashboard-yenile'));
            } catch (e) {
                this.hata = 'Bağlantı hatası oluştu.';
            } finally {
                this.yukleniyor = false;
            }
        },
    };
}
</script>


