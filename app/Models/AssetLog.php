<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model
{
    use HasFactory;

    // Menonaktifkan updated_at jika tabel log hanya mencatat waktu dibuat
    public $timestamps = false; 

    protected $fillable = [
        'asset_id',
        'user_id',
        'action',
        'description',
        'properties',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array', // Otomatis cast JSON database menjadi Array PHP
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke Asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}