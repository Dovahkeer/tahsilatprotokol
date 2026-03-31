<x-layouts.app>
    <div
        x-data="tahsilatPanel(@js($visibleTabs), @js($defaultTab))"
        @tahsilat-tab-degistir.window="tabDegistir($event.detail?.tab)"
        class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.16),_transparent_28%),linear-gradient(180deg,_#fffaf0_0%,_#f8fafc_48%,_#f1f5f9_100%)]"
    >
        <header class="border-b border-white/70 bg-white/80 backdrop-blur">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 px-6 py-6 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-700">Tahsilat Yönetimi</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900">Protokol ve tahsilat merkezi</h1>
                    <p class="mt-2 text-sm text-slate-500">{{ auth()->user()->name }} olarak giriş yaptınız.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <template x-for="tab in tabs" :key="tab.key">
                        <button type="button"
                            @click="tabDegistir(tab.key)"
                            :class="aktifTab === tab.key ? 'bg-slate-900 text-white shadow-lg shadow-slate-900/15' : 'bg-white text-slate-600 hover:bg-slate-50'"
                            class="inline-flex h-11 items-center rounded-2xl border border-slate-200 px-4 text-sm font-semibold transition">
                            <span x-text="tab.label"></span>
                        </button>
                    </template>

                    @if(auth()->user()->isAdmin())
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('tahsilat-yetki-modal-ac'))"
                            class="inline-flex h-11 items-center rounded-2xl border border-amber-200 bg-amber-50 px-4 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                            Yetki ve Prim
                        </button>
                    @endif

                    <form action="{{ route('logout') }}" method="post" class="inline-flex">
                        @csrf
                        <button type="submit"
                            class="inline-flex h-11 items-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                            Çıkış
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
            @if($visibleTabs->isEmpty())
                <section class="rounded-[2rem] border border-white/80 bg-white/85 px-8 py-12 text-center shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-amber-700">Yetki Gerekli</p>
                    <h2 class="mt-3 text-2xl font-black text-slate-900">Bu hesap için görünür sekme atanmadı.</h2>
                    <p class="mt-3 text-sm text-slate-500">Admin hesabı ile Yetki ve Prim ekranından bu kullanıcıya sekme yetkisi verebilirsiniz.</p>
                </section>
            @endif

            @if($visibleTabs->contains(fn ($tab) => $tab['key'] === 'dashboard'))
                <section x-show="aktifTab === 'dashboard'" x-cloak class="rounded-[2rem] border border-white/80 bg-white/85 shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur">
                    @include('components.private.tahsilat.DashboardTab')
                </section>
            @endif

            @if($visibleTabs->contains(fn ($tab) => $tab['key'] === 'tahsilat'))
                <section x-show="aktifTab === 'tahsilat'" x-cloak class="rounded-[2rem] border border-white/80 bg-white/85 shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur">
                    @include('components.private.tahsilat.TahsilatTakipTab')
                </section>
            @endif

            @if($visibleTabs->contains(fn ($tab) => $tab['key'] === 'tum_tahsilatlar'))
                <section x-show="aktifTab === 'tum_tahsilatlar'" x-cloak class="rounded-[2rem] border border-white/80 bg-white/85 shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur">
                    @include('components.private.tahsilat.TumTahsilatlarTab')
                </section>
            @endif

            @if($visibleTabs->contains(fn ($tab) => $tab['key'] === 'protokol'))
                <section x-show="aktifTab === 'protokol'" x-cloak class="rounded-[2rem] border border-white/80 bg-white/85 shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur">
                    @include('components.private.tahsilat.ProtokolTab')
                </section>
            @endif

            @if($visibleTabs->contains(fn ($tab) => $tab['key'] === 'prim'))
                <section x-show="aktifTab === 'prim'" x-cloak class="rounded-[2rem] border border-white/80 bg-white/85 shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur">
                    @include('components.private.tahsilat.PrimListesiTab')
                </section>
            @endif
        </main>

        @if($visibleTabs->contains(fn ($tab) => $tab['key'] === 'tahsilat'))
            <template x-teleport="body">
            @include('components.private.tahsilat.TopluTahsilatModal')
            </template>
        @endif
        @if(auth()->user()->isAdmin())
            @include('components.private.tahsilat.YetkiModal')
        @endif
    </div>

    <script>
        function tahsilatPanel(tabs, defaultTab) {
            return {
                tabs,
                aktifTab: defaultTab,

                tabDegistir(tabKey) {
                    if (!this.tabs.some((tab) => tab.key === tabKey)) {
                        return;
                    }

                    this.aktifTab = tabKey;
                },
            };
        }
    </script>
</x-layouts.app>
