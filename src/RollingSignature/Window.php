<?php

namespace Cabinet\RollingSignature;

use Carbon\CarbonImmutable;

class Window
{
    public function __construct(
        public readonly int $index,
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly int $lengthInMinutes,
    ) {
    }

    protected static function getEpoch(): CarbonImmutable
    {
        return CarbonImmutable::create(1970, 1, 1, 0, 0, 0, 'UTC');
    }

    protected static function getWindowIndex(CarbonImmutable $date, int $windowDurationInMinutes): int
    {
        $minutesSinceEpoch = static::getEpoch()->diffInMinutes($date);

        return (int) floor($minutesSinceEpoch / $windowDurationInMinutes);
    }

    public static function getLengthInMinutes(): int
    {
        return config('cabinet.rolling_signature.expires_after_minutes', 60 * 24 * 2);
    }

    public static function make(CarbonImmutable $date, ?int $lengthInMinutes = null): static
    {
        $lengthInMinutes ??= static::getLengthInMinutes();
        $window = static::getWindowIndex($date, $lengthInMinutes);

        $start = static::getEpoch()
            ->addMinutes($lengthInMinutes * $window);

        $end = $start->addMinutes($lengthInMinutes);

        return new static($window, $start, $end, $lengthInMinutes);
    }

    public static function now(): static
    {
        return static::make(date: CarbonImmutable::now());
    }
}
