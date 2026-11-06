<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    public const STATUS_HADIR = 'hadir';
    public const STATUS_TIDAK_HADIR = 'tidak_hadir';

    protected $fillable = [
        'user_id',
        'attendance_date',
        'status',
        'attended_at',
        'category_snapshot',
        'latitude',
        'longitude',
        'accuracy_m',
        'distance_m',
        'selfie_path',
        'qr_session_id',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'attended_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy_m' => 'decimal:2',
            'distance_m' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function qrSession(): BelongsTo
    {
        return $this->belongsTo(QrSession::class);
    }
}
