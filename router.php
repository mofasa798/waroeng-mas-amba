<?php

/**
 * Router script for PHP built-in server.
 * Handles URL rewriting for Laravel.
 *
 * Usage: php -S 0.0.0.0:8080 -t public/ router.php
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// If file exists in public/, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Otherwise, route through Laravel's index.php
require_once __DIR__ . '/public/index.php';
