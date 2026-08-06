<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique();

            // Karyawan yang mengajukan & Departemen asalnya (karena departemen sudah mandiri)
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            // Relasi ke Lokasi Peminjaman (disamakan namanya menjadi location_id)
            $table->foreignId('location_id')->nullable()->constrained('asset_locations')->nullOnDelete();

            // Aset yang dipinjam
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();

            // Tanggal
            $table->date('request_date');
            $table->date('start_date');
            $table->date('expected_return_date');
            $table->date('actual_return_date')->nullable();

            // Kondisi
            $table->enum('condition_before', ['good', 'minor_damage', 'heavy_damage'])->default('good');
            $table->enum('condition_after', ['good', 'minor_damage', 'heavy_damage', 'lost'])->nullable();

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            // TRACKING: Status Peminjaman
            $table->enum('status', ['pending', 'approved', 'rejected', 'borrowed', 'returned', 'overdue'])->default('pending');

            // APPROVAL: Atasan / Admin
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // NOTIFIKASI
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexing
            $table->index(['asset_id', 'location_id', 'status']);
            $table->index(['status', 'expected_return_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_loans');
    }
};
