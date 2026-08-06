@extends('layouts.admin')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.15] dark:opacity-[0.08]"
            style="background-image: radial-gradient(#e2e8f0 1.5px, transparent 1.5px); background-size: 40px 40px;"></div>
        <div class="text-slate-900 dark:text-white opacity-[0.03] dark:opacity-[0.02] transform scale-100 sm:scale-125 md:scale-150 p-4">
            <svg class="w-72 h-72 sm:w-96 sm:h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="0.5" stroke-dasharray="4 4" />
                <circle cx="12" cy="12" r="7.5" stroke-width="0.75" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M12 2v4m0 12v4M2 12h4m12 0h4" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M12 8.5c-1.5 0-3.5 1-3.5 3.5 0 2.5 2 4 3.5 4.5 1.5-.5 3.5-2 3.5-4.5 0-2.5-2-3.5-3.5-3.5z" />
            </svg>
        </div>
    </div>

    <!-- Efek Kilau Gradasi Background -->
    <div class="absolute top-0 left-1/4 w-72 sm:w-[500px] h-72 sm:h-[500px] bg-blue-400/10 dark:bg-blue-500/5 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <div class="relative z-10">

        {{-- Header Action --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100">Mutasi & Perpindahan Aset</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola histori perpindahan, peminjaman sementara, dan perubahan lokasi aset.</p>
            </div>
            @can('transfer.create')
            <button onclick="openCreateModal()" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-semibold rounded-lg shadow transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Mutasi Baru
            </button>
            @endcan
        </div>

        {{-- 🌟 SECTION FILTER LENGKAP --}}
        <div class="mb-6 p-4 sm:p-5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <form action="{{ request()->url() }}" method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-3 sm:gap-4">
                    {{-- Cari Nama/Kode Aset --}}
                    <div class="sm:col-span-2 lg:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Cari Aset</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau kode aset..."
                            class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>

                    {{-- Filter Tipe Mutasi --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tipe Mutasi</label>
                        <select name="transfer_type" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border bg-white">
                            <option value="">Semua Tipe</option>
                            <option value="location_change" {{ request('transfer_type') === 'location_change' ? 'selected' : '' }}>Permanen (Pindah Lokasi)</option>
                            <option value="temporary" {{ request('transfer_type') === 'temporary' ? 'selected' : '' }}>Sementara (Peminjaman)</option>
                            <option value="return" {{ request('transfer_type') === 'return' ? 'selected' : '' }}>Pengembalian</option>
                        </select>
                    </div>

                    {{-- Filter Status --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Status</label>
                        <select name="status" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border bg-white">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>

                    {{-- Tanggal Selesai --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                            class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4 mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/50">
                    {{-- Lokasi Asal --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Lokasi Asal</label>
                        <select name="from_location_id" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border bg-white">
                            <option value="">Semua Lokasi Asal</option>
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('from_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} @if($loc->department_name) [Dept: {{ $loc->department_name }}] @endif
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lokasi Tujuan --}}
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Lokasi Tujuan</label>
                        <select name="to_location_id" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border bg-white">
                            <option value="">Semua Lokasi Tujuan</option>
                            @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('to_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }} @if($loc->department_name) [Dept: {{ $loc->department_name }}] @endif
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Aksi Filter --}}
                    <div class="lg:col-span-2 flex items-end gap-2 mt-2 sm:mt-0">
                        <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg shadow transition-all flex items-center justify-center gap-2 h-[38px]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Terapkan Filter
                        </button>
                        @if(request()->anyFilled(['search', 'transfer_type', 'status', 'start_date', 'end_date', 'from_location_id', 'to_location_id']))
                        <a href="{{ request()->url() }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition-all flex items-center justify-center h-[38px]" title="Reset Filter">
                            Reset
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- 🌟 VIEW MOBILE & TABLET (Tampilan Card untuk Layar Kecil < lg) --}}
        <div class="block lg:hidden space-y-4 mb-6">
            @forelse($transfers as $transfer)
            <div class="p-4 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700/50 space-y-3">
                <div class="flex justify-between items-start gap-2">
                    <div>
                        <div class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ $transfer->asset->name ?? 'Aset Tidak Ditemukan' }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Code: {{ $transfer->asset->asset_code ?? '-' }}</div>
                    </div>
                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full tracking-wide uppercase shrink-0
                        {{ $transfer->status === 'draft' ? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' : '' }}
                        {{ $transfer->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' : '' }}
                        {{ $transfer->status === 'rejected' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' : '' }}">
                        {{ $transfer->status }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-100 dark:border-slate-700/40">
                    <span class="px-2 py-0.5 text-[10px] font-medium rounded-full 
                        {{ $transfer->transfer_type === 'location_change' ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400' : '' }}
                        {{ $transfer->transfer_type === 'temporary' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400' : '' }}
                        {{ $transfer->transfer_type === 'return' ? 'bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400' : '' }}">
                        {{ $transfer->transfer_type === 'location_change' ? 'Permanen' : ($transfer->transfer_type === 'temporary' ? 'Sementara' : 'Pengembalian') }}
                    </span>
                    <span class="text-slate-500 dark:text-slate-400 text-[11px] flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $transfer->transfer_date->format('d M Y') }}
                    </span>
                </div>

                <!-- Alur Mutasi Ringkas Mobile -->
                <div class="p-2.5 bg-slate-50 dark:bg-slate-900/50 rounded-lg text-xs space-y-1.5 border border-slate-100 dark:border-slate-700/30">
                    <div class="flex items-center gap-1 text-slate-500 dark:text-slate-400">
                        <span class="w-8 font-medium">Dari:</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $transfer->fromLocation->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                        <span class="w-8 font-medium">Ke:</span>
                        <span class="font-bold">{{ $transfer->toLocation->name ?? '-' }}</span>
                    </div>
                </div>

                @if($transfer->reason || $transfer->notes)
                <div class="text-xs text-slate-600 dark:text-slate-300">
                    <span class="font-medium">Alasan:</span> {{ $transfer->reason ?? '-' }}
                    @if($transfer->notes)
                    <div class="text-[11px] text-slate-400 italic mt-0.5">{{ $transfer->notes }}</div>
                    @endif
                </div>
                @endif

                {{-- Action Buttons Mobile --}}
                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-700/40">
                    @if($transfer->status === 'draft')
                    <form action="{{ route('admin.asset_transfers.approve', $transfer->id) }}" method="POST" onsubmit="return confirm('Setujui mutasi ini?')">
                        @csrf
                        <button type="submit" class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-lg" title="Approve">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </button>
                    </form>

                    <form action="{{ route('admin.asset_transfers.reject', $transfer->id) }}" method="POST" onsubmit="return confirm('Tolak mutasi ini?')">
                        @csrf
                        <button type="submit" class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-lg" title="Reject">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </form>

                    <button onclick="openEditModal({{ json_encode($transfer) }})" class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>

                    <form action="{{ route('admin.asset_transfers.destroy', $transfer->id) }}" method="POST" onsubmit="return confirm('Hapus draft mutasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-lg" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                    @else
                    <span class="text-xs text-slate-400 italic">No Action</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-6 bg-white dark:bg-slate-800 rounded-xl text-center text-slate-400 italic text-xs">
                Belum ada data mutasi aset.
            </div>
            @endforelse
        </div>

        {{-- 🌟 VIEW DESKTOP (Tabel Tradisional untuk Layar Large lg:) --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100/70 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700/50 text-slate-400 text-[11px] font-semibold uppercase tracking-wider">
                            <th class="px-6 py-3.5">Informasi Aset</th>
                            <th class="px-6 py-3.5">Tipe & Tanggal</th>
                            <th class="px-6 py-3.5">Alur Perpindahan</th>
                            <th class="px-6 py-3.5">Alasan / Catatan</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            @canany(['asset-transfers.approve', 'asset-transfers.edit', 'asset-transfers.delete'])
                            <th class="px-6 py-4 text-center">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-sm text-slate-600 dark:text-slate-400">
                        @forelse($transfers as $transfer)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/20 transition-colors">
                            {{-- Informasi Aset --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $transfer->asset->name ?? 'Aset Tidak Ditemukan' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">Code: {{ $transfer->asset->asset_code ?? '-' }}</div>
                            </td>

                            {{-- Tipe & Tanggal --}}
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full 
                                    {{ $transfer->transfer_type === 'location_change' ? 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400' : '' }}
                                    {{ $transfer->transfer_type === 'temporary' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400' : '' }}
                                    {{ $transfer->transfer_type === 'return' ? 'bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400' : '' }}">
                                    {{ $transfer->transfer_type === 'location_change' ? 'Permanen' : ($transfer->transfer_type === 'temporary' ? 'Sementara' : 'Pengembalian') }}
                                </span>
                                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $transfer->transfer_date->format('d M Y') }}
                                </div>
                            </td>

                            {{-- Alur Perpindahan --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <div class="text-xs text-slate-400 flex flex-wrap items-center gap-1">
                                        <span class="w-10 font-medium text-slate-500">Dari:</span>
                                        <span class="text-slate-600 dark:text-slate-300 font-semibold bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">
                                            {{ $transfer->fromLocation->name ?? '-' }}
                                        </span>
                                        @if(isset($transfer->fromLocation->department_name) && $transfer->fromLocation->department_name)
                                        <span class="text-slate-400 text-[11px] bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700 px-1.5 py-0.5 rounded">
                                            Lantai: {{ $transfer->fromLocation->floor ?? '-' }}
                                        </span>
                                        <span class="text-slate-400 text-[11px] bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700 px-1.5 py-0.5 rounded">
                                            Dept: {{ $transfer->fromLocation->department_name ?? '-' }}
                                        </span>
                                        @endif
                                    </div>

                                    <div class="text-xs text-slate-400 flex flex-wrap items-center gap-1">
                                        <span class="w-10 font-medium text-blue-500">Ke:</span>
                                        <span class="text-blue-700 dark:text-blue-400 font-bold bg-blue-50 dark:bg-blue-950/40 px-1.5 py-0.5 rounded">
                                            {{ $transfer->toLocation->name ?? '-' }}
                                        </span>
                                        @if(isset($transfer->toLocation->department_name) && $transfer->toLocation->department_name)
                                        <span class="text-slate-400 text-[11px] bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700 px-1.5 py-0.5 rounded">
                                            Lantai: {{ $transfer->toLocation->floor ?? '-' }}
                                        </span>
                                        <span class="text-blue-600 dark:text-blue-400 text-[11px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100/50 dark:border-blue-900/40 px-1.5 py-0.5 rounded font-medium">
                                            Dept: {{ $transfer->toLocation->department_name ?? '-' }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Alasan --}}
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-700 dark:text-slate-300 font-medium line-clamp-1">{{ $transfer->reason ?? '-' }}</div>
                                @if($transfer->notes)
                                <div class="text-[11px] text-slate-400 italic mt-0.5">{{ $transfer->notes }}</div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-full tracking-wide uppercase
                                    {{ $transfer->status === 'draft' ? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' : '' }}
                                    {{ $transfer->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' : '' }}
                                    {{ $transfer->status === 'rejected' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' : '' }}">
                                    {{ $transfer->status }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            @canany(['asset-transfers.approve', 'asset-transfers.edit', 'asset-transfers.delete'])
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if($transfer->status === 'draft')

                                    {{-- Button Approve --}}
                                    @can('asset-transfers.approve')
                                    <form action="{{ route('admin.asset_transfers.approve', $transfer->id) }}" method="POST" onsubmit="return confirm('Setujui mutasi ini?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 rounded transition-all" title="Approve">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Button Reject --}}
                                    <form action="{{ route('admin.asset_transfers.reject', $transfer->id) }}" method="POST" onsubmit="return confirm('Tolak mutasi ini?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-100 rounded transition-all" title="Reject">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan

                                    {{-- Button Edit --}}
                                    @can('asset-transfers.edit')
                                    <button onclick="openEditModal({{ json_encode($transfer) }})" class="p-1.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 hover:bg-blue-100 rounded transition-all" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endcan

                                    {{-- Button Delete --}}
                                    @can('asset-transfers.delete')
                                    <form action="{{ route('admin.asset_transfers.destroy', $transfer->id) }}" method="POST" onsubmit="return confirm('Hapus draft mutasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 rounded transition-all" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan

                                    @else
                                    <span class="text-xs text-slate-400 italic">No Action</span>
                                    @endif
                                </div>
                            </td>
                            @endcanany
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">Belum ada data mutasi aset.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($transfers->hasPages())
        <div class="mt-4 p-4 border-t border-slate-100 dark:border-slate-700/50 bg-white dark:bg-slate-800 rounded-xl">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    {{-- MODAL FORM RESPONSIVE --}}
    <div id="modal-transfer" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex justify-center items-center p-3 sm:p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700/60 w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800/50 flex justify-between items-center shrink-0">
                <h3 id="modal-title" class="font-bold text-slate-800 dark:text-slate-100 text-sm">Formulir Mutasi Aset</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-2xl leading-none">&times;</button>
            </div>

            <form id="transfer-form" action="{{ route('admin.asset_transfers.store') }}" method="POST" class="p-5 space-y-4 overflow-y-auto">
                @csrf
                <div id="method-container"></div>

                {{-- Pilih Aset --}}
                <div class="relative">
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Aset Yang Dimutasi *</label>
                    <input type="hidden" name="asset_id" id="form-asset_id" required>

                    <div class="relative">
                        <input type="text" id="asset-search-input" readonly placeholder="-- Pilih & Cari Aset --"
                            class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 bg-white p-2 border cursor-pointer pr-8"
                            onclick="toggleAssetDropdown()">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>

                    <div id="asset-dropdown-list" class="hidden absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-2 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50 dark:bg-slate-800">
                            <input type="text" id="asset-inner-search" placeholder="Ketik nama atau kode aset..."
                                class="w-full text-xs rounded border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-1.5 border"
                                oninput="filterAssetList()">
                        </div>
                        <div id="asset-options-container" class="max-h-48 overflow-y-auto divide-y divide-slate-50 dark:divide-slate-800">
                            <div class="px-3 py-2 text-xs text-slate-400 italic" id="no-asset-found" style="display: none;">Aset tidak ditemukan...</div>

                            @foreach($assets as $asset)
                            @php
                            $displayName = $asset->name . ($asset->asset_code ? ' ['.$asset->asset_code.']' : '');
                            @endphp
                            <button type="button"
                                class="asset-option-item w-full text-left px-3 py-2 text-xs hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 transition-colors flex justify-between items-center"
                                data-id="{{ $asset->id }}"
                                data-name="{{ $asset->name }}"
                                data-search="{{ strtolower($asset->name . ' ' . ($asset->asset_code ?? '')) }}"
                                onclick="selectAsset('{{ $asset->id }}', '{{ $displayName }}')">
                                <span class="font-medium truncate max-w-[200px] sm:max-w-xs">{{ $asset->name }}</span>
                                <span class="text-[10px] text-slate-500 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded font-mono shrink-0 ml-2">
                                    {{ $asset->asset_code ?? '-' }}
                                </span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Tipe Transfer --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Tipe Mutasi *</label>
                        <select name="transfer_type" id="form-transfer_type" required class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 bg-white p-2 border">
                            <option value="location_change">Permanen (Pindah Lokasi)</option>
                            <option value="temporary">Sementara (Peminjaman)</option>
                            <option value="return">Pengembalian</option>
                        </select>
                    </div>
                    {{-- Tanggal --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mutasi *</label>
                        <input type="date" name="transfer_date" id="form-transfer_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>
                </div>

                {{-- Lokasi Tujuan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Lokasi Tujuan *</label>
                    <select name="to_location_id" id="form-to_location_id" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 bg-white p-2 border">
                        <option value="">-- Pilih Lokasi Target --</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }} @if($loc->department_name) [Dept: {{ $loc->department_name }}] @endif</option>
                        @endforeach
                    </select>
                </div>

                {{-- Departemen Tujuan & Penanggung Jawab Tujuan (Opsional) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Departemen Tujuan</label>
                        <select name="to_department_id" id="form-to_department_id" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 bg-white p-2 border">
                            <option value="">-- Pilih Departemen --</option>
                            @isset($departments)
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Penanggung Jawab Tujuan (User)</label>
                        <select name="to_user_id" id="form-to_user_id" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 bg-white p-2 border">
                            <option value="">-- Pilih User / Penanggung Jawab --</option>
                            @isset($users)
                            @foreach($users as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>
                </div>

                {{-- No Referensi & No Dokumen --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Dokumen / Berita Acara</label>
                        <input type="text" name="document_number" id="form-document_number" placeholder="Contoh: BA-001/MTS/..." class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>
                </div>

                {{-- Alasan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Alasan Perpindahan</label>
                    <input type="text" name="reason" id="form-reason" placeholder="Contoh: Kebutuhan operasional pos baru" class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border">
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Catatan Tambahan (Notes)</label>
                    <textarea name="notes" id="form-notes" rows="2" placeholder="Tulis instruksi atau kondisi fisik barang..." class="w-full text-xs rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 p-2 border"></textarea>
                </div>

                {{-- Hidden / Additional input for Entry Method --}}
                <input type="hidden" name="entry_method" value="manual">

                {{-- Footer Modal --}}
                <div class="pt-3 border-t border-slate-100 dark:border-slate-700/60 flex justify-end gap-2 shrink-0">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition-all">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow transition-all">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-transfer');
    const form = document.getElementById('transfer-form');
    const modalTitle = document.getElementById('modal-title');
    const methodContainer = document.getElementById('method-container');

    // Element Dropdown Aset Custom
    const assetSearchInput = document.getElementById('asset-search-input');
    const assetDropdownList = document.getElementById('asset-dropdown-list');
    const assetInnerSearch = document.getElementById('asset-inner-search');
    const hiddenAssetId = document.getElementById('form-asset_id');
    const assetOptions = document.querySelectorAll('.asset-option-item');
    const noAssetFound = document.getElementById('no-asset-found');

    // 1. Toggle Buka/Tutup Dropdown Aset
    function toggleAssetDropdown() {
        assetDropdownList.classList.toggle('hidden');
        if (!assetDropdownList.classList.contains('hidden')) {
            assetInnerSearch.focus();
        }
    }

    // Fungsi pencarian/filter di dalam dropdown custom
    function filterAssetList() {
        const input = assetInnerSearch.value.toLowerCase();
        let anyFound = false;

        assetOptions.forEach(item => {
            const searchString = item.getAttribute('data-search');
            if (searchString.includes(input)) {
                item.style.display = 'flex';
                anyFound = true;
            } else {
                item.style.display = 'none';
            }
        });

        noAssetFound.style.display = anyFound ? 'none' : 'block';
    }

    // Fungsi ketika salah satu aset dipilih
    function selectAsset(id, displayName) {
        hiddenAssetId.value = id;
        assetSearchInput.value = displayName;
        assetDropdownList.classList.add('hidden');
    }

    // Menutup dropdown otomatis jika klik di luar area custom select dropdown
    document.addEventListener('click', function(event) {
        const isClickInside = event.target.closest('.relative');
        if (!isClickInside) {
            assetDropdownList.classList.add('hidden');
        }
    });

    // === LOGIKA UTAMA MODAL CRUD ===

    function openCreateModal() {
        modalTitle.innerText = "Buat Mutasi Baru";
        form.action = "{{ route('admin.asset_transfers.store') }}";
        methodContainer.innerHTML = "";

        form.reset();
        document.getElementById('form-transfer_date').value = "{{ date('Y-m-d') }}";

        // Reset Custom Dropdown Aset
        hiddenAssetId.value = "";
        assetSearchInput.value = "";
        assetInnerSearch.value = "";
        filterAssetList();

        modal.classList.remove('hidden');
    }

    function openEditModal(data) {
        modalTitle.innerText = "Edit Data Mutasi (Draft)";
        form.action = `/admin/asset_transfers/${data.id}`;
        methodContainer.innerHTML = `@method('PUT')`;

        // Mapping ke input text biasa
        document.getElementById('form-transfer_type').value = data.transfer_type;
        document.getElementById('form-transfer_date').value = data.transfer_date.split('T')[0];
        document.getElementById('form-to_location_id').value = data.to_location_id;
        document.getElementById('form-reason').value = data.reason || '';
        document.getElementById('form-notes').value = data.notes || '';

        // Mapping Khusus untuk Searchable Dropdown Aset
        hiddenAssetId.value = data.asset_id;

        // Cari text nama aset berdasarkan asset_id yang dikirim untuk ditampilkan di display box
        const selectedAssetOption = Array.from(assetOptions).find(opt => opt.getAttribute('data-id') == data.asset_id);
        if (selectedAssetOption) {
            assetSearchInput.value = selectedAssetOption.getAttribute('data-name');
        } else {
            assetSearchInput.value = "-- Aset Tidak Ditemukan --";
        }

        assetInnerSearch.value = "";
        filterAssetList();

        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        assetDropdownList.classList.add('hidden');
    }
</script>

@if ($errors->any())
<script>
    // Otomatis buka modal saat validasi server gagal
    document.addEventListener('DOMContentLoaded', function() {
        openModal('create');
    });
</script>
@endif

@endsection