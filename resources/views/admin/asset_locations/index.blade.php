@extends('layouts.admin')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300 w-full">

    <!-- ELEMEN WATERMARK TRANSPARAN -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.04]"
            style="background-image: radial-gradient(#94a3b8 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
        <div class="text-slate-900 dark:text-white opacity-[0.02] dark:opacity-[0.02] transform scale-125 md:scale-150 p-4">
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="0.5" stroke-dasharray="4 4" />
                <circle cx="12" cy="12" r="7.5" stroke-width="0.75" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M12 2v4m0 12v4M2 12h4m12 0h4" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M12 8.5c-1.5 0-3.5 1-3.5 3.5 0 2.5 2 4 3.5 4.5 1.5-.5 3.5-2 3.5-4.5 0-2.5-2-3.5-3.5-3.5z" />
            </svg>
        </div>
    </div>

    <!-- Efek Glow Light -->
    <div class="absolute top-0 left-1/4 w-72 sm:w-[500px] h-72 sm:h-[500px] bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none z-0"></div>

    <!-- Container Full Width -->
    <div class="relative z-10 w-full space-y-6">

        <!-- HEADER SECTION -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm text-blue-600 dark:text-blue-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Lokasi Aset</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">Kelola penanggung jawab, area, lantai, ruangan, dan pemetaan lokasi fisik aset.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden md:flex bg-white dark:bg-slate-900 px-4 py-2.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm items-center gap-3">
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total</span>
                    <span class="px-2.5 py-0.5 bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 text-sm font-black rounded-lg border border-blue-200/50 dark:border-blue-800/50">{{ $locations->total() }}</span>
                </div>

                @can('asset-locations.create')
                <button type="button" onclick="openModal('create')"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-md shadow-blue-500/20 active:scale-95 cursor-pointer text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Lokasi</span>
                </button>
                @endcan
            </div>
        </div>

        <!-- BAR FILTER & PENCARIAN -->
        <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <form action="{{ url()->current() }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">

                <div class="lg:col-span-8 space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider ml-1">Pencarian</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode, nama, atau area..."
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider ml-1">Status</label>
                    <select name="status" class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-xl text-sm font-semibold transition-all shadow-sm active:scale-95 text-center cursor-pointer">
                        Filter
                    </button>
                    @if(request()->has('search') || request()->has('status'))
                    <a href="{{ url()->current() }}" class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all" title="Reset Filter">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABEL DATA -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden w-full">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse min-w-[850px]">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200/80 dark:border-slate-800 text-slate-400 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                            <th class="px-6 py-3.5">Info Lokasi</th>
                            <th class="px-6 py-3.5">Area</th>
                            <th class="px-6 py-3.5">Lantai</th>
                            <th class="px-6 py-3.5">Ruangan</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            @canany(['asset-locations.edit', 'asset-locations.delete'])
                            <th class="px-6 py-3.5 text-right">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($locations as $location)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group">

                            <!-- Info Lokasi -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-0.5">
                                    <span class="inline-flex items-center w-max px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 font-bold text-[10px] tracking-wide border border-blue-200/40 dark:border-blue-800/40">
                                        {{ $location->code ?? 'AUTO-GENERATED' }}
                                    </span>
                                    <span class="text-slate-900 dark:text-slate-100 font-semibold text-sm mt-1">{{ $location->name }}</span>
                                    @if($location->address)
                                    <span class="text-xs text-slate-400 dark:text-slate-500 line-clamp-1 font-normal">{{ $location->address }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Area -->
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                    {{ $location->building ?? '-' }}
                                </span>
                            </td>

                            <!-- Lantai -->
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                    {{ $location->floor ? 'Lt. ' . $location->floor : '-' }}
                                </span>
                            </td>

                            <!-- Ruangan -->
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                    {{ $location->room ?? '-' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">
                                @if($location->status == 'active')
                                <span class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border border-emerald-200/60 dark:border-emerald-800/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border border-slate-200/80 dark:border-slate-700/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            @canany(['asset-locations.edit', 'asset-locations.delete'])
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1">

                                    {{-- Tombol Edit --}}
                                    @can('asset-locations.edit')
                                    <button type="button" onclick='openEditModal(@json($location))'
                                        class="p-1.5 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/50 rounded-lg transition-all cursor-pointer"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endcan

                                    {{-- Tombol Hapus --}}
                                    @can('asset-locations.delete')
                                    <button type="button" onclick="openDeleteModal('{{ route('admin.asset_locations.destroy', $location->id) }}', '{{ e($location->name) }}')"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-lg transition-all cursor-pointer"
                                        title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endcan

                                </div>
                            </td>
                            @endcanany
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 font-medium text-sm italic">Belum ada data lokasi aset yang sesuai.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($locations->hasPages())
            <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
                {{ $locations->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL FORM (CREATE & EDIT) -->
<div id="crudModal" class="fixed inset-0 z-[99] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4 pointer-events-none">
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden transform transition-all scale-95 opacity-0 duration-200 max-h-[90vh] flex flex-col pointer-events-auto" id="modalContainer">

            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
                <h3 id="modalTitle" class="text-base font-bold text-slate-900 dark:text-white">Tambah Lokasi</h3>
                <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg transition-all cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body Modal (Form) -->
            <div class="p-6 overflow-y-auto space-y-4">
                <form id="crudForm" method="POST" action="{{ route('admin.asset_locations.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            Nama Lokasi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="loc_name" required placeholder="Contoh: Gedung Utama / Ruang Server"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Area</label>
                            <input type="text" name="building" id="loc_building" placeholder="Area A" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lantai</label>
                            <input type="text" name="floor" id="loc_floor" placeholder="3" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ruangan</label>
                            <input type="text" name="room" id="loc_room" placeholder="R.302" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Fisik</label>
                        <textarea name="address" id="loc_address" rows="2" placeholder="Detail alamat lokasi..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status <span class="text-rose-500">*</span></label>
                        <select name="status" id="loc_status" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" id="submitBtn" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm shadow-md shadow-blue-500/20 transition-all active:scale-95 cursor-pointer mt-2">
                        Simpan Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI HAPUS -->
<div id="deleteModal" class="fixed inset-0 z-[99] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden p-6 text-center space-y-4">
            <div class="w-12 h-12 bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 rounded-2xl flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Hapus Lokasi Aset?</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Anda yakin ingin menghapus lokasi <strong id="deleteTargetName" class="text-slate-800 dark:text-slate-200"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <form id="deleteForm" method="POST" class="flex items-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-semibold text-sm transition-all cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-semibold text-sm transition-all shadow-md shadow-rose-500/20 active:scale-95 cursor-pointer">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT PENGELOLAAN MODAL -->
<script>
    function openModal(mode, data = null) {
        const modal = document.getElementById('crudModal');
        const container = document.getElementById('modalContainer');
        const form = document.getElementById('crudForm');
        const formMethod = document.getElementById('formMethod');
        const modalTitle = document.getElementById('modalTitle');

        form.reset();

        if (mode === 'create') {
            modalTitle.textContent = 'Tambah Lokasi Aset';
            form.action = "{{ route('admin.asset_locations.store') }}";
            formMethod.value = "POST";
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function openEditModal(location) {
        const modal = document.getElementById('crudModal');
        const container = document.getElementById('modalContainer');
        const form = document.getElementById('crudForm');
        const formMethod = document.getElementById('formMethod');
        const modalTitle = document.getElementById('modalTitle');

        modalTitle.textContent = 'Edit Lokasi Aset';
        form.action = `/admin/asset_locations/${location.id}`;
        formMethod.value = "PUT";

        document.getElementById('loc_name').value = location.name || '';
        document.getElementById('loc_building').value = location.building || '';
        document.getElementById('loc_floor').value = location.floor || '';
        document.getElementById('loc_room').value = location.room || '';
        document.getElementById('loc_address').value = location.address || '';
        document.getElementById('loc_status').value = location.status || 'active';

        modal.classList.remove('hidden');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('crudModal');
        const container = document.getElementById('modalContainer');

        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    function openDeleteModal(actionUrl, name) {
        document.getElementById('deleteForm').action = actionUrl;
        document.getElementById('deleteTargetName').textContent = name;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>

@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        openModal('create');
    });
</script>
@endif

@endsection