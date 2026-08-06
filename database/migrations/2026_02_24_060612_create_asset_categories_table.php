<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();

            // Self-referencing FK untuk Sub-Kategori (Nullable: Null = Kategori Utama)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('asset_categories')
                ->nullOnDelete();

            $table->string('name');
            $table->string('code_prefix', 10)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexing untuk mempercepat query hirarki
            $table->index(['parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};