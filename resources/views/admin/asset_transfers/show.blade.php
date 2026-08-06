@extends('layouts.admin')

@section('title', 'Detail Mutasi Aset - #' . ($assetTransfer->transfer_number ?? $assetTransfer->id))

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6">

    {{-- Top Bar & Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                    #{{ $assetTransfer->transfer_number ?? 'TRF-' . str_pad($assetTransfer->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                <span class="text-xs text-slate-400">
                    Dibuat: {{ $assetTransfer->created_at ? $assetTransfer->created_at->format('d M Y, H:i') : '-' }}
                </span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                Mutasi Aset: {{ $assetTransfer->asset->name ?? 'Aset Tidak Ditemukan' }}
            </h1>
        </div>
        <a href="{{ route('admin.asset_transfers.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-medium text-sm rounded-xl transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Index
        </a>
    </div>

    {{-- Alert Success / Error --}}
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Action Card & Workflow Status --}}
    <div class="bg-slate-900 text-white p-6 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-white/10 rounded-2xl backdrop-blur-md">
                <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block">Status Mutasi Saat Ini</span>
                <div class="flex items-center gap-2 mt-1">
                    @php
                        $statusBadges = [
                            'draft' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                            'completed' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                            'rejected' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-lg text-xs font-bold border {{ $statusBadges[$assetTransfer->status] ?? 'bg-slate-700 text-slate-300' }}">
                        {{ strtoupper($assetTransfer->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Dynamic Quick Action Buttons --}}
        <div class="flex items-center gap-3 w-full md:w-auto">
            @if($assetTransfer->status === 'draft')
                {{-- Button: Setujui Mutasi --}}
                <form method="POST" action="{{ route('admin.asset_transfers.approve', $assetTransfer->id) }}" class="w-full md:w-auto">
                    @csrf 
                    <button type="submit" onclick="return confirm('Setujui dan proses mutasi aset ini?')" class="w-full md:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-xl transition shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Setujui Mutasi
                    </button>
                </form>

                {{-- Button Option: Tolak Mutasi --}}
                <form method="POST" action="{{ route('admin.asset_transfers.reject', $assetTransfer->id) }}" class="w-full md:w-auto">
                    @csrf 
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menolak mutasi ini?')" class="w-full md:w-auto px-4 py-3 bg-slate-800 hover:bg-rose-900/50 text-slate-300 hover:text-rose-300 text-sm font-medium rounded-xl transition border border-slate-700 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Tolak Mutasi
                    </button>
                </form>
            @elseif($assetTransfer->status === 'completed')
                <div class="flex items-center gap-2 text-emerald-400 text-sm font-semibold bg-emerald-500/10 px-4 py-2.5 rounded-xl border border-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mutasi Telah Disetujui & Selesai
                </div>
            @elseif($assetTransfer->status === 'rejected')
                <div class="flex items-center gap-2 text-rose-400 text-sm font-semibold bg-rose-500/10 px-4 py-2.5 rounded-xl border border-rose-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mutasi Ditolak
                </div>
            @endif
        </div>
    </div>

    {{-- Grid Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left / Main Column --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Alur Perpindahan Lokasi --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">
                    Alur Perpindahan Lokasi
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Lokasi Asal --}}
                    <div class="p-4 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-200/80 dark:border-slate-700 space-y-2">
                        <span class="text-xs font-bold uppercase text-slate-400 tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            Lokasi Asal (Dari)
                        </span>
                        <div class="text-base font-bold text-slate-800 dark:text-slate-100">
                            {{ $assetTransfer->fromLocation->name ?? '-' }}
                        </div>
                        @if(isset($assetTransfer->fromLocation->department_name) && $assetTransfer->fromLocation->department_name)
                        <div class="flex flex-wrap gap-2 pt-1">
                            <span class="text-[11px] font-medium bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-600">
                                Lantai: {{ $assetTransfer->fromLocation->floor ?? '-' }}
                            </span>
                            <span class="text-[11px] font-medium bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-600">
                                Dept: {{ $assetTransfer->fromLocation->department_name }}
                            </span>
                        </div>
                        @endif
                    </div>

                    {{-- Lokasi Tujuan --}}
                    <div class="p-4 bg-blue-50/50 dark:bg-blue-950/20 rounded-xl border border-blue-100 dark:border-blue-900/40 space-y-2">
                        <span class="text-xs font-bold uppercase text-blue-500 tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Lokasi Tujuan (Ke)
                        </span>
                        <div class="text-base font-bold text-blue-900 dark:text-blue-300">
                            {{ $assetTransfer->toLocation->name ?? '-' }}
                        </div>
                        @if(isset($assetTransfer->toLocation->department_name) && $assetTransfer->toLocation->department_name)
                        <div class="flex flex-wrap gap-2 pt-1">
                            <span class="text-[11px] font-medium bg-white dark:bg-slate-800 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800/50">
                                Lantai: {{ $assetTransfer->toLocation->floor ?? '-' }}
                            </span>
                            <span class="text-[11px] font-medium bg-white dark:bg-slate-800 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800/50">
                                Dept: {{ $assetTransfer->toLocation->department_name }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Alasan & Catatan --}}
                <div class="space-y-4 pt-2">
                    <div>
                        <span class="block text-xs font-semibold uppercase text-slate-400 mb-1">Alasan Perpindahan</span>
                        <p class="text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl text-sm border border-slate-200/80 dark:border-slate-600 leading-relaxed">
                            {{ $assetTransfer->reason ?? 'Tidak ada alasan khusus yang dicantumkan.' }}
                        </p>
                    </div>

                    @if($assetTransfer->notes)
                    <div>
                        <span class="block text-xs font-semibold uppercase text-slate-400 mb-1">Catatan Tambahan (Notes)</span>
                        <p class="text-slate-600 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-700/30 p-3 rounded-xl text-xs border border-slate-200/60 dark:border-slate-700 italic">
                            {{ $assetTransfer->notes }}
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Parameter Informasi Tambahan --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100 dark:border-slate-700/50">
                    <div class="p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-100 dark:border-slate-700/50">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase">Tipe Mutasi</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 capitalize mt-0.5 block">
                            @if($assetTransfer->transfer_type === 'location_change')
                                Permanen (Pindah Lokasi)
                            @elseif($assetTransfer->transfer_type === 'temporary')
                                Sementara (Peminjaman)
                            @elseif($assetTransfer->transfer_type === 'return')
                                Pengembalian
                            @else
                                {{ $assetTransfer->transfer_type }}
                            @endif
                        </span>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-100 dark:border-slate-700/50">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase">Tanggal Mutasi</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5 block">
                            {{ $assetTransfer->transfer_date ? \Carbon\Carbon::parse($assetTransfer->transfer_date)->format('d M Y') : '-' }}
                        </span>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-100 dark:border-slate-700/50 col-span-2 sm:col-span-1">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase">Dibuat Oleh</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5 block truncate">
                            {{ $assetTransfer->user->name ?? $assetTransfer->creator->name ?? 'System' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Penanggung Jawab / Pemohon --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3 mb-4">
                    Petugas / Pembuat Pengajuan
                </h2>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                        {{ strtoupper(substr($assetTransfer->user->name ?? $assetTransfer->creator->name ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 dark:text-white">
                            {{ $assetTransfer->user->name ?? $assetTransfer->creator->name ?? 'Administrator' }}
                        </h4>
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                            {{ $assetTransfer->user->email ?? $assetTransfer->creator->email ?? 'Petugas Operational' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column (Asset Info) --}}
        <div class="space-y-6">

            {{-- Detail Aset --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                <h3 class="text-md font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">
                    Aset Terkait
                </h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nama Aset</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200">
                            {{ $assetTransfer->asset->name ?? 'Aset Tidak Ditemukan' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Kode Aset</span>
                        <span class="text-xs font-mono font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded inline-block mt-0.5">
                            {{ $assetTransfer->asset->asset_code ?? $assetTransfer->asset->code ?? '-' }}
                        </span>
                    </div>
                    @if(isset($assetTransfer->asset->category))
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Kategori</span>
                        <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                            {{ $assetTransfer->asset->category->name ?? '-' }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Histori / Ringkasan Aset --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                <h3 class="text-md font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">
                    Informasi Status
                </h3>
                
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                        <span>Sifat Mutasi</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200">
                            {{ $assetTransfer->transfer_type === 'temporary' ? 'Peminjaman' : 'Permanen' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                        <span>Persetujuan</span>
                        <span class="font-medium text-slate-800 dark:text-slate-200 capitalize">
                            {{ $assetTransfer->status }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection