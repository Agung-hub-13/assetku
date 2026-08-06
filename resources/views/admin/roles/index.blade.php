@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-[#0f172a] text-slate-200 p-6 md:p-8 font-sans" x-data="{ createModal: false, editModal: false, editRole: { id: '', name: '' } }">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Role Management</h1>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mt-1 font-medium">
                <span>Dashboard</span>
                <span>•</span>
                <span class="text-slate-500">Roles</span>
            </nav>
        </div>

        <div class="flex items-center gap-3">
            <!-- LINK KE PROFILE TO MENU MATRIX -->
            <a href="{{ route('admin.roles.permissions') }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold px-4 py-2.5 rounded-lg border border-slate-700 transition-all">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Profile To Menu (Matrix)
            </a>

            <!-- ADD ROLE BUTTON -->
            <button @click="createModal = true" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm px-4 py-2.5 rounded-lg shadow-lg shadow-blue-500/20 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Role
            </button>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- ROLES TABLE CARD -->
    <div class="bg-[#1e293b]/70 border border-slate-800 rounded-xl shadow-2xl p-6 backdrop-blur-md">
        <div class="overflow-x-auto rounded-lg border border-slate-800/80">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#111827]/60 text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-800">
                        <th class="py-3.5 px-4 w-16 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Role</th>
                        <th class="py-3.5 px-4 text-center">Total Pengguna</th>
                        <th class="py-3.5 px-4 text-center">Akses Hak Menu</th>
                        <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-sm font-medium text-slate-300">
                    @forelse($roles as $index => $role)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-bold">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4 font-semibold text-white">
                                {{ ucfirst($role->name) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 bg-slate-800 text-slate-300 rounded-full text-xs font-semibold border border-slate-700">
                                    {{ $role->users_count }} Users
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.roles.permissions', ['role_id' => $role->id]) }}" class="inline-flex items-center gap-1.5 text-xs text-blue-400 hover:text-blue-300 font-semibold underline underline-offset-4">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Atur Permission
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- EDIT -->
                                    <button @click="editModal = true; editRole = { id: '{{ $role->id }}', name: '{{ $role->name }}' }" class="p-1.5 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <!-- DELETE -->
                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">Belum ada data Role.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL CREATE ROLE -->
    <div x-show="createModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" x-cloak>
        <div @click.away="createModal = false" class="bg-[#1e293b] border border-slate-800 rounded-xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-white mb-4">Tambah Role Baru</h3>
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nama Role</label>
                    <input type="text" name="name" required placeholder="Contoh: Manager, HRD, Supervisor" 
                        class="w-full bg-[#111827] border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg text-xs font-semibold hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-500">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT ROLE -->
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" x-cloak>
        <div @click.away="editModal = false" class="bg-[#1e293b] border border-slate-800 rounded-xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-white mb-4">Edit Nama Role</h3>
            <form :action="'{{ url('admin/roles') }}/' + editRole.id" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-400 mb-1">Nama Role</label>
                    <input type="text" name="name" x-model="editRole.name" required 
                        class="w-full bg-[#111827] border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 rounded-lg text-xs font-semibold hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-500">Update</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection