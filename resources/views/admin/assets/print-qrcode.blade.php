<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stiker QR Code Aset - {{ $asset->asset_code ?? $asset->asset_number }}</title>
    <style>
        /* Ganti angka 24mm di 2 tempat (size & .stiker-page) kalau lebar tape berubah */
        @page {
            size: 24mm auto;
            margin: 0 !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
        }

        .stiker-page-single {
            width: 24mm;
            min-height: 24mm;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
        }

        /* ===== Styling stiker — satu-satunya definisi, dipakai sekali per halaman ini ===== */
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
<body onload="window.print();">

    <div class="stiker-page-single">
        @include('admin.assets.partials.qr-sticker', ['asset' => $asset])
    </div>

</body>
</html>