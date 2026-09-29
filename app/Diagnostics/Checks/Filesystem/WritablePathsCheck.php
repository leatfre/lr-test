<?php

namespace App\Diagnostics\Checks\Filesystem;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class WritablePathsCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Filesystem;
    }

    public function label(): string
    {
        return 'Writable paths';
    }

    public function run(): CheckResult
    {
        $paths = [
            'storage' => storage_path(),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $children = [];
        $writableCount = 0;

        foreach ($paths as $name => $path) {
            $writable = is_dir($path) && is_writable($path);
            $writableCount += $writable ? 1 : 0;

            $children[] = $writable
                ? CheckResult::pass($this->group(), $name, 'writable')
                : CheckResult::fail($this->group(), $name, is_dir($path) ? 'not writable' : 'missing');
        }

        $total = count($paths);
        $value = "{$writableCount}/{$total}";

        $parent = $writableCount === $total
            ? CheckResult::pass($this->group(), $this->label(), $value)
            : CheckResult::fail($this->group(), $this->label(), $value, 'Laravel needs write access to these paths to boot.');

        return $parent->withChildren(...$children);
    }
}
