<?php

namespace App\Diagnostics\Checks\CacheSession;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

final class CacheDriverCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::CacheSession;
    }

    public function label(): string
    {
        return 'Cache driver round trip';
    }

    public function run(): CheckResult
    {
        $driver = (string) config('cache.default');
        $key = 'hosting-check-'.Str::random(8);
        $expected = Str::random(12);

        try {
            Cache::put($key, $expected, 30);
            $actual = Cache::get($key);
            Cache::forget($key);

            if ($actual !== $expected) {
                return CheckResult::fail($this->group(), $this->label(), $driver, 'Value read back did not match what was written.');
            }

            return CheckResult::pass($this->group(), $this->label(), $driver);
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), $driver, $e->getMessage());
        }
    }
}
