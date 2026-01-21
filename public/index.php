<?php

// Force Laravel to recognize subfolder /dev
$_SERVER['SCRIPT_NAME'] = str_replace('/dev/', '/', $_SERVER['SCRIPT_NAME']);
$_SERVER['REQUEST_URI'] = preg_replace('#^/dev#', '', $_SERVER['REQUEST_URI']);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->usePublicPath(__DIR__);

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
