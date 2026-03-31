{{-- Yetki ve Prim Ayarları Modalı (Sadece Yönetici) --}}
<div x-data="yetkiYonetimiModal()"
     x-show="acik"
     @tahsilat-yetki-modal-ac.window="acik = true; yukle()"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60"
     x-cloak>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-6xl max-h-[90vh] flex flex-col" @click.stop>
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tahsilat Yetki ve Prim Yönetimi</h3>
            <button @click="acik = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <div x-show="yukleniyor" class="flex justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            <div x-show="!yukleniyor" class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex flex-wrap gap-2">
                        <button type="button" @click="aktifSekme='kullanicilar'"
                            :class="aktifSekme === 'kullanicilar' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Kullanıcı Yetkileri
                        </button>
                        <button type="button" @click="aktifSekme='hacizci-kademe'"
                            :class="aktifSekme === 'hacizci-kademe' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Hacizci Kademeleri
                        </button>
                        <button type="button" @click="aktifSekme='kademe-pay'"
                            :class="aktifSekme === 'kademe-pay' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Kademe Arası Pay
                        </button>
                        <button type="button" @click="aktifSekme='kademe-asama'"
                            :class="aktifSekme === 'kademe-asama' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Kademe Prim Eşikleri
                        </button>
                        <button type="button" @click="aktifSekme='muvekkil'"
                            :class="aktifSekme === 'muvekkil' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Müvekkil Bazlı Prim
                        </button>
                        <button type="button" @click="aktifSekme='audit'"
                            :class="aktifSekme === 'audit' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium transition-colors">
                            Audit Geçmişi
                        </button>
                    </nav>
                </div>

                <div x-show="aktifSekme === 'kullanicilar'" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Kullanıcı</th>
                                <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400 text-xs">Tahsilat<br>Oluşturabilir</th>
                                <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400 text-xs">Protokol<br>Oluşturabilir</th>
                                <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400 text-xs">Protokol<br>Düzenleyebilir</th>
                                <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400 text-xs">Toplu Protokol<br>Ekleyebilir</th>
                                <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400 text-xs">Tahsilat Takip<br>Sorumlusu</th>
                                <template x-for="tab in tabTanimlari" :key="'head-' + tab.key">
                                    <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400 text-xs" x-text="tab.label"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="kul in kullanicilar" :key="kul.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="py-3">
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="kul.ad"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="kul.email"></div>
                                    </td>
                                    <td class="py-3 text-center">
                                        <input type="checkbox" :checked="kul.tahsilat_olusturabilir"
                                            @change="yetkiGuncelle(kul, 'tahsilat_olusturabilir', $event.target.checked)"
                                            class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                    </td>
                                    <td class="py-3 text-center">
                                        <input type="checkbox" :checked="kul.protokol_olusturabilir"
                                            @change="yetkiGuncelle(kul, 'protokol_olusturabilir', $event.target.checked)"
                                            class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                    </td>
                                    <td class="py-3 text-center">
                                        <input type="checkbox" :checked="kul.protokol_duzenleyebilir"
                                            @change="yetkiGuncelle(kul, 'protokol_duzenleyebilir', $event.target.checked)"
                                            class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                    </td>
                                    <td class="py-3 text-center">
                                        <input type="checkbox" :checked="kul.toplu_protokol_ekleyebilir"
                                            :disabled="!kul.yonetici"
                                            @change="yetkiGuncelle(kul, 'toplu_protokol_ekleyebilir', $event.target.checked)"
                                            class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                    </td>
                                    <td class="py-3 text-center">
                                        <input type="checkbox" :checked="kul.tahsilat_takip_sorumlusu"
                                            @change="yetkiGuncelle(kul, 'tahsilat_takip_sorumlusu', $event.target.checked)"
                                            class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                    </td>
                                    <template x-for="tab in tabTanimlari" :key="kul.id + '-' + tab.key">
                                        <td class="py-3 text-center">
                                            <input type="checkbox"
                                                :checked="tabYetkisiVarMi(kul, tab.key)"
                                                :disabled="kul.yonetici"
                                                @change="tabYetkiGuncelle(kul, tab.key, $event.target.checked)"
                                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="aktifSekme === 'hacizci-kademe'" class="space-y-3">
                    <div class="flex justify-end">
                        <button @click="kaydetHacizciKademeleri()"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                            :disabled="kaydediliyor">
                            Kaydet
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Hacizci</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Sicil No</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Kademe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="kayit in hacizciKademeleri" :key="kayit.hacizci_id">
                                    <tr>
                                        <td class="py-3 text-gray-900 dark:text-white" x-text="kayit.ad_soyad"></td>
                                        <td class="py-3 text-gray-500 dark:text-gray-400" x-text="kayit.sicil_no || '-'"></td>
                                        <td class="py-3">
                                            <select x-model="kayit.kademe"
                                                class="w-48 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                                <template x-for="kademe in kademeler" :key="kademe.kademe">
                                                    <option :value="kademe.kademe" x-text="kademe.kademe_adi + ' (' + kademe.kademe + ')'"></option>
                                                </template>
                                            </select>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="aktifSekme === 'kademe-pay'" class="space-y-3">
                    <div class="flex justify-end">
                        <button @click="kaydetKademePayOranlari()"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                            :disabled="kaydediliyor">
                            Kaydet
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Üst Kademe</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Alt Kademe</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Üst Pay (%)</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Alt Pay (%)</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Toplam</th>
                                    <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400">Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="pay in kademePayOranlari" :key="pay.ust_kademe + '_' + pay.alt_kademe">
                                    <tr>
                                        <td class="py-3 text-gray-900 dark:text-white" x-text="kademeEtiketi(pay.ust_kademe)"></td>
                                        <td class="py-3 text-gray-900 dark:text-white" x-text="kademeEtiketi(pay.alt_kademe)"></td>
                                        <td class="py-3">
                                            <input type="number" min="0" max="100" step="0.01" x-model="pay.ust_kademe_orani"
                                                class="w-32 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-3">
                                            <input type="number" min="0" max="100" step="0.01" x-model="pay.alt_kademe_orani"
                                                class="w-32 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-3">
                                            <span :class="Math.abs(oranToplami(pay) - 100) < 0.01 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                                class="font-medium" x-text="oranToplami(pay).toFixed(2)"></span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <input type="checkbox" x-model="pay.aktif"
                                                class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="aktifSekme === 'kademe-asama'" class="space-y-3">
                    <div class="flex justify-between items-center gap-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Her kademe için 3 aşama zorunludur. Eşik tutarı aylık hacizci toplam tahsilat payını esas alır.
                        </p>
                        <button @click="kaydetKademePrimAsamalari()"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                            :disabled="kaydediliyor">
                            Kaydet
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Kademe</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Aşama</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Eşik Tutarı</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Prim Oranı (%)</th>
                                    <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400">Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="asama in kademePrimAsamalari" :key="asama.kademe + '_' + asama.asama_no">
                                    <tr>
                                        <td class="py-3 text-gray-900 dark:text-white" x-text="kademeEtiketi(asama.kademe)"></td>
                                        <td class="py-3 text-gray-700 dark:text-gray-300" x-text="'Asama ' + asama.asama_no"></td>
                                        <td class="py-3">
                                            <input type="number" min="0" step="0.01" x-model="asama.esik_tutari"
                                                class="w-40 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-3">
                                            <input type="number" min="0" max="100" step="0.01" x-model="asama.prim_orani"
                                                class="w-32 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-3 text-center">
                                            <input type="checkbox" x-model="asama.aktif"
                                                class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div x-show="aktifSekme === 'muvekkil'" class="space-y-3">
                    <div class="flex justify-end">
                        <button @click="kaydetMuvekkilOranlari()"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
                            :disabled="kaydediliyor">
                            Kaydet
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Müvekkil</th>
                                    <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Prim Orani (%)</th>
                                    <th class="py-2 text-center font-medium text-gray-600 dark:text-gray-400">Aktif</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="kayit in muvekkilOranlari" :key="kayit.muvekkil_id">
                                    <tr :class="!kayit.muvekkil_aktif ? 'opacity-50' : ''">
                                        <td class="py-3 text-gray-900 dark:text-white" x-text="kayit.muvekkil_ad"></td>
                                        <td class="py-3">
                                            <input type="number" min="0" max="100" step="0.01" x-model="kayit.prim_orani"
                                                placeholder="Boş bırak: kaldır"
                                                class="w-40 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white">
                                        </td>
                                        <td class="py-3 text-center">
                                            <input type="checkbox" x-model="kayit.aktif"
                                                class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="aktifSekme === 'audit'" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Tarih</th>
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Alan</th>
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">İşlem</th>
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Değiştiren</th>
                                <th class="py-2 text-left font-medium text-gray-600 dark:text-gray-400">Detay</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-if="auditKayitlari.length === 0">
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-500 dark:text-gray-400">Audit kaydı bulunamadı.</td>
                                </tr>
                            </template>
                            <template x-for="kayit in auditKayitlari" :key="kayit.id">
                                <tr class="align-top">
                                    <td class="py-3 text-gray-900 dark:text-white whitespace-nowrap" x-text="new Date(kayit.created_at).toLocaleString('tr-TR')"></td>
                                    <td class="py-3 text-gray-700 dark:text-gray-300" x-text="auditAlanEtiketi(kayit.alan_tipi)"></td>
                                    <td class="py-3">
                                        <span class="px-2 py-1 text-xs rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                                            x-text="auditIslemEtiketi(kayit.islem_tipi)"></span>
                                    </td>
                                    <td class="py-3 text-gray-700 dark:text-gray-300" x-text="kayit.degistiren"></td>
                                    <td class="py-3 text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                        <div x-show="kayit.hedef_anahtar" x-text="'Anahtar: ' + jsonOzet(kayit.hedef_anahtar)"></div>
                                        <div x-show="kayit.eski_deger" x-text="'Eski: ' + jsonOzet(kayit.eski_deger)"></div>
                                        <div x-show="kayit.yeni_deger" x-text="'Yeni: ' + jsonOzet(kayit.yeni_deger)"></div>
                                        <div x-show="kayit.aciklama" class="text-gray-500 dark:text-gray-500" x-text="kayit.aciklama"></div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <button @click="acik = false"
                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                Kapat
            </button>
        </div>
    </div>
