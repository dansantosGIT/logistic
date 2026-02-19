<?php

/**
 * Development server router for the Laravel application.
 * This file is used by `php -S` when `php artisan serve` is run
 * and exists here to provide a stable router when the vendor
 * server.php is not present.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the requested resource exists in the public folder, let the webserver serve it.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
