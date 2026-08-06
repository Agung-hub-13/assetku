<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_number')->unique();
            $table->string('asset_code')->nullable();
            $table->string('name');
            $table->string('serial_number')->nullable();
            $table->longText('description')->nullable();
            $table->uuid('qr_token')->unique()->nullable();

            // Relasi Master
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Keuangan & Pembelian
            $table->date('purchase_date')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('purchase_price', 18, 2)->default(0);
            $table->decimal('total_price', 18, 2)->default(0);

            // Depresiasi
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'none'])->default('straight_line');
            $table->decimal('book_value', 18, 2)->default(0);
            $table->decimal('residual_value', 18, 2)->default(0);
            $table->decimal('accumulated_depreciation', 18, 2)->default(0);
            $table->integer('useful_life_month')->nullable();

            // Maintenance Schedule
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();

            // Status & Kondisi
            $table->enum('status', ['draft', 'active', 'borrowed', 'maintenance', 'disposed', 'lost'])->default('draft');

            // Accurate Integration
            $table->unsignedBigInteger('accurate_item_id')->nullable();
            $table->unsignedBigInteger('accurate_fixed_asset_id')->nullable();
            $table->unsignedBigInteger('accurate_purchase_id')->nullable();
            $table->unsignedBigInteger('accurate_db_id')->nullable();
            $table->string('accurate_session')->nullable();
            $table->string('accurate_host')->nullable();
            $table->string('accurate_no')->nullable();
            $table->string('accurate_name')->nullable();
            $table->string('accurate_item_type')->nullable();

            $table->boolean('is_synced')->default(false);
            $table->boolean('from_accurate')->default(false);
            $table->boolean('auto_sync')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('accurate_last_update')->nullable();
            $table->timestamp('accurate_last_pull_at')->nullable();
            $table->timestamp('accurate_last_push_at')->nullable();
            $table->longText('sync_error')->nullable();
            $table->longText('accurate_raw_json')->nullable();
            $table->string('accurate_sync_hash', 64)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
