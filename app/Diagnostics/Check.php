<?php

namespace App\Diagnostics;

interface Check
{
    public function group(): CheckGroup;

    public function label(): string;

    public function run(): CheckResult;
}
