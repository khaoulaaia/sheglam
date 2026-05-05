<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "PHP : " . phpversion() . "\n";
echo "HOST : " . ($_SERVER['HTTP_HOST'] ?? '?') . "\n";
echo "__DIR__ : " . __DIR__ . "\n";

// Test config
echo "\n--- config.php ---\n";
$cp = __DIR__ . '/includes/config.php';
if (file_exists($cp)) {
    echo "ok trouve\n";
    include $cp;
    echo "BASE_URL = " . BASE_URL . "\n";
} else {
    echo "INTROUVABLE : $cp\n";
}

// Test db
echo "\n--- db.php ---\n";
$dp = __DIR__ . '/includes/db.php';
if (file_exists($dp)) {
    echo "ok trouve\n";
    try {
        include $dp;
        echo "PDO OK\n";
        echo "Produits : " . $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() . "\n";
    } catch (Throwable $e) {
        echo "ERREUR : " . $e->getMessage() . "\n";
    }
} else {
    echo "INTROUVABLE : $dp\n";
}

echo "\n--- Extensions PDO ---\n";
echo "pdo : "       . (extension_loaded('pdo')       ? 'OUI' : 'NON') . "\n";
echo "pdo_mysql : " . (extension_loaded('pdo_mysql') ? 'OUI' : 'NON') . "\n";
echo "pdo_pgsql : " . (extension_loaded('pdo_pgsql') ? 'OUI' : 'NON') . "\n";

echo "</pre>";
echo "<b style='color:red'>SUPPRIME CE FICHIER apres diagnostic !</b>";