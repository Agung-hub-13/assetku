@extends('layouts.admin')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.15] dark:opacity-[0.08]"
            style="background-image: radial-gradient(#e2e8f0 1.5px, transparent 1.5px); background-size: 40px 40px;"></div>
        <div class="text-slate-900 dark:text-white opacity-[0.03] dark:opacity-[0.02] transform scale-125 md:scale-150 p-4">
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="0.5" stroke-dasharray="4 4" />
                <circle cx="12" cy="12" r="7.5" stroke-width="0.75" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M12 2v4m0 12v4M2 12h4m12 0h4" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M12 8.5c-1.5 0-3.5 1-3.5 3.5 0 2.5 2 4 3.5 4.5 1.5-.5 3.5-2 3.5-4.5 0-2.5-2-3.5-3.5-3.5z" />
            </svg>
        </div>
    </div>

    <!-- HEADER & AKSI MASSAL -->
    <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">Inventaris Aset</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data seluruh aset perusahaan secara terpusat.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <form action="{{ route('admin.assets.sync-all') }}" method="POST" style="display: none;">
                @csrf
                <button id="btnSyncSingle" type="submit">🔄 Sync Semua Data dari Accurate</button>
            </form>

            @can('reports.export')
            <a href="{{ route('admin.assets.export-excel', request()->query()) }}"
                class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-2xl font-bold transition-all shadow-lg shadow-emerald-600/10 active:scale-95 text-xs sm:text-sm flex-1 sm:flex-none">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </a>
            @endcan

            @can('asset.create')
            <button type="button" onclick="openCreateModal()"
                class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-2xl font-bold transition-all shadow-lg shadow-blue-600/10 active:scale-95 text-xs sm:text-sm flex-1 sm:flex-none">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Aset
            </button>
            @endcan

            <div class="flex items-center gap-3 bg-white dark:bg-slate-800 px-4 py-2 rounded-2xl border border-slate-100 dark:border-slate-700/50 shadow-sm w-full sm:w-auto justify-between sm:justify-start">
                <div class="font-semibold text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                    <span id="bulk-count-top" class="text-xs text-slate-600 dark:text-slate-300 font-medium">0 Asset Selected</span>
                </div>

                <button id="btnBulkAssign" type="button" disabled onclick="openBulkModal()"
                    class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl disabled:opacity-40 disabled:cursor-not-allowed transition-all active:scale-95">
                    Bulk Assign Lokasi
                </button>
            </div>
        </div>
    </div>

    <!-- CARDS STATISTIK -->
    <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-[2rem] border border-slate-100 dark:border-slate-700/50 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-widest">Total Aset</p>
            <p class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ $assets->total() }}</p>
        </div>

        <a href="{{ request()->get('depreciated') == '1' ? request()->fullUrlWithQuery(['depreciated' => null]) : request()->fullUrlWithQuery(['depreciated' => '1']) }}"
            class="relative block p-5 rounded-[2rem] border transition-all duration-200 group {{ request()->get('depreciated') == '1' ? 'bg-red-50/50 dark:bg-red-950/20 border-red-200 dark:border-red-900 shadow-inner' : 'bg-white dark:bg-slate-900 border-slate-100 dark:border-slate-700/50 shadow-sm hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md' }}">
            <div class="flex justify-between items-start">
                <p class="text-[10px] font-bold uppercase tracking-widest {{ request()->get('depreciated') == '1' ? 'text-red-500' : 'text-slate-400 group-hover:text-blue-500' }}">
                    Nilai Buku Habis
                </p>
                @if(request()->get('depreciated') == '1')
                <span class="text-[9px] font-extrabold bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400 px-2 py-0.5 rounded-full uppercase tracking-wider">Aktif</span>
                @else
                <span class="text-[9px] font-bold opacity-0 group-hover:opacity-100 text-blue-500 transition-opacity uppercase tracking-wider">Filter 🔍</span>
                @endif
            </div>
            <p class="text-2xl sm:text-3xl font-black mt-1 {{ request()->get('depreciated') == '1' ? 'text-red-600' : 'text-blue-600' }}">
                {{ $totalBookValueHabis ?? 0 }}
            </p>
        </a>
    </div>

    <!-- FILTER & PENCARIAN -->
    <form action="{{ request()->url() }}" method="GET" class="relative z-10 bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm mb-6 transition-all">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
        
        <!-- Cari Aset -->
        <div class="lg:col-span-3 space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Cari Aset</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-transparent dark:border-slate-700/50 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 font-medium text-xs sm:text-sm text-slate-700 dark:text-slate-200 transition-all outline-none"
                    placeholder="Nama aset, kode, serial...">
            </div>
        </div>

        <!-- Kategori -->
        <div class="lg:col-span-2 space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Kategori</label>
            <select name="category_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-transparent dark:border-slate-700/50 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 font-medium text-xs sm:text-sm text-slate-700 dark:text-slate-200 transition-all outline-none">
                <option value="">Semua Kategori</option>
                @foreach($categories ?? [] as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                    {{ strtoupper($category->name) }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Lokasi -->
        <div class="lg:col-span-2 space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Lokasi</label>
            <select name="room_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-transparent dark:border-slate-700/50 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 font-medium text-xs sm:text-sm text-slate-700 dark:text-slate-200 transition-all outline-none">
                <option value="">Semua Lokasi</option>
                @foreach($locations ?? [] as $loc)
                <option value="{{ $loc->id }}" {{ request('room_id') == $loc->id ? 'selected' : '' }}>
                    {{ $loc->name }} {{ $loc->parent ? '('.$loc->parent->name.')' : '' }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Status -->
        <div class="lg:col-span-2 space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Status</label>
            <select name="status" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-transparent dark:border-slate-700/50 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 font-medium text-xs sm:text-sm text-slate-700 dark:text-slate-200 transition-all outline-none">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
        </div>

        <!-- Tombol Aksi (Filter & Reset) -->
        <div class="lg:col-span-3 flex items-center gap-2">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs sm:text-sm transition-all shadow-sm hover:shadow-blue-500/20 active:scale-95 text-center flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter
            </button>
            
            @if(request()->anyFilled(['search', 'category_id', 'room_id', 'status']))
            <a href="{{ request()->url() }}" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-semibold py-2.5 px-4 rounded-xl text-xs sm:text-sm transition-all active:scale-95 text-center flex items-center justify-center">
                Reset
            </a>
            @endif
        </div>

    </div>
</form>

    <!-- TOMBOL AKSI CETAK QR MASSAL -->
    <div class="relative z-10 flex flex-wrap gap-2 mb-4">
        <button type="button" onclick="printSelectedQR()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-xs sm:text-sm transition-all active:scale-95 flex items-center gap-2">
            🖨️ Cetak QR Terpilih (<span id="selected-count">0</span>)
        </button>
    </div>

    <!-- FORM SUBMIT CETAK MASSAL -->
    <form id="bulk-qr-form" action="{{ route('admin.assets.bulkPrintQr') }}" method="POST" target="_blank" class="hidden">
        @csrf
        <input type="hidden" name="asset_ids" id="form-asset-ids">
    </form>

    <!-- TABEL DATA ASET -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100 dark:border-slate-700/50">
                        <th class="px-4 py-4 text-center w-12">
                            <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 shadow-sm">
                        </th>
                        <th class="px-6 py-4">Informasi Aset</th>
                        <th class="px-6 py-4">Lokasi & Departemen</th>
                        <th class="px-6 py-4">Nilai Buku</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-sm text-slate-700 dark:text-slate-200 divide-y divide-slate-100 dark:divide-slate-700/30">
                    @forelse($assets as $asset)
                    @php
                    // SESUAIKAN MENJADI SEPERTI INI
                    $activeLocation = $asset->transfer->toLocation ?? $asset->location;

                    $qrPayload = "AST:" . ($asset->asset_code ?? '-') . "\n" .
                    "NM:" . $asset->name . "\n" .
                    "LOC:" . ($activeLocation->name ?? '-');
                    @endphp
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition-colors align-middle">
                        <td class="px-4 py-4 text-center">
                            <input type="checkbox" name="selected_assets[]" value="{{ $asset->id }}" class="asset-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 shadow-sm">
                        </td>

                        <!-- Informasi Ringkas -->
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800 dark:text-white text-base leading-snug">{{ $asset->name }}</span>
                                <div class="flex items-center gap-3 text-xs text-slate-400 mt-0.5">
                                    <span>Code: <strong class="text-slate-600 dark:text-slate-300 font-mono">{{ $asset->asset_code ?? '-' }}</strong></span>
                                    <span>•</span>
                                    <span>Accurate: <strong class="text-slate-600 dark:text-slate-300 font-mono">{{ $asset->accurate_no ?? '-' }}</strong></span>
                                </div>
                            </div>
                        </td>

                        <!-- Lokasi & Departemen -->
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                    📍 {{ $activeLocation->name ?? 'Belum Ditentukan' }}
                                </span>
                                <span class="text-[11px] text-slate-400">
                                    🏢 Dept: {{ $asset->department->name ?? '-' }}
                                </span>
                                <span class="text-[11px] text-slate-400">
                                    🧑 User: {{ $asset->user->name ?? '-' }}
                                </span>
                            </div>
                        </td>

                        <!-- Nilai Buku -->
                        <td class="px-6 py-4">
                            <div class="text-xs">
                                @if(($asset->book_value ?? 0) <= 0)
                                    <span class="text-red-600 dark:text-red-400 font-bold">Rp 0 (Habis)</span>
                                    @else
                                    <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($asset->book_value, 0, ',', '.') }}</span>
                                    @endif
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="px-6 py-4">
                            @if(strtolower($asset->status) == 'active')
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 uppercase">ACTIVE</span>
                            @elseif(strtolower($asset->status) == 'maintenance')
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg bg-amber-50 text-amber-600 border border-amber-200 dark:bg-amber-900/20 dark:text-amber-400 uppercase">MAINTENANCE</span>
                            @else
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-lg bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 uppercase">{{ strtoupper($asset->status) }}</span>
                            @endif
                        </td>

                        <!-- Action Buttons -->
                        <td class="px-6 py-4 text-center">
                            @php
                            $detailPayload = [
                            "id" => $asset->id,
                            "name" => $asset->name,
                            "asset_code" => $asset->asset_code ?? "-",
                            "accurate_no" => $asset->accurate_no ?? "-",
                            "serial_number" => $asset->serial_number ?? "-",
                            "description" => $asset->description ?? "-",
                            "quantity" => $asset->quantity ?? 1,
                            "purchase_price" => number_format($asset->purchase_price ?? 0, 0, ",", "."),
                            "book_value" => number_format($asset->book_value ?? 0, 0, ",", "."),
                            "category" => $asset->category->name ?? $asset->accurate_category_name ?? "-",
                            "department" => $asset->department->name ?? "-",
                            "status" => strtoupper($asset->status ?? "-"),
                            "location" => $activeLocation->name ?? "-",
                            "building" => $activeLocation->building ?? "-",
                            "floor" => $activeLocation->floor ?? "-",
                            "room" => $activeLocation->room ?? "-",
                            "qr_payload" => $qrPayload,
                            "print_url" => route("admin.assets.print-qrcode", $asset->id)
                            ];
                            @endphp

                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button"
                                    onclick='openDetailModal(@json($detailPayload))'
                                    title="Lihat Detail"
                                    class="p-2 bg-sky-50 dark:bg-sky-950/30 text-sky-600 dark:text-sky-400 rounded-xl hover:bg-sky-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>

                                @canany(['asset.edit', 'asset.delete'])
                                @can('asset.edit')
                                <button type="button"
                                    onclick='openEditModal(@json($asset->load(["location", "department", "category"])))'
                                    title="Edit"
                                    class="p-2 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-xl hover:bg-amber-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                @endcan

                                @can('asset.delete')
                                <button type="button"
                                    onclick="openDeleteModal('{{ route('admin.assets.destroy', $asset->id) }}', '{{ e($asset->name) }}')"
                                    title="Hapus"
                                    class="p-2 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                @endcan
                                @endcanany
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                            Tidak ada data aset ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-700/50">
            {{ $assets->links() }}
        </div>
        @endif
    </div>

    <!-- STORE DATA FILTERED ID -->
    <div id="filtered-assets-data" data-ids="{{ implode(',', $assets->pluck('id')->toArray()) }}"></div>
</div>

<!-- 🔍 MODAL DETAIL ASET -->
<div id="modalDetailAsset" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative w-full max-w-2xl bg-white dark:bg-slate-800 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-700/50 overflow-hidden transform transition-all">
        <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">Detail Informasi Aset</span>
                <h3 id="m-name" class="text-xl font-black text-slate-800 dark:text-white leading-tight">-[Nama Aset]-</h3>
            </div>
            <button onclick="closeDetailModal()" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                ✕
            </button>
        </div>

        <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
            <div class="flex flex-col sm:flex-row gap-6 items-center bg-slate-50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50">
                <div id="m-qr-container" class="p-2 bg-white rounded-xl shadow-sm border border-slate-200 shrink-0"></div>
                <div class="space-y-2 flex-1 text-center sm:text-left w-full">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <span id="m-status" class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400 uppercase">ACTIVE</span>
                        <span id="m-category" class="px-2.5 py-0.5 text-[10px] font-bold rounded-lg bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300 uppercase">KATEGORI</span>
                        <span id="m-department" class="px-2.5 py-0.5 text-[10px] font-bold rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 uppercase">DEPARTEMEN</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs pt-2">
                        <div>
                            <span class="text-slate-400 block">Asset Code</span>
                            <span id="m-code" class="font-bold font-mono text-slate-700 dark:text-slate-200">-</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Accurate No</span>
                            <span id="m-accurate" class="font-bold font-mono text-slate-700 dark:text-slate-200">-</span>
                        </div>
                    </div>
                    <div class="pt-2 flex items-center gap-2 justify-center sm:justify-start">
                        <a id="m-print-btn" href="#" target="_blank" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition-all shadow-sm">
                            🖨️ Cetak QR
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50 bg-white dark:bg-slate-800 space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Informasi Spesifikasi & Keuangan</span>
                    <div>
                        <span class="text-slate-400">Serial Number:</span>
                        <p id="m-serial" class="font-semibold text-slate-700 dark:text-slate-200">-</p>
                    </div>
                    <div>
                        <span class="text-slate-400">Harga Beli / Nilai Buku:</span>
                        <p class="font-semibold text-slate-700 dark:text-slate-200">
                            Rp <span id="m-price">0</span> / Rp <span id="m-bookvalue">0</span>
                        </p>
                    </div>
                    <div>
                        <span class="text-slate-400">Deskripsi:</span>
                        <p id="m-desc" class="text-slate-600 dark:text-slate-300 leading-relaxed">-</p>
                    </div>
                </div>

                <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-700/50 bg-white dark:bg-slate-800 space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Lokasi & Penempatan</span>
                    <div>
                        <span class="text-slate-400">Lokasi / Ruangan:</span>
                        <p id="m-location" class="font-bold text-slate-800 dark:text-slate-100">-</p>
                    </div>
                    <div>
                        <span class="text-slate-400">Gedung / Lantai / Ruang:</span>
                        <p id="m-sublocation" class="font-medium text-slate-600 dark:text-slate-300">-</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/50 flex justify-end">
            <button onclick="closeDetailModal()" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 font-bold text-xs rounded-xl transition-all">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL CRUD (Tambah / Edit Aset - Lengkap dengan Deskripsi & Departemen) -->
<div id="crudModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 transition-opacity" onclick="closeModal()"></div>
    <div class="flex min-h-full items-center justify-center p-3 sm:p-4">
        <div class="relative w-full max-w-4xl rounded-2xl md:rounded-3xl bg-white dark:bg-slate-800 shadow-2xl transition-all scale-95 opacity-0 duration-300" id="modalContainer">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 p-5 sm:px-8">
                <h3 id="modalTitle" class="text-lg sm:text-2xl font-bold text-slate-800 dark:text-white tracking-tight">Form Aset</h3>
                <button type="button" onclick="closeModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="crudForm" method="POST" class="p-5 sm:p-8 space-y-5 max-h-[75vh] sm:max-h-[80vh] overflow-y-auto">
                @csrf
                <div id="methodField"></div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nomor Aset</label>
                                <input type="text" name="asset_number" id="a_number" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition" placeholder="Auto-generate">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Asset Code</label>
                                <input type="text" name="asset_code" id="a_asset_code" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition" placeholder="Auto dari nama jika kosong">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="a_name" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Serial Number</label>
                                <input type="text" name="serial_number" id="a_serial" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Jumlah (Qty) <span class="text-rose-500">*</span></label>
                                <input type="number" name="quantity" id="a_quantity" required min="1" value="1" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Kategori</label>
                                <select name="category_id" id="a_category" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Departemen</label>
                                <select name="department_id" id="a_department" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                                    <option value="">-- Pilih Departemen --</option>
                                    @foreach($departments ?? [] as $dept)
                                    <option value="{{ $dept->id }}">
                                        {{ $dept->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">User</label>
                                <select name="user_id" id="a_user" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                                    <option value="">-- Pilih User --</option>
                                    @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Lokasi Aset</label>
                            <select name="location_id" id="a_location" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach($locations ?? [] as $location)
                                <option value="{{ $location->id }}">
                                    {{ $location->parent->name ?? '' }} {{ $location->parent ? '»' : '' }} {{ $location->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 📝 INPUT DESKRIPSI -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Deskripsi Aset</label>
                            <textarea name="description" id="a_description" rows="3" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition" placeholder="Keterangan tambahan aset..."></textarea>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Status Aset <span class="text-rose-500">*</span></label>
                            <select name="status" id="a_status" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="disposed">Disposed</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Harga Beli Satuan</label>
                            <input type="number" name="purchase_price" id="a_price" min="0" step="any" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition" placeholder="0">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tanggal Pembelian</label>
                            <input type="date" name="purchase_date" id="a_purchase_date" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition text-sm">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md transition active:scale-95 text-sm">
                        Simpan Data Aset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL BULK ASSIGN -->
<div id="bulkAssignModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 transition-opacity" onclick="closeBulkModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl transition-all">
            <h2 class="text-lg sm:text-xl font-bold mb-4 text-slate-800 dark:text-white">
                Bulk Assign Lokasi & Kategori
            </h2>

            <form method="POST" action="{{ route('admin.assets.bulkAssign') }}" class="space-y-4">
                @csrf
                <input type="hidden" id="bulk_asset_ids" name="asset_ids">

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Kategori Aset</label>
                    <select name="category_id" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm font-medium">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Departemen</label>
                    <select name="department_id" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm font-medium">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Lokasi Aset</label>
                    <select name="location_id" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-sm font-medium">
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($locations ?? [] as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" onclick="closeBulkModal()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm font-bold hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-md hover:bg-blue-700 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DELETE KONFIRMASI -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 transition-opacity" onclick="closeDelete()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white dark:bg-slate-800 w-full max-w-sm rounded-2xl sm:rounded-3xl p-6 sm:p-8 text-center shadow-2xl transition-all scale-95 opacity-0 duration-300" id="deleteContainer">
            <div class="w-16 h-16 bg-rose-50 dark:bg-rose-950/40 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white mb-2">Hapus Aset?</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6 font-medium text-xs sm:text-sm">Aset "<span id="del_name" class="font-bold text-slate-800 dark:text-slate-200"></span>" akan dihapus permanen dari sistem.</p>
            <form id="deleteForm" method="POST" class="flex gap-3">
                @csrf @method('DELETE')
                <button type="button" onclick="closeDelete()" class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-bold hover:bg-slate-200 transition text-sm">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-rose-600 text-white rounded-xl font-bold hover:bg-rose-700 transition text-sm">Hapus</button>
            </form>
        </div>
    </div>
</div>

<!-- Easy QR Generator Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    function openDetailModal(data) {
        document.getElementById('m-name').innerText = data.name;
        document.getElementById('m-code').innerText = data.asset_code;
        document.getElementById('m-accurate').innerText = data.accurate_no;
        document.getElementById('m-serial').innerText = data.serial_number;
        document.getElementById('m-price').innerText = data.purchase_price;
        document.getElementById('m-bookvalue').innerText = data.book_value;
        document.getElementById('m-desc').innerText = data.description;
        document.getElementById('m-category').innerText = data.category;
        document.getElementById('m-department').innerText = data.department;
        document.getElementById('m-status').innerText = data.status;

        document.getElementById('m-location').innerText = data.location;
        document.getElementById('m-sublocation').innerText = `Gedung: ${data.building} | Lt: ${data.floor} | Ruang: ${data.room}`;

        document.getElementById('m-print-btn').href = data.print_url;

        const qrContainer = document.getElementById('m-qr-container');
        qrContainer.innerHTML = '';
        new QRCode(qrContainer, {
            text: data.qr_payload,
            width: 110,
            height: 110
        });

        document.getElementById('modalDetailAsset').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('modalDetailAsset').classList.add('hidden');
    }

    const selectAll = document.getElementById('select-all');
    const btnBulk = document.getElementById('btnBulkAssign');
    const countLabel = document.getElementById('selected-count');
    const bulkCountTop = document.getElementById('bulk-count-top');
    const bulkAssetIdsInput = document.getElementById('bulk_asset_ids');

    function updateSelection() {
        const currentCheckboxes = document.querySelectorAll('.asset-checkbox');
        let selectedIds = [];

        currentCheckboxes.forEach(cb => {
            if (cb.checked) {
                selectedIds.push(cb.value);
            }
        });

        if (countLabel) countLabel.innerText = selectedIds.length;
        if (bulkCountTop) bulkCountTop.innerText = `${selectedIds.length} Asset Selected`;

        if (btnBulk) btnBulk.disabled = selectedIds.length === 0;
        if (bulkAssetIdsInput) bulkAssetIdsInput.value = selectedIds.join(',');

        if (selectAll) {
            if (selectedIds.length === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else if (selectedIds.length === currentCheckboxes.length && currentCheckboxes.length > 0) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            }
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const currentCheckboxes = document.querySelectorAll('.asset-checkbox');
            currentCheckboxes.forEach(cb => cb.checked = this.checked);
            updateSelection();
        });
    }

    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('asset-checkbox')) {
            updateSelection();
        }
    });

    document.addEventListener('DOMContentLoaded', updateSelection);

    function printSelectedQR() {
        const checkedBoxes = document.querySelectorAll('.asset-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Silakan pilih minimal satu aset terlebih dahulu!');
            return;
        }

        const ids = Array.from(checkedBoxes).map(cb => cb.value).join(',');
        const inputForm = document.getElementById('form-asset-ids');
        const form = document.getElementById('bulk-qr-form');

        if (inputForm && form) {
            inputForm.value = ids;
            form.submit();
        }
    }

    const crudModal = document.getElementById('crudModal');
    const modalCont = document.getElementById('modalContainer');
    const delModal = document.getElementById('deleteModal');
    const delCont = document.getElementById('deleteContainer');
    const bulkModal = document.getElementById('bulkAssignModal');

    function openCreateModal() {
        const form = document.getElementById('crudForm');
        const method = document.getElementById('methodField');
        const title = document.getElementById('modalTitle');

        title.innerText = 'Tambah Aset Baru';
        form.action = "{{ route('admin.assets.store') }}";
        method.innerHTML = '';

        form.reset();
        document.getElementById('a_status').value = 'draft';
        document.getElementById('a_quantity').value = 1;

        crudModal.classList.remove('hidden');
        setTimeout(() => {
            modalCont.classList.remove('scale-95', 'opacity-0');
            modalCont.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function openEditModal(data) {
        const form = document.getElementById('crudForm');
        const method = document.getElementById('methodField');
        const title = document.getElementById('modalTitle');

        title.innerText = 'Edit Detail Aset';
        form.action = `/admin/assets/${data.id}`;

        // Gunakan HTML hidden input untuk method PUT
        method.innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('a_number').value = data.asset_number ?? '';
        document.getElementById('a_asset_code').value = data.asset_code ?? '';
        document.getElementById('a_name').value = data.name ?? '';
        document.getElementById('a_serial').value = data.serial_number ?? '';
        document.getElementById('a_quantity').value = data.quantity ?? 1;
        document.getElementById('a_price').value = data.purchase_price ?? 0;
        document.getElementById('a_status').value = data.status ?? 'draft';
        document.getElementById('a_category').value = data.category_id ?? '';
        document.getElementById('a_user').value = data.user_id ?? '';

        // PASTIKAN department_id terpetakan dengan benar
        document.getElementById('a_department').value = data.department_id ?? '';

        document.getElementById('a_location').value = data.location_id ?? '';
        document.getElementById('a_description').value = data.description ?? '';

        // PASTIKAN purchase_date terpetakan dengan format YYYY-MM-DD
        document.getElementById('a_purchase_date').value = data.purchase_date ? data.purchase_date.substring(0, 10) : '';

        crudModal.classList.remove('hidden');
        setTimeout(() => {
            modalCont.classList.remove('scale-95', 'opacity-0');
            modalCont.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal() {
        modalCont.classList.add('scale-95', 'opacity-0');
        setTimeout(() => crudModal.classList.add('hidden'), 300);
    }

    function openBulkModal() {
        if (bulkModal) bulkModal.classList.remove('hidden');
    }

    function closeBulkModal() {
        if (bulkModal) bulkModal.classList.add('hidden');
    }

    function openDeleteModal(url, name) {
        document.getElementById('deleteForm').action = url;
        document.getElementById('del_name').innerText = name;
        delModal.classList.remove('hidden');
        setTimeout(() => {
            delCont.classList.remove('scale-95', 'opacity-0');
            delCont.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeDelete() {
        delCont.classList.add('scale-95', 'opacity-0');
        setTimeout(() => delModal.classList.add('hidden'), 300);
    }
</script>
@endsection