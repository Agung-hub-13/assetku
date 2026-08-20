<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aset - {{ $asset->name }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eff6ff', 500: '#3b82f6', 600: '#2563eb', 900: '#1e3a8a' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased min-h-screen flex flex-col items-center justify-center p-4 relative overflow-x-hidden">

    <!-- Background Glow & Grid Accent -->
    <div class="absolute inset-0 pointer-events-none z-0 flex items-center justify-center opacity-20">
        <div class="absolute w-[500px] h-[500px] bg-blue-600/30 rounded-full blur-[120px]"></div>
        <div class="absolute inset-0" style="background-image: radial-gradient(#334155 1px, transparent 1px); background-size: 32px 32px;"></div>
    </div>

    <!-- Card Container -->
    <div class="relative z-10 max-w-lg w-full bg-slate-900/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl border border-slate-800 overflow-hidden">
        
        <!-- Top Banner / Header -->
        <div class="relative bg-gradient-to-br from-blue-600 to-indigo-700 px-6 pt-8 pb-6 text-white text-center">
            <div class="absolute top-4 right-4">
                @php
                    $status = strtolower($asset->status ?? 'active');
                    $statusColor = match($status) {
                        'active' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                        'maintenance' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                        default => 'bg-slate-500/20 text-slate-300 border-slate-500/30'
                    };
                @endphp
                <span class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest rounded-full border {{ $statusColor }} backdrop-blur-md">
                    {{ $asset->status ?? 'Active' }}
                </span>
            </div>

            <span class="inline-block text-[10px] uppercase tracking-widest font-black bg-white/10 px-3 py-1 rounded-full mb-3 backdrop-blur-md">
                PT Sentral Layanan Prima (SLP)
            </span>
            <h1 class="text-2xl font-black tracking-tight leading-snug">{{ $asset->name }}</h1>
            <p class="text-xs text-blue-100 font-mono tracking-wider mt-1 opacity-90">
                {{ $asset->asset_code ?? $asset->asset_number ?? '-' }}
            </p>
        </div>

        <!-- Body Detail Content -->
        <div class="p-6 sm:p-8 space-y-6">

            <!-- Grid Informasi Utama (Identitas) -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-800/50 border border-slate-800 p-4 rounded-2xl">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nomor Seri (SN)</span>
                    <span class="text-xs sm:text-sm font-bold font-mono text-slate-200 truncate block">{{ $asset->serial_number ?: '-' }}</span>
                </div>
                <div class="bg-slate-800/50 border border-slate-800 p-4 rounded-2xl">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">No. Accurate</span>
                    <span class="text-xs sm:text-sm font-bold font-mono text-slate-200 truncate block">{{ $asset->accurate_no ?? '-' }}</span>
                </div>
            </div>

            <!-- List Atribut Detail & Lokasi Terstruktur -->
            <div class="bg-slate-800/30 border border-slate-800 rounded-2xl p-4 space-y-3.5 text-xs sm:text-sm">
                
                <div class="flex justify-between items-center pb-2.5 border-b border-slate-800/60">
                    <span class="text-slate-400 font-medium">Kategori</span>
                    <span class="font-bold text-slate-200 text-right">{{ $asset->category->name ?? $asset->accurate_category_name ?? '-' }}</span>
                </div>

                <div class="flex justify-between items-center pb-2.5 border-b border-slate-800/60">
                    <span class="text-slate-400 font-medium">Departemen</span>
                    <span class="font-bold text-slate-200 text-right">{{ $asset->department->name ?? '-' }}</span>
                </div>

                <!-- 🏢 DETAIL LOKASI DIPERJELAS (Gedung, Lantai, Ruangan, Area) -->
                @php
                    $activeLocation = $asset->transfer->toLocation ?? $asset->location;
                @endphp
                
                <div class="py-2.5 border-b border-slate-800/60 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Area / Lokasi Utama</span>
                        <span class="font-bold text-blue-400 text-right">{{ $activeLocation->name ?? '-' }}</span>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <div class="bg-slate-900/60 p-2 rounded-xl border border-slate-800 text-center">
                            <span class="block text-[9px] uppercase tracking-wider text-slate-500">Gedung</span>
                            <span class="font-semibold text-slate-200 text-xs">{{ $activeLocation->building ?? $asset->building_name ?? '-' }}</span>
                        </div>
                        <div class="bg-slate-900/60 p-2 rounded-xl border border-slate-800 text-center">
                            <span class="block text-[9px] uppercase tracking-wider text-slate-500">Lantai</span>
                            <span class="font-semibold text-slate-200 text-xs">{{ $activeLocation->floor ?? '-' }}</span>
                        </div>
                        <div class="bg-slate-900/60 p-2 rounded-xl border border-slate-800 text-center">
                            <span class="block text-[9px] uppercase tracking-wider text-slate-500">Ruangan</span>
                            <span class="font-semibold text-slate-200 text-xs">{{ $activeLocation->room ?? $asset->room_name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- CONTOH SLOT TAMBAHAN (Bisa dikurangi/ditambah sesuai kebutuhan tanpa merusak QR) -->
                @if(!empty($asset->purchase_date))
                <div class="flex justify-between items-center pb-2.5 border-b border-slate-800/60">
                    <span class="text-slate-400 font-medium">Tanggal Beli</span>
                    <span class="font-bold text-slate-200 text-right">{{ \Carbon\Carbon::parse($asset->purchase_date)->format('d M Y') }}</span>
                </div>
                @endif

                @if(!empty($asset->user->name))
                <div class="flex justify-between items-center">
                    <span class="text-slate-400 font-medium">Pengguna / Pemegang</span>
                    <span class="font-bold text-slate-200 text-right">{{ $asset->user->name }}</span>
                </div>
                @endif

            </div>

            <!-- Keterangan / Deskripsi Khusus -->
            @if(!empty($asset->description))
            <div class="space-y-1.5">
                <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 ml-1">Keterangan / Spesifikasi</span>
                <div class="bg-slate-800/50 border border-slate-800 p-4 rounded-2xl text-xs text-slate-300 leading-relaxed">
                    {{ $asset->description }}
                </div>
            </div>
            @endif

        </div>

        <!-- Footer / Branding -->
        <div class="bg-slate-950/60 px-6 py-4 text-center border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-500">
            <span>Assetku</span>
            <span class="font-mono text-slate-400">&bull; PT SLP &bull;</span>
        </div>

    </div>

</body>
</html>