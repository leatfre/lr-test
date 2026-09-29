<?php

namespace App\Diagnostics\Checks\Assets;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class HotFileCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Assets;
    }

    public function label(): string
    {
        return 'Hot file';
    }

    public function run(): CheckResult
    {
        $hotFile = public_path('hot');

        if (file_exists($hotFile)) {
            return CheckResult::warn(
                $this->group(),
                $this->label(),
                'present',
                'public/hot exists; the app will try to load assets from a Vite dev server. Remove it in production.',
            );
        }

        return CheckResult::pass($this->group(), $this->label(), 'absent');
    }
}