</div>

<script>
function yetkiYonetimiModal() {
    return {
        acik: false,
        yukleniyor: false,
        kaydediliyor: false,
        aktifSekme: 'kullanicilar',

        kullanicilar: [],
        tabTanimlari: [],
        kademeler: [],
        hacizciKademeleri: [],
        kademePayOranlari: [],
        kademePrimAsamalari: [],
        muvekkilOranlari: [],
        auditKayitlari: [],

        async yukle() {
            this.yukleniyor = true;
            try {
                const [kullaniciRes, primRes] = await Promise.all([
                    fetch('/tahsilat/yetki', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
                    fetch('/tahsilat/yetki/prim-ayarlar', { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
                ]);

                if (!kullaniciRes.ok) {
                    throw new Error(await this.hataMesaji(kullaniciRes));
                }
                if (!primRes.ok) {
                    throw new Error(await this.hataMesaji(primRes));
                }

                const kullaniciData = await kullaniciRes.json();
                this.kullanicilar = Array.isArray(kullaniciData)
                    ? kullaniciData
                    : (kullaniciData.kullanicilar ?? []);
                this.tabTanimlari = Array.isArray(kullaniciData.tab_tanimlari)
                    ? kullaniciData.tab_tanimlari
                    : [];
                this.kullanicilar = this.kullanicilar.map((kul) => ({
                    ...kul,
                    yonetici: Boolean(kul.yonetici),
                    protokol_duzenleyebilir: Boolean(kul.protokol_duzenleyebilir),
                    toplu_protokol_ekleyebilir: Boolean(kul.toplu_protokol_ekleyebilir),
                    tab_permissions: kul.tab_permissions ?? {},
                }));

                const primData = await primRes.json();
                this.kademeler = (primData.kademeler ?? []).map((k) => ({
                    ...k,
                    varsayilan_prim_orani: this.toNumber(k.varsayilan_prim_orani),
                    aktif: Boolean(k.aktif),
                }));

                this.hacizciKademeleri = (primData.hacizci_kademeleri ?? []).map((h) => ({
                    ...h,
                    aktif: Boolean(h.aktif),
                }));

                this.kademePayOranlari = (primData.kademe_pay_oranlari ?? []).map((p) => ({
                    ...p,
                    ust_kademe_orani: this.toNumber(p.ust_kademe_orani),
                    alt_kademe_orani: this.toNumber(p.alt_kademe_orani),
                    aktif: Boolean(p.aktif),
                }));
                this.kademePrimAsamalari = (primData.kademe_prim_asamalari ?? []).map((a) => ({
                    ...a,
                    asama_no: parseInt(a.asama_no, 10) || 1,
                    esik_tutari: this.toNumber(a.esik_tutari),
                    prim_orani: this.toNumber(a.prim_orani),
                    aktif: Boolean(a.aktif),
                })).sort((sol, sag) => {
                    const kademeFark = this.kademeEtiketi(sol.kademe).localeCompare(this.kademeEtiketi(sag.kademe), 'tr');
                    if (kademeFark !== 0) return kademeFark;
                    return this.toNumber(sol.asama_no) - this.toNumber(sag.asama_no);
                });

                this.muvekkilOranlari = (primData.muvekkil_oranlari ?? []).map((m) => ({
                    ...m,
                    prim_orani: m.prim_orani === null ? '' : this.toNumber(m.prim_orani),
                    aktif: Boolean(m.aktif),
                }));

                this.auditKayitlari = primData.audit_kayitlari ?? [];
            } catch (error) {
                alert(error.message || 'Veriler yüklenemedi.');
            } finally {
                this.yukleniyor = false;
            }
        },

        async yetkiGuncelle(kul, alan, deger) {
            const onceki = kul[alan];
            kul[alan] = deger;

            try {
                await this.kullaniciYetkiKaydet(kul);
            } catch (error) {
                kul[alan] = onceki;
                alert(error.message || 'Yetki güncellenemedi.');
            }
        },

        tabYetkisiVarMi(kul, tabAnahtari) {
            if (!kul || !tabAnahtari) {
                return false;
            }

            if (kul.yonetici) {
                return true;
            }

            return Boolean(kul.tab_permissions?.[tabAnahtari]);
        },

        async tabYetkiGuncelle(kul, tabAnahtari, goruntuleyebilir) {
            if (kul?.yonetici) {
                return;
            }

            const oncekiMap = { ...(kul.tab_permissions ?? {}) };
            kul.tab_permissions = {
                ...oncekiMap,
                [tabAnahtari]: Boolean(goruntuleyebilir),
            };

            try {
                await this.kullaniciYetkiKaydet(kul);
            } catch (error) {
                kul.tab_permissions = oncekiMap;
                alert(error.message || 'Tab yetkisi güncellenemedi.');
            }
        },

        async kullaniciYetkiKaydet(kul) {
            const res = await fetch('/tahsilat/yetki/' + kul.id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    tahsilat_olusturabilir: Boolean(kul.tahsilat_olusturabilir),
                    protokol_olusturabilir: Boolean(kul.protokol_olusturabilir),
                    protokol_duzenleyebilir: Boolean(kul.protokol_duzenleyebilir),
                    toplu_protokol_ekleyebilir: Boolean(kul.toplu_protokol_ekleyebilir),
                    tahsilat_takip_sorumlusu: Boolean(kul.tahsilat_takip_sorumlusu),
                    aktif: true,
                    tab_permissions: kul.tab_permissions ?? {},
                }),
            });

            if (!res.ok) {
                throw new Error(await this.hataMesaji(res));
            }
        },

        async kaydetKademePayOranlari() {
            for (const pay of this.kademePayOranlari) {
                const toplam = this.oranToplami(pay);
                if (Math.abs(toplam - 100) > 0.01) {
                    alert(this.kademeEtiketi(pay.ust_kademe) + ' / ' + this.kademeEtiketi(pay.alt_kademe) + ' için oran toplamı 100 olmalıdır.');
                    return;
                }
            }

            this.kaydediliyor = true;
            try {
                const payload = {
                    pay_oranlari: this.kademePayOranlari.map((p) => ({
                        ust_kademe: p.ust_kademe,
                        alt_kademe: p.alt_kademe,
                        ust_kademe_orani: this.toNumber(p.ust_kademe_orani),
                        alt_kademe_orani: this.toNumber(p.alt_kademe_orani),
                        aktif: Boolean(p.aktif),
                    })),
                };

                const res = await fetch('/tahsilat/yetki/prim-ayarlar/kademe-pay', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    throw new Error(await this.hataMesaji(res));
                }

                alert('Kademe arası pay oranları kaydedildi.');
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },


        async kaydetKademePrimAsamalari() {
            const grupMap = {};
            for (const asama of this.kademePrimAsamalari) {
                const kademe = String(asama.kademe ?? '');
                if (!grupMap[kademe]) {
                    grupMap[kademe] = [];
                }
                grupMap[kademe].push(asama);
            }

            for (const [kademe, satirlar] of Object.entries(grupMap)) {
                if (satirlar.length !== 3) {
                    alert(this.kademeEtiketi(kademe) + ' için 3 aşama zorunludur.');
                    return;
                }

                const sirali = [...satirlar].sort((a, b) => this.toNumber(a.asama_no) - this.toNumber(b.asama_no));
                const asamaListesi = sirali.map((s) => this.toNumber(s.asama_no));
                if (asamaListesi.join(',') !== '1,2,3') {
                    alert(this.kademeEtiketi(kademe) + ' için aşama numaraları 1, 2 ve 3 olmalıdır.');
                    return;
                }

                let oncekiEsik = -1;
                for (const satir of sirali) {
                    const esik = this.toNumber(satir.esik_tutari);
                    if (esik < oncekiEsik) {
                        alert(this.kademeEtiketi(kademe) + ' için eşik tutarları artan veya eşit olmalıdır.');
                        return;
                    }
                    oncekiEsik = esik;
                }
            }

            this.kaydediliyor = true;
            try {
                const payload = {
                    asamalar: this.kademePrimAsamalari.map((a) => ({
                        kademe: a.kademe,
                        asama_no: parseInt(a.asama_no, 10) || 1,
                        esik_tutari: this.toNumber(a.esik_tutari),
                        prim_orani: this.toNumber(a.prim_orani),
                        aktif: Boolean(a.aktif),
                    })),
                };

                const res = await fetch('/tahsilat/yetki/prim-ayarlar/kademe-asama', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    throw new Error(await this.hataMesaji(res));
                }

                alert('Kademe prim aşama ayarları kaydedildi.');
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },
        async kaydetHacizciKademeleri() {
            this.kaydediliyor = true;
            try {
                const payload = {
                    hacizciler: this.hacizciKademeleri.map((h) => ({
                        hacizci_id: h.hacizci_id,
                        kademe: h.kademe,
                    })),
                };

                const res = await fetch('/tahsilat/yetki/prim-ayarlar/hacizci-kademe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    throw new Error(await this.hataMesaji(res));
                }

                alert('Hacizci kademeleri güncellendi.');
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        async kaydetMuvekkilOranlari() {
            this.kaydediliyor = true;
            try {
                const payload = {
                    muvekkil_oranlari: this.muvekkilOranlari.map((m) => ({
                        muvekkil_id: m.muvekkil_id,
                        prim_orani: m.prim_orani === '' || m.prim_orani === null ? null : this.toNumber(m.prim_orani),
                        aktif: Boolean(m.aktif),
                    })),
                };

                const res = await fetch('/tahsilat/yetki/prim-ayarlar/muvekkil-oranlari', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    throw new Error(await this.hataMesaji(res));
                }

                alert('Müvekkil bazlı prim oranları kaydedildi.');
                await this.yukle();
            } catch (error) {
                alert(error.message || 'Kayıt sırasında hata oluştu.');
            } finally {
                this.kaydediliyor = false;
            }
        },

        oranToplami(pay) {
            return this.toNumber(pay.ust_kademe_orani) + this.toNumber(pay.alt_kademe_orani);
        },

        kademeEtiketi(kademe) {
            return kademe ? kademe.replace('kademe_', 'Kademe ') : '-';
        },

        auditAlanEtiketi(alan) {
            const map = {
                kademe_varsayilan_orani: 'Kademe Varsayılan Prim',
                hacizci_kademe: 'Hacizci Kademe',
                kademe_pay_orani: 'Kademe Pay Oranı',
                kademe_prim_asama_orani: 'Kademe Prim Aşama',
                muvekkil_genel_prim_orani: 'Müvekkil Genel Prim',
            };
            return map[alan] ?? alan;
        },

        auditIslemEtiketi(islem) {
            const map = {
                create: 'Oluşturma',
                update: 'Güncelleme',
                delete: 'Silme',
            };
            return map[islem] ?? islem;
        },

        jsonOzet(veri) {
            if (!veri) return '-';
            try {
                return JSON.stringify(veri);
            } catch (_) {
                return String(veri);
            }
        },

        toNumber(value) {
            const parsed = parseFloat(value);
            return Number.isFinite(parsed) ? parsed : 0;
        },

        async hataMesaji(res) {
            try {
                const data = await res.json();
                if (data.message) return data.message;
                if (data.error) return data.error;
                if (data.errors) {
                    const first = Object.values(data.errors)[0];
                    if (Array.isArray(first) && first.length > 0) return first[0];
                }
            } catch (_) {
                // noop
            }

            return 'Sunucu hatası oluştu.';
        },
    };
}
</script>










