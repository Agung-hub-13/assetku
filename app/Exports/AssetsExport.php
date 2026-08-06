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
        $query = Asset::query();

        // Eager Loading relasi krusial secara lengkap
        $query->with([
            'category',
            'location.parent',
            'transfer.toLocation.parent'
        ]);

        // 1. Filter Berdasarkan Text Search (SINKRON DENGAN CONTROLLER)
        if ($this->request->filled('search')) {
            $search = strtolower($this->request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(assets.name) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(asset_code) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(asset_number) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(serial_number) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(accurate_no) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) like ?', ["%{$search}%"])
                    ->orWhereHas('location', function ($locationQuery) use ($search) {
                        $locationQuery->whereRaw('LOWER(name) like ?', ["%{$search}%"]);
                    });
            });
        }

        // 2. Filter Berdasarkan Dropdown Kategori
        if ($this->request->filled('category_id')) {
            $query->where('category_id', $this->request->category_id);
        }

        // 3. Filter Berdasarkan Dropdown Lokasi Utama
        if ($this->request->filled('location_id')) {
            $query->where('location_id', $this->request->location_id);
        }

        // 4. Filter Sub-Lokasi: Departemen
        if ($this->request->filled('department_id')) {
            $deptSearch = strtolower($this->request->department_id);
            $query->whereHas('location', function ($q) use ($deptSearch) {
                $q->where(function ($subQuery) use ($deptSearch) {
                    $subQuery->whereRaw('LOWER(department_name) like ?', ["%{$deptSearch}%"]);
                    if (is_numeric($deptSearch)) {
                        $subQuery->orWhere('department_id', $deptSearch);
                    }
                });
            });
        }

        // 5. Filter Sub-Lokasi: Lantai
        if ($this->request->filled('floor_id')) {
            $floorSearch = strtolower($this->request->floor_id);
            $query->whereHas('location', function ($q) use ($floorSearch) {
                $q->whereRaw('LOWER(floor) like ?', ["%{$floorSearch}%"]);
            });
        }

        // 6. Filter Sub-Lokasi: Ruangan
        if ($this->request->filled('room_id')) {
            $roomSearch = strtolower($this->request->room_id);
            $query->whereHas('location', function ($q) use ($roomSearch) {
                $q->whereRaw('LOWER(room) like ?', ["%{$roomSearch}%"]);
            });
        }

        // 7. Filter Berdasarkan Dropdown Departemen Akurat
        // 7. Filter Berdasarkan Dropdown Departemen Akurat
        if ($this->request->filled('accurate_department_id')) {
            $accurateDeptId = $this->request->accurate_department_id;

            $query->whereHas('location', function ($q) use ($accurateDeptId) {
                $q->where('accurate_department_id', $accurateDeptId);
            });
        }

        // 8. Filter Berdasarkan Dropdown Status
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        // 9. Filter Berdasarkan Nilai Buku Habis (Card Clickable)
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
            'Deskripsi Lokasi',
            'PIC Lokasi',
            'Departemen',
            'Tanggal Beli',
            'Qty',
            'Harga Aset (Rp)',
            'Nilai Buku (Rp)',
            'Useful Life',
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

        if (!$activeLocation) {
            return [
                $asset->name,
                $asset->asset_code ?? '-',
                $asset->serial_number ?? '-',
                $asset->accurate_no ?? '-',
                $asset->category->name ?? $asset->accurate_category_name ?? '-',
                '-',
                '-',
                '-',
                '-',
                $asset->purchase_date ? Carbon::parse($asset->purchase_date)->format('d M Y') : '-',
                $asset->quantity ?? 0,
                $asset->purchase_price ?? 0,
                $asset->book_value ?? 0,
                ($asset->useful_life_month ?? 0) . ' Bulan',
                strtoupper($asset->status)
            ];
        }

        $locationName = $activeLocation->parent->name ?? $activeLocation->name ?? '-';

        $locationDesc = '-';
        if (!empty($activeLocation->parent_id)) {
            $locationDesc = $activeLocation->name ?? '-';
        } else {
            $locationDesc = $activeLocation->description ?? $activeLocation->building ?? '-';
        }

        $picName = $activeLocation->pic_name ?? '-';

        return [
            $asset->name,
            $asset->asset_code ?? '-',
            $asset->serial_number ?? '-',
            $asset->accurate_no ?? '-',
            $asset->category->name ?? $asset->accurate_category_name ?? '-',
            $locationName,
            $locationDesc,
            $picName,
            $activeLocation->department_name ?? '-',
            $asset->purchase_date ? Carbon::parse($asset->purchase_date)->format('d M Y') : '-',
            $asset->quantity ?? 0,
            $asset->purchase_price ?? 0,
            $asset->book_value ?? 0,
            ($asset->useful_life_month ?? 0) . ' Bulan',
            strtoupper($asset->status)
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => '"Rp "#,##0',
            'M' => '"Rp "#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O'];

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
                $sheet->getStyle('A1:O1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
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
