@extends('layouts.admin')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
<!-- Background Watermark: Accurate API Token & Sync -->
<div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
    <!-- Grid Dot Pattern Overlay -->
    <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.05]"
        style="background-image: radial-gradient(#0284c7 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>

    <!-- Central SVG Watermark Icon (Representasi Token, Accurate Key & Sync API) -->
    <div class="text-sky-900 dark:text-sky-400 opacity-[0.035] dark:opacity-[0.03] transform -rotate-6 scale-100 sm:scale-125 md:scale-150 p-4 transition-transform duration-700">
        <svg class="w-72 h-72 sm:w-96 sm:h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <!-- Radar Sync / Cloud API Outer Orbit -->
            <circle cx="12" cy="12" r="10" stroke-width="0.5" stroke-dasharray="4 4" />
            <circle cx="12" cy="12" r="8" stroke-width="0.5" />

            <!-- Ikon Kunci Token / API Key (Integrasi Kemanan) -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M15 7a3 3 0 11-6 0 3 3 0 016 0zM12 10v10m0 0l3-3m-3 3l-3-3m0-3h6" />

            <!-- Panah Melingkar (Representasi Data Sync / Exchange) -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" stroke-dasharray="2 2" d="M4 12a8 8 0 0114.93-3M20 12a8 8 0 01-14.93 3" />

            <!-- Lightning / Connection Spark (Status Keterhubungan API) -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.6" d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
        </svg>
    </div>
</div>

