<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'description', 'ip_address'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catat satu tindakan admin. Contoh: ActivityLog::record($request, 'buat_akun', 'Membuat akun budi');
     */
    public static function record(\Illuminate\Http\Request $request, string $action, ?string $description = null): void
    {
        static::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
        ]);
    }
}
