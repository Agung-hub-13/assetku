<!-- CONTAINER UTAMA: Hapus overflow-hidden agar dropdown tidak terpotong -->
<header class="h-20 relative bg-white/40 dark:bg-slate-950/40 border-b border-slate-200/40 dark:border-slate-800/40 sticky top-0 z-50 px-6 flex items-center justify-between transition-all duration-300 backdrop-blur-xl shadow-sm shadow-slate-950/5">

    <!-- PENGHIAS LATAR BELAKANG GEOMETRIS (overflow-hidden dipindah ke sini) -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-[0.02] dark:opacity-[0.04]">
        <div class="absolute -top-10 left-1/4 w-32 h-32 border border-slate-900 dark:border-white rounded-full"></div>
        <div class="absolute top-1/2 left-1/3 w-96 h-1 bg-gradient-to-r from-slate-900 dark:from-white to-transparent transform -rotate-12"></div>
    </div>

    {{-- LEFT: Info Menu Aktif --}}
    <div class="flex flex-col gap-0.5 relative z-10">
        <p class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-extrabold flex items-center gap-1.5">
            <span class="w-1 h-1 bg-blue-500 dark:bg-blue-400 rounded-full"></span>
            {{ $activeGroup ?? 'Admin Panel' }}
        </p>
        <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white bg-clip-text">
            {{ $activeLabel ?? 'Dashboard' }}
        </h1>
    </div>

    {{-- RIGHT: Tools & User Profile --}}
    <div class="flex items-center gap-3 sm:gap-4 relative z-10">

        {{-- TOGGLE DARK MODE --}}
        <button type="button" id="dark-mode-toggle" class="p-2.5 rounded-xl border border-slate-200/50 dark:border-slate-800/60 bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-yellow-400 hover:bg-white/90 dark:hover:bg-slate-800/80 hover:text-blue-600 dark:hover:text-yellow-300 transition-all duration-300 shadow-sm hover:scale-105 active:scale-95" title="Ubah Kontras Tema">
            <svg id="sun-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.243 17.657l.707.707M6.343 6.343l.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
            </svg>
            <svg id="moon-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        {{-- NOTIFIKASI DROPDOWN DINAMIS --}}
        @php
        $unreadCount = auth()->user()->unreadNotifications->count();
        $notifications = auth()->user()->notifications()->take(6)->get();
        @endphp

        <div class="relative" x-data="{ open: false }">
            <!-- Tombol Bell Icon -->
            <button @click="open = !open" @click.away="open = false" type="button"
                class="relative p-2.5 rounded-xl border border-slate-200/50 dark:border-slate-800/60 bg-white/60 dark:bg-slate-900/40 text-slate-600 dark:text-slate-300 hover:bg-white/90 dark:hover:bg-slate-800/80 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300 shadow-sm hover:scale-105 active:scale-95"
                title="Notifikasi">

                <i data-lucide="bell" class="w-4 h-4"></i>

                <!-- Badge Jumlah Notifikasi Belum Dibaca -->
                @if($unreadCount > 0)
                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[9px] font-black text-white items-center justify-center leading-none">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                </span>
                @endif
            </button>

            <!-- Dropdown Menu Content (Z-Index dinaikkan ke z-[100]) -->
            <div x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="absolute right-0 mt-3 w-80 sm:w-96 rounded-2xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-2xl shadow-slate-950/20 z-[100] overflow-hidden"
                style="display: none;">

                <!-- Dropdown Header -->
                <div class="px-4 py-3.5 border-b border-slate-200/50 dark:border-slate-800/60 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.notifications.index') }}" class="text-sm font-bold text-slate-800 dark:text-white hover:text-blue-600 transition-colors">
                            Notifikasi
                        </a>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-500/10 text-blue-600 dark:text-blue-400">
                            {{ $unreadCount }} Baru
                        </span>
                    </div>
                    @if($unreadCount > 0)
                    <form action="{{ route('admin.notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            Tandai semua dibaca
                        </button>
                    </form>
                    @endif
                </div>

                <!-- List Notifikasi Dinamis -->
                <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/40 custom-scrollbar">
                    @forelse($notifications as $notification)
                    @php
                    $isUnread = is_null($notification->read_at);
                    $data = $notification->data;
                    $icon = $data['icon'] ?? 'bell';

                    $targetUrl = Route::has('admin.notifications.read')
                    ? route('admin.notifications.read', $notification->id)
                    : route('admin.notifications.index');
                    @endphp

                    <a href="{{ $targetUrl }}"
                        class="flex gap-3 px-4 py-3 transition-colors duration-200 group {{ $isUnread ? 'bg-blue-50/30 dark:bg-blue-500/5 hover:bg-blue-50/60' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">

                        <!-- Icon Notifikasi -->
                        <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5 group-hover:scale-110 transition-transform">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                        </div>

                        <!-- Text Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">
                                {{ $data['title'] ?? 'Notifikasi Baru' }}
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5">
                                {{ $data['message'] ?? '' }}
                            </p>
                            <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 mt-1 block">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Indikator Unread -->
                        @if($isUnread)
                        <div class="w-2 h-2 rounded-full bg-blue-500 shrink-0 self-center"></div>
                        @endif
                    </a>
                    @empty
                    <div class="py-8 text-center">
                        <i data-lucide="bell-off" class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600 mb-2"></i>
                        <p class="text-xs font-medium text-slate-400 dark:text-slate-500">Tidak ada notifikasi saat ini</p>
                    </div>
                    @endforelse
                </div>

                <!-- FOOTER: TOMBOL LIHAT SEMUA NOTIFIKASI -->
                <div class="p-2 border-t border-slate-200/50 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-800/30 text-center">
                    <a href="{{ route('admin.notifications.index') }}"
                        class="block w-full py-2 px-3 text-xs font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-xl transition-colors">
                        Lihat Semua Notifikasi
                    </a>
                </div>

            </div>
        </div>

        {{-- USER INFO --}}
        <div class="flex items-center gap-3 bg-white/60 dark:bg-slate-900/40 border border-slate-200/50 dark:border-slate-800/60 rounded-xl px-3.5 py-1.5 shadow-sm transition-all duration-300 hover:bg-white/90 dark:hover:bg-slate-800/80 cursor-pointer group">
            <div class="relative">
                <img
                    src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'Admin') }}&background=3b82f6&color=fff&bold=true"
                    class="w-8 h-8 rounded-lg ring-2 ring-blue-500/10 group-hover:ring-blue-500/30 transition-all duration-300"
                    alt="User Avatar">
                <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 border-2 border-white dark:border-slate-950 rounded-full">
                    <span class="absolute inset-0 rounded-full bg-emerald-400 ping opacity-75 animate-ping"></span>
                </div>
            </div>
            <div class="hidden sm:block text-left">
                <p class="text-xs font-bold leading-none text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                    {{ auth()->user()->name ?? 'Administrator' }}
                </p>
                @foreach(auth()->user()->roles as $role)
                <span class="inline-flex items-center mt-1 px-1.5 py-0.5 rounded text-[8px] font-black uppercase bg-blue-500/10 text-blue-600 dark:text-blue-400 tracking-wider">
                    {{ $role->name }}
                </span>
                @endforeach
            </div>
        </div>

    </div>
</header>