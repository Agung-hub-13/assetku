@extends('layouts.mobile')

@section('title', 'Manajemen Lokasi - AssetKu')

@section('content')
<!-- ─── TOP HEADER BAR ─── -->
<header class="absolute top-0 left-0 right-0 z-40 bg-gradient-to-b from-slate-900 via-slate-900/80 to-transparent px-4 pt-6 pb-12 flex items-center justify-between text-white">
    <div class="flex items-center gap-3">
        <a href="javascript:void(0)" onclick="event.preventDefault(); history.back();" class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-lg border border-white/20 flex items-center justify-center text-white shadow-md active:scale-90 transition-all">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <p class="text-[10px] text-slate-300 font-medium tracking-wide uppercase leading-none">Pengaturan Master</p>
            <h2 class="text-sm font-black mt-1 tracking-tight">Master Lokasi Aset</h2>
        </div>
    </div>

    <!-- Tombol Tambah Lokasi Baru -->
    <button type="button" onclick="openCreateModal()" class="w-10 h-10 rounded-xl bg-indigo-600 border border-indigo-500/30 flex items-center justify-center text-white shadow-[0_4px_12px_rgba(79,70,229,0.3)] active:scale-90 transition-all">
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
    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm animate-fade-in">
        <i class="fa-solid fa-circle-xmark text-rose-500 text-sm"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <!-- ─── PILAR SEARCH & FILTER BAR ─── -->
    <div class="bg-white p-3 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.02)] border border-slate-100/80 flex gap-2">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" id="searchInput" onkeyup="searchLocation()" placeholder="Cari nama lokasi atau kode..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 transition-colors">
        </div>
        <button class="w-11 h-11 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-600 active:scale-95 transition-all">
            <i class="fa-solid fa-sliders text-xs"></i>
        </button>
    </div>

    <!-- ─── STATUS FILTER CAPSULES ─── -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar py-1 -mx-4 px-4">
        <button class="px-4 py-1.5 rounded-full bg-indigo-600 text-white text-[11px] font-black shadow-sm shrink-0">
            Semua Data ({{ $locations instanceof \Illuminate\Pagination\LengthAwarePaginator ? $locations->total() : $locations->count() }})
        </button>
    </div>

    <!-- ─── LIST DAFTAR LOKASI (CARDS LOOPING) ─── -->
    <div class="space-y-3" id="locationListContainer">

        @forelse ($locations as $location)
        @php
        $isActive = ($location->status ?? 'active') === 'active';
        $statusBadge = $isActive ? 'text-emerald-600 bg-emerald-50' : 'text-slate-500 bg-slate-100';

        $isBuilding = !empty($location->building);
        $locationIcon = $isBuilding ? 'fa-building text-indigo-600' : 'fa-map-location-dot text-indigo-500';

        $jsonPayload = json_encode([
        'id' => $location->id,
        'code' => $location->code ?? 'LOC-' . str_pad($location->id, 4, '0', STR_PAD_LEFT),
        'name' => $location->name,
        'building' => $location->building ?? '',
        'floor' => $location->floor ?? '',
        'room' => $location->room ?? '',
        'status' => $location->status ?? 'active',
        'pic_name' => $location->pic_name ?? '',
        'parent_id' => $location->parent_id ?? ''
        ], JSON_HEX_APOS | JSON_HEX_QUOT);
        @endphp

        <div data-location="{{ $jsonPayload }}" onclick="openEditModalFromElement(this)" class="location-card bg-white border border-slate-100/70 rounded-2xl p-3.5 shadow-[0_4px_12px_rgba(0,0,0,0.01)] flex gap-3 items-center relative overflow-hidden border-b-[3px] border-b-slate-100 active:bg-slate-50/50 transition-colors cursor-pointer">

            <!-- Icon Blok Visual -->
            <div class="w-14 h-14 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center shrink-0">
                <i class="fa-solid {{ $locationIcon }} text-lg"></i>
                <span class="text-[7px] uppercase font-bold tracking-tighter text-slate-400 mt-0.5 truncate max-w-full px-1">
                    {{ $location->floor ? 'Lantai ' . $location->floor : 'LOKASI' }}
                </span>
            </div>

            <!-- Info Rincian Detail Lokasi -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <span class="location-code text-[9px] font-black font-mono tracking-wider text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">
                        {{ $location->code ?? 'LOC-' . str_pad($location->id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $statusBadge }}">
                        {{ $isActive ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </div>

                <!-- Judul Nama Ruangan/Lokasi Utama -->
                <h3 class="location-name text-xs font-bold text-slate-800 mt-1 truncate">
                    {{ $location->name }}
                </h3>

                <!-- Informasi Meta / Sub-informasi Gedung & Departemen -->
                <div class="flex flex-wrap gap-x-2 mt-0.5 text-[10px] text-slate-400 font-semibold">
                    @if($location->building || $location->room)
                    <span class="flex items-center gap-1">
                        <i class="fa-solid fa-layer-group text-[9px]"></i>
                        {{ implode(' - ', array_filter([$location->building, $location->room])) }}
                    </span>
                    @endif

                    @if($location->parent)
                    <span class="text-slate-300">|</span>
                    <span class="truncate max-w-[120px]">
                        Induk: {{ $location->parent->name }}
                    </span>
                    @endif
                </div>

                <!-- Info Penanggung Jawab (PIC Area) -->
                @if($location->pic_name)
                <p class="text-[9px] text-slate-500 font-medium mt-1 flex items-center gap-1 bg-slate-50 px-1.5 py-0.5 rounded w-max border border-slate-100/50">
                    <i class="fa-solid fa-user-tie text-[8px] text-slate-400"></i>
                    PIC: <span class="font-bold text-slate-600">{{ $location->pic_name }}</span>
                </p>
                @endif

                @if($location->department_name)
                <p class="text-[9px] text-slate-500 font-medium mt-1 flex items-center gap-1 bg-slate-50 px-1.5 py-0.5 rounded w-max border border-slate-100/50">
                    <i class="fa-solid fa-user-tie text-[8px] text-slate-400"></i>
                    Departemen: <span class="font-bold text-slate-600">{{ $location->department_name }}</span>
                </p>
                @endif

            </div>

            <!-- Sisi Kanan: Tombol Aksi Mobile Quick Action -->
            <div class="flex items-center gap-1.5 shrink-0 pl-2 border-l border-slate-100">
                <!-- Tombol Edit -->
                <button type="button"
                    data-location="{{ $jsonPayload }}"
                    onclick="openEditModalFromElement(this)"
                    class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 active:scale-90 transition-all">
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                </button>

                <!-- Tombol Delete Langsung -->
                <button type="button" onclick="directDelete({{ $location->id }})" class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 active:scale-90 transition-all">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </div>

            <!-- Quick Action Arrow Indicator -->
            <i class="fa-solid fa-chevron-right text-slate-300 text-xs pl-1"></i>
        </div>
        @empty
        <div class="bg-white border border-slate-100 rounded-2xl p-8 text-center">
            <i class="fa-solid fa-folder-open text-slate-300 text-3xl mb-2"></i>
            <p class="text-xs font-bold text-slate-400">Belum ada master data lokasi yang terdaftar.</p>
        </div>
        @endforelse

    </div>

    <!-- ─── PAGINATION SECTION ─── -->
    <div class="pt-2">
        @if($locations instanceof \Illuminate\Pagination\LengthAwarePaginator && $locations->hasPages())
        <div class="mt-4 mobile-pagination">
            {{ $locations->links() }}
        </div>
        @endif
        <div class="text-center mt-2">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">
                Menampilkan {{ $locations->count() }} Data
            </p>
        </div>
    </div>

</div>

<!-- ─── BOTTOM SHEET MODAL FORM (CREATE / EDIT) ─── -->
<div id="formModal" class="fixed inset-0 z-50 invisible transition-all duration-300">
    <!-- Backdrop Gelap -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" onclick="closeFormModal()"></div>

    <!-- Panel Bottom Sheet -->
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-[28px] shadow-2xl max-h-[85vh] overflow-y-auto flex flex-col transform translate-y-full transition-transform duration-300 no-scrollbar">
        <!-- Handle Bar Mini -->
        <div class="w-12 h-1 bg-slate-200 rounded-full mx-auto my-3 shrink-0" onclick="closeFormModal()"></div>

        <div class="px-5 pb-8">
            <div class="flex items-center justify-between mb-5">
                <h3 id="modalTitle" class="text-sm font-black text-slate-800">Tambah Lokasi</h3>
                <button type="button" onclick="closeFormModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>

            <!-- Formulir Utama Terpadu -->
            <form id="locationForm" method="POST" action="{{ route('mobile.asset_locations.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <!-- Input Kode Lokasi -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kode Lokasi (Opsional)</label>
                    <input type="text" name="code" id="inputCode" placeholder="Contoh: LOC-001" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Input Nama Lokasi -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Lokasi / Ruangan</label>
                    <input type="text" name="name" id="inputName" required placeholder="Contoh: Ruang Rapat Utama" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Baris Grid: Gedung & Lantai -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Gedung / Blok</label>
                        <input type="text" name="building" id="inputBuilding" placeholder="Gedung A" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Lantai</label>
                        <input type="text" name="floor" id="inputFloor" placeholder="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <!-- Input Nama Ruang Spesifik -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Detail Ruangan (Nomor/Nama)</label>
                    <input type="text" name="room" id="inputRoom" placeholder="R.204" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Pilihan Lokasi Induk (Parent) -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Lokasi Induk Sub-Area (Opsional)</label>
                    <select name="parent_id" id="inputParentId" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-indigo-500">
                        <option value="">-- Tidak Ada (Lokasi Utama) --</option>
                        @foreach($locations as $parentLoc)
                        <option value="{{ $parentLoc->id }}">{{ $parentLoc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Input PIC Area -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nama PIC Penanggung Jawab</label>
                    <input type="text" name="pic_name" id="inputPicName" placeholder="Nama Penanggung Jawab" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Status Aktif / Non-Aktif -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Keaktifan</label>
                    <select name="status" id="inputStatus" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-indigo-500">
                        <option value="active">Aktif</option>
                        <option value="inactive">Non-Aktif</option>
                    </select>
                </div>

                <!-- Tombol Submit / Aksi Form Modal -->
                <div class="pt-2 flex gap-3">
                    <button type="button" id="btnDeleteLocation" onclick="confirmDelete()" class="hidden flex-1 py-3 bg-rose-50 border border-rose-200 text-rose-600 font-black text-xs rounded-xl active:scale-95 transition-all text-center">
                        Hapus Lokasi
                    </button>
                    <button type="submit" class="flex-[2] py-3 bg-indigo-600 text-white font-black text-xs rounded-xl shadow-[0_4px_12px_rgba(79,70,229,0.2)] active:scale-95 transition-all text-center">
                        Simpan Data
                    </button>
                </div>
            </form>

            <!-- Hidden Form untuk pemrosesan metode DELETE -->
            <form id="deleteLocationForm" method="POST" action="" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<!-- ─── JAVASCRIPT LOGIC WORKER ─── -->
<script>
    const modal = document.getElementById('formModal');
    const backdrop = modal ? modal.querySelector('.bg-slate-900\\/60') : null;
    const panel = modal ? modal.querySelector('.bg-white') : null;
    const locationForm = document.getElementById('locationForm');
    const deleteForm = document.getElementById('deleteLocationForm');

    function openCreateModal() {
        if (!locationForm) return;

        locationForm.reset();
        document.getElementById('formMethod').value = 'POST';

        // PERBAIKAN: Ubah "_" menjadi "-" agar sesuai dengan Route web.php
        locationForm.action = "/mobile/asset-locations";

        document.getElementById('modalTitle').innerText = 'Tambah Lokasi Baru';
        document.getElementById('btnDeleteLocation').classList.add('hidden');

        showModalSystem();
    }

    function openEditModal(locationData) {
        if (!locationForm) return;

        locationForm.reset();
        document.getElementById('formMethod').value = 'PUT';

        // PERBAIKAN: Ubah "_" menjadi "-" agar sesuai dengan Route web.php
        const targetUrl = window.location.origin + `/mobile/asset-locations/${locationData.id}`;
        locationForm.action = targetUrl;
        if (deleteForm) {
            deleteForm.action = targetUrl;
        }

        document.getElementById('modalTitle').innerText = 'Ubah Detail Lokasi';
        document.getElementById('inputCode').value = locationData.code;
        document.getElementById('inputName').value = locationData.name;
        document.getElementById('inputBuilding').value = locationData.building;
        document.getElementById('inputFloor').value = locationData.floor;
        document.getElementById('inputRoom').value = locationData.room;
        document.getElementById('inputParentId').value = locationData.parent_id;
        document.getElementById('inputPicName').value = locationData.pic_name;
        document.getElementById('inputStatus').value = locationData.status;

        document.getElementById('btnDeleteLocation').classList.remove('hidden');

        showModalSystem();
    }

    function openEditModalFromElement(element) {
        try {
            const rawData = element.getAttribute('data-location');
            const locationData = JSON.parse(rawData);
            openEditModal(locationData);
        } catch (error) {
            console.error("Gagal memproses data lokasi:", error);
            alert("Terjadi kesalahan saat memuat data lokasi.");
        }
    }

    function showModalSystem() {
        if (!modal || !backdrop || !panel) return;

        modal.classList.remove('invisible');
        setTimeout(() => {
            backdrop.classList.replace('opacity-0', 'opacity-100');
            panel.classList.replace('translate-y-full', 'translate-y-0');
        }, 10);
    }

    function closeFormModal() {
        if (!modal || !backdrop || !panel) return;

        backdrop.classList.replace('opacity-100', 'opacity-0');
        panel.classList.replace('translate-y-0', 'translate-y-full');
        setTimeout(() => {
            modal.classList.add('invisible');
        }, 300);
    }

    function directDelete(locationId) {
        if (deleteForm) {
            deleteForm.action = window.location.origin + `/mobile/asset-locations/${locationId}`;
            confirmDelete();
        }
    }

    function confirmDelete() {
        if (deleteForm && confirm('Apakah Anda yakin ingin menghapus data master lokasi ini secara permanen?')) {
            deleteForm.submit();
        }
    }

    function searchLocation() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const cards = document.getElementsByClassName('location-card');

        for (let i = 0; i < cards.length; i++) {
            const name = cards[i].querySelector('.location-name').innerText.toLowerCase();
            const code = cards[i].querySelector('.location-code').innerText.toLowerCase();

            if (name.includes(input) || code.includes(input)) {
                cards[i].style.display = "";
            } else {
                cards[i].style.display = "none";
            }
        }
    }
</script>
@endsection