@extends('layouts.admin')

@section('title', 'Peminjaman & Pengembalian Asset')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- Background Watermark: Peminjaman Aset -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <!-- Grid Dot Pattern Overlay -->
        <div class="absolute inset-0 opacity-[0.15] dark:opacity-[0.08]"
            style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>

        <!-- Central SVG Watermark Icon (Representasi Aset & Peminjaman) -->
        <div class="text-slate-900 dark:text-white opacity-[0.03] dark:opacity-[0.025] transform -rotate-12 scale-100 sm:scale-125 md:scale-150 p-4 transition-transform duration-700">
            <svg class="w-72 h-72 sm:w-96 sm:h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <!-- Outer Circular Target Lines -->
                <circle cx="12" cy="12" r="10" stroke-width="0.5" stroke-dasharray="4 4" />
                <circle cx="12" cy="12" r="8" stroke-width="0.5" />

                <!-- Asset Box Icon (Aset) -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M20 7.5L12 3 4 7.5M20 7.5l-8 4.5m8-4.5v9l-8 4.5M4 7.5l8 4.5M4 7.5v9l8 4.5m0-9v9" />

                <!-- Handshake / Loan Symbol Curves (Peminjaman) -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" stroke-dasharray="2 2" d="M7 11h10M12 7v10" />
            </svg>
        </div>
    </div>

    <!-- Efek Kilau Gradasi Background -->
    <div class="absolute top-0 left-1/4 w-72 sm:w-[500px] h-72 sm:h-[500px] bg-blue-400/10 dark:bg-blue-500/5 rounded-full blur-[120px] pointer-events-none z-0"></div>

    <div class="relative z-10">

        <!-- Header Page & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Daftar Peminjaman & Pengembalian Asset</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola riwayat, persetujuan, dan pengembalian aset perusahaan.</p>
            </div>
            @can('asset-loans.create')
            <button onclick="openModal('modalCreate')" type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Pengajuan Pinjam</span>
            </button>
            @endcan
        </div>

        <!-- Table Container -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200/50 dark:border-slate-800/60">
                        <tr>
                            <th class="px-6 py-4">No. Pinjam / Asset</th>
                            <th class="px-6 py-4">Peminjam & Departemen</th>
                            <th class="px-6 py-4">Lokasi</th>
                            <th class="px-6 py-4">Tgl Pinjam / Return</th>
                            <th class="px-6 py-4">Kondisi (Awal / Akhir)</th>
                            <th class="px-6 py-4">Status</th>
                            @canany(['asset-loans.approve', 'asset-loans.handover', 'asset-loans.return', 'asset-loans.view', 'asset-loans.edit', 'asset-loans.delete'])
                            <th class="px-6 py-4 text-center">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                        @forelse($loans ?? [] as $loan)
                        @php
                        $rawStatus = strtolower(trim($loan->status ?? 'pending'));
                        $isOverdue = ($rawStatus === 'borrowed' && \Carbon\Carbon::parse($loan->expected_return_date)->isPast());
                        $displayStatus = $isOverdue ? 'overdue' : $rawStatus;
                        @endphp
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-white">
                                <div class="text-[10px] text-blue-600 dark:text-blue-400 font-mono font-bold">{{ $loan->loan_number }}</div>
                                <div class="font-bold text-slate-800 dark:text-white">{{ $loan->asset->name ?? 'Asset Tidak Ditemukan' }}</div>
                                <div class="text-[10px] text-slate-400 font-normal">{{ $loan->asset->code ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                <div class="font-semibold">{{ $loan->user->name ?? '-' }}</div>
                                <div class="text-[10px] text-blue-500 dark:text-blue-400 font-medium">{{ $loan->department->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $loan->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                <div class="font-medium">{{ $loan->location->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $loan->location->code ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                <div><span class="text-slate-400">Pinjam:</span> {{ $loan->start_date ? \Carbon\Carbon::parse($loan->start_date)->format('d M Y') : '-' }}</div>
                                <div class="text-[10px] text-slate-400">Ekspektasi: {{ $loan->expected_return_date ? \Carbon\Carbon::parse($loan->expected_return_date)->format('d M Y') : '-' }}</div>
                                @if($loan->actual_return_date)
                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">
                                    Kembali: {{ \Carbon\Carbon::parse($loan->actual_return_date)->format('d M Y') }}
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 capitalize text-slate-600 dark:text-slate-300">
                                @php
                                $formatCond = function($cond) {
                                    return match($cond) {
                                        'minor_damage' => 'Rusak Ringan',
                                        'heavy_damage' => 'Rusak Berat',
                                        'lost' => 'Hilang',
                                        'good' => 'Baik',
                                        default => '-'
                                    };
                                };
                                @endphp
                                <div class="space-y-0.5">
                                    <div><span class="text-[10px] text-slate-400">Awal:</span> {{ $formatCond($loan->condition_before ?? 'good') }}</div>
                                    @if($loan->condition_after)
                                    <div><span class="text-[10px] text-slate-400">Akhir:</span> <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $formatCond($loan->condition_after) }}</span></div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                $badgeClass = match($displayStatus) {
                                    'approved' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                    'borrowed' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
                                    'returned' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                    'rejected' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                    'overdue' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20 animate-pulse font-extrabold',
                                    default => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                                    {{ $displayStatus }}
                                </span>
                            </td>
                            @canany(['asset-loans.approve', 'asset-loans.handover', 'asset-loans.return', 'asset-loans.view', 'asset-loans.edit', 'asset-loans.delete'])
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">

                                    @if($rawStatus === 'pending')
                                    @can('asset-loans.approve')
                                    <form action="{{ route('admin.asset_loans.approve', $loan->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-lg text-[10px] font-bold transition-all flex items-center gap-1" title="Setujui Peminjaman">
                                            <i data-lucide="check" class="w-3 h-3"></i> Setujui
                                        </button>
                                    </form>
                                    <button type="button" onclick="openRejectModal({{ json_encode($loan) }})" class="px-2 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 rounded-lg text-[10px] font-bold transition-all flex items-center gap-1" title="Tolak Peminjaman">
                                        <i data-lucide="x" class="w-3 h-3"></i> Tolak
                                    </button>
                                    @endcan
                                    @endif

                                    @if($rawStatus === 'approved')
                                    @can('asset-loans.handover')
                                    <form action="{{ route('admin.asset_loans.handover', $loan->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-1 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 rounded-lg text-[10px] font-bold transition-all flex items-center gap-1" title="Serahkan Barang ke Peminjam">
                                            <i data-lucide="package-check" class="w-3 h-3"></i> Serahkan Barang
                                        </button>
                                    </form>
                                    @endcan
                                    @endif

                                    @if($rawStatus === 'borrowed' || $isOverdue)
                                    @can('asset-loans.return')
                                    <button type="button" onclick="openReturnModal({{ $loan->id }}, '{{ $loan->loan_number }}', '{{ $loan->asset->name ?? 'Asset' }}')" class="px-2 py-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-lg text-[10px] font-bold transition-all flex items-center gap-1" title="Proses Pengembalian Asset">
                                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i> Kembalikan
                                    </button>
                                    @endcan
                                    @endif

                                    @can('asset-loans.view')
                                    <button type="button" onclick="showDetail({{ json_encode($loan->load(['asset', 'user', 'department', 'location'])) }})" class="p-1.5 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Detail">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    @endcan

                                    @can('asset-loans.edit')
                                    <button type="button" onclick="openEditModal({{ json_encode($loan) }})" class="p-1.5 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    @endcan

                                    @can('asset-loans.delete')
                                    <form action="{{ route('admin.asset_loans.destroy', $loan->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data peminjaman ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors" title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @endcan

                                </div>
                            </td>
                            @endcanany
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="arrow-left-right" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                <p>Belum ada data peminjaman asset.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL REJECT (PENOLAKAN) ==================== -->
    <div id="modalReject" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-white">Tolak Peminjaman</h3>
                    <p id="reject_loan_info" class="text-[11px] text-slate-400 mt-0.5"></p>
                </div>
                <button onclick="closeModal('modalReject')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="formReject" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Tuliskan alasan mengapa pengajuan peminjaman ditolak..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modalReject')" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-rose-500/20">Konfirmasi Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL RETURN (PENGEMBALIAN ASET) ==================== -->
    <div id="modalReturn" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-white">Form Pengembalian Asset</h3>
                    <p id="return_asset_info" class="text-[11px] text-slate-400 mt-0.5"></p>
                </div>
                <button onclick="closeModal('modalReturn')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="formReturn" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tanggal Pengembalian Aktual</label>
                    <input type="date" name="actual_return_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kondisi Setelah Dipinjam (Kondisi Akhir)</label>
                    <select name="condition_after" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                        <option value="good">Baik (Good)</option>
                        <option value="minor_damage">Rusak Ringan (Minor Damage)</option>
                        <option value="heavy_damage">Rusak Berat (Heavy Damage)</option>
                        <option value="lost">Hilang (Lost)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Catatan Pengembalian</label>
                    <textarea name="notes" rows="3" placeholder="Masukkan kelengkapan, kendala, atau kondisi aset saat dikembalikan..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modalReturn')" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-lg shadow-emerald-500/20">Proses Pengembalian</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL CREATE ==================== -->
    <div id="modalCreate" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <h3 class="text-base font-bold text-slate-800 dark:text-white">Tambah Pengajuan Peminjaman</h3>
                <button onclick="closeModal('modalCreate')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('admin.asset_loans.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Peminjam / Karyawan</label>
                        <select name="user_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">-- Pilih Karyawan --</option>
                            @foreach($users ?? [] as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Departemen</label>
                        <select name="department_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">-- Pilih Departemen --</option>
                            @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                {{ $dept->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Lokasi Peminjaman</label>
                        <select name="location_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">-- Pilih Lokasi --</option>
                            @foreach($locations ?? [] as $location)
                            <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                {{ $location->code ? '[' . $location->code . '] ' : '' }}{{ $location->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Asset</label>
                        <select name="asset_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            <option value="" class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">-- Pilih Asset --</option>
                            @forelse($assets ?? [] as $asset)
                            <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                {{ Str::limit($asset->name, 25) }} ({{ $asset->code }}) - {{ ucfirst($asset->status) }}
                            </option>
                            @empty
                            <option value="" disabled class="bg-white dark:bg-slate-800 text-slate-400 dark:text-slate-500">-- Tidak ada aset yang tersedia --</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tgl Pengajuan</label>
                        <input type="date" name="request_date" value="{{ old('request_date', date('Y-m-d')) }}" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tgl Mulai Pinjam</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ekspektasi Kembali</label>
                        <input type="date" name="expected_return_date" value="{{ old('expected_return_date') }}" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kondisi Sebelum Dipinjam</label>
                    <select name="condition_before" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                        <option value="good" {{ old('condition_before') == 'good' ? 'selected' : '' }}>Baik (Good)</option>
                        <option value="minor_damage" {{ old('condition_before') == 'minor_damage' ? 'selected' : '' }}>Rusak Ringan (Minor Damage)</option>
                        <option value="heavy_damage" {{ old('condition_before') == 'heavy_damage' ? 'selected' : '' }}>Rusak Berat (Heavy Damage)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alasan Peminjaman</label>
                    <textarea name="reason" rows="2" placeholder="Tujuan / Keperluan meminjam..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">{{ old('reason') }}</textarea>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan (opsional)..." class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modalCreate')" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700">Simpan Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL EDIT ==================== -->
    <div id="modalEdit" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <h3 class="text-base font-bold text-slate-800 dark:text-white">Edit Data Peminjaman</h3>
                <button onclick="closeModal('modalEdit')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="formEdit" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Peminjam / Karyawan</label>
                        <select name="user_id" id="edit_user_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                            @foreach($users ?? [] as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Departemen</label>
                        <select name="department_id" id="edit_department_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Lokasi Peminjaman</label>
                        <select name="location_id" id="edit_location_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach($locations ?? [] as $location)
                            <option value="{{ $location->id }}">{{ $location->code ? '[' . $location->code . '] ' : '' }}{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Asset</label>
                        <select name="asset_id" id="edit_asset_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                            @foreach($assets ?? [] as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tgl Mulai Pinjam</label>
                        <input type="date" name="start_date" id="edit_start_date" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Ekspektasi Kembali</label>
                        <input type="date" name="expected_return_date" id="edit_expected_return_date" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kondisi Awal</label>
                        <select name="condition_before" id="edit_condition_before" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                            <option value="good">Baik (Good)</option>
                            <option value="minor_damage">Rusak Ringan (Minor Damage)</option>
                            <option value="heavy_damage">Rusak Berat (Heavy Damage)</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Status</label>
                        <select name="status" id="edit_status" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="borrowed">Borrowed</option>
                            <option value="returned">Returned</option>
                            <option value="rejected">Rejected</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kondisi Akhir (Opsional)</label>
                        <select name="condition_after" id="edit_condition_after" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Kondisi Akhir --</option>
                            <option value="good">Baik</option>
                            <option value="minor_damage">Rusak Ringan</option>
                            <option value="heavy_damage">Rusak Berat</option>
                            <option value="lost">Hilang</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alasan</label>
                    <textarea name="reason" id="edit_reason" rows="2" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Catatan Pengembalian</label>
                    <textarea name="notes" id="edit_notes" rows="2" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-xl font-medium text-slate-700 dark:text-slate-200 text-xs focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('modalEdit')" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-xl text-xs font-semibold hover:bg-amber-700">Update Peminjaman</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL DETAIL ==================== -->
    <div id="modalDetail" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-white">Rincian Peminjaman</h3>
                    <span id="detail_loan_number" class="text-[10px] text-blue-600 dark:text-blue-400 font-mono font-bold"></span>
                </div>
                <button onclick="closeModal('modalDetail')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-slate-400 block font-medium">Asset:</span>
                        <span id="detail_asset" class="font-bold text-slate-700 dark:text-slate-200"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Peminjam:</span>
                        <span id="detail_borrower" class="font-bold text-slate-700 dark:text-slate-200"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-slate-400 block font-medium">Departemen:</span>
                        <span id="detail_department" class="font-semibold text-slate-600 dark:text-slate-300"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Lokasi:</span>
                        <span id="detail_location" class="font-semibold text-slate-600 dark:text-slate-300"></span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <span class="text-slate-400 block font-medium">Tgl Pengajuan:</span>
                        <span id="detail_request_date" class="font-semibold text-slate-600 dark:text-slate-300"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Tgl Mulai:</span>
                        <span id="detail_start_date" class="font-semibold text-slate-600 dark:text-slate-300"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Ekspektasi Kembali:</span>
                        <span id="detail_expected_return" class="font-semibold text-slate-600 dark:text-slate-300"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-slate-400 block font-medium">Kondisi Awal:</span>
                        <span id="detail_condition_before" class="font-semibold capitalize text-slate-700 dark:text-slate-200"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Kondisi Akhir:</span>
                        <span id="detail_condition_after" class="font-semibold capitalize text-slate-700 dark:text-slate-200"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-slate-400 block font-medium">Status:</span>
                        <span id="detail_status" class="font-bold uppercase tracking-wider text-[10px]"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Tgl Pengembalian Aktual:</span>
                        <span id="detail_actual_return" class="font-semibold text-slate-600 dark:text-slate-300"></span>
                    </div>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Alasan Meminjam:</span>
                    <p id="detail_reason" class="mt-1 p-2 bg-slate-50 dark:bg-slate-800/50 rounded-lg text-slate-600 dark:text-slate-300 italic"></p>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Catatan Pengembalian:</span>
                    <p id="detail_notes" class="mt-1 p-2 bg-slate-50 dark:bg-slate-800/50 rounded-lg text-slate-600 dark:text-slate-300 italic"></p>
                </div>

                <div id="rejection_container" class="hidden">
                    <span class="text-rose-400 block font-medium">Alasan Penolakan:</span>
                    <p id="detail_rejection_reason" class="mt-1 p-2 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-lg italic"></p>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-right">
                <button type="button" onclick="closeModal('modalDetail')" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ==================== JAVASCRIPT ==================== -->
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            const d = new Date(dateString);
            return d.toISOString().split('T')[0];
        }

        function formatCondition(condition) {
            switch (condition) {
                case 'minor_damage':
                    return 'Rusak Ringan';
                case 'heavy_damage':
                    return 'Rusak Berat';
                case 'lost':
                    return 'Hilang';
                case 'good':
                    return 'Baik';
                default:
                    return '-';
            }
        }

        function openRejectModal(loan) {
            const form = document.getElementById('formReject');
            form.action = `/admin/asset_loans/${loan.id}/reject`;

            const info = document.getElementById('reject_loan_info');
            if (info) {
                info.innerText = `${loan.loan_number} - ${loan.asset?.name ?? 'Asset'}`;
            }

            openModal('modalReject');
        }

        function openReturnModal(loanId, loanNumber = '', assetName = 'Asset') {
            const form = document.getElementById('formReturn');

            if (!loanId) {
                console.error("Loan ID tidak ditemukan!");
                return;
            }

            form.action = `/admin/asset_loans/${loanId}/return`;

            const info = document.getElementById('return_asset_info');
            if (info) {
                info.innerText = `${loanNumber} - ${assetName}`;
            }

            openModal('modalReturn');
        }

        function openEditModal(loan) {
            const form = document.getElementById('formEdit');
            form.action = `/admin/asset_loans/${loan.id}`;

            document.getElementById('edit_user_id').value = loan.user_id ?? '';
            document.getElementById('edit_department_id').value = loan.department_id ?? '';
            document.getElementById('edit_location_id').value = loan.location_id ?? '';
            document.getElementById('edit_asset_id').value = loan.asset_id ?? '';
            document.getElementById('edit_start_date').value = formatDate(loan.start_date);
            document.getElementById('edit_expected_return_date').value = formatDate(loan.expected_return_date);
            document.getElementById('edit_condition_before').value = loan.condition_before ?? 'good';
            document.getElementById('edit_status').value = loan.status ?? 'pending';
            document.getElementById('edit_condition_after').value = loan.condition_after ?? '';
            document.getElementById('edit_reason').value = loan.reason ?? '';
            document.getElementById('edit_notes').value = loan.notes ?? '';

            openModal('modalEdit');
        }

        function showDetail(loan) {
            document.getElementById('detail_loan_number').innerText = loan.loan_number ?? '-';
            document.getElementById('detail_asset').innerText = loan.asset?.name ?? '-';
            document.getElementById('detail_borrower').innerText = loan.user?.name ?? '-';
            document.getElementById('detail_department').innerText = loan.department?.name ?? '-';
            document.getElementById('detail_location').innerText = loan.location?.name ?? '-';
            document.getElementById('detail_request_date').innerText = formatDate(loan.request_date);
            document.getElementById('detail_start_date').innerText = formatDate(loan.start_date);
            document.getElementById('detail_expected_return').innerText = formatDate(loan.expected_return_date);
            document.getElementById('detail_condition_before').innerText = formatCondition(loan.condition_before);
            document.getElementById('detail_condition_after').innerText = formatCondition(loan.condition_after);

            const statusEl = document.getElementById('detail_status');
            statusEl.innerText = loan.status ?? 'pending';

            document.getElementById('detail_actual_return').innerText = formatDate(loan.actual_return_date);
            document.getElementById('detail_reason').innerText = loan.reason || '-';
            document.getElementById('detail_notes').innerText = loan.notes || '-';

            const rejectionContainer = document.getElementById('rejection_container');
            if (loan.status === 'rejected' && loan.rejection_reason) {
                document.getElementById('detail_rejection_reason').innerText = loan.rejection_reason;
                rejectionContainer.classList.remove('hidden');
            } else {
                rejectionContainer.classList.add('hidden');
            }

            openModal('modalDetail');
        }
    </script>

    @if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            openModal('modalCreate');
        });
    </script>
    @endif

</div>
@endsection