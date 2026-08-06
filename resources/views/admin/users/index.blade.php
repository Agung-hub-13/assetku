@extends('layouts.admin')

@section('content')
<div x-data="userManagement()" class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300 overflow-x-hidden">

    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN (USER MANAGEMENT & SECURITY) -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <!-- Dot Matrix Grid Pattern -->
        <div class="absolute inset-0 opacity-[0.15] dark:opacity-[0.08]"
            style="background-image: radial-gradient(#e2e8f0 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>

        <!-- Watermark Graphic Vector -->
        <div class="text-slate-900 dark:text-white opacity-[0.035] dark:opacity-[0.025] transform scale-100 sm:scale-110 md:scale-125 p-4 transition-transform duration-500">
            <svg class="w-[320px] h-[320px] sm:w-[450px] sm:h-[450px] md:w-[600px] md:h-[600px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="11" stroke-width="0.3" stroke-dasharray="2 2" />
                <circle cx="12" cy="12" r="9.5" stroke-width="0.5" />
                <path stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M12 3C7 3 4 5 4 8v5c0 5.25 4.5 8 8 9 3.5-1 8-3.75 8-9V8c0-3-3-5-8-5z" />
                <circle cx="12" cy="10" r="2" stroke-width="0.8" />
                <path stroke-width="0.8" stroke-linecap="round" d="M9 15c0-1.657 1.343-3 3-3s3 1.343 3 3" />
                <circle cx="8" cy="11" r="1.3" stroke-width="0.6" />
                <path stroke-width="0.6" stroke-linecap="round" d="M6 15c0-1.105.895-2 2-2 .418 0 .804.128 1.125.347" />
                <circle cx="16" cy="11" r="1.3" stroke-width="0.6" />
                <path stroke-width="0.6" stroke-linecap="round" d="M18 15c0-1.105-.895-2-2-2-.418 0-.804.128-1.125.347" />
                <path stroke-width="0.5" stroke-dasharray="1 1" d="M12 1v2M12 21v2M1 12h2M21 12h2" />
            </svg>
        </div>
    </div>

    <!-- Main Content Container (Di atas watermark) -->
    <div class="relative z-10 max-w-7xl mx-auto space-y-6">

        <!-- Header Page -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Manajemen Pengguna</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola akun, hak akses role, dan departemen pengguna platform PT SLP.</p>
            </div>
            <div class="shrink-0">
                <button @click="openCreateModal()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-medium text-sm rounded-xl transition-all shadow-sm shadow-indigo-200 dark:shadow-none focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Pengguna</span>
                </button>
            </div>
        </div>

        <!-- Alert Success -->
        @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl flex items-center justify-between text-emerald-800 dark:text-emerald-300 text-sm shadow-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        <!-- Alert Validation Error -->
        @if($errors->any())
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-xl text-rose-800 dark:text-rose-300 text-sm shadow-sm">
            <div class="font-semibold mb-1 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Terjadi kesalahan input:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 pl-1 text-xs sm:text-sm">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Data Display Container -->
        <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">

            <!-- 1. MOBILE CARD VIEW (Tampil Hanya di Layar Kecil < md) -->
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($users as $user)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-center text-sm ring-2 ring-indigo-50 dark:ring-indigo-900/20 shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $user->name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</div>
                            </div>
                        </div>

                        <!-- Action Mobile Buttons -->
                        <div class="flex items-center gap-1 shrink-0">
                            <button @click="openEditModal({{ json_encode($user) }}, {{ json_encode($user->roles->pluck('id')) }})"
                                class="p-2 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button @click="openDeleteModal('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')"
                                class="p-2 text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                        <div>
                            <span class="text-slate-400 block font-medium">Departemen</span>
                            <span class="inline-flex items-center px-2 py-0.5 mt-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $user->department->name ?? 'Tanpa Departemen' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium">Kontak</span>
                            <span class="text-slate-600 dark:text-slate-300">{{ $user->phone ?? '-' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-xs font-medium mb-1">Role / Akses</span>
                        <div class="flex flex-wrap gap-1">
                            @forelse($user->roles as $role)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50">
                                {{ $role->name }}
                            </span>
                            @empty
                            <span class="text-xs text-slate-400 italic">Tanpa Role</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 dark:text-slate-500 text-sm">
                    Belum ada data pengguna.
                </div>
                @endforelse
            </div>

            <!-- 2. DESKTOP TABLE VIEW (Tampil di Layar Desktop >= md) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                            <th class="px-6 py-4">Pengguna</th>
                            <th class="px-6 py-4">Kontak</th>
                            <th class="px-6 py-4">Departemen</th>
                            <th class="px-6 py-4">Role / Akses</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700 dark:text-slate-200 divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors align-middle">
                            <!-- Name & Email -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-center text-sm ring-2 ring-indigo-50 dark:ring-indigo-900/20 shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Phone -->
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                {{ $user->phone ?? '-' }}
                            </td>

                            <!-- Department -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/50">
                                    {{ $user->department->name ?? 'Tanpa Departemen' }}
                                </span>
                            </td>

                            <!-- Roles -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50">
                                        {{ $role->name }}
                                    </span>
                                    @empty
                                    <span class="text-xs text-slate-400 italic">Tanpa Role</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button @click="openEditModal({{ json_encode($user) }}, {{ json_encode($user->roles->pluck('id')) }})"
                                        class="p-2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <button @click="openDeleteModal('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')"
                                        class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                Belum ada data pengguna.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(method_exists($users, 'hasPages') && $users->hasPages())
            <div class="px-4 py-3 sm:px-6 sm:py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL CREATE / EDIT                        -->
    <!-- ========================================== -->
    <div x-show="isFormOpen" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">

        <div @click.away="isFormOpen = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden flex flex-col max-h-[85vh] my-auto">

            <!-- 1. Modal Header (Fixed / Floating di Atas) -->
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50 shrink-0">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base sm:text-lg" x-text="isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru'"></h3>
                <button @click="isFormOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form Wrapper -->
            <form :action="formAction" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- 2. Modal Body / Content (Hanya bagian ini yang scrollable) -->
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 custom-scrollbar">

                    <!-- Nama -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" x-model="formData.name" required
                            class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">Email *</label>
                        <input type="email" name="email" x-model="formData.email" required
                            class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">
                            Password <span x-show="isEdit" class="text-slate-400 lowercase font-normal">(Kosongkan jika tidak diubah)</span> *
                        </label>
                        <input type="password" name="password" :required="!isEdit"
                            class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition">
                    </div>

                    <!-- Phone & Department -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">No. Telepon</label>
                            <input type="text" name="phone" x-model="formData.phone"
                                class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-1">Departemen</label>
                            <select name="department_id" x-model="formData.department_id"
                                class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Roles Checkbox -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase mb-2">Pilih Role / Hak Akses *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 border border-slate-200 dark:border-slate-700 rounded-xl p-3 bg-slate-50/50 dark:bg-slate-800/50">
                            @foreach($roles as $role)
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300 cursor-pointer select-none hover:text-indigo-600">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" x-model="formData.roles"
                                    class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-700">
                                <span>{{ $role->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- 3. Modal Footer (Fixed / Floating di Bawah) -->
                <div class="p-4 sm:p-5 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 shrink-0">
                    <button type="button" @click="isFormOpen = false"
                        class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium rounded-xl text-sm transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl text-sm transition shadow-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL CONFIRM DELETE                       -->
    <!-- ========================================== -->
    <div x-show="isDeleteOpen" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">

        <div @click.away="isDeleteOpen = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-sm w-full p-6 text-center shadow-2xl my-auto">

            <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-lg mb-1">Hapus Pengguna?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Apakah Anda yakin ingin menghapus akun <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="deleteUserName"></span>?</p>

            <form :action="deleteAction" method="POST" class="flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="isDeleteOpen = false" class="px-4 py-2 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium rounded-xl text-sm transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-medium rounded-xl text-sm transition shadow-sm">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

</div>

<!-- CSS tambahan untuk custom scrollbar yang slim & halus -->
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }
</style>

<!-- Alpine.js Logic -->
<script>
    function userManagement() {
        return {
            isFormOpen: false,
            isDeleteOpen: false,
            isEdit: false,
            formAction: '',
            deleteAction: '',
            deleteUserName: '',
            formData: {
                id: null,
                name: '',
                email: '',
                phone: '',
                department_id: '',
                roles: []
            },

            openCreateModal() {
                this.isEdit = false;
                this.formAction = "{{ route('admin.users.store') }}";
                this.formData = {
                    id: null,
                    name: '',
                    email: '',
                    phone: '',
                    department_id: '',
                    roles: []
                };
                this.isFormOpen = true;
            },

            openEditModal(user, userRoleIds) {
                this.isEdit = true;
                this.formAction = `/admin/users/${user.id}`;
                this.formData = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    phone: user.phone ?? '',
                    department_id: user.department_id ?? '',
                    roles: userRoleIds
                };
                this.isFormOpen = true;
            },

            openDeleteModal(actionUrl, userName) {
                this.deleteAction = actionUrl;
                this.deleteUserName = userName;
                this.isDeleteOpen = true;
            }
        }
    }
</script>
@endsection