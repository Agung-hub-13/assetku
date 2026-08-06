@extends('layouts.admin')

@section('title', 'Log Aktivitas Aset')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- Ambient Glow Light -->
    <div class="absolute top-0 right-1/4 w-72 sm:w-[450px] h-72 sm:h-[450px] bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="absolute bottom-0 left-1/4 w-60 sm:w-[350px] h-60 sm:h-[350px] bg-slate-500/10 dark:bg-slate-500/5 rounded-full blur-[100px] pointer-events-none z-0"></div>

    <div class="relative z-10 space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                    <div class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    Log Aktivitas & Audit Trail
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Lacak riwayat mutasi, peminjaman, perbaikan, dan perubahan data aset secara rinci.</p>
            </div>
        </div>

        {{-- Filter & Search Card --}}
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-800/80">
            <form method="GET" action="{{ route('admin.asset_logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Cari Kata Kunci -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Pencarian Kata Kunci</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama aset, user, atau catatan..."
                            class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-xs sm:text-sm transition">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Filter Aset -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Pilih Aset Spesifik</label>
                    <select name="asset_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-xs sm:text-sm transition">
                        <option value="">Semua Aset</option>
                        @foreach($assets as $ast)
                        <option value="{{ $ast->id }}" {{ request('asset_id') == $ast->id ? 'selected' : '' }}>
                            {{ $ast->name }} ({{ $ast->asset_code ?? '-' }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Kategori / Action -->
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Jenis Operasi / Aksi</label>
                    <select name="action" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-xs sm:text-sm transition">
                        <option value="">Semua Jenis Aktivitas</option>
                        <option value="borrow" {{ request('action') == 'borrow' ? 'selected' : '' }}>📦 Peminjaman</option>
                        <option value="return" {{ request('action') == 'return' ? 'selected' : '' }}>↩️ Pengembalian</option>
                        <option value="maintenance" {{ request('action') == 'maintenance' ? 'selected' : '' }}>🛠️ Maintenance / Perbaikan</option>
                        <option value="transfer" {{ request('action') == 'transfer' ? 'selected' : '' }}>🔄 Mutasi / Transfer Lokasi</option>
                        <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>➕ Input Baru</option>
                        <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>✏️ Update Data</option>
                        <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>🗑️ Hapus Aset</option>
                    </select>
                </div>

                <!-- Tombol Filter -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-4 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-medium rounded-xl text-xs sm:text-sm transition shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter Data
                    </button>
                    @if(request()->hasAny(['search', 'asset_id', 'action']))
                    <a href="{{ route('admin.asset_logs.index') }}" class="py-2 px-3.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-medium rounded-xl text-xs sm:text-sm transition" title="Reset Filter">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50/80 dark:bg-slate-800/60 text-[11px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Waktu Operasi</th>
                            <th class="px-5 py-3.5">Nama & Kode Aset</th>
                            <th class="px-5 py-3.5">Jenis Aktivitas</th>
                            <th class="px-5 py-3.5">Deskripsi Catatan</th>
                            <th class="px-5 py-3.5">Aktor / Pengguna</th>
                            <th class="px-5 py-3.5 text-center">Detail Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors duration-150">
                            
                            <!-- Waktu -->
                            <td class="px-5 py-4 whitespace-nowrap align-top">
                                <div class="font-mono text-xs text-slate-900 dark:text-slate-200 font-bold">
                                    {{ $log->created_at ? $log->created_at->format('d M Y') : '-' }}
                                </div>
                                <div class="font-mono text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $log->created_at ? $log->created_at->format('H:i:s') : '' }} WIB
                                </div>
                            </td>

                            <!-- Aset -->
                            <td class="px-5 py-4 align-top">
                                @if($log->asset)
                                <div class="font-bold text-slate-900 dark:text-white leading-tight">{{ $log->asset->name }}</div>
                                <div class="text-[11px] font-mono text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $log->asset->asset_code ?? '-' }}</div>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200/50 dark:border-rose-900/50">
                                    ⚠️ Aset Telah Dihapus
                                </span>
                                @endif
                            </td>

                            <!-- Action Badge -->
                            <td class="px-5 py-4 whitespace-nowrap align-top">
                                @php
                                $act = strtolower($log->action);
                                $badgeMap = [
                                    'borrow'            => ['bg' => 'bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800', 'icon' => '📦', 'label' => 'PEMINJAMAN'],
                                    'return'            => ['bg' => 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800', 'icon' => '↩️', 'label' => 'PENGEMBALIAN'],
                                    'maintenance'       => ['bg' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800', 'icon' => '🛠️', 'label' => 'MAINTENANCE'],
                                    'transfer'          => ['bg' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800', 'icon' => '🔄', 'label' => 'MUTASI / TRANSFER'],
                                    'transfer_requested'=> ['bg' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800', 'icon' => '📋', 'label' => 'DRAFT MUTASI'],
                                    'transfer_rejected' => ['bg' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800', 'icon' => '❌', 'label' => 'MUTASI DITOLAK'],
                                    'mutation'          => ['bg' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800', 'icon' => '🔄', 'label' => 'MUTASI'],
                                    'create'            => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800', 'icon' => '➕', 'label' => 'INPUT BARU'],
                                    'update'            => ['bg' => 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800', 'icon' => '✏️', 'label' => 'UPDATE DATA'],
                                    'delete'            => ['bg' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800', 'icon' => '🗑️', 'label' => 'DIHAPUS'],
                                ];
                                $style = $badgeMap[$act] ?? ['bg' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700', 'icon' => '📝', 'label' => strtoupper($act)];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $style['bg'] }}">
                                    <span>{{ $style['icon'] }}</span>
                                    <span>{{ $style['label'] }}</span>
                                </span>
                            </td>

                            <!-- Deskripsi -->
                            <td class="px-5 py-4 max-w-sm align-top">
                                <p class="text-slate-700 dark:text-slate-200 text-xs leading-relaxed font-normal">
                                    {{ $log->description }}
                                </p>
                            </td>

                            <!-- User (Aktor) -->
                            <td class="px-5 py-4 whitespace-nowrap align-top">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs shadow-sm ring-2 ring-white dark:ring-slate-900">
                                        {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-xs text-slate-900 dark:text-slate-100">{{ $log->user->name ?? 'Sistem Automated' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $log->user->email ?? 'System Process' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Properti Detail Button -->
                            <td class="px-5 py-4 text-center whitespace-nowrap align-top">
                                @if($log->properties && (is_array($log->properties) ? count($log->properties) > 0 : count((array)$log->properties) > 0))
                                <button onclick='showProperties(@json($log->properties), "{{ $style['label'] }}", "{{ $log->asset->name ?? 'Aset' }}", "{{ $log->created_at ? $log->created_at->format('d M Y, H:i') : '-' }}")'
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-300 rounded-xl transition border border-indigo-200/60 dark:border-indigo-800/60 shadow-sm group">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat Detail
                                </button>
                                @else
                                <span class="text-slate-400 italic text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="p-3 rounded-full bg-slate-100 dark:bg-slate-800/80">
                                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Belum ada riwayat log aktivitas yang sesuai filter.</p>
                                    <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau reset filter di atas.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Detail Properties --}}
<div id="propertiesModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4 transition-all duration-300">
    <div class="relative w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-start p-5 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/80 dark:bg-slate-800/40">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span id="modalCategoryBadge" class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800"></span>
                    <span id="modalTimestamp" class="text-xs text-slate-400 font-mono"></span>
                </div>
                <h3 id="modalAssetName" class="text-base font-bold text-slate-900 dark:text-white">Detail Parameter Data</h3>
            </div>
            <button onclick="closePropertiesModal()" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body Container -->
        <div class="p-5 sm:p-6">
            <div id="formattedProperties" class="space-y-2.5 max-h-[60vh] overflow-y-auto pr-1"></div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end p-4 bg-slate-50/80 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
            <button onclick="closePropertiesModal()" class="px-5 py-2 text-xs font-bold bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl transition">
                Tutup Detail
            </button>
        </div>
    </div>
</div>

<script>
    // Kamus Menerjemahkan Key Database ID ke Bahasa Manusia
    const keyTranslations = {
        'from_location_id': 'Lokasi Asal',
        'to_location_id': 'Lokasi Tujuan',
        'asset_location_id': 'Lokasi Aset',
        'location_id': 'Lokasi',
        'user_id': 'Pengguna / Aktor',
        'borrower_id': 'Peminjam',
        'pic_id': 'Penanggung Jawab (PIC)',
        'category_id': 'Kategori Aset',
        'vendor_id': 'Vendor / Supplier',
        'asset_id': 'Aset',
        'status': 'Status',
        'condition': 'Kondisi Aset',
        'notes': 'Catatan Tambahan',
        'cost': 'Biaya / Harga',
        'serial_number': 'Nomor Seri',
        'asset_code': 'Kode Aset',
        'created_at': 'Waktu Dibuat',
        'updated_at': 'Waktu Diperbarui',
        'from_location': 'Lokasi Asal',
        'to_location': 'Lokasi Tujuan',
        'location': 'Lokasi',
        'borrower': 'Nama Peminjam',
        'user': 'Nama Pengguna'
    };

    function showProperties(data, categoryName, assetName, timeStamp) {
        const container = document.getElementById('formattedProperties');
        document.getElementById('modalCategoryBadge').textContent = categoryName || 'Aktivitas';
        document.getElementById('modalAssetName').textContent = 'Detail Log: ' + (assetName || '');
        document.getElementById('modalTimestamp').textContent = timeStamp || '';
        
        container.innerHTML = '';

        if (typeof data === 'object' && data !== null && Object.keys(data).length > 0) {
            Object.keys(data).forEach(key => {
                const val = data[key];
                
                // Jika val berupa null atau undefined, lewati atau beri strip
                let displayVal = val;
                
                // Jika properti berupa Objek (misal relasi yang ter-load seperti location: {name: 'Ruang Server'})
                if (typeof val === 'object' && val !== null) {
                    displayVal = val.name || val.title || val.code || JSON.stringify(val);
                }

                // Terjemahkan nama key dari Kamus atau Format Karakter Otomatis
                const readableKey = keyTranslations[key] || key.replace(/_id$/g, '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                const row = document.createElement('div');
                row.className = 'flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800/80 text-xs gap-2 hover:border-indigo-300 dark:hover:border-indigo-800 transition';
                
                row.innerHTML = `
                    <span class="font-bold text-slate-600 dark:text-slate-400 capitalize flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        ${readableKey}
                    </span>
                    <span class="font-mono text-slate-900 dark:text-slate-100 font-semibold break-all bg-white dark:bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-200/80 dark:border-slate-800 shadow-sm sm:max-w-[60%] sm:text-right">
                        ${displayVal ?? '-'}
                    </span>
                `;
                container.appendChild(row);
            });
        } else {
            container.innerHTML = `
                <div class="text-center py-8 text-slate-400 italic text-xs">
                    Tidak ada atribut parameter khusus yang terekam pada log ini.
                </div>
            `;
        }

        document.getElementById('propertiesModal').classList.remove('hidden');
    }

    function closePropertiesModal() {
        document.getElementById('propertiesModal').classList.add('hidden');
    }

    window.onclick = function(event) {
        const modal = document.getElementById('propertiesModal');
        if (event.target == modal) {
            closePropertiesModal();
        }
    }
</script>
@endsection