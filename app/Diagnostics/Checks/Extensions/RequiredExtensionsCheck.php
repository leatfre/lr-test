<?php

namespace App\Diagnostics\Checks\Extensions;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class RequiredExtensionsCheck implements Check
{
    /**
     * The extension set Laravel documents as required.
     */
    private const EXTENSIONS = [
        'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring',
        'openssl', 'pcre', 'pdo', 'session', 'tokenizer', 'xml',
    ];

    public function group(): CheckGroup
    {
        return CheckGroup::Extensions;
    }

    public function label(): string
    {
        return 'Required extensions';
    }

    public function run(): CheckResult
    {
        $children = [];
        $loadedCount = 0;

        foreach (self::EXTENSIONS as $extension) {
            $loaded = extension_loaded($extension);
            $loadedCount += $loaded ? 1 : 0;

            $children[] = $loaded
                ? CheckResult::pass($this->group(), $extension, 'loaded')
                : CheckResult::fail($this->group(), $extension, 'missing');
        }

        $total = count(self::EXTENSIONS);
        $value = "{$loadedCount}/{$total}";

        $parent = $loadedCount === $total
            ? CheckResult::pass($this->group(), $this->label(), $value)
            : CheckResult::fail($this->group(), $this->label(), $value, 'Laravel will not boot without every required extension.');

        return $parent->withChildren(...$children);
    }
}
