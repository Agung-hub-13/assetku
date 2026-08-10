{{-- resources/views/admin/assets/partials/qr-sticker.blade.php --}}
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
    <div class="qr-wrapper">
        {!! QrCode::size(96)->margin(1)->errorCorrection('H')->generate($qrPayload) !!}
        <div class="qr-logo">
            <img src="{{ asset('images/slp.png') }}" alt="SLP"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <span style="display:none; font-size: 7px; font-weight: 900; color: #0f172a;">SLP</span>
        </div>
    </div>
    <div class="asset-code-text">
        {{ $asset->asset_code ?? $asset->asset_number ?? '-' }}
    </div>
</div>