@extends('layouts.admin')

@section('title', 'Detail & Exec Opname - ' . $audit->audit_code)

@section('content')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<div class="p-6 max-w-7xl mx-auto space-y-6" x-data="auditExecution()">
    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.asset_audits.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Sesi Audit
        </a>

        <span class="px-3 py-1 rounded-full text-xs font-bold font-mono uppercase bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
            Status Sesi: {{ str_replace('_', ' ', $audit->status) }}
        </span>
    </div>

    {{-- Ringkasan Sesi Audit (Header) --}}
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-700 pb-4">
            <div>
                <span class="text-xs font-bold font-mono text-indigo-600 dark:text-indigo-400">#{{ $audit->audit_code }}</span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white mt-0.5">{{ $audit->title }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Auditor: <strong class="text-slate-700 dark:text-slate-300">{{ $audit->auditor->name ?? 'N/A' }}</strong> |
                    Lokasi Acuan: <strong class="text-slate-700 dark:text-slate-300">{{ $audit->location->name ?? 'Multiple Scope' }}</strong>
                </p>
            </div>

            {{-- Metric Stats --}}
            @php
            $total = $audit->items->count();
            $found = $audit->items->where('physical_status', 'found')->count();
            $missing = $audit->items->where('physical_status', 'missing')->count();
            $damaged = $audit->items->where('physical_status', 'damaged')->count();
            $pending = $audit->items->where('physical_status', 'pending')->count();
            $progressPercent = $total > 0 ? round((($total - $pending) / $total) * 100) : 0;
            @endphp
            <div class="flex items-center gap-3">
                <div class="text-center px-3 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-700">
                    <span class="block text-xs text-slate-400">Total</span>
                    <span class="text-lg font-bold text-slate-800 dark:text-white">{{ $total }}</span>
                </div>
                <div class="text-center px-3 py-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl border border-emerald-200 dark:border-emerald-800">
                    <span class="block text-xs text-emerald-600 dark:text-emerald-400">Sesuai</span>
                    <span class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ $found }}</span>
                </div>
                <div class="text-center px-3 py-2 bg-rose-50 dark:bg-rose-900/30 rounded-xl border border-rose-200 dark:border-rose-800">
                    <span class="block text-xs text-rose-600 dark:text-rose-400">Hilang</span>
                    <span class="text-lg font-bold text-rose-700 dark:text-rose-300">{{ $missing }}</span>
                </div>
                <div class="text-center px-3 py-2 bg-amber-50 dark:bg-amber-900/30 rounded-xl border border-amber-200 dark:border-amber-800">
                    <span class="block text-xs text-amber-600 dark:text-amber-400">Rusak</span>
                    <span class="text-lg font-bold text-amber-700 dark:text-amber-300">{{ $damaged }}</span>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div>
            <div class="flex justify-between text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                <span>Progres Audit Fisik</span>
                <span>{{ $total - $pending }} dari {{ $total }} Aset Terverifikasi ({{ $progressPercent }}%)</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
            </div>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- Filter & Fast Scan Barcode Input --}}
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
        {{-- Scan Quick Input --}}
        {{-- Scan Quick Input --}}
        <form method="POST" action="{{ route('admin.asset_audits.scan', $audit->audit_code) }}" class="w-full sm:w-auto flex items-center gap-2">
            @csrf
            <div class="relative w-full sm:w-80">
                {{-- Ubah name menjadi asset_code agar sesuai dengan input controller di bawah --}}
                <input type="text" name="asset_code" autofocus placeholder="Scan Barcode / Kode Aset..." class="w-full pl-9 pr-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold text-sm rounded-xl hover:bg-indigo-500 transition shrink-0">
                Submit Scan
            </button>
        </form>

        {{-- Quick Filter Status --}}
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-2 sm:pb-0">
            <button type="button" @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-800' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">Semua ({{ $total }})</button>
            <button type="button" @click="filterStatus = 'pending'" :class="filterStatus === 'pending' ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-800' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">Belum ({{ $pending }})</button>
            <button type="button" @click="filterStatus = 'found'" :class="filterStatus === 'found' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">Ada ({{ $found }})</button>
            <button type="button" @click="filterStatus = 'missing'" :class="filterStatus === 'missing' ? 'bg-rose-600 text-white' : 'bg-rose-50 dark:bg-rose-900/30 text-rose-600'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition">Hilang ({{ $missing }})</button>
        </div>
    </div>

    {{-- Tabel Item Opname Aset --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/70 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 text-xs uppercase font-semibold">
                        <th class="py-3.5 px-4">Kode & Nama Aset</th>
                        <th class="py-3.5 px-4">Lokasi Acuan Sistem</th>
                        <th class="py-3.5 px-4">Status Hasil Opname</th>
                        <th class="py-3.5 px-4">Keterangan / Catatan</th>
                        <th class="py-3.5 px-4 text-center">Update Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    @forelse($audit->items as $auditItem)
                    <tr x-show="filterStatus === 'all' || filterStatus === '{{ $auditItem->physical_status }}'" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                        <td class="py-3.5 px-4">
                            <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400 block">
                                {{ $auditItem->asset->asset_code ?? $auditItem->asset->code ?? '-' }}
                            </span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                {{ $auditItem->asset->name ?? 'Aset Tidak Ditemukan' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400 text-xs">
                            📍 {{ $auditItem->asset->location->name ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                            $statusStyles = [
                            'pending' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
                            'found' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                            'missing' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400',
                            'damaged' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                            'transferred' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                            ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase {{ $statusStyles[$auditItem->physical_status] ?? 'bg-slate-100' }}">
                                {{ $auditItem->physical_status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-xs text-slate-500 dark:text-slate-400">
                            {{ $auditItem->notes ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <button type="button" @click="openVerifyModal({{ Js::from($auditItem) }})" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-semibold transition">
                                Update Status
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-slate-400">
                            Belum ada item aset yang dimasukkan ke dalam sesi audit ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL VERIFIKASI ITEM AUDIT --}}
    <div x-show="isVerifyModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">

        <div @click.away="isVerifyModalOpen = false"
            class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">

            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-700 pb-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Verifikasi Item Aset</h3>
                <button type="button" @click="isVerifyModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
            </div>

            <form :action="verifyActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Status Fisik Aset</label>
                    <select name="physical_status" x-model="selectedItem.physical_status" required class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="found">ADA & SESUAI (Found)</option>
                        <option value="missing">HILANG (Missing)</option>
                        <option value="damaged">RUSAK (Damaged)</option>
                        <option value="transferred">BERPINDAH LOKASI (Transferred)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400 mb-1">Catatan Auditor</label>
                    <textarea name="notes" x-model="selectedItem.notes" rows="3" placeholder="Contoh: Posisi aset tergeser ke lantai 2..." class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-xl text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="isVerifyModalOpen = false" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-500 transition shadow-md shadow-indigo-600/20">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function auditExecution() {
        return {
            filterStatus: 'all',
            isVerifyModalOpen: false,
            verifyActionUrl: '',
            selectedItem: {
                id: '',
                physical_status: 'pending',
                notes: ''
            },

            openVerifyModal(item) {
                this.selectedItem = {
                    id: item.id,
                    physical_status: item.physical_status !== 'pending' ? item.physical_status : 'found',
                    notes: item.notes || ''
                };

                // Set endpoint update item
                this.verifyActionUrl = `{{ url('admin/asset-audit-items') }}/${item.id}`;
                this.isVerifyModalOpen = true;
            }
        };
    }
</script>
@endsection