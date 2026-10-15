<?php

namespace App\Exports;

use App\Models\Asset;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    protected $request;

    // Menangkap request pencarian dari Controller
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Mengambil kumpulan data aset berdasarkan filter pencarian yang SINKRON dengan AssetController.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $user = auth()->user();

        // 0. Ambil ID lokasi user jika bukan Super Admin (Menyamakan dengan AssetController)
        $userLocationIds = $user && $user->hasRole('Super Admin')
            ? []
            : ($user ? $user->locations()->pluck('asset_locations.id') : collect());

        $query = Asset::query();

        // Eager Loading relasi krusial secara lengkap (ambil parent bertingkat jika ada)
        $query->with([
            'category',
            'location.parent.parent',
            'transfer.toLocation.parent.parent'
        ]);

        // Batasi data aset berdasarkan lokasi user (Jika bukan Super Admin)
        if ($user && !$user->hasRole('Super Admin')) {
            $query->where(function ($q) use ($userLocationIds) {
                $q->whereIn('location_id', $userLocationIds)
                    ->orWhereNull('location_id');
            });
        }

        // 1. Filter Berdasarkan Text Search (Sinkron dengan Controller menggunakan ilike)
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('assets.name', 'ilike', "%{$search}%")
                    ->orWhere('asset_code', 'ilike', "%{$search}%")
                    ->orWhere('asset_number', 'ilike', "%{$search}%")
                    ->orWhere('serial_number', 'ilike', "%{$search}%")
                    ->orWhere('accurate_no', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhereHas('location', function ($locationQuery) use ($search) {
                        $locationQuery->where('name', 'ilike', "%{$search}%")
                            ->orWhere('building', 'ilike', "%{$search}%")
                            ->orWhere('floor', 'ilike', "%{$search}%")
                            ->orWhere('room', 'ilike', "%{$search}%");
                    });
            });
        }

        // 2. Filter Kategori
        if ($this->request->filled('category_id')) {
            $query->where('category_id', $this->request->category_id);
        }

        // 3. Filter Ruangan / Lokasi
        if ($this->request->filled('room_id')) {
            $roomId = $this->request->room_id;
            if ($user && ($user->hasRole('Super Admin') || $userLocationIds->contains($roomId))) {
                $query->where('location_id', $roomId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // 4. Filter Status
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        // 5. Filter Nilai Buku Habis
        if ($this->request->get('depreciated') == '1') {
            $query->where('book_value', '<=', 0);
        }

        return $query->orderBy('updated_at', 'desc')->get();
    }

    /**
     * Mendefinisikan header kolom spreadsheet XLSX.
     */
    public function headings(): array
    {
        return [
            'Nama Aset',
            'Asset Code',
            'Serial Number',
            'Accurate No',
            'Kategori',
            'Nama Lokasi',
            'Gedung',
            'Lantai',
            'Ruang',
            'Deskripsi Lokasi',
            'PIC Lokasi',
            'Departemen',
            'Tanggal Beli',
            'Qty',
            'Harga Aset (Rp)',
            'Nilai Buku (Rp)',
            'Useful Life',
            'Deskripsi',
            'Status'
        ];
    }

    /**
     * Memetakan properti setiap model Asset ke dalam baris kolom spreadsheet.
     */
    public function map($asset): array
    {
        $activeLocation = null;

        if ($asset->transfer) {
            if ($asset->transfer instanceof \Illuminate\Database\Eloquent\Collection) {
                $latestTransfer = $asset->transfer->sortByDesc('created_at')->first();
                $activeLocation = $latestTransfer->toLocation ?? $asset->location;
            } else {
                $activeLocation = $asset->transfer->toLocation ?? $asset->location;
            }
        } else {
            $activeLocation = $asset->location;
        }

        // Jika lokasi tidak ada
        if (!$activeLocation) {
            return [
                $asset->name,
                $asset->asset_code ?? '-',
                $asset->serial_number ?? '-',
                $asset->accurate_no ?? '-',
                $asset->category->name ?? $asset->accurate_category_name ?? '-',
                '-', // Nama Lokasi
                '-', // Gedung
                '-', // Lantai
                '-', // Ruang
                '-', // Deskripsi Lokasi
                '-', // PIC Lokasi
                '-', // Departemen
                $asset->purchase_date ? Carbon::parse($asset->purchase_date)->format('d M Y') : '-',
                $asset->quantity ?? 0,
                $asset->purchase_price ?? 0,
                $asset->book_value ?? 0,
                ($asset->useful_life_month ?? 0) . ' Bulan',
                $asset->description ?? '-',
                strtoupper($asset->status)
            ];
        }

        /**
         * Memetakan struktur hirarki Lokasi, Gedung, Lantai, dan Ruang.
         * Sesuaikan bagian ini jika struktur database Anda menggunakan relasi parent/child atau kolom langsung pada tabel locations.
         */
        $namaLokasi = '-';
        $gedung = '-';
        $lantai = '-';
        $ruang = '-';

        // Contoh asumsi struktur: 
        // Jika model Location bertingkat (parent-child), atau memiliki kolom langsung:
        $roomName   = $activeLocation->room ?? $activeLocation->name ?? '-';
        $floorName  = $activeLocation->floor ?? '-';
        $buildingName = $activeLocation->building ?? '-';
        $parentLocName = $activeLocation->parent->name ?? '-';

        // Logika pengisian kolom terpisah (bisa disesuaikan dengan skema database Anda):
        $ruang = $roomName;
        $lantai = $floorName;
        $gedung = $buildingName !== '-' ? $buildingName : ($activeLocation->parent->building ?? '-');
        $namaLokasi = $parentLocName !== '-' ? $parentLocName : ($activeLocation->name ?? '-');

        $locationDesc = $activeLocation->description ?? '-';
        $picName = $activeLocation->pic_name ?? '-';
        $departmentName = $activeLocation->department_name ?? '-';

        return [
            $asset->name,
            $asset->asset_code ?? '-',
            $asset->serial_number ?? '-',
            $asset->accurate_no ?? '-',
            $asset->category->name ?? $asset->accurate_category_name ?? '-',
            $namaLokasi,
            $gedung,
            $lantai,
            $ruang,
            $locationDesc,
            $picName,
            $departmentName,
            $asset->purchase_date ? Carbon::parse($asset->purchase_date)->format('d M Y') : '-',
            $asset->quantity ?? 0,
            $asset->purchase_price ?? 0,
            $asset->book_value ?? 0,
            ($asset->useful_life_month ?? 0) . ' Bulan',
            $asset->description ?? '-',
            strtoupper($asset->status)
        ];
    }

    public function columnFormats(): array
    {
        return [
            'O' => '"Rp "#,##0', // Kolom Harga Aset (Rp) bergeser ke huruf O
            'P' => '"Rp "#,##0', // Kolom Nilai Buku (Rp) bergeser ke huruf P
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Total kolom dari A sampai S (19 Kolom)
                $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];

                foreach ($columns as $column) {
                    if ($column === 'A') {
                        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(30);
                        $sheet->getStyle('A')->getAlignment()->setWrapText(true);
                    } else {
                        $currentWidth = $sheet->getColumnDimension($column)->getWidth();
                        if ($currentWidth > 0) {
                            $sheet->getColumnDimension($column)->setAutoSize(false)->setWidth($currentWidth + 4);
                        }
                    }
                }

                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getStyle('A1:S1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            },
        ];
    }

    /**
     * Mengunduh berkas XLSX dengan meneruskan parameter request yang aktif.
     */
    public static function exportExcel(Request $request): BinaryFileResponse
    {
        $fileName = 'Daftar_Aset_AssetKu_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new self($request), $fileName);
    }
}