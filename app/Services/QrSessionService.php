<?php

namespace App\Services;

use App\Models\QrSession;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class QrSessionService
{
    /**
     * Buat atau buat ulang QR untuk hari ini (Asia/Jakarta).
     * Token asli hanya dikembalikan sekali, tidak disimpan.
     *
     * @return array{token: string, session: QrSession, regenerated: bool}
     */
    public function issueForToday(?int $adminId): array
    {
        $now = CarbonImmutable::now(config('app.timezone'));
        $date = $now->toDateString();

        $token = Str::random(40);
        $existing = QrSession::where('attendance_date', $date)->exists();

        $session = QrSession::updateOrCreate(
            ['attendance_date' => $date],
            [
                'token_hash' => $this->hash($token),
                'expires_at' => $now->endOfDay(),
                'revoked_at' => null,
                'created_by' => $adminId,
            ]
        );

        return ['token' => $token, 'session' => $session, 'regenerated' => $existing];
    }

    public function revokeToday(): bool
    {
        $date = CarbonImmutable::now(config('app.timezone'))->toDateString();

        return QrSession::where('attendance_date', $date)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]) > 0;
    }

    /**
     * @return array{valid: bool, reason: string, session: ?QrSession}
     */
    public function verify(?string $token): array
    {
        if ($token === null || $token === '') {
            return $this->result(false, 'invalid');
        }

        $session = QrSession::where('token_hash', $this->hash($token))->first();

        if (! $session || ! hash_equals($session->token_hash, $this->hash($token))) {
            return $this->result(false, 'invalid');
        }

        $now = CarbonImmutable::now(config('app.timezone'));

        if ($session->revoked_at !== null) {
            return $this->result(false, 'revoked', $session);
        }

        if ($session->attendance_date->toDateString() !== $now->toDateString()) {
            return $this->result(false, 'wrong_date', $session);
        }

        if ($session->expires_at->lt($now)) {
            return $this->result(false, 'expired', $session);
        }

        return $this->result(true, 'ok', $session);
    }

    private function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    private function result(bool $valid, string $reason, ?QrSession $session = null): array
    {
        return ['valid' => $valid, 'reason' => $reason, 'session' => $session];
    }
}
