<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$publicPath = __DIR__.DIRECTORY_SEPARATOR.'public';
$requestedFile = realpath($publicPath.$uri);

if ($uri !== '/' && $requestedFile && str_starts_with($requestedFile, realpath($publicPath)) && is_file($requestedFile)) {
    return false;
}

require $publicPath.DIRECTORY_SEPARATOR.'index.php';
