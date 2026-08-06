@extends('layouts.admin')

@section('title', 'Laporan & Ringkasan Asset')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- ELEMEN WATERMARK TRANSPARAN -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.05]"
            style="background-image: radial-gradient(#94a3b8 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
        <div class="text-slate-900 dark:text-white opacity-[0.03] dark:opacity-[0.02] transform -rotate-12 scale-125 md:scale-150 p-4">
            <!-- SVG Ikon Dokumen Laporan / Grafik / Audit -->
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
    </div>

    <div class="space-y-6 report-watermark pb-12">
        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-bold font-mono text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Laporan & Audit</span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">Laporan Ringkasan Asset</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ikhtisar total aset, nilai investasi, serta distribusi per kategori dan lokasi.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-600/20 flex items-center gap-2 transition print:hidden">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-slate-200 dark:border-slate-700 print:hidden">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.asset_reports.index') }}" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 border-b-2 py-3 px-1 text-sm font-semibold">Ringkasan Utama</a>
                <a href="{{ route('admin.asset_reports.maintenance') }}" class="border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 hover:border-slate-300 border-b-2 py-3 px-1 text-sm font-semibold transition">Laporan Maintenance</a>
                <a href="{{ route('admin.asset_reports.loans') }}" class="border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 hover:border-slate-300 border-b-2 py-3 px-1 text-sm font-semibold transition">Laporan Peminjaman</a>
            </nav>
        </div>

        <!-- Top Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">Total Unit Asset</p>
                    <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($totalAssets) }}</h3>
                </div>
                <div class="p-3.5 bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">Total Nilai Asset</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                        Rp {{ number_format($totalValue, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm relative z-10">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Sebaran Status Asset</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @forelse($reportByStatus as $status)
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-700">
                    <span class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">{{ $status->status ?? 'Tanpa Status' }}</span>
                    <p class="text-xl font-extrabold text-slate-800 dark:text-white mt-1">{{ number_format($status->total) }} Unit</p>
                </div>
                @empty
                <p class="text-slate-400 text-sm col-span-full">Belum ada data status.</p>
                @endforelse
            </div>
        </div>

        <!-- Tables Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 relative z-10">
            <!-- By Category -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50">
                    <h2 class="font-bold text-slate-800 dark:text-white text-sm">Laporan Per Kategori</h2>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs text-slate-500 dark:text-slate-400 uppercase bg-slate-100/70 dark:bg-slate-700/50 font-semibold">
                            <tr>
                                <th class="px-4 py-3.5">Kategori</th>
                                <th class="px-4 py-3.5 text-center">Jumlah</th>
                                <th class="px-4 py-3.5 text-right">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($reportByCategory as $cat)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                <td class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200">{{ $cat->name }}</td>
                                <td class="px-4 py-3.5 text-center font-medium">{{ number_format($cat->assets_count) }}</td>
                                <td class="px-4 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($cat->total_price_sum ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-400">Tidak ada data kategori.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- By Location -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50">
                    <h2 class="font-bold text-slate-800 dark:text-white text-sm">Laporan Per Lokasi</h2>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                        <thead class="text-xs text-slate-500 dark:text-slate-400 uppercase bg-slate-100/70 dark:bg-slate-700/50 font-semibold">
                            <tr>
                                <th class="px-4 py-3.5">Lokasi</th>
                                <th class="px-4 py-3.5 text-center">Jumlah</th>
                                <th class="px-4 py-3.5 text-right">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($reportByLocation as $loc)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                <td class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200">{{ $loc->name }}</td>
                                <td class="px-4 py-3.5 text-center font-medium">{{ number_format($loc->assets_count) }}</td>
                                <td class="px-4 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($loc->total_price_sum ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-400">Tidak ada data lokasi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection