@extends('layouts.admin')

@section('title', 'Manajemen Asset Maintenance')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">


    <!-- Background Watermark & Glow: Asset Maintenance -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <!-- Grid Dot Pattern Overlay -->
        <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.04]"
            style="background-image: radial-gradient(#94a3b8 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>

        <!-- Central SVG Watermark Icon (Representasi Perawatan & Perbaikan Aset) -->
        <div class="text-amber-600 dark:text-amber-400 opacity-[0.035] dark:opacity-[0.025] transform -rotate-6 scale-125 md:scale-150 p-4 transition-transform duration-700">
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <!-- Lingkaran Luar Berpola Tech Radar -->
                <circle cx="12" cy="12" r="10" stroke-width="0.5" stroke-dasharray="4 4" />
                <circle cx="12" cy="12" r="8.2" stroke-width="0.5" />

                <!-- Ikon Wrench / Kunci Pas & Baut (Maintenance) -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z" />

                <!-- Elemen Shield / Perlindungan Aset -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" stroke-dasharray="2 2" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
        </div>
    </div>

    <!-- Efek Glow Light (Nuansa Warm / Maintenance) -->
    <div class="absolute top-0 left-1/3 w-72 sm:w-[500px] h-72 sm:h-[500px] bg-amber-500/10 dark:bg-amber-500/5 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none z-0"></div>
    <div class="absolute bottom-0 right-1/4 w-60 sm:w-[400px] h-60 sm:h-[400px] bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none z-0"></div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Asset Maintenance</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola pemeliharaan, perbaikan, dan jadwal reminder aset dalam satu tempat.</p>
        </div>
        @can('maintenance.create')
        <button onclick="openFormModal('create')" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl transition shadow-lg shadow-indigo-500/20 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Maintenance
        </button>
        @endcan
    </div>

    {{-- Reminder & Overdue Warning Banner --}}
    @php
    $today = \Carbon\Carbon::today();
    $upcomingReminders = $maintenances->filter(function($m) use ($today) {
    if (!$m->due_date || $m->status === 'completed' || $m->status === 'cancelled') return false;
    $dueDate = \Carbon\Carbon::parse($m->due_date);
    $daysLeft = $today->diffInDays($dueDate, false);
    return $daysLeft <= ($m->reminder_days_before ?? 3);
        });
        @endphp

        @if($upcomingReminders->count() > 0)
        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl flex items-start gap-3">
            <div class="p-2 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-xl shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <div class="flex-1 text-sm">
                <h4 class="font-bold text-amber-900 dark:text-amber-300">Pengingat Jadwal Maintenance ({{ $upcomingReminders->count() }})</h4>
                <p class="text-amber-700 dark:text-amber-400 text-xs mt-0.5">Terdapat tugas pemeliharaan yang mendekati atau telah melewati batas jatuh tempo:</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($upcomingReminders->take(5) as $rem)
                    @php
                    $dDate = \Carbon\Carbon::parse($rem->due_date);
                    $isOverdue = $dDate->isPast() && !$dDate->isToday();
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold {{ $isOverdue ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                        <span class="font-mono">#{{ $rem->ticket_number }}</span> - {{ $rem->asset->name ?? 'Aset' }}
                        ({{ $isOverdue ? 'Terlambat ' . abs((int)$today->diffInDays($dDate, false)) . ' hr' : ($dDate->isToday() ? 'Hari Ini' : $today->diffInDays($dDate) . ' hr lagi') }})
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Filter & Search Bar --}}
        <div class="mb-6 bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
            <form method="GET" action="{{ route('admin.asset_maintenances.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Tiket, Aset, Judul..."
                    class="w-full px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">

                <select name="status" class="w-full px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Semua Status --</option>
                    <option value="reported" {{ request('status') === 'reported' ? 'selected' : '' }}>Dilaporkan</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Dijadwalkan</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Proses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <select name="type" class="w-full px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Semua Tipe --</option>
                    <option value="routine" {{ request('type') === 'routine' ? 'selected' : '' }}>Routine (Rutin)</option>
                    <option value="repair" {{ request('type') === 'repair' ? 'selected' : '' }}>Repair (Perbaikan)</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-slate-900 dark:bg-slate-700 hover:bg-slate-800 text-white font-medium text-sm rounded-xl transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'type']))
                    <a href="{{ route('admin.asset_maintenances.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200 transition flex items-center justify-center">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-4">Tiket & Aset</th>
                            <th class="px-6 py-4">Pekerjaan</th>
                            <th class="px-6 py-4">Prioritas</th>
                            <th class="px-6 py-4">Pelaksana</th>
                            <th class="px-6 py-4">Jadwal & Reminder</th>
                            <th class="px-6 py-4">Total Biaya</th>
                            <th class="px-6 py-4">Status</th>
                            {{-- Hanya muncul jika user memiliki minimal salah satu permission --}}
                            @canany(['maintenance.edit', 'maintenance.view', 'maintenance.delete'])
                            <th class="px-6 py-4 text-right">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($maintenances as $item)
                        @php
                        $dueDate = $item->due_date ? \Carbon\Carbon::parse($item->due_date) : null;
                        $isOverdue = $dueDate && $dueDate->isPast() && !$dueDate->isToday() && !in_array($item->status, ['completed', 'cancelled']);
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                            <!-- Tiket & Aset -->
                            <td class="px-6 py-4 space-y-0.5">
                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 block">#{{ $item->ticket_number }}</span>
                                <span class="font-semibold text-slate-900 dark:text-white block">{{ $item->asset->name ?? '-' }}</span>
                                <span class="text-xs text-slate-400 block font-mono">{{ $item->asset->code ?? '' }}</span>
                            </td>

                            <!-- Judul Pekerjaan -->
                            <td class="px-6 py-4 max-w-xs space-y-1">
                                <span class="font-semibold text-slate-900 dark:text-white block truncate" title="{{ $item->title }}">{{ $item->title }}</span>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold {{ $item->type === 'routine' ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400' }}">
                                        {{ ucfirst($item->type) }}
                                    </span>
                                    @if($item->frequency && $item->frequency !== 'none')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                        {{ ucfirst($item->frequency) }}
                                    </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Prioritas -->
                            <td class="px-6 py-4">
                                @php
                                $priorityClasses = [
                                'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                'medium' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'high' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                'urgent' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 font-bold animate-pulse',
                                ];
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $priorityClasses[$item->priority] ?? $priorityClasses['medium'] }}">
                                    {{ strtoupper($item->priority ?? 'medium') }}
                                </span>
                            </td>

                            <!-- Pelaksana -->
                            <td class="px-6 py-4">
                                @if($item->technician)
                                <span class="block font-medium text-slate-800 dark:text-slate-200">{{ $item->technician->name }}</span>
                                <span class="text-xs text-slate-400">Teknisi Internal</span>
                                @elseif($item->vendor_name)
                                <span class="block font-medium text-slate-800 dark:text-slate-200">{{ $item->vendor_name }}</span>
                                <span class="text-xs text-slate-400">Vendor Eksternal</span>
                                @else
                                <span class="text-slate-400 italic">-</span>
                                @endif
                            </td>

                            <!-- Tanggal & Reminder Status -->
                            <td class="px-6 py-4 font-mono text-xs space-y-1">
                                @if($dueDate)
                                <div class="flex items-center gap-1.5">
                                    <span class="{{ $isOverdue ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-700 dark:text-slate-300' }}">
                                        {{ $dueDate->format('d/m/Y') }}
                                    </span>
                                    @if($isOverdue)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300">
                                        OVERDUE
                                    </span>
                                    @endif
                                </div>
                                @else
                                <span class="text-slate-400">-</span>
                                @endif

                                @if($item->is_reminder_active)
                                <div class="flex items-center gap-1 text-[11px] text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span>H-{{ $item->reminder_days_before ?? 3 }} Reminder</span>
                                </div>
                                @endif
                            </td>

                            <!-- Total Biaya -->
                            <td class="px-6 py-4 font-mono font-semibold text-slate-800 dark:text-slate-200">
                                Rp {{ number_format($item->total_cost ?? ($item->cost_sparepart + $item->cost_service), 0, ',', '.') }}
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                $statusClasses = [
                                'reported' => 'bg-amber-50 text-amber-600 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
                                'scheduled' => 'bg-purple-50 text-purple-600 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800',
                                'in_progress' => 'bg-blue-50 text-blue-600 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800',
                                'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
                                'cancelled' => 'bg-rose-50 text-rose-600 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
                                ];
                                @endphp

                                <span class="inline-block whitespace-nowrap px-2.5 py-1 rounded-lg text-xs font-bold border {{ $statusClasses[$item->status] ?? '' }}">
                                    {{ strtoupper(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </td>

                            @canany(['maintenance.edit', 'maintenance.view', 'maintenance.delete'])
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">

                                    {{-- Quick Status Update: Proses --}}
                                    @can('maintenance.edit')
                                    @if($item->status === 'reported' || $item->status === 'scheduled')
                                    <form method="POST" action="{{ route('admin.asset_maintenances.update', $item->id) }}" class="inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="quick_update" value="1">
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit"
                                            title="Mulai Pengerjaan"
                                            class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/60 dark:text-blue-400 rounded-lg transition-all duration-150 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Quick Status Update: Selesai --}}
                                    @elseif($item->status === 'in_progress')
                                    <form method="POST" action="{{ route('admin.asset_maintenances.update', $item->id) }}" class="inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="quick_update" value="1">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit"
                                            title="Tandai Selesai"
                                            class="p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 dark:text-emerald-400 rounded-lg transition-all duration-150 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    @endcan

                                    {{-- Tombol Detail --}}
                                    @can('maintenance.view')
                                    <button onclick="openDetailModal({{ json_encode($item) }})"
                                        title="Lihat Detail"
                                        class="p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 dark:text-indigo-400 rounded-lg transition-all duration-150 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    @endcan

                                    {{-- Tombol Edit --}}
                                    @can('maintenance.edit')
                                    <button onclick="openFormModal('edit', {{ json_encode($item) }})"
                                        title="Edit Data"
                                        class="p-2 text-amber-600 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-900/60 dark:text-amber-400 rounded-lg transition-all duration-150 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    @endcan

                                    {{-- Tombol Hapus --}}
                                    @can('maintenance.delete')
                                    <button onclick="openDeleteModal('{{ route('admin.asset_maintenances.destroy', $item->id) }}', '{{ $item->ticket_number }}')"
                                        title="Hapus Data"
                                        class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-400 rounded-lg transition-all duration-150 transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    @endcan

                                </div>
                            </td>
                            @endcanany
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                                Belum ada data maintenance aset.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($maintenances->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $maintenances->links() }}
            </div>
            @endif
        </div>
</div>

{{-- MODAL FORM (Create / Edit) --}}
<div id="maintenanceModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/70 backdrop-blur-md transition-all duration-300">
    <div class="min-h-screen px-4 py-8 sm:py-12 text-center flex items-center justify-center">
        <!-- Backdrop Overlay Click to Close -->
        <div class="fixed inset-0" onclick="closeFormModal()"></div>

        <!-- Modal Box -->
        <div class="relative w-full max-w-2xl text-left align-middle transition-all transform bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden my-auto z-10 max-h-[90vh] flex flex-col">

            <!-- Header Modal (Sticky Top) -->
            <div class="flex justify-between items-center px-6 sm:px-8 py-5 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 id="modalTitle" class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Tambah Maintenance</h3>
                </div>
                <button onclick="closeFormModal()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-full transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form Element -->
            <form id="maintenanceForm" action="{{ route('admin.asset_maintenances.store') }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" id="methodField" name="_method" value="POST">

                <!-- Scrollable Form Body -->
                <div class="p-6 sm:p-8 space-y-5 overflow-y-auto flex-1">

                    <!-- Pilih Aset -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pilih Aset <span class="text-rose-500">*</span></label>
                        <select name="asset_id" id="inputAssetId" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                            <option value="">-- Pilih Aset --</option>
                            @foreach($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->code ?? 'No Code' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tipe, Prioritas & Status -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tipe <span class="text-rose-500">*</span></label>
                            <select name="type" id="inputType" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                                <option value="routine">Routine (Rutin)</option>
                                <option value="repair">Repair (Perbaikan)</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Prioritas <span class="text-rose-500">*</span></label>
                            <select name="priority" id="inputPriority" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status <span class="text-rose-500">*</span></label>
                            <select name="status" id="inputStatus" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                                <option value="reported">Dilaporkan</option>
                                <option value="scheduled">Dijadwalkan</option>
                                <option value="in_progress">Proses</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Judul Pekerjaan -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Judul Pekerjaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="inputTitle" required placeholder="Contoh: Servis Perbaikan AC / Ganti RAM"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                    </div>

                    <!-- Deskripsi -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Deskripsi / Detail Masalah</label>
                        <textarea name="description" id="inputDescription" rows="3" placeholder="Jelaskan detail pekerjaan atau kendala..."
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none resize-none"></textarea>
                    </div>

                    <!-- Teknisi, Vendor & Frekuensi -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Teknisi Internal</label>
                            <select name="technician_id" id="inputTechnicianId" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                                <option value="">-- Pilih Teknisi --</option>
                                @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Vendor Eksternal</label>
                            <input type="text" name="vendor_name" id="inputVendorName" placeholder="Nama PT / Toko Pihak Ke-3"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Frekuensi <span class="text-rose-500">*</span></label>
                            <select name="frequency" id="inputFrequency" required class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                                <option value="none">None (Sekali Jalan)</option>
                                <option value="monthly">Monthly (Bulanan)</option>
                                <option value="quarterly">Quarterly (3 Bulan)</option>
                                <option value="yearly">Yearly (Tahunan)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tanggal Jatuh Tempo, Mulai & Selesai -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Jatuh Tempo (Due Date)</label>
                            <input type="date" name="due_date" id="inputDueDate"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="inputStartDate"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tanggal Selesai</label>
                            <input type="date" name="completion_date" id="inputCompletionDate"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                        </div>
                    </div>

                    <!-- REMINDER SYSTEM SECTION -->
                    <div class="p-4 sm:p-5 bg-gradient-to-br from-indigo-50/60 to-slate-50 dark:from-indigo-950/20 dark:to-slate-800/40 rounded-2xl border border-indigo-100/80 dark:border-indigo-900/40 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                Notification & Reminder System
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_reminder_active" id="inputIsReminderActive" value="1" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Ingatkan Sebelum (Hari)</label>
                                <input type="number" name="reminder_days_before" id="inputReminderDaysBefore" min="1" max="30" placeholder="3"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Email Penerima (Opsional)</label>
                                <input type="email" name="reminder_email" id="inputReminderEmail" placeholder="teknisi@perusahaan.com"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Biaya Sparepart & Service -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Biaya Sparepart (Rp)</label>
                            <input type="number" step="0.01" name="cost_sparepart" id="inputCostSparepart" placeholder="0"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs font-mono transition-all outline-none">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Biaya Service / Jasa (Rp)</label>
                            <input type="number" step="0.01" name="cost_service" id="inputCostService" placeholder="0"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/60 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs font-mono transition-all outline-none">
                        </div>
                    </div>

                </div>

                <!-- Footer Modal (Sticky Bottom) -->
                <div class="px-6 sm:px-8 py-4 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="closeFormModal()" class="px-5 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200/60 dark:hover:bg-slate-800 rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-md shadow-indigo-500/20">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL DETAIL MAINTENANCE --}}
<div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/70 backdrop-blur-md transition-all duration-300">
    <div class="min-h-screen px-4 py-8 sm:py-12 text-center flex items-center justify-center">
        <!-- Backdrop Overlay Click to Close -->
        <div class="fixed inset-0" onclick="closeDetailModal()"></div>

        <!-- Modal Box -->
        <div class="relative w-full max-w-2xl text-left align-middle transition-all transform bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden my-auto z-10 max-h-[90vh] flex flex-col">

            <!-- Header Modal -->
            <div class="relative px-6 sm:px-8 py-5 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50 flex justify-between items-start gap-4 shrink-0">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span id="detailTicketNumber" class="text-[11px] font-mono font-bold px-2.5 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 dark:bg-indigo-950/60 dark:border-indigo-800/50 dark:text-indigo-300 rounded-lg">#TICKET</span>
                        <span id="detailStatusBadge" class="text-[11px] font-bold px-2.5 py-1 rounded-lg">--</span>
                    </div>
                    <h3 id="detailTitle" class="text-xl font-bold text-slate-900 dark:text-white tracking-tight pt-0.5">Judul Pekerjaan</h3>
                </div>

                <button onclick="closeDetailModal()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-full transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body Detail Scrollable -->
            <div class="p-6 sm:p-8 space-y-6 overflow-y-auto flex-1">

                <!-- Metadata Grid -->
                <div class="grid grid-cols-3 gap-3 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tipe</span>
                        <p id="detailType" class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">--</p>
                    </div>
                    <div class="space-y-1 border-l border-slate-200 dark:border-slate-700/60 pl-3.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Prioritas</span>
                        <p id="detailPriority" class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">--</p>
                    </div>
                    <div class="space-y-1 border-l border-slate-200 dark:border-slate-700/60 pl-3.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Frekuensi</span>
                        <p id="detailFrequency" class="text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase">--</p>
                    </div>
                </div>

                <!-- Detail Aset Card -->
                <div class="p-4 sm:p-5 bg-gradient-to-br from-indigo-50/60 to-slate-50 dark:from-indigo-950/20 dark:to-slate-800/40 border border-indigo-100/80 dark:border-indigo-900/30 rounded-2xl flex items-center gap-4">
                    <div class="p-3 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Informasi Aset</span>
                        <p id="detailAssetName" class="text-sm font-bold text-slate-900 dark:text-white">Nama Aset</p>
                        <p id="detailAssetCode" class="text-xs text-slate-500 dark:text-slate-400 font-mono">Kode Aset</p>
                    </div>
                </div>

                <!-- Deskripsi / Catatan -->
                <div class="space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        Deskripsi / Catatan Masalah
                    </label>
                    <div id="detailDescription" class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-2xl text-xs text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line min-h-[75px]">
                        -
                    </div>
                </div>

                <!-- Pelaksana & Biaya -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-2xl space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Pelaksana / Penanggung Jawab
                        </span>
                        <p id="detailExecutor" class="text-xs font-semibold text-slate-800 dark:text-slate-200 pt-0.5">-</p>
                    </div>

                    <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-2xl space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Total Biaya
                        </span>
                        <p id="detailTotalCost" class="text-sm font-mono font-bold text-emerald-600 dark:text-emerald-400 pt-0.5">Rp 0</p>
                    </div>
                </div>

                <!-- Timeline Tanggal -->
                <div class="pt-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-3">Timeline & Jadwal</label>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl">
                            <span class="block text-[10px] font-medium text-slate-400 mb-1">Mulai</span>
                            <span id="detailStartDate" class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">-</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl">
                            <span class="block text-[10px] font-medium text-slate-400 mb-1">Jatuh Tempo</span>
                            <span id="detailDueDate" class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">-</span>
                        </div>
                        <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-xl">
                            <span class="block text-[10px] font-medium text-slate-400 mb-1">Selesai</span>
                            <span id="detailCompletionDate" class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">-</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Modal -->
            <div class="px-6 sm:px-8 py-4 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end shrink-0">
                <button onclick="closeDetailModal()" class="px-6 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-all">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/70 backdrop-blur-md transition-all duration-300">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
        <!-- Backdrop Overlay Click to Close -->
        <div class="fixed inset-0" onclick="closeDeleteModal()"></div>

        <!-- Delete Modal Card -->
        <div class="inline-block w-full max-w-md text-center align-middle transition-all transform bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 space-y-5 my-8 relative z-10">

            <div class="w-14 h-14 rounded-2xl bg-rose-500/10 text-rose-500 border border-rose-500/20 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <div class="space-y-2">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Hapus Maintenance?</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed px-2">
                    Apakah Anda yakin ingin menghapus tiket <span id="deleteTicketText" class="font-mono font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50 px-2 py-0.5 rounded-md border border-rose-100 dark:border-rose-900/50"></span>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <form id="deleteForm" method="POST" class="flex items-center justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()" class="w-full py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" class="w-full py-2.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all shadow-md shadow-rose-500/20">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const formModal = document.getElementById('maintenanceModal');
    const detailModal = document.getElementById('detailModal');
    const deleteModal = document.getElementById('deleteModal');

    const maintenanceForm = document.getElementById('maintenanceForm');
    const deleteForm = document.getElementById('deleteForm');
    const methodField = document.getElementById('methodField');
    const modalTitle = document.getElementById('modalTitle');

    /**
     * Open & Reset/Populate Form Modal (Create / Edit)
     */
    function openFormModal(type, data = null) {
        formModal.classList.remove('hidden');

        if (type === 'create') {
            modalTitle.innerText = 'Tambah Maintenance Baru';
            maintenanceForm.action = "{{ route('admin.asset_maintenances.store') }}";
            methodField.value = 'POST';

            // Reset Form Fields
            maintenanceForm.reset();

            // Set Default Values
            document.getElementById('inputAssetId').value = '';
            document.getElementById('inputType').value = 'routine';
            document.getElementById('inputPriority').value = 'medium';
            document.getElementById('inputStatus').value = 'reported';
            document.getElementById('inputFrequency').value = 'none';
            document.getElementById('inputTechnicianId').value = '';
            document.getElementById('inputVendorName').value = '';
            document.getElementById('inputTitle').value = '';
            document.getElementById('inputDescription').value = '';
            document.getElementById('inputDueDate').value = '';
            document.getElementById('inputStartDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('inputCompletionDate').value = '';
            document.getElementById('inputCostSparepart').value = '';
            document.getElementById('inputCostService').value = '';

            // Default Reminder Config
            document.getElementById('inputIsReminderActive').checked = true;
            document.getElementById('inputReminderDaysBefore').value = 3;
            document.getElementById('inputReminderEmail').value = '';

        } else if (type === 'edit') {
            modalTitle.innerText = 'Edit Maintenance';
            maintenanceForm.action = `/admin/asset_maintenances/${data.id}`;
            methodField.value = 'PUT';

            // Populate Form Values
            document.getElementById('inputAssetId').value = data.asset_id || '';
            document.getElementById('inputType').value = data.type || 'routine';
            document.getElementById('inputPriority').value = data.priority || 'medium';
            document.getElementById('inputStatus').value = data.status || 'reported';
            document.getElementById('inputTitle').value = data.title || '';
            document.getElementById('inputDescription').value = data.description || '';
            document.getElementById('inputTechnicianId').value = data.technician_id || '';
            document.getElementById('inputVendorName').value = data.vendor_name || '';
            document.getElementById('inputFrequency').value = data.frequency || 'none';

            // Format Tanggal (YYYY-MM-DD)
            document.getElementById('inputDueDate').value = data.due_date ? data.due_date.split('T')[0] : '';
            document.getElementById('inputStartDate').value = data.start_date ? data.start_date.split('T')[0] : '';
            document.getElementById('inputCompletionDate').value = data.completion_date ? data.completion_date.split('T')[0] : '';

            // Costs
            document.getElementById('inputCostSparepart').value = data.cost_sparepart || 0;
            document.getElementById('inputCostService').value = data.cost_service || 0;

            // Reminder Config
            document.getElementById('inputIsReminderActive').checked = (data.is_reminder_active == 1 || data.is_reminder_active === true);
            document.getElementById('inputReminderDaysBefore').value = data.reminder_days_before || 3;
            document.getElementById('inputReminderEmail').value = data.reminder_email || '';
        }
    }

    /**
     * Close Form Modal
     */
    function closeFormModal() {
        formModal.classList.add('hidden');
    }

    /**
     * Open & Populate Detail Modal
     */
    function openDetailModal(item) {
        document.getElementById('detailTicketNumber').innerText = '#' + (item.ticket_number || 'TRX-UNKNOWN');
        document.getElementById('detailTitle').innerText = item.title || '-';

        // Dynamic Status Badge & Colors
        const statusEl = document.getElementById('detailStatusBadge');
        const statusText = (item.status || '-').replace('_', ' ').toUpperCase();
        statusEl.innerText = statusText;

        // Reset badge styles
        statusEl.className = 'text-[11px] font-bold px-2.5 py-1 rounded-lg border ';
        switch (item.status) {
            case 'completed':
                statusEl.classList.add('bg-emerald-500/10', 'text-emerald-600', 'border-emerald-500/20');
                break;
            case 'in_progress':
                statusEl.classList.add('bg-amber-500/10', 'text-amber-600', 'border-amber-500/20');
                break;
            case 'scheduled':
                statusEl.classList.add('bg-blue-500/10', 'text-blue-600', 'border-blue-500/20');
                break;
            case 'cancelled':
                statusEl.classList.add('bg-rose-500/10', 'text-rose-600', 'border-rose-500/20');
                break;
            default: // reported
                statusEl.classList.add('bg-slate-500/10', 'text-slate-600', 'border-slate-500/20');
        }

        // Asset Information
        document.getElementById('detailAssetName').innerText = item.asset ? item.asset.name : '-';
        document.getElementById('detailAssetCode').innerText = item.asset ? (item.asset.code || 'Tanpa Kode') : '-';

        // General Details
        document.getElementById('detailType').innerText = item.type || '-';
        document.getElementById('detailPriority').innerText = item.priority || '-';
        document.getElementById('detailFrequency').innerText = item.frequency || '-';
        document.getElementById('detailDescription').innerText = item.description || 'Tidak ada deskripsi.';

        // Executor Info
        let executor = '-';
        if (item.technician) {
            executor = item.technician.name + ' (Teknisi Internal)';
        } else if (item.vendor_name) {
            executor = item.vendor_name + ' (Vendor Eksternal)';
        }
        document.getElementById('detailExecutor').innerText = executor;

        // Total Cost Calculation
        const cost = item.total_cost ?? ((parseFloat(item.cost_sparepart) || 0) + (parseFloat(item.cost_service) || 0));
        document.getElementById('detailTotalCost').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(cost);

        // Dates formatting
        document.getElementById('detailStartDate').innerText = item.start_date ? formatDate(item.start_date) : '-';
        document.getElementById('detailDueDate').innerText = item.due_date ? formatDate(item.due_date) : '-';
        document.getElementById('detailCompletionDate').innerText = item.completion_date ? formatDate(item.completion_date) : '-';

        // Show Detail Modal
        detailModal.classList.remove('hidden');
    }

    /**
     * Close Detail Modal
     */
    function closeDetailModal() {
        detailModal.classList.add('hidden');
    }

    /**
     * Open Delete Modal
     */
    function openDeleteModal(url, ticketNumber) {
        deleteForm.action = url;
        document.getElementById('deleteTicketText').innerText = '#' + ticketNumber;
        deleteModal.classList.remove('hidden');
    }

    /**
     * Close Delete Modal
     */
    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
    }

    /**
     * Date Formatting Helper (YYYY-MM-DD -> DD/MM/YYYY)
     */
    function formatDate(dateString) {
        if (!dateString) return '-';
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;
        return d.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    /**
     * Global Close Handlers (Esc Key)
     */
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeFormModal();
            closeDetailModal();
            closeDeleteModal();
        }
    });
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