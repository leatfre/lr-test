<?php

namespace App\Diagnostics\Checks\Assets;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class ViteManifestCheck implements Check
{
    /**
     * Candidate manifest locations across Vite plugin versions.
     */
    public static function manifestPath(): ?string
    {
        foreach ([
            public_path('build/manifest.json'),
            public_path('build/.vite/manifest.json'),
        ] as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function group(): CheckGroup
    {
        return CheckGroup::Assets;
    }

    public function label(): string
    {
        return 'Vite manifest';
    }

    public function run(): CheckResult
    {
        $path = self::manifestPath();

        if ($path === null) {
            return CheckResult::fail(
                $this->group(),
                $this->label(),
                'missing',
                'No manifest.json found under public/build. Run `npm run build` before deploying.',
            );
        }

        return CheckResult::pass($this->group(), $this->label(), 'present', $path);
    }
}
