<?php
include_once 'db.php';

if (!isset($_GET['query'])) {
    echo json_encode([]);
    exit;
}

$query = trim($_GET['query']);
$stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE name ILIKE ? LIMIT 10");
$stmt->execute(["%$query%"]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($results);
?>
