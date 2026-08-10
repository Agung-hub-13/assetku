@php
    $activeLocation = $asset->transfer->toLocation ?? $asset->location;

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
    <div class="brand-text">SLP</div>
    <div class="qr-wrapper">
        {!! QrCode::format('svg')->size(600)->margin(0)->errorCorrection('M')->generate($qrPayload) !!}
    </div>
    <div class="asset-code-text">
        {{ $asset->asset_code ?? $asset->asset_number ?? '-' }}
    </div>
</div>

<style>
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
    }

    .brand-text {
        font-size: 8pt; /* Diubah ke pt agar ukurannya konsisten dan tegas dibaca printer */
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
        font-size: 9.5pt; /* Diperbesar agar kode aset sangat jelas terbaca */
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