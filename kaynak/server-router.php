<?php

$publicPath = __DIR__.'/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$requested = $publicPath.$uri;

if ($uri !== '/' && file_exists($requested) && ! is_dir($requested)) {
    return false;
}

require_once $publicPath.'/index.php';
