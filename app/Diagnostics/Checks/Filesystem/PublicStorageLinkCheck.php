<?php

namespace App\Diagnostics\Checks\Filesystem;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class PublicStorageLinkCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Filesystem;
    }

    public function label(): string
    {
        return 'Public storage link';
    }

    public function run(): CheckResult
    {
        $link = public_path('storage');

        if (is_link($link)) {
            return CheckResult::pass($this->group(), $this->label(), 'linked', readlink($link) ?: null);
        }

        if (is_dir($link)) {
            return CheckResult::warn($this->group(), $this->label(), 'directory (not a symlink)', 'public/storage exists but is not a symlink; likely copied manually.');
        }

        return CheckResult::warn($this->group(), $this->label(), 'missing', 'Run `php artisan storage:link` if the app serves user uploads.');
    }
}
