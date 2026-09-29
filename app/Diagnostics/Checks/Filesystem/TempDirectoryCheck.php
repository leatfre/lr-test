<?php

namespace App\Diagnostics\Checks\Filesystem;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class TempDirectoryCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Filesystem;
    }

    public function label(): string
    {
        return 'Temp directory';
    }

    public function run(): CheckResult
    {
        $dir = sys_get_temp_dir();

        if ($dir === '' || ! is_dir($dir) || ! is_writable($dir)) {
            return CheckResult::fail($this->group(), $this->label(), $dir ?: 'unknown', 'The system temp directory is missing or not writable.');
        }

        $probe = $dir.DIRECTORY_SEPARATOR.'hosting-check-'.uniqid().'.tmp';

        try {
            $written = @file_put_contents($probe, 'probe');

            if ($written === false) {
                return CheckResult::fail($this->group(), $this->label(), $dir, 'Failed to write a probe file.');
            }

            return CheckResult::pass($this->group(), $this->label(), $dir);
        } finally {
            if (file_exists($probe)) {
                @unlink($probe);
            }
        }
    }
}
