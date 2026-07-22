<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Router;

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);

    $debug = filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL);
    echo $debug
        ? '<pre>' . e($exception->getMessage()) . "
" . e($exception->getTraceAsString()) . '</pre>'
        : '<h1>Internal Server Error</h1>';
}
