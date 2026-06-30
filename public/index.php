<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Strip the subdirectory prefix from the request URI so that Laravel's
// router matches routes against the path it expects (e.g. /dashboard/propietario
// instead of /inf513/.../public/dashboard/propietario). This is required when
// the public/ folder is hosted under a subdirectory without changing the
// Apache/Nginx DocumentRoot.
$subPath = '/inf513/grupo18sa/proyecto2/tecnoweb-web/public';
if (! empty($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], $subPath)) {
    $stripped = substr($_SERVER['REQUEST_URI'], strlen($subPath));
    $_SERVER['REQUEST_URI'] = $stripped === '' ? '/' : $stripped;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
