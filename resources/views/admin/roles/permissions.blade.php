@extends('layouts.admin')

@section('title', 'Matriks Hak Akses - AssetKu')

@section('content')
<div class="space-y-6">

    {{-- HEADER PAGE --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                <div class="p-2 bg-blue-500/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-xl">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                Matriks Hak Akses (Role & Permission)
            </h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Atur granularitas izin modul, fitur, dan tindakan untuk setiap role pengguna.
            </p>
        </div>
    </div>

    {{-- FILTER ROLE CARD --}}
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl p-4 md:p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <form action="{{ route('admin.roles.permissions') }}" method="GET" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="p-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <label for="role_id" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Role Terpilih
                    </label>
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200">
                        {{ $selectedRole ? $selectedRole->name : 'Pilih Role' }}
                    </span>
                </div>
            </div>

            <div class="w-full sm:w-72">
                <select name="role_id" id="role_id" onchange="this.form.submit()"
                    class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block p-2.5 transition-all cursor-pointer font-medium">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $selectedRole && $selectedRole->id == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($selectedRole)
    <form action="{{ route('admin.role-permissions.update') }}" method="POST">
        @csrf
        <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">

        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            
            {{-- CARD HEADER WITH ACTION BUTTON --}}
            <div class="p-4 md:p-5 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center px-3 py-1 text-xs font-bold rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                        Role: {{ $selectedRole->name }}
                    </span>
                    <p class="text-xs text-slate-500 dark:text-slate-400 hidden md:block">
                        Centang hak akses yang ingin diberikan ke role ini.
                    </p>
                </div>

                <button type="submit" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 rounded-xl shadow-md hover:shadow-lg transition-all focus:ring-2 focus:ring-blue-500/50">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>

            {{-- MATRIX TABLE --}}
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100/70 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th scope="col" class="py-3.5 px-4 md:px-6 w-1/4">Modul Utama</th>
                            <th scope="col" class="py-3.5 px-4 md:px-6 w-1/4">Fitur / Submenu</th>
                            <th scope="col" class="py-3.5 px-4 md:px-6">Daftar Permission (Hak Akses)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800/80 text-sm">
                        @foreach($groupedPermissions as $moduleName => $submodules)
                            @foreach($submodules as $submoduleName => $permissions)
                                @if($permissions->isNotEmpty())
                                    <tr x-data="{ 
                                            checkAll: false,
                                            toggleAll(el) {
                                                let checkboxes = $el.querySelectorAll('.perm-checkbox');
                                                checkboxes.forEach(cb => cb.checked = el.checked);
                                            }
                                        }"
                                        class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                                        
                                        @if($loop->first)
                                            <td rowspan="{{ count($submodules) }}" 
                                                class="py-4 px-4 md:px-6 font-bold text-slate-800 dark:text-slate-200 bg-slate-50/30 dark:bg-slate-900/40 border-r border-slate-200/80 dark:border-slate-800 align-top">
                                                <div class="sticky top-4 flex items-center gap-2">
                                                    <i data-lucide="folder-git-2" class="w-4 h-4 text-blue-500 shrink-0"></i>
                                                    <span>{{ $moduleName }}</span>
                                                </div>
                                            </td>
                                        @endif

                                        <td class="py-4 px-4 md:px-6 font-medium text-slate-700 dark:text-slate-300 align-top">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="flex items-center gap-2">
                                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                                                    {{ $submoduleName }}
                                                </span>
                                                {{-- Quick Check All for this row --}}
                                                <label class="inline-flex items-center gap-1 text-[11px] text-slate-400 hover:text-blue-500 cursor-pointer select-none">
                                                    <input type="checkbox" @change="toggleAll($event.target)" class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 w-3 h-3">
                                                    <span>Pilih Semua</span>
                                                </label>
                                            </div>
                                        </td>

                                        <td class="py-4 px-4 md:px-6">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                                @foreach($permissions as $perm)
                                                    @php
                                                        $action = explode('.', $perm->name)[1] ?? $perm->name;
                                                        
                                                        // Soft color coding per action type
                                                        $badgeStyle = match($action) {
                                                            'view' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                                            'create' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                                            'edit' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                                            'delete' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                                            'approve' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                                                            default => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20',
                                                        };
                                                    @endphp
                                                    <label for="perm_{{ $perm->id }}" 
                                                        class="flex items-center gap-2.5 p-2 rounded-xl border border-slate-200/60 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 hover:border-blue-500/40 hover:bg-blue-500/[0.02] dark:hover:bg-blue-500/[0.05] transition-all cursor-pointer group">
                                                        
                                                        <input type="checkbox" 
                                                            name="permissions[]" 
                                                            value="{{ $perm->name }}" 
                                                            id="perm_{{ $perm->id }}"
                                                            class="perm-checkbox rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer"
                                                            {{ in_array($perm->name, $rolePermissions) ? 'checked' : '' }}>

                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider border {{ $badgeStyle }}">
                                                            {{ $action }}
                                                        </span>

                                                        <span class="text-xs font-mono text-slate-500 dark:text-slate-400 truncate group-hover:text-slate-900 dark:group-hover:text-slate-200">
                                                            {{ $perm->name }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- FOOTER BUTTON --}}
            <div class="p-4 border-t border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-right">
                <button type="submit" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 rounded-xl shadow-md hover:shadow-lg transition-all focus:ring-2 focus:ring-blue-500/50">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan Permission</span>
                </button>
            </div>
        </div>
    </form>
    @endif

</div>
@endsection