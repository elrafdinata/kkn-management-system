<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrSession extends Model
{
    protected $fillable = ['attendance_date', 'token_hash', 'expires_at', 'revoked_at', 'created_by'];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
