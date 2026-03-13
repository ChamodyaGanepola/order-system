<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Disable CSRF verification for all routes.
     */
    protected $except = [
        '*'
    ];
}
