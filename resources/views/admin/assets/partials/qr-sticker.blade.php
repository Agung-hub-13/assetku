@php
    $activeLocation = $asset->transfer->toLocation ?? $asset->location;

    // Pastikan qr_token ada
    if (empty($asset->qr_token)) {
        $asset->qr_token = (string) \Illuminate\Support\Str::uuid();
    }

    $qrPayload = route('assets.public-preview', $asset->qr_token);

    // 📌 Logika Nama Lokasi (Khusus untuk nama tempat/ruangan spesifik)
    $locationName = '-';
    if ($activeLocation && !empty($activeLocation->name) && $activeLocation->name !== '-') {
        $locationName = $activeLocation->name;
    }

    // 📌 Logika Pemetaan Gedung, Lantai, & Ruang
    $buildingInfo = '-';
    if ($activeLocation) {
        $chunks = [];

        if (!empty($activeLocation->building) && $activeLocation->building !== '-') {
            $chunks[] = $activeLocation->building; 
        }
        if (!empty($activeLocation->floor) && $activeLocation->floor !== '-') {
            $floorTxt = str_ireplace(['lantai', 'floor', 'lt'], '', $activeLocation->floor);
            $chunks[] = 'L.' . trim($floorTxt);
        }
        if (!empty($activeLocation->room) && $activeLocation->room !== '-') {
            $chunks[] = 'R.' . $activeLocation->room;
        }

        if (count($chunks) > 0) {
            $buildingInfo = implode(' | ', $chunks);
        }
    }

    // 📌 Logika Pembersihan/Penyingkatan Nama Aset
    $rawAssetName = $asset->name ?? $asset->asset_name ?? '';
    $cleanAssetName = '-';
    
    if (!empty($rawAssetName)) {
        $nameTrimmed = trim($rawAssetName);
        $words = explode(' ', $nameTrimmed);
        
        if (count($words) > 3 || strlen($nameTrimmed) > 20) {
            $cleanAssetName = implode(' ', array_slice($words, 0, 3)); 
        } else {
            $cleanAssetName = $nameTrimmed;
        }
    }
@endphp

<div class="stiker-container" style="box-sizing: border-box; width: 19.5mm; margin: 0 auto 1mm auto; padding: 0.5mm 1mm; background: #ffffff; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'Arial', Helvetica, sans-serif;">
    
    {{-- Brand / Logo --}}
    <div class="brand-text" style="font-size: 7pt; font-weight: 800; color: #000000; margin-bottom: 0.5px; line-height: 1.0; text-transform: uppercase; width: 100%;">
        SLP
    </div>
    
    {{-- QR Code (Dikecilkan sedikit ke size 65 agar muat tambahan 1 baris teks) --}}
    <div class="qr-wrapper" style="margin: 0 auto; width: 100%; line-height: 0;">
        {!! QrCode::format('svg')->size(65)->margin(0)->errorCorrection('M')->generate($qrPayload) !!}
    </div>
    
    {{-- 1. Kode Aset (Utama) --}}
    <div class="asset-code-text" style="font-size: 6.5pt; font-weight: 900; color: #000000; line-height: 1.1; margin-top: 1px; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">
        {{ $asset->asset_code ?? $asset->asset_number ?? '-' }}
    </div>

    {{-- 2. Nama Lokasi (Tepat di bawah Kode Aset) --}}
    <div class="location-name-text" style="font-size: 4.5pt; font-weight: 700; color: #000000; line-height: 1.1; margin-top: 0.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">
        {{ $locationName }}
    </div>

    {{-- 3. Gedung, Lantai, Ruang (Di bawah Nama Lokasi) --}}
    <div class="location-building-text" style="font-size: 4.5pt; font-weight: 700; color: #000000; line-height: 1.1; margin-top: 0.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">
        {{ $buildingInfo }}
    </div>

    {{-- 4. Nama Aset Ringkas (Paling Bawah) --}}
    <div class="asset-name-text" style="font-size: 4.5pt; font-weight: 700; color: #000000; line-height: 1.1; margin-top: 0.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">
        {{ $cleanAssetName }}
    </div>

</div>