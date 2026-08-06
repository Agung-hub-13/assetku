@extends('layouts.admin')

@section('title', 'Manajemen Departemen Asset - AssetKu')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN DEPARTEMEN -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.05]"
            style="background-image: radial-gradient(#94a3b8 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
        <div class="text-slate-900 dark:text-white opacity-[0.03] dark:opacity-[0.02] transform -rotate-12 scale-125 md:scale-150 p-4">
            <!-- SVG Ikon Gedung Kantor / Departemen -->
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5" />
            </svg>
        </div>
    </div>

    <!-- Efek Premium Glow di Latar Belakang -->
    <div class="absolute top-0 right-1/4 w-72 sm:w-[500px] h-72 sm:h-[500px] bg-indigo-400/10 dark:bg-indigo-500/5 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none z-0"></div>

    <div class="relative z-10 max-w-7xl mx-auto space-y-6">

        <!-- HEADER SECTION -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Manajemen Departemen</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 font-medium">Kelola struktur departemen, alokasi aset, dan entitas pengguna.</p>
            </div>
            @can('asset-departments.create')
            <button onclick="openModal('create')" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-6 py-3.5 rounded-2xl transition-all shadow-lg shadow-indigo-500/25 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Departemen</span>
            </button>
            @endcan
        </div>

        <!-- TABEL DATA CARD -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-100/70 dark:bg-slate-800/50 border-b border-slate-200/80 dark:border-slate-800">
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Nama Departemen</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Statistik Relasi</th>
                            @canany(['asset-departments.edit', 'asset-departments.delete'])
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($departments as $dept)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group">
                            <!-- Kode -->
                            <td class="px-6 py-4 font-mono font-black text-xs text-indigo-600 dark:text-indigo-400">
                                <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100/50 dark:border-indigo-900/30 rounded-xl">
                                    {{ $dept->code }}
                                </span>
                            </td>

                            <!-- Nama -->
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white text-base">
                                {{ $dept->name }}
                            </td>

                            <!-- Relasi/Statistik -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-xs font-semibold">
                                    <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30 rounded-lg">
                                        {{ $dept->users_count ?? 0 }} User
                                    </span>
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/30 rounded-lg">
                                        {{ $dept->assets_count ?? 0 }} Asset
                                    </span>
                                    <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/30 rounded-lg">
                                        {{ $dept->locations_count ?? 0 }} Lokasi
                                    </span>
                                </div>
                            </td>

                            <!-- Action -->
                            @canany(['asset-departments.edit', 'asset-departments.delete'])
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1.5 sm:opacity-0 group-hover:opacity-100 transition-opacity">

                                    {{-- Tombol Edit --}}
                                    @can('asset-departments.edit')
                                    <button onclick="openModal('edit', {{ json_encode($dept) }})"
                                        class="p-2 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-xl hover:bg-amber-600 hover:text-white dark:hover:bg-amber-500 transition-all"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endcan

                                    {{-- Tombol Hapus --}}
                                    @can('asset-departments.delete')
                                    <button onclick="openDeleteModal('{{ route('admin.asset_departments.destroy', $dept->id) }}', '{{ $dept->name }}')"
                                        class="p-2 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-600 hover:text-white dark:hover:bg-rose-500 transition-all"
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
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 font-medium italic">
                                Belum ada data departemen.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if(is_object($departments) && method_exists($departments, 'hasPages') && $departments->hasPages())
            <div class="p-4 sm:p-6 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800">
                {{ $departments->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL CRUD (Create / Edit) -->
<div id="deptModal" class="fixed inset-0 z-[99] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300 max-h-[90vh] flex flex-col" id="modalContainer">

            <!-- Header Modal -->
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Tambah Departemen</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-full transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 overflow-y-auto space-y-4">
                <form id="deptForm" action="{{ route('admin.asset_departments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" id="methodField" name="_method" value="POST">

                    <div class="space-y-4">
                        {{-- Input Kode (Tampil Hanya Saat Edit) --}}
                        <div id="codeWrapper" class="space-y-1 hidden">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kode Departemen</label>
                            <input type="text" name="code" id="inputCode" readonly
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-sm font-mono font-bold cursor-not-allowed">
                        </div>

                        {{-- Nama Departemen --}}
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Departemen <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="inputName" required placeholder="Contoh: Information Technology"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        </div>
                     
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-base shadow-lg shadow-indigo-500/20 transition-all active:scale-95 mt-6">
                        Simpan Data Departemen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL HAPUS -->
<div id="deleteModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-3xl p-6 text-center shadow-2xl transform scale-95 opacity-0 transition-all duration-300" id="deleteContainer">
            <div class="w-16 h-16 bg-rose-50 dark:bg-rose-950/40 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-rose-100 dark:border-rose-900/40">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Hapus Departemen?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 font-medium">Departemen <span id="del_name" class="font-bold text-slate-800 dark:text-slate-200"></span> akan dihapus dari sistem.</p>

            <form id="deleteForm" method="POST" class="flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDelete()" class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Batal</button>
                <button type="submit" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold transition-all">Hapus</button>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('deptModal');
    const modalCont = document.getElementById('modalContainer');
    const form = document.getElementById('deptForm');
    const methodField = document.getElementById('methodField');
    const modalTitle = document.getElementById('modalTitle');
    const codeWrapper = document.getElementById('codeWrapper');

    const delModal = document.getElementById('deleteModal');
    const delCont = document.getElementById('deleteContainer');

    function openModal(type, data = null) {
        if (type === 'create') {
            modalTitle.innerText = 'Tambah Departemen Baru';
            form.action = "{{ route('admin.asset_departments.store') }}";
            methodField.value = 'POST';
            form.reset();
            codeWrapper.classList.add('hidden');
        } else if (type === 'edit') {
            modalTitle.innerText = 'Edit Detail Departemen';
            form.action = `/admin/asset_departments/${data.id}`;
            methodField.value = 'PUT';

            codeWrapper.classList.remove('hidden');
            document.getElementById('inputCode').value = data.code || '';
            document.getElementById('inputName').value = data.name || '';
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            modalCont.classList.remove('scale-95', 'opacity-0');
            modalCont.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal() {
        modalCont.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
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
        setTimeout(() => delModal.classList.add('hidden'), 200);
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