<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class TimezoneIcuCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'Timezone & ICU';
    }

    public function run(): CheckResult
    {
        $timezone = date_default_timezone_get();
        $intlLoaded = extension_loaded('intl');
        $icuVersion = defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : null;

        $value = $timezone.($intlLoaded ? ' / intl '.($icuVersion ?? 'unknown') : ' / no intl');

        if (! $intlLoaded) {
            return CheckResult::warn($this->group(), $this->label(), $value, 'intl extension not loaded; locale-aware formatting will fall back.');
        }

        return CheckResult::pass($this->group(), $this->label(), $value);
    }
}
