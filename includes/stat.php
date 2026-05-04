<?php
// =====================================================
//  stats.php — Statistiques tableau de bord
//  GET /sheglam/includes/api/stats.php
//
//  Retourne :
//  { revenue, orders_total, clients_total, pending,
//    products_active, cancelled, monthly_sales }
// =====================================================
require_once __DIR__ . '/config.php';

try {
    $db = getDB();

    // Chiffre d'affaires total (commandes livrées + en cours + expédiées)
    $revenue = $db->query("
        SELECT COALESCE(SUM(total), 0) AS revenue
        FROM orders
        WHERE status NOT IN ('cancelled')
    ")->fetchColumn();

    // Nombre total de commandes
    $orders_total = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();

    // Nombre de clients (table clientglam)
    $clients_total = $db->query("SELECT COUNT(*) FROM clientglam")->fetchColumn();

    // En attente
    $pending = $db->query("
        SELECT COUNT(*) FROM orders WHERE status = 'pending'
    ")->fetchColumn();

    // Produits actifs (ayant du stock dans teintes)
    $products_active = $db->query("
        SELECT COUNT(DISTINCT p.id)
        FROM products p
        JOIN teintes t ON t.product_id = p.id
        WHERE t.stock > 0
    ")->fetchColumn();

    // Annulées
    $cancelled = $db->query("
        SELECT COUNT(*) FROM orders WHERE status = 'cancelled'
    ")->fetchColumn();

    // Ventes mensuelles sur 12 mois glissants
    // Retourne [{month: '1'...'12', revenue: ...}]
    $monthly = $db->query("
        SELECT
            EXTRACT(MONTH FROM created_at)::int AS month,
            COALESCE(SUM(total), 0)             AS revenue
        FROM orders
        WHERE
            status NOT IN ('cancelled')
            AND created_at >= NOW() - INTERVAL '12 months'
        GROUP BY EXTRACT(MONTH FROM created_at)
        ORDER BY month
    ")->fetchAll();

    jsonResponse([
        'revenue'        => (float) $revenue,
        'orders_total'   => (int)   $orders_total,
        'clients_total'  => (int)   $clients_total,
        'pending'        => (int)   $pending,
        'products_active'=> (int)   $products_active,
        'cancelled'      => (int)   $cancelled,
        'monthly_sales'  => $monthly,
    ]);

} catch (Throwable $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}