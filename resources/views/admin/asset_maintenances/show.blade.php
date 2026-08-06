@extends('layouts.admin')

@section('title', 'Detail Maintenance - #' . $assetMaintenance->ticket_number)

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6">

    {{-- Top Bar & Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                    #{{ $assetMaintenance->ticket_number }}
                </span>
                <span class="text-xs text-slate-400">
                    Dibuat: {{ $assetMaintenance->created_at->format('d M Y, H:i') }}
                </span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $assetMaintenance->title }}</h1>
        </div>
        <a href="{{ route('admin.asset_maintenances.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-medium text-sm rounded-xl transition flex items-center gap-2">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold block">Status Pekerjaan Saat Ini</span>
                <div class="flex items-center gap-2 mt-1">
                    @php
                        $statusBadges = [
                            'reported' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                            'scheduled' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                            'in_progress' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                            'completed' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                            'cancelled' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-lg text-xs font-bold border {{ $statusBadges[$assetMaintenance->status] ?? 'bg-slate-700 text-slate-300' }}">
                        {{ strtoupper(str_replace('_', ' ', $assetMaintenance->status)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Dynamic Quick Action Buttons --}}
        <div class="flex items-center gap-3 w-full md:w-auto">
            @if($assetMaintenance->status === 'reported' || $assetMaintenance->status === 'scheduled')
                {{-- Button: Proses Pekerjaan --}}
                <form method="POST" action="{{ route('admin.asset_maintenances.update', $assetMaintenance->id) }}" class="w-full md:w-auto">
                    @csrf 
                    @method('PUT')
                    <input type="hidden" name="quick_update" value="1">
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit" onclick="return confirm('Mulai pengerjaan tiket maintenance ini?')" class="w-full md:w-auto px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm rounded-xl transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        </svg>
                        Mulai Pengerjaan
                    </button>
                </form>
            @elseif($assetMaintenance->status === 'in_progress')
                {{-- Button: Tandai Selesai --}}
                <form method="POST" action="{{ route('admin.asset_maintenances.update', $assetMaintenance->id) }}" class="w-full md:w-auto">
                    @csrf 
                    @method('PUT')
                    <input type="hidden" name="quick_update" value="1">
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" onclick="return confirm('Tandai pekerjaan ini sebagai selesai?')" class="w-full md:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-xl transition shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Tandai Selesai
                    </button>
                </form>
            @elseif($assetMaintenance->status === 'completed')
                <div class="flex items-center gap-2 text-emerald-400 text-sm font-semibold bg-emerald-500/10 px-4 py-2.5 rounded-xl border border-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pekerjaan Telah Rampung
                </div>
            @endif

            @if($assetMaintenance->status !== 'completed' && $assetMaintenance->status !== 'cancelled')
                {{-- Button Option: Batalkan --}}
                <form method="POST" action="{{ route('admin.asset_maintenances.update', $assetMaintenance->id) }}" class="inline">
                    @csrf 
                    @method('PUT')
                    <input type="hidden" name="quick_update" value="1">
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin membatalkan tiket ini?')" class="px-4 py-3 bg-slate-800 hover:bg-rose-900/50 text-slate-300 hover:text-rose-300 text-sm font-medium rounded-xl transition border border-slate-700">
                        Batalkan Tiket
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Grid Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left / Main Column --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Detail Informasi Maintenance --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">
                    Informasi Pemeliharaan
                </h2>

                <div>
                    <span class="block text-xs font-semibold uppercase text-slate-400 mb-1">Deskripsi Kendala & Tugas</span>
                    <p class="text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl text-sm border border-slate-200/80 dark:border-slate-600 leading-relaxed whitespace-pre-line">
                        {{ $assetMaintenance->description ?? 'Tidak ada deskripsi detail yang dicantumkan.' }}
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
                    <div class="p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-100 dark:border-slate-700/50">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase">Tipe</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 capitalize mt-0.5 block">
                            {{ $assetMaintenance->type }}
                        </span>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-100 dark:border-slate-700/50">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase">Prioritas</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 capitalize mt-0.5 block">
                            {{ $assetMaintenance->priority }}
                        </span>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-100 dark:border-slate-700/50">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase">Tanggal Mulai</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5 block">
                            {{ $assetMaintenance->start_date ? \Carbon\Carbon::parse($assetMaintenance->start_date)->format('d/m/Y') : '-' }}
                        </span>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-100 dark:border-slate-700/50">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase">Tanggal Selesai</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5 block">
                            {{ $assetMaintenance->completion_date ? \Carbon\Carbon::parse($assetMaintenance->completion_date)->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Pelaksana / Teknisi --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3 mb-4">
                    Penanggung Jawab / Pelaksana
                </h2>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                        {{ strtoupper(substr($assetMaintenance->technician->name ?? $assetMaintenance->vendor_name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        @if($assetMaintenance->technician)
                            <h4 class="font-bold text-slate-900 dark:text-white">{{ $assetMaintenance->technician->name }}</h4>
                            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">Teknisi Internal</p>
                        @elseif($assetMaintenance->vendor_name)
                            <h4 class="font-bold text-slate-900 dark:text-white">{{ $assetMaintenance->vendor_name }}</h4>
                            <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Vendor Eksternal</p>
                        @else
                            <p class="text-sm text-slate-400 italic">Belum ada pelaksana yang ditugaskan.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column (Asset Info & Financials) --}}
        <div class="space-y-6">

            {{-- Detail Aset --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                <h3 class="text-md font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">
                    Aset Terkait
                </h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Nama Aset</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $assetMaintenance->asset->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">Kode Aset</span>
                        <span class="text-xs font-mono font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded inline-block mt-0.5">
                            {{ $assetMaintenance->asset->code ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Rincian Biaya --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                <h3 class="text-md font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">
                    Rincian Biaya
                </h3>
                
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                        <span>Biaya Sparepart</span>
                        <span class="font-mono font-medium text-slate-800 dark:text-slate-200">
                            Rp {{ number_format($assetMaintenance->cost_sparepart ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                        <span>Biaya Jasa</span>
                        <span class="font-mono font-medium text-slate-800 dark:text-slate-200">
                            Rp {{ number_format($assetMaintenance->cost_service ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-slate-100 dark:border-slate-700 font-bold text-slate-900 dark:text-white text-base">
                        <span>Total Biaya</span>
                        <span class="font-mono text-indigo-600 dark:text-indigo-400">
                            Rp {{ number_format($assetMaintenance->total_cost ?? (($assetMaintenance->cost_sparepart ?? 0) + ($assetMaintenance->cost_service ?? 0)), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection