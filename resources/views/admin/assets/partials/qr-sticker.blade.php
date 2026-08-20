@php
    $activeLocation = $asset->transfer->toLocation ?? $asset->location;

    // Pastikan qr_token ada
    if (empty($asset->qr_token)) {
        $asset->qr_token = (string) \Illuminate\Support\Str::uuid();
    }

    // 🔗 UBAH INI: Dari teks biasa menjadi URL Route Public Preview
    $qrPayload = route('assets.public-preview', $asset->qr_token);
@endphp

<div class="stiker-container">
    <div class="brand-text">SLP</div>
    <div class="qr-wrapper">
        {{-- Generator QR code akan merubah URL di atas menjadi bentuk barcode kotak-kotak --}}
        {!! QrCode::format('svg')->size(600)->margin(0)->errorCorrection('M')->generate($qrPayload) !!}
    </div>
    <div class="asset-code-text">
        {{ $asset->asset_code ?? $asset->asset_number ?? '-' }}
    </div>
</div>