<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class ScheduleService
{
    public const CATEGORIES = [
        'stay' => 'Stay',
        'shift_malam' => 'Shift Malam',
        'weekend' => 'Weekend',
    ];

    /** Nomor hari ISO: 1 = Senin ... 7 = Minggu */
    private const REQUIRED_DAYS = [
        'stay' => [1, 2, 3, 4, 5, 6, 7],
        'shift_malam' => [1, 2, 3, 4, 5, 6, 7],
        'weekend' => [6, 7],
    ];

    public function label(?string $category): string
    {
        if ($category === null) {
            return '-';
        }

        return self::CATEGORIES[$category] ?? '-';
    }

    public function isRequiredDay(?string $category, CarbonInterface|string $date): bool
    {
        if ($category === null || ! isset(self::REQUIRED_DAYS[$category])) {
            return false;
        }

        $day = $this->toLocalDate($date);

        return in_array($day->dayOfWeekIso, self::REQUIRED_DAYS[$category], true);
    }

    public function isRequiredToday(?string $category): bool
    {
        return $this->isRequiredDay($category, CarbonImmutable::now(config('app.timezone')));
    }

    /**
     * Daftar tanggal wajib (format Y-m-d) dalam rentang, inklusif.
     *
     * @return array<int, string>
     */
    public function requiredDays(?string $category, CarbonInterface|string $from, CarbonInterface|string $to): array
    {
        $start = $this->toLocalDate($from);
        $end = $this->toLocalDate($to);
        $days = [];

        for ($d = $start; $d->lte($end); $d = $d->addDay()) {
            if ($this->isRequiredDay($category, $d)) {
                $days[] = $d->toDateString();
            }
        }

        return $days;
    }

    private function toLocalDate(CarbonInterface|string $date): CarbonImmutable
    {
        $tz = config('app.timezone');

        $value = $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->setTimezone($tz)
            : CarbonImmutable::parse($date, $tz);

        return $value->startOfDay();
    }
}
