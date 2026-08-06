<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Massal QR Code Aset</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none;
            }
            .qr-card {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 font-sans">

    <!-- Tombol navigasi atas (Disembunyikan otomatis saat diprint) -->
    <div class="no-print mb-8 text-center bg-white p-4 rounded-2xl shadow-sm max-w-xl mx-auto">
        <h2 class="font-bold text-slate-800 text-lg mb-1">Siap Mencetak {{ $assets->count() }} QR Code</h2>
        <p class="text-xs text-slate-500 mb-4">Pastikan opsi "Headers and Footers" di pengaturan print browser Anda dinonaktifkan.</p>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md active:scale-95 text-sm">
            🖨️ Cetak Sekarang
        </button>
    </div>

    <!-- Layout Grid Kartu Label Stiker -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-w-5xl mx-auto">
        @foreach($assets as $asset)
            <div class="qr-card bg-white border border-slate-300 rounded-xl p-4 flex items-center gap-4 shadow-sm">
                
                <!-- QR Code hasil generate on-the-fly -->
                <div class="flex-shrink-0 bg-white p-1 border border-slate-100 rounded-lg">
                    {!! $asset->generated_qr !!}
                </div>
                
                <!-- Detail keterangan barang & tujuan lokasi untuk kurir/staff penempel -->
                <div class="flex-1 min-w-0">
                    <span class="text-[9px] font-black text-blue-500 uppercase tracking-widest block mb-0.5">
                        {{ $asset->category->name ?? 'Aset Accurate' }}
                    </span>
                    <h3 class="text-sm font-bold text-slate-800 truncate mb-0.5">
                        {{ $asset->name }}
                    </h3>
                    <p class="text-[11px] font-mono text-slate-400 truncate mb-2">
                        {{ $asset->asset_code ?? $asset->asset_number }}
                    </p>
                    
                    <!-- Informasi krusial agar staff tahu stiker ini harus dibawa ke ruangan mana -->
                    <div class="pt-1.5 border-t border-dashed border-slate-200">
                        <span class="text-[9px] text-slate-400 block leading-tight">Lokasi Target:</span>
                        <strong class="text-xs text-slate-700 block truncate">
                            {{ $asset->location->name ?? 'Belum Ditentukan' }}
                        </strong>
                        @if($asset->location && $asset->location->room)
                            <span class="text-[10px] text-slate-500 block truncate">
                                Ruang: {{ $asset->location->room }}
                            </span>
                        @endif
                    </div>
                </div>

            </div>
        @endforeach
    </div>

    <!-- Otomatis memicu dialog print sesaat setelah halaman selesai di-load -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>