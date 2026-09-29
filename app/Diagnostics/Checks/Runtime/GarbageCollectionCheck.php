<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class GarbageCollectionCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'Garbage collection';
    }

    public function run(): CheckResult
    {
        if (! function_exists('gc_enabled')) {
            return CheckResult::skipped($this->group(), $this->label(), 'unknown', 'gc_enabled() unavailable.');
        }

        $enabled = gc_enabled();
        $value = $enabled ? 'enabled' : 'disabled';

        if (! $enabled) {
            return CheckResult::warn($this->group(), $this->label(), $value, 'The cyclic garbage collector is off; long-running workers may leak memory.');
        }

        return CheckResult::pass($this->group(), $this->label(), $value);
    }
}
