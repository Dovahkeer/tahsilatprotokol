<x-layouts.app>
    <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.25),_transparent_34%),linear-gradient(135deg,_#fff8eb_0%,_#f8fafc_50%,_#eef2ff_100%)]">
        <div class="absolute inset-0 opacity-60">
            <div class="absolute left-[-8rem] top-[-7rem] h-64 w-64 rounded-full bg-amber-300/35 blur-3xl"></div>
            <div class="absolute bottom-[-10rem] right-[-5rem] h-72 w-72 rounded-full bg-sky-300/30 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-6xl items-center px-6 py-12">
            <div class="grid w-full gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="hidden rounded-[2rem] border border-white/70 bg-white/55 p-10 shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur lg:block">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-amber-700">Tahsilat Operasyon Paneli</p>
                    <h1 class="mt-6 max-w-xl text-5xl font-black leading-tight text-slate-900">Protokol, tahsilat ve izlence tek akışta.</h1>
                    <p class="mt-6 max-w-xl text-base leading-7 text-slate-600">
                        Protokol bakiye takibi, beklemede/onay/red yaşam döngüsü, dashboard analitiği ve prim pivotu aynı ekranda çalışır.
                    </p>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl border border-slate-200 bg-white/80 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Onay Akışı</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">Beklemede, onay, red ve iptal etkileri ayrıdır.</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white/80 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Import</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">Toplu protokol ve dashboard geçmiş importu hazırdır.</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white/80 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Prim Pivot</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">Sadece gerçek onaylı tahsilatlardan beslenir.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center">
                    <div class="w-full max-w-md rounded-[2rem] border border-white/80 bg-white/90 p-8 shadow-[0_24px_80px_rgba(15,23,42,0.12)] backdrop-blur">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-amber-700">Giriş</p>
                            <h2 class="mt-3 text-3xl font-black text-slate-900">Oturum açın</h2>
                            <p class="mt-2 text-sm text-slate-500">Lokal kullanıcı hesabınız ile sisteme giriş yapın.</p>
                        </div>

                        <form action="{{ route('login.store') }}" method="post" class="mt-8 space-y-5">
                            @csrf

                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">E-posta</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15">
                                @error('email')
                                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Şifre</label>
                                <input id="password" name="password" type="password" required
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm text-slate-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/15">
                            </div>

                            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                Beni hatırla
                            </label>

                            <button type="submit"
                                class="inline-flex h-12 w-full items-center justify-center rounded-2xl bg-slate-900 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-900/15">
                                Giriş Yap
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
