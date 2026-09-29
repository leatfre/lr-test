<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class MemoryLimitCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'Memory limit';
    }

    public function run(): CheckResult
    {
        $raw = (string) ini_get('memory_limit');
        $bytes = $this->toBytes($raw);

        if ($bytes === -1) {
            return CheckResult::warn($this->group(), $this->label(), 'unlimited', 'No memory limit is enforced.');
        }

        if ($bytes < 128 * 1024 * 1024) {
            return CheckResult::warn($this->group(), $this->label(), $raw, 'Below the 128M Laravel commonly needs for Composer/artisan operations.');
        }

        return CheckResult::pass($this->group(), $this->label(), $raw);
    }

    private function toBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }
}
