@extends('layouts.mobile')

@section('title', 'Manajemen Aset - AssetKu')

@section('content')
<!-- ─── TOP HEADER BAR ─── -->
<header class="absolute top-0 left-0 right-0 z-40 bg-gradient-to-b from-slate-900 via-slate-900/80 to-transparent px-4 pt-6 pb-12 flex items-center justify-between text-white">
    <div class="flex items-center gap-3">
        <div>
            <h2 class="text-sm font-black mt-1 tracking-tight">Manajemen Aset</h2>
        </div>
    </div>

    <!-- Tombol Tambah Aset Baru -->
    <a href="#" class="w-10 h-10 rounded-xl bg-indigo-600 border border-indigo-500/30 flex items-center justify-center text-white shadow-[0_4px_12px_rgba(79,70,229,0.3)] active:scale-90 transition-all">
        <i class="fa-solid fa-plus text-sm"></i>
    </a>
</header>

<!-- ─── TOP SECTION BUFFER (PENGGANTI HERO) ─── -->
<div class="h-28 w-full bg-slate-900"></div>

<!-- ─── MAIN CONTENT CONTAINER ─── -->
<div class="px-4 -mt-8 relative z-30 space-y-4 pb-28">

    <!-- ─── PILAR SEARCH & FILTER BAR ─── -->
    <div class="bg-white p-3 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.02)] border border-slate-100/80 flex gap-2">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" placeholder="Cari nama aset atau kode tag..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-semibold text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-500 transition-colors">
        </div>
        <button class="w-11 h-11 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-600 active:scale-95 transition-all">
            <i class="fa-solid fa-sliders text-xs"></i>
        </button>
    </div>

    <!-- ─── KATEGORI / STATUS FILTER CAPSULES ─── -->
    <div class="flex gap-2 overflow-x-auto no-scrollbar py-1 -mx-4 px-4">
        <button class="px-4 py-1.5 rounded-full bg-indigo-600 text-white text-[11px] font-black shadow-sm shrink-0">
            Semua ({{ $assets->total() }})
        </button>
        <button class="px-4 py-1.5 rounded-full bg-white border border-slate-100 text-slate-600 text-[11px] font-bold shadow-sm shrink-0">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> Normal
        </button>
        <button class="px-4 py-1.5 rounded-full bg-white border border-slate-100 text-slate-600 text-[11px] font-bold shadow-sm shrink-0">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 mr-1"></span> Maintenance
        </button>
        <button class="px-4 py-1.5 rounded-full bg-white border border-slate-100 text-slate-600 text-[11px] font-bold shadow-sm shrink-0">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-rose-500 mr-1"></span> Rusak
        </button>
    </div>

    <!-- ─── LIST DAFTAR ASET (CARDS LOOPING) ─── -->
    <div class="space-y-3">

        @forelse ($assets as $asset)
            @php
                // 1. Menentukan Gaya Warna Berdasarkan Status Aset (Sesuaikan string status dengan DB Anda)
                $statusLower = strtolower($asset->status ?? 'normal');
                if ($statusLower === 'maintenance' || $statusLower === 'perbaikan') {
                    $statusColor = 'text-amber-600 bg-amber-50';
                    $statusDot = 'bg-amber-500';
                } elseif ($statusLower === 'rusak' || $statusLower === 'broken') {
                    $statusColor = 'text-rose-600 bg-rose-50';
                    $statusDot = 'bg-rose-500';
                } else {
                    $statusColor = 'text-emerald-600 bg-emerald-50';
                    $statusDot = 'bg-emerald-500';
                }

                // 2. Menentukan Ikon FontAwesome Berdasarkan Nama Kategori
                $categoryLower = strtolower($asset->category->name ?? '');
                if (str_contains($categoryLower, 'elektronik') || str_contains($categoryLower, 'computer')) {
                    $categoryIcon = 'fa-computer text-indigo-600';
                } elseif (str_contains($categoryLower, 'fasilitas') || str_contains($categoryLower, 'ac')) {
                    $categoryIcon = 'fa-fan text-amber-600';
                } elseif (str_contains($categoryLower, 'utilitas') || str_contains($categoryLower, 'genset')) {
                    $categoryIcon = 'fa-bolt text-rose-600';
                } else {
                    $categoryIcon = 'fa-box text-slate-600'; // Default Icon
                }
            @endphp

            <div onclick="window.location='{{ route('mobile.assets.index', $asset->id) }}'" class="bg-white border border-slate-100/70 rounded-2xl p-3.5 shadow-[0_4px_12px_rgba(0,0,0,0.01)] flex gap-3 items-center relative overflow-hidden border-b-[3px] border-b-slate-100 active:bg-slate-50/50 transition-colors cursor-pointer">
                
                <!-- Thumbnail / Icon Kategori Aset -->
                <div class="w-14 h-14 rounded-xl bg-slate-50 border border-slate-100 text-slate-500 flex flex-col items-center justify-center shrink-0">
                    <i class="fa-solid {{ $categoryIcon }} text-lg"></i>
                    <span class="text-[7px] uppercase font-bold tracking-tighter text-slate-400 mt-0.5 truncate max-w-full px-1">
                        {{ $asset->category->name ?? 'N/A' }}
                    </span>
                </div>

                <!-- Detail Info Teks -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] font-black font-mono tracking-wider text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded">
                            {{ $asset->asset_code ?? $asset->code ?? 'NO-CODE' }}
                        </span>
                        <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $statusColor }}">
                            {{ ucfirst($asset->status ?? 'Normal') }}
                        </span>
                    </div>
                    <h3 class="text-xs font-bold text-slate-800 mt-1 truncate">{{ $asset->name }}</h3>
                    <p class="text-[10px] text-slate-400 font-semibold mt-0.5 flex items-center gap-1">
                        <i class="fa-solid fa-location-dot text-[9px]"></i> 
                        {{ $asset->location->name ?? 'Tidak Ada Lokasi' }}
                    </p>
                </div>

                <!-- Aksi Detail Quick Arrow -->
                <i class="fa-solid fa-chevron-right text-slate-300 text-xs pl-1"></i>
            </div>
        @empty
            <div class="bg-white border border-slate-100 rounded-2xl p-8 text-center">
                <i class="fa-solid fa-box-open text-slate-300 text-3xl mb-2"></i>
                <p class="text-xs font-bold text-slate-400">Belum ada data aset yang tersedia.</p>
            </div>
        @endforelse

    </div>

    <!-- ─── PAGINATION / DATA END NOTE ─── -->
    <div class="pt-2">
        @if($assets->hasPages())
            <div class="mt-4 mobile-pagination">
                {{ $assets->links() }}
            </div>
        @endif
        <div class="text-center mt-2">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">
                Menampilkan {{ $assets->count() }} dari {{ $assets->total() }} Data Aset
            </p>
        </div>
    </div>

</div>
@endsection