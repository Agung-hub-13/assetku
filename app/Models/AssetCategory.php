<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'code_prefix',
        'description',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    /**
     * Relasi ke Parent Kategori (Kategori Atasan)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'parent_id');
    }

    /**
     * Relasi ke Sub-Kategori (Anak-anak Kategori)
     */
    public function children(): HasMany
    {
        return $this->hasMany(AssetCategory::class, 'parent_id');
    }

    /**
     * Scope untuk mengambil hanya Parent Kategori (Utama)
     */
    public function scopeOnlyParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope untuk mengambil hanya Sub-Kategori
     */
    public function scopeOnlyChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }
}