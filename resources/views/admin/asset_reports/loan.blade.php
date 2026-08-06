@extends('layouts.admin')

@section('title', 'Laporan Peminjaman Asset')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN LAPORAN PEMINJAMAN -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.05]"
            style="background-image: radial-gradient(#94a3b8 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
        <div class="text-slate-900 dark:text-white opacity-[0.03] dark:opacity-[0.02] transform -rotate-12 scale-125 md:scale-150 p-4">
            <!-- SVG Ikon Peminjaman / Handshake / Transfer / Hand & Item / Arrow Exchange -->
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
        </div>
    </div>

    <div class="space-y-6 report-watermark pb-12">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-bold font-mono text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Laporan & Audit</span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">Laporan Riwayat Peminjaman</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Daftar penggunaan dan riwayat peminjaman aset oleh pengguna.</p>
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
                <a href="{{ route('admin.asset_reports.maintenance') }}" class="border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 hover:border-slate-300 border-b-2 py-3 px-1 text-sm font-semibold transition">Laporan Maintenance</a>
                <a href="{{ route('admin.asset_reports.loans') }}" class="border-indigo-500 text-indigo-600 dark:text-indigo-400 border-b-2 py-3 px-1 text-sm font-semibold">Laporan Peminjaman</a>
            </nav>
        </div>

        <!-- Filter Status -->
        <form method="GET" action="{{ route('admin.asset_reports.loans') }}" class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-wrap items-end gap-4 print:hidden relative z-10">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Status Peminjaman</label>
                <select name="status" class="px-3.5 py-2 text-sm bg-slate-50 dark:bg-slate-700 text-slate-800 dark:text-white border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:outline-none min-w-[180px]">
                    <option value="">-- Semua Status --</option>
                    <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Approval</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition">Filter</button>
                <a href="{{ route('admin.asset_reports.loans') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 text-sm font-semibold rounded-xl transition">Reset</a>
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden relative z-10">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                    <thead class="text-xs text-slate-500 dark:text-slate-400 uppercase bg-slate-100/70 dark:bg-slate-700/50 font-semibold">
                        <tr>
                            <th class="px-4 py-3.5">Peminjam</th>
                            <th class="px-4 py-3.5">Asset</th>
                            <th class="px-4 py-3.5">Tanggal Pinjam</th>
                            <th class="px-4 py-3.5">Tanggal Kembali</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($loans as $loan)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                            <td class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200">{{ $loan->user->name ?? 'User Dihapus' }}</td>
                            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $loan->asset->name ?? 'Asset Dihapus' }}</td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $loan->created_at ? $loan->created_at->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $loan->returned_at ? \Carbon\Carbon::parse($loan->returned_at)->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-3 py-1 text-xs font-bold rounded-full 
                                    {{ $loan->status === 'returned' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400' : '' }}
                                    {{ $loan->status === 'borrowed' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400' : '' }}
                                    {{ $loan->status === 'pending' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-400' : '' }}">
                                    {{ ucfirst($loan->status ?? 'N/A') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada riwayat peminjaman ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($loans->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700 print:hidden">
                {{ $loans->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection