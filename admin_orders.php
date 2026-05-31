<?php
// ============================================
//  SheGlamour — Gestion des Commandes v3.2
//  Tableau nettoyé, aligné sur le dashboard
// ============================================
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/config.php';
$b = defined('BASE_URL') ? BASE_URL : '';

// ─── ACTION : CHANGEMENT DE STATUT ───────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $allowed   = ['confirmed', 'shipped', 'delivered', 'cancelled', 'pending'];
    $newStatus = $_POST['action'];
    $orderId   = $_POST['order_id'];
    if (in_array($newStatus, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
        $stmt->execute([$newStatus, $orderId]);
    }
    $qs = http_build_query(array_filter([
        'status'  => $_POST['_status']  ?? '',
        'wilaya'  => $_POST['_wilaya']  ?? '',
        'payment' => $_POST['_payment'] ?? '',
        'q'       => $_POST['_q']       ?? '',
        'page'    => $_POST['_page']    ?? '',
    ], fn($v) => $v !== ''));
    header('Location: admin_orders.php' . ($qs ? '?' . $qs : ''));
    exit;
}

// ─── FILTRES ──────────────────────────────────
$filterStatus  = $_GET['status']  ?? '';
$filterWilaya  = $_GET['wilaya']  ?? '';
$filterPayment = $_GET['payment'] ?? '';
$search        = trim($_GET['q']  ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 20;

// ─── WHERE ────────────────────────────────────
$conditions = [];
$params     = [];

if ($filterStatus !== '') {
    $conditions[] = "status = ?";
    $params[]     = $filterStatus;
}
if ($filterWilaya !== '') {
    $conditions[] = "shipping->>'wilaya' = ?";
    $params[]     = $filterWilaya;
}
if ($filterPayment !== '') {
    $conditions[] = "payment_method = ?";
    $params[]     = $filterPayment;
}
if ($search !== '') {
    $conditions[] = "(
        order_id                  ILIKE ?
        OR shipping->>'prenom'    ILIKE ?
        OR shipping->>'nom'       ILIKE ?
        OR shipping->>'tel'       ILIKE ?
        OR shipping->>'firstName' ILIKE ?
        OR shipping->>'lastName'  ILIKE ?
        OR shipping->>'phone'     ILIKE ?
    )";
    $like   = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like]);
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders $where");
$countStmt->execute($params);
$total  = (int) $countStmt->fetchColumn();
$pages  = max(1, (int) ceil($total / $perPage));
$page   = min($page, $pages);
$offset = ($page - 1) * $perPage;

// Fetch commandes
$stmt = $pdo->prepare("
    SELECT o.id, o.order_id, o.status, o.payment_method, o.total,
           o.shipping, o.created_at, o.updated_at
    FROM orders o
    $where
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
");
$allParams = array_merge($params, [$perPage, $offset]);
foreach ($allParams as $i => $val) {
    $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($i + 1, $val, $type);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pré-charge les articles
$orderDbIds   = array_column($orders, 'id');
$itemsByOrder = [];
if ($orderDbIds) {
    $placeholders = implode(',', array_fill(0, count($orderDbIds), '?'));
    $itemStmt = $pdo->prepare("
        SELECT order_db_id, name, shade, quantity, unit_price
        FROM order_items
        WHERE order_db_id IN ($placeholders)
        ORDER BY id ASC
    ");
    $itemStmt->execute($orderDbIds);
    foreach ($itemStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $itemsByOrder[$row['order_db_id']][] = $row;
    }
}

// ─── STATS RAPIDES ────────────────────────────
$statuses     = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
$statusCounts = [];
foreach ($statuses as $s) {
    $r = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = ?");
    $r->execute([$s]);
    $statusCounts[$s] = (int) $r->fetchColumn();
}

// ─── WILAYAS DISTINCTES ───────────────────────
$wilayas = $pdo->query("
    SELECT DISTINCT COALESCE(shipping->>'wilaya', '') AS w
    FROM orders
    WHERE shipping->>'wilaya' IS NOT NULL
      AND shipping->>'wilaya' != ''
    ORDER BY w
")->fetchAll(PDO::FETCH_COLUMN);

// ─── HELPERS ──────────────────────────────────
function parseShipping($raw): array {
    $sh = is_string($raw) ? (json_decode($raw, true) ?: []) : ($raw ?: []);
    return [
        'prenom'  => $sh['prenom']  ?? ($sh['firstName'] ?? ''),
        'nom'     => $sh['nom']     ?? ($sh['lastName']  ?? ''),
        'tel'     => $sh['tel']     ?? ($sh['phone']     ?? ''),
        'wilaya'  => $sh['wilaya']  ?? '',
        'adresse' => $sh['adresse'] ?? ($sh['address']   ?? ''),
        'note'    => $sh['note']    ?? '',
    ];
}

function statusBadge($s) {
    $map = [
        'pending'   => ['En attente', 'bdg-amber'],
        'confirmed' => ['Confirmée',  'bdg-plum'],
        'shipped'   => ['Expédiée',   'bdg-blue'],
        'delivered' => ['Livrée',     'bdg-green'],
        'cancelled' => ['Annulée',    'bdg-red'],
    ];
    [$label, $cls] = $map[$s] ?? [$s, 'bdg-gray'];
    return "<span class='badge $cls'>$label</span>";
}

function payLabel($p) {
    return match($p) {
        'cash'      => '💵 Livraison',
        'ccp'       => '🏦 CCP',
        'baridimob' => '📱 Baridi',
        default     => htmlspecialchars((string)$p)
    };
}

function qs(array $extra = []): string {
    $allowed = ['status', 'wilaya', 'payment', 'q', 'page'];
    $base = [];
    foreach ($allowed as $k) {
        if (isset($_GET[$k]) && $_GET[$k] !== '') $base[$k] = $_GET[$k];
    }
    foreach ($extra as $k => $v) {
        if ($v === '' || $v === null || $v === false) unset($base[$k]);
        else $base[$k] = $v;
    }
    if (($base['page'] ?? '') == 1) unset($base['page']);
    $q = http_build_query($base);
    return $q ? '?' . $q : '?';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>SheGlamour — Commandes</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%23c4697a'/%3E%3C/svg%3E">

<style>
/* ── TOKENS (identiques au dashboard) ───────────────────────────────────── */
:root {
  --bg:#f9f5f2; --surface:#fff; --surface2:#f4eeea;
  --border:#ede5de; --border2:#e0d5cc;
  --text:#16100e; --text2:#4a3c36; --muted:#9c8d85; --muted2:#c0afa6;

  --rose:#c4697a; --rose-d:#a8505f; --rose-bg:#fdf0f2; --rose-lt:#f5d0d7;
  --plum:#8b5a8b; --plum-bg:#f5eef5; --plum-lt:#dfc8df;
  --green:#3a8a5c; --green-bg:#eef7f2; --green-lt:#b8dfc9;
  --amber:#b07030; --amber-bg:#fdf5eb; --amber-lt:#f0d4a8;
  --blue:#3a6db0; --blue-bg:#eef3fb; --blue-lt:#b8cff0;
  --red:#c0392b; --red-bg:#fdf0ee; --red-lt:#f0c0bb;

  --sidebar-w:240px; --top-h:60px; --r:16px; --r-sm:10px;
  --shadow:0 2px 8px rgba(0,0,0,.06),0 8px 24px rgba(0,0,0,.05);
  --shadow-sm:0 1px 4px rgba(0,0,0,.05);
}

*,*::before,*::after { margin:0; padding:0; box-sizing:border-box; }
body { background:var(--bg); color:var(--text); font-family:'DM Sans',sans-serif; font-size:14px; min-height:100vh; line-height:1.5; }

/* ── SIDEBAR ─────────────────────────────────────────────────────────────── */
.sidebar {
  position:fixed; top:0; left:0;
  width:var(--sidebar-w); height:100vh;
  background:var(--surface); border-right:1px solid var(--border);
  display:flex; flex-direction:column; z-index:200;
  transition:transform .3s cubic-bezier(.4,0,.2,1);
  overflow-y:auto; overscroll-behavior:contain;
}
.sidebar-logo { padding:28px 24px 24px; display:flex; align-items:center; gap:11px; border-bottom:1px solid var(--border); flex-shrink:0; }
.sidebar-logo svg { width:36px; height:36px; flex-shrink:0; }
.sidebar-logo-text { font-family:'Cormorant Garamond',serif; font-size:22px; letter-spacing:-.01em; color:var(--text); }
.sidebar-logo-text span { color:var(--rose); }
.sidebar-section { padding:20px 20px 8px; font-size:10px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--muted2); }
.sidebar-nav { display:flex; flex-direction:column; gap:2px; padding:0 12px; flex:1; }
.nav-link { display:flex; align-items:center; gap:11px; padding:10px 13px; border-radius:var(--r-sm); font-size:13.5px; font-weight:500; color:var(--muted); text-decoration:none; transition:all .15s; }
.nav-link:hover { background:var(--surface2); color:var(--text2); }
.nav-link.active { background:var(--rose-bg); color:var(--rose); font-weight:600; }
.nav-link .nav-ico { font-size:15px; width:20px; text-align:center; flex-shrink:0; }
.nav-badge { margin-left:auto; background:var(--amber); color:#fff; border-radius:10px; padding:1px 7px; font-size:10px; font-weight:700; }
.sidebar-footer { padding:16px 20px; border-top:1px solid var(--border); font-size:11px; color:var(--muted2); display:flex; justify-content:space-between; align-items:center; flex-shrink:0; }
.logout-link { color:var(--muted); text-decoration:none; font-weight:600; font-size:11px; transition:color .15s; }
.logout-link:hover { color:var(--red); }

/* ── TOPBAR MOBILE ───────────────────────────────────────────────────────── */
.topbar { display:none; position:fixed; top:0; left:0; right:0; height:var(--top-h); background:var(--surface); border-bottom:1px solid var(--border); align-items:center; justify-content:space-between; padding:0 18px; z-index:180; box-shadow:var(--shadow-sm); }
.topbar-logo { display:flex; align-items:center; gap:9px; font-family:'Cormorant Garamond',serif; font-size:20px; color:var(--text); }
.topbar-logo svg { width:30px; height:30px; }
.topbar-logo span { color:var(--rose); }
.hamburger { width:40px; height:40px; border:1.5px solid var(--border); border-radius:10px; background:var(--surface); cursor:pointer; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:5px; }
.hamburger span { display:block; width:18px; height:1.5px; background:var(--text2); border-radius:2px; transition:transform .3s,opacity .3s; }
.hamburger.open span:nth-child(1) { transform:translateY(6.5px) rotate(45deg); }
.hamburger.open span:nth-child(2) { opacity:0; }
.hamburger.open span:nth-child(3) { transform:translateY(-6.5px) rotate(-45deg); }
.sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(22,16,14,.35); z-index:190; backdrop-filter:blur(2px); }
.sidebar-overlay.active { display:block; }

/* ── MAIN ────────────────────────────────────────────────────────────────── */
.main { margin-left:var(--sidebar-w); padding:40px 36px; min-height:100vh; max-width:1400px; }

/* ── PAGE HEADER ─────────────────────────────────────────────────────────── */
.page-header { display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:28px; gap:16px; flex-wrap:wrap; }
.page-header h1 { font-family:'Cormorant Garamond',serif; font-size:36px; letter-spacing:-.02em; color:var(--text); line-height:1; }
.page-header p { color:var(--muted); font-size:13px; margin-top:5px; }

/* ── STATUS TABS ─────────────────────────────────────────────────────────── */
.status-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; }
.status-tab { display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:30px; font-size:12.5px; font-weight:700; text-decoration:none; transition:all .18s; border:1.5px solid var(--border); color:var(--muted); background:var(--surface); }
.status-tab:hover { border-color:var(--border2); color:var(--text2); background:var(--surface2); }
.status-tab.active { border-color:var(--rose); color:var(--rose); background:var(--rose-bg); }
.tab-count { background:rgba(0,0,0,.07); border-radius:10px; padding:1px 7px; font-size:10.5px; }
.status-tab.active .tab-count { background:rgba(196,105,122,.15); }

/* ── FILTERS BAR ─────────────────────────────────────────────────────────── */
.filters-bar { display:flex; gap:10px; align-items:center; margin-bottom:20px; flex-wrap:wrap; }
.filters-bar input,
.filters-bar select {
  background:var(--surface); border:1.5px solid var(--border); border-radius:var(--r-sm);
  padding:9px 14px; font-family:'DM Sans',sans-serif; font-size:13px;
  color:var(--text); outline:none; transition:border-color .15s;
}
.filters-bar input:focus,
.filters-bar select:focus { border-color:var(--rose); }
.filters-bar input { min-width:210px; }
.btn-filter { padding:9px 18px; background:var(--rose); color:#fff; border:none; border-radius:var(--r-sm); font-family:'DM Sans',sans-serif; font-size:13px; font-weight:700; cursor:pointer; transition:background .15s; }
.btn-filter:hover { background:var(--rose-d); }
.btn-reset { padding:9px 14px; background:var(--surface); color:var(--muted); border:1.5px solid var(--border); border-radius:var(--r-sm); font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; transition:all .15s; }
.btn-reset:hover { background:var(--surface2); color:var(--text2); }

/* ── CARD ────────────────────────────────────────────────────────────────── */
.card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--shadow-sm); }
.card-head { padding:16px 22px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--border); background:var(--surface2); gap:12px; flex-wrap:wrap; }
.card-title { font-size:10.5px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); }

/* ── BADGES ──────────────────────────────────────────────────────────────── */
.badge { display:inline-flex; align-items:center; padding:3px 11px; border-radius:20px; font-size:11px; font-weight:700; letter-spacing:.03em; white-space:nowrap; }
.bdg-amber  { background:var(--amber-bg);  color:var(--amber);  border:1px solid var(--amber-lt); }
.bdg-plum   { background:var(--plum-bg);   color:var(--plum);   border:1px solid var(--plum-lt); }
.bdg-blue   { background:var(--blue-bg);   color:var(--blue);   border:1px solid var(--blue-lt); }
.bdg-green  { background:var(--green-bg);  color:var(--green);  border:1px solid var(--green-lt); }
.bdg-red    { background:var(--red-bg);    color:var(--red);    border:1px solid var(--red-lt); }
.bdg-gray   { background:var(--surface2);  color:var(--muted);  border:1px solid var(--border); }

/* ── TABLE ───────────────────────────────────────────────────────────────── */
.tbl-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; }
.tbl { width:100%; border-collapse:collapse; min-width:780px; }
.tbl th {
  font-size:10px; font-weight:800; letter-spacing:.1em;
  text-transform:uppercase; color:var(--muted);
  padding:0 16px 13px 0; text-align:left;
  border-bottom:2px solid var(--border); white-space:nowrap;
}
.tbl th:first-child { padding-left:22px; }
.tbl td {
  padding:13px 16px 13px 0; border-bottom:1px solid var(--border);
  font-size:13px; color:var(--text2); vertical-align:middle;
}
.tbl td:first-child { padding-left:22px; }
.tbl tr:last-child td { border-bottom:none; }
.tbl tbody tr:hover td { background:var(--surface2); }
.tbl td:last-child, .tbl th:last-child { text-align:right; padding-right:22px; }

/* Cellules spécifiques */
.oid {
  font-family:'Courier New',monospace; font-size:10.5px;
  color:var(--rose); font-weight:700;
  background:var(--rose-bg); padding:3px 7px;
  border-radius:5px; border:1px solid var(--rose-lt);
  white-space:nowrap;
}
.client-name { font-weight:700; color:var(--text); }
.client-sub  { font-size:11px; color:var(--muted); margin-top:2px; }
.total-cell  { font-weight:800; color:var(--rose); white-space:nowrap; font-size:13.5px; }
.items-tags  { display:flex; flex-wrap:wrap; gap:3px; max-width:180px; }
.item-tag {
  display:inline-block; background:var(--surface2);
  border:1px solid var(--border); border-radius:6px;
  padding:2px 7px; font-size:10.5px; color:var(--text2);
  white-space:nowrap;
}
.item-tag-more { color:var(--rose); background:var(--rose-bg); border-color:var(--rose-lt); }

/* ── ACTIONS ─────────────────────────────────────────────────────────────── */
.action-group { display:flex; gap:5px; flex-wrap:nowrap; align-items:center; justify-content:flex-end; }
.btn-action {
  display:inline-flex; align-items:center; gap:3px;
  padding:5px 10px; border-radius:7px;
  font-size:11px; font-weight:700; cursor:pointer;
  transition:all .15s; border:1.5px solid;
  font-family:'DM Sans',sans-serif; white-space:nowrap;
}
.btn-confirm { background:var(--plum-bg);   color:var(--plum);   border-color:var(--plum-lt); }
.btn-ship    { background:var(--blue-bg);   color:var(--blue);   border-color:var(--blue-lt); }
.btn-deliver { background:var(--green-bg);  color:var(--green);  border-color:var(--green-lt); }
.btn-cancel  { background:var(--red-bg);    color:var(--red);    border-color:var(--red-lt); }
.btn-pending { background:var(--amber-bg);  color:var(--amber);  border-color:var(--amber-lt); }
.btn-detail  { background:var(--surface2);  color:var(--muted);  border-color:var(--border); }
.btn-action:hover { filter:brightness(.92); transform:scale(.97); }

/* ── PAGINATION ──────────────────────────────────────────────────────────── */
.pagination { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:24px; flex-wrap:wrap; }
.page-btn {
  display:flex; align-items:center; justify-content:center;
  min-width:36px; height:36px; padding:0 12px; border-radius:9px;
  font-size:13px; font-weight:700; text-decoration:none;
  transition:all .15s; border:1.5px solid var(--border);
  background:var(--surface); color:var(--muted);
}
.page-btn:hover  { background:var(--surface2); color:var(--text); }
.page-btn.active { background:var(--rose); border-color:var(--rose); color:#fff; }
.page-btn.disabled { opacity:.35; pointer-events:none; }
.page-info { font-size:12px; color:var(--muted); font-weight:600; padding:0 8px; }

/* ── EMPTY STATE ─────────────────────────────────────────────────────────── */
.empty-state { text-align:center; padding:56px 20px; }
.empty-icon  { font-size:44px; opacity:.25; margin-bottom:12px; }
.empty-text  { color:var(--muted); font-size:14px; }

/* ── MOBILE CARDS ────────────────────────────────────────────────────────── */
.mobile-orders { display:none; padding:16px; }
.m-order-card {
  border:1px solid var(--border); border-radius:12px;
  padding:14px 16px; margin-bottom:10px; background:var(--surface);
}
.m-order-top  { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:9px; }
.m-order-name { font-size:14px; font-weight:700; color:var(--text); }
.m-order-id   { font-family:'Courier New',monospace; font-size:10px; color:var(--rose); font-weight:700; margin-top:2px; }
.m-order-row  { display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--muted); margin-top:5px; gap:8px; }
.m-order-total { font-size:14px; font-weight:800; color:var(--rose); }
.m-order-actions { display:flex; gap:5px; flex-wrap:wrap; margin-top:10px; padding-top:10px; border-top:1px solid var(--border); }

/* ── MODAL ───────────────────────────────────────────────────────────────── */
.modal-overlay {
  display:none; position:fixed; inset:0;
  background:rgba(0,0,0,.35); z-index:500;
  align-items:center; justify-content:center;
  backdrop-filter:blur(4px);
}
.modal-overlay.open { display:flex; }
.modal {
  background:var(--surface); border-radius:var(--r);
  padding:32px; max-width:500px; width:94%;
  box-shadow:0 8px 40px rgba(0,0,0,.15);
  max-height:90vh; overflow-y:auto; position:relative;
}
.modal-close { position:absolute; top:16px; right:20px; background:none; border:none; font-size:24px; cursor:pointer; color:var(--muted); line-height:1; }
.modal h2 { font-family:'Cormorant Garamond',serif; font-size:24px; margin-bottom:4px; }
.modal-section { background:var(--surface2); border:1px solid var(--border); border-radius:var(--r-sm); padding:16px; margin:14px 0; }
.modal-section-title { font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:var(--muted2); margin-bottom:12px; }
.modal-row { display:flex; justify-content:space-between; align-items:flex-start; font-size:13px; padding:6px 0; border-bottom:1px solid var(--border); gap:12px; }
.modal-row:last-child { border-bottom:none; }
.modal-row span:first-child { color:var(--muted); flex-shrink:0; }
.modal-row span:last-child  { font-weight:600; color:var(--text); text-align:right; }
.modal-item { display:flex; justify-content:space-between; font-size:12.5px; padding:7px 0; border-bottom:1px solid var(--border); gap:10px; }
.modal-item:last-child { border-bottom:none; }
.modal-item-name { color:var(--text2); flex:1; }
.modal-item-shade { display:inline-block; background:var(--plum-bg); color:var(--plum); border:1px solid var(--plum-lt); border-radius:10px; padding:1px 7px; font-size:10.5px; margin-left:5px; }
.modal-item-price { font-weight:700; color:var(--rose); white-space:nowrap; }

/* ── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media (max-width:900px) {
  .sidebar { transform:translateX(calc(-1 * var(--sidebar-w))); box-shadow:4px 0 24px rgba(0,0,0,.12); }
  .sidebar.open { transform:translateX(0); }
  .topbar { display:flex; }
  .main { margin-left:0; padding:calc(var(--top-h) + 20px) 16px 32px; }
  .status-tabs { gap:6px; }
  .status-tab { padding:7px 13px; font-size:11.5px; }
  .filters-bar input { min-width:160px; }
  /* table → masquée, mobile cards → visible */
  .desktop-tbl { display:none; }
  .mobile-orders { display:block; }
}
@media (max-width:480px) {
  .main { padding-left:12px; padding-right:12px; }
  .page-header h1 { font-size:28px; }
  .page-header { flex-direction:column; align-items:flex-start; }
}
</style>
</head>
<body>

<!-- ── TOPBAR MOBILE ──────────────────────────────────────────────────────── -->
<header class="topbar" id="topbar">
  <div class="topbar-logo">
    <svg viewBox="0 0 32 32" fill="none">
      <circle cx="16" cy="16" r="16" fill="#fdf0f2"/>
      <path d="M16 5C10.477 5 6 9.477 6 15c0 3.09 1.39 5.863 3.6 7.744V26h12.8v-3.256C24.61 20.863 26 18.09 26 15c0-5.523-4.477-10-10-10z" fill="#f5c6cd"/>
      <path d="M16 8c-3.866 0-7 3.134-7 7 0 2.256 1.066 4.261 2.728 5.553V24h8.544v-3.447C21.934 19.261 23 17.256 23 15c0-3.866-3.134-7-7-7z" fill="#e8899a"/>
      <circle cx="16" cy="15" r="4.5" fill="#c4697a"/>
    </svg>
    She<span>Glamour</span>
  </div>
  <button class="hamburger" id="hamburger" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</header>

<!-- ── OVERLAY ────────────────────────────────────────────────────────────── -->
<div class="sidebar-overlay" id="overlay"></div>

<!-- ── SIDEBAR ───────────────────────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <svg viewBox="0 0 36 36" fill="none">
      <circle cx="18" cy="18" r="18" fill="#fdf0f2"/>
      <path d="M18 6C12.477 6 8 10.477 8 16c0 3.09 1.39 5.863 3.6 7.744V28h12.8v-4.256C26.61 21.863 28 19.09 28 16 28 10.477 23.523 6 18 6z" fill="#f5c6cd"/>
      <path d="M18 10c-3.314 0-6 2.686-6 6 0 2.032.997 3.836 2.537 4.96V27h6.926v-6.04C23.003 19.836 24 18.032 24 16c0-3.314-2.686-6-6-6z" fill="#e8899a"/>
      <circle cx="18" cy="16" r="3.8" fill="#c4697a"/>
    </svg>
    <span class="sidebar-logo-text">She<span>Glamour</span></span>
  </div>
  <p class="sidebar-section">Navigation</p>
  <nav class="sidebar-nav">
    <a class="nav-link" href="dashboard.php">
      <span class="nav-ico">◈</span> Tableau de bord
    </a>
    <a class="nav-link active" href="admin_orders.php">
      <span class="nav-ico">📦</span> Commandes
      <?php if (($statusCounts['pending'] ?? 0) > 0): ?>
        <span class="nav-badge"><?= $statusCounts['pending'] ?></span>
      <?php endif; ?>
    </a>
    <a class="nav-link" href="admin_products.php">
      <span class="nav-ico">✦</span> Produits
    </a>
    <a class="nav-link" href="index.php" target="_blank">
      <span class="nav-ico">↗</span> Voir la boutique
    </a>
  </nav>
  <div class="sidebar-footer">
    <span>v3.2</span>
    <a href="dashboard.php?logout=1" class="logout-link">Déconnexion</a>
  </div>
</aside>

<!-- ── MAIN ───────────────────────────────────────────────────────────────── -->
<main class="main">

  <div class="page-header">
    <div>
      <h1>Commandes</h1>
      <p>
        <?= $total ?> commande<?= $total > 1 ? 's' : '' ?> trouvée<?= $total > 1 ? 's' : '' ?>
        <?php if ($filterStatus || $filterWilaya || $filterPayment || $search): ?>
          — <a href="admin_orders.php" style="color:var(--rose);font-size:12px;text-decoration:none;font-weight:600">✕ Effacer les filtres</a>
        <?php endif; ?>
      </p>
    </div>
  </div>

  <!-- ONGLETS STATUT -->
  <div class="status-tabs">
    <a href="<?= qs(['status' => false, 'page' => false]) ?>" class="status-tab <?= $filterStatus === '' ? 'active' : '' ?>">
      Toutes <span class="tab-count"><?= array_sum($statusCounts) ?></span>
    </a>
    <?php foreach ([
      'pending'   => 'En attente',
      'confirmed' => 'Confirmées',
      'shipped'   => 'Expédiées',
      'delivered' => 'Livrées',
      'cancelled' => 'Annulées',
    ] as $key => $label): ?>
    <a href="<?= qs(['status' => $key, 'page' => false]) ?>" class="status-tab <?= $filterStatus === $key ? 'active' : '' ?>">
      <?= $label ?> <span class="tab-count"><?= $statusCounts[$key] ?? 0 ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- FILTRES -->
  <form method="GET" action="admin_orders.php">
    <?php if ($filterStatus !== ''): ?>
      <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
    <?php endif; ?>
    <div class="filters-bar">
      <input type="text" name="q" placeholder="🔍 ID, nom, téléphone…" value="<?= htmlspecialchars($search) ?>">
      <select name="wilaya">
        <option value="">Toutes les wilayas</option>
        <?php foreach ($wilayas as $w): ?>
          <option value="<?= htmlspecialchars($w) ?>" <?= $filterWilaya === $w ? 'selected' : '' ?>><?= htmlspecialchars($w) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="payment">
        <option value="">Tous les paiements</option>
        <option value="cash"      <?= $filterPayment === 'cash'      ? 'selected' : '' ?>>💵 Livraison</option>
        <option value="ccp"       <?= $filterPayment === 'ccp'       ? 'selected' : '' ?>>🏦 CCP</option>
        <option value="baridimob" <?= $filterPayment === 'baridimob' ? 'selected' : '' ?>>📱 Baridi</option>
      </select>
      <button type="submit" class="btn-filter">Filtrer</button>
      <a href="admin_orders.php" class="btn-reset">Réinitialiser</a>
    </div>
  </form>

  <!-- TABLEAU -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Liste des commandes</span>
      <span style="font-size:12px;color:var(--muted);font-weight:600">
        <?php if ($pages > 1): ?>Page <?= $page ?> / <?= $pages ?> &middot; <?php endif; ?>
        <?= $total ?> résultat<?= $total > 1 ? 's' : '' ?>
      </span>
    </div>

    <!-- Desktop : tableau -->
    <div class="tbl-wrap desktop-tbl" style="padding:0 0 4px">
      <table class="tbl">
        <thead>
          <tr>
            <th>Commande</th>
            <th>Client</th>
            <th>Wilaya</th>
            <th>Articles</th>
            <th>Paiement</th>
            <th>Statut</th>
            <th>Date</th>
            <th>Total</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($orders): ?>
          <?php foreach ($orders as $o):
            $sh       = parseShipping($o['shipping']);
            $fullName = trim($sh['prenom'] . ' ' . $sh['nom']) ?: '—';
            $items    = $itemsByOrder[$o['id']] ?? [];
          ?>
          <tr>
            <!-- ID -->
            <td><span class="oid"><?= htmlspecialchars($o['order_id']) ?></span></td>

            <!-- Client -->
            <td>
              <div class="client-name"><?= htmlspecialchars($fullName) ?></div>
              <?php if ($sh['tel']): ?>
                <div class="client-sub">📞 <?= htmlspecialchars($sh['tel']) ?></div>
              <?php endif; ?>
            </td>

            <!-- Wilaya -->
            <td style="font-size:12.5px;font-weight:600"><?= htmlspecialchars($sh['wilaya'] ?: '—') ?></td>

            <!-- Articles -->
            <td>
              <div class="items-tags">
                <?php foreach (array_slice($items, 0, 2) as $item): ?>
                  <span class="item-tag">
                    <?= htmlspecialchars($item['name']) ?>
                    <?php if ($item['shade']): ?>&nbsp;·&nbsp;<?= htmlspecialchars($item['shade']) ?><?php endif; ?>
                    <strong style="color:var(--rose)"> ×<?= $item['quantity'] ?></strong>
                  </span>
                <?php endforeach; ?>
                <?php if (count($items) > 2): ?>
                  <span class="item-tag item-tag-more">+<?= count($items) - 2 ?></span>
                <?php endif; ?>
                <?php if (!$items): ?><span style="color:var(--muted2);font-size:11px">—</span><?php endif; ?>
              </div>
            </td>

            <!-- Paiement -->
            <td style="white-space:nowrap"><?= payLabel($o['payment_method']) ?></td>

            <!-- Statut -->
            <td><?= statusBadge($o['status']) ?></td>

            <!-- Date -->
            <td style="font-size:12px;color:var(--muted);white-space:nowrap">
              <?= date('d/m/Y', strtotime($o['created_at'])) ?><br>
              <span style="font-size:11px"><?= date('H:i', strtotime($o['created_at'])) ?></span>
            </td>

            <!-- Total -->
            <td class="total-cell"><?= number_format((float)$o['total'], 2, ',', ' ') ?> DA</td>

            <!-- Actions -->
            <td>
              <div class="action-group">
                <?php
                $nextActions = match ($o['status']) {
                  'pending'   => [['confirmed','✔ Confirmer','btn-confirm'],['cancelled','✗','btn-cancel']],
                  'confirmed' => [['shipped',  '🚚 Expédier', 'btn-ship'],  ['cancelled','✗','btn-cancel']],
                  'shipped'   => [['delivered','✅ Livré',    'btn-deliver'],['pending', '↩','btn-pending']],
                  'delivered' => [],
                  'cancelled' => [['pending',  '↩ Réactiver','btn-pending']],
                  default     => [],
                };
                foreach ($nextActions as [$action, $label, $cls]): ?>
                  <form method="POST" style="display:inline" onsubmit="return confirm('<?= htmlspecialchars($label) ?> ?')">
                    <input type="hidden" name="action"   value="<?= $action ?>">
                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($o['order_id']) ?>">
                    <input type="hidden" name="_status"  value="<?= htmlspecialchars($filterStatus) ?>">
                    <input type="hidden" name="_wilaya"  value="<?= htmlspecialchars($filterWilaya) ?>">
                    <input type="hidden" name="_payment" value="<?= htmlspecialchars($filterPayment) ?>">
                    <input type="hidden" name="_q"       value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="_page"    value="<?= $page ?>">
                    <button type="submit" class="btn-action <?= $cls ?>"><?= $label ?></button>
                  </form>
                <?php endforeach; ?>
                <button class="btn-action btn-detail"
                  onclick="openDetail(<?= htmlspecialchars(json_encode([
                    'order_id'       => $o['order_id'],
                    'status'         => $o['status'],
                    'payment_method' => $o['payment_method'],
                    'total'          => $o['total'],
                    'created_at'     => $o['created_at'],
                    'sh'             => $sh,
                    'items'          => $items,
                  ]), ENT_QUOTES) ?>)">👁</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9">
              <div class="empty-state">
                <div class="empty-icon">📭</div>
                <div class="empty-text">Aucune commande ne correspond à vos critères</div>
              </div>
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Mobile : cartes -->
    <div class="mobile-orders">
      <?php if ($orders): ?>
        <?php foreach ($orders as $o):
          $sh       = parseShipping($o['shipping']);
          $fullName = trim($sh['prenom'] . ' ' . $sh['nom']) ?: '—';
          $items    = $itemsByOrder[$o['id']] ?? [];
          $nextActions = match ($o['status']) {
            'pending'   => [['confirmed','✔ Confirmer','btn-confirm'],['cancelled','✗ Annuler','btn-cancel']],
            'confirmed' => [['shipped',  '🚚 Expédier', 'btn-ship'],  ['cancelled','✗ Annuler','btn-cancel']],
            'shipped'   => [['delivered','✅ Livré',    'btn-deliver'],['pending', '↩ Attente','btn-pending']],
            'delivered' => [],
            'cancelled' => [['pending',  '↩ Réactiver','btn-pending']],
            default     => [],
          };
        ?>
        <div class="m-order-card">
          <div class="m-order-top">
            <div>
              <div class="m-order-name"><?= htmlspecialchars($fullName) ?></div>
              <div class="m-order-id"><?= htmlspecialchars($o['order_id']) ?></div>
            </div>
            <?= statusBadge($o['status']) ?>
          </div>
          <div class="m-order-row">
            <span><?= htmlspecialchars($sh['wilaya'] ?: '—') ?> · <?= payLabel($o['payment_method']) ?></span>
            <span class="m-order-total"><?= number_format((float)$o['total'], 2, ',', ' ') ?> DA</span>
          </div>
          <?php if ($sh['tel']): ?>
          <div class="m-order-row">
            <span>📞 <?= htmlspecialchars($sh['tel']) ?></span>
            <span style="font-size:11px"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($items): ?>
          <div class="m-order-row" style="flex-wrap:wrap;gap:4px;margin-top:7px">
            <?php foreach (array_slice($items, 0, 3) as $item): ?>
              <span class="item-tag"><?= htmlspecialchars($item['name']) ?><?php if ($item['shade']): ?> · <?= htmlspecialchars($item['shade']) ?><?php endif; ?> ×<?= $item['quantity'] ?></span>
            <?php endforeach; ?>
            <?php if (count($items) > 3): ?>
              <span class="item-tag item-tag-more">+<?= count($items) - 3 ?></span>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <div class="m-order-actions">
            <?php foreach ($nextActions as [$action, $label, $cls]): ?>
              <form method="POST" style="display:inline" onsubmit="return confirm('<?= htmlspecialchars($label) ?> ?')">
                <input type="hidden" name="action"   value="<?= $action ?>">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($o['order_id']) ?>">
                <input type="hidden" name="_status"  value="<?= htmlspecialchars($filterStatus) ?>">
                <input type="hidden" name="_wilaya"  value="<?= htmlspecialchars($filterWilaya) ?>">
                <input type="hidden" name="_payment" value="<?= htmlspecialchars($filterPayment) ?>">
                <input type="hidden" name="_q"       value="<?= htmlspecialchars($search) ?>">
                <input type="hidden" name="_page"    value="<?= $page ?>">
                <button type="submit" class="btn-action <?= $cls ?>"><?= $label ?></button>
              </form>
            <?php endforeach; ?>
            <button class="btn-action btn-detail"
              onclick="openDetail(<?= htmlspecialchars(json_encode([
                'order_id'       => $o['order_id'],
                'status'         => $o['status'],
                'payment_method' => $o['payment_method'],
                'total'          => $o['total'],
                'created_at'     => $o['created_at'],
                'sh'             => $sh,
                'items'          => $items,
              ]), ENT_QUOTES) ?>)">👁 Détail</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">📭</div>
          <div class="empty-text">Aucune commande ne correspond à vos critères</div>
        </div>
      <?php endif; ?>
    </div>
  </div><!-- /.card -->

  <!-- PAGINATION -->
  <?php if ($pages > 1): ?>
  <div class="pagination">
    <?php if ($page > 2): ?>
      <a href="<?= qs(['page' => 1]) ?>" class="page-btn" title="Première">«</a>
    <?php endif; ?>
    <a href="<?= qs(['page' => $page - 1]) ?>" class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">← Préc</a>
    <?php
      $window = 2;
      $start  = max(1, $page - $window);
      $end    = min($pages, $page + $window);
      if ($end - $start < $window * 2) {
        if ($start === 1) $end = min($pages, $start + $window * 2);
        else              $start = max(1, $end - $window * 2);
      }
      for ($i = $start; $i <= $end; $i++): ?>
      <a href="<?= qs(['page' => $i]) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="<?= qs(['page' => $page + 1]) ?>" class="page-btn <?= $page >= $pages ? 'disabled' : '' ?>">Suiv →</a>
    <?php if ($page < $pages - 1): ?>
      <a href="<?= qs(['page' => $pages]) ?>" class="page-btn" title="Dernière">»</a>
    <?php endif; ?>
    <span class="page-info"><?= $page ?> / <?= $pages ?></span>
  </div>
  <?php endif; ?>

