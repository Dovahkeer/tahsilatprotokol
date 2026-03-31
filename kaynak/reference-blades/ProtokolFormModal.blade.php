{{-- Yeni Protokol Form Modalı --}}
@php
    $yoneticiMi = auth()->check() && auth()->user()->isYonetici();
@endphp
<div x-data="protokolFormModal()"
     x-show="acik"
     @protokol-form-ac.window="modalAc()"
     @protokol-form-duzenle.window="modalDuzenle($event)"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/55 backdrop-blur-[2px]"
     x-cloak>

    <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl border border-gray-200/80 dark:border-gray-700 shadow-2xl w-full max-w-3xl max-h-[92vh] overflow-y-auto" @click.stop>
        <div class="sticky top-0 z-10 px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-amber-50 to-white dark:from-gray-800 dark:to-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="duzenlemeModu ? 'Protokol Düzenle' : 'Yeni Protokol'"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Borçlu, tutar ve hacizci dağılım bilgilerini girin.</p>
                </div>
                <button @click="kapat()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6">

        <form @submit.prevent="kaydet()" class="space-y-5">

            {{-- Temel Bilgiler --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Müvekkil --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Müvekkil <span class="text-red-500">*</span></label>
                    <select x-model="form.muvekkil_id" @change="muvekkilDegisti()" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        <option value="">- Seçin -</option>
                        <template x-for="m in muvekkiller" :key="m.id">
                            <option :value="m.id" x-text="m.ad"></option>
                        </template>
                    </select>
                </div>

                {{-- Portföy --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Portföy</label>
                    <select x-model="form.portfoy_id" :disabled="!form.muvekkil_id || portfoyYukleniyor"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm disabled:opacity-50">
                        <option value="">- Yok -</option>
                        <template x-for="p in portfoyler" :key="p.id">
                            <option :value="p.id" x-text="p.ad + (p.kod ? ' (' + p.kod + ')' : '')"></option>
                        </template>
                    </select>
                    <p x-show="portfoyYukleniyor" class="text-xs text-gray-400 mt-1">Yükleniyor...</p>
                </div>

                {{-- Protokol Tarihi --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Protokol Tarihi <span class="text-red-500">*</span></label>
                    <input type="date" x-model="form.protokol_tarihi" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                </div>

                {{-- Protokol Durumu (Sadece Yönetici + Düzenleme) --}}
                <div x-show="duzenlemeModu && yoneticiMi">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Protokol Durumu</label>
                    <div class="inline-flex w-full rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                        <button type="button"
                            @click="form.aktif = true"
                            :class="form.aktif ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                            class="flex-1 px-3 py-2 text-xs font-medium transition-colors">
                            Aktif
                        </button>
                        <button type="button"
                            @click="form.aktif = false"
                            :class="!form.aktif ? 'bg-rose-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                            class="flex-1 px-3 py-2 text-xs font-medium border-l border-gray-300 dark:border-gray-600 transition-colors">
                            Pasif
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Pasife alınan protokoller listede gösterilmez.</p>
                </div>

                {{-- Borçlu Adı --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Borçlu Adı <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.borclu_adi" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                </div>

                {{-- TCKN/VKN --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">TCKN / VKN</label>
                    <input type="text" x-model="form.borclu_tckn_vkn" maxlength="20"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                </div>

                {{-- Peşinat --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Peşinat (TL)</label>
                    <input type="text"
                        x-model="form.pesinat"
                        @input="form.pesinat = formatAmountInput($event.target.value)"
                        inputmode="decimal"
                        placeholder="Örn: 150.000 veya 150.000,25"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                </div>

                {{-- Muhatap Bilgisi --}}
                <div class="sm:col-span-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Muhatap Adı <span class="text-red-500">*</span></label>
                            <input type="text" x-model="form.muhatap_adi" maxlength="255" :required="!duzenlemeModu"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Muhatap Telefonu <span class="text-red-500">*</span></label>
                            <input type="text" x-model="form.muhatap_telefon" maxlength="30" :required="!duzenlemeModu" placeholder="05xx..."
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Toplam Tutar --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Toplam Protokol Tutarı (TL) <span class="text-red-500">*</span></label>
                    <input type="text"
                        x-model="form.toplam_protokol_tutari"
                        @input="form.toplam_protokol_tutari = formatAmountInput($event.target.value)"
                        inputmode="decimal"
                        required
                        placeholder="Örn: 1.500.000 veya 1.500.000,50"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                    <p class="text-[11px] text-gray-500 mt-1">Binlik ayırıcılar otomatik eklenir. Kuruş için virgül kullanın.</p>
                </div>

                {{-- PDF Yukleme --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Protokol PDF</label>
                    <input type="file" @change="pdfSecildi($event)" accept=".pdf"
                        class="w-full px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-amber-100 file:text-amber-700">
                    <p x-show="form.pdf_dosya" class="text-xs text-green-600 mt-1" x-text="form.pdf_dosya?.name"></p>
                </div>
            </div>

            {{-- Hacizciler --}}
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Hacizciler <span class="text-red-500">*</span></label>
                    <button type="button" @click="hacizciEkle()"
                        class="text-xs text-amber-600 hover:text-amber-700 font-medium">+ Hacizci Ekle</button>
                </div>
                <div x-show="manuelPayZorunlu()" class="mb-2 px-2 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-xs text-amber-700 dark:text-amber-300">
                    3 hacizci seçildiği için manuel pay oranları zorunludur. Toplam:
                    <span class="font-semibold" x-text="oranToplami().toFixed(2) + '%' "></span>
                </div>
                <div class="space-y-2">
                    <template x-for="(h, i) in form.hacizciler" :key="i">
                        <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/40 p-2 rounded-lg">
                            <select x-model="h.hacizci_id" required
                                class="flex-1 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">
                                <option value="">- Hacizci Seçin -</option>
                                <template x-for="hc in hacizciler" :key="hc.id">
                                    <option :value="hc.id" x-text="hc.ad_soyad + (hc.sicil_no ? ' (' + hc.sicil_no + ')' : '')"></option>
                                </template>
                            </select>
                            <select x-model="h.haciz_turu" required
                                class="w-36 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">
                                <option value="">- Haciz Türü -</option>
                                <option value="istihkakli">İstihkaklı</option>
                                <option value="nami_mustear">Namı Müstear</option>
                                <option value="97">97</option>
                                <option value="ihtiyati">İhtiyati</option>
                            </select>
                            <input type="number" x-model="h.pay_orani"
                                :required="manuelPayZorunlu()"
                                :disabled="!manuelPayZorunlu()"
                                min="0" max="100" step="0.01" placeholder="Pay %"
                                class="w-24 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs disabled:opacity-50">
                            <button type="button" @click="form.hacizciler.splice(i, 1)"
                                class="text-red-400 hover:text-red-600 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <p x-show="form.hacizciler.length === 0" class="text-xs text-gray-400 text-center py-2">
                        En az bir hacizci eklemeniz gerekmektedir.
                    </p>
                </div>
            </div>

            {{-- Taksitler --}}
            <div x-show="!duzenlemeModu" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Taksitler</label>
                    <button type="button" @click="taksitEkle()"
                        class="text-xs text-amber-600 hover:text-amber-700 font-medium">+ Taksit Ekle</button>
                </div>
                <div class="space-y-2">
                    <template x-for="(taksit, i) in form.taksitler" :key="i">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400 w-16 flex-shrink-0" x-text="(i+1) + '. Taksit'"></span>
                            <input type="date" x-model="taksit.taksit_tarihi" required
                                class="flex-1 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">
                            <input type="text"
                                x-model="taksit.taksit_tutari"
                                @input="taksit.taksit_tutari = formatAmountInput($event.target.value)"
                                inputmode="decimal"
                                placeholder="Tutar"
                                required
                                class="w-28 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">
                            <button type="button" @click="form.taksitler.splice(i, 1)"
                                class="text-red-400 hover:text-red-600 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <p x-show="form.taksitler.length === 0" class="text-xs text-gray-400 text-center py-2">
                        Taksit eklenmedi. Peşin ödeme olarak işlenecektir.
                    </p>
                </div>
            </div>

            <div x-show="duzenlemeModu" class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 rounded-lg px-3 py-2">
                Taksit planı mevcut haliyle korunur. Hacizci dağılımını bu ekrandan güncelleyebilirsiniz.
            </div>

            {{-- Hata --}}
            <div x-show="hata" class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-3 py-2 rounded-lg" x-text="hata"></div>

            {{-- Butonlar --}}
            <div class="flex justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="kapat()"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    İptal
                </button>
                <button type="submit" :disabled="kaydediliyor || form.hacizciler.length === 0"
                    class="px-5 py-2 text-sm bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors">
                    <span x-show="!kaydediliyor" x-text="duzenlemeModu ? 'Değişiklikleri Kaydet' : 'Protokol Oluştur'"></span>
                    <span x-show="kaydediliyor" x-text="duzenlemeModu ? 'Kaydediliyor...' : 'Oluşturuluyor...'"></span>
                </button>
            </div>

        </form>
        </div>
    </div>
</div>

<script>
function protokolFormModal() {
    return {
        acik: false,
        duzenlemeModu: false,
        duzenlenenProtokolId: null,
        kaydediliyor: false,
        hata: '',
        yoneticiMi: @js((bool) $yoneticiMi),
        muvekkiller: [],
        portfoyler: [],
        hacizciler: [],
        portfoyYukleniyor: false,

        form: {
            muvekkil_id: '',
            portfoy_id: '',
            protokol_tarihi: new Date().toISOString().slice(0, 10),
            borclu_adi: '',
            borclu_tckn_vkn: '',
            aktif: true,
            muhatap_adi: '',
            muhatap_telefon: '',
            pesinat: '',
            toplam_protokol_tutari: '',
            taksitler: [],
            hacizciler: [],
            pdf_dosya: null,
        },

        async modalAc() {
            this.acik = true;
            this.duzenlemeModu = false;
            this.duzenlenenProtokolId = null;
            this.resetForm();
            await Promise.all([
                this.muvekkillerYukle(),
                this.hacizcilerYukle()
            ]);
        },

        async modalDuzenle(event) {
            const id = event?.detail?.id ?? event?.detail ?? null;
            if (!id) return;

            this.acik = true;
            this.duzenlemeModu = true;
            this.duzenlenenProtokolId = id;
            this.resetForm();

            await Promise.all([
                this.muvekkillerYukle(),
                this.hacizcilerYukle()
            ]);

            await this.protokolDetayYukle(id);
        },

        kapat() {
            this.acik = false;
            this.duzenlemeModu = false;
            this.duzenlenenProtokolId = null;
            this.resetForm();
        },

        resetForm() {
            this.form = {
                muvekkil_id: '',
                portfoy_id: '',
                protokol_tarihi: new Date().toISOString().slice(0, 10),
                borclu_adi: '',
                borclu_tckn_vkn: '',
                aktif: true,
                muhatap_adi: '',
                muhatap_telefon: '',
                pesinat: '',
                toplam_protokol_tutari: '',
                taksitler: [],
                hacizciler: [],
                pdf_dosya: null,
            };
            this.portfoyler = [];
            this.hata = '';
        },

        async muvekkillerYukle() {
            try {
                const res = await fetch('/muvekkil/list', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) this.muvekkiller = await res.json();
            } catch (e) {
                // noop
            }
        },

        async hacizcilerYukle() {
            try {
                const res = await fetch('/tahsilat/protokol/hacizciler', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) this.hacizciler = await res.json();
            } catch (e) {
                // noop
            }
        },

        async protokolDetayYukle(id) {
            try {
                const res = await fetch('/tahsilat/protokol/' + id, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) {
                    this.hata = 'Protokol detayları alınamadı.';
                    return;
                }

                const protokol = await res.json();
                const seciliPortfoyId = protokol.portfoy_id ?? '';

                this.form = {
                    muvekkil_id: protokol.muvekkil_id ?? '',
                    portfoy_id: seciliPortfoyId,
                    protokol_tarihi: (protokol.protokol_tarihi ?? '').slice(0, 10),
                    borclu_adi: protokol.borclu_adi ?? '',
                    borclu_tckn_vkn: protokol.borclu_tckn_vkn ?? '',
                    aktif: Boolean(protokol.aktif ?? true),
                    muhatap_adi: protokol.muhatap_adi ?? '',
                    muhatap_telefon: protokol.muhatap_telefon ?? '',
                    pesinat: this.formatAmountFromNumber(protokol.pesinat ?? ''),
                    toplam_protokol_tutari: this.formatAmountFromNumber(protokol.toplam_protokol_tutari ?? ''),
                    taksitler: Array.isArray(protokol.taksitler)
                        ? protokol.taksitler.map((t) => ({
                            id: t.id,
                            taksit_tarihi: (t.taksit_tarihi ?? '').slice(0, 10),
                            taksit_tutari: this.formatAmountFromNumber(t.taksit_tutari ?? ''),
                            odendi: !!t.odendi,
                        }))
                        : [],
                    hacizciler: Array.isArray(protokol.hacizciler)
                        ? protokol.hacizciler.map((h) => ({
                            hacizci_id: h.id,
                            haciz_turu: this.normalizeHacizTuru(h?.pivot?.haciz_turu ?? ''),
                            pay_orani: h?.pivot?.pay_orani ?? '',
                        }))
                        : [],
                    pdf_dosya: null,
                };

                const seciliHacizciler = Array.isArray(protokol.hacizciler) ? protokol.hacizciler : [];
                for (const h of seciliHacizciler) {
                    if (!h?.id) {
                        continue;
                    }

                    const varMi = this.hacizciler.some((hc) => hc.id === h.id);
                    if (varMi) {
                        continue;
                    }

                    this.hacizciler.push({
                        id: h.id,
                        ad_soyad: h.ad_soyad ?? ('Hacizci ' + String(h.id).slice(0, 8)),
                        sicil_no: h.sicil_no ?? null,
                        kademe: h.kademe ?? null,
                    });
                }

                if (this.form.muvekkil_id) {
                    await this.muvekkilDegisti(true);
                    this.form.portfoy_id = seciliPortfoyId;
                }
            } catch (e) {
                this.hata = 'Protokol detayları alınırken hata oluştu.';
            }
        },

        async muvekkilDegisti(portfoyKoru = false) {
            const seciliPortfoy = this.form.portfoy_id;

            if (!portfoyKoru) {
                this.form.portfoy_id = '';
            }
            this.portfoyler = [];

            if (!this.form.muvekkil_id) return;

            this.portfoyYukleniyor = true;
            try {
                const res = await fetch('/tahsilat/protokol/portfoyler/' + this.form.muvekkil_id, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) this.portfoyler = await res.json();
            } catch (e) {
                // noop
            }

            if (portfoyKoru) {
                this.form.portfoy_id = seciliPortfoy;
            }

            this.portfoyYukleniyor = false;
        },

        taksitEkle() {
            this.form.taksitler.push({ taksit_tarihi: '', taksit_tutari: '' });
        },

        hacizciEkle() {
            this.form.hacizciler.push({ hacizci_id: '', haciz_turu: '', pay_orani: '' });
        },

        pdfSecildi(event) {
            const file = event.target.files[0];
            if (file && file.type === 'application/pdf') {
                this.form.pdf_dosya = file;
            } else {
                this.form.pdf_dosya = null;
                event.target.value = '';
            }
        },

        async kaydet() {
            this.hata = '';

            if (!this.duzenlemeModu) {
                if (!String(this.form.muhatap_adi ?? '').trim()) {
                    this.hata = 'Muhatap adı zorunludur.';
                    return;
                }

                if (!String(this.form.muhatap_telefon ?? '').trim()) {
                    this.hata = 'Muhatap telefonu zorunludur.';
                    return;
                }
            }

            if (this.form.muhatap_telefon && !/^[0-9+\s\-()]+$/.test(this.form.muhatap_telefon)) {
                this.hata = 'Muhatap telefonu geçersiz formatta.';
                return;
            }

            if (this.form.hacizciler.length === 0) {
                this.hata = 'En az bir hacizci eklemeniz gerekmektedir.';
                return;
            }

            const hacizciIdler = this.form.hacizciler.map((h) => h.hacizci_id).filter(Boolean);
            if ((new Set(hacizciIdler)).size !== hacizciIdler.length) {
                this.hata = 'Aynı hacizci bir protokole birden fazla kez eklenemez.';
                return;
            }

            for (const h of this.form.hacizciler) {
                if (!h.hacizci_id || !h.haciz_turu) {
                    this.hata = 'Tüm hacizciler için hacizci ve haciz türü seçilmelidir.';
                    return;
                }
            }

            if (this.manuelPayZorunlu()) {
                for (const h of this.form.hacizciler) {
                    const oran = this.parseOran(h.pay_orani);
                    if (h.pay_orani === '' || h.pay_orani === null || !Number.isFinite(oran)) {
                        this.hata = '3 hacizcili protokolde tüm manuel pay oranları zorunludur.';
                        return;
                    }

                    if (oran < 0 || oran > 100) {
                        this.hata = 'Pay oranları 0 ile 100 arasında olmalıdır.';
                        return;
                    }
                }

                const toplam = this.oranToplami();
                if (Math.abs(toplam - 100) > 0.01) {
                    this.hata = '3 hacizcili protokolde pay oranları toplamı 100 olmalıdır.';
                    return;
                }
            }

            if (!this.duzenlemeModu) {
                for (const t of this.form.taksitler) {
                    const taksitTutari = this.parseAmount(t.taksit_tutari);
                    if (!t.taksit_tarihi || !Number.isFinite(taksitTutari) || taksitTutari <= 0) {
                        this.hata = 'Tüm taksitler için tarih ve tutar girilmelidir.';
                        return;
                    }
                }
            }

            this.kaydediliyor = true;
            try {
                const parsedPesinat = this.toBackendAmount(this.form.pesinat);
                if ((this.form.pesinat ?? '') !== '' && parsedPesinat === null) {
                    this.hata = 'Peşinat alanı geçersiz. Lütfen tutarı düzgün formatta girin.';
                    this.kaydediliyor = false;
                    return;
                }

                const normalizedPesinat = parsedPesinat ?? '0.00';
                const normalizedToplamProtokolTutari = this.toBackendAmount(this.form.toplam_protokol_tutari);
                if (!normalizedToplamProtokolTutari || Number(normalizedToplamProtokolTutari) <= 0) {
                    this.hata = 'Toplam protokol tutarı zorunludur.';
                    this.kaydediliyor = false;
                    return;
                }

                const payload = {
                    muvekkil_id: this.form.muvekkil_id,
                    portfoy_id: this.form.portfoy_id || null,
                    protokol_tarihi: this.form.protokol_tarihi,
                    borclu_adi: this.form.borclu_adi,
                    borclu_tckn_vkn: this.form.borclu_tckn_vkn || null,
                    muhatap_adi: this.form.muhatap_adi || null,
                    muhatap_telefon: this.form.muhatap_telefon || null,
                    pesinat: normalizedPesinat,
                    toplam_protokol_tutari: normalizedToplamProtokolTutari,
                    hacizciler: this.form.hacizciler.map((h) => ({
                        hacizci_id: h.hacizci_id,
                        haciz_turu: h.haciz_turu,
                        pay_orani: this.manuelPayZorunlu() ? this.parseOran(h.pay_orani) : null,
                    })),
                };

                if (!this.duzenlemeModu) {
                    payload.taksitler = this.form.taksitler.map((t) => ({
                        ...t,
                        taksit_tutari: this.toBackendAmount(t.taksit_tutari),
                    }));
                }

                if (this.duzenlemeModu && this.yoneticiMi) {
                    payload.aktif = Boolean(this.form.aktif);
                }

                const url = this.duzenlemeModu
                    ? ('/tahsilat/protokol/' + this.duzenlenenProtokolId)
                    : '/tahsilat/protokol/store';

                const res = await fetch(url, {
                    method: this.duzenlemeModu ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    const protokolId = data.protokol?.id ?? this.duzenlenenProtokolId;

                    if (this.form.pdf_dosya && protokolId) {
                        await this.pdfYukle(protokolId);
                    }

                    this.kapat();
                    window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
                } else {
                    const ilkDogrulamaHatasi = data?.errors ? Object.values(data.errors).flat()[0] : null;
                    this.hata = data.error ?? ilkDogrulamaHatasi ?? (data.message ?? 'Bir hata oluştu.');
                }
            } catch (e) {
                this.hata = 'Sunucu hatası: ' + (e.message || 'Bilinmeyen hata');
            } finally {
                this.kaydediliyor = false;
            }
        },

        async pdfYukle(protokolId) {
            try {
                const formData = new FormData();
                formData.append('pdf', this.form.pdf_dosya);

                await fetch('/tahsilat/protokol/' + protokolId + '/pdf', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
            } catch (e) {
                console.error('PDF yükleme hatası:', e);
            }
        },

        manuelPayZorunlu() {
            return this.form.hacizciler.length === 3;
        },

        parseOran(value) {
            const parsed = parseFloat(value);
            return Number.isFinite(parsed) ? parsed : NaN;
        },

        normalizeHacizTuru(value) {
            if (!value || typeof value !== 'string') {
                return '';
            }

            const normalized = value
                .toLocaleLowerCase('tr-TR')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');

            if (normalized === 'istihkakli') return 'istihkakli';
            if (normalized === 'nami_mustear') return 'nami_mustear';
            if (normalized === '97') return '97';
            if (normalized === 'ihtiyati') return 'ihtiyati';
            return '';
        },

        oranToplami() {
            if (!this.manuelPayZorunlu()) {
                return 0;
            }

            return this.form.hacizciler.reduce((toplam, h) => {
                const oran = this.parseOran(h.pay_orani);
                return toplam + (Number.isFinite(oran) ? oran : 0);
            }, 0);
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

