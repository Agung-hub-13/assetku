@extends('layouts.admin')

@section('title', 'Pusat Notifikasi')

@section('content')
<div class="relative p-4 sm:p-6 lg:p-8 min-h-screen text-slate-800 dark:text-slate-100 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">


    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Pusat Notifikasi</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Lihat semua riwayat pemberitahuan sistem Anda.</p>
        </div>
        
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5 transition-all">
                    <i data-lucide="check-check" class="w-4 h-4"></i>
                    <span>Tandai Semua Dibaca</span>
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="p-3 text-xs rounded-xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white/60 dark:bg-slate-900/40 backdrop-blur-xl border border-slate-200/50 dark:border-slate-800/60 rounded-2xl divide-y divide-slate-100 dark:divide-slate-800/40 overflow-hidden shadow-xl shadow-slate-950/5">
        @forelse($notifications as $notification)
            @php
                $isUnread = is_null($notification->read_at);
                $data = $notification->data;
            @endphp
            <div class="flex items-center justify-between gap-4 p-4 transition-colors {{ $isUnread ? 'bg-blue-50/40 dark:bg-blue-500/5 hover:bg-blue-50/70' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }}">
                
                {{-- Form POST untuk klik notifikasi (tandai dibaca & redirect) --}}
                <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" class="flex-1 flex items-start gap-4 cursor-pointer">
                    @csrf
                    <button type="submit" class="flex items-start gap-4 text-left w-full">
                        <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5">
                            <i data-lucide="{{ $data['icon'] ?? 'bell' }}" class="w-5 h-5"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-white truncate">
                                    {{ $data['title'] ?? 'Notifikasi' }}
                                </h4>
                                <span class="text-[10px] text-slate-400 shrink-0">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                {{ $data['message'] ?? '' }}
                            </p>
                        </div>

                        @if($isUnread)
                            <div class="w-2 h-2 rounded-full bg-blue-500 shrink-0 self-center"></div>
                        @endif
                    </button>
                </form>

                {{-- Action Hapus Notifikasi --}}
                <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            onclick="return confirm('Hapus notifikasi ini?')"
                            class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 rounded-lg transition-colors"
                            title="Hapus">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>

            </div>
        @empty
            <div class="py-12 text-center text-slate-400 dark:text-slate-500">
                <i data-lucide="bell-off" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                <p class="text-xs">Tidak ada riwayat notifikasi.</p>
            </div>
        @endforelse
    </div>

    {{-- Link Paginasi --}}
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection