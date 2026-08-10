<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal QR Code Aset</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: 24mm auto;
            margin: 0 !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 24mm;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
        }

        .no-print {
            width: 100%;
            max-width: 480px;
            margin: 20px auto;
            text-align: center;
        }

        @media print {
            .no-print { display: none; }
            html, body { width: 24mm; }
        }

        .stiker-page {
            width: 24mm;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 1.5mm 0;
            box-sizing: border-box;
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>
<body>

    <div class="no-print bg-slate-50 p-4 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="font-bold text-slate-800 text-lg mb-1">Siap Mencetak {{ $assets->count() }} QR Code</h2>
        <p class="text-xs text-slate-500 mb-4">Label tercetak beruntun secara efisien tanpa boros kertas.</p>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md active:scale-95 text-sm cursor-pointer">
            🖨️ Cetak Sekarang
        </button>
    </div>

    @foreach($assets as $asset)
        <div class="stiker-page">
            @include('admin.assets.partials.qr-sticker', ['asset' => $asset])
        </div>
    @endforeach

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 600);
        });
    </script>
</body>
</html>