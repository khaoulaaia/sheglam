<?php
if (isset($pdo)) return; // évite la double inclusion

$host     = "187.127.228.186";
$port     = "5432";
$dbname   = "sheglamour";
$user     = "sheglam_user";
$password = "1234";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Erreur de connexion : " . $e->getMessage());
}