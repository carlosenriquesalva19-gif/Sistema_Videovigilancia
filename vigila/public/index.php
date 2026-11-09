<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Servir archivos estáticos cuando se ejecuta con el servidor embebido de PHP
if (php_sapi_name() === 'cli-server') {
    $uri = urldecode(
        parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? ''
    );
    if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
        return false;
    }
}

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
