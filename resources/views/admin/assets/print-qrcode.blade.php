<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stiker QR Code Aset - {{ $asset->asset_code ?? $asset->asset_number }}</title>
    <style>
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
    </style>
</head>
<body onload="window.print();">

    <div class="stiker-page-single">
        @include('admin.assets.partials.qr-sticker', ['asset' => $asset])
    </div>

</body>
</html>