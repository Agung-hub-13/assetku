<!-- ─── STICKY BOTTOM NAVIGATION BAR ─── -->
<nav class="absolute bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md border-t border-slate-100/80 h-16 grid grid-cols-3 items-center z-40 shadow-[0_-4px_16px_rgba(0,0,0,0.04)]">

    <!-- Kolom 1: Beranda (Presisi Tengah Kiri) -->
    <div class="flex justify-center items-center h-full">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center text-indigo-600 w-16 h-12 transition-all active:scale-90 relative group">
            <!-- Indikator Garis Aktif Atas -->
            <span class="absolute -top-2 w-5 h-1 rounded-full bg-indigo-600"></span>
            <i class="fa-solid fa-house text-xl"></i>
            <span class="text-[9px] font-black mt-1 tracking-tight">Beranda</span>
        </a>
    </div>

    <!-- Kolom 2: Tombol QR (Presisi Tengah-Tengah) -->
    <div class="flex justify-center items-center h-full">
        <div class="relative w-16 h-16 flex justify-center -mt-7">
            <a href="#" class="w-14 h-14 bg-gradient-to-tr from-indigo-600 to-indigo-800 text-white rounded-full flex items-center justify-center shadow-[0_4px_14px_rgba(79,70,229,0.4)] border-[4px] border-slate-50 active:scale-90 transition-transform">
                <i class="fa-solid fa-qrcode text-xl"></i>
            </a>
        </div>
    </div>

    <!-- Kolom 3: Keluar / Logout (Presisi Tengah Kanan) -->
    <div class="flex justify-center items-center h-full">
        <form method="POST" action="{{ route('logout') }}" class="w-full flex justify-center">
            @csrf
            <button type="submit" class="flex flex-col items-center justify-center text-rose-500 hover:text-rose-600 w-16 h-12 transition-all active:scale-90 group focus:outline-none">
                <i class="fa-solid fa-arrow-right-from-bracket text-xl group-hover:scale-105 transition-transform"></i>
                <span class="text-[9px] font-black mt-1 tracking-tight">Keluar</span>
            </button>
        </form>
    </div>
</nav>