<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menggunakan 'asset_audit_items' (bukan asset_audits_items)
        Schema::create('asset_audit_items', function (Blueprint $table) {
            $table->id();
            
            // Menunjuk secara eksplisit ke tabel 'asset_audits'
            $table->foreignId('asset_audit_id')
                  ->constrained('asset_audits')
                  ->onDelete('cascade');
                  
            // Pastikan tabel 'assets' sudah ada
            $table->foreignId('asset_id')
                  ->constrained('assets')
                  ->onDelete('cascade');
                  
            $table->enum('physical_status', ['pending', 'found', 'missing', 'damaged', 'transferred'])->default('pending');
            $table->string('scanned_location_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_audit_items');
    }
};