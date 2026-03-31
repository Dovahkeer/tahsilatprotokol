{{-- Yeni Tahsilat Form Modalı --}}
<template x-teleport="body">
<div x-data="tahsilatFormModal()"
     x-show="acik"
     @tahsilat-form-ac.window="yeniKayitAc()"
     @tahsilat-form-duzenle.window="duzenlemeAc($event)"
     @protokol-listesi-yenile.window="protokolleriYukle()"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-cloak>

    <div class="fixed inset-0 bg-slate-950/65 backdrop-blur-sm"></div>
    <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
    <div class="relative w-full max-w-2xl max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3rem)] overflow-y-auto rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl" @click.stop>
        <div class="sticky top-0 z-10 px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="duzenlemeModu ? 'Tahsilat Düzenle' : 'Yeni Tahsilat'"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tahsilat bilgilerini girin ve dekont ekleyin.</p>
                </div>
                <button type="button" @click="kapat()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6">
        <form @submit.prevent="kaydet()" class="space-y-3">

            <div x-show="duzenlemeModu" class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 rounded-lg px-3 py-2">
                Düzenleme modunda protokol ve müvekkil bağlantısı korunur; sadece tahsilat detayları güncellenir.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        Borçlu Adı
                    </label>
                    <input type="text"
                        x-model="form.borclu_adi"
                        @input="borcluAramaPlanla()"
                        placeholder="Borçlu adını yazın"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                        TCKN / VKN
                    </label>
                    <input type="text"
                        x-model="form.borclu_tckn_vkn"
                        @input="borcluAramaPlanla()"
                        inputmode="numeric"
                        maxlength="11"
                        placeholder="Sadece rakam"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/25 p-3">
                <div class="flex items-center justify-between gap-3">
                    <label class="text-xs font-semibold tracking-wide text-gray-600 dark:text-gray-300 uppercase">Tahsilat Turu</label>
                    <span x-show="duzenlemeModu" class="text-[11px] text-gray-500 dark:text-gray-400">Düzenleme modunda değiştirilemez</span>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <button type="button"
                        @click="if (!duzenlemeModu) { form.protokolsuz = false; protokolSecildi(); }"
                        :disabled="duzenlemeModu"
                        :class="!form.protokolsuz
                            ? 'bg-amber-600 text-white border-amber-600 shadow-sm'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/60'"
                        class="h-10 rounded-lg border text-sm font-medium transition-colors disabled:opacity-60">
                        Protokollü
                    </button>
                    <button type="button"
                        @click="if (!duzenlemeModu) { form.protokolsuz = true; protokolSecildi(); }"
                        :disabled="duzenlemeModu"
                        :class="form.protokolsuz
                            ? 'bg-amber-600 text-white border-amber-600 shadow-sm'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/60'"
                        class="h-10 rounded-lg border text-sm font-medium transition-colors disabled:opacity-60">
                        Protokolsuz
                    </button>
                </div>
                <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400"
                    x-text="form.protokolsuz ? 'Protokol bağlantısı olmadan serbest tutarlı tahsilat girilir.' : 'Protokole bağlı ödeme kalemi ve kalan tutar ile tahsilat girilir.'"></p>
            </div>

            <div x-show="!form.protokolsuz" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/20 p-3">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Protokol <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select x-model="form.protokol_id" @change="protokolSecildi()" :disabled="duzenlemeModu"
                        class="w-full h-10 pl-3 pr-9 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-200 appearance-none disabled:opacity-50 focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                        <option value="">-- Protokol Seç --</option>
                        <template x-for="p in protokolSecenekleri()" :key="p.id">
                            <option :value="p.id" x-text="formatProtokolEtiketi(p)"></option>
                        </template>
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <p x-show="!form.protokolsuz && aramaYukleniyor && !duzenlemeModu" class="text-[11px] text-gray-500 mt-1">
                    Borçlu bilgisine göre protokoller aranıyor...
                </p>
                <p x-show="!form.protokolsuz && aramaCalisti && aramaAktifMi() && !aramaYukleniyor && eslesenProtokoller.length === 0 && !duzenlemeModu && !form.protokol_id"
                    class="text-[11px] text-amber-600 mt-1">
                    Eşleştirme kriterlerine uygun protokol bulunamadı.
                </p>
                <p x-show="!form.protokolsuz && aramaAktifMi() && eslesenProtokoller.length > 1 && !duzenlemeModu"
                    class="text-[11px] text-gray-500 mt-1">
                    Aynı borçluya ait birden fazla protokol bulundu, lütfen birini seçin.
                </p>
            </div>

            <div x-show="!form.protokolsuz && !!form.protokol_id" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/20 p-3">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Ödeme Kalemi <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select x-model="form.odeme_kalemi" @change="odemeKalemiSecildi()" :disabled="duzenlemeModu"
                        class="w-full h-10 pl-3 pr-9 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-200 appearance-none disabled:opacity-50 focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                        <option value="">-- Peşinat / Taksit Seç --</option>
                        <template x-for="k in protokolOdemeKalemleri()" :key="k.value">
                            <option :value="k.value" x-text="k.label"></option>
                        </template>
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/20 p-3">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Müvekkil <span class="text-red-500">*</span></label>
                <select x-model="form.muvekkil_id" required
                    :disabled="duzenlemeModu || !!secilenProtokolMuvekkili"
                    class="w-full h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm disabled:opacity-50 focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                    <option value="">-- Müvekkil Seç --</option>
                    <template x-for="m in muvekkiller" :key="m.id">
                        <option :value="m.id" x-text="m.ad"></option>
                    </template>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Tarih <span class="text-red-500">*</span></label>
                    <input type="date" x-model="form.tahsilat_tarihi" required
                        class="w-full h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Tutar (TL) <span class="text-red-500">*</span></label>
                    <input type="text"
                        x-model="form.tutar"
                        @input="form.tutar = formatAmountInput($event.target.value)"
                        inputmode="decimal"
                        required
                        placeholder="Örn: 150.000 veya 150.000,25"
                        class="w-full h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                    <p x-show="!form.protokolsuz && !!form.protokol_id && !duzenlemeModu" class="text-[11px] text-gray-500 mt-1">
                        Tutar varsayılan olarak seçilen kalemin kalan tutarı ile gelir; gerekirse değiştirebilirsiniz.
                    </p>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Binlik ayırıcılar otomatik eklenir. Kuruş yazacaksanız virgül kullanın (örnek: 150.000,75).
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/20 p-3">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">
                    Tahsilat Birimi <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-1.5">
                    <template x-for="birim in tahsilatBirimSecenekleri" :key="birim.value">
                        <label class="cursor-pointer">
                            <input type="checkbox"
                                :value="birim.value"
                                x-model="form.tahsilat_birimleri"
                                class="sr-only">
                            <div
                                :class="form.tahsilat_birimleri.includes(birim.value)
                                    ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300'
                                    : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                class="h-9 rounded-lg border px-2 flex items-center justify-between text-xs font-medium transition-colors">
                                <span class="truncate pr-1" x-text="birim.label"></span>
                                <span x-show="form.tahsilat_birimleri.includes(birim.value)"
                                    class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-600 text-white text-[11px]">OK</span>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/25 p-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:items-start">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2.5">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            Tahsilat Yöntemi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select x-model="form.tahsilat_yontemi"
                                class="w-full h-10 pl-3 pr-9 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-700 dark:text-gray-200 appearance-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                                <option value="">-- Seçin --</option>
                                <template x-for="yontem in tahsilatYontemSecenekleri" :key="yontem.value">
                                    <option :value="yontem.value" x-text="yontem.label"></option>
                                </template>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Yöntem seçimi zorunludur.</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2.5 w-full md:max-w-sm md:justify-self-end">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            <span x-text="duzenlemeModu ? 'Yeni Dekont (Opsiyonel)' : 'Dekont (PDF/JPG/PNG) *'"></span>
                        </label>
                        <input type="file"
                            x-ref="dekontInput"
                            @change="dekontSecildi($event)"
                            accept=".pdf,.png,.jpg,.jpeg,.webp,application/pdf,image/png,image/jpeg,image/webp"
                            class="sr-only">
                        <div class="mt-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <button type="button"
                                @click="$refs.dekontInput?.click()"
                                class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition-colors">
                                Dosya Seç
                            </button>
                            <div class="text-xs text-gray-500 dark:text-gray-400 sm:text-right">
                                PDF/JPG/PNG, en fazla 2 MB
                            </div>
                        </div>
                        <div x-show="form.dekont" class="mt-2 rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-2 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300 truncate" x-text="form.dekont?.name ?? ''"></p>
                                <p class="text-[11px] text-emerald-600/80 dark:text-emerald-300/80" x-text="formatDosyaBoyutu(form.dekont?.size ?? 0)"></p>
                            </div>
                            <button type="button"
                                @click="dekontTemizle()"
                                class="shrink-0 text-[11px] px-2 py-1 rounded-md bg-white/80 dark:bg-gray-800 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 hover:bg-white">
                                Kaldır
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1" x-text="duzenlemeModu ? 'Yeni dekont seçerseniz mevcut kayda eklenir.' : 'Maksimum 2 MB. PDF/JPG/JPEG/PNG/WEBP dosyalari kabul edilir.'"></p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Notlar</label>
                <textarea x-model="form.notlar" rows="2"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm resize-none"></textarea>
            </div>

            <div x-show="hata" class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-3 py-2 rounded-lg" x-text="hata"></div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="kapat()"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    İptal
                </button>
                <button type="submit" :disabled="kaydediliyor"
                    class="px-5 py-2 text-sm bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors">
                    <span x-show="!kaydediliyor" x-text="duzenlemeModu ? 'Değişiklikleri Kaydet' : 'Kaydet'"></span>
                    <span x-show="kaydediliyor">Kaydediliyor...</span>
                </button>
            </div>

        </form>
        </div>
    </div>
    </div>
