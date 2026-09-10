@extends('layouts.admin')

@section('content')
<div x-data="userManagement()" class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50/50 dark:bg-slate-950 transition-colors duration-300 overflow-x-hidden">

    <!-- 🌟 ELEMEN WATERMARK TRANSPARAN (USER MANAGEMENT & SECURITY) -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center select-none overflow-hidden">
        <!-- Dot Matrix Grid Pattern -->
        <div class="absolute inset-0 opacity-[0.12] dark:opacity-[0.06]"
            style="background-image: radial-gradient(#6366f1 1.2px, transparent 1.2px); background-size: 36px 36px;"></div>

        <!-- Watermark Graphic Vector -->
        <div class="text-indigo-900/10 dark:text-indigo-400/5 transform scale-100 sm:scale-110 md:scale-125 p-4 transition-transform duration-500">
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-xs">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800/50 mb-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>PT SLP Directory</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Manajemen Pengguna</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Kelola akun, hak akses role, dan multi-lokasi pengguna platform PT SLP dengan aman.</p>
            </div>
            <div class="shrink-0">
                <button @click="openCreateModal()"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 active:scale-[0.98] text-white font-semibold text-sm rounded-xl transition-all shadow-lg shadow-indigo-500/25 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Pengguna</span>
                </button>
            </div>
        </div>

        <!-- Alert Success -->
        @if(session('success'))
        <div class="p-4 bg-emerald-50/90 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60 rounded-2xl flex items-center justify-between text-emerald-800 dark:text-emerald-300 text-sm shadow-sm backdrop-blur-md">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        <!-- Alert Validation Error -->
        @if($errors->any())
        <div class="p-4 bg-rose-50/90 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-800/60 rounded-2xl text-rose-800 dark:text-rose-300 text-sm shadow-sm backdrop-blur-md">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="font-bold">Terjadi kesalahan input pada formulir:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 pl-11 text-xs sm:text-sm text-rose-700 dark:text-rose-400">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Data Display Container -->
        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">

            <!-- 1. MOBILE CARD VIEW -->
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($users as $user)
                @php
                    // Ambil lokasi user (asumsi relasi / accessor locations / location berupa array atau string)
                    $userLocations = method_exists($user, 'locations') ? $user->locations->pluck('name')->toArray() : (is_array($user->location ?? null) ? $user->location : explode(',', $user->location ?? ''));
                    $userLocations = array_filter(array_map('trim', $userLocations));
                @endphp
                <div class="p-4 space-y-3 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold flex items-center justify-center text-sm shadow-md shadow-indigo-500/20 shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-slate-800 dark:text-slate-100 truncate">{{ $user->name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</div>
                            </div>
                        </div>

                        <!-- Action Mobile Buttons -->
                        <div class="flex items-center gap-1 shrink-0">
                            @php
                                $locIds = method_exists($user, 'locations') ? $user->locations->pluck('id')->toArray() : [];
                                if(empty($locIds) && !empty($user->location)) {
                                    // Fallback jika berupa string comma-separated atau nama
                                    $locIds = $user->location; 
                                }
                            @endphp
                            <button @click="openEditModal({{ json_encode($user) }}, {{ json_encode($user->roles->pluck('id')) }}, {{ json_encode($locIds) }})"
                                class="p-2 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-xl transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button @click="openDeleteModal('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')"
                                class="p-2 text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-xl transition" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs pt-1 bg-slate-50/80 dark:bg-slate-800/50 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800/80">
                        <div>
                            <span class="text-slate-400 block font-medium mb-0.5">Departemen</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 shadow-2xs">
                                {{ $user->department->name ?? 'Tanpa Departemen' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-medium mb-0.5">Kontak</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $user->phone ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div>
                            <span class="text-slate-400 block text-xs font-semibold mb-1 uppercase tracking-wider">Lokasi Akses</span>
                            <div class="flex flex-wrap gap-1">
                                @forelse($userLocations as $loc)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ is_array($loc) ? ($loc['name'] ?? '') : $loc }}
                                </span>
                                @empty
                                <span class="text-xs text-slate-400 italic">Semua Lokasi / Tidak ada</span>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-xs font-semibold mb-1 uppercase tracking-wider">Role / Hak Akses</span>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($user->roles as $role)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50">
                                    {{ $role->name }}
                                </span>
                                @empty
                                <span class="text-xs text-slate-400 italic">Tanpa Role</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-slate-400 dark:text-slate-500 text-sm">
                    Belum ada data pengguna yang tersedia.
                </div>
                @endforelse
            </div>

            <!-- 2. DESKTOP TABLE VIEW -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/60 text-slate-400 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                            <th class="px-6 py-4">Pengguna</th>
                            <th class="px-6 py-4">Kontak</th>
                            <th class="px-6 py-4">Departemen</th>
                            <th class="px-6 py-4">Lokasi Akses</th>
                            <th class="px-6 py-4">Role / Akses</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700 dark:text-slate-200 divide-y divide-slate-100 dark:divide-slate-800/60">
                        @forelse($users as $user)
                        @php
                            $userLocations = method_exists($user, 'locations') ? $user->locations->pluck('name')->toArray() : (is_array($user->location ?? null) ? $user->location : explode(',', $user->location ?? ''));
                            $userLocations = array_filter(array_map('trim', $userLocations));

                            $locIds = method_exists($user, 'locations') ? $user->locations->pluck('id')->toArray() : [];
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors align-middle">
                            <!-- Name & Email -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold flex items-center justify-center text-sm shadow-md shadow-indigo-500/20 shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Phone -->
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300 font-medium">
                                {{ $user->phone ?? '-' }}
                            </td>

                            <!-- Department -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/50 shadow-2xs">
                                    {{ $user->department->name ?? '-' }}
                                </span>
                            </td>

                            <!-- Multi Locations -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @forelse($userLocations as $loc)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/40 dark:border-slate-700/50">
                                        {{ is_array($loc) ? ($loc['name'] ?? '') : $loc }}
                                    </span>
                                    @empty
                                    <span class="text-xs text-slate-400 italic">Semua Lokasi / Global</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Roles -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/50">
                                        {{ $role->name }}
                                    </span>
                                    @empty
                                    <span class="text-xs text-slate-400 italic">Tanpa Role</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="openEditModal({{ json_encode($user) }}, {{ json_encode($user->roles->pluck('id')) }}, {{ json_encode($locIds) }})"
                                        class="p-2 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/50 rounded-xl transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <button @click="openDeleteModal('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')"
                                        class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-xl transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                Belum ada data pengguna yang tersedia.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(method_exists($users, 'hasPages') && $users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
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
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">

        <div @click.away="isFormOpen = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden flex flex-col max-h-[85vh] my-auto">

            <!-- 1. Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/80 dark:bg-slate-800/50 shrink-0">
                <div>
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base sm:text-lg" x-text="isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru'"></h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Lengkapi informasi kredensial, role, dan multi-lokasi akses.</p>
                </div>
                <button @click="isFormOpen = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
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

                <!-- 2. Modal Body / Content -->
                <div class="p-6 space-y-4 overflow-y-auto flex-1 custom-scrollbar">

                    <!-- Nama -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required placeholder="Masukkan nama lengkap"
                            class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 dark:bg-slate-800/80 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition shadow-2xs">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" x-model="formData.email" required placeholder="nama@ptsip.co.id"
                            class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 dark:bg-slate-800/80 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition shadow-2xs">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Password <span x-show="isEdit" class="text-slate-400 lowercase font-normal">(Kosongkan jika tidak diubah)</span> <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password" :required="!isEdit" placeholder="••••••••"
                            class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 dark:bg-slate-800/80 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition shadow-2xs">
                    </div>

                    <!-- Phone & Department -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">No. Telepon</label>
                            <input type="text" name="phone" x-model="formData.phone" placeholder="08xxxxxxxxxx"
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 dark:bg-slate-800/80 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition shadow-2xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Departemen</label>
                            <select name="department_id" x-model="formData.department_id"
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-700 dark:bg-slate-800/80 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none transition shadow-2xs">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- MULTI LOKASI CHECKBOX (Pilih 1, 2, atau Semua Lokasi Sekaligus) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Akses Multi-Lokasi</label>
                            <button type="button" @click="toggleAllLocations()" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                <span x-text="isAllLocationsSelected() ? 'Batalkan Semua' : 'Pilih Semua Lokasi'"></span>
                            </button>
                        </div>
                        <div class="border border-slate-200 dark:border-slate-700/80 rounded-2xl p-3.5 bg-slate-50/50 dark:bg-slate-800/40 space-y-2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto custom-scrollbar pr-1">
                                @foreach($locations as $loc)
                                @php
                                    $locId = is_object($loc) ? ($loc->id ?? $loc->name) : $loc;
                                    $locName = is_object($loc) ? ($loc->name ?? $loc->id) : $loc;
                                @endphp
                                <label class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-100/80 dark:hover:bg-slate-800 text-sm text-slate-700 dark:text-slate-300 cursor-pointer select-none transition">
                                    <input type="checkbox" name="locations[]" value="{{ $locId }}" x-model="formData.locations"
                                        class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-700">
                                    <span class="font-medium truncate">{{ $locName }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Centang satu, beberapa, atau seluruh lokasi agar user memiliki hak akses operasional terkait.</p>
                    </div>

                    <!-- Roles Checkbox -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Role / Hak Akses <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-3.5 bg-slate-50/50 dark:bg-slate-800/40">
                            @foreach($roles as $role)
                            <label class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-100/80 dark:hover:bg-slate-800 text-sm text-slate-700 dark:text-slate-300 cursor-pointer select-none transition">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" x-model="formData.roles"
                                    class="w-4 h-4 rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-700">
                                <span class="font-medium">{{ $role->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- 3. Modal Footer -->
                <div class="px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 shrink-0">
                    <button type="button" @click="isFormOpen = false"
                        class="px-5 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold rounded-xl text-sm transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-semibold rounded-xl text-sm transition shadow-lg shadow-indigo-600/20">
                        Simpan Perubahan
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
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-md flex items-center justify-center p-4">

        <div @click.away="isDeleteOpen = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl my-auto space-y-4">

            <div class="w-14 h-14 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-lg">Hapus Pengguna?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Apakah Anda yakin ingin menghapus akun <span class="font-bold text-slate-800 dark:text-slate-200" x-text="deleteUserName"></span>? Tindakan ini tidak dapat dibatalkan.</p>
            </div>

            <form :action="deleteAction" method="POST" class="flex items-center justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="isDeleteOpen = false" class="px-5 py-2.5 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold rounded-xl text-sm transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-[0.98] text-white font-semibold rounded-xl text-sm transition shadow-lg shadow-rose-600/20">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

</div>

<!-- CSS tambahan custom scrollbar -->
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
            allLocationIds: @json($locations->map(fn($l) => is_object($l) ? ($l->id ?? $l->name) : $l)),
            formData: {
                id: null,
                name: '',
                email: '',
                phone: '',
                department_id: '',
                locations: [],
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
                    locations: [],
                    roles: []
                };
                this.isFormOpen = true;
            },

            openEditModal(user, userRoleIds, userLocationIds) {
                this.isEdit = true;
                this.formAction = `/admin/users/${user.id}`;
                this.formData = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    phone: user.phone ?? '',
                    department_id: user.department_id ?? '',
                    locations: userLocationIds ?? [],
                    roles: userRoleIds ?? []
                };
                this.isFormOpen = true;
            },

            openDeleteModal(actionUrl, userName) {
                this.deleteAction = actionUrl;
                this.deleteUserName = userName;
                this.isDeleteOpen = true;
            },

            isAllLocationsSelected() {
                if (this.allLocationIds.length === 0) return false;
                return this.allLocationIds.every(id => this.formData.locations.includes(id));
            },

            toggleAllLocations() {
                if (this.isAllLocationsSelected()) {
                    this.formData.locations = [];
                } else {
                    this.formData.locations = [...this.allLocationIds];
                }
            }
        }
    }
</script>
@endsection