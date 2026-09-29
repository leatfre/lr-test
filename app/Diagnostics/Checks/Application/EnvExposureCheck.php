<?php

namespace App\Diagnostics\Checks\Application;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class EnvExposureCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Application;
    }

    public function label(): string
    {
        return '.env exposure';
    }

    public function run(): CheckResult
    {
        if (file_exists(public_path('.env'))) {
            return CheckResult::warn($this->group(), $this->label(), 'exposed', 'public/.env exists and may be servable directly by the web server.');
        }

        return CheckResult::pass($this->group(), $this->label(), 'not exposed');
    }
}
