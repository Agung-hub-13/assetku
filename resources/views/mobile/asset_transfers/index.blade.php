@extends('layouts.mobile')

@section('title', 'Mutasi Aset - AssetKu')

@section('content')
<!-- ─── TOP HEADER BAR ─── -->
<header class="absolute top-0 left-0 right-0 z-40 bg-gradient-to-b from-slate-900 via-slate-900/80 to-transparent px-4 pt-6 pb-12 flex items-center justify-between text-white">
    <div class="flex items-center gap-3">
        <div>
            <h2 class="text-sm font-black mt-1 tracking-tight">Mutasi & Transfer Aset</h2>
        </div>
    </div>

    <!-- Tombol Tambah Mutasi Baru -->
    <button onclick="openCreateModal()" class="w-10 h-10 rounded-xl bg-indigo-600 border border-indigo-500/30 flex items-center justify-center text-white shadow-[0_4px_12px_rgba(79,70,229,0.3)] active:scale-90 transition-all">
        <i class="fa-solid fa-plus text-sm"></i>
    </button>
</header>

<!-- ─── TOP SECTION BUFFER ─── -->
<div class="h-28 w-full bg-slate-900"></div>

<!-- ─── MAIN CONTENT CONTAINER ─── -->
<div class="px-4 -mt-8 relative z-30 space-y-4 pb-28">

    <!-- ─── NOTIFICATION ALERT SYSTEM ─── -->
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm animate-fade-in">
        <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm animate-fade-in">
        <i class="fa-solid fa-circle-xmark text-rose-500 text-sm"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- ─── PILAR SEARCH & FILTER BAR ─── -->
    <div class="bg-white p-3 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.02)] border border-slate-100/80 flex gap-2">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" placeholder="Cari riwayat mutasi aset..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 transition-colors">
        </div>
        <button class="w-11 h-11 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-600 active:scale-95 transition-all">
            <i class="fa-solid fa-sliders text-xs"></i>
        </button>
    </div>

    <!-- ─── STATUS FILTER CAPSULES ─── -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar py-1 -mx-4 px-4">
        <button class="px-4 py-1.5 rounded-full bg-indigo-600 text-white text-[11px] font-black shadow-sm shrink-0">
            Semua ({{ $transfers->total() }})
        </button>
        <button class="px-4 py-1.5 rounded-full bg-white border border-slate-100 text-slate-600 text-[11px] font-bold shadow-sm shrink-0">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> Completed
        </button>
        <button class="px-4 py-1.5 rounded-full bg-white border border-slate-100 text-slate-600 text-[11px] font-bold shadow-sm shrink-0">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 mr-1"></span> Draft
        </button>
    </div>

    <!-- ─── LIST DAFTAR TRANSFERS (CARDS LOOPING) ─── -->
    <div class="space-y-3">
        @forelse ($transfers as $transfer)
        @php
        $status = strtolower($transfer->status ?? 'draft');
        if ($status === 'completed') {
        $badgeStyle = 'text-emerald-600 bg-emerald-50 border border-emerald-100';
        $statusText = 'Selesai';
        } elseif ($status === 'rejected') {
        $badgeStyle = 'text-rose-600 bg-rose-50 border border-rose-100';
        $statusText = 'Ditolak';
        } elseif ($status === 'waiting_approval') {
        $badgeStyle = 'text-blue-600 bg-blue-50 border border-blue-100';
        $statusText = 'Menunggu';
        } else {
        $badgeStyle = 'text-amber-600 bg-amber-50 border border-amber-100';
        $statusText = 'Draft';
        }

        $type = $transfer->transfer_type;
        $typeText = $type === 'location_change' ? 'Pindah Lokasi' : ($type === 'temporary' ? 'Pinjam Sementara' : 'Pengembalian');
        @endphp

        <!-- Card item dengan event click untuk edit (kecuali jika status sudah Selesai) -->
        <div
            @if($status !=='completed' )
            onclick="openEditModal({{ json_encode($transfer) }})"
            class="bg-white border border-slate-100/80 rounded-2xl p-4 shadow-[0_4px_14px_rgba(0,0,0,0.01)] space-y-3 relative overflow-hidden border-b-[3px] border-b-slate-100 active:bg-slate-50 transition-colors cursor-pointer"
            @else
            class="bg-white border border-slate-100/80 rounded-2xl p-4 shadow-[0_4px_14px_rgba(0,0,0,0.01)] space-y-3 relative overflow-hidden border-b-[3px] border-b-slate-100"
            @endif>
            <!-- Baris Atas: Kode Log & Status -->
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1">
                    <i class="fa-regular fa-calendar text-[10px]"></i>
                    {{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d M Y') }}
                </span>
                <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $badgeStyle }}">
                    {{ $statusText }}
                </span>
            </div>

            <!-- Bagian Tengah: Detail Nama Aset -->
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 border border-indigo-100/50">
                    <i class="fa-solid fa-right-left text-sm"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs font-black text-slate-800 truncate">
                        {{ $transfer->asset->name ?? 'Aset Tidak Ditemukan' }}
                    </h4>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">
                        Asset Code: {{ $transfer->asset->asset_code ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- Alur Perpindahan Alamat Lokasi (Dari -> Ke) -->
            <div class="bg-slate-50/70 p-2.5 rounded-xl border border-slate-100/50 space-y-2 text-[10px]">

                <!-- Lokasi Asal -->
                <div class="flex items-start gap-2.5">
                    <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-[9px] shrink-0 mt-0.5 shadow-sm">
                        Dari
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-700 dark:text-slate-300 truncate">
                            {{ $transfer->from_location_name ?? '-' }}
                        </div>
                        <div class="flex flex-wrap items-center gap-1 mt-1 text-[10px]">
                            <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-md border border-slate-200/40 dark:border-slate-700/50">
                                Lantai {{ $transfer->fromLocation->floor ?? '-' }}
                            </span>
                            <span class="text-slate-400 italic">
                                ({{ $transfer->fromLocation->department_name ?? '-' }})
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Garis Penghubung Alur dengan Ikon Panah ke Bawah -->
                <div class="flex items-center pl-2.5">
                    <div class="h-4 border-l-2 border-dashed border-slate-300 dark:border-slate-700"></div>
                    <svg class="w-3 h-3 text-slate-300 dark:text-slate-700 -ml-1.5 mt-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"></path>
                    </svg>
                </div>

                <!-- Lokasi Tujuan -->
                <div class="flex items-start gap-2.5">
                    <div class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-[9px] shrink-0 mt-0.5 shadow-sm">
                        Ke
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-blue-900 dark:text-blue-400 truncate">
                            {{ $transfer->to_location_name ?? '-' }}
                        </div>
                        <div class="flex flex-wrap items-center gap-1 mt-1 text-[10px]">
                            <span class="px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-md border border-blue-100/30 dark:border-blue-800/30 font-medium">
                                Lantai {{ $transfer->toLocation->floor ?? '-' }}
                            </span>
                            <span class="text-blue-500 dark:text-blue-500 font-medium italic">
                                ({{ $transfer->toLocation->department_name ?? '-' }})
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Card: Tanggal, Keterangan & Aksi Cepat Mobile -->
            <div class="pt-1 flex items-center justify-between border-t border-slate-50 text-[9px] font-bold text-slate-400">

                <!-- Quick Action Mobile Buttons untuk Draft -->
                @if($status === 'draft' || $status === 'waiting_approval')
                <div class="flex items-center gap-1.5" onclick="event.stopPropagation();">
                    <form action="{{ route('mobile.asset_transfers.approve', $transfer->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 text-white rounded-lg active:scale-95 transition-transform flex items-center gap-1 font-black">
                            <i class="fa-solid fa-check text-[8px]"></i> Setujui
                        </button>
                    </form>
                    <form action="{{ route('mobile.asset_transfers.reject', $transfer->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 bg-rose-50 border border-rose-100 text-rose-600 rounded-lg active:scale-95 transition-transform font-bold">
                            Tolak
                        </button>
                    </form>
                </div>
                @endif
            </div>

        </div>
        @empty
        <div class="bg-white border border-slate-100 rounded-2xl p-8 text-center">
            <i class="fa-solid fa-shuffle text-slate-300 text-3xl mb-2"></i>
            <p class="text-xs font-bold text-slate-400">Belum ada riwayat mutasi logistik aset.</p>
        </div>
        @endforelse
    </div>

    <!-- ─── PAGINATION SECTION ─── -->
    @if($transfers->hasPages())
    <div class="mt-4 mobile-pagination">
        {{ $transfers->links() }}
    </div>
    @endif
</div>

<!-- ─── MODAL DIALOG SYSTEM (SINGLE MODAL SYSTEM) ─── -->
<div id="transferModal" class="fixed inset-0 z-50 flex items-end justify-center hidden">
    <!-- Backdrop overlay -->
    <div onclick="closeModalSystem()" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

    <!-- Sheet Panel Container -->
    <div class="relative w-full max-w-md bg-white rounded-t-[28px] shadow-2xl p-6 pb-8 transition-transform duration-300 transform translate-y-full z-10 max-h-[85vh] overflow-y-auto">
        <!-- Handlebar bar top -->
        <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto mb-5"></div>

        <!-- Header Modal -->
        <div class="flex items-center justify-between mb-5">
            <h3 id="modalTitle" class="text-sm font-black text-slate-800">Tambah Mutasi Baru</h3>
            <button onclick="closeModalSystem()" class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:text-slate-600 active:scale-90 transition-transform">
                <i class="fa-solid fa-xmark text-xs"></i>
            </button>
        </div>

        <!-- Form Transaksi Utama -->
        <form id="transferForm" method="POST" action="" class="space-y-4">
            @csrf
            <input type="hidden" id="formMethod" name="_method" value="POST">

            <!-- Dropdown Pilih Aset dengan Fitur Cari (Mobile-Friendly) -->
            <div class="space-y-1 relative" id="customAssetDropdownContainer">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Pilih Aset</label>

                <!-- Select Asli (Tetap disembunyikan agar form submission Laravel tidak error) -->
                <select id="inputAssetId" name="asset_id" required class="hidden">
                    <option value="">-- Pilih Aset --</option>
                    @foreach($assets as $asset)
                    <option value="{{ $asset->id }}">
                        {{ $asset->name }} ({{ $asset->location->name ?? 'Belum ada lokasi' }})
                    </option>
                    @endforeach
                </select>

                <!-- Trigger Button (Tampilan pengganti Select Box) -->
                <div id="assetDropdownTrigger" onclick="toggleAssetDropdown()" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs font-semibold text-slate-700 flex justify-between items-center cursor-pointer active:bg-slate-100 transition-colors">
                    <span id="assetDropdownSelectedText" class="truncate text-slate-400">-- Pilih Aset --</span>
                    <i class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform duration-200" id="assetDropdownArrow"></i>
                </div>

                <!-- Panel Dropdown Kustom (Muncul melayang saat diklik) -->
                <div id="assetDropdownPanel" class="hidden absolute left-0 right-0 z-50 mt-1 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden max-h-60 flex flex-col">
                    <!-- Kotak Cari (Search Input) -->
                    <div class="p-2 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-[10px] pl-2"></i>
                        <input type="text" id="assetSearchInput" oninput="filterAssetOptions()" placeholder="Cari nama aset..." class="w-full bg-transparent border-none text-xs font-semibold text-slate-700 focus:outline-none placeholder-slate-400">
                        <button type="button" onclick="clearAssetSearch()" id="btnClearAssetSearch" class="hidden text-slate-400 hover:text-slate-600 px-1">
                            <i class="fa-solid fa-circle-xmark text-xs"></i>
                        </button>
                    </div>

                    <!-- Daftar Pilihan Aset -->
                    <div id="assetOptionsList" class="overflow-y-auto divide-y divide-slate-50 max-h-48 no-scrollbar">
                        <div onclick="selectAsset('', '-- Pilih Aset --')" class="px-4 py-2.5 text-xs text-slate-400 font-semibold hover:bg-indigo-50 cursor-pointer">
                            -- Pilih Aset --
                        </div>
                        @foreach($assets as $asset)
                        <div
                            onclick="selectAsset('{{ $asset->id }}', '{{ $asset->name }} ({{ $asset->location->name ?? 'Belum ada lokasi' }})')"
                            data-id="{{ $asset->id }}"
                            data-search="{{ strtolower($asset->name . ' ' . ($asset->location->name ?? 'Belum ada lokasi')) }}"
                            class="asset-option-item px-4 py-2.5 text-xs text-slate-700 font-semibold hover:bg-indigo-50 active:bg-indigo-100 cursor-pointer transition-colors">
                            {{ $asset->name }}
                            <span class="block text-[9px] text-slate-400 font-medium mt-0.5">
                                📍 {{ $asset->location->name ?? 'Belum ada lokasi' }}
                            </span>
                        </div>
                        @endforeach
                        <!-- Info jika tidak ada hasil pencarian -->
                        <div id="noAssetFoundText" class="hidden px-4 py-4 text-xs text-slate-400 text-center">
                            Aset tidak ditemukan.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dropdown Lokasi Tujuan -->
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Lokasi Tujuan</label>
                <div class="relative">
                    <select id="inputToLocationId" name="to_location_id" required class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs font-semibold text-slate-700 focus:outline-none focus:border-indigo-500 transition-colors appearance-none">
                        <option value="">-- Pilih Lokasi Tujuan --</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}">
                            {{ $loc->name }} {{ $loc->department_name ? "({$loc->department_name})" : '' }}
                        </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <!-- Dropdown Tipe Mutasi -->
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Tipe Mutasi</label>
                <div class="relative">
                    <select id="inputTransferType" name="transfer_type" required class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs font-semibold text-slate-700 focus:outline-none focus:border-indigo-500 transition-colors appearance-none">
                        <option value="location_change">Pindah Lokasi (Permanen)</option>
                        <option value="temporary">Pinjam Sementara</option>
                        <option value="return">Pengembalian</option>
                    </select>
                    <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <!-- Tanggal Mutasi -->
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Tanggal Mutasi</label>
                <input type="date" id="inputTransferDate" name="transfer_date" required class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs font-semibold text-slate-700 focus:outline-none focus:border-indigo-500 transition-colors">
            </div>

            <!-- Alasan Mutasi (Reason) -->
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Alasan</label>
                <input type="text" id="inputReason" name="reason" placeholder="Contoh: Pemindahan ruang kerja baru" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 transition-colors">
            </div>

            <!-- Catatan Tambahan -->
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Catatan Tambahan</label>
                <textarea id="inputNotes" name="notes" rows="2" placeholder="Detail atau catatan pendukung tambahan..." class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 transition-colors"></textarea>
            </div>

            <!-- Tombol Submit Action -->
            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 transition-all text-white rounded-xl text-xs font-black shadow-md">
                    Simpan Mutasi
                </button>
            </div>
        </form>

        <!-- Tombol Tambahan Hapus (Hanya muncul saat Mode Edit) -->
        <div id="btnDeleteContainer" class="pt-2 hidden">
            <form id="deleteForm" method="POST" action="" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draft mutasi ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-3 bg-rose-50 border border-rose-100 hover:bg-rose-100 active:scale-95 transition-all text-rose-600 rounded-xl text-xs font-bold">
                    Hapus Draft Mutasi
                </button>
            </form>
        </div>
    </div>
</div>

<!-- ─── JAVASCRIPT SYSTEM CONTROLLER ─── -->
<script>
    const transferModal = document.getElementById('transferModal');
    const modalContent = transferModal ? transferModal.querySelector('.relative') : null;
    const transferForm = document.getElementById('transferForm');
    const deleteForm = document.getElementById('deleteForm');
    const triggerBtn = document.getElementById('assetDropdownTrigger');
    const dropdownPanel = document.getElementById('assetDropdownPanel');
    const searchInput = document.getElementById('assetSearchInput');
    const selectedText = document.getElementById('assetDropdownSelectedText');
    const dropdownArrow = document.getElementById('assetDropdownArrow');
    const realSelect = document.getElementById('inputAssetId');
    const clearSearchBtn = document.getElementById('btnClearAssetSearch');

    function showModalSystem() {
        if (!transferModal || !modalContent) return;
        transferModal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('translate-y-full');
        }, 10);
    }

    function closeModalSystem() {
        if (!transferModal || !modalContent) return;
        modalContent.classList.add('translate-y-full');
        setTimeout(() => {
            transferModal.classList.add('hidden');
        }, 300);
    }

    function openCreateModal() {
        if (!transferForm) return;

        transferForm.reset();
        document.getElementById('formMethod').value = 'POST';

        // Atur default tanggal hari ini agar user tidak perlu mengisi manual jika hari ini
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('inputTransferDate').value = today;

        // Posisikan endpoint action untuk store
        transferForm.action = "/mobile/asset-transfers";

        document.getElementById('modalTitle').innerText = 'Tambah Mutasi Baru';
        document.getElementById('btnDeleteContainer').classList.add('hidden');

        showModalSystem();
    }

    function openEditModal(transferData) {
        if (!transferForm) return;

        // Cegah edit jika status sudah selesai (opsional, karena card-nya sendiri sudah dimatikan fungsinya di Laravel)
        if (transferData.status === 'completed') {
            alert('Mutasi yang sudah disetujui tidak dapat diubah kembali.');
            return;
        }

        transferForm.reset();
        document.getElementById('formMethod').value = 'PUT';

        const targetUrl = window.location.origin + `/mobile/asset-transfers/${transferData.id}`;
        transferForm.action = targetUrl;
        if (deleteForm) {
            deleteForm.action = targetUrl;
        }

        document.getElementById('modalTitle').innerText = 'Ubah Draft Mutasi';

        // Isi nilai input form
        document.getElementById('inputAssetId').value = transferData.asset_id;
        document.getElementById('inputToLocationId').value = transferData.to_location_id;
        document.getElementById('inputTransferType').value = transferData.transfer_type;

        // Parsing format tanggal
        if (transferData.transfer_date) {
            const dateOnly = transferData.transfer_date.split(' ')[0];
            document.getElementById('inputTransferDate').value = dateOnly;
        }

        document.getElementById('inputReason').value = transferData.reason || '';
        document.getElementById('inputNotes').value = transferData.notes || '';

        document.getElementById('btnDeleteContainer').classList.remove('hidden');

        showModalSystem();
    }

    // Membuka / Menutup Dropdown Panel
    function toggleAssetDropdown() {
        const isOpen = !dropdownPanel.classList.contains('hidden');
        if (isOpen) {
            closeAssetDropdown();
        } else {
            dropdownPanel.classList.remove('hidden');
            dropdownArrow.classList.add('rotate-180');
            // Fokus otomatis ke input pencarian saat terbuka
            setTimeout(() => searchInput.focus(), 50);
        }
    }

    function closeAssetDropdown() {
        dropdownPanel.classList.add('hidden');
        dropdownArrow.classList.remove('rotate-180');
    }

    // Melakukan Filter Pilihan (Pencarian Real-time)
    function filterAssetOptions() {
        const query = searchInput.value.toLowerCase().trim();
        const items = document.querySelectorAll('.asset-option-item');
        const noResult = document.getElementById('noAssetFoundText');
        let anyVisible = false;

        // Tampilkan tombol "clear" jika ada teks pencarian
        if (query.length > 0) {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }

        items.forEach(item => {
            const searchText = item.getAttribute('data-search');
            if (searchText.includes(query)) {
                item.classList.remove('hidden');
                anyVisible = true;
            } else {
                item.classList.add('hidden');
            }
        });

        // Tampilkan teks "Aset tidak ditemukan" jika tidak ada yang cocok
        if (anyVisible) {
            noResult.classList.add('hidden');
        } else {
            noResult.classList.remove('hidden');
        }
    }

    // Menghapus Kolom Pencarian
    function clearAssetSearch() {
        searchInput.value = '';
        clearSearchBtn.classList.add('hidden');
        filterAssetOptions();
        searchInput.focus();
    }

    // Memilih Aset & Sinkronisasi ke Select Bawaan Laravel
    function selectAsset(id, text) {
        // 1. Perbarui teks di tombol trigger
        selectedText.innerText = text;
        if (id === '') {
            selectedText.classList.add('text-slate-400');
            selectedText.classList.remove('text-slate-700');
        } else {
            selectedText.classList.remove('text-slate-400');
            selectedText.classList.add('text-slate-700');
        }

        // 2. Set value ke select asli milik Laravel agar request POST/PUT tidak kosong
        realSelect.value = id;

        // Atur agar validasi HTML5 bawaan form mendeteksi perubahannya
        realSelect.dispatchEvent(new Event('change'));

        // 3. Tutup Panel
        closeAssetDropdown();
        clearAssetSearch();
    }

    // Menutup dropdown jika user mengklik di luar area dropdown
    document.addEventListener('click', function(event) {
        const container = document.getElementById('customAssetDropdownContainer');
        if (container && !container.contains(event.target)) {
            closeAssetDropdown();
        }
    });
</script>
@endsection