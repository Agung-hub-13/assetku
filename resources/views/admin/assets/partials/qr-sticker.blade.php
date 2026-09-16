@php
    $activeLocation = $asset->transfer->toLocation ?? $asset->location;

    // Pastikan qr_token ada
    if (empty($asset->qr_token)) {
        $asset->qr_token = (string) \Illuminate\Support\Str::uuid();
    }

    $qrPayload = route('assets.public-preview', $asset->qr_token);

    // 📌 Logika Pemetaan Lokasi (Gedung, Lantai, Ruang, Nama Lokasi)
    $locationShort = '-';
    if ($activeLocation) {
        $chunks = [];

        if (!empty($activeLocation->name) && $activeLocation->name !== '-') {
            $chunks[] = $activeLocation->name;
        }
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
            $locationShort = implode(' | ', $chunks);
        }
    }

    // 📌 Logika Pembersihan/Penyingkatan Nama Aset
    $rawAssetName = $asset->name ?? $asset->asset_name ?? '';
    $cleanAssetName = '-';
    
    if (!empty($rawAssetName)) {
        // Hapus simbol berlebih agar bersih
        $nameTrimmed = trim($rawAssetName);
        $words = explode(' ', $nameTrimmed);
        
        // Jika kata lebih dari 3 atau total karakter > 20, buat versi singkatnya yang rapi
        if (count($words) > 3 || strlen($nameTrimmed) > 20) {
            // Opsi: Ambil maksimal 3 kata pertama agar tidak terlalu panjang, atau buat inisial
            $cleanAssetName = implode(' ', array_slice($words, 0, 3)); 
        } else {
            $cleanAssetName = $nameTrimmed;
        }
    }
@endphp

<div class="stiker-item" style="display: inline-block; width: 24mm; vertical-align: top; text-align: center; box-sizing: border-box; padding: 2mm;">
    <div class="brand-text" style="font-size: 7px; font-weight: bold; margin-bottom: 1px;">SLP</div>
    
    <div class="qr-wrapper" style="margin: 0 auto;">
        {{-- Ukuran QR code dikecilkan sedikit agar pas di lebar 24mm --}}
        {!! QrCode::format('svg')->size(120)->margin(0)->errorCorrection('M')->generate($qrPayload) !!}
    </div>
    
    <div class="asset-code-text" style="font-size: 6.5px; font-weight: bold; line-height: 1.1; margin-top: 2px;">
        {{ $asset->asset_code ?? $asset->asset_number ?? '-' }}
    </div>

    {{-- Teks Lokasi Ringkas --}}
    <div class="location-short-text" style="font-size: 4px; color: #444; line-height: 1.1; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
        {{ $locationShort }}
    </div>

    {{-- Teks Nama Aset Ringkas --}}
    <div class="asset-name-text" style="font-size: 4px; color: #666; line-height: 1.1; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
        {{ $cleanAssetName }}
    </div>
</div>