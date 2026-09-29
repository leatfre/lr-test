<?php

namespace App\Diagnostics\Checks\Extensions;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class OptionalExtensionsCheck implements Check
{
    private const EXTENSIONS = [
        'gd', 'imagick', 'intl', 'zip', 'bcmath', 'sodium', 'exif',
        'redis', 'memcached', 'pcntl', 'posix', 'soap', 'ldap',
        'opcache', 'apcu', 'gmp', 'sockets',
    ];

    public function group(): CheckGroup
    {
        return CheckGroup::Extensions;
    }

    public function label(): string
    {
        return 'Optional extensions';
    }

    public function run(): CheckResult
    {
        $children = [];
        $loadedCount = 0;

        foreach (self::EXTENSIONS as $extension) {
            // The Zend OPcache extension registers itself as "Zend OPcache", not "opcache".
            $loaded = $extension === 'opcache'
                ? extension_loaded('Zend OPcache') || extension_loaded('opcache')
                : extension_loaded($extension);

            $loadedCount += $loaded ? 1 : 0;

            $children[] = $loaded
                ? CheckResult::pass($this->group(), $extension, 'loaded')
                : CheckResult::warn($this->group(), $extension, 'missing');
        }

        $total = count(self::EXTENSIONS);
        $value = "{$loadedCount}/{$total}";

        $parent = $loadedCount === $total
            ? CheckResult::pass($this->group(), $this->label(), $value)
            : CheckResult::warn($this->group(), $this->label(), $value, 'Missing optional extensions may disable specific features.');

        return $parent->withChildren(...$children);
    }
}
