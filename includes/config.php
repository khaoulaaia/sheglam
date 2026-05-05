<?php
// ============================================================
//  includes/config.php
//  Local  : fichiers dans C:/xampp/htdocs/sheglam/
//           → URL : localhost/sheglam/  → BASE_URL = '/sheglam'
//  Prod   : fichiers dans /var/www/sheglamour/
//           → URL : sheglamour.fr/      → BASE_URL = ''
// ============================================================

// Compatibilité PHP 7.x (str_starts_with n'existe qu'en PHP 8+)
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return strpos($haystack, $needle) === 0;
    }
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (in_array($host, ['sheglamour.fr', 'www.sheglamour.fr', '187.127.228.186'])) {
    define('BASE_URL', '');        // production : racine du domaine
} else {
    define('BASE_URL', '/sheglam'); // local XAMPP
}