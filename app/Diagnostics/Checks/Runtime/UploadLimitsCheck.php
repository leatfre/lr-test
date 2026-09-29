<?php

namespace App\Diagnostics\Checks\Runtime;

use App\Diagnostics\Check;
use App\Diagnostics\CheckGroup;
use App\Diagnostics\CheckResult;

final class UploadLimitsCheck implements Check
{
    public function group(): CheckGroup
    {
        return CheckGroup::Runtime;
    }

    public function label(): string
    {
        return 'Upload limits';
    }

    public function run(): CheckResult
    {
        $uploadMax = (string) ini_get('upload_max_filesize');
        $postMax = (string) ini_get('post_max_size');
        $value = "upload_max_filesize={$uploadMax}, post_max_size={$postMax}";

        $uploadsEnabled = ini_get('file_uploads');

        if (! $uploadsEnabled) {
            return CheckResult::fail($this->group(), $this->label(), $value, 'file_uploads is disabled in php.ini.');
        }

        return CheckResult::pass($this->group(), $this->label(), $value);
    }
}