</div>
</template>

<script>
function tahsilatFormModal() {
    return {
        acik: false,
        duzenlemeModu: false,
        duzenlenenTahsilatId: null,
        kaydediliyor: false,
        hata: '',
        maxDekontBytes: 2 * 1024 * 1024,
        izinliDekontUzantilari: ['pdf', 'png', 'jpg', 'jpeg', 'webp'],
        aramaYukleniyor: false,
        aramaCalisti: false,
        aramaZamanlayici: null,
        secilenProtokolMuvekkili: null,
        protokoller: [],
        eslesenProtokoller: [],
        muvekkiller: [],

        tahsilatYontemSecenekleri: [
            { value: 'muvekkil_hesabina_reddiyat', label: 'Müvekkil Hesabına Reddiyat' },
            { value: 'vekil_hesabina_reddiyat', label: 'Vekil Hesabına Reddiyat' },
            { value: 'muvekkil_hesabina_mail_order', label: 'Müvekkil Hesabına Mail Order' },
            { value: 'vekil_hesabina_mail_order', label: 'Vekil Hesabına Mail Order' },
            { value: 'muvekkil_hesabina_eft_havale', label: 'Müvekkil Hesabına EFT/Havale' },
            { value: 'vekil_hesabina_eft_havale', label: 'Vekil Hesabına EFT/Havale' },
            { value: 'elden_alindi', label: 'Elden Alındı' },
        ],

        tahsilatBirimSecenekleri: [
            { value: 'sulhen', label: 'Sulhen' },
            { value: 'satis', label: 'Satış' },
            { value: 'istihkak', label: 'İstihkak' },
            { value: 'takibin_devami', label: 'Takibin Devamı' },
            { value: 'nami_mustear', label: 'Namı Müstear' },
            { value: 'tasarrufun_iptali', label: 'Tasarrufun İptali' },
            { value: 'itirazin_iptali', label: 'İtirazın İptali' },
            { value: 'konkordato', label: 'Konkordato' },
        ],

        form: {
            protokolsuz: false,
            protokol_id: '',
            odeme_kalemi: '',
            muvekkil_id: '',
            borclu_adi: '',
            borclu_tckn_vkn: '',
            tahsilat_tarihi: new Date().toISOString().slice(0, 10),
            tutar: '',
            tahsilat_yontemi: '',
            tahsilat_birimleri: [],
            dekont: null,
            notlar: '',
        },

        async init() {
            try {
                const res = await fetch('/muvekkil/list', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) this.muvekkiller = await res.json();
            } catch (e) {}

            await this.protokolleriYukle();
        },

        async protokolleriYukle() {
            try {
                const res = await fetch('/tahsilat/protokol/list?per_page=100', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) {
                    const data = await res.json();
                    this.protokoller = data.data ?? [];
                }
            } catch (e) {}
        },

        yeniKayitAc() {
            this.duzenlemeModu = false;
            this.duzenlenenTahsilatId = null;
            this.acik = true;
            this.sifirla();
            this.protokolleriYukle();
        },

        async duzenlemeAc(event) {
            const id = event?.detail?.id ?? event?.detail ?? null;
            if (!id) return;

            this.sifirla();
            this.duzenlemeModu = true;
            this.duzenlenenTahsilatId = id;
            this.acik = true;

            await this.tahsilatDetayYukle(id);
        },

        kapat() {
            this.acik = false;
            this.duzenlemeModu = false;
            this.duzenlenenTahsilatId = null;
            this.sifirla();
        },

        async tahsilatDetayYukle(id) {
            try {
                const res = await fetch('/tahsilat/' + id, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!res.ok) {
                    this.hata = 'Tahsilat detayı alınamadı.';
                    return;
                }

                const tahsilat = await res.json();

                if (tahsilat?.protokol && !this.protokoller.some((p) => p.id === tahsilat.protokol.id)) {
                    this.protokoller.unshift(tahsilat.protokol);
                }

                const odemeKalemiMeta = tahsilat?.tahsilat_turu?.odeme_kalemi ?? null;
                let odemeKalemi = '';
                if (odemeKalemiMeta && typeof odemeKalemiMeta === 'object') {
                    if (odemeKalemiMeta.tip === 'pesinat') {
                        odemeKalemi = 'pesinat';
                    }
                    if (odemeKalemiMeta.tip === 'taksit' && odemeKalemiMeta.taksit_id) {
                        odemeKalemi = `taksit:${odemeKalemiMeta.taksit_id}`;
                    }
                }

                this.form = {
                    protokolsuz: !!tahsilat.protokolsuz,
                    protokol_id: tahsilat.protokol_id ?? '',
                    odeme_kalemi: odemeKalemi,
                    muvekkil_id: tahsilat.muvekkil_id ?? '',
                    borclu_adi: tahsilat.borclu_adi ?? '',
                    borclu_tckn_vkn: (tahsilat.borclu_tckn_vkn ?? '').replace(/\D/g, '').slice(0, 11),
                    tahsilat_tarihi: (tahsilat.tahsilat_tarihi ?? '').slice(0, 10),
                    tutar: this.formatAmountFromNumber(tahsilat.tutar ?? ''),
                    tahsilat_yontemi: tahsilat.tahsilat_yontemi ?? '',
                    tahsilat_birimleri: Array.isArray(tahsilat.tahsilat_birimleri) ? tahsilat.tahsilat_birimleri : [],
                    dekont: null,
                    notlar: tahsilat.notlar ?? '',
                };

                if (!this.form.protokolsuz && this.form.protokol_id) {
                    const secilen = this.protokoller.find((p) => p.id === this.form.protokol_id)
                        ?? this.eslesenProtokoller.find((p) => p.id === this.form.protokol_id);

                    this.secilenProtokolMuvekkili = secilen?.muvekkil_id ?? (tahsilat.muvekkil_id ?? null);
                } else {
                    this.secilenProtokolMuvekkili = null;
                }
            } catch (e) {
                this.hata = 'Tahsilat detayı alınırken hata oluştu.';
            }
        },

        aramaAktifMi() {
            const borcluAdi = (this.form.borclu_adi ?? '').trim();
            const borcluTcknVkn = (this.form.borclu_tckn_vkn ?? '').replace(/\D/g, '');
            return borcluAdi.length >= 2 || borcluTcknVkn.length >= 3;
        },

        protokolSecenekleri() {
            if (this.aramaAktifMi() && !this.duzenlemeModu && this.eslesenProtokoller.length > 0) {
                return this.eslesenProtokoller;
            }

            return this.protokoller;
        },

        formatProtokolEtiketi(protokol) {
            const borclu = protokol?.borclu_adi ?? '';
            const tcknVkn = protokol?.borclu_tckn_vkn ? ` (${protokol.borclu_tckn_vkn})` : '';
            return `${protokol.protokol_no} - ${borclu}${tcknVkn}`;
        },

        borcluAramaPlanla() {
            this.form.borclu_tckn_vkn = (this.form.borclu_tckn_vkn ?? '').replace(/\D/g, '').slice(0, 11);

            if (this.form.protokolsuz || this.duzenlemeModu) {
                this.aramaCalisti = false;
                return;
            }

            if (this.aramaZamanlayici) {
                clearTimeout(this.aramaZamanlayici);
            }

            this.aramaZamanlayici = setTimeout(() => this.protokolAra(), 350);
        },

        async protokolAra() {
            if (!this.aramaAktifMi()) {
                this.eslesenProtokoller = [];
                this.aramaYukleniyor = false;
                this.aramaCalisti = false;
                return;
            }

            this.aramaYukleniyor = true;
            this.aramaCalisti = false;

            try {
                const params = new URLSearchParams();
                const borcluAdi = (this.form.borclu_adi ?? '').trim();
                const borcluTcknVkn = (this.form.borclu_tckn_vkn ?? '').replace(/\D/g, '');

                if (borcluAdi.length >= 2) {
                    params.set('borclu_adi', borcluAdi);
                }

                if (borcluTcknVkn.length >= 3) {
                    params.set('borclu_tckn_vkn', borcluTcknVkn);
                }

                params.set('limit', '25');

                const res = await fetch('/tahsilat/protokol/borclu-ara?' + params.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!res.ok) return;

                const data = await res.json();
                this.eslesenProtokoller = Array.isArray(data) ? data : [];
                this.aramaCalisti = true;

                if (this.eslesenProtokoller.length === 1) {
                    this.form.protokol_id = this.eslesenProtokoller[0].id;
                    this.protokolSecildi();
                    return;
                }

                const seciliVar = this.eslesenProtokoller.some(p => p.id === this.form.protokol_id);
                if (!seciliVar) {
                    this.form.protokol_id = '';
                    this.form.odeme_kalemi = '';
                    this.form.tutar = '';
                    this.secilenProtokolMuvekkili = null;
                }
            } finally {
                this.aramaYukleniyor = false;
            }
        },

        protokolSecildi() {
            if (this.form.protokolsuz) {
                this.form.protokol_id = '';
                this.form.odeme_kalemi = '';
                this.form.tutar = '';
                this.secilenProtokolMuvekkili = null;
                return;
            }

            const secilen = this.protokolSecenekleri().find(p => p.id === this.form.protokol_id)
                ?? this.protokoller.find(p => p.id === this.form.protokol_id)
                ?? this.eslesenProtokoller.find(p => p.id === this.form.protokol_id);

            if (this.duzenlemeModu) {
                this.secilenProtokolMuvekkili = secilen?.muvekkil_id ?? this.secilenProtokolMuvekkili;
                return;
            }

            this.form.odeme_kalemi = '';
            this.form.tutar = '';

            if (secilen) {
                this.form.muvekkil_id = secilen.muvekkil_id;
                this.form.borclu_adi = secilen.borclu_adi ?? '';
                this.form.borclu_tckn_vkn = (secilen.borclu_tckn_vkn ?? '').replace(/\D/g, '').slice(0, 11);
                this.secilenProtokolMuvekkili = secilen.muvekkil_id;

                const kalemler = this.protokolOdemeKalemleri();
                if (kalemler.length === 1) {
                    this.form.odeme_kalemi = kalemler[0].value;
                    this.form.tutar = this.formatAmountFromNumber(kalemler[0].tutar);
                }
            } else {
                this.secilenProtokolMuvekkili = null;
            }
        },

        protokolOdemeKalemleri() {
            const secilen = this.protokolSecenekleri().find(p => p.id === this.form.protokol_id)
                ?? this.protokoller.find(p => p.id === this.form.protokol_id)
                ?? this.eslesenProtokoller.find(p => p.id === this.form.protokol_id);
            if (!secilen) return [];

            const items = [];
            const pesinat = Number(secilen.pesinat ?? 0);
            if (pesinat > 0) {
                items.push({
                    value: 'pesinat',
                    tutar: pesinat,
                    label: 'Peşinat - ' + this.formatPara(pesinat),
                });
            }

            for (const t of (secilen.taksitler ?? [])) {
                const kalanTutar = Number(t.kalan_tutar ?? t.taksit_tutari ?? 0);
                if (kalanTutar <= 0) {
                    continue;
                }

                const tarih = t.taksit_tarihi ?? t.vade_tarihi ?? '';
                const taksitNo = t.taksit_no ? `${t.taksit_no}. Taksit` : 'Taksit';
                const tarihText = tarih ? ` - ${tarih}` : '';

                items.push({
                    value: `taksit:${t.id}`,
                    tutar: kalanTutar,
                    label: `${taksitNo}${tarihText} - Kalan ${this.formatPara(kalanTutar)}`,
                });
            }

            return items;
        },

        odemeKalemiSecildi() {
            const secim = this.protokolOdemeKalemleri().find(k => k.value === this.form.odeme_kalemi);
            this.form.tutar = secim ? this.formatAmountFromNumber(secim.tutar) : '';
        },

        dekontSecildi(event) {
            const dosya = event?.target?.files?.[0] ?? null;
            if (!dosya) {
                this.form.dekont = null;
                return;
            }

            const uzanti = ((dosya.name ?? '').split('.').pop() ?? '').toLowerCase();
            if (!this.izinliDekontUzantilari.includes(uzanti)) {
                this.hata = 'Dekont formati gecersiz. PDF/JPG/JPEG/PNG/WEBP yukleyebilirsiniz.';
                this.form.dekont = null;
                if (this.$refs.dekontInput) {
                    this.$refs.dekontInput.value = '';
                }
                return;
            }

            if (dosya.size > this.maxDekontBytes) {
                this.hata = 'Dekont en fazla 2 MB olabilir.';
                this.form.dekont = null;
                if (this.$refs.dekontInput) {
                    this.$refs.dekontInput.value = '';
                }
                return;
            }

            this.hata = '';
            this.form.dekont = dosya;
        },

        dekontTemizle() {
            this.form.dekont = null;
            if (this.$refs.dekontInput) {
                this.$refs.dekontInput.value = '';
            }
        },

        async kaydet() {
            this.hata = '';

            if (!this.duzenlemeModu) {
                if (!this.form.protokolsuz) {
                    if (!this.form.protokol_id) {
                        this.hata = 'Protokollü tahsilat için protokol seçimi zorunludur.';
                        return;
                    }
                    if (!this.form.odeme_kalemi) {
                        this.hata = 'Peşinat veya taksit kalemlerinden birini seçmelisiniz.';
                        return;
                    }

                    if (this.form.odeme_kalemi.startsWith('taksit:')) {
                        const secim = this.protokolOdemeKalemleri().find(k => k.value === this.form.odeme_kalemi);
                        const girilen = this.parseAmount(this.form.tutar);
                        const kalan = Number(secim?.tutar ?? 0);
                        if (!Number.isFinite(girilen) || girilen <= 0) {
                            this.hata = 'Taksit tahsilatında tutar zorunludur.';
                            return;
                        }
                        if (girilen - kalan > 0.0001) {
                            this.hata = 'Girilen tutar seçilen taksidin kalan tutarını geçemez.';
                            return;
                        }
                    }
                }

                if (!this.form.dekont) {
                    this.hata = 'Dekont yuklemek zorunludur (PDF/JPG/JPEG/PNG/WEBP).';
                    return;
                }
            }

            if (!this.form.tahsilat_yontemi) {
                this.hata = 'Tahsilat yöntemi seçimi zorunludur.';
                return;
            }

            if (!Array.isArray(this.form.tahsilat_birimleri) || this.form.tahsilat_birimleri.length === 0) {
                this.hata = 'En az bir tahsilat birimi seçmelisiniz.';
                return;
            }

            const normalizedTutar = this.toBackendAmount(this.form.tutar);
            if (!normalizedTutar || Number(normalizedTutar) <= 0) {
                this.hata = 'Tahsilat tutarı zorunludur.';
                return;
            }

            this.kaydediliyor = true;
            try {
                if (this.duzenlemeModu) {
                    await this.guncelle();
                } else {
                    await this.olustur();
                }
            } catch (e) {
                this.hata = e?.message || 'Sunucu hatası. Lütfen tekrar deneyin.';
            } finally {
                this.kaydediliyor = false;
            }
        },

        async olustur() {
            const normalizedTutar = this.toBackendAmount(this.form.tutar);
            const formData = new FormData();
            formData.append('protokolsuz', this.form.protokolsuz ? '1' : '0');
            formData.append('protokol_id', this.form.protokol_id ?? '');
            formData.append('odeme_kalemi', this.form.odeme_kalemi ?? '');
            formData.append('muvekkil_id', this.form.muvekkil_id ?? '');
            formData.append('borclu_adi', this.form.borclu_adi ?? '');
            formData.append('borclu_tckn_vkn', this.form.borclu_tckn_vkn ?? '');
            formData.append('tahsilat_tarihi', this.form.tahsilat_tarihi ?? '');
            formData.append('tutar', normalizedTutar ?? '');
            formData.append('tahsilat_yontemi', this.form.tahsilat_yontemi ?? '');
            formData.append('notlar', this.form.notlar ?? '');
            formData.append('dekont', this.form.dekont);

            for (const birim of this.form.tahsilat_birimleri) {
                formData.append('tahsilat_birimleri[]', birim);
            }

            const res = await fetch('/tahsilat/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await res.json();

            if (res.ok && data.success) {
                this.kapat();
                window.dispatchEvent(new CustomEvent('tahsilat-listesi-yenile'));
                window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
                return;
            }

            const ilkDogrulamaHatasi = data?.errors ? Object.values(data.errors).flat()[0] : null;
            throw new Error(data.error ?? ilkDogrulamaHatasi ?? (data.message ?? 'Bir hata oluştu.'));
        },

        async guncelle() {
            const normalizedTutar = this.toBackendAmount(this.form.tutar);
            const payload = {
                tahsilat_tarihi: this.form.tahsilat_tarihi ?? '',
                tutar: normalizedTutar ?? '',
                tahsilat_yontemi: this.form.tahsilat_yontemi ?? '',
                tahsilat_birimleri: this.form.tahsilat_birimleri ?? [],
                borclu_adi: this.form.borclu_adi ?? '',
                borclu_tckn_vkn: this.form.borclu_tckn_vkn ?? '',
                notlar: this.form.notlar ?? '',
            };

            const res = await fetch('/tahsilat/' + this.duzenlenenTahsilatId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                const ilkDogrulamaHatasi = data?.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(data.error ?? ilkDogrulamaHatasi ?? (data.message ?? 'Bir hata oluştu.'));
            }

            if (this.form.dekont) {
                await this.dekontYukle(this.duzenlenenTahsilatId);
            }

            this.kapat();
            window.dispatchEvent(new CustomEvent('tahsilat-listesi-yenile'));
            window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
        },

        async dekontYukle(tahsilatId) {
            const formData = new FormData();
            formData.append('dekont', this.form.dekont);

            const res = await fetch('/tahsilat/' + tahsilatId + '/dekont', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            if (res.ok) {
                return;
            }

            let data = null;
            try {
                data = await res.json();
            } catch (e) {
                // noop
            }

            throw new Error(data?.error ?? data?.message ?? 'Dekont yüklenemedi.');
        },

        sifirla() {
            if (this.aramaZamanlayici) {
                clearTimeout(this.aramaZamanlayici);
            }

            this.form = {
                protokolsuz: false,
                protokol_id: '',
                odeme_kalemi: '',
                muvekkil_id: '',
                borclu_adi: '',
                borclu_tckn_vkn: '',
                tahsilat_tarihi: new Date().toISOString().slice(0, 10),
                tutar: '',
                tahsilat_yontemi: '',
                tahsilat_birimleri: [],
                dekont: null,
                notlar: '',
            };
            this.eslesenProtokoller = [];
            this.secilenProtokolMuvekkili = null;
            this.hata = '';
            this.aramaYukleniyor = false;
            this.aramaCalisti = false;

            if (this.$refs.dekontInput) {
                this.$refs.dekontInput.value = '';
            }
        },

        formatPara(deger) {
            const sayi = Number(deger ?? 0);
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(sayi);
        },

        formatDosyaBoyutu(byteDegeri) {
            const bytes = Number(byteDegeri ?? 0);
            if (!Number.isFinite(bytes) || bytes <= 0) {
                return '0 KB';
            }
            if (bytes < 1024) {
                return bytes + ' B';
            }
            if (bytes < 1024 * 1024) {
                return (bytes / 1024).toFixed(1) + ' KB';
            }
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        },

        formatAmountInput(rawValue) {
            const cleaned = String(rawValue ?? '')
                .replace(/\s+/g, '')
                .replace(/[^\d,]/g, '');

            if (cleaned === '') {
                return '';
            }

            const commaIndex = cleaned.indexOf(',');
            let whole = commaIndex >= 0 ? cleaned.slice(0, commaIndex) : cleaned;
            let decimal = commaIndex >= 0 ? cleaned.slice(commaIndex + 1).replace(/,/g, '') : '';

            whole = whole.replace(/^0+(?=\d)/, '');
            if (whole === '') {
                whole = '0';
            }
            const groupedWhole = this.groupThousands(whole);

            if (commaIndex < 0) {
                return groupedWhole;
            }

            decimal = decimal.slice(0, 2);
            return `${groupedWhole},${decimal}`;
        },

        formatAmountFromNumber(value) {
            if (value === null || value === undefined || value === '') {
                return '';
            }

            const amount = Number(value);
            if (!Number.isFinite(amount)) {
                return '';
            }

            const fixed = amount.toFixed(2);
            const [whole, decimal] = fixed.split('.');
            const groupedWhole = this.groupThousands(whole);

            if (decimal === '00') {
                return groupedWhole;
            }

            return `${groupedWhole},${decimal}`;
        },

        groupThousands(value) {
            const digits = String(value ?? '').replace(/\D/g, '');
            if (digits === '') {
                return '';
            }
            return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        parseAmount(value) {
            const raw = String(value ?? '').trim();
            if (raw === '') {
                return NaN;
            }

            const normalized = raw
                .replace(/\./g, '')
                .replace(/\s+/g, '')
                .replace(',', '.')
                .replace(/[^0-9.]/g, '');

            if (normalized === '' || normalized === '.') {
                return NaN;
            }

            const parsed = Number(normalized);
            return Number.isFinite(parsed) ? parsed : NaN;
        },

        toBackendAmount(value) {
            const parsed = this.parseAmount(value);
            if (!Number.isFinite(parsed)) {
                return null;
            }

            return parsed.toFixed(2);
        },
    };
}
</script>


