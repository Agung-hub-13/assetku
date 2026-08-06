<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('transfer_type', ['location_change', 'temporary', 'return'])->default('location_change');

            // Relasi Lokasi Asal & Tujuan
            $table->foreignId('from_location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('asset_locations')->nullOnDelete();

            // Relasi Departemen Asal & Tujuan
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_department_id')->nullable()->constrained('departments')->nullOnDelete();

            // Relasi User/Penanggung Jawab Asal & Tujuan
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Snapshot Name (Menjaga riwayat teks jika data master diubah/dihapus)
            $table->string('from_location_name')->nullable();
            $table->string('from_department_name')->nullable();
            $table->string('from_user_name')->nullable(); // <-- DITAMBAHKAN AGAR KONSISTEN
            $table->string('to_location_name')->nullable();
            $table->string('to_department_name')->nullable();
            $table->string('to_user_name')->nullable();   // <-- DITAMBAHKAN AGAR KONSISTEN

            $table->date('transfer_date');
            $table->string('document_number')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();

            // STATUS & APPROVAL
            $table->enum('status', ['draft', 'waiting_approval', 'approved', 'completed', 'rejected', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('attachment')->nullable();

            // SCAN QR HP
            $table->enum('entry_method', ['manual', 'qr_scan'])->default('manual');

            // Accurate Sync
            $table->string('accurate_transaction_id')->nullable();
            $table->string('accurate_transaction_no')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('accurate_last_update')->nullable();
            $table->text('sync_error')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['asset_id', 'status']);
            $table->index(['to_location_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_transfers');
    }
};