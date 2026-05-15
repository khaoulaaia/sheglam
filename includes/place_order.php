<?php
// =====================================================
//  SheGlamour — /includes/place_order.php
//  Reçoit un JSON POST depuis checkout.js et :
//    1. Valide le payload
//    2. Vérifie et verrouille les stocks (FOR UPDATE)
//    3. Insère dans orders + order_items
//    4. Décrémente le stock avec vérification stricte
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

// ── Lecture et validation du payload ─────────────────
$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data || empty($data["order_id"]) || empty($data["items"]) || empty($data["shipping"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Données manquantes"]);
    exit;
}

if (empty($data["shipping"]["firstName"]) || empty($data["shipping"]["lastName"]) ||
    empty($data["shipping"]["phone"])     || empty($data["shipping"]["wilaya"])    ||
    empty($data["shipping"]["address"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Informations de livraison incomplètes"]);
    exit;
}

// ── Construction du JSON livraison ───────────────────
$shipping     = $data["shipping"];
$shippingJson = json_encode([
    "prenom"  => trim($shipping["firstName"] ?? ""),
    "nom"     => trim($shipping["lastName"]  ?? ""),
    "tel"     => trim($shipping["phone"]     ?? ""),
    "wilaya"  => trim($shipping["wilaya"]    ?? ""),
    "adresse" => trim($shipping["address"]   ?? ""),
    "note"    => trim($shipping["note"]      ?? ""),
], JSON_UNESCAPED_UNICODE);

// ── Normalisation des items ──────────────────────────
$items = [];
foreach ($data["items"] as $item) {
    if (empty($item["name"]) || empty($item["quantity"])) continue;

    $productId = isset($item["product_id"]) ? (int)$item["product_id"]
               : (isset($item["productId"])  ? (int)$item["productId"] : null);

    $items[] = [
        "product_id" => $productId,
        "name"       => trim($item["name"]),
        "shade"      => trim($item["shade"] ?? ""),
        "quantity"   => max(1, (int)$item["quantity"]),
        "unit_price" => (float)($item["unit_price"] ?? $item["price"] ?? 0),
    ];
}

if (empty($items)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Aucun article valide dans la commande"]);
    exit;
}

// ── Transaction principale ───────────────────────────
try {
    $pdo->beginTransaction();

    // ── 1. Verrouillage des produits (FOR UPDATE) ────
    $productIds = array_values(array_filter(
        array_unique(array_column($items, "product_id")),
        fn($id) => $id !== null && $id > 0
    ));

    if (!empty($productIds)) {
        $placeholders = implode(",", array_fill(0, count($productIds), "?"));
        $pdo->prepare("SELECT id, stock, name FROM products WHERE id IN ($placeholders) FOR UPDATE")
            ->execute($productIds);
    }

    // ── 2. Vérification des stocks ───────────────────
    foreach ($items as $item) {
        if (!$item["product_id"]) continue;

        $shade = $item["shade"];
        $qty   = $item["quantity"];

        if ($shade !== "") {
            // Vérification stock teinte
            $stmt = $pdo->prepare("
                SELECT stock, nom_teinte FROM teintes
                WHERE product_id = ? AND LOWER(nom_teinte) = LOWER(?)
            ");
            $stmt->execute([$item["product_id"], $shade]);
            $teinte = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$teinte) {
                throw new Exception("Teinte « {$shade} » introuvable pour « {$item['name']} »");
            }
            if ((int)$teinte["stock"] < $qty) {
                throw new Exception(
                    "Stock insuffisant pour la teinte « {$shade} » de « {$item['name']} » " .
                    "(disponible : {$teinte['stock']}, demandé : {$qty})"
                );
            }
        } else {
            // Vérification stock produit global
            $stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = ?");
            $stmt->execute([$item["product_id"]]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception("Produit « {$item['name']} » introuvable");
            }
            if ((int)$product["stock"] < $qty) {
                throw new Exception(
                    "Stock insuffisant pour « {$item['name']} » " .
                    "(disponible : {$product['stock']}, demandé : {$qty})"
                );
            }
        }
    }

    // ── 3. Insertion de la commande ──────────────────
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
    if (!$dbOrderId) throw new Exception("Échec de la création de la commande");

    // ── 4. Insertion des articles + décrémentation ───
    $stmtItem = $pdo->prepare("
        INSERT INTO order_items (order_db_id, product_id, name, shade, quantity, unit_price)
        VALUES (:order_db_id, :product_id, :name, :shade, :quantity, :unit_price)
    ");

    $stmtShadeStock = $pdo->prepare("
        UPDATE teintes
        SET stock = stock - :qty
        WHERE product_id = :product_id
          AND LOWER(nom_teinte) = LOWER(:shade)
          AND stock >= :qty
    ");

    $stmtProdStock = $pdo->prepare("
        UPDATE products
        SET stock = stock - :qty
        WHERE id = :product_id
          AND stock >= :qty
    ");

    foreach ($items as $item) {
        // Insertion article
        $stmtItem->execute([
            ":order_db_id" => $dbOrderId,
            ":product_id"  => $item["product_id"],
            ":name"        => $item["name"],
            ":shade"       => $item["shade"] !== "" ? $item["shade"] : null,
            ":quantity"    => $item["quantity"],
            ":unit_price"  => $item["unit_price"],
        ]);

        if (!$item["product_id"]) continue;

        if ($item["shade"] !== "") {
            // Décrémente stock teinte
            $stmtShadeStock->execute([
                ":qty"        => $item["quantity"],
                ":product_id" => $item["product_id"],
                ":shade"      => $item["shade"],
            ]);
            if ($stmtShadeStock->rowCount() === 0) {
                throw new Exception(
                    "Échec décrémentation stock teinte « {$item['shade']} » pour « {$item['name']} »"
                );
            }

            // Décrémente aussi stock global
            $stmtProdStock->execute([
                ":qty"        => $item["quantity"],
                ":product_id" => $item["product_id"],
            ]);

        } else {
            // Décrémente stock global uniquement
            $stmtProdStock->execute([
                ":qty"        => $item["quantity"],
                ":product_id" => $item["product_id"],
            ]);
            if ($stmtProdStock->rowCount() === 0) {
                throw new Exception(
                    "Échec décrémentation stock pour « {$item['name']} »"
                );
            }
        }
    }

    $pdo->commit();

    echo json_encode([
        "success"  => true,
        "order_id" => $data["order_id"],
        "db_id"    => $dbOrderId,
        "message"  => "Commande enregistrée avec succès",
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
    ]);
}