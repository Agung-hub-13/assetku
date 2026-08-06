<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Category (Hirarki Kategori & Subkategori)
        $catIT = DB::table('asset_categories')->insertGetId([
            'name' => 'IT & Elektronik',
            'code_prefix' => 'ITE',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $subLaptop = DB::table('asset_categories')->insertGetId([
            'parent_id' => $catIT,
            'name' => 'Laptop & Notebook',
            'code_prefix' => 'LPT',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 2. Location
        $locOffice = DB::table('asset_locations')->insertGetId([
            'code' => 'HQ-L1',
            'name' => 'Kantor Pusat - Lantai 1',
            'building' => 'Gedung A',
            'floor' => '1',
            'room' => 'Ruang IT',
            'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 3. Asset
        $assetId = DB::table('assets')->insertGetId([
            'asset_number' => 'AST-2026-0001',
            'asset_code' => 'LPT-001',
            'name' => 'MacBook Pro M2 16 Inch',
            // 'brand_name' dihapus karena tidak ada kolomnya di database
            'serial_number' => 'C02G1234MD6R',
            'qr_token' => Str::uuid(),
            'category_id' => $subLaptop,
            'location_id' => $locOffice,
            'purchase_date' => '2026-01-10',
            'purchase_price' => 35000000,
            'book_value' => 30000000,
            'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}