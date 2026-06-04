<?php
if (defined('BASE_URL')) return; // évite la double définition

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return strpos($haystack, $needle) === 0;
    }
}

$host_name = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (in_array($host_name, ['sheglamour.fr', 'www.sheglamour.fr', '187.127.228.186'])) {
    define('BASE_URL', '');
} else {
    define('BASE_URL', '/sheglam');
}