<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stiker Label Aset - {{ $asset->asset_code ?? $asset->asset_number }}</title>
    <style>
        @page {
            size: auto;
            margin: 0mm;
        }

        body {
            font-family: 'Arial', Helvetica, sans-serif;
            margin: 0;
            padding: 2px;
            color: #000000;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
        }

        .stiker-container {
            border: 1.5px solid #000000;
            padding: 6px;
            border-radius: 4px;
            width: 255px;
            /* Sedikit diperlebar agar muat lebih leluasa */
            background: #ffffff;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 8.5px;
            font-weight: 900;
            letter-spacing: 0.3px;
            color: #000000;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .left-cell {
            width: 70px;
            vertical-align: top;
            /* QR Code di atas agar sejajar dengan mulainya teks */
            text-align: center;
            padding-right: 6px;
        }

        .qr-wrapper {
            border: 1px solid #000000;
            padding: 2px;
            display: inline-block;
            line-height: 0;
            background: #ffffff;
        }

        .right-cell {
            vertical-align: top;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-td {
            padding-bottom: 4px;
            vertical-align: top;
        }

        .label-inline {
            font-size: 6px;
            color: #444444;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 0.5px;
            letter-spacing: 0.2px;
        }

        .value-inline {
            font-size: 7.5px;
            font-weight: bold;
            display: block;
            color: #000000;
            line-height: 1.15;
            word-break: break-word;
            white-space: normal;
            /* Memastikan teks turun ke bawah jika panjang (tidak terpotong) */
        }
    </style>
</head>

<body onload="window.print(); window.close();">

    <div class="stiker-container">
        <!-- Header Nama Perusahaan -->
        <div class="header">
            <div class="company-name">PT SENTRAL LAYANAN PRIMA</div>
        </div>

        @php
        $activeLocation = $asset->transfer->toLocation ?? $asset->location;

        $qrPayload = "AST:" . ($asset->asset_code ?? '-') . "\n" .
        "NM:" . $asset->name . "\n" .
        "LOC:" . ($activeLocation->name ?? '-');
        @endphp

        <!-- Tabel Konten Utama -->
        <table class="content-table">
            <tr>
                <!-- SISI KIRI: QR Code -->
                <td class="left-cell">
                    <div class="qr-wrapper">
                        {!! QrCode::size(64)->margin(0)->generate($qrPayload) !!}
                    </div>
                </td>

                <!-- SISI KANAN: Informasi Detail -->
                <td class="right-cell">
                    <table class="info-table">
                        <!-- Baris 1: Kode Aset & Nama Barang -->
                        <tr>
                            <td class="info-td" style="width: 50%; padding-right: 2px;">
                                <span class="label-inline">Kode Aset</span>
                                <span class="value-inline">{{ $asset->asset_code ?? '-' }}</span>
                            </td>
                            <td class="info-td" style="width: 50%;">
                                <span class="label-inline">Nama Barang</span>
                                <span class="value-inline">{{ $asset->name }}</span>
                            </td>
                        </tr>

                        <!-- Baris 2: No. SN -->
                        <tr>
                            <td class="info-td" style="width: 50%; padding-right: 2px;">
                                <span class="label-inline">No. SN</span>
                                <span class="value-inline">{{ $asset->serial_number ?: '-' }}</span>
                            </td>

                            <td class="info-td" style="width: 50%;">
                                <span class="label-inline">No. Accurate</span>
                                <span class="value-inline">{{ $asset->accurate_no ?? '-' }}</span>
                            </td>
                        </tr>

                        <!-- Baris 3: Lokasi (Full Span ke samping agar leluasa & tidak terpotong) -->
                        <tr>
                            <td class="info-td" colspan="2">
                                <span class="label-inline">Lokasi</span>
                                <span class="value-inline">
                                    @php
                                    if ($activeLocation) {
                                    $locParts = [];
                                    if (!empty($activeLocation->name)) $locParts[] = $activeLocation->name;
                                    if (!empty($activeLocation->building)) {
                                    $bldg = $activeLocation->building;
                                    $locParts[] = stripos($bldg, 'gedung') === false ? 'Gedung ' . $bldg : $bldg;
                                    }
                                    if (!empty($activeLocation->room)) {
                                    $room = $activeLocation->room;
                                    $locParts[] = (stripos($room, 'r') === false && stripos($room, 'ruang') === false) ? 'R. ' . $room : $room;
                                    }

                                    $locDetail = implode(', ', $locParts); // Menggunakan koma sebagai pemisah
                                    } else {
                                    $locDetail = '-';
                                    }
                                    @endphp
                                    {{ $locDetail }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>