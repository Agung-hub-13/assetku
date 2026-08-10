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
            width: 100%;
            background-color: #f1f5f9;
            -webkit-print-color-adjust: exact;
        }

        .no-print {
            width: 100%;
            max-width: 480px;
            margin: 20px auto;
            text-align: center;
        }

        .print-preview-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding-bottom: 40px;
        }

        .stiker-page {
            width: 24mm;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
            padding: 1.5mm 0;
            box-sizing: border-box;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 2px;

            /* SEMUA label mengalir ke bawah dalam SATU halaman panjang
               (tinggi @page = auto -> mengikuti total tinggi semua label),
               dicetak sekali jalan tanpa tape terbuang antar label.
               Cuma dijaga supaya 1 label tidak terpotong di tengah: */
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @media print {
            .no-print { display: none !important; }

            html, body {
                width: 24mm !important;
                background-color: #ffffff !important;
            }

            .print-preview-wrapper {
                gap: 0;
                padding-bottom: 0;
            }

            .stiker-page {
                width: 24mm;
                box-shadow: none;
                border-radius: 0;
            }
        }

        /* ===== Styling stiker — satu-satunya definisi, dipakai untuk semua label di loop ===== */
        .stiker-container {
            box-sizing: border-box;
            width: 18.5mm;
            margin: 0 auto;
            padding: 0.5mm;
            background: #ffffff;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', Helvetica, sans-serif; /* dikunci -> tidak lagi terpengaruh Tailwind/reset browser */
        }

        .stiker-container * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: inherit; /* semua anak elemen ikut font Arial, tidak ada yang bocor dari Tailwind */
        }

        .brand-text {
            font-size: 8pt;
            font-weight: 800;
            color: #000000;
            margin-bottom: 1mm;
            letter-spacing: 0.2px;
            line-height: 1.1;
            text-transform: uppercase;
            width: 100%;
        }

        .qr-wrapper {
            position: relative;
            display: block;
            width: 100%;
            line-height: 0;
            background: #ffffff;
            box-sizing: border-box;
        }

        .qr-wrapper svg {
            width: 100%;
            height: auto;
            display: block;
        }

        .asset-code-text {
            margin-top: 1mm;
            font-size: 9.5pt;
            font-weight: 900;
            color: #000000;
            letter-spacing: 0.3px;
            text-align: center;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            line-height: 1.1;
        }
    </style>
</head>
<body>

    <div class="no-print bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="font-bold text-slate-800 text-lg mb-1">Siap Mencetak {{ $assets->count() }} QR Code</h2>
        <p class="text-xs text-slate-500 mb-4">Semua label akan tercetak berurutan dalam satu strip panjang, tidak boros tape.</p>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-md active:scale-95 text-sm cursor-pointer">
            🖨️ Cetak Sekarang
        </button>
    </div>

    <div class="print-preview-wrapper">
        @foreach($assets as $asset)
            <div class="stiker-page">
                @include('admin.assets.partials.qr-sticker', ['asset' => $asset])
            </div>
        @endforeach
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 600);
        });
    </script>
</body>
</html>