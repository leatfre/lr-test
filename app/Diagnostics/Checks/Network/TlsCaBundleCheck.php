<?php

namespace App\Diagnostics\Checks\Network;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class TlsCaBundleCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Network;
    }

    public function label(): string
    {
        return 'TLS / CA bundle';
    }

    public function run(): CheckResult
    {
        if (! extension_loaded('openssl')) {
            return CheckResult::fail($this->group(), $this->label(), 'openssl missing', 'The openssl extension is not loaded; HTTPS/TLS is unavailable.');
        }

        $locations = function_exists('openssl_get_cert_locations') ? openssl_get_cert_locations() : [];
        $defaultFile = $locations['default_cert_file'] ?? null;
        $defaultDir = $locations['default_cert_dir'] ?? null;

        $bundleExists = ($defaultFile && is_readable($defaultFile))
            || ($defaultDir && is_dir($defaultDir) && count(glob($defaultDir.'/*') ?: []) > 0)
            || ini_get('curl.cainfo') || ini_get('openssl.cafile');

        if (! $bundleExists) {
            return CheckResult::warn($this->group(), $this->label(), 'no CA bundle found', 'TLS verification may fail without a trusted CA bundle configured.');
        }

        return CheckResult::pass($this->group(), $this->label(), 'available', $defaultFile ?: $defaultDir ?: 'system default');
    }
}