</main>

<!-- ── MODAL DÉTAIL ───────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this)closeDetail()">
  <div class="modal">
    <button class="modal-close" onclick="closeDetail()">×</button>
    <h2 id="modalTitle"></h2>
    <p id="modalOrderId" style="font-family:monospace;font-size:12px;color:var(--rose);margin-top:4px"></p>
    <div id="modalStatusWrap" style="margin:12px 0"></div>
    <div class="modal-section">
      <div class="modal-section-title">Livraison</div>
      <div id="modalShipping"></div>
    </div>
    <div class="modal-section" id="modalItemsSection">
      <div class="modal-section-title">Articles commandés</div>
      <div id="modalItems"></div>
    </div>
    <div class="modal-section">
      <div class="modal-section-title">Commande</div>
      <div id="modalMeta"></div>
    </div>
  </div>
</div>

<script>
// ── Modal ─────────────────────────────────────────────────────────────────────
const statusMap = {pending:'En attente',confirmed:'Confirmée',shipped:'Expédiée',delivered:'Livrée',cancelled:'Annulée'};
const colorMap  = {pending:'bdg-amber',confirmed:'bdg-plum',shipped:'bdg-blue',delivered:'bdg-green',cancelled:'bdg-red'};

function openDetail(o) {
  const sh = o.sh || {};
  document.getElementById('modalTitle').textContent    = [sh.prenom, sh.nom].filter(Boolean).join(' ') || 'Client inconnu';
  document.getElementById('modalOrderId').textContent  = o.order_id;
  document.getElementById('modalStatusWrap').innerHTML = `<span class="badge ${colorMap[o.status]||'bdg-gray'}">${statusMap[o.status]||o.status}</span>`;

  document.getElementById('modalShipping').innerHTML = [
    ['Téléphone', sh.tel     || '—'],
    ['Wilaya',    sh.wilaya  || '—'],
    ['Adresse',   sh.adresse || '—'],
    ['Note',      sh.note    || '—'],
  ].map(([k,v]) => `<div class="modal-row"><span>${k}</span><span>${v}</span></div>`).join('');

  const items = o.items || [];
  if (items.length) {
    document.getElementById('modalItemsSection').style.display = '';
    document.getElementById('modalItems').innerHTML = items.map(item => `
      <div class="modal-item">
        <span class="modal-item-name">
          ${item.name}${item.shade ? `<span class="modal-item-shade">${item.shade}</span>` : ''}
          <span style="color:var(--muted);font-size:11px"> × ${item.quantity}</span>
        </span>
        <span class="modal-item-price">${(item.unit_price * item.quantity).toLocaleString('fr')} DA</span>
      </div>`).join('');
  } else {
    document.getElementById('modalItemsSection').style.display = 'none';
  }

  const payStr = o.payment_method === 'cash' ? '💵 Livraison' : o.payment_method === 'ccp' ? '🏦 CCP' : '📱 Baridi';
  document.getElementById('modalMeta').innerHTML = [
    ['Paiement', payStr],
    ['Total',    parseFloat(o.total).toLocaleString('fr',{minimumFractionDigits:2}) + ' DA'],
    ['Date',     o.created_at ? o.created_at.substring(0,16).replace('T',' ') : '—'],
  ].map(([k,v]) => `<div class="modal-row"><span>${k}</span><span>${v}</span></div>`).join('');

  document.getElementById('modalOverlay').classList.add('open');
}

function closeDetail() {
  document.getElementById('modalOverlay').classList.remove('open');
}

// Fermer modal avec Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });

// ── Sidebar mobile ────────────────────────────────────────────────────────────
const sidebar    = document.getElementById('sidebar');
const hamburger  = document.getElementById('hamburger');
const overlay    = document.getElementById('overlay');
const openSb     = () => { sidebar.classList.add('open'); overlay.classList.add('active'); hamburger.classList.add('open'); document.body.style.overflow = 'hidden'; };
const closeSb    = () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); hamburger.classList.remove('open'); document.body.style.overflow = ''; };
hamburger.addEventListener('click', () => sidebar.classList.contains('open') ? closeSb() : openSb());
overlay.addEventListener('click', closeSb);
sidebar.querySelectorAll('.nav-link').forEach(l => l.addEventListener('click', () => { if (window.innerWidth <= 900) closeSb(); }));
</script>
</body>
</html>