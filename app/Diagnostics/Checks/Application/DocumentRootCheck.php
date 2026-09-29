<?php

namespace App\Diagnostics\Checks\Application;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class DocumentRootCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Application;
    }

    public function label(): string
    {
        return 'Document root';
    }

    public function run(): CheckResult
    {
        $publicPath = rtrim(str_replace('\\', '/', public_path()), '/');
        $documentRoot = null;

        if (app()->bound('request') && request() !== null) {
            $documentRoot = request()->server('DOCUMENT_ROOT');
        }

        if (! $documentRoot) {
            return CheckResult::warn(
                $this->group(),
                $this->label(),
                $publicPath,
                'DOCUMENT_ROOT not available in this context (likely a CLI run). On shared hosting, point the document root at the public/ directory or copy its contents to public_html.',
            );
        }

        $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');

        if ($documentRoot === $publicPath) {
            return CheckResult::pass($this->group(), $this->label(), $documentRoot);
        }

        return CheckResult::warn(
            $this->group(),
            $this->label(),
            $documentRoot,
            "Web server document root ({$documentRoot}) does not match the app's public/ directory ({$publicPath}).",
        );
    }
}
