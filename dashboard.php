<?php
// ============================================
//  SheGlamour — Dashboard Admin (PostgreSQL)
//  v3.0 — Mobile-first, full redesign
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
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SheGlamour Admin — Connexion</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    :root{--rose:#c4697a;--rose-dark:#a8505f;--ink:#16100e;--soft:#f9f5f2;--border:#ede5de}
    body{background:var(--soft);font-family:'DM Sans',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
    .login-wrap{background:#fff;border:1px solid var(--border);border-radius:20px;padding:52px 44px;width:100%;max-width:400px;box-shadow:0 8px 48px rgba(0,0,0,.08)}
    .login-logo{display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:32px}
    .login-logo svg{width:42px;height:42px}
    .login-logo h1{font-family:'Cormorant Garamond',serif;font-size:30px;color:var(--ink);letter-spacing:-.02em}
    .login-logo h1 span{color:var(--rose)}
    .sub{color:#9c8d85;font-size:13px;text-align:center;margin-bottom:30px;margin-top:-20px}
    input{width:100%;padding:13px 16px;border:1.5px solid var(--border);border-radius:12px;font-size:14px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s,background .2s;background:#faf8f6;color:var(--ink)}
    input:focus{border-color:var(--rose);background:#fff}
    button{width:100%;padding:14px;background:var(--rose);color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer;margin-top:12px;letter-spacing:.02em;transition:background .2s,transform .1s}
    button:hover{background:var(--rose-dark)}
    button:active{transform:scale(.99)}
    .error{color:#b83232;font-size:12.5px;background:#fef2f2;border:1px solid #fcc;padding:10px 14px;border-radius:10px;margin-bottom:16px;text-align:center}
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-logo">
      <svg viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="21" cy="21" r="21" fill="#fdf0f2"/>
        <path d="M21 8C14.373 8 9 13.373 9 20c0 3.866 1.74 7.33 4.5 9.68V34h15v-4.32C31.26 27.33 33 23.866 33 20c0-6.627-5.373-12-12-12z" fill="#f5c6cd"/>
        <path d="M21 11c-4.97 0-9 4.03-9 9 0 2.9 1.37 5.48 3.5 7.12V31h11v-3.88C28.63 25.48 30 22.9 30 20c0-4.97-4.03-9-9-9z" fill="#e8899a"/>
        <ellipse cx="21" cy="20" rx="5" ry="5" fill="#c4697a"/>
        <path d="M18 18.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5S18 19.33 18 18.5z" fill="#fff" opacity=".6"/>
        <rect x="17" y="31" width="8" height="2" rx="1" fill="#c4697a"/>
        <rect x="18.5" y="33" width="5" height="1.5" rx=".75" fill="#a8505f"/>
      </svg>
      <h1>She<span>Glamour</span></h1>
    </div>
    <p class="sub">Accès administration réservé</p>
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
      <div class="error">⚠ Mot de passe incorrect.</div>
    <?php endif; ?>
    <form method="POST">
      <input type="password" name="password" placeholder="Mot de passe admin" autofocus required>
      <button type="submit">Accéder au dashboard →</button>
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

$r = $pdo->query("
    SELECT COALESCE(SUM(total),0) FROM orders
    WHERE TO_CHAR(created_at,'YYYY-MM') = TO_CHAR(NOW(),'YYYY-MM')
      AND status != 'cancelled'
");
$kpis['revenue_month'] = (float) $r->fetchColumn();

$r = $pdo->query("SELECT COUNT(*) FROM orders WHERE created_at::date = CURRENT_DATE");
$kpis['today_orders'] = (int) $r->fetchColumn();

$r = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'");
$delivered = (int) $r->fetchColumn();
$kpis['conversion'] = $kpis['orders'] > 0 ? round($delivered / $kpis['orders'] * 100, 1) : 0;

// ─── STATUTS ──────────────────────────────────────────────────────────────────
$statusCounts = [];
$stRes = $pdo->query("SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status");
while ($row = $stRes->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['status']] = (int) $row['cnt'];
}
$totalOrders = array_sum($statusCounts);

// ─── CA PAR JOUR (30 derniers jours) ──────────────────────────────────────────
$chartData = $pdo->query("
    SELECT created_at::date AS day, SUM(total) AS total
    FROM orders
    WHERE created_at >= NOW() - INTERVAL '30 days'
      AND status != 'cancelled'
    GROUP BY day ORDER BY day ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ─── TOP PRODUITS ─────────────────────────────────────────────────────────────
$topProducts = $pdo->query("
    SELECT oi.name, oi.shade,
           SUM(oi.quantity) AS qty,
           SUM(oi.quantity * oi.unit_price) AS revenue
    FROM order_items oi
    GROUP BY oi.name, oi.shade
    ORDER BY revenue DESC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// ─── TOP TEINTES ──────────────────────────────────────────────────────────────
$topShades = $pdo->query("
    SELECT oi.shade, oi.name AS product_name,
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
    ORDER BY qty DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// ─── STOCK CRITIQUE ───────────────────────────────────────────────────────────
$lowStockShades = $pdo->query("
    SELECT t.nom_teinte, t.code_couleur, t.stock, t.prix,
           p.name AS product_name, p.id AS product_id
    FROM teintes t
    JOIN products p ON p.id = t.product_id
    WHERE t.stock <= 5
    ORDER BY t.stock ASC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// ─── WILAYAS ──────────────────────────────────────────────────────────────────
$wilayaData = $pdo->query("
    SELECT shipping->>'wilaya' AS wilaya, COUNT(*) AS cnt
    FROM orders
    WHERE shipping->>'wilaya' IS NOT NULL
    GROUP BY wilaya ORDER BY cnt DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// ─── PAIEMENTS ────────────────────────────────────────────────────────────────
$paymentData = $pdo->query("
    SELECT payment_method, COUNT(*) AS cnt
    FROM orders GROUP BY payment_method
")->fetchAll(PDO::FETCH_ASSOC);

// ─── COMMANDES RÉCENTES ───────────────────────────────────────────────────────
$recentOrders = $pdo->query("
    SELECT order_id, status, payment_method, total, shipping, created_at
    FROM orders ORDER BY created_at DESC LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// ─── HELPERS ──────────────────────────────────────────────────────────────────
function statusBadge($s) {
    $map = [
        'pending'   => ['En attente', 'amber'],
        'confirmed' => ['Confirmée',  'plum'],
        'shipped'   => ['Expédiée',   'blue'],
        'delivered' => ['Livrée',     'green'],
        'cancelled' => ['Annulée',    'red'],
    ];
    $d = $map[$s] ?? [$s, 'gray'];
    return "<span class='badge bdg-{$d[1]}'>{$d[0]}</span>";
}
function payLabel($p) {
    $map = ['cash' => '💵 Livraison', 'ccp' => '🏦 CCP', 'baridimob' => '📱 Baridi'];
    return $map[$p] ?? htmlspecialchars($p ?? '—');
}
function stockBadge($stock) {
    if ((int)$stock === 0) return "<span class='badge bdg-red'>Épuisé</span>";
    if ((int)$stock <= 3)  return "<span class='badge bdg-red'>Critique</span>";
    return "<span class='badge bdg-amber'>Faible</span>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>SheGlamour — Dashboard</title>

<!-- Favicon SVG inline via data URI -->
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%23c4697a'/%3E%3Cpath d='M16 4C10 4 5 9 5 15c0 4 2 7 5 9v8h12v-8c3-2 5-5 5-9 0-6-5-11-11-11z' fill='%23f5c6cd'/%3E%3Ccircle cx='16' cy='15' r='5' fill='%23fff'/%3E%3Crect x='12' y='23' width='8' height='2' rx='1' fill='%23fff' opacity='.8'/%3E%3C/svg%3E">

<!-- PWA — Écran d'accueil mobile -->
<link rel="manifest" href="/manifest.json">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="SheGlamour">
<meta name="theme-color" content="#c4697a">
<link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 180'%3E%3Ccircle cx='90' cy='90' r='90' fill='%23c4697a'/%3E%3Cpath d='M90 26C62 26 40 48 40 76c0 16 7 30 19 40v40h62v-40c12-10 19-24 19-40 0-28-22-50-50-50z' fill='%23f5c6cd'/%3E%3Cpath d='M90 50c-14 0-26 12-26 26 0 9 4 17 11 22v35h30v-35c7-5 11-13 11-22 0-14-12-26-26-26z' fill='%23e8899a'/%3E%3Ccircle cx='90' cy='76' r='14' fill='%23fff'/%3E%3Crect x='72' y='130' width='36' height='8' rx='4' fill='%23fff' opacity='.8'/%3E%3C/svg%3E">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

<style>
/* ── TOKENS ──────────────────────────────────────────────────────────────── */
:root {
  --bg: #f9f5f2;
  --surface: #ffffff;
  --surface2: #f4eeea;
  --border: #ede5de;
  --border2: #e0d5cc;
  --text: #16100e;
  --text2: #4a3c36;
  --muted: #9c8d85;
  --muted2: #c0afa6;

  --rose: #c4697a;
  --rose-d: #a8505f;
  --rose-bg: #fdf0f2;
  --rose-lt: #f5d0d7;

  --plum: #8b5a8b;
  --plum-bg: #f5eef5;
  --plum-lt: #dfc8df;

  --green: #3a8a5c;
  --green-bg: #eef7f2;
  --green-lt: #b8dfc9;

  --amber: #b07030;
  --amber-bg: #fdf5eb;
  --amber-lt: #f0d4a8;

  --blue: #3a6db0;
  --blue-bg: #eef3fb;
  --blue-lt: #b8cff0;

  --red: #c0392b;
  --red-bg: #fdf0ee;
  --red-lt: #f0c0bb;

  --sidebar-w: 240px;
  --top-h: 60px;
  --r: 16px;
  --r-sm: 10px;
  --shadow: 0 2px 8px rgba(0,0,0,.06), 0 8px 24px rgba(0,0,0,.05);
  --shadow-sm: 0 1px 4px rgba(0,0,0,.05);
}

/* ── BASE ────────────────────────────────────────────────────────────────── */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior: smooth; }
body {
  background: var(--bg);
  color: var(--text);
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  min-height: 100vh;
  line-height: 1.5;
}

/* ── SIDEBAR ─────────────────────────────────────────────────────────────── */
.sidebar {
  position: fixed; top: 0; left: 0;
  width: var(--sidebar-w); height: 100vh;
  background: var(--surface);
  border-right: 1px solid var(--border);
  display: flex; flex-direction: column;
  z-index: 200;
  transition: transform .3s cubic-bezier(.4,0,.2,1);
  overflow-y: auto;
  overscroll-behavior: contain;
}

.sidebar-logo {
  padding: 28px 24px 24px;
  display: flex; align-items: center; gap: 11px;
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}
.sidebar-logo svg { width: 36px; height: 36px; flex-shrink: 0; }
.sidebar-logo-text {
  font-family: 'Cormorant Garamond', serif;
  font-size: 22px; letter-spacing: -.01em; color: var(--text);
}
.sidebar-logo-text span { color: var(--rose); }

.sidebar-section {
  padding: 20px 20px 8px;
  font-size: 10px; font-weight: 700;
  letter-spacing: .12em; text-transform: uppercase;
  color: var(--muted2);
}

.sidebar-nav { display: flex; flex-direction: column; gap: 2px; padding: 0 12px; flex: 1; }
.nav-link {
  display: flex; align-items: center; gap: 11px;
  padding: 10px 13px; border-radius: var(--r-sm);
  font-size: 13.5px; font-weight: 500; color: var(--muted);
  text-decoration: none; transition: all .15s;
  position: relative;
}
.nav-link:hover { background: var(--surface2); color: var(--text2); }
.nav-link.active { background: var(--rose-bg); color: var(--rose); font-weight: 600; }
.nav-link .nav-ico { font-size: 15px; width: 20px; text-align: center; flex-shrink: 0; }
.nav-badge {
  margin-left: auto;
  background: var(--amber); color: #fff;
  border-radius: 10px; padding: 1px 7px;
  font-size: 10px; font-weight: 700;
}

.sidebar-footer {
  padding: 16px 20px;
  border-top: 1px solid var(--border);
  font-size: 11px; color: var(--muted2);
  display: flex; justify-content: space-between; align-items: center;
  flex-shrink: 0;
}
.logout-link {
  color: var(--muted); text-decoration: none;
  font-weight: 600; font-size: 11px;
  transition: color .15s;
}
.logout-link:hover { color: var(--red); }

/* Overlay quand sidebar ouverte sur mobile */
.sidebar-overlay {
  display: none;
  position: fixed; inset: 0;
  background: rgba(22,16,14,.35);
  z-index: 190;
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
}
.sidebar-overlay.active { display: block; }

/* ── TOPBAR MOBILE ───────────────────────────────────────────────────────── */
.topbar {
  display: none;
  position: fixed; top: 0; left: 0; right: 0;
  height: var(--top-h);
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  align-items: center; justify-content: space-between;
  padding: 0 18px;
  z-index: 180;
  box-shadow: var(--shadow-sm);
}
.topbar-logo {
  display: flex; align-items: center; gap: 9px;
  font-family: 'Cormorant Garamond', serif;
  font-size: 20px; color: var(--text);
}
.topbar-logo svg { width: 30px; height: 30px; }
.topbar-logo span { color: var(--rose); }

.hamburger {
  width: 40px; height: 40px;
  border: 1.5px solid var(--border);
  border-radius: 10px;
  background: var(--surface);
  cursor: pointer;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  gap: 5px; transition: background .15s;
}
.hamburger:hover { background: var(--surface2); }
.hamburger span {
  display: block; width: 18px; height: 1.5px;
  background: var(--text2); border-radius: 2px;
  transition: transform .3s, opacity .3s;
}
.hamburger.open span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
.hamburger.open span:nth-child(2) { opacity: 0; }
.hamburger.open span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }

/* ── MAIN ────────────────────────────────────────────────────────────────── */
.main {
  margin-left: var(--sidebar-w);
  padding: 40px 36px;
  min-height: 100vh;
  max-width: 1400px;
}

/* ── PAGE HEADER ─────────────────────────────────────────────────────────── */
.page-header {
  display: flex; align-items: flex-end; justify-content: space-between;
  margin-bottom: 32px; gap: 16px; flex-wrap: wrap;
}
.page-header h1 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 36px; letter-spacing: -.02em; color: var(--text);
  line-height: 1;
}
.page-header p { color: var(--muted); font-size: 13px; margin-top: 5px; }
.date-chip {
  background: var(--rose-bg); color: var(--rose);
  border: 1px solid var(--rose-lt);
  padding: 7px 16px; border-radius: 24px;
  font-size: 12px; font-weight: 600; white-space: nowrap;
}

/* ── KPI GRID ────────────────────────────────────────────────────────────── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px; margin-bottom: 14px;
}
.kpi-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 20px 22px 18px;
  position: relative; overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: box-shadow .2s, transform .2s;
}
.kpi-card:hover { box-shadow: var(--shadow); transform: translateY(-2px); }
.kpi-accent {
  position: absolute; top: 0; left: 0;
  width: 3px; height: 100%;
  border-radius: 16px 0 0 16px;
}
.kpi-label {
  font-size: 10.5px; font-weight: 700;
  letter-spacing: .1em; text-transform: uppercase;
  color: var(--muted); margin-bottom: 9px;
}
.kpi-value {
  font-size: 26px; font-weight: 700;
  letter-spacing: -.03em; color: var(--text); line-height: 1;
}
.kpi-value small { font-size: 13px; font-weight: 500; color: var(--muted); margin-left: 3px; }
.kpi-sub { font-size: 11px; color: var(--muted2); margin-top: 6px; }
.kpi-icon {
  position: absolute; top: 16px; right: 16px;
  font-size: 26px; opacity: .15; pointer-events: none;
}

/* ── CARDS ───────────────────────────────────────────────────────────────── */
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.card-head {
  padding: 16px 22px;
  display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid var(--border);
  background: var(--surface2); gap: 12px; flex-wrap: wrap;
}
.card-title {
  font-size: 10.5px; font-weight: 800;
  letter-spacing: .1em; text-transform: uppercase; color: var(--muted);
}
.card-body { padding: 20px 22px; }

/* ── BADGES ──────────────────────────────────────────────────────────────── */
.badge {
  display: inline-flex; align-items: center;
  padding: 3px 11px; border-radius: 20px;
  font-size: 11px; font-weight: 700; letter-spacing: .03em;
}
.bdg-amber  { background: var(--amber-bg);  color: var(--amber);  border: 1px solid var(--amber-lt); }
.bdg-plum   { background: var(--plum-bg);   color: var(--plum);   border: 1px solid var(--plum-lt); }
.bdg-blue   { background: var(--blue-bg);   color: var(--blue);   border: 1px solid var(--blue-lt); }
.bdg-green  { background: var(--green-bg);  color: var(--green);  border: 1px solid var(--green-lt); }
.bdg-red    { background: var(--red-bg);    color: var(--red);    border: 1px solid var(--red-lt); }
.bdg-gray   { background: var(--surface2);  color: var(--muted);  border: 1px solid var(--border); }

/* ── GRIDS ───────────────────────────────────────────────────────────────── */
.grid-2-1 { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; margin-bottom: 18px; }
.grid-1-1 { display: grid; grid-template-columns: 1fr 1fr;   gap: 18px; margin-bottom: 18px; }
.mb-18 { margin-bottom: 18px; }

/* ── STATUS BAR ──────────────────────────────────────────────────────────── */
.status-bar {
  display: flex; height: 9px;
  border-radius: 5px; overflow: hidden;
  border: 1px solid var(--border); margin: 8px 0 14px;
}
.status-seg { height: 100%; }
.status-legend { display: flex; gap: 14px; flex-wrap: wrap; }
.status-item {
  display: flex; align-items: center; gap: 7px;
  font-size: 12.5px; font-weight: 500; color: var(--text2);
}
.status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

/* ── MINI BAR ────────────────────────────────────────────────────────────── */
.mini-bar { margin-bottom: 13px; }
.mini-bar:last-child { margin-bottom: 0; }
.mini-bar-top {
  display: flex; justify-content: space-between;
  font-size: 13px; font-weight: 600; color: var(--text2); margin-bottom: 6px;
}
.mini-bar-top span:last-child { color: var(--muted); font-weight: 500; }
.mini-bar-track {
  height: 7px; background: var(--surface2);
  border-radius: 4px; overflow: hidden; border: 1px solid var(--border);
}
.mini-bar-fill {
  height: 100%; border-radius: 4px;
  background: linear-gradient(90deg, var(--rose), var(--plum));
  transition: width 1.2s cubic-bezier(.4,0,.2,1);
}

/* ── CHART ───────────────────────────────────────────────────────────────── */
.chart-wrap { height: 220px; position: relative; padding-top: 10px; }
.chart-wrap-sm { height: 160px; position: relative; }

/* ── PRODUCT LIST ────────────────────────────────────────────────────────── */
.prod-row {
  display: flex; align-items: center; gap: 13px;
  padding: 11px 0; border-bottom: 1px solid var(--border);
}
.prod-row:last-child { border-bottom: none; }
.prod-rank {
  width: 28px; height: 28px; border-radius: 50%;
  background: var(--surface2); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 800; color: var(--muted); flex-shrink: 0;
}
.prod-rank.gold { background: var(--amber-bg); border-color: var(--amber-lt); color: var(--amber); }
.prod-info { flex: 1; min-width: 0; }
.prod-name { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.prod-qty { font-size: 11px; color: var(--muted); margin-top: 2px; }
.prod-rev { font-size: 13px; font-weight: 800; color: var(--rose); white-space: nowrap; }

/* ── SHADE LIST ──────────────────────────────────────────────────────────── */
.swatch { display: inline-block; width: 15px; height: 15px; border-radius: 50%; border: 2px solid var(--border); flex-shrink: 0; }
.shade-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--border); }
.shade-row:last-child { border-bottom: none; }
.shade-info { flex: 1; min-width: 0; }
.shade-name { font-size: 13px; font-weight: 700; }
.shade-prod { font-size: 11px; color: var(--muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.shade-qty { font-size: 13px; font-weight: 800; color: var(--plum); white-space: nowrap; }

/* ── STOCK LIST ──────────────────────────────────────────────────────────── */
.stock-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--border); }
.stock-row:last-child { border-bottom: none; }
.stock-info { flex: 1; min-width: 0; }
.stock-name { font-size: 13px; font-weight: 700; }
.stock-prod { font-size: 11px; color: var(--muted); margin-top: 2px; }
.stock-qty { font-size: 18px; font-weight: 800; color: var(--red); }

/* ── STAT PILLS ──────────────────────────────────────────────────────────── */
.pills { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
.pill {
  flex: 1; min-width: 72px;
  background: var(--surface2); border: 1px solid var(--border);
  border-radius: var(--r-sm); padding: 13px 10px; text-align: center;
}
.pill-val { font-size: 20px; font-weight: 800; color: var(--text); }
.pill-lbl { font-size: 10px; color: var(--muted); margin-top: 3px; letter-spacing: .06em; text-transform: uppercase; font-weight: 700; }

/* ── TABLE ───────────────────────────────────────────────────────────────── */
.tbl-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.tbl {
  width: 100%; border-collapse: collapse; min-width: 600px;
}
.tbl th {
  font-size: 10px; font-weight: 800; letter-spacing: .1em;
  text-transform: uppercase; color: var(--muted);
  padding: 0 12px 12px 0; text-align: left;
  border-bottom: 2px solid var(--border); white-space: nowrap;
}
.tbl td {
  padding: 12px 12px 12px 0; border-bottom: 1px solid var(--border);
  font-size: 13px; color: var(--text2); vertical-align: middle;
}
.tbl tr:last-child td { border-bottom: none; }
.tbl tr:hover td { background: var(--surface2); }
.tbl td:last-child, .tbl th:last-child { text-align: right; padding-right: 0; }
.oid {
  font-family: 'Courier New', monospace; font-size: 10.5px;
  color: var(--rose); font-weight: 700;
  background: var(--rose-bg); padding: 2px 6px;
  border-radius: 5px; border: 1px solid var(--rose-lt);
}

/* ── BUTTONS ─────────────────────────────────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 16px; border-radius: 8px;
  font-size: 12px; font-weight: 700; font-family: 'DM Sans', sans-serif;
  cursor: pointer; transition: all .15s; border: none; text-decoration: none;
}
.btn-rose { background: var(--rose); color: #fff; }
.btn-rose:hover { background: var(--rose-d); }
.btn-sm { padding: 5px 12px; font-size: 11px; border-radius: 7px; }

.empty { color: var(--muted); font-size: 13px; text-align: center; padding: 22px 0; }

/* ── MOBILE CARDS (commandes) ────────────────────────────────────────────── */
.mobile-orders { display: none; }
.m-order-card {
  border: 1px solid var(--border);
  border-radius: 12px; padding: 14px 16px;
  margin-bottom: 10px; background: var(--surface);
}
.m-order-top {
  display: flex; justify-content: space-between;
  align-items: flex-start; margin-bottom: 8px;
}
.m-order-name { font-size: 14px; font-weight: 700; color: var(--text); }
.m-order-id { font-family: 'Courier New', monospace; font-size: 10px; color: var(--rose); font-weight: 700; }
.m-order-row { display: flex; justify-content: space-between; font-size: 12px; color: var(--muted); margin-top: 4px; }
.m-order-total { font-size: 14px; font-weight: 800; color: var(--rose); }

/* ── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media (max-width: 900px) {
  .sidebar {
    transform: translateX(calc(-1 * var(--sidebar-w)));
    box-shadow: 4px 0 24px rgba(0,0,0,.12);
  }
  .sidebar.open { transform: translateX(0); }

  .topbar { display: flex; }

  .main {
    margin-left: 0;
    padding: calc(var(--top-h) + 20px) 16px 32px;
  }

  .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .kpi-card { padding: 16px 16px 14px; }
  .kpi-value { font-size: 22px; }
  .kpi-icon { display: none; }

  .grid-2-1, .grid-1-1 { grid-template-columns: 1fr; gap: 14px; }
  .page-header h1 { font-size: 28px; }
  .date-chip { font-size: 11px; padding: 6px 12px; }

  /* table → mobile cards */
  .desktop-tbl { display: none; }
  .mobile-orders { display: block; }
}

@media (max-width: 480px) {
  .kpi-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
  .kpi-value { font-size: 20px; }
  .kpi-label { font-size: 9.5px; }
  .main { padding-left: 12px; padding-right: 12px; }
  .page-header { flex-direction: column; align-items: flex-start; }
}

/* ── ANIMATION ───────────────────────────────────────────────────────────── */
@keyframes fadeUp {
  from { opacity:0; transform: translateY(12px); }
  to   { opacity:1; transform: translateY(0); }
}
.kpi-card, .card { animation: fadeUp .4s both; }
.kpi-card:nth-child(1) { animation-delay: .04s; }
.kpi-card:nth-child(2) { animation-delay: .08s; }
.kpi-card:nth-child(3) { animation-delay: .12s; }
.kpi-card:nth-child(4) { animation-delay: .16s; }
.kpi-card:nth-child(5) { animation-delay: .20s; }
.kpi-card:nth-child(6) { animation-delay: .24s; }
</style>
</head>
<body>

<!-- ── TOPBAR MOBILE ──────────────────────────────────────────────────────── -->
<header class="topbar" id="topbar">
  <div class="topbar-logo">
    <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="16" cy="16" r="16" fill="#fdf0f2"/>
      <path d="M16 5C10.477 5 6 9.477 6 15c0 3.09 1.39 5.863 3.6 7.744V26h12.8v-3.256C24.61 20.863 26 18.09 26 15c0-5.523-4.477-10-10-10z" fill="#f5c6cd"/>
      <path d="M16 8c-3.866 0-7 3.134-7 7 0 2.256 1.066 4.261 2.728 5.553V24h8.544v-3.447C21.934 19.261 23 17.256 23 15c0-3.866-3.134-7-7-7z" fill="#e8899a"/>
      <circle cx="16" cy="15" r="4.5" fill="#c4697a"/>
      <ellipse cx="14.5" cy="13.5" rx="1.2" ry="1.2" fill="#fff" opacity=".5"/>
      <rect x="13" y="24" width="6" height="1.8" rx=".9" fill="#c4697a"/>
      <rect x="14" y="25.8" width="4" height="1.4" rx=".7" fill="#a8505f"/>
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
    <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="18" cy="18" r="18" fill="#fdf0f2"/>
      <path d="M18 6C12.477 6 8 10.477 8 16c0 3.09 1.39 5.863 3.6 7.744V28h12.8v-4.256C26.61 21.863 28 19.09 28 16 28 10.477 23.523 6 18 6z" fill="#f5c6cd"/>
      <path d="M18 10c-3.314 0-6 2.686-6 6 0 2.032.997 3.836 2.537 4.96V27h6.926v-6.04C23.003 19.836 24 18.032 24 16c0-3.314-2.686-6-6-6z" fill="#e8899a"/>
      <circle cx="18" cy="16" r="3.8" fill="#c4697a"/>
      <ellipse cx="16.8" cy="14.8" rx="1" ry="1" fill="#fff" opacity=".55"/>
      <rect x="15" y="27" width="6" height="1.8" rx=".9" fill="#c4697a"/>
      <rect x="16" y="28.8" width="4" height="1.4" rx=".7" fill="#a8505f"/>
    </svg>
    <span class="sidebar-logo-text">She<span>Glamour</span></span>
  </div>

  <p class="sidebar-section">Navigation</p>
  <nav class="sidebar-nav">
    <a class="nav-link active" href="dashboard.php">
      <span class="nav-ico">◈</span> Tableau de bord
    </a>
    <a class="nav-link" href="admin_orders.php">
      <span class="nav-ico">📦</span> Commandes
      <?php if ($kpis['pending'] > 0): ?>
        <span class="nav-badge"><?= $kpis['pending'] ?></span>
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
    <span>v3.0</span>
    <a href="dashboard.php?logout=1" class="logout-link">Déconnexion</a>
  </div>
</aside>

<!-- ── MAIN ───────────────────────────────────────────────────────────────── -->
<main class="main">

  <!-- En-tête -->
  <div class="page-header">
    <div>
      <h1>Tableau de bord</h1>
      <p>Vue d'ensemble en temps réel</p>
    </div>
    <span class="date-chip">📅 <?= date('d F Y, H:i') ?></span>
  </div>

  <!-- KPI ligne 1 -->
  <div class="kpi-grid">
    <div class="kpi-card">
      <div class="kpi-accent" style="background:var(--rose)"></div>
      <div class="kpi-label">CA Total</div>
      <div class="kpi-value"><?= number_format($kpis['revenue'],0,',',' ') ?><small>DA</small></div>
      <div class="kpi-sub">Hors annulées</div>
      <div class="kpi-icon">💰</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-accent" style="background:var(--plum)"></div>
      <div class="kpi-label">CA ce mois</div>
      <div class="kpi-value"><?= number_format($kpis['revenue_month'],0,',',' ') ?><small>DA</small></div>
      <div class="kpi-sub"><?= date('F Y') ?></div>
      <div class="kpi-icon">📅</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-accent" style="background:var(--green)"></div>
      <div class="kpi-label">Taux livraison</div>
      <div class="kpi-value"><?= $kpis['conversion'] ?><small>%</small></div>
      <div class="kpi-sub">Livrées / total</div>
      <div class="kpi-icon">✅</div>
    </div>
  </div>

  <!-- KPI ligne 2 -->
  <div class="kpi-grid mb-18">
    <div class="kpi-card">
      <div class="kpi-accent" style="background:var(--blue)"></div>
      <div class="kpi-label">Commandes totales</div>
      <div class="kpi-value"><?= $kpis['orders'] ?></div>
      <div class="kpi-sub">Tous statuts</div>
      <div class="kpi-icon">📦</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-accent" style="background:var(--amber)"></div>
      <div class="kpi-label">En attente</div>
      <div class="kpi-value"><?= $kpis['pending'] ?></div>
      <div class="kpi-sub">À traiter</div>
      <div class="kpi-icon">⏳</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-accent" style="background:var(--rose)"></div>
      <div class="kpi-label">Aujourd'hui</div>
      <div class="kpi-value"><?= $kpis['today_orders'] ?></div>
      <div class="kpi-sub">Nouvelles commandes</div>
      <div class="kpi-icon">🛍</div>
    </div>
  </div>

  <!-- Répartition statuts -->
  <?php
    $stColors = ['pending'=>'#b07030','confirmed'=>'#8b5a8b','shipped'=>'#3a6db0','delivered'=>'#3a8a5c','cancelled'=>'#c0392b'];
    $stLabels = ['pending'=>'En attente','confirmed'=>'Confirmée','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];
  ?>
  <?php if ($totalOrders > 0): ?>
  <div class="card mb-18">
    <div class="card-head">
      <span class="card-title">Répartition des statuts</span>
      <span style="font-size:12px;color:var(--muted);font-weight:600"><?= $totalOrders ?> commandes</span>
    </div>
    <div class="card-body">
      <div class="status-bar">
        <?php foreach ($stColors as $key => $color):
          $cnt = $statusCounts[$key] ?? 0; if (!$cnt) continue;
          $pct = round($cnt / $totalOrders * 100, 1); ?>
          <div class="status-seg" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
        <?php endforeach; ?>
      </div>
      <div class="status-legend">
        <?php foreach ($stColors as $key => $color):
          $cnt = $statusCounts[$key] ?? 0; if (!$cnt) continue;
          $pct = round($cnt / $totalOrders * 100, 1); ?>
          <div class="status-item">
            <span class="status-dot" style="background:<?= $color ?>"></span>
            <?= $stLabels[$key] ?> — <strong style="color:var(--text)"><?= $cnt ?></strong>&nbsp;(<?= $pct ?>%)
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Graphique + Wilayas -->
  <div class="grid-2-1">
    <div class="card">
      <div class="card-head">
        <span class="card-title">CA — 30 derniers jours</span>
        <span style="font-size:13px;font-weight:800;color:var(--rose)"><?= number_format($kpis['revenue_month'],0,',',' ') ?> DA</span>
      </div>
      <div class="card-body">
        <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
      </div>
    </div>
    <div class="card">
      <div class="card-head"><span class="card-title">Top Wilayas</span></div>
      <div class="card-body">
        <?php $maxW = $wilayaData ? max(array_column($wilayaData,'cnt')) : 1;
          foreach ($wilayaData as $w): $pct = round($w['cnt'] / $maxW * 100); ?>
        <div class="mini-bar">
          <div class="mini-bar-top">
            <span><?= htmlspecialchars($w['wilaya'] ?? '—') ?></span>
            <span><?= $w['cnt'] ?> cmd</span>
          </div>
          <div class="mini-bar-track"><div class="mini-bar-fill" style="width:<?= $pct ?>%"></div></div>
        </div>
        <?php endforeach; ?>
        <?php if (!$wilayaData): ?><p class="empty">Aucune donnée</p><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Top teintes + Stock critique -->
  <div class="grid-1-1">
    <div class="card">
      <div class="card-head">
        <span class="card-title">🎨 Top Teintes</span>
        <span style="font-size:11px;color:var(--muted)">par quantité</span>
      </div>
      <div class="card-body">
        <?php if ($topShades): foreach ($topShades as $s): ?>
        <div class="shade-row">
          <span class="swatch" style="background:<?= htmlspecialchars($s['code_couleur'] ?? '#ccc') ?>"></span>
          <div class="shade-info">
            <div class="shade-name"><?= htmlspecialchars($s['shade']) ?></div>
            <div class="shade-prod"><?= htmlspecialchars($s['product_name']) ?></div>
          </div>
          <div style="text-align:right">
            <div class="shade-qty"><?= $s['qty'] ?> vendus</div>
            <div style="font-size:11px;color:var(--muted)"><?= number_format($s['revenue'],0,',',' ') ?> DA</div>
          </div>
        </div>
        <?php endforeach; else: ?>
          <p class="empty">Aucune vente de teinte</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <span class="card-title">⚠️ Stock critique</span>
        <span style="font-size:11px;color:var(--red);font-weight:700">≤ 5 unités</span>
      </div>
      <div class="card-body">
        <?php if ($lowStockShades): foreach ($lowStockShades as $s): ?>
        <div class="stock-row">
          <span class="swatch" style="background:<?= htmlspecialchars($s['code_couleur'] ?? '#ccc') ?>"></span>
          <div class="stock-info">
            <div class="stock-name"><?= htmlspecialchars($s['nom_teinte']) ?></div>
            <div class="stock-prod">
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
          <p class="empty" style="color:var(--green)">✅ Tous les stocks sont OK</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Top produits + Paiements -->
  <div class="grid-2-1">
    <div class="card">
      <div class="card-head">
        <span class="card-title">Top Produits</span>
        <span style="font-size:11px;color:var(--muted)">par revenue</span>
      </div>
      <div class="card-body">
        <?php foreach ($topProducts as $i => $p): ?>
        <div class="prod-row">
          <div class="prod-rank <?= $i === 0 ? 'gold' : '' ?>"><?= $i + 1 ?></div>
          <div class="prod-info">
            <div class="prod-name">
              <?= htmlspecialchars($p['name']) ?>
              <?php if (!empty($p['shade'])): ?>
                <span style="font-size:10.5px;color:var(--plum);font-weight:600;background:var(--plum-bg);padding:1px 6px;border-radius:10px;margin-left:4px"><?= htmlspecialchars($p['shade']) ?></span>
              <?php endif; ?>
            </div>
            <div class="prod-qty"><?= $p['qty'] ?> unités</div>
          </div>
          <div class="prod-rev"><?= number_format($p['revenue'],0,',',' ') ?> DA</div>
        </div>
        <?php endforeach; ?>
        <?php if (!$topProducts): ?><p class="empty">Aucune vente</p><?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><span class="card-title">Paiements</span></div>
      <div class="card-body">
        <div class="chart-wrap-sm"><canvas id="paymentChart"></canvas></div>
        <div class="pills">
          <?php foreach ($paymentData as $pm): ?>
          <div class="pill">
            <div class="pill-val"><?= $pm['cnt'] ?></div>
            <div class="pill-lbl"><?= payLabel($pm['payment_method']) ?></div>
          </div>
          <?php endforeach; ?>
          <?php if (!$paymentData): ?><p class="empty" style="width:100%">Aucune donnée</p><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Commandes récentes -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">Commandes récentes</span>
      <a href="admin_orders.php" class="btn btn-rose btn-sm">Voir toutes →</a>
    </div>

    <!-- Desktop table -->
    <div class="card-body desktop-tbl" style="padding:0 22px">
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>ID</th><th>Client</th><th>Wilaya</th>
              <th>Paiement</th><th>Statut</th><th>Date</th><th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentOrders as $o):
              $sh     = is_array($o['shipping']) ? $o['shipping'] : (json_decode($o['shipping'] ?? '{}', true) ?: []);
              $prenom = $sh['prenom'] ?? ($sh['firstName'] ?? '—');
              $nom    = $sh['nom']    ?? ($sh['lastName']  ?? '');
              $wilaya = $sh['wilaya'] ?? '—';
            ?>
            <tr>
              <td><span class="oid"><?= htmlspecialchars($o['order_id']) ?></span></td>
              <td style="font-weight:700;color:var(--text)"><?= htmlspecialchars(trim($prenom . ' ' . $nom)) ?></td>
              <td style="color:var(--muted);font-size:12px"><?= htmlspecialchars($wilaya) ?></td>
              <td><?= payLabel($o['payment_method']) ?></td>
              <td><?= statusBadge($o['status']) ?></td>
              <td style="color:var(--muted);font-size:12px"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
              <td style="font-weight:800;color:var(--rose)"><?= number_format($o['total'],2,',',' ') ?> DA</td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$recentOrders): ?>
              <tr><td colspan="7" class="empty">Aucune commande</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile cards -->
    <div class="card-body mobile-orders">
      <?php foreach ($recentOrders as $o):
        $sh     = is_array($o['shipping']) ? $o['shipping'] : (json_decode($o['shipping'] ?? '{}', true) ?: []);
        $prenom = $sh['prenom'] ?? ($sh['firstName'] ?? '—');
        $nom    = $sh['nom']    ?? ($sh['lastName']  ?? '');
        $wilaya = $sh['wilaya'] ?? '—';
      ?>
      <div class="m-order-card">
        <div class="m-order-top">
          <div>
            <div class="m-order-name"><?= htmlspecialchars(trim($prenom . ' ' . $nom)) ?></div>
            <div class="m-order-id"><?= htmlspecialchars($o['order_id']) ?></div>
          </div>
          <?= statusBadge($o['status']) ?>
        </div>
        <div class="m-order-row">
          <span><?= htmlspecialchars($wilaya) ?> · <?= payLabel($o['payment_method']) ?></span>
          <span class="m-order-total"><?= number_format($o['total'],2,',',' ') ?> DA</span>
        </div>
        <div class="m-order-row" style="margin-top:4px">
          <span><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (!$recentOrders): ?><p class="empty">Aucune commande</p><?php endif; ?>
    </div>
  </div>

</main>

<script>
// ── Charts ────────────────────────────────────────────────────────────────────
Chart.defaults.color = '#9c8d85';
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.font.size = 11;

const chartDays   = <?= json_encode(array_map(fn($r) => (string)$r['day'],   $chartData)) ?>;
const chartTotals = <?= json_encode(array_map(fn($r) => (float)$r['total'],  $chartData)) ?>;

new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: chartDays,
    datasets: [{
      label: 'CA (DA)', data: chartTotals,
      borderColor: '#c4697a',
      backgroundColor: 'rgba(196,105,122,.08)',
      borderWidth: 2.5, pointRadius: 3,
      pointBackgroundColor: '#c4697a',
      pointBorderColor: '#fff', pointBorderWidth: 2,
      tension: .4, fill: true,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(0,0,0,.04)' }, ticks: { maxTicksLimit: 7 } },
      y: { grid: { color: 'rgba(0,0,0,.04)' }, ticks: { callback: v => v.toLocaleString('fr') } }
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
      datasets: [{
        data: pmCounts,
        backgroundColor: ['#c4697a','#8b5a8b','#3a6db0'],
        borderWidth: 3, borderColor: '#fff', hoverOffset: 8
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '68%',
      plugins: {
        legend: { position: 'bottom', labels: { padding: 12, boxWidth: 10, borderRadius: 4 } }
      }
    }
  });
}

// ── Sidebar mobile toggle ─────────────────────────────────────────────────────
const sidebar   = document.getElementById('sidebar');
const hamburger = document.getElementById('hamburger');
const overlay   = document.getElementById('overlay');

function openSidebar() {
  sidebar.classList.add('open');
  overlay.classList.add('active');
  hamburger.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeSidebar() {
  sidebar.classList.remove('open');
  overlay.classList.remove('active');
  hamburger.classList.remove('open');
  document.body.style.overflow = '';
}

hamburger.addEventListener('click', () => {
  sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
});
overlay.addEventListener('click', closeSidebar);

// Fermer la sidebar si on clique sur un lien nav (mobile)
sidebar.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', () => {
    if (window.innerWidth <= 900) closeSidebar();
  });
});
</script>
</body>
</html>