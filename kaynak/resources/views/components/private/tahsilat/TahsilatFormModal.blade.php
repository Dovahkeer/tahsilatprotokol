<script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>
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

                        {{-- YENİ EKLENEN POS SEÇİM ALANI (Sadece Mail Order seçilirse açılır) --}}
                        <div x-show="['vekil_hesabina_mail_order', 'vekalet_ucreti_mail_order'].includes(form.tahsilat_yontemi)" 
                            x-collapse class="mt-3 border-t border-gray-100 dark:border-gray-700 pt-3">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-1">
                                POS Cihazı <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select x-model="form.pos_cihazi"
                                    class="w-full h-9 pl-3 pr-9 rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 text-sm text-amber-900 dark:text-amber-100 appearance-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500">
                                    <option value="">-- POS Cihazı Seçin --</option>
                                    <option value="ParamPOS">ParamPOS</option>
                                    <option value="DenizBank POS">DenizBank POS</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2.5 w-full md:max-w-sm md:justify-self-end">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            <span x-text="duzenlemeModu ? 'Ek Dekontlar (Opsiyonel)' : 'Dekontlar (PDF/JPG/PNG)'"></span>
                            {{-- YENİ: Elden Alındıysa Kırmızı Yıldız Gizlenir --}}
                            <span x-show="!['elden_alindi', 'vekalet_ucreti_elden_alindi'].includes(form.tahsilat_yontemi) && !duzenlemeModu" class="text-red-500">*</span>
                        </label>

                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-2.5 w-full md:max-w-sm md:justify-self-end">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                            <span x-text="duzenlemeModu ? 'Ek Dekontlar (Opsiyonel)' : 'Dekontlar (PDF/JPG/PNG) *'"></span>
                        </label>
                        
                        {{-- Sürükle Bırak Alanı --}}
                        <div class="relative flex flex-col items-center justify-center w-full h-24 px-4 mt-2 transition-colors border-2 border-dashed rounded-xl"
                             :class="surukleniyor ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700/50'"
                             @dragover.prevent="surukleniyor = true"
                             @dragleave.prevent="surukleniyor = false"
                             @drop.prevent="dosyalariAl($event)">
                             
                            <input type="file" multiple accept=".pdf,.png,.jpg,.jpeg,.webp,.bmp,.gif,.tif,.tiff" class="absolute inset-0 z-50 w-full h-full opacity-0 cursor-pointer" @change="dosyalariAl($event)">
                            
                            <div class="flex flex-col items-center justify-center text-center pointer-events-none">
                                <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="font-bold text-amber-600">Dosyaları seçin</span> veya sürükleyin
                                </p>
                            </div>
                        </div>

                        {{-- Dosya Listesi --}}
                        <div x-show="dosyalar.length > 0" class="mt-2 space-y-1.5 max-h-32 overflow-y-auto pr-1 custom-scrollbar">
                            <template x-for="(dosya, index) in dosyalar" :key="index">
                                <div class="flex items-center justify-between p-2 text-xs bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
                                    <div class="flex items-center gap-2 truncate">
                                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <span class="truncate text-emerald-800 dark:text-emerald-300 font-medium" x-text="dosya.name"></span>
                                    </div>
                                    <button type="button" @click="dosyalar.splice(index, 1)" class="shrink-0 text-emerald-700 hover:text-red-600 ml-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
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
                    <span x-show="kaydediliyor" x-text="kaydediliyorDurum"></span>
                </button>
            </div>

        </form>
        </div>
    </div>
    </div>
    {{-- BAŞARI VE WHATSAPP MODALI --}}
        <div x-show="basariModaliAcik" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700 overflow-hidden" @click.stop>
                <div class="bg-emerald-500 p-5 text-center relative">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-2 shadow-lg">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white">Tahsilat Kaydedildi!</h3>
                </div>

                <div class="p-5 space-y-4" x-show="basariData">
                    <a x-show="basariData?.download_url" :href="basariData?.download_url" download class="flex items-center justify-center gap-2 w-full py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl font-bold transition-colors border border-gray-300 dark:border-gray-600">
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                        Birleştirilmiş Dekontu İndir
                    </a>

                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-gray-700 dark:text-gray-300">WhatsApp Şablonu</label>
                            <button type="button" @click="whatsappKopyala()" class="text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span x-text="kopyalandi ? 'Kopyalandı!' : 'Kopyala'"></span>
                            </button>
                        </div>
                        <textarea readonly rows="6" class="w-full p-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-800 dark:text-gray-200 resize-none outline-none focus:ring-2 focus:ring-amber-500 font-mono" x-text="`*YENİ TAHSİLAT GİRİŞİ*
                        *Borçlu:* ${basariData?.borclu}
                        *TCKN/VKN:* ${basariData?.tckn ?? '-'}
                        *Müvekkil:* ${basariData?.muvekkil}
                        *Portföy:* ${basariData?.portfoy}
                        *Taksit/Durum:* ${basariData?.taksit}
                        *Tutar:* ${basariData?.tutar}
                        Makbuz sisteme yüklendi. İyi çalışmalar.`"></textarea>
                    </div>
                </div>

                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button type="button" @click="basariModaliAcik = false; kapat()" class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-bold rounded-lg transition-colors">Kapat</button>
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
        maxDekontBytes: 15 * 1024 * 1024, // 15 MB
        izinliDekontUzantilari: ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'bmp', 'gif', 'tif', 'tiff'],
        aramaYukleniyor: false,
        aramaCalisti: false,
        aramaZamanlayici: null,
        secilenProtokolMuvekkili: null,
        protokoller: [],
        eslesenProtokoller: [],
        muvekkiller: [],
        
        // Yeni Sistem Değişkenleri
        surukleniyor: false,
        dosyalar: [],
        basariModaliAcik: false,
        basariData: null,
        kopyalandi: false,
        kaydediliyorDurum: 'Kaydediliyor...',

        tahsilatYontemSecenekleri: [
            { value: 'muvekkil_hesabina_eft_havale', label: 'Müvekkil Hesabına EFT/Havale' },
            { value: 'muvekkil_hesabina_reddiyat', label: 'Müvekkil Hesabına Reddiyat' },
            { value: 'muvekkil_hesabina_mail_order', label: 'Müvekkil Hesabına Mail Order' },
            { value: 'vekil_hesabina_eft_havale', label: 'Vekil Hesabına EFT/Havale' },
            { value: 'vekil_hesabina_reddiyat', label: 'Vekil Hesabına Reddiyat' },
            { value: 'vekil_hesabina_mail_order', label: 'Vekil Hesabına Mail Order' },
            { value: 'elden_alindi', label: 'Elden Alındı' },
            { value: 'vekalet_ucreti_vekil_hesabina_eft_havale', label: 'Vekalet Ücreti Vekil Hesabına EFT/Havale' },
            { value: 'vekalet_ucreti_reddiyat', label: 'Vekalet Ücreti Reddiyat' },
            { value: 'vekalet_ucreti_mail_order', label: 'Vekalet Ücreti Mail Order' },
            { value: 'vekalet_ucreti_elden_alindi', label: 'Vekalet Ücreti Elden Alındı' },
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
            { value: 'maas_haczi', label: 'Maaş Haczi' },
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
            pos_cihazi: '', // YENİ EKLENDİ
            tahsilat_birimleri: [],
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
                    pos_cihazi: tahsilat.pos_cihazi ?? '', // YENİ
                    tahsilat_birimleri: Array.isArray(tahsilat.tahsilat_birimleri) ? tahsilat.tahsilat_birimleri : [],
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

                // DEKONT ZORUNLULUĞU KONTROLÜ
                const isEldenAlindi = ['elden_alindi', 'vekalet_ucreti_elden_alindi'].includes(this.form.tahsilat_yontemi);
                if (!isEldenAlindi && this.dosyalar.length === 0) {
                    this.hata = 'Lütfen en az bir dekont (PDF/JPG/PNG) ekleyin.';
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

            // POS CİHAZI ZORUNLULUĞU KONTROLÜ
            const isMailOrder = ['vekil_hesabina_mail_order', 'vekalet_ucreti_mail_order'].includes(this.form.tahsilat_yontemi);
            if (isMailOrder && !this.form.pos_cihazi) {
                this.hata = 'Mail Order işlemleri için POS cihazı seçimi zorunludur.';
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
            let finalDekont = null;
            if (this.dosyalar.length > 0) {
                this.kaydediliyorDurum = 'Dekontlar birleştiriliyor (PDF)...';
                finalDekont = await this.dekontlariBirlestir();
            }

            this.kaydediliyorDurum = 'Sunucuya gönderiliyor...';
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
            formData.append('pos_cihazi', this.form.pos_cihazi ?? ''); // YENİ
            formData.append('notlar', this.form.notlar ?? '');

            if (finalDekont) {
                formData.append('dekont', finalDekont); // Sadece dekont varsa gönder
            }

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
                const t = data.tahsilat;
                const seciliKalem = this.protokolOdemeKalemleri().find(k => k.value === this.form.odeme_kalemi);
                const taksitEtiketi = seciliKalem ? seciliKalem.label.split(' - ')[0] : 'Serbest / Peşinat';

                this.basariData = {
                    // DEKONT YOKSA (Elden Alındı vb.) İndir Butonunu Gizle veya Linki Boşalt
                    download_url: (t.dekontlar && t.dekontlar.length > 0) ? `/tahsilat/dekont/${t.dekontlar[0].id}/view` : null,
                    borclu: t.borclu_adi,
                    tckn: t.borclu_tckn_vkn,
                    muvekkil: t.muvekkil ? t.muvekkil.ad : '-',
                    portfoy: t.portfoy ? t.portfoy.ad : '-',
                    tutar: this.formatPara(t.tutar),
                    taksit: taksitEtiketi,
                    kanal: t.tahsilat_yontemi,
                    kullanici: 'Kullanıcı',
                    tarih: t.tahsilat_tarihi
                };

                this.basariModaliAcik = true;
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
                pos_cihazi: this.form.pos_cihazi ?? '', // YENİ
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

            // Düzenleme modunda yeni dosya yüklendiyse, onları da birleştirip gönderelim
            if (this.dosyalar.length > 0) {
                await this.dekontYukle(this.duzenlenenTahsilatId);
            }

            this.kapat();
            window.dispatchEvent(new CustomEvent('tahsilat-listesi-yenile'));
            window.dispatchEvent(new CustomEvent('protokol-listesi-yenile'));
        },

        async dekontYukle(tahsilatId) {
            this.kaydediliyorDurum = 'Yeni dekontlar işleniyor...';
            const finalDekont = await this.dekontlariBirlestir();
            
            const formData = new FormData();
            formData.append('dekont', finalDekont);

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
            } catch (e) {}

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
                pos_cihazi: '', // YENİ
                tahsilat_birimleri: [],
                notlar: '',
            };
            this.eslesenProtokoller = [];
            this.secilenProtokolMuvekkili = null;
            this.hata = '';
            this.aramaYukleniyor = false;
            this.aramaCalisti = false;
            
            // Temizlik işlemleri burada yapılıyor
            this.dosyalar = []; 
            this.basariModaliAcik = false;
        },

        formatPara(deger) {
            const sayi = Number(deger ?? 0);
            return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(sayi);
        },

        formatDosyaBoyutu(byteDegeri) {
            const bytes = Number(byteDegeri ?? 0);
            if (!Number.isFinite(bytes) || bytes <= 0) return '0 KB';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
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

        dosyalariAl(event) {
            this.surukleniyor = false;
            const files = event.dataTransfer ? event.dataTransfer.files : event.target.files;
            
            for (let i = 0; i < files.length; i++) {
                const dosya = files[i];
                const uzanti = (dosya.name.split('.').pop() || '').toLowerCase();
                
                if (this.izinliDekontUzantilari.includes(uzanti)) {
                    this.dosyalar.push(dosya);
                } else {
                    alert(`${dosya.name} desteklenmeyen bir format.`);
                }
            }
        },

        async dekontlariBirlestir(tahsilatData = null) {
            this.kaydediliyorDurum = 'Dekontlar birleştiriliyor (PDF)...';
            const { PDFDocument } = window.PDFLib;
            const mergedPdf = await PDFDocument.create();

            for (const dosya of this.dosyalar) {
                const tip = dosya.type;
                const uzanti = (dosya.name.split('.').pop() || '').toLowerCase();
                const arrayBuffer = await dosya.arrayBuffer();

                if (tip === 'application/pdf') {
                    const pdf = await PDFDocument.load(arrayBuffer);
                    const copiedPages = await mergedPdf.copyPages(pdf, pdf.getPageIndices());
                    copiedPages.forEach((page) => mergedPdf.addPage(page));
                } else if (tip.startsWith('image/') || ['tif', 'tiff', 'bmp'].includes(uzanti)) {
                    
                    try {
                        const pngBuffer = await new Promise((resolve, reject) => {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                const img = new Image();
                                img.onload = () => {
                                    const canvas = document.createElement('canvas');
                                    canvas.width = img.width; canvas.height = img.height;
                                    const ctx = canvas.getContext('2d');
                                    ctx.drawImage(img, 0, 0);
                                    canvas.toBlob((blob) => {
                                        if (blob) {
                                            blob.arrayBuffer().then(resolve);
                                        } else {
                                            reject(new Error("Canvas dönüşümü başarısız."));
                                        }
                                    }, 'image/png');
                                };
                                img.onerror = () => {
                                    reject(new Error(`Tarayıcınız "${dosya.name}" (TIFF/Özel Format) dosyasını işleyemiyor. Lütfen bu dosyayı JPG, PNG veya PDF olarak yükleyin.`));
                                };
                                img.src = e.target.result;
                            };
                            reader.onerror = () => reject(new Error("Dosya okunamadı."));
                            reader.readAsDataURL(dosya);
                        });

                        const image = await mergedPdf.embedPng(pngBuffer);
                        const page = mergedPdf.addPage([595.28, 841.89]); // A4 Boyutu
                        const dims = image.scaleToFit(550, 800); 
                        page.drawImage(image, {
                            x: (595.28 - dims.width) / 2,
                            y: (841.89 - dims.height) / 2,
                            width: dims.width, height: dims.height,
                        });
                    } catch (error) {
                        alert(error.message);
                        throw error; // Kaydetme işlemini durdur
                    }
                }
            }

            const pdfBytes = await mergedPdf.save();
            
            let dosyaIsmi = "Birlestirilmis_Dekont.pdf";
            if (tahsilatData) {
                const taksitNo = tahsilatData.tahsilat_turu?.odeme_kalemi?.taksit_id || 'Serbest';
                dosyaIsmi = `${tahsilatData.id}_${tahsilatData.borclu_adi}_${tahsilatData.borclu_tckn_vkn}_${taksitNo}.pdf`.replace(/\s+/g, '-');
            }

            return new File([pdfBytes], dosyaIsmi, { type: 'application/pdf' });
        },

        whatsappKopyala() {
            if (!this.basariData) return;
            
            const text = `*YENİ TAHSİLAT GİRİŞİ*
        *Borçlu:* ${this.basariData.borclu}
        *TCKN/VKN:* ${this.basariData.tckn ?? '-'}
        *Müvekkil:* ${this.basariData.muvekkil}
        *Portföy:* ${this.basariData.portfoy}
        *Taksit/Durum:* ${this.basariData.taksitBilgisi ?? '-'}
        *Tutar:* ${this.formatPara(this.basariData.tutar)}
        *Tahsilat Kanalı:* ${this.basariData.kanal}
        *Giriş Yapan:* ${this.basariData.kullanici}
        *Tarih:* ${this.basariData.tarih}`.replace(/^[ \t]+/gm, ''); // Baştaki boşlukları temizler

            // HTTPS zorunluluğunu aşan garantili kopyalama yöntemi
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed"; 
            textArea.style.left = "-999999px"; 
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                alert('Mesaj kopyalandı! WhatsApp grubuna yapıştırabilirsiniz.');
            } catch (err) {
                alert('Kopyalama başarısız oldu. Lütfen manuel kopyalayın.');
            } finally {
                textArea.remove();
            }
        },
    };
}
</script>


