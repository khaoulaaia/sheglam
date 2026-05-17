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

// Normalise image_url pour chaque produit
foreach ($results as &$row) {
    $url = trim($row['image_url'] ?? '');
    if (empty($url)) {
        $row['image_url'] = '/images/placeholder.jpg';
    } elseif (strpos($url, 'http') === 0) {
        // URL absolue — on la garde telle quelle
    } else {
        // Extrait juste le nom du fichier et reconstruit le chemin
        $row['image_url'] = '/images/' . basename($url);
    }
}
unset($row);

header('Content-Type: application/json');
echo json_encode($results);
?>