<?php
require_once __DIR__ . "/db.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") { http_response_code(204); exit; }
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
    exit;
}

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data || empty($data["order_id"]) || empty($data["items"]) || empty($data["shipping"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données manquantes"]);
    exit;
}

$shipping     = $data["shipping"];
$shippingJson = json_encode([
    "prenom"  => trim($shipping["firstName"] ?? ""),
    "nom"     => trim($shipping["lastName"]  ?? ""),
    "tel"     => trim($shipping["phone"]     ?? ""),
    "wilaya"  => trim($shipping["wilaya"]    ?? ""),
    "adresse" => trim($shipping["address"]   ?? ""),
    "note"    => trim($shipping["note"]      ?? ""),
], JSON_UNESCAPED_UNICODE);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO orders (order_id, status, payment_method, total, shipping, created_at, updated_at)
        VALUES (:order_id, 'pending', :payment_method, :total, :shipping::jsonb, NOW(), NOW())
        RETURNING id
    ");
    $stmt->execute([
        ":order_id"       => $data["order_id"],
        ":payment_method" => $data["payment_method"] ?? "cash",
        ":total"          => $data["total"]          ?? 0,
        ":shipping"       => $shippingJson,
    ]);

    $dbOrderId = $stmt->fetchColumn();
    if (!$dbOrderId) throw new Exception("RETURNING id vide");

    $stmtItem = $pdo->prepare("
        INSERT INTO order_items (order_db_id, product_id, name, shade, quantity, unit_price)
        VALUES (:order_db_id, :product_id, :name, :shade, :quantity, :unit_price)
    ");

    foreach ($data["items"] as $item) {
        if (empty($item["name"]) || empty($item["quantity"])) continue;

        // ✅ JS stocke "productId" (camelCase) dans le panier
        $productId = $item["product_id"] ?? $item["productId"] ?? null;

        $stmtItem->execute([
            ":order_db_id" => $dbOrderId,
            ":product_id"  => $productId ? (int)$productId : null,
            ":name"        => $item["name"],
            ":shade"       => $item["shade"]     ?? null,
            ":quantity"    => (int)$item["quantity"],
            ":unit_price"  => (float)($item["unit_price"] ?? $item["price"] ?? 0),
        ]);
    }

    $pdo->commit();
    echo json_encode(["success" => true, "order_id" => $data["order_id"], "db_id" => $dbOrderId]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
