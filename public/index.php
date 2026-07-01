<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Strip the deployed subdirectory prefix so Laravel matches routes like /login.
$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
$scriptDir = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
$basePath = str_ends_with($scriptDir, '/public') ? substr($scriptDir, 0, -7) : $scriptDir;

foreach (array_filter([$scriptDir, $basePath]) as $subPath) {
    if ($subPath !== '/' && str_starts_with($requestUri, $subPath)) {
        $stripped = substr($requestUri, strlen($subPath));
        $_SERVER['REQUEST_URI'] = $stripped === '' ? '/' : $stripped;
        break;
    }
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
