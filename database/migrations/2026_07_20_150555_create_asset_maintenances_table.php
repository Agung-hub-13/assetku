<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            
            // -------------------------------------------------------------
            // INTEGRASI API BMS (Lebih Aman dengan Unique & Sync Status)
            // -------------------------------------------------------------
            $table->string('bms_work_order_id')->nullable()->unique(); // Mencegah data ganda dari API BMS
            $table->timestamp('bms_synced_at')->nullable();           // Tanggal & waktu sukses sync
            $table->enum('sync_status', ['synced', 'pending', 'failed'])->default('synced'); // Tracking status sync

            $table->string('ticket_number')->unique();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();

            // Kategori Maintenance & Urgensi
            $table->enum('type', ['routine', 'repair'])->default('routine');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->string('title');
            $table->text('description')->nullable();

            // DETAIL KERUSAKAN & TINDAKAN (Data Lapangan dari BMS)
            $table->text('issue_description')->nullable(); 
            $table->string('damaged_parts')->nullable();    
            $table->text('action_taken')->nullable();     
            $table->text('completion_notes')->nullable(); 

            // Penugasan
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('technician_name')->nullable(); // Nama teknisi jika dikirim dari BMS eksternal
            $table->string('vendor_name')->nullable();

            // Jadwal & Reminder
            $table->enum('frequency', ['none', 'monthly', 'quarterly', 'yearly'])->default('none');
            $table->date('due_date')->nullable(); 
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->timestamp('work_started_at')->nullable();
            $table->timestamp('work_completed_at')->nullable();

            $table->timestamp('reminder_sent_at')->nullable();
            $table->boolean('is_reminder_active')->default(false);
            $table->unsignedTinyInteger('reminder_days_before')->default(3);
            $table->string('reminder_email')->nullable();

            // Pencatatan Biaya (Financial & Depreciation Log)
            $table->decimal('cost_sparepart', 18, 2)->default(0);
            $table->decimal('cost_service', 18, 2)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);

            // Status & Pelapor
            $table->enum('status', ['reported', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('reported');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexing Optimasi Performance Query API & Scheduler
            $table->index(['asset_id', 'status', 'type']);
            $table->index(['due_date', 'status']);
            $table->index(['status', 'sync_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
    }
};