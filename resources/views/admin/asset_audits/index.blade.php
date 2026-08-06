@extends('layouts.admin')

@section('title', 'Audit & Stock Opname Aset')

@section('content')

<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300" x-data="auditManagement()">

    <!-- Background Watermark: Asset Audit / Stock Take -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <!-- Grid Dot Pattern Overlay -->
        <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.06]"
            style="background-image: radial-gradient(#64748b 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>

        <!-- Central SVG Watermark Icon -->
        <div class="text-teal-900 dark:text-teal-400 opacity-[0.035] dark:opacity-[0.03] transform -rotate-6 scale-100 sm:scale-125 md:scale-150 p-4 transition-transform duration-700">
            <svg class="w-72 h-72 sm:w-96 sm:h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="0.5" stroke-dasharray="4 4" />
                <circle cx="12" cy="12" r="8" stroke-width="0.5" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.65" d="M12 3L4 7.5v9l8 4.5 8-4.5v-9L12 3zM4 7.5l8 4.5 8-4.5M12 12v9" />
                <circle cx="15.5" cy="15.5" r="3.5" stroke-width="0.75" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M18 18l2.5 2.5" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.6" d="M14 15.5l1 1 2-2" />
            </svg>
        </div>
    </div>

    <!-- Efek Ambient Glow Light -->
    <div class="absolute top-0 left-1/3 w-72 sm:w-[480px] h-72 sm:h-[480px] bg-teal-500/10 dark:bg-teal-500/5 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none z-0"></div>
    <div class="absolute bottom-0 right-1/4 w-60 sm:w-[380px] h-60 sm:h-[380px] bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none z-0"></div>
    
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- Header & Bar Aksi --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 relative z-10">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Audit & Stock Opname Aset
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola sesi audit, verifikasi kondisi fisik lapangan, dan rekonsiliasi data master aset.</p>
        </div>
        <div>
            <button type="button" @click="openCreateModal()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm rounded-xl transition shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Sesi Audit Baru
            </button>
        </div>
    </div>

    {{-- Alert Flash Messages --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400 relative z-10">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm flex items-center gap-2 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-400 relative z-10">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm space-y-1 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-400 relative z-10">
        <div class="font-bold flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Terjadi kesalahan input:
        </div>
        <ul class="list-disc list-inside pl-2 space-y-0.5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Filter Bar --}}
    <div class="mb-6 bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors relative z-10">
        <form method="GET" action="{{ route('admin.asset_audits.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-5 md:col-span-6">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" name="audit_code" value="{{ request('audit_code') }}" placeholder="Cari Kode atau Judul Audit..."
                        class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition">
                </div>
            </div>

            <div class="sm:col-span-4 md:col-span-3">
                <select name="status" class="w-full px-3.5 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition">
                    <option value="">-- Semua Status --</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>DRAFT</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>IN PROGRESS</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>COMPLETED</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>CANCELLED</option>
                </select>
            </div>

            <div class="sm:col-span-3 md:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold transition-all shadow-sm active:scale-[0.98] flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>

                @if(request()->anyFilled(['audit_code', 'status']))
                <a href="{{ route('admin.asset_audits.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-medium transition flex items-center justify-center" title="Reset Filter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Data Header Audit --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden relative z-10">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100/70 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 text-xs uppercase font-semibold">
                    <th class="py-3.5 px-4">Kode & Sesi Audit</th>
                    <th class="py-3.5 px-4">Cakupan Lokasi</th>
                    <th class="py-3.5 px-4">Auditor / PIC</th>
                    <th class="py-3.5 px-4">Progres Item</th>
                    <th class="py-3.5 px-4">Status Sesi</th>
                    <th class="py-3.5 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                @forelse($audits as $item)
                @php
                $totalItems = $item->items_count ?? $item->items->count() ?? 0;
                $checkedItems = $item->items ? $item->items->where('physical_status', '!=', 'pending')->count() : 0;
                $progressPercent = $totalItems > 0 ? round(($checkedItems / $totalItems) * 100) : 0;
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                    <td class="py-3.5 px-4">
                        <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 inline-block">
                            #{{ $item->audit_code }}
                        </span>
                        <span class="block text-xs font-semibold text-slate-800 dark:text-slate-200 mt-1">{{ $item->title }}</span>
                        <span class="block text-[11px] text-slate-400 mt-0.5">Mulai: {{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('d M Y') : '-' }}</span>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="font-semibold text-slate-800 dark:text-slate-200">
                            📍 {{ $item->location->name ?? 'Beberapa Aset Spesifik' }}
                        </div>
                        <span class="text-xs text-slate-400 block mt-0.5">Scope: {{ strtoupper($item->scope_type ?? 'LOCATION') }}</span>
                    </td>
                    <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-medium">
                        {{ $item->auditor->name ?? 'Belum Ditentukan' }}
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ $checkedItems }}/{{ $totalItems }} ({{ $progressPercent }}%)</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        @php
                        $statusBadges = [
                            'draft' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                            'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                            'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                            'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400',
                        ];
                        @endphp
                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $statusBadges[$item->status] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ str_replace('_', ' ', strtoupper($item->status)) }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.asset_audits.show', $item->id) }}" class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition" title="Opname Aset (Scan / Verifikasi)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>

                            <button type="button" @click="openEditModal({{ Js::from($item) }})" class="p-1.5 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition" title="Edit Sesi Audit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            <form method="POST" action="{{ route('admin.asset_audits.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi audit ini beserta seluruh record itemnya?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition" title="Hapus Data">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-slate-400 dark:text-slate-500">
                        Belum ada sesi audit aset yang tercatat.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($audits->hasPages())
    <div class="p-4 border-t border-slate-200 dark:border-slate-700 relative z-10">
        {{ $audits->links() }}
    </div>
    @endif

    {{-- MODAL CREATE SESI AUDIT BARU --}}
    <div x-show="isCreateModalOpen" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">

        <div @click.away="closeAllModals()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white dark:bg-slate-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">

            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-700 pb-3">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Buat Sesi Audit Aset Baru</h3>
                <button type="button" @click="closeAllModals()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl font-bold leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.asset_audits.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Judul / Kegiatan Audit</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Stock Opname Gedung Utama Q3" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Metode Pemilihan Target Aset</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600 cursor-pointer">
                            <input type="radio" name="scope_type" value="location" x-model="scopeType" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Per Lokasi / Gedung</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600 cursor-pointer">
                            <input type="radio" name="scope_type" value="selected_assets" x-model="scopeType" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Pilih Aset Spesifik</span>
                        </label>
                    </div>
                </div>

                {{-- Opsi A: Berdasarkan Lokasi --}}
                <div x-show="scopeType === 'location'" class="space-y-1">
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Pilih Lokasi Aset</label>
                    <select name="location_id" :disabled="scopeType !== 'location'" :required="scopeType === 'location'" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Opsi B: Pilihan Aset Manual --}}
                <div x-show="scopeType === 'selected_assets'" class="space-y-1">
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Pilih Aset (Bisa Lebih Dari Satu)</label>
                    <select name="asset_ids[]" multiple :disabled="scopeType !== 'selected_assets'" :required="scopeType === 'selected_assets'" class="w-full h-32 px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->asset_code ?? $asset->code }} - {{ $asset->name }}</option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-400">Tahan tombol Ctrl (Windows) atau Cmd (Mac) untuk memilih beberapa aset.</span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Auditor / Penanggung Jawab</label>
                        <select name="auditor_id" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Pilih Auditor --</option>
                            @foreach($auditors as $user)
                            <option value="{{ $user->id }}" {{ old('auditor_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Tanggal Mulai Audit</label>
                        <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="closeAllModals()" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-500 transition shadow-md shadow-indigo-600/20">Generate Sesi Audit</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT HEADER SESI AUDIT --}}
    <div x-show="isEditModalOpen" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">

        <div @click.away="closeAllModals()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">

            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-700 pb-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Sesi Audit</h3>
                    <span class="text-xs text-indigo-500 font-mono font-semibold" x-text="'#' + editForm.audit_code"></span>
                </div>
                <button type="button" @click="closeAllModals()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl font-bold leading-none">&times;</button>
            </div>

            <form :action="editActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Judul Audit</label>
                    <input type="text" name="title" x-model="editForm.title" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Auditor / PIC</label>
                    <select name="auditor_id" x-model="editForm.auditor_id" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @foreach($auditors as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Status Sesi Audit</label>
                    <select name="status" x-model="editForm.status" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="draft">DRAFT</option>
                        <option value="in_progress">IN PROGRESS</option>
                        <option value="completed">COMPLETED (Selesai & Rekonsiliasi Master)</option>
                        <option value="cancelled">CANCELLED</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Catatan Auditor</label>
                    <textarea name="notes" x-model="editForm.notes" rows="3" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="closeAllModals()" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-500 transition shadow-md shadow-indigo-600/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- Alpine.js Controller --}}
<script>
    function auditManagement() {
        return {
            isCreateModalOpen: @json($errors->any()),
            isEditModalOpen: false,
            scopeType: @json(old("scope_type", "location")),
            editActionUrl: '',
            updateRouteTemplate: @json(route("admin.asset_audits.update", ":id")),
            editForm: {
                id: '',
                audit_code: '',
                title: '',
                auditor_id: '',
                status: 'draft',
                notes: ''
            },

            openCreateModal() {
                this.isCreateModalOpen = true;
            },

            openEditModal(item) {
                this.editForm = {
                    id: item.id || '',
                    audit_code: item.audit_code || item.id || '',
                    title: item.title || '',
                    auditor_id: item.auditor_id || '',
                    status: item.status || 'draft',
                    notes: item.notes || ''
                };
                this.editActionUrl = this.updateRouteTemplate.replace(':id', item.id);
                this.isEditModalOpen = true;
            },

            closeAllModals() {
                this.isCreateModalOpen = false;
                this.isEditModalOpen = false;
            }
        }
    }
</script>
@endsection