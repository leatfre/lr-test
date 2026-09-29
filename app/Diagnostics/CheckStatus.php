<?php

namespace App\Diagnostics;

enum CheckStatus: string
{
    case Pass = 'pass';
    case Warn = 'warn';
    case Fail = 'fail';
    case Skipped = 'skipped';

    /**
     * A numeric weight used to compute the worst status across a set of results.
     */
    public function severity(): int
    {
        return match ($this) {
            self::Skipped => 0,
            self::Pass => 1,
            self::Warn => 2,
            self::Fail => 3,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pass => 'Pass',
            self::Warn => 'Warn',
            self::Fail => 'Fail',
            self::Skipped => 'Skipped',
        };
    }

    /**
     * Tailwind classes for a pill/badge rendering of this status.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pass => 'bg-emerald-500/15 text-emerald-300 ring-1 ring-inset ring-emerald-500/30',
            self::Warn => 'bg-amber-500/15 text-amber-300 ring-1 ring-inset ring-amber-500/30',
            self::Fail => 'bg-rose-500/15 text-rose-300 ring-1 ring-inset ring-rose-500/30',
            self::Skipped => 'bg-slate-500/15 text-slate-300 ring-1 ring-inset ring-slate-500/30',
        };
    }

    /**
     * Tailwind classes for a small solid status dot.
     */
    public function dotClasses(): string
    {
        return match ($this) {
            self::Pass => 'bg-emerald-400',
            self::Warn => 'bg-amber-400',
            self::Fail => 'bg-rose-400',
            self::Skipped => 'bg-slate-400',
        };
    }
}
