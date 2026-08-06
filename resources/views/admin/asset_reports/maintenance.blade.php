@extends('layouts.admin')

@section('title', 'Laporan Maintenance Asset')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- ELEMEN WATERMARK TRANSPARAN -->
    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN LAPORAN MAINTENANCE -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.05]"
            style="background-image: radial-gradient(#94a3b8 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
        <div class="text-slate-900 dark:text-white opacity-[0.03] dark:opacity-[0.02] transform -rotate-12 scale-125 md:scale-150 p-4">
            <!-- SVG Ikon Wrench / Perbaikan / Maintenance / Tools -->
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
    </div>

    <div class="space-y-6 report-watermark pb-12">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-bold font-mono text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Laporan & Audit</span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">Laporan Maintenance & Perbaikan</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Riwayat biaya dan aktivitas pemeliharaan aset.</p>
            </div>
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-600/20 flex items-center gap-2 transition print:hidden self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Laporan
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-slate-200 dark:border-slate-700 print:hidden">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.asset_reports.index') }}" class="border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 hover:border-slate-300 border-b-2 py-3 px-1 text-sm font-semibold transition">Ringkasan Utama</a>
                <a href="{{ route('admin.asset_reports.maintenance') }}" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 border-b-2 py-3 px-1 text-sm font-semibold">Laporan Maintenance</a>
                <a href="{{ route('admin.asset_reports.loans') }}" class="border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 hover:border-slate-300 border-b-2 py-3 px-1 text-sm font-semibold transition">Laporan Peminjaman</a>
            </nav>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.asset_reports.maintenance') }}" class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-wrap items-end gap-4 print:hidden relative z-10">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition">Filter</button>
                <a href="{{ route('admin.asset_reports.maintenance') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-sm font-semibold rounded-xl transition">Reset</a>
            </div>
        </form>

        <!-- Summary Box -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 p-5 rounded-2xl flex items-center justify-between relative z-10">
            <span class="text-sm font-bold text-amber-800 dark:text-amber-300">Total Biaya Perbaikan (Filter Terpilih):</span>
            <span class="text-xl sm:text-2xl font-black text-amber-900 dark:text-amber-400">Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden relative z-10">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                    <thead class="text-xs text-slate-500 dark:text-slate-400 uppercase bg-slate-100/70 dark:bg-slate-700/50 font-semibold">
                        <tr>
                            <th class="px-4 py-3.5">Tanggal</th>
                            <th class="px-4 py-3.5">Asset</th>
                            <th class="px-4 py-3.5">Keterangan / Masalah</th>
                            <th class="px-4 py-3.5 text-right">Biaya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($maintenances as $item)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200">{{ $item->asset->name ?? 'Asset Dihapus' }}</td>
                            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $item->description ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($item->cost ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">Tidak ada riwayat perbaikan ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($maintenances->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700 print:hidden">
                {{ $maintenances->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection