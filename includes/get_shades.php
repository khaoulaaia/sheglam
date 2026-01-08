<?php
// includes/get_shades.php
include_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

// Vérifier la présence du paramètre
if (!isset($_GET['product_id'])) {
    echo json_encode([]);
    exit;
}

$productId = (int) $_GET['product_id'];

try {
    // Récupérer le nom et le code couleur
    $stmt = $pdo->prepare("SELECT nom_teinte, code_couleur FROM teintes WHERE product_id = ?");
    $stmt->execute([$productId]);
    $shades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($shades ?: []);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
exit;
