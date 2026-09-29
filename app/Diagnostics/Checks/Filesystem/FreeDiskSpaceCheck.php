<?php

namespace App\Diagnostics\Checks\Filesystem;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class FreeDiskSpaceCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Filesystem;
    }

    public function label(): string
    {
        return 'Free disk space';
    }

    public function run(): CheckResult
    {
        try {
            $free = @disk_free_space(base_path());
        } catch (\Throwable) {
            $free = false;
        }

        if ($free === false) {
            return CheckResult::skipped($this->group(), $this->label(), 'unknown', 'disk_free_space() is unavailable or restricted.');
        }

        $value = $this->humanBytes((float) $free);

        if ($free < 100 * 1024 * 1024) {
            return CheckResult::warn($this->group(), $this->label(), $value, 'Less than 100MB free; deploys and log growth may fail.');
        }

        return CheckResult::pass($this->group(), $this->label(), $value);
    }

    private function humanBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
