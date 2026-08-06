@extends('layouts.mobile')

@section('title', 'Mobile Dashboard - AssetKu')

@section('content')
<!-- ─── TOP HEADER BAR ─── -->
<header class="absolute top-0 left-0 right-0 z-40 bg-gradient-to-b from-black/80 via-black/40 to-transparent px-4 pt-6 pb-12 flex items-center justify-between text-white">
    <!-- Sisi Kiri: Profil & Nama -->
    <div class="flex items-center gap-3">
        <!-- Pemicu Menu / Sidebar Drawer -->
        <button onclick="toggleMobileMenu()" class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-lg border border-white/20 flex items-center justify-center text-white font-black text-sm shadow-md active:scale-90 transition-all hover:bg-white/20">
            {{ strtoupper(substr(auth()->user()->name ?? 'AG', 0, 2)) }}
        </button>
        <div>
            <p class="text-[10px] text-slate-300 font-medium tracking-wide opacity-90 leading-none">Selamat bekerja,</p>
            <h2 class="text-sm font-bold mt-1 tracking-tight">{{ auth()->user()->name ?? 'Agung' }}</h2>
        </div>
    </div>

    <!-- Sisi Kanan: Live Clock Widget -->
    <div class="flex flex-col items-end bg-black/20 backdrop-blur-lg border border-white/10 px-3 py-1.5 rounded-xl shadow-inner">
        <span class="text-[8px] uppercase font-black tracking-widest text-indigo-300 leading-none">Waktu</span>
        <span id="live-clock" class="text-xs font-black text-white mt-0.5 tracking-wider font-mono">00:00:00</span>
    </div>
</header>

<!-- ─── HERO BACKGROUND ─── -->
<div class="relative h-52 w-full bg-slate-950 overflow-hidden">
    <img src="{{ asset('/images/dormy-bg.jpg') }}"
        alt="BMS PRO Branding"
        class="w-full h-full object-cover opacity-50 scale-105">
    <!-- Overlay Gradasi Premium ke Arah Konten -->
    <div class="absolute inset-0 from-slate-50 via-slate-950/20 to-black/30"></div>
</div>

<!-- ─── MAIN CONTENT CONTAINER ─── -->
<div class="px-4 -mt-12 relative z-30 space-y-6 pb-28">

    <!-- ─── STATISTIK UTAMA (2 KOLOM MODERN) ─── -->
    <div class="grid grid-cols-2 gap-3">
        <!-- Kotak 1: Total Aset -->
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 p-4 rounded-2xl text-white shadow-[0_8px_20px_rgba(79,70,229,0.15)] flex flex-col justify-between min-h-[90px] border border-indigo-500/20 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-white/5 rounded-full pointer-events-none transition-all group-hover:scale-125"></div>
            <span class="text-[9px] text-indigo-200 font-extrabold uppercase tracking-wider leading-tight block">Total Aset</span>
            <span class="text-xl font-black tracking-tight mt-2 block leading-none">
                1,240 <span class="text-xs font-normal text-indigo-200/90 ml-0.5">Unit</span>
            </span>
        </div>

        <!-- Kotak 2: Total Lokasi -->
        <div class="bg-white border border-slate-100 p-4 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.03)] flex flex-col justify-between min-h-[90px] relative overflow-hidden group">
            <span class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wider leading-tight block">Total Lokasi</span>
            <span class="text-xl font-black text-slate-800 tracking-tight mt-2 block leading-none">
                45 <span class="text-xs font-bold text-slate-400 ml-0.5">Area</span>
            </span>
        </div>
    </div>

    <!-- ─── QUICK ACTION HUB (DASHBOARD UTAMA) ─── -->
    <section class="space-y-3.5">
        <!-- Grid Menu Utama Dashboard (2 Kolom Presisi, Diperbesar) -->
        <div class="grid grid-cols-2 gap-3.5">
            <!-- Menu 1: Scan QR -->
            <!-- Menu 1: Scan QR (Menuju Halaman Asset) -->
            <a href="{{ route('mobile.assets.index') }}" class="bg-white border border-slate-100/85 p-5 min-h-[142px] rounded-2xl flex flex-col items-center justify-center text-center shadow-[0_4px_16px_rgba(0,0,0,0.02)] active:scale-95 transition-all group border-b-[3px] border-b-slate-100 hover:border-b-indigo-500">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm shadow-emerald-100/50">
                    <i class="fa-solid fa-qrcode text-2xl"></i>
                </div>
                <span class="text-xs font-black text-slate-700 tracking-tight">Asset</span>
            </a>

            <!-- Menu 2: Lapor Keluhan / Temuan -->
            <a href="{{ route('mobile.asset_locations.index') }}" class="bg-white border border-slate-100/85 p-5 min-h-[142px] rounded-2xl flex flex-col items-center justify-center text-center shadow-[0_4px_16px_rgba(0,0,0,0.02)] active:scale-95 transition-all group border-b-[3px] border-b-slate-100 hover:border-b-indigo-500">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-3 group-hover:bg-rose-600 group-hover:text-white transition-all shadow-sm shadow-rose-100/50">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
                <span class="text-xs font-black text-slate-700 tracking-tight">Lokasi</span>
            </a>

            <!-- Menu 3: Transfer Mutasi Aset -->
            <a href="{{ route('mobile.asset_transfers.index') }}" class="bg-white border border-slate-100/85 p-5 min-h-[142px] rounded-2xl flex flex-col items-center justify-center text-center shadow-[0_4px_16px_rgba(0,0,0,0.02)] active:scale-95 transition-all group border-b-[3px] border-b-slate-100 hover:border-b-indigo-500">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3 group-hover:bg-amber-600 group-hover:text-white transition-all shadow-sm shadow-amber-100/50">
                    <i class="fa-solid fa-route text-xl"></i>
                </div>
                <span class="text-xs font-black text-slate-700 tracking-tight">Mutasi</span>
            </a>

            <!-- Menu 4: Stock Opname -->
            <a href="{{ route('mobile.users.index') }}" class="bg-white border border-slate-100/85 p-5 min-h-[142px] rounded-2xl flex flex-col items-center justify-center text-center shadow-[0_4px_16px_rgba(0,0,0,0.02)] active:scale-95 transition-all group border-b-[3px] border-b-slate-100 hover:border-b-indigo-500">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm shadow-indigo-100/50">
                    <i class="fa-solid fa-clipboard-check text-xl"></i>
                </div>
                <span class="text-xs font-black text-slate-700 tracking-tight">User</span>
            </a>
        </div>
    </section>

</div>
@endsection