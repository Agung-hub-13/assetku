@extends('layouts.admin')

@section('title', 'Manajemen Kategori Asset - AssetKu')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">

    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.05]"
            style="background-image: radial-gradient(#94a3b8 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
        <div class="text-slate-900 dark:text-white opacity-[0.03] dark:opacity-[0.02] transform -rotate-12 scale-125 md:scale-150 p-4">
            <svg class="w-96 h-96 md:w-[500px] md:h-[500px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.75" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M9 13h6m-3-3v6" />
            </svg>
        </div>
    </div>

    <!-- Efek Premium Glow di Latar Belakang -->
    <div class="absolute top-0 right-1/4 w-72 sm:w-[500px] h-72 sm:h-[500px] bg-indigo-400/10 dark:bg-indigo-500/5 rounded-full blur-[100px] sm:blur-[120px] pointer-events-none z-0"></div>

    <div class="relative z-10 max-w-7xl mx-auto space-y-6">

        <!-- HEADER SECTION -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="bg-white dark:bg-slate-900 px-6 py-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center gap-4 w-max">
                <div>
                    <p class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Kategori & Sub</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white leading-none mt-0.5">
                        {{ is_object($categories) && method_exists($categories, 'total') ? $categories->total() : count($categories) }}
                    </p>
                </div>
            </div>

            @can('asset-categories.create')
            <button onclick="openModal('create')"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white px-6 py-3.5 rounded-2xl font-bold transition-all shadow-lg shadow-indigo-500/25 active:scale-95">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Kategori</span>
            </button>
            @endcan
        </div>

        <!-- BAR FILTER & PENCARIAN -->
        <div class="bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm transition-colors">
            <form action="{{ url()->current() }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">

                <!-- Search Input -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pencarian</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama kategori / prefix..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    </div>
                </div>

                <!-- Type Filter (Parent / Child) -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tipe Hirarki</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                        </span>
                        <select name="type" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all appearance-none cursor-pointer">
                            <option value="">-- Semua Tipe --</option>
                            <option value="parent" {{ request('type') == 'parent' ? 'selected' : '' }}>Kategori Utama (Parent)</option>
                            <option value="child" {{ request('type') == 'child' ? 'selected' : '' }}>Sub-Kategori (Child)</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                </div>

                <!-- Submit / Reset Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-md active:scale-95 text-center flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Filter
                    </button>
                    <a href="{{ url()->current() }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-center flex items-center justify-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- TABEL DATA -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-100/70 dark:bg-slate-800/50 border-b border-slate-200/80 dark:border-slate-800">
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider w-16 text-center">No</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Kategori & Sub</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Prefix Kode</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider">Format Serial ({{ date('Y') }})</th>
                            @canany(['asset-categories.edit', 'asset-categories.delete'])
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($categories as $index => $category)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors group">
                            <!-- Index -->
                            <td class="px-6 py-4 text-center text-xs font-bold text-slate-400 dark:text-slate-500">
                                {{ (is_object($categories) && method_exists($categories, 'firstItem')) ? $categories->firstItem() + $index : $index + 1 }}
                            </td>

                            <!-- Nama Kategori & Parent Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if($category->parent)
                                    <span class="text-xs font-semibold px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-md">
                                        ↳ Sub dari {{ $category->parent->name }}
                                    </span>
                                    @else
                                    <span class="text-xs font-bold px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-md border border-emerald-200/50 dark:border-emerald-900/30">
                                        Induk (Parent)
                                    </span>
                                    @endif
                                </div>
                                <span class="text-slate-800 dark:text-slate-100 font-bold text-base block mt-1">{{ $category->name }}</span>
                            </td>

                            <!-- Prefix Kode -->
                            <td class="px-6 py-4">
                                @if($category->code_prefix)
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100/50 dark:border-indigo-900/30 tracking-wider font-mono">
                                    {{ $category->code_prefix }}
                                </span>
                                @else
                                <span class="text-xs text-slate-400 dark:text-slate-500 italic">-</span>
                                @endif
                            </td>

                            <!-- Format Serial -->
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400">
                                    {{ $category->code_prefix ?? 'AST' }}-{{ date('Y') }}-0001
                                </span>
                            </td>

                            <!-- Actions -->
                            @canany(['asset-categories.edit', 'asset-categories.delete'])
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1.5 sm:opacity-0 group-hover:opacity-100 transition-opacity">

                                    {{-- Tombol Edit --}}
                                    @can('asset-categories.edit')
                                    <button onclick="openEditModal({{ json_encode($category) }})"
                                        class="p-2 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-xl hover:bg-amber-600 hover:text-white dark:hover:bg-amber-500 transition-all"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endcan

                                    {{-- Tombol Delete --}}
                                    @can('asset-categories.delete')
                                    <button onclick="openDeleteModal('{{ route('admin.asset_categories.destroy', $category->id) }}', '{{ $category->name }}')"
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
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 font-medium italic">Belum ada data kategori aset.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if(is_object($categories) && method_exists($categories, 'hasPages') && $categories->hasPages())
            <div class="p-4 sm:p-6 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800">
                {{ $categories->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL CRUD -->
<div id="crudModal" class="fixed inset-0 z-[99] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-slate-900 w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300 max-h-[90vh] flex flex-col" id="modalContainer">

            <!-- Header Modal -->
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Tambah Kategori</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-full transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 overflow-y-auto space-y-4">
                <form id="crudForm" method="POST">
                    @csrf
                    <div id="methodField"></div>

                    <div class="space-y-4">
                        <!-- Induk Kategori (Parent) -->
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kategori Induk (Kosongkan Jika Utama)</label>
                            <select name="parent_id" id="cat_parent_id" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                                <option value="">-- Tidak Ada (Jadikan Kategori Utama) --</option>
                                @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Nama Kategori -->
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Kategori / Sub-Kategori</label>
                            <input type="text" name="name" id="cat_name" required placeholder="Contoh: Laptop, Mobil Operasional, Printer"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Prefix Kode -->
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Prefix Kode (Opsional, Maks 10 Karakter)</label>
                            <input type="text" name="code_prefix" id="cat_code_prefix" maxlength="10" placeholder="Contoh: LPT, MBL, PRN"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-mono font-bold uppercase tracking-wider text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 ml-1">Gunakan prefix khusus untuk penomoran otomatis aset.</p>
                        </div>                        
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-base shadow-lg shadow-indigo-500/20 transition-all active:scale-95 mt-6">
                        Simpan Data Kategori
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
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Hapus Kategori?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 font-medium">Kategori <span id="del_name" class="font-bold text-slate-800 dark:text-slate-200"></span> akan dihapus dari sistem.</p>

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
    const modal = document.getElementById('crudModal');
    const modalCont = document.getElementById('modalContainer');
    const delModal = document.getElementById('deleteModal');
    const delCont = document.getElementById('deleteContainer');

    function openModal(type) {
        const form = document.getElementById('crudForm');
        const method = document.getElementById('methodField');
        const title = document.getElementById('modalTitle');

        if (type === 'create') {
            title.innerText = 'Tambah Kategori Baru';
            form.action = "{{ route('admin.asset_categories.store') }}";
            method.innerHTML = '';
            form.reset();
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            modalCont.classList.remove('scale-95', 'opacity-0');
            modalCont.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function openEditModal(data) {
        const form = document.getElementById('crudForm');
        const method = document.getElementById('methodField');
        const title = document.getElementById('modalTitle');

        title.innerText = 'Edit Detail Kategori';
        form.action = `/admin/asset_categories/${data.id}`;
        method.innerHTML = `@method('PUT')`;

        // Mapping nilai kolom database ke form input modal
        document.getElementById('cat_parent_id').value = data.parent_id || '';
        document.getElementById('cat_name').value = data.name || '';
        document.getElementById('cat_code_prefix').value = data.code_prefix || '';

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
    document.addEventListener('DOMContentLoaded', function() {
        // Otomatis buka modal create/edit jika validasi form gagal
        openModal('create');
    });
</script>
@endif

@endsection