<?php
// ============================================
//  SheGlamour — Dashboard Admin (PostgreSQL)
// ============================================

header('X-Frame-Options: SAMEORIGIN');
header('Content-Security-Policy: frame-ancestors \'self\'');

session_start();

define('ADMIN_PASSWORD', 'sheglamour2024');

if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['password'] ?? '') === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        ?><!DOCTYPE html>
        <html lang="fr">
        <head>
          <meta charset="UTF-8">
          <title>SheGlamour Admin — Connexion</title>
          <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
          <style>
            *{margin:0;padding:0;box-sizing:border-box}
            body{background:#f7f4f2;font-family:'Nunito',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}
            .box{background:#fff;border:1px solid #e8e0d8;border-radius:16px;padding:48px 40px;width:360px;box-shadow:0 4px 24px rgba(0,0,0,.07);text-align:center}
            h1{font-family:'Playfair Display',serif;font-size:28px;color:#1a1714;margin-bottom:6px}
            h1 span{color:#c4697a}
            p{color:#8c7d73;font-size:13px;margin-bottom:28px}
            input{width:100%;padding:12px 16px;border:1px solid #e8e0d8;border-radius:10px;font-size:14px;font-family:'Nunito',sans-serif;outline:none;margin-bottom:14px;background:#f7f4f2}
            input:focus{border-color:#c4697a;background:#fff}
            button{width:100%;padding:13px;background:#c4697a;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;font-family:'Nunito',sans-serif;cursor:pointer}
            button:hover{background:#b05568}
            .error{color:#c0392b;font-size:12px;margin-bottom:12px;background:#fdf0ee;border:1px solid #f0c0bb;padding:8px 12px;border-radius:8px}
          </style>
        </head>
        <body>
          <div class="box">
            <h1>She<span>Glamour</span></h1>
            <p>Accès administration</p>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
              <div class="error">Mot de passe incorrect.</div>
            <?php endif; ?>
            <form method="POST">
              <input type="password" name="password" placeholder="Mot de passe" autofocus required>
              <button type="submit">Accéder au dashboard</button>
            </form>
          </div>
        </body>
        </html><?php
        exit;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: dashboard.php');
    exit;
}

include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/config.php';
$b = BASE_URL;

// ─── KPIs ─────────────────────────────────────────────────────────────────────

$r = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'");
$kpis['revenue'] = (float) $r->fetchColumn();

$r = $pdo->query("SELECT COUNT(*) FROM orders");
$kpis['orders'] = (int) $r->fetchColumn();

$r = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$kpis['pending'] = (int) $r->fetchColumn();

$r = $pdo->query("SELECT COUNT(*) FROM products");
$kpis['products'] = (int) $r->fetchColumn();

// PostgreSQL : TO_CHAR au lieu de DATE_FORMAT
$r = $pdo->query("
    SELECT COALESCE(SUM(total),0) FROM orders
    WHERE TO_CHAR(created_at,'YYYY-MM') = TO_CHAR(NOW(),'YYYY-MM')
      AND status != 'cancelled'
");
$kpis['revenue_month'] = (float) $r->fetchColumn();

// PostgreSQL : CURRENT_DATE au lieu de CURDATE()
$r = $pdo->query("SELECT COUNT(*) FROM orders WHERE created_at::date = CURRENT_DATE");
$kpis['today_orders'] = (int) $r->fetchColumn();

$r = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'");
$delivered = (int) $r->fetchColumn();
$kpis['conversion'] = $kpis['orders'] > 0 ? round($delivered / $kpis['orders'] * 100, 1) : 0;

// ─── RÉPARTITION STATUTS ──────────────────────────────────────────────────────
$statusCounts = [];
$stRes = $pdo->query("SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status");
while ($row = $stRes->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['status']] = (int) $row['cnt'];
}
$totalOrders = array_sum($statusCounts);

// ─── CA PAR JOUR (30 derniers jours) — PostgreSQL ────────────────────────────
$chartData = $pdo->query("
    SELECT created_at::date AS day, SUM(total) AS total
    FROM orders
    WHERE created_at >= NOW() - INTERVAL '30 days'
      AND status != 'cancelled'
    GROUP BY day
    ORDER BY day ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ─── TOP PRODUITS ─────────────────────────────────────────────────────────────
$topProducts = $pdo->query("
    SELECT oi.name, oi.shade,
           SUM(oi.quantity) AS qty,
           SUM(oi.quantity * oi.unit_price) AS revenue
    FROM order_items oi
    GROUP BY oi.name, oi.shade
    ORDER BY revenue DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// ─── TOP TEINTES ──────────────────────────────────────────────────────────────
$topShades = $pdo->query("
    SELECT oi.shade,
           oi.name AS product_name,
           SUM(oi.quantity) AS qty,
           SUM(oi.quantity * oi.unit_price) AS revenue,
           t.code_couleur
    FROM order_items oi
    LEFT JOIN teintes t ON t.nom_teinte = oi.shade
                       AND t.product_id = (
                           SELECT id FROM products WHERE name = oi.name LIMIT 1
                       )
    WHERE oi.shade IS NOT NULL AND oi.shade != ''
    GROUP BY oi.shade, oi.name, t.code_couleur
    ORDER BY qty DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// ─── STOCK TEINTES CRITIQUE ───────────────────────────────────────────────────
$lowStockShades = $pdo->query("
    SELECT t.nom_teinte, t.code_couleur, t.stock, t.prix,
           p.name AS product_name, p.id AS product_id
    FROM teintes t
    JOIN products p ON p.id = t.product_id
    WHERE t.stock <= 5
    ORDER BY t.stock ASC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// ─── WILAYAS — PostgreSQL JSON ────────────────────────────────────────────────
// PostgreSQL utilise ->>'clé' pour extraire du JSONB/JSON
$wilayaData = $pdo->query("
    SELECT shipping->>'wilaya' AS wilaya, COUNT(*) AS cnt
    FROM orders
    WHERE shipping->>'wilaya' IS NOT NULL
    GROUP BY wilaya
    ORDER BY cnt DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// ─── MODES DE PAIEMENT ────────────────────────────────────────────────────────
$paymentData = $pdo->query("
    SELECT payment_method, COUNT(*) AS cnt
    FROM orders
    GROUP BY payment_method
")->fetchAll(PDO::FETCH_ASSOC);

// ─── COMMANDES RÉCENTES ───────────────────────────────────────────────────────
$recentOrders = $pdo->query("
    SELECT order_id, status, payment_method, total, shipping, created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// ─── HELPERS ──────────────────────────────────────────────────────────────────
function statusBadge($s) {
    $map = [
        'pending'   => ['label' => 'En attente', 'class' => 'badge-amber'],
        'confirmed' => ['label' => 'Confirmée',  'class' => 'badge-indigo'],
        'shipped'   => ['label' => 'Expédiée',   'class' => 'badge-blue'],
        'delivered' => ['label' => 'Livrée',     'class' => 'badge-green'],
        'cancelled' => ['label' => 'Annulée',    'class' => 'badge-red'],
    ];
    $d = $map[$s] ?? ['label' => $s, 'class' => 'badge-gray'];
    return "<span class='badge {$d['class']}'>{$d['label']}</span>";
}
function payLabel($p) {
    $map = ['cash' => '💵 Livraison', 'ccp' => '🏦 CCP', 'baridimob' => '📱 Baridi'];
    return $map[$p] ?? htmlspecialchars($p ?? '—');
}
function stockBadge($stock) {
    if ((int)$stock === 0) return "<span class='badge badge-red'>Épuisé</span>";
    if ((int)$stock <= 3)  return "<span class='badge badge-red'>Critique</span>";
    return "<span class='badge badge-amber'>Faible</span>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SheGlamour — Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<link rel="icon" type="image/png" href="<?= $b ?>/images/logofib.png">
<style>
:root {
  --bg:#f7f4f2;--surface:#fff;--surface2:#f2eee9;
  --border:#e8e0d8;--text:#1a1714;--text2:#4a3f38;--muted:#8c7d73;--muted2:#b5a99f;
  --rose:#c4697a;--rose-bg:#fdf0f2;--rose-lt:#f5d0d7;
  --plum:#8b5a8b;--plum-bg:#f5eef5;--plum-lt:#dfc8df;
  --green:#3a8a5c;--green-bg:#eef7f2;--green-lt:#b8dfc9;
  --amber:#b07030;--amber-bg:#fdf5eb;--amber-lt:#f0d4a8;
  --blue:#3a6db0;--blue-bg:#eef3fb;--blue-lt:#b8cff0;
  --red:#c0392b;--red-bg:#fdf0ee;--red-lt:#f0c0bb;
  --radius:16px;--radius-sm:10px;
  --shadow:0 1px 4px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.05);
  --shadow-sm:0 1px 3px rgba(0,0,0,.05);
  --font-display:'Playfair Display',serif;--font-body:'Nunito',sans-serif;
}
*{margin:0;padding:0;box-sizing:border-box}
body{background:var(--bg);color:var(--text);font-family:var(--font-body);font-size:14px;min-height:100vh}

.adm-sidebar{position:fixed;top:0;left:0;width:230px;height:100vh;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;padding:32px 0 24px;box-shadow:2px 0 12px rgba(0,0,0,.04)}
.adm-sidebar-logo{padding:0 24px 32px;font-family:var(--font-display);font-size:24px}
.adm-sidebar-logo span{color:var(--rose)}
.sidebar-section{padding:0 16px 8px;font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted2);margin-top:8px}
.sidebar-nav{display:flex;flex-direction:column;gap:3px;padding:0 12px;flex:1}
.nav-item{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:var(--radius-sm);font-size:13.5px;font-weight:600;color:var(--muted);transition:all .18s;text-decoration:none}
.nav-item:hover{background:var(--surface2);color:var(--text2)}
.nav-item.active{background:var(--rose-bg);color:var(--rose)}
.nav-icon{font-size:15px;width:20px;text-align:center}
.sidebar-footer{padding:16px 24px 0;font-size:11px;color:var(--muted2);border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.logout-btn{font-size:11px;color:var(--muted);text-decoration:none;font-weight:600}
.logout-btn:hover{color:var(--red)}

.main{margin-left:230px;padding:40px 36px;min-height:100vh}
.page-header{margin-bottom:32px;display:flex;align-items:flex-end;justify-content:space-between}
.page-header h1{font-family:var(--font-display);font-size:34px;letter-spacing:-.01em}
.page-header p{color:var(--muted);margin-top:4px;font-size:13px}
.header-badge{background:var(--rose-bg);color:var(--rose);border:1px solid var(--rose-lt);padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700}

.kpi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
.kpi-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px 24px;position:relative;overflow:hidden;box-shadow:var(--shadow-sm);transition:box-shadow .2s,transform .2s}
.kpi-card:hover{box-shadow:var(--shadow);transform:translateY(-1px)}
.kpi-card-accent{position:absolute;top:0;left:0;width:4px;height:100%;border-radius:16px 0 0 16px}
.kpi-label{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:10px}
.kpi-value{font-size:28px;font-weight:700;letter-spacing:-.02em}
.kpi-value small{font-size:14px;font-weight:500;color:var(--muted);margin-left:3px}
.kpi-sub{font-size:11px;color:var(--muted2);margin-top:5px}
.kpi-icon{position:absolute;top:18px;right:20px;font-size:28px;opacity:.18}

.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm)}
.card-header{padding:18px 24px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);background:var(--surface2)}
.card-title{font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
.card-body{padding:20px 24px}

.badge{display:inline-flex;align-items:center;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.04em}
.badge-amber {background:var(--amber-bg);color:var(--amber);border:1px solid var(--amber-lt)}
.badge-indigo{background:var(--plum-bg);color:var(--plum);border:1px solid var(--plum-lt)}
.badge-blue  {background:var(--blue-bg);color:var(--blue);border:1px solid var(--blue-lt)}
.badge-green {background:var(--green-bg);color:var(--green);border:1px solid var(--green-lt)}
.badge-red   {background:var(--red-bg);color:var(--red);border:1px solid var(--red-lt)}
.badge-gray  {background:var(--surface2);color:var(--muted);border:1px solid var(--border)}

.grid-2 {display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px}
.grid-2b{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
.mb-20  {margin-bottom:20px}

.status-bar{display:flex;height:10px;border-radius:5px;overflow:hidden;margin:8px 0 16px;border:1px solid var(--border)}
.status-bar-seg{height:100%}
.status-legend{display:flex;gap:16px;flex-wrap:wrap}
.status-legend-item{display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--text2);font-weight:500}
.status-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}

.mini-bar-row{margin-bottom:14px}
.mini-bar-row:last-child{margin-bottom:0}
.mini-bar-label{display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;font-weight:600;color:var(--text2)}
.mini-bar-label span:last-child{color:var(--muted);font-weight:500}
.mini-bar-track{height:7px;background:var(--surface2);border-radius:4px;overflow:hidden;border:1px solid var(--border)}
.mini-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--rose),var(--plum));transition:width 1s ease}

.chart-wrap{padding-top:12px;height:220px;position:relative}

.product-row{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--border)}
.product-row:last-child{border-bottom:none}
.product-rank{width:30px;height:30px;border-radius:50%;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:var(--muted);flex-shrink:0}
.product-rank.gold{background:var(--amber-bg);border-color:var(--amber-lt);color:var(--amber)}
.product-info{flex:1;min-width:0}
.product-name{font-size:13.5px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.product-qty{font-size:11px;color:var(--muted);margin-top:2px}
.product-revenue{font-size:13px;font-weight:800;color:var(--rose);white-space:nowrap}

.shade-swatch{display:inline-block;width:16px;height:16px;border-radius:50%;border:2px solid var(--border);flex-shrink:0}
.shade-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)}
.shade-row:last-child{border-bottom:none}
.shade-info{flex:1;min-width:0}
.shade-name{font-size:13px;font-weight:700}
.shade-product{font-size:11px;color:var(--muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.shade-qty{font-size:13px;font-weight:800;color:var(--plum);white-space:nowrap}

.stock-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)}
.stock-row:last-child{border-bottom:none}
.stock-info{flex:1;min-width:0}
.stock-name{font-size:13px;font-weight:700}
.stock-product{font-size:11px;color:var(--muted);margin-top:1px}
.stock-qty{font-size:18px;font-weight:800;color:var(--red)}

.stat-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
.stat-pill{flex:1;min-width:80px;background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:14px;text-align:center}
.stat-pill-val{font-size:22px;font-weight:800}
.stat-pill-lbl{font-size:10px;color:var(--muted);margin-top:3px;letter-spacing:.06em;text-transform:uppercase;font-weight:700}

.table-scroll{overflow-y:auto;max-height:420px}
.table{width:100%;border-collapse:collapse}
.table th{font-size:10.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);padding:0 0 14px;text-align:left;border-bottom:2px solid var(--border)}
.table td{padding:13px 0;border-bottom:1px solid var(--border);vertical-align:middle;font-size:13px;color:var(--text2)}
.table tr:last-child td{border-bottom:none}
.table tr:hover td{background:var(--surface2)}
.table td:last-child,.table th:last-child{text-align:right}
.order-id{font-family:'Courier New',monospace;font-size:11px;color:var(--rose);font-weight:700;background:var(--rose-bg);padding:2px 7px;border-radius:5px;border:1px solid var(--rose-lt)}

.btn{display:inline-flex;align-items:center;gap:7px;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;transition:all .15s;border:none;font-family:var(--font-body);text-decoration:none}
.btn-primary{background:var(--rose);color:#fff}
.btn-primary:hover{background:#b05568}
.btn-sm{padding:4px 10px;font-size:11px;border-radius:6px}
.empty-msg{color:var(--muted);font-size:13px;text-align:center;padding:24px 0}

@media(max-width:900px){
  .adm-sidebar{display:none}
  .main{margin-left:0;padding:20px}
  .kpi-grid{grid-template-columns:repeat(2,1fr)}
  .grid-2,.grid-2b{grid-template-columns:1fr}
}
</style>
</head>
<body>

<aside class="adm-sidebar">
  <div class="adm-sidebar-logo">She<span>Glamour</span></div>
  <div class="sidebar-section">Navigation</div>
  <nav class="sidebar-nav">
    <a class="nav-item active" href="dashboard.php">
      <span class="nav-icon">◈</span> Tableau de bord
    </a>
    <a class="nav-item" href="admin_orders.php">
      <span class="nav-icon">📦</span> Commandes
      <?php if ($kpis['pending'] > 0): ?>
        <span style="margin-left:auto;background:var(--amber);color:#fff;border-radius:10px;padding:1px 7px;font-size:10px"><?= $kpis['pending'] ?></span>
      <?php endif; ?>
    </a>
    <a class="nav-item" href="admin_products.php">
      <span class="nav-icon">✦</span> Produits
    </a>
    <a class="nav-item" href="index.php" target="_blank">
      <span class="nav-icon">↗</span> Voir la boutique
    </a>
  </nav>
  <div class="sidebar-footer">
    SheGlamour Admin · v2.0
    <a href="dashboard.php?logout=1" class="logout-btn">Déconnexion</a>
  </div>
</aside>

<main class="main">

  <div class="page-header">
    <div>
      <h1>Tableau de bord</h1>
      <p>Vue d'ensemble en temps réel</p>
    </div>
    <span class="header-badge">📅 <?= date('d F Y, H:i') ?></span>
  </div>

  <!-- KPIs LIGNE 1 -->
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-card-accent" style="background:var(--rose)"></div>
      <div class="kpi-label">CA Total</div>
      <div class="kpi-value"><?= number_format($kpis['revenue'],0,',',' ') ?><small>DA</small></div>
      <div class="kpi-sub">Hors commandes annulées</div>
      <div class="kpi-icon">💰</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-card-accent" style="background:var(--plum)"></div>
      <div class="kpi-label">CA ce mois</div>
      <div class="kpi-value"><?= number_format($kpis['revenue_month'],0,',',' ') ?><small>DA</small></div>
      <div class="kpi-sub"><?= date('F Y') ?></div>
      <div class="kpi-icon">📅</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-card-accent" style="background:var(--green)"></div>
      <div class="kpi-label">Taux livraison</div>
      <div class="kpi-value"><?= $kpis['conversion'] ?><small>%</small></div>
      <div class="kpi-sub">Commandes livrées / total</div>
      <div class="kpi-icon">✅</div>
    </div>
  </div>

  <!-- KPIs LIGNE 2 -->
  <div class="kpi-grid mb-20">
    <div class="kpi-card">
      <div class="kpi-card-accent" style="background:var(--blue)"></div>
      <div class="kpi-label">Commandes totales</div>
      <div class="kpi-value"><?= $kpis['orders'] ?></div>
      <div class="kpi-sub">Tous statuts confondus</div>
      <div class="kpi-icon">📦</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-card-accent" style="background:var(--amber)"></div>
      <div class="kpi-label">En attente</div>
      <div class="kpi-value"><?= $kpis['pending'] ?></div>
      <div class="kpi-sub">À traiter en priorité</div>
      <div class="kpi-icon">⏳</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-card-accent" style="background:var(--rose)"></div>
      <div class="kpi-label">Aujourd'hui</div>
      <div class="kpi-value"><?= $kpis['today_orders'] ?></div>
      <div class="kpi-sub">Nouvelles commandes</div>
      <div class="kpi-icon">🛍</div>
    </div>
  </div>

  <!-- RÉPARTITION STATUTS -->
  <?php
    $statusColors = ['pending'=>'#b07030','confirmed'=>'#8b5a8b','shipped'=>'#3a6db0','delivered'=>'#3a8a5c','cancelled'=>'#c0392b'];
    $statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmée','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];
  ?>
  <?php if ($totalOrders > 0): ?>
  <div class="card mb-20">
    <div class="card-header">
      <span class="card-title">Répartition des statuts</span>
      <span style="font-size:12px;color:var(--muted);font-weight:600"><?= $totalOrders ?> commandes au total</span>
    </div>
    <div class="card-body">
      <div class="status-bar">
        <?php foreach ($statusColors as $key => $color):
          $cnt = $statusCounts[$key] ?? 0; if (!$cnt) continue;
          $pct = round($cnt / $totalOrders * 100, 1);
        ?>
          <div class="status-bar-seg" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
        <?php endforeach; ?>
      </div>
      <div class="status-legend">
        <?php foreach ($statusColors as $key => $color):
          $cnt = $statusCounts[$key] ?? 0; if (!$cnt) continue;
          $pct = round($cnt / $totalOrders * 100, 1);
        ?>
          <div class="status-legend-item">
            <span class="status-dot" style="background:<?= $color ?>"></span>
            <?= $statusLabels[$key] ?> — <strong style="color:var(--text)"><?= $cnt ?></strong>&nbsp;(<?= $pct ?>%)
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- GRAPHIQUE + WILAYAS -->
  <div class="grid-2">
    <div class="card">
      <div class="card-header">
        <span class="card-title">CA — 30 derniers jours</span>
        <span style="font-size:13px;font-weight:800;color:var(--rose)"><?= number_format($kpis['revenue_month'],0,',',' ') ?> DA ce mois</span>
      </div>
      <div class="card-body">
        <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><span class="card-title">Top Wilayas</span></div>
      <div class="card-body">
        <?php $maxW = $wilayaData ? max(array_column($wilayaData,'cnt')) : 1;
          foreach ($wilayaData as $w):
            $pct = round($w['cnt'] / $maxW * 100); ?>
        <div class="mini-bar-row">
          <div class="mini-bar-label">
            <span><?= htmlspecialchars($w['wilaya'] ?? '—') ?></span>
            <span><?= $w['cnt'] ?> cmd</span>
          </div>
          <div class="mini-bar-track"><div class="mini-bar-fill" style="width:<?= $pct ?>%"></div></div>
        </div>
        <?php endforeach; ?>
        <?php if (!$wilayaData): ?><p class="empty-msg">Aucune donnée</p><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- TOP TEINTES + STOCK CRITIQUE -->
  <div class="grid-2b">
    <div class="card">
      <div class="card-header">
        <span class="card-title">🎨 Top Teintes vendues</span>
        <span style="font-size:11px;color:var(--muted)">par quantité commandée</span>
      </div>
      <div class="card-body">
        <?php if ($topShades): foreach ($topShades as $s): ?>
        <div class="shade-row">
          <span class="shade-swatch" style="background:<?= htmlspecialchars($s['code_couleur'] ?? '#ccc') ?>"></span>
          <div class="shade-info">
            <div class="shade-name"><?= htmlspecialchars($s['shade']) ?></div>
            <div class="shade-product"><?= htmlspecialchars($s['product_name']) ?></div>
          </div>
          <div style="text-align:right">
            <div class="shade-qty"><?= $s['qty'] ?> vendus</div>
            <div style="font-size:11px;color:var(--muted)"><?= number_format($s['revenue'],0,',',' ') ?> DA</div>
          </div>
        </div>
        <?php endforeach; else: ?>
          <p class="empty-msg">Aucune vente de teinte enregistrée</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <span class="card-title">⚠️ Stock teintes critique</span>
        <span style="font-size:11px;color:var(--red);font-weight:700">≤ 5 unités</span>
      </div>
      <div class="card-body">
        <?php if ($lowStockShades): foreach ($lowStockShades as $s): ?>
        <div class="stock-row">
          <span class="shade-swatch" style="background:<?= htmlspecialchars($s['code_couleur'] ?? '#ccc') ?>"></span>
          <div class="stock-info">
            <div class="stock-name"><?= htmlspecialchars($s['nom_teinte']) ?></div>
            <div class="stock-product">
              <a href="admin_products.php?id=<?= $s['product_id'] ?>" style="color:var(--muted);text-decoration:none">
                <?= htmlspecialchars($s['product_name']) ?>
              </a>
            </div>
          </div>
          <div style="text-align:right">
            <div class="stock-qty"><?= $s['stock'] ?></div>
            <?= stockBadge($s['stock']) ?>
          </div>
        </div>
        <?php endforeach; else: ?>
          <p class="empty-msg" style="color:var(--green)">✅ Tous les stocks sont OK</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- TOP PRODUITS + PAIEMENTS -->
  <div class="grid-2">
    <div class="card">
      <div class="card-header">
        <span class="card-title">Top Produits</span>
        <span style="font-size:11px;color:var(--muted)">produit + teinte</span>
      </div>
      <div class="card-body">
        <?php foreach ($topProducts as $i => $p): ?>
        <div class="product-row">
          <div class="product-rank <?= $i === 0 ? 'gold' : '' ?>"><?= $i + 1 ?></div>
          <div class="product-info">
            <div class="product-name">
              <?= htmlspecialchars($p['name']) ?>
              <?php if (!empty($p['shade'])): ?>
                <span style="font-size:11px;color:var(--plum);font-weight:600;background:var(--plum-bg);padding:1px 6px;border-radius:10px;margin-left:4px">
                  <?= htmlspecialchars($p['shade']) ?>
                </span>
              <?php endif; ?>
            </div>
            <div class="product-qty"><?= $p['qty'] ?> unités vendues</div>
          </div>
          <div class="product-revenue"><?= number_format($p['revenue'],0,',',' ') ?> DA</div>
        </div>
        <?php endforeach; ?>
        <?php if (!$topProducts): ?><p class="empty-msg">Aucune vente enregistrée</p><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Modes de paiement</span></div>
      <div class="card-body">
        <div class="chart-wrap" style="height:160px;padding-top:0">
          <canvas id="paymentChart"></canvas>
        </div>
        <div class="stat-row">
          <?php foreach ($paymentData as $pm): ?>
          <div class="stat-pill">
            <div class="stat-pill-val"><?= $pm['cnt'] ?></div>
            <div class="stat-pill-lbl"><?= payLabel($pm['payment_method']) ?></div>
          </div>
          <?php endforeach; ?>
          <?php if (!$paymentData): ?><p class="empty-msg" style="width:100%">Aucune donnée</p><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- COMMANDES RÉCENTES -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Commandes récentes</span>
      <a href="admin_orders.php" class="btn btn-primary btn-sm">Voir toutes →</a>
    </div>
    <div class="card-body" style="padding:0 24px">
      <div class="table-scroll">
        <table class="table">
          <thead>
            <tr><th>ID</th><th>Client</th><th>Wilaya</th><th>Paiement</th><th>Statut</th><th>Date</th><th>Total</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recentOrders as $o):
              $sh     = is_array($o['shipping']) ? $o['shipping'] : (json_decode($o['shipping'] ?? '{}', true) ?: []);
              $prenom = $sh['prenom'] ?? ($sh['firstName'] ?? '—');
              $nom    = $sh['nom']    ?? ($sh['lastName']  ?? '');
              $wilaya = $sh['wilaya'] ?? '—';
            ?>
            <tr>
              <td><span class="order-id"><?= htmlspecialchars($o['order_id']) ?></span></td>
              <td style="font-weight:700;color:var(--text)"><?= htmlspecialchars(trim($prenom . ' ' . $nom)) ?></td>
              <td style="color:var(--muted);font-size:12px"><?= htmlspecialchars($wilaya) ?></td>
              <td><?= payLabel($o['payment_method']) ?></td>
              <td><?= statusBadge($o['status']) ?></td>
              <td style="color:var(--muted);font-size:12px"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
              <td style="font-weight:800;color:var(--rose)"><?= number_format($o['total'],2,',',' ') ?> DA</td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$recentOrders): ?>
              <tr><td colspan="7" class="empty-msg">Aucune commande pour le moment</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</main>

<script>
Chart.defaults.color = '#8c7d73';
Chart.defaults.font.family = "'Nunito', sans-serif";
Chart.defaults.font.size   = 11;

const chartDays   = <?= json_encode(array_map(fn($r) => (string)$r['day'], $chartData)) ?>;
const chartTotals = <?= json_encode(array_map(fn($r) => (float)$r['total'], $chartData)) ?>;

new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: chartDays,
    datasets: [{
      label: 'CA (DA)', data: chartTotals,
      borderColor: '#c4697a', backgroundColor: 'rgba(196,105,122,.08)',
      borderWidth: 2.5, pointRadius: 3.5,
      pointBackgroundColor: '#c4697a', pointBorderColor: '#fff', pointBorderWidth: 2,
      tension: .4, fill: true,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(0,0,0,.05)' }, ticks: { maxTicksLimit: 8 } },
      y: { grid: { color: 'rgba(0,0,0,.05)' }, ticks: { callback: v => v.toLocaleString('fr') } }
    }
  }
});

const pmLabels = <?= json_encode(array_map(fn($p) => strip_tags(payLabel($p['payment_method'])), $paymentData)) ?>;
const pmCounts = <?= json_encode(array_column($paymentData,'cnt')) ?>;

if (pmCounts.length) {
  new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
      labels: pmLabels,
      datasets: [{ data: pmCounts, backgroundColor: ['#c4697a','#8b5a8b','#3a6db0'], borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '70%',
      plugins: { legend: { position: 'bottom', labels: { padding: 14, boxWidth: 10, borderRadius: 4 } } }
    }
  });
}
</script>
</body>
</html>