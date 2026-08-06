<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification as BaseNotification;

class Notification extends BaseNotification
{
    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Tipe primary key (UUID).
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indikasi apakah ID auto incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
}