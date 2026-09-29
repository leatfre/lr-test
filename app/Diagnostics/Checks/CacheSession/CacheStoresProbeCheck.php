<?php

namespace App\Diagnostics\Checks\CacheSession;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Predis\Client;
use Throwable;

final class CacheStoresProbeCheck implements Check
{
    private const STORES = ['file', 'database', 'array', 'redis', 'memcached'];

    public function group(): CheckGroup
    {
        return CheckGroup::CacheSession;
    }

    public function label(): string
    {
        return 'Cache stores';
    }

    public function run(): CheckResult
    {
        $children = [];
        $usableCount = 0;

        foreach (self::STORES as $store) {
            $result = $this->probe($store);
            $usableCount += $result->status->value === 'pass' ? 1 : 0;
            $children[] = $result;
        }

        $value = "{$usableCount}/".count(self::STORES).' usable';
        $parent = CheckResult::pass($this->group(), $this->label(), $value);

        return $parent->withChildren(...$children);
    }

    private function probe(string $store): CheckResult
    {
        if ($store === 'redis' && ! extension_loaded('redis') && ! class_exists(Client::class)) {
            return CheckResult::skipped($this->group(), $store, 'skipped', 'No redis client (phpredis or predis) available.');
        }

        if ($store === 'memcached' && ! extension_loaded('memcached')) {
            return CheckResult::skipped($this->group(), $store, 'skipped', 'memcached extension not loaded.');
        }

        if (! is_array(config("cache.stores.{$store}"))) {
            return CheckResult::skipped($this->group(), $store, 'skipped', 'No configuration for this store.');
        }

        $key = 'hosting-check-'.Str::random(8);
        $expected = Str::random(12);

        try {
            $repository = Cache::store($store);
            $repository->put($key, $expected, 30);
            $actual = $repository->get($key);
            $repository->forget($key);

            if ($actual !== $expected) {
                return CheckResult::fail($this->group(), $store, 'unusable', 'Round trip value mismatch.');
            }

            return CheckResult::pass($this->group(), $store, 'usable');
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $store, 'unusable', $e->getMessage());
        }
    }
}