<!-- Efek Ambient Glow Light (Nuansa Sky Blue & Cyan untuk Integrasi API Cloud) -->
<div class="absolute top-0 right-1/3 w-72 sm:w-[480px] h-72 sm:h-[480px] bg-sky-500/10 dark:bg-sky-500/5 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none z-0"></div>
<div class="absolute bottom-0 left-1/4 w-60 sm:w-[380px] h-60 sm:h-[380px] bg-cyan-500/10 dark:bg-cyan-500/5 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none z-0"></div>

    <div class="relative z-10 space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                    Accurate API Tokens
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola integrasi Accurate Online, database ID, session, dan host API.
                </p>
            </div>

            <div class="flex">
                <a href="{{ url('/accurate/connect') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-2xl font-bold text-sm sm:text-base shadow-lg transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Connect Accurate
                </a>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-2xl text-sm font-semibold">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-400 px-4 py-3 rounded-2xl text-sm font-semibold">
            {{ session('error') }}
        </div>
        @endif

        {{-- STATS GRID --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">

            <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-[2rem] p-4 sm:p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <p class="text-[10px] sm:text-xs uppercase tracking-widest text-slate-400 dark:text-slate-500 font-black">
                    Total Token
                </p>
                <h2 class="text-2xl sm:text-4xl font-black text-blue-600 dark:text-blue-400 mt-1 sm:mt-2">
                    {{ $tokens->count() }}
                </h2>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-[2rem] p-4 sm:p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                @php $connected = $tokens->whereNotNull('access_token')->count(); @endphp
                <p class="text-[10px] sm:text-xs uppercase tracking-widest text-slate-400 dark:text-slate-500 font-black">
                    Status
                </p>
                <div class="flex items-center gap-2 mt-2 sm:mt-3">
                    <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full {{ $connected ? 'bg-emerald-500' : 'bg-rose-500' }} animate-pulse"></div>
                    <span class="font-black text-sm sm:text-xl text-slate-800 dark:text-white">
                        {{ $connected ? 'Connected' : 'Disconnected' }}
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-[2rem] p-4 sm:p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <p class="text-[10px] sm:text-xs uppercase tracking-widest text-slate-400 dark:text-slate-500 font-black">
                    DB ID
                </p>
                <h2 class="text-sm sm:text-lg font-black text-slate-800 dark:text-white mt-1 sm:mt-2 break-all">
                    {{ $tokens->first()->db_id ?? '-' }}
                </h2>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-[2rem] p-4 sm:p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <p class="text-[10px] sm:text-xs uppercase tracking-widest text-slate-400 dark:text-slate-500 font-black">
                    Host API
                </p>
                <h2 class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 mt-1 sm:mt-2 break-all">
                    {{ $tokens->first()->host ?? '-' }}
                </h2>
            </div>

        </div>

        {{-- MAIN DATA CONTAINER --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">

            <div class="p-4 sm:px-6 sm:py-5 border-b border-slate-100 dark:border-slate-700">
                <h2 class="text-lg sm:text-xl font-black text-slate-800 dark:text-white">
                    Data Token Accurate
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Data access token, refresh token, session, host, dan database Accurate.
                </p>
            </div>

            {{-- 1. MOBILE VIEW (CARD FORMAT) -- Tampil hanya di HP (lg:hidden) --}}
            <div class="block lg:hidden divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse($tokens as $token)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-sm text-slate-700 dark:text-slate-200">
                            ID Token: #{{ $token->id }}
                        </span>

                        {{-- Expired Status --}}
                        <div>
                            @if($token->expired_at)
                                @if(\Carbon\Carbon::parse($token->expired_at)->isPast())
                                    <span class="px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 font-black text-[10px]">
                                        EXPIRED
                                    </span>
                                @else
                                    <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">
                                        Exp: {{ \Carbon\Carbon::parse($token->expired_at)->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/50">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">DB ID</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">{{ $token->db_id ?? '-' }}</span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/50">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Host</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300 break-all">{{ $token->host ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Access Token</span>
                            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700 rounded-xl p-2.5">
                                <p class="font-mono text-[10px] text-slate-700 dark:text-slate-300 break-all line-clamp-2">
                                    {{ $token->access_token }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Session</span>
                            <p class="font-mono text-[10px] text-slate-600 dark:text-slate-400 break-all">
                                {{ $token->session ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-12 text-center">
                    <p class="text-slate-500 dark:text-slate-400 font-medium">Belum Ada Token</p>
                </div>
                @endforelse
            </div>

            {{-- 2. DESKTOP VIEW (TABLE FORMAT) -- Tampil hanya di Layar Besar (hidden lg:block) --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-100/60 dark:bg-slate-700/50 text-slate-400">
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest">Access Token</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest">Refresh Token</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest">DB ID</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest">Session</th>
                            <th class="px-6 py-4 text-xs font-black uppercase tracking-widest">Host</th>
                            <th class="px-6 py-4 text-center text-xs font-black uppercase tracking-widest">Expired</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse($tokens as $token)
                        <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-700/50 transition-all">
                            <td class="px-6 py-5 font-black text-slate-700 dark:text-slate-200">
                                #{{ $token->id }}
                            </td>
                            <td class="px-6 py-5">
                                <div class="max-w-[240px]">
                                    <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700 rounded-xl p-3">
                                        <p class="font-mono text-[11px] text-slate-700 dark:text-slate-300 break-all line-clamp-2">
                                            {{ $token->access_token }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="max-w-[240px]">
                                    <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700 rounded-xl p-3">
                                        <p class="font-mono text-[11px] text-slate-500 dark:text-slate-400 break-all line-clamp-2">
                                            {{ $token->refresh_token ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="inline-flex px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-black text-xs">
                                    {{ $token->db_id ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="max-w-[200px]">
                                    <p class="font-mono text-[11px] text-slate-600 dark:text-slate-400 break-all line-clamp-2">
                                        {{ $token->session ?? '-' }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="max-w-[200px]">
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 break-all">
                                        {{ $token->host ?? '-' }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                @if($token->expired_at)
                                    @if(\Carbon\Carbon::parse($token->expired_at)->isPast())
                                    <span class="inline-flex px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 font-black text-xs">
                                        EXPIRED
                                    </span>
                                    @else
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 dark:text-slate-200 text-xs">
                                            {{ \Carbon\Carbon::parse($token->expired_at)->format('d/m/Y') }}
                                        </span>
                                        <span class="text-[10px] uppercase tracking-widest text-slate-400 font-black">
                                            {{ \Carbon\Carbon::parse($token->expired_at)->format('H:i') }} WIB
                                        </span>
                                    </div>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-sm">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 rounded-full bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center mb-4">
                                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-black text-slate-700 dark:text-slate-200">Belum Ada Token</h3>
                                    <p class="text-xs text-slate-400 mt-1">Hubungkan Accurate Online terlebih dahulu.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

</div>
@endsection