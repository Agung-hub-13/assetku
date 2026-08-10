<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal QR Code Aset</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: auto; margin: 10mm; }
        body {
            font-family: 'Arial', Helvetica, sans-serif;
            margin: 0; padding: 20px; background-color: #ffffff;
            -webkit-print-color-adjust: exact;
        }
        .no-print { margin-bottom: 2rem; text-align: center; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
        .stiker-grid {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 12px; justify-content: center; align-items: center;
        }
        @media print {
            .stiker-grid { grid-template-columns: repeat(4, 1fr); gap: 8px; }
        }
        .stiker-container {
            border: 1px solid #1e293b; padding: 6px 8px; border-radius: 6px;
            background: #ffffff; display: inline-block; text-align: center;
            box-sizing: border-box; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            page-break-inside: avoid; break-inside: avoid; margin: auto;
        }
        .qr-wrapper { position: relative; display: inline-block; line-height: 0; background: #ffffff; }
        .qr-logo {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 24px; height: 24px; background: #ffffff; border-radius: 50%;
            padding: 2px; border: 1.5px solid #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            display: flex; align-items: center; justify-content: center; overflow: hidden;
        }
        .qr-logo img { width: 100%; height: 100%; object-fit: contain; }
        .asset-code-text {
            margin-top: 5px; font-size: 9px; font-weight: 800; color: #0f172a;
            letter-spacing: 0.5px; text-align: center; text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="no-print bg-slate-50 p-4 rounded-2xl shadow-sm max-w-xl mx-auto border border-slate-200">
        <h2 class="font-bold text-slate-800 text-lg mb-1">Siap Mencetak {{ $assets->count() }} QR Code</h2>
        <p class="text-xs text-slate-500 mb-4">Pastikan opsi "Headers and Footers" di pengaturan print browser dinonaktifkan.</p>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md active:scale-95 text-sm cursor-pointer">
            🖨️ Cetak Sekarang
        </button>
    </div>

    <div class="stiker-grid">
        @foreach($assets as $asset)
            {{-- sama persis dengan halaman single: satu partial, satu logic --}}
            @include('admin.assets.partials.qr-sticker', ['asset' => $asset])
        @endforeach
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 600);
        });
    </script>
</body>
</html>