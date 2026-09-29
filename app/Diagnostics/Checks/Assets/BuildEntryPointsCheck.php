<?php

namespace App\Diagnostics\Checks\Assets;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;
use Throwable;

final class BuildEntryPointsCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Assets;
    }

    public function label(): string
    {
        return 'Build entry points';
    }

    public function run(): CheckResult
    {
        $path = ViteManifestCheck::manifestPath();

        if ($path === null) {
            return CheckResult::skipped($this->group(), $this->label(), 'n/a', 'No manifest to read entry points from.');
        }

        try {
            $manifest = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return CheckResult::fail($this->group(), $this->label(), 'unreadable', $e->getMessage());
        }

        if (! is_array($manifest)) {
            return CheckResult::fail($this->group(), $this->label(), 'invalid', 'manifest.json did not decode to an array.');
        }

        $entries = [];

        foreach ($manifest as $key => $entry) {
            if (is_array($entry) && ! empty($entry['isEntry'])) {
                $entries[] = $entry['src'] ?? $key;
            }
        }

        if (empty($entries)) {
            return CheckResult::warn($this->group(), $this->label(), '0 entries', 'The manifest has no entry points.');
        }

        return CheckResult::pass($this->group(), $this->label(), implode(', ', $entries));
    }
}
