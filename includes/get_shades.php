<?php
// includes/get_shades.php
include_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['product_id'])) { echo json_encode([]); exit; }

$productId = (int) $_GET['product_id'];

try {
    $stmt = $pdo->prepare("
        SELECT id,
               nom_teinte,
               code_couleur,
               image,
               COALESCE(prix, 0)  AS prix,
               COALESCE(stock, 0) AS stock
        FROM teintes
        WHERE product_id = ?
        ORDER BY id ASC
    ");
    $stmt->execute([$productId]);
    $shades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!defined('BASE_URL')) @include_once __DIR__ . '/config.php';
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';

    foreach ($shades as &$s) {
        $s['prix']  = (float)$s['prix'];
        $s['stock'] = (int)$s['stock'];
        if (!empty($s['image'])) {
            $raw = $s['image'];
            $s['img_src'] = str_starts_with($raw, 'http')
                ? $raw
                : $baseUrl . '/images/' . basename($raw);
        } else {
            $s['img_src'] = null;
        }
    }
    unset($s);

    echo json_encode($shades ?: []);
} catch (PDOException $e) {
    echo json_encode([]);
}
