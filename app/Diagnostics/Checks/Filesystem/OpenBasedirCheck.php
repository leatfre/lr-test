<?php

namespace App\Diagnostics\Checks\Filesystem;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class OpenBasedirCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Filesystem;
    }

    public function label(): string
    {
        return 'open_basedir';
    }

    public function run(): CheckResult
    {
        $raw = trim((string) ini_get('open_basedir'));

        if ($raw === '') {
            return CheckResult::pass($this->group(), $this->label(), 'not restricted');
        }

        return CheckResult::warn($this->group(), $this->label(), 'restricted', $raw);
    }
}
