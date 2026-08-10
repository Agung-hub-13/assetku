<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stiker QR Code Aset - {{ $asset->asset_code ?? $asset->asset_number }}</title>
    <style>
        @page { size: auto; margin: 0mm; }
        body {
            font-family: 'Arial', Helvetica, sans-serif;
            margin: 0; padding: 0; background-color: #ffffff;
            -webkit-print-color-adjust: exact;
            display: flex; justify-content: center; align-items: center; height: 100vh;
        }
        .stiker-container {
            border: 1px solid #1e293b; padding: 6px 8px; border-radius: 6px;
            background: #ffffff; display: inline-block; text-align: center;
            box-sizing: border-box; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
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
<body onload="window.print(); window.close();">

    {{-- Satu-satunya sumber logic QR: partials/qr-sticker.blade.php --}}
    @include('admin.assets.partials.qr-sticker', ['asset' => $asset])

</body>
</html>