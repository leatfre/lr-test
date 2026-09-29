<?php

namespace App\Diagnostics\Checks\Application;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class ConfigCacheCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Application;
    }

    public function label(): string
    {
        return 'Config/route/view cache';
    }

    public function run(): CheckResult
    {
        $cached = [
            'config' => file_exists(base_path('bootstrap/cache/config.php')),
            'routes' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
            'events' => file_exists(base_path('bootstrap/cache/events.php')),
        ];

        $summary = implode(', ', array_map(
            fn (string $name, bool $on) => $name.'='.($on ? 'cached' : 'not cached'),
            array_keys($cached),
            $cached,
        ));

        return CheckResult::pass($this->group(), $this->label(), $summary);
    }
}
