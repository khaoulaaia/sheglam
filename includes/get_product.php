<?php
include_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id'])) { echo json_encode(null); exit; }
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($product ?: null);
exit;
