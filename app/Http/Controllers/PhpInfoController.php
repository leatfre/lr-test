<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PhpInfoController extends Controller
{
    public function __invoke(): Response
    {
        ob_start();
        phpinfo();
        $captured = (string) ob_get_clean();

        $html = <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>phpinfo</title>
            </head>
            <body>
            {$captured}
            </body>
            </html>
            HTML;

        return response($html);
    }
}
