<?php

namespace App\Diagnostics\Checks\Extensions;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use PDO;

final class PdoDriversCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Extensions;
    }

    public function label(): string
    {
        return 'PDO drivers';
    }

    public function run(): CheckResult
    {
        if (! extension_loaded('pdo')) {
            return CheckResult::fail($this->group(), $this->label(), 'none', 'The pdo extension is not loaded.');
        }

        $drivers = PDO::getAvailableDrivers();

        if (empty($drivers)) {
            return CheckResult::fail($this->group(), $this->label(), 'none', 'No PDO drivers are available.');
        }

        return CheckResult::pass($this->group(), $this->label(), implode(', ', $drivers));
    }
}
