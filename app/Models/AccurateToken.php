<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AccurateToken extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */
    protected $table = 'accurate_tokens';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'access_token',
        'refresh_token',
        'expired_at',
        'db_id',
        'session',   // 🔥 tambahan
        'host',      // 🔥 tambahan
    ];

    /*
    |--------------------------------------------------------------------------
    | CAST
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'expired_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | HELPER: TOKEN EXPIRED
    |--------------------------------------------------------------------------
    */
    public function isExpired()
    {
        return !$this->expired_at || Carbon::now()->gte($this->expired_at);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: TOKEN HAMPIR EXPIRED
    |--------------------------------------------------------------------------
    */
    public function isAlmostExpired($minutes = 60)
    {
        return $this->expired_at
            ? Carbon::now()->addMinutes($minutes)->gte($this->expired_at)
            : true;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: CEK SIAP PAKAI
    |--------------------------------------------------------------------------
    */
    public function isReady()
    {
        return $this->access_token
            && $this->db_id
            && $this->session
            && $this->host;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: BASE URL API
    |--------------------------------------------------------------------------
    */
    public function apiUrl($path = '')
    {
        return rtrim($this->host, '/') . '/accurate/api/' . ltrim($path, '/');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: HEADER STANDARD ACCURATE
    |--------------------------------------------------------------------------
    */
    public function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->access_token,
            'X-Session-ID'  => $this->session,
            'Accept'        => 'application/json',
        ];
    }
}