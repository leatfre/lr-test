<?php

namespace App\Diagnostics\Checks\Filesystem;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class SymlinkSupportCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Filesystem;
    }

    public function label(): string
    {
        return 'Symlink support';
    }

    public function run(): CheckResult
    {
        if (! function_exists('symlink')) {
            return CheckResult::fail($this->group(), $this->label(), 'unavailable', 'symlink() does not exist in this build of PHP.');
        }

        $dir = storage_path('framework/testing-symlink-probe');
        $target = $dir.'/target.txt';
        $link = $dir.'/link.txt';

        try {
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            if (! is_dir($dir)) {
                return CheckResult::fail($this->group(), $this->label(), 'unavailable', 'Could not create a temp directory to test symlink().');
            }

            file_put_contents($target, 'probe');

            if (file_exists($link)) {
                @unlink($link);
            }

            $created = @symlink($target, $link);

            if (! $created || ! is_link($link)) {
                return CheckResult::fail($this->group(), $this->label(), 'unavailable', 'symlink() failed. `php artisan storage:link` will not work on this host.');
            }

            return CheckResult::pass($this->group(), $this->label(), 'available');
        } catch (\Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'unavailable', $e->getMessage());
        } finally {
            if (isset($link) && (is_link($link) || file_exists($link))) {
                @unlink($link);
            }
            if (isset($target) && file_exists($target)) {
                @unlink($target);
            }
            if (isset($dir) && is_dir($dir)) {
                @rmdir($dir);
            }
        }
    }
}
