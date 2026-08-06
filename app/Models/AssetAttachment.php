<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssetAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'mime_type',
        'is_primary',
        'caption',
        'uploaded_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Accessor URL file
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}