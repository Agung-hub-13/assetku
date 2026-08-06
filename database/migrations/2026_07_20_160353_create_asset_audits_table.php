<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_code')->unique();
            $table->string('title');
            // Pastikan tabel locations & users sudah ada sebelum migrasi ini
            $table->foreignId('location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            $table->foreignId('auditor_id')->constrained('users');
            $table->date('start_date');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_audits');
    }
};