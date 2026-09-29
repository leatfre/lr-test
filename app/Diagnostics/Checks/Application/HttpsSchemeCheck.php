<?php

namespace App\Diagnostics\Checks\Application;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class HttpsSchemeCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Application;
    }

    public function label(): string
    {
        return 'HTTPS / scheme';
    }

    public function run(): CheckResult
    {
        if (app()->bound('request') && request() !== null) {
            $secure = request()->isSecure();

            return $secure
                ? CheckResult::pass($this->group(), $this->label(), 'https')
                : CheckResult::warn($this->group(), $this->label(), 'http', 'The current request is not over HTTPS.');
        }

        $appUrl = (string) config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'unknown';

        return $scheme === 'https'
            ? CheckResult::pass($this->group(), $this->label(), 'https (from APP_URL)')
            : CheckResult::warn($this->group(), $this->label(), $scheme.' (from APP_URL)', 'Configure APP_URL with https:// once TLS is set up.');
    }
}
