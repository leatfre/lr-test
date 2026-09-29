<?php

namespace App\Diagnostics\Checks\Filesystem;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class RealpathCacheCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Filesystem;
    }

    public function label(): string
    {
        return 'Realpath cache';
    }

    public function run(): CheckResult
    {
        $size = (string) ini_get('realpath_cache_size');
        $ttl = (string) ini_get('realpath_cache_ttl');

        return CheckResult::pass($this->group(), $this->label(), "size={$size}, ttl={$ttl}s");
    }
}
