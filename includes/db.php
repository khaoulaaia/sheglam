<?php
$host = "localhost";
$port = "5432";
$dbname = "sheglamour";
$user = "sheglam_user";
$password = "sheglam_pass";
$mdproothostinger="t2#cy0scJZ)cZG6k"

try {
  $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "❌ Erreur : " . $e->getMessage();
}
?>
