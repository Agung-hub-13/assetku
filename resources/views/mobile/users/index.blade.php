@extends('layouts.mobile')

@section('title', 'Manajemen Pengguna - AssetKu')

@section('content')
<!-- ─── TOP HEADER BAR ─── -->
<header class="absolute top-0 left-0 right-0 z-40 bg-gradient-to-b from-slate-900 via-slate-900/80 to-transparent px-4 pt-6 pb-12 flex items-center justify-between text-white">
    <div class="flex items-center gap-3">
        <div>
            <h2 class="text-sm font-black mt-1 tracking-tight">Manajemen User</h2>
        </div>
    </div>

    <!-- Tombol Tambah Pengguna Baru -->
    <button type="button" onclick="openCreateModal()" class="w-10 h-10 rounded-xl bg-indigo-600 border border-indigo-500/30 flex items-center justify-center text-white shadow-[0_4px_12px_rgba(79,70,229,0.3)] active:scale-90 transition-all">
        <i class="fa-solid fa-user-plus text-sm"></i>
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

    <!-- ─── PILAR SEARCH BAR ─── -->
    <div class="bg-white p-3 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.02)] border border-slate-100/80 flex gap-2">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" id="searchInput" onkeyup="searchUser()" placeholder="Cari nama atau email pengguna..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 transition-colors">
        </div>
    </div>

    <!-- ─── TOTAL ACCOUNT SUMMARY CAPSULE ─── -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar py-1 -mx-4 px-4">
        <button class="px-4 py-1.5 rounded-full bg-indigo-600 text-white text-[11px] font-black shadow-sm shrink-0">
            Semua Akun ({{ $users instanceof \Illuminate\Pagination\LengthAwarePaginator ? $users->total() : $users->count() }})
        </button>
    </div>

    <!-- ─── LIST DAFTAR USERS (CARDS LOOPING) ─── -->
    <div class="space-y-3" id="userListContainer">

        @forelse ($users as $user)
        @php
        $words = explode(' ', $user->name);
        $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

        $hasDesktop = $user->hasPermissionTo('access-desktop');
        $hasMobile = $user->hasPermissionTo('access-mobile');
        $userRole = $user->roles->first()->id ?? '';

        $userPermissions = $user->permissions->whereNotIn('name', ['access-desktop', 'access-mobile'])->pluck('name')->toArray();

        // Menggunakan json_encode yang aman dari distorsi spasi / tanda kutip di HTML attribute
        $jsonPayload = json_encode([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $userRole,
        'hasDesktop' => $hasDesktop,
        'hasMobile' => $hasMobile,
        'permissions' => $userPermissions
        ], JSON_HEX_APOS | JSON_HEX_QUOT);
        @endphp

        <div class="user-card bg-white border border-slate-100/80 rounded-2xl p-4 shadow-[0_4px_12px_rgba(0,0,0,0.01)] flex items-center justify-between gap-3 relative overflow-hidden border-b-[3px] border-b-slate-100 transition-colors">

            <!-- Sisi Kiri: Informasi User -->
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <!-- Avatar / Inisial Bundar -->
                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-600 border-2 border-white text-white flex items-center justify-center text-xs font-black tracking-wider shadow-sm shrink-0">
                    {{ $initials }}
                </div>

                <!-- Konten Rincian Data Utama Pengguna -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="user-name text-xs font-black text-slate-800 truncate">
                            {{ $user->name }}
                        </h3>
                        <span class="text-[8px] font-black px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-600 uppercase tracking-wide shrink-0">
                            {{ $user->roles->first()->name ?? 'No Role' }}
                        </span>
                    </div>

                    <p class="user-email text-[10px] text-slate-400 font-semibold truncate mt-0.5">
                        {{ $user->email }}
                    </p>

                    <!-- Akses Platform Tags Indicator -->
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="text-[8px] font-bold text-slate-400">Platform:</span>
                        <span class="inline-flex items-center gap-0.5 text-[8px] font-bold px-1.5 py-0.5 rounded-full {{ $hasDesktop ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400 line-through' }}">
                            <i class="fa-solid fa-desktop text-[7px]"></i> PC
                        </span>
                        <span class="inline-flex items-center gap-0.5 text-[8px] font-bold px-1.5 py-0.5 rounded-full {{ $hasMobile ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400 line-through' }}">
                            <i class="fa-solid fa-mobile-screen text-[7px]"></i> Mobile
                        </span>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Tombol Aksi Mobile Quick Action -->
            <div class="flex items-center gap-1.5 shrink-0 pl-2 border-l border-slate-100">
                <!-- Tombol Edit -->
                <button type="button"
                    data-user="{{ $jsonPayload }}"
                    onclick="openEditModalFromElement(this)"
                    class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 active:scale-90 transition-all">
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                </button>

                <!-- Tombol Delete Langsung -->
                <button type="button" onclick="directDelete({{ $user->id }})" class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 active:scale-90 transition-all">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </div>
        </div>
        @empty
        <div class="bg-white border border-slate-100 rounded-2xl p-8 text-center">
            <i class="fa-solid fa-users-slash text-slate-300 text-3xl mb-2"></i>
            <p class="text-xs font-bold text-slate-400">Tidak ada pengguna terdaftar.</p>
        </div>
        @endforelse

    </div>

    <!-- ─── PAGINATION SECTION ─── -->
    @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
    <div class="mt-4 mobile-pagination">
        {{ $users->links() }}
    </div>
    @endif

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
                <h3 id="modalTitle" class="text-sm font-black text-slate-800">Tambah Pengguna</h3>
                <button type="button" onclick="closeFormModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>

            <!-- Formulir Utama Terpadu -->
            <form id="userForm" method="POST" action="{{ route('mobile.users.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="POST">

                <!-- Input Nama -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="inputName" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Input Email -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Alamat Email</label>
                    <input type="email" name="email" id="inputEmail" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-500">
                </div>

                <!-- Pilihan Role -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Peran (Role)</label>
                    <select name="role" id="inputRole" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:border-indigo-500">
                        <option value="">-- Pilih Peran --</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelompok Akses Platform Perangkat -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Otorisasi Platform</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center justify-between p-2.5 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer active:bg-slate-100">
                            <span class="text-xs font-bold text-slate-700"><i class="fa-solid fa-desktop text-slate-400 mr-1.5"></i> Desktop PC</span>
                            <input type="checkbox" name="platforms[]" value="access-desktop" id="platformDesktop" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        </label>
                        <label class="flex items-center justify-between p-2.5 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer active:bg-slate-100">
                            <span class="text-xs font-bold text-slate-700"><i class="fa-solid fa-mobile-screen text-slate-400 mr-1.5"></i> Mobile App</span>
                            <input type="checkbox" name="platforms[]" value="access-mobile" id="platformMobile" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        </label>
                    </div>
                </div>

                <!-- Input Password & Konfirmasi -->
                <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl space-y-3">
                    <p id="passwordHint" class="text-[9px] text-slate-400 font-semibold leading-relaxed hidden">Kosongkan kolom sandi jika tidak berniat mengubah kunci enkripsi.</p>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kata Sandi</label>
                        <input type="password" name="password" id="inputPassword" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Konfirmasi Kata Sandi</label>
                        <input type="password" name="password_confirmation" id="inputPasswordConfirmation" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <!-- Opsi Tambahan Modul Khusus -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Izin Khusus Tambahan (Opsional)</label>
                    <div class="max-h-32 overflow-y-auto space-y-2 pr-1 border border-slate-100 p-2 rounded-xl bg-slate-50/50">
                        @foreach($permissions as $perm)
                        <label class="flex items-center gap-2 px-1">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" data-perm-name="{{ $perm->name }}" class="permission-checkbox w-3.5 h-3.5 text-indigo-600 border-slate-300 rounded">
                            <span class="text-[11px] font-semibold text-slate-600">{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Tombol Submit / Aksi Form Modal -->
                <div class="pt-2 flex gap-3">
                    <button type="button" id="btnDeleteUser" onclick="confirmDelete()" class="hidden flex-1 py-3 bg-rose-50 border border-rose-200 text-rose-600 font-black text-xs rounded-xl active:scale-95 transition-all text-center">
                        Hapus Akun
                    </button>
                    <button type="submit" class="flex-[2] py-3 bg-indigo-600 text-white font-black text-xs rounded-xl shadow-[0_4px_12px_rgba(79,70,229,0.2)] active:scale-95 transition-all text-center">
                        Simpan Data
                    </button>
                </div>
            </form>

            <!-- Hidden Form untuk pemrosesan metode DELETE -->
            <form id="deleteUserForm" method="POST" action="" class="hidden">
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
    const userForm = document.getElementById('userForm');
    const deleteForm = document.getElementById('deleteUserForm');

    function openCreateModal() {
        if (!userForm) return;

        userForm.reset();
        document.getElementById('formMethod').value = 'POST';

        // Menggunakan penulisan base path yang konsisten
        userForm.action = "/mobile/users";

        document.getElementById('modalTitle').innerText = 'Tambah Pengguna';
        document.getElementById('inputPassword').required = true;
        document.getElementById('passwordHint').classList.add('hidden');
        document.getElementById('btnDeleteUser').classList.add('hidden');

        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        showModalSystem();
    }

    function openEditModal(userData) {
        if (!userForm) return;

        userForm.reset();
        document.getElementById('formMethod').value = 'PUT';

        // Harmonisasi endpoint URL string agar dibaca utuh sebagai request AJAX/Form POST oleh Laravel
        const targetUrl = window.location.origin + `/mobile/users/${userData.id}`;
        userForm.action = targetUrl;
        if (deleteForm) {
            deleteForm.action = targetUrl;
        }

        document.getElementById('modalTitle').innerText = 'Ubah Detail Pengguna';
        document.getElementById('inputName').value = userData.name;
        document.getElementById('inputEmail').value = userData.email;
        document.getElementById('inputRole').value = userData.role;

        document.getElementById('platformDesktop').checked = userData.hasDesktop;
        document.getElementById('platformMobile').checked = userData.hasMobile;

        document.getElementById('inputPassword').required = false;
        document.getElementById('passwordHint').classList.remove('hidden');
        document.getElementById('btnDeleteUser').classList.remove('hidden');

        document.querySelectorAll('.permission-checkbox').forEach(cb => {
            const name = cb.getAttribute('data-perm-name');
            cb.checked = userData.permissions.includes(name);
        });

        showModalSystem();
    }

    // Fungsi baru untuk menjembatani element HTML dengan fungsi modal utama
function openEditModalFromElement(element) {
    try {
        const rawData = element.getAttribute('data-user');
        const userData = JSON.parse(rawData);
        
        // Panggil fungsi openEditModal bawaan Anda yang sudah ada sebelumnya
        openEditModal(userData);
    } catch (error) {
        console.error("Gagal memproses data pengguna:", error);
        alert("Terjadi kesalahan saat memuat data pengguna.");
    }
}

    function directDelete(userId) {
        if (deleteForm) {
            deleteForm.action = window.location.origin + `/mobile/users/${userId}`;
            confirmDelete();
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

    function confirmDelete() {
        if (deleteForm && confirm('Apakah Anda yakin ingin menghapus akun pengguna ini secara permanen?')) {
            deleteForm.submit();
        }
    }

    function searchUser() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const cards = document.getElementsByClassName('user-card');

        for (let i = 0; i < cards.length; i++) {
            const name = cards[i].querySelector('.user-name').innerText.toLowerCase();
            const email = cards[i].querySelector('.user-email').innerText.toLowerCase();

            if (name.includes(input) || email.includes(input)) {
                cards[i].style.display = "";
            } else {
                cards[i].style.display = "none";
            }
        }
    }
</script>
@endsection