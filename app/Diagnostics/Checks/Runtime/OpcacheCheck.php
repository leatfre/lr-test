<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class OpcacheCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'OPcache';
    }

    public function run(): CheckResult
    {
        if (! extension_loaded('Zend OPcache')) {
            return CheckResult::warn($this->group(), $this->label(), 'not loaded', 'OPcache improves performance significantly under PHP-FPM.');
        }

        $enabled = (bool) ini_get('opcache.enable');

        if (! $enabled) {
            return CheckResult::warn($this->group(), $this->label(), 'disabled', 'OPcache extension is loaded but disabled via ini configuration.');
        }

        try {
            $status = function_exists('opcache_get_status') ? opcache_get_status(false) : false;
        } catch (\Throwable) {
            $status = false;
        }

        if ($status === false) {
            return CheckResult::warn($this->group(), $this->label(), 'enabled', 'Status API unavailable (opcache.enable_cli may be off).');
        }

        $cachedScripts = $status['opcache_statistics']['num_cached_scripts'] ?? 0;

        return CheckResult::pass($this->group(), $this->label(), 'enabled', "{$cachedScripts} scripts cached.");
    }
}
