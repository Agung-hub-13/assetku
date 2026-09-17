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

<div class="stiker-container" style="box-sizing: border-box; width: 22mm; margin: 0 auto 1mm auto; padding: 0.5mm; background: #ffffff; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'Arial', Helvetica, sans-serif;">
    
    {{-- Brand / Logo --}}
    <div class="brand-text" style="font-size: 6.5pt; font-weight: 800; color: #000000; margin-bottom: 0.5px; line-height: 1.0; text-transform: uppercase; width: 100%;">
        SLP
    </div>
    
    {{-- QR Code (Dikecilkan sedikit ke size 75 agar sisa tempat untuk teks lebih lega) --}}
    <div class="qr-wrapper" style="margin: 0 auto; width: 100%; line-height: 0;">
        {!! QrCode::format('svg')->size(75)->margin(0)->errorCorrection('M')->generate($qrPayload) !!}
    </div>
    
    {{-- Kode Aset --}}
    <div class="asset-code-text" style="font-size: 6pt; font-weight: 900; color: #000000; line-height: 1.1; margin-top: 0.5px; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">
        {{ $asset->asset_code ?? $asset->asset_number ?? '-' }}
    </div>

    {{-- Teks Lokasi Ringkas --}}
    <div class="location-short-text" style="font-size: 4.5pt; font-weight: 700; color: #000000; line-height: 1.1; margin-top: 0.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">
        {{ $locationShort }}
    </div>

    {{-- Teks Nama Aset Ringkas --}}
    <div class="asset-name-text" style="font-size: 4.5pt; font-weight: 700; color: #000000; line-height: 1.1; margin-top: 0.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;">
        {{ $cleanAssetName }}
    </div>

</div>