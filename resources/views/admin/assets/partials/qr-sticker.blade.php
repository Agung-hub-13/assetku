{{-- resources/views/admin/assets/partials/qr-sticker.blade.php --}}
{{-- HANYA markup di sini. Styling ada di <head> masing-masing halaman pemanggil
     (print-qrcode.blade.php & print-qrcode-bulk.blade.php) supaya tidak
     ter-duplikasi berkali-kali saat partial ini di-loop pada cetak massal. --}}
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