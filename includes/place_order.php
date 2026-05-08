<?php
// =====================================================
//  SheGlamour — /includes/place_order.php
//
//  Reçoit un JSON POST depuis checkout.js et :
//    1. Valide le payload
//    2. Insère dans orders + order_items
//    3. Décrémente le stock (teinte si shade, produit sinon)
// =====================================================
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

    // ── 1. Créer la commande ──────────────────────────────
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

    // ── Requêtes préparées ────────────────────────────────

    $stmtItem = $pdo->prepare("
        INSERT INTO order_items (order_db_id, product_id, name, shade, quantity, unit_price)
        VALUES (:order_db_id, :product_id, :name, :shade, :quantity, :unit_price)
    ");

    // Décrémente le stock de la teinte spécifique
    $stmtShadeStock = $pdo->prepare("
        UPDATE teintes
        SET stock = GREATEST(stock - :qty, 0)
        WHERE product_id = :product_id
          AND LOWER(nom_teinte) = LOWER(:shade)
    ");

    // Décrémente le stock global du produit
    $stmtProdStock = $pdo->prepare("
        UPDATE products
        SET stock = GREATEST(stock - :qty, 0)
        WHERE id = :product_id
    ");

    // ── 2. Insérer les items + décrémenter le stock ───────
    foreach ($data["items"] as $item) {
        if (empty($item["name"]) || empty($item["quantity"])) continue;

        // JS peut envoyer product_id (snake) ou productId (camel)
        $productId = isset($item["product_id"]) ? (int)$item["product_id"]
                   : (isset($item["productId"])  ? (int)$item["productId"] : null);
        $qty   = (int)$item["quantity"];
        $shade = trim($item["shade"] ?? "");

        // Insertion order_item
        $stmtItem->execute([
            ":order_db_id" => $dbOrderId,
            ":product_id"  => $productId,
            ":name"        => $item["name"],
            ":shade"       => $shade !== "" ? $shade : null,
            ":quantity"    => $qty,
            ":unit_price"  => (float)($item["unit_price"] ?? $item["price"] ?? 0),
        ]);

        if (!$productId) continue; // pas d'ID produit = pas de décrémentation

        if ($shade !== "") {
            // Produit avec teinte :
            // → décrémente le stock de la teinte
            $stmtShadeStock->execute([
                ":qty"        => $qty,
                ":product_id" => $productId,
                ":shade"      => $shade,
            ]);
            // → décrémente aussi le stock global (vue admin)
            $stmtProdStock->execute([
                ":qty"        => $qty,
                ":product_id" => $productId,
            ]);
        } else {
            // Produit sans teinte : stock global uniquement
            $stmtProdStock->execute([
                ":qty"        => $qty,
                ":product_id" => $productId,
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        "success"  => true,
        "order_id" => $data["order_id"],
        "db_id"    => $dbOrderId,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}