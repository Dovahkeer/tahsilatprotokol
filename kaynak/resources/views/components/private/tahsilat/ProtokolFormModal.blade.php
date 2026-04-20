{{-- Yeni Protokol Form Modalı --}}
@php
    $yoneticiMi = auth()->check() && auth()->user()->isYonetici();
@endphp
<template x-teleport="body">
<div x-data="protokolFormModal()"
     x-show="acik"
     @protokol-form-ac.window="modalAc()"
     @protokol-form-duzenle.window="modalDuzenle($event)"
     class="fixed inset-0 z-50 overflow-y-auto"
     x-cloak>

    <div class="fixed inset-0 bg-slate-950/65 backdrop-blur-sm"></div>
    <div class="relative flex min-h-screen items-center justify-center p-4 sm:p-6">
    <div class="relative w-full max-w-3xl max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3rem)] overflow-y-auto rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl" @click.stop>
        <div class="sticky top-0 z-10 px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
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

                {{-- Ana Para --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Ana Para (TL)</label>
                    <input type="text"
                        x-model="form.ana_para"
                        @input="form.ana_para = formatAmountInput($event.target.value)"
                        inputmode="decimal"
                        placeholder="Örn: 1.000.000,00"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                </div>

                {{-- Kapak Hesabı --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Kapak Hesabı (TL)</label>
                    <input type="text"
                        x-model="form.kapak_hesabi"
                        @input="form.kapak_hesabi = formatAmountInput($event.target.value)"
                        inputmode="decimal"
                        placeholder="Örn: 1.200.000,00"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
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

            {{-- Hacizciler ve Akıllı Dağıtım --}}
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Hacizciler ve Pay Dağılımı <span class="text-red-500">*</span></label>
                    <button type="button" @click="hacizciEkle()"
                        class="text-xs text-amber-600 hover:text-amber-700 font-medium">+ Hacizci Ekle</button>
                </div>
                
                {{-- YENİ: Akıllı Dağıtım Bilgi Kutucuğu --}}
                <div x-show="form.hacizciler.length > 0" class="mb-3 px-3 py-2.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-300 shadow-sm">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <span class="font-bold">Akıllı Dağıtım Aktif:</span> Sistem seçtiğiniz haciz türleri (İstihkak, 97, Nam-ı Müstear vb.) ve personel kademelerine göre oranları <strong>otomatik</strong> hesaplar. 
                            <br><span class="opacity-80">Özel bir durum varsa hesaplanan oranlara tıklayıp manuel değiştirebilirsiniz. (Şu anki Toplam Pay: <span class="font-bold" x-text="oranToplami().toFixed(2) + '%'"></span>)</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <template x-for="(h, i) in form.hacizciler" :key="i">
                        <div class="flex items-center gap-2 mb-2">
                            <select x-model="h.hacizci_id" @change="paylariOtomatikDagit()" required
                                class="flex-1 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">
                                <option value="">- Hacizci Seçin -</option>
                                <template x-for="hc in hacizciler" :key="hc.id">
                                    <option :value="String(hc.id)" x-text="hc.ad_soyad + (hc.sicil_no ? ' (' + hc.sicil_no + ')' : '')"></option>
                                </template>
                            </select>

                            <select x-model="h.haciz_turu" @change="paylariOtomatikDagit()" required
                                class="w-36 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">
                                <option value="">- Haciz Türü -</option>
                                <option value="istihkakli">İstihkaklı</option>
                                <option value="nami_mustear">Namı Müstear</option>
                                <option value="97">97</option>
                                <option value="ihtiyati">İhtiyati</option>
                                <option value="sulhen">Sulhen</option>
                            </select>

                            {{-- YENİ: step="0.01" özelliği eklendi, küsurata izin veriliyor --}}
                            <input type="number" x-model="h.pay_orani"
                                required
                                min="0" step="0.01" placeholder="Pay %"
                                class="w-24 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">

                            <button type="button" @click="form.hacizciler.splice(i, 1); paylariOtomatikDagit()"
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

            {{-- Taksitler (Dinamik Çek/Senet Modüllü) --}}
            <div x-show="!duzenlemeModu" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Taksit / Çek / Senet Planı</label>
                    <button type="button" @click="taksitEkle()"
                        class="text-xs text-amber-600 hover:text-amber-700 font-medium">+ Yeni Ekle</button>
                </div>
                <div class="space-y-3">
                    <template x-for="(taksit, i) in form.taksitler" :key="i">
                        <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/30 transition-all">
                            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                                <span class="text-xs font-medium text-gray-500 w-14 flex-shrink-0" x-text="(i+1) + '. Sıra'"></span>

                                <select x-model="taksit.odeme_tipi"
                                    class="w-24 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs font-medium text-gray-700 dark:text-gray-200">
                                    <option value="taksit">Taksit</option>
                                    <option value="cek">Çek</option>
                                    <option value="senet">Senet</option>
                                </select>

                                <input type="date" x-model="taksit.taksit_tarihi" required title="Vade Tarihi"
                                    class="flex-1 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">

                                <input type="text"
                                    x-model="taksit.taksit_tutari"
                                    @input="taksit.taksit_tutari = formatAmountInput($event.target.value)"
                                    inputmode="decimal"
                                    placeholder="Tutar (TL)"
                                    required
                                    class="w-28 px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">

                                <button type="button" @click="form.taksitler.splice(i, 1)" title="Sil"
                                    class="text-red-400 hover:text-red-600 flex-shrink-0 ml-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- Çek/Senet Detayları (Sadece Çek veya Senet seçilirse animasyonla açılır) --}}
                            <div x-show="taksit.odeme_tipi === 'cek'"
                                x-collapse
                                class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-600 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div>
                                    <input type="text" x-model="taksit.banka_adi" placeholder="Banka Adı (Örn: Ziraat)"
                                        class="w-full px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">
                                </div>
                                <div>
                                    <input type="text" x-model="taksit.seri_no" placeholder="Evrak Seri No"
                                        class="w-full px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">
                                </div>
                                <div>
                                    <input type="text" x-model="taksit.kesideci" placeholder="Keşideci (Yazan)"
                                        class="w-full px-2 py-1.5 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs">
                                </div>
                            </div>
                        </div>
                    </template>
                    <p x-show="form.taksitler.length === 0" class="text-xs text-gray-400 text-center py-2">
                        Ödeme planı eklenmedi. Tamamı peşin ödeme olarak işlenecektir.
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
</div>
</template>

<script>
function protokolFormModal() {
    return {
        acik: false,
        duzenlemeModu: false,
        _ilkYukleme: false, // Düzenlemede oranların ezilmesini önler
        duzenlenenProtokolId: null,
        kaydediliyor: false,
        hata: '',
        yoneticiMi: @js((bool) $yoneticiMi),
        muvekkiller: [],
        portfoyler: [],
        hacizciler: [],
        kademePayOranlari: [], // Matris için eklendi
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
            ana_para: '',
            kapak_hesabi: '',
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
                this.hacizcilerYukle(),
                this.primAyarlariYukle() // Matrisi Çek
            ]);
        },

        async modalDuzenle(event) {
            const id = event?.detail?.id ?? event?.detail ?? null;
            if (!id) return;

            this.acik = true;
            this.duzenlemeModu = true;
            this._ilkYukleme = true; // DB'den gelen oranları koru
            this.duzenlenenProtokolId = id;
            this.resetForm();

            await Promise.all([
                this.muvekkillerYukle(),
                this.hacizcilerYukle(),
                this.primAyarlariYukle() // Matrisi Çek
            ]);

            await this.protokolDetayYukle(id);
            this._ilkYukleme = false; // Sonra serbest bırak
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
                ana_para: '',
                kapak_hesabi: '',
                taksitler: [],
                hacizciler: [],
                pdf_dosya: null,
            };
            this.portfoyler = [];
            this.hata = '';
        },

        // YENİ: Kademe Matrisini Arka Plandan Çek
        async primAyarlariYukle() {
            try {
                const res = await fetch('/tahsilat/yetki/prim-ayarlar', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) {
                    const data = await res.json();
                    this.kademePayOranlari = data.kademe_pay_oranlari ?? [];
                }
            } catch (e) {
                // sessizce geç
            }
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

                const seciliHacizciler = Array.isArray(protokol.hacizciler) ? protokol.hacizciler : [];
                for (const h of seciliHacizciler) {
                    if (!h?.id) {
                        continue;
                    }

                    const varMi = this.hacizciler.some((hc) => String(hc.id) === String(h.id));
                    if (!varMi) {
                        this.hacizciler.push({
                            id: String(h.id),
                            ad_soyad: h.ad_soyad ?? ('Hacizci ' + String(h.id).slice(0, 8)),
                            sicil_no: h.sicil_no ?? null,
                            kademe: h.kademe ?? null,
                        });
                    }
                }

                const doldurulacakHacizciler = Array.isArray(protokol.hacizciler)
                    ? protokol.hacizciler.map((h) => ({
                        hacizci_id: '', 
                        _gercek_id: String(h.id), 
                        haciz_turu: this.normalizeHacizTuru(h?.pivot?.haciz_turu ?? ''),
                        pay_orani: h?.pivot?.pay_orani ?? '',
                    }))
                    : [];

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
                    ana_para: this.formatAmountFromNumber(protokol.ana_para ?? ''),
                    kapak_hesabi: this.formatAmountFromNumber(protokol.kapak_hesabi ?? ''),
                    taksitler: Array.isArray(protokol.taksitler)
                        ? protokol.taksitler.map((t) => ({
                            id: t.id,
                            taksit_tarihi: (t.taksit_tarihi ?? '').slice(0, 10),
                            taksit_tutari: this.formatAmountFromNumber(t.taksit_tutari ?? ''),
                            odendi: !!t.odendi,
                        }))
                        : [],
                    hacizciler: doldurulacakHacizciler,
                    pdf_dosya: null,
                };

                this.$nextTick(() => {
                    this.form.hacizciler.forEach(h => {
                        h.hacizci_id = h._gercek_id;
                    });
                });

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
            this.form.taksitler.push({ 
                odeme_tipi: 'taksit', // Varsayılan olarak taksit seçili gelsin
                taksit_tarihi: '', 
                taksit_tutari: '',
                banka_adi: '',        // Yeni alan
                seri_no: '',          // Yeni alan
                kesideci: ''          // Yeni alan
            });
        },

        hacizciEkle() {
            this.form.hacizciler.push({ hacizci_id: '', haciz_turu: '', pay_orani: '' });
        },

        pdfSecildi(event) {
            const file = event.target.files[0];
            if (file) {
                // 10 MB Kontrolü (10 * 1024 * 1024 bytes)
                if (file.size > 10485760) {
                    alert('HATA: Seçtiğiniz dosya çok büyük (' + (file.size / 1024 / 1024).toFixed(2) + ' MB).\n\nLütfen en fazla 10 MB boyutunda bir PDF yükleyin.');
                    this.form.pdf_dosya = null;
                    event.target.value = '';
                    return;
                }

                if (file.type === 'application/pdf') {
                    this.form.pdf_dosya = file;
                } else {
                    alert('HATA: Lütfen sadece PDF formatında bir dosya seçin.');
                    this.form.pdf_dosya = null;
                    event.target.value = '';
                }
            } else {
                this.form.pdf_dosya = null;
            }
        },

        // ==========================================
        // YENİ: AKILLI PAY DAĞITIM MOTORU (RULE ENGINE)
        // ==========================================
        paylariOtomatikDagit() {
            // Düzenleme modunda ilk açılışta DB'deki veriyi ezmesini engelle
            if (this.duzenlemeModu && this._ilkYukleme) return;

            const seciliTurler = this.form.hacizciler.map(h => h.haciz_turu).filter(Boolean);
            
            // İhtiyati'yi senaryo analizi için İstihkaklı gibi sayıyoruz
            const normalizeTurler = seciliTurler.map(t => t === 'ihtiyati' ? 'istihkakli' : t);
            const benzersizTurler = [...new Set(normalizeTurler)];

            // 1. HAVUZLARI (Senaryo Dağılımlarını) BELİRLE
            let havuz = {};
            let ozelDurum = false; // 3 ve daha fazla benzersiz veya bilinmeyen kombinasyon

            if (benzersizTurler.length === 1) {
                const tur = benzersizTurler[0];
                havuz[tur] = 100;
            } 
            else if (benzersizTurler.includes('istihkakli') && benzersizTurler.includes('97') && benzersizTurler.length === 2) {
                havuz['istihkakli'] = 50;
                havuz['97'] = 50;
            } 
            else if (benzersizTurler.includes('istihkakli') && benzersizTurler.includes('nami_mustear')) {
                havuz['istihkakli'] = 32.5;
                havuz['nami_mustear'] = 32.5;
                if (benzersizTurler.includes('97')) havuz['97'] = 0; // 97 Nam-ı Müstear senaryosunda 0 çeker
            } 
            else if (benzersizTurler.includes('istihkakli') && benzersizTurler.includes('sulhen') && benzersizTurler.length === 2) {
                havuz['istihkakli'] = 50;
                havuz['sulhen'] = 50;
            } 
            else {
                ozelDurum = true; // Sistemin tanımadığı karmaşık kombinasyon -> Manuel müdahaleye bırak
            }

            // 2. KİŞİLERİ GRUPLA
            const gruplar = {};
            this.form.hacizciler.forEach(h => {
                if (!h.hacizci_id || !h.haciz_turu) return;
                // Gerçek türü grupla (ihtiyati ise onu tut)
                const analizTuru = h.haciz_turu === 'ihtiyati' ? 'istihkakli' : h.haciz_turu;
                if (!gruplar[analizTuru]) gruplar[analizTuru] = [];
                gruplar[analizTuru].push(h);
            });

            // 3. DAĞITIMI YAP (Kademe Matrisine Göre)
            for (const tur in gruplar) {
                const kisiler = gruplar[tur];
                const ayrilanPay = havuz[tur] !== undefined ? havuz[tur] : null;

                // Havuz yoksa, karmaşık senaryoysa veya bir türde 3'ten fazla kişi varsa DOKUNMA
                if (ayrilanPay === null || ozelDurum || kisiler.length >= 3) {
                    continue; 
                }

                if (kisiler.length === 1) {
                    kisiler[0].pay_orani = ayrilanPay.toString(); // Tek kişiyse havuzun tümünü alır
                } 
                else if (kisiler.length === 2) {
                    const k1 = kisiler[0];
                    const k2 = kisiler[1];

                    const h1_data = this.hacizciler.find(x => String(x.id) === String(k1.hacizci_id));
                    const h2_data = this.hacizciler.find(x => String(x.id) === String(k2.hacizci_id));

                    const kademe1 = h1_data?.kademe;
                    const kademe2 = h2_data?.kademe;

                    if (kademe1 && kademe2) {
                        if (kademe1 === kademe2) {
                            // Aynı kademe -> %50 %50
                            k1.pay_orani = (ayrilanPay / 2).toFixed(2);
                            k2.pay_orani = (ayrilanPay / 2).toFixed(2);
                        } else {
                            // Kademe Farklı -> Matristen Çek
                            const kural1 = this.kademePayOranlari.find(p => p.ust_kademe === kademe1 && p.alt_kademe === kademe2);
                            const kural2 = this.kademePayOranlari.find(p => p.ust_kademe === kademe2 && p.alt_kademe === kademe1);

                            if (kural1) {
                                k1.pay_orani = ((ayrilanPay * Number(kural1.ust_kademe_orani)) / 100).toFixed(2);
                                k2.pay_orani = ((ayrilanPay * Number(kural1.alt_kademe_orani)) / 100).toFixed(2);
                            } else if (kural2) {
                                k2.pay_orani = ((ayrilanPay * Number(kural2.ust_kademe_orani)) / 100).toFixed(2);
                                k1.pay_orani = ((ayrilanPay * Number(kural2.alt_kademe_orani)) / 100).toFixed(2);
                            } else {
                                // Matris kuralı unutulmuşsa mecburen %50-%50
                                k1.pay_orani = (ayrilanPay / 2).toFixed(2);
                                k2.pay_orani = (ayrilanPay / 2).toFixed(2);
                            }
                        }
                    } else {
                         // Kademesi belli değilse
                         k1.pay_orani = (ayrilanPay / 2).toFixed(2);
                         k2.pay_orani = (ayrilanPay / 2).toFixed(2);
                    }
                }
            }

            // Görüntü kirliliğini önle (.00 sil)
            this.form.hacizciler.forEach(h => {
                 if(h.pay_orani && h.pay_orani.endsWith('.00')) {
                     h.pay_orani = Number(h.pay_orani).toString();
                 }
            });
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
                // YENİ: Toplam oran limitini kaldırdık ancak her birinin oranının girildiğinden emin oluyoruz
                const oran = this.parseOran(h.pay_orani);
                if (h.pay_orani === '' || h.pay_orani === null || !Number.isFinite(oran)) {
                    this.hata = 'Tüm hacizciler için pay oranı girilmelidir.';
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
                    ana_para: this.form.ana_para ? this.toBackendAmount(this.form.ana_para) : null,
                    kapak_hesabi: this.form.kapak_hesabi ? this.toBackendAmount(this.form.kapak_hesabi) : null,
                    hacizciler: this.form.hacizciler.map((h) => ({
                        hacizci_id: h.hacizci_id,
                        haciz_turu: h.haciz_turu,
                        pay_orani: this.parseOran(h.pay_orani),
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
                        try {
                            await this.pdfYukle(protokolId);
                        } catch (pdfError) {
                            alert('DİKKAT: Protokol başarıyla oluşturuldu ancak PDF DOSYASI YÜKLENEMEDİ!\n\nSebep: ' + pdfError.message + '\n\nLütfen protokole tıklayıp "Düzenle" diyerek PDF dosyasını tekrar yüklemeyi deneyin.');
                        }
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
            const formData = new FormData();
            formData.append('pdf', this.form.pdf_dosya);

            const res = await fetch('/tahsilat/protokol/' + protokolId + '/pdf', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                const hataMesaji = data?.errors?.pdf?.[0] || data?.message || 'Dosya çok büyük olduğu için sunucu tarafından reddedildi.';
                throw new Error(hataMesaji);
            }
        },
        
        parseOran(value) {
            const parsed = parseFloat(value);
            return Number.isFinite(parsed) ? parsed : NaN;
        },

        normalizeHacizTuru(value) {
            if (!value || typeof value !== 'string') return '';

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
            if (normalized === 'sulhen') return 'sulhen';
            return '';
        },

        oranToplami() {
            return this.form.hacizciler.reduce((toplam, h) => {
                const oran = this.parseOran(h.pay_orani);
                return toplam + (Number.isFinite(oran) ? oran : 0);
            }, 0);
        },

        formatAmountInput(rawValue) {
            const cleaned = String(rawValue ?? '').replace(/\s+/g, '').replace(/[^\d,]/g, '');
            if (cleaned === '') return '';

            const commaIndex = cleaned.indexOf(',');
            let whole = commaIndex >= 0 ? cleaned.slice(0, commaIndex) : cleaned;
            let decimal = commaIndex >= 0 ? cleaned.slice(commaIndex + 1).replace(/,/g, '') : '';

            whole = whole.replace(/^0+(?=\d)/, '');
            if (whole === '') whole = '0';
            
            const groupedWhole = this.groupThousands(whole);

            if (commaIndex < 0) return groupedWhole;

            decimal = decimal.slice(0, 2);
            return `${groupedWhole},${decimal}`;
        },

        formatAmountFromNumber(value) {
            if (value === null || value === undefined || value === '') return '';

            const amount = Number(value);
            if (!Number.isFinite(amount)) return '';

            const fixed = amount.toFixed(2);
            const [whole, decimal] = fixed.split('.');
            const groupedWhole = this.groupThousands(whole);

            if (decimal === '00') return groupedWhole;

            return `${groupedWhole},${decimal}`;
        },

        groupThousands(value) {
            const digits = String(value ?? '').replace(/\D/g, '');
            if (digits === '') return '';
            return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        parseAmount(value) {
            const raw = String(value ?? '').trim();
            if (raw === '') return NaN;

            const normalized = raw
                .replace(/\./g, '')
                .replace(/\s+/g, '')
                .replace(',', '.')
                .replace(/[^0-9.]/g, '');

            if (normalized === '' || normalized === '.') return NaN;

            const parsed = Number(normalized);
            return Number.isFinite(parsed) ? parsed : NaN;
        },

        toBackendAmount(value) {
            const parsed = this.parseAmount(value);
            if (!Number.isFinite(parsed)) return null;
            return parsed.toFixed(2);
        },
    };
}
</script>