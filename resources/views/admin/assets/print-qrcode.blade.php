<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stiker QR Code Aset - {{ $asset->asset_code ?? $asset->asset_number }}</title>
    <style>
        @page {
            size: auto;
            margin: 0mm;
        }

        body {
            font-family: 'Arial', Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .stiker-container {
            border: 1px solid #1e293b;
            padding: 6px 8px;
            border-radius: 6px;
            background: #ffffff;
            display: inline-block;
            text-align: center;
            box-sizing: border-box;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .qr-wrapper {
            position: relative;
            display: inline-block;
            line-height: 0;
            background: #ffffff;
        }

        /* Styling Logo SLP di Tengah QR Code yang Lebih Proporsional & Elegan */
        .qr-logo {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            background: #ffffff;
            border-radius: 50%;
            padding: 2px;
            border: 1.5px solid #0f172a;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .qr-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Teks Kode Aset di Bawah QR Code */
        .asset-code-text {
            margin-top: 5px;
            font-size: 9px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-align: center;
            text-transform: uppercase;
        }
    </style>
</head>

<body onload="window.print(); window.close();">

    @php
    $activeLocation = $asset->transfer->toLocation ?? $asset->location;

    // Format teks detail aset lengkap di dalam payload QR Code
    $locParts = [];
    if ($activeLocation) {
        if (!empty($activeLocation->name)) $locParts[] = $activeLocation->name;
        if (!empty($activeLocation->building)) {
            $bldg = $activeLocation->building;
            $locParts[] = stripos($bldg, 'gedung') === false ? 'Gedung ' . $bldg : $bldg;
        }
        if (!empty($activeLocation->room)) {
            $room = $activeLocation->room;
            $locParts[] = (stripos($room, 'r') === false && stripos($room, 'ruang') === false) ? 'R. ' . $room : $room;
        }
        $locDetail = implode(', ', $locParts);
    } else {
        $locDetail = '-';
    }

    $qrPayload = "KODE: " . ($asset->asset_code ?? '-') . "\n" .
                 "NAMA: " . $asset->name . "\n" .
                 "SN: " . ($asset->serial_number ?: '-') . "\n" .
                 "ACCURATE: " . ($asset->accurate_no ?? '-') . "\n" .
                 "LOKASI: " . $locDetail;
    @endphp

    <div class="stiker-container">
        <div class="qr-wrapper">
            {{-- Ukuran QR disesuaikan ke 96 agar pas, rapi, dan tidak terlalu besar --}}
            {!! QrCode::size(96)->margin(1)->errorCorrection('H')->generate($qrPayload) !!}
            
            <!-- Logo SLP di Tengah QR Code -->
            <div class="qr-logo">
                <img src="{{ asset('images/slp.png') }}" alt="SLP" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span style="display:none; font-size: 7px; font-weight: 900; color: #0f172a;">SLP</span>
            </div>
        </div>

        <!-- Teks Kode Aset di Bawah QR Code -->
        <div class="asset-code-text">
            {{ $asset->asset_code ?? $asset->asset_number ?? '-' }}
        </div>
    </div>

</body>

</html>