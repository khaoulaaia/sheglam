<?php
// ============================================
//  SheGlamour — Caisse & Bénéfices v1.0
//  Calcul net + calculateur prix produit
// ============================================
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/config.php';
$b = defined('BASE_URL') ? BASE_URL : '';

// ─── CA LIVRÉ (commandes delivered) ──────────────────
$r = $pdo->query("
    SELECT COALESCE(SUM(total), 0)
    FROM orders
    WHERE status = 'delivered'
");
$caLivre = (float) $r->fetchColumn();

// ─── COMMANDES LIVRÉES COUNT ──────────────────────────
$r = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'");
$nbLivre = (int) $r->fetchColumn();

// ─── CA PAR MOIS (livrées) ───────────────────────────
$monthlyCA = $pdo->query("
    SELECT
        TO_CHAR(created_at, 'YYYY-MM') AS mois,
        SUM(total) AS total,
        COUNT(*) AS cnt
    FROM orders
    WHERE status = 'delivered'
    GROUP BY mois
    ORDER BY mois DESC
    LIMIT 12
")->fetchAll(PDO::FETCH_ASSOC);

// ─── DÉPENSES ENREGISTRÉES ────────────────────────────
// Table expenses : id, label, amount, category, created_at
// Créée si inexistante
$pdo->exec("
    CREATE TABLE IF NOT EXISTS expenses (
        id          SERIAL PRIMARY KEY,
        label       TEXT NOT NULL,
        amount      NUMERIC(12,2) NOT NULL,
        category    VARCHAR(80) DEFAULT 'Autre',
        note        TEXT,
        created_at  TIMESTAMP DEFAULT NOW()
    )
");

// Action : ajout dépense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_expense') {
    $label    = trim($_POST['label']    ?? '');
    $amount   = (float) ($_POST['amount'] ?? 0);
    $category = trim($_POST['category'] ?? 'Autre');
    $note     = trim($_POST['note']     ?? '');
    if ($label && $amount > 0) {
        $stmt = $pdo->prepare("INSERT INTO expenses (label, amount, category, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([$label, $amount, $category, $note]);
    }
    header('Location: admin_caisse.php');
    exit;
}

// Action : suppression dépense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_expense') {
    $id = (int) ($_POST['expense_id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("DELETE FROM expenses WHERE id = ?")->execute([$id]);
    }
    header('Location: admin_caisse.php');
    exit;
}

// Fetch dépenses
$expenses = $pdo->query("
    SELECT * FROM expenses ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$totalDepenses = array_sum(array_column($expenses, 'amount'));
$beneficeNet   = $caLivre - $totalDepenses;

// ─── CATÉGORIES DÉPENSES ──────────────────────────────
$categories = ['Stock / Réapprovisionnement', 'Livraison / Transport', 'Emballage', 'Marketing / Pub', 'Frais fixes', 'Autre'];

// ─── HELPERS ──────────────────────────────────────────
function fda(float $n): string {
    return number_format($n, 2, ',', ' ') . ' DA';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>SheGlamour — Caisse</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%23c4697a'/%3E%3C/svg%3E">

<style>
/* ── TOKENS ──────────────────────────────────────────────────────────────── */
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
.sidebar-footer { padding:16px 20px; border-top:1px solid var(--border); font-size:11px; color:var(--muted2); display:flex; justify-content:space-between; align-items:center; flex-shrink:0; }
.logout-link { color:var(--muted); text-decoration:none; font-weight:600; font-size:11px; transition:color .15s; }
.logout-link:hover { color:var(--red); }
.sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(22,16,14,.35); z-index:190; backdrop-filter:blur(2px); }
.sidebar-overlay.active { display:block; }

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

/* ── MAIN ────────────────────────────────────────────────────────────────── */
.main { margin-left:var(--sidebar-w); padding:40px 36px; min-height:100vh; max-width:1400px; }
.page-header { display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:32px; gap:16px; flex-wrap:wrap; }
.page-header h1 { font-family:'Cormorant Garamond',serif; font-size:36px; letter-spacing:-.02em; color:var(--text); line-height:1; }
.page-header p { color:var(--muted); font-size:13px; margin-top:5px; }

/* ── KPIs BÉNÉFICE ───────────────────────────────────────────────────────── */
.kpi-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px; }
.kpi-card {
  background:var(--surface); border:1px solid var(--border);
  border-radius:var(--r); padding:22px 24px 20px;
  position:relative; overflow:hidden;
  box-shadow:var(--shadow-sm);
  transition:box-shadow .2s, transform .2s;
}
.kpi-card:hover { box-shadow:var(--shadow); transform:translateY(-2px); }
.kpi-accent { position:absolute; top:0; left:0; width:3px; height:100%; border-radius:16px 0 0 16px; }
.kpi-label { font-size:10.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:10px; }
.kpi-value { font-size:26px; font-weight:800; letter-spacing:-.03em; color:var(--text); line-height:1; }
.kpi-value small { font-size:13px; font-weight:500; color:var(--muted); margin-left:3px; }
.kpi-sub { font-size:11px; color:var(--muted2); margin-top:7px; }
.kpi-icon { position:absolute; top:18px; right:18px; font-size:28px; opacity:.13; pointer-events:none; }

/* Carte bénéfice — couleur dynamique */
.kpi-benef-pos .kpi-value { color:var(--green); }
.kpi-benef-neg .kpi-value { color:var(--red); }
.kpi-benef-pos { border-color:var(--green-lt); background:var(--green-bg); }
.kpi-benef-neg { border-color:var(--red-lt);   background:var(--red-bg); }
.kpi-benef-pos .kpi-accent { background:var(--green); }
.kpi-benef-neg .kpi-accent { background:var(--red); }

/* ── LAYOUT 2 COLONNES ───────────────────────────────────────────────────── */
.grid-2-1 { display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:24px; }
.mb-24 { margin-bottom:24px; }

/* ── CARDS ───────────────────────────────────────────────────────────────── */
.card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; box-shadow:var(--shadow-sm); }
.card-head { padding:16px 22px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--border); background:var(--surface2); gap:12px; flex-wrap:wrap; }
.card-title { font-size:10.5px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); }
.card-body { padding:22px; }

/* ── FORMULAIRE DÉPENSE ──────────────────────────────────────────────────── */
.expense-form { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.form-field label { display:block; font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.form-field input,
.form-field select,
.form-field textarea {
  width:100%; border:1.5px solid var(--border); border-radius:var(--r-sm);
  padding:10px 14px; font-family:'DM Sans',sans-serif; font-size:13.5px;
  color:var(--text); background:var(--surface); outline:none;
  transition:border-color .15s;
}
.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus { border-color:var(--rose); }
.form-field.full { grid-column:1/-1; }
.form-field textarea { resize:vertical; min-height:60px; }
.btn-add {
  grid-column:1/-1; padding:12px;
  background:var(--rose); color:#fff; border:none;
  border-radius:var(--r-sm); font-family:'DM Sans',sans-serif;
  font-size:13px; font-weight:700; cursor:pointer;
  transition:background .15s;
}
.btn-add:hover { background:var(--rose-d); }

/* ── LISTE DÉPENSES ──────────────────────────────────────────────────────── */
.expense-row { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid var(--border); }
.expense-row:last-child { border-bottom:none; }
.expense-cat-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
.expense-info { flex:1; min-width:0; }
.expense-label { font-size:13.5px; font-weight:700; color:var(--text); }
.expense-meta { font-size:11px; color:var(--muted); margin-top:2px; }
.expense-amount { font-size:14px; font-weight:800; color:var(--red); white-space:nowrap; }
.btn-delete {
  width:28px; height:28px; border-radius:8px;
  background:var(--red-bg); border:1px solid var(--red-lt);
  color:var(--red); font-size:14px; cursor:pointer;
  display:flex; align-items:center; justify-content:center;
  transition:background .15s; flex-shrink:0;
}
.btn-delete:hover { background:var(--red); color:#fff; }

/* Résumé par catégorie */
.cat-summary { display:flex; flex-direction:column; gap:10px; }
.cat-row { display:flex; align-items:center; justify-content:space-between; gap:10px; }
.cat-row-left { display:flex; align-items:center; gap:8px; font-size:12.5px; font-weight:600; color:var(--text2); }
.cat-row-right { font-size:13px; font-weight:800; color:var(--red); }
.cat-bar-wrap { height:5px; background:var(--surface2); border-radius:3px; margin-top:4px; overflow:hidden; border:1px solid var(--border); }
.cat-bar-fill { height:100%; border-radius:3px; background:var(--rose); }

/* ── CALCULATEUR PRIX ────────────────────────────────────────────────────── */
.calc-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.calc-field label { display:block; font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.calc-field input {
  width:100%; border:1.5px solid var(--border); border-radius:var(--r-sm);
  padding:11px 14px; font-family:'DM Sans',sans-serif; font-size:16px;
  font-weight:700; color:var(--text); background:var(--surface);
  outline:none; transition:border-color .15s;
}
.calc-field input:focus { border-color:var(--rose); }
.calc-field.full { grid-column:1/-1; }

.calc-results {
  margin-top:18px; background:var(--surface2);
  border:1px solid var(--border); border-radius:var(--r-sm);
  overflow:hidden;
}
.calc-result-row {
  display:flex; align-items:center; justify-content:space-between;
  padding:12px 16px; border-bottom:1px solid var(--border); gap:10px;
}
.calc-result-row:last-child { border-bottom:none; }
.calc-result-label { font-size:12px; color:var(--muted); font-weight:600; }
.calc-result-val { font-size:15px; font-weight:800; color:var(--text); }
.calc-result-val.highlight { color:var(--rose); font-size:17px; }
.calc-result-val.green { color:var(--green); }
.calc-result-val.amber { color:var(--amber); }
.calc-result-val.red   { color:var(--red); }

.margin-badge {
  display:inline-block; padding:3px 10px; border-radius:20px;
  font-size:11px; font-weight:800;
}

/* Presets marges */
.margin-presets { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
.preset-btn {
  padding:6px 14px; border-radius:20px; border:1.5px solid var(--border);
  background:var(--surface); color:var(--text2); font-family:'DM Sans',sans-serif;
  font-size:12px; font-weight:700; cursor:pointer; transition:all .15s;
}
.preset-btn:hover { border-color:var(--rose); color:var(--rose); background:var(--rose-bg); }
.preset-btn.active { border-color:var(--rose); color:var(--rose); background:var(--rose-bg); }

/* ── TABLE MENSUELLE ─────────────────────────────────────────────────────── */
.tbl-wrap { overflow-x:auto; }
.tbl { width:100%; border-collapse:collapse; }
.tbl th { font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); padding:0 0 12px; text-align:left; border-bottom:2px solid var(--border); white-space:nowrap; }
.tbl td { padding:12px 0; border-bottom:1px solid var(--border); font-size:13px; color:var(--text2); vertical-align:middle; }
.tbl tr:last-child td { border-bottom:none; }
.tbl tbody tr:hover td { background:var(--surface2); }
.tbl td:last-child, .tbl th:last-child { text-align:right; }

/* ── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media (max-width:1100px) {
  .grid-2-1 { grid-template-columns:1fr; }
}
@media (max-width:900px) {
  .sidebar { transform:translateX(calc(-1 * var(--sidebar-w))); box-shadow:4px 0 24px rgba(0,0,0,.12); }
  .sidebar.open { transform:translateX(0); }
  .topbar { display:flex; }
  .main { margin-left:0; padding:calc(var(--top-h) + 20px) 16px 32px; }
  .kpi-grid { grid-template-columns:1fr 1fr; gap:10px; }
  .expense-form { grid-template-columns:1fr; }
  .calc-grid { grid-template-columns:1fr; }
}
@media (max-width:480px) {
  .kpi-grid { grid-template-columns:1fr; }
  .main { padding-left:12px; padding-right:12px; }
  .page-header h1 { font-size:28px; }
}

/* ── ANIMATIONS ──────────────────────────────────────────────────────────── */
@keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
.kpi-card, .card { animation:fadeUp .4s both; }
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
    <a class="nav-link" href="dashboard.php"><span class="nav-ico">◈</span> Tableau de bord</a>
    <a class="nav-link" href="admin_orders.php"><span class="nav-ico">📦</span> Commandes</a>
    <a class="nav-link" href="admin_products.php"><span class="nav-ico">✦</span> Produits</a>
    <a class="nav-link active" href="admin_caisse.php"><span class="nav-ico">💰</span> Caisse</a>
    <a class="nav-link" href="index.php" target="_blank"><span class="nav-ico">↗</span> Voir la boutique</a>
  </nav>
  <div class="sidebar-footer">
    <span>v1.0</span>
    <a href="dashboard.php?logout=1" class="logout-link">Déconnexion</a>
  </div>
</aside>

<!-- ── MAIN ───────────────────────────────────────────────────────────────── -->
<main class="main">

  <div class="page-header">
    <div>
      <h1>Caisse</h1>
      <p>Bénéfice net & calculateur de prix</p>
    </div>
  </div>

  <!-- ═══ KPIs BILAN ════════════════════════════════════════════════════════ -->
  <div class="kpi-grid">

    <div class="kpi-card" style="animation-delay:.04s">
      <div class="kpi-accent" style="background:var(--green)"></div>
      <div class="kpi-label">CA Livrées</div>
      <div class="kpi-value"><?= number_format($caLivre, 0, ',', ' ') ?><small>DA</small></div>
      <div class="kpi-sub"><?= $nbLivre ?> commandes livrées</div>
      <div class="kpi-icon">✅</div>
    </div>

    <div class="kpi-card" style="animation-delay:.08s">
      <div class="kpi-accent" style="background:var(--red)"></div>
      <div class="kpi-label">Total Dépenses</div>
      <div class="kpi-value"><?= number_format($totalDepenses, 0, ',', ' ') ?><small>DA</small></div>
      <div class="kpi-sub"><?= count($expenses) ?> dépense<?= count($expenses) > 1 ? 's' : '' ?> enregistrée<?= count($expenses) > 1 ? 's' : '' ?></div>
      <div class="kpi-icon">📉</div>
    </div>

    <div class="kpi-card <?= $beneficeNet >= 0 ? 'kpi-benef-pos' : 'kpi-benef-neg' ?>" style="animation-delay:.12s">
      <div class="kpi-accent"></div>
      <div class="kpi-label">Bénéfice Net</div>
      <div class="kpi-value"><?= number_format($beneficeNet, 0, ',', ' ') ?><small>DA</small></div>
      <div class="kpi-sub">CA livrées − Dépenses</div>
      <div class="kpi-icon"><?= $beneficeNet >= 0 ? '💹' : '⚠️' ?></div>
    </div>

  </div>

  <!-- ═══ DÉPENSES ══════════════════════════════════════════════════════════ -->
  <div class="grid-2-1 mb-24">

    <!-- Liste dépenses -->
    <div class="card">
      <div class="card-head">
        <span class="card-title">Dépenses</span>
        <span style="font-size:13px;font-weight:800;color:var(--red)"><?= fda($totalDepenses) ?></span>
      </div>

      <!-- Formulaire ajout -->
      <div class="card-body" style="border-bottom:1px solid var(--border);background:var(--rose-bg)">
        <form method="POST" action="admin_caisse.php">
          <input type="hidden" name="action" value="add_expense">
          <div class="expense-form">
            <div class="form-field">
              <label>Libellé *</label>
              <input type="text" name="label" placeholder="Ex : Stock rouge à lèvres" required>
            </div>
            <div class="form-field">
              <label>Montant (DA) *</label>
              <input type="number" name="amount" placeholder="0.00" step="0.01" min="0" required>
            </div>
            <div class="form-field">
              <label>Catégorie</label>
              <select name="category">
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-field">
              <label>Note (optionnel)</label>
              <input type="text" name="note" placeholder="Précisions…">
            </div>
            <button type="submit" class="btn-add">＋ Ajouter la dépense</button>
          </div>
        </form>
      </div>

      <!-- Liste -->
      <div class="card-body">
        <?php if ($expenses): ?>
          <?php
          $catColors = [
            'Stock / Réapprovisionnement' => '#c4697a',
            'Livraison / Transport'       => '#3a6db0',
            'Emballage'                   => '#8b5a8b',
            'Marketing / Pub'             => '#b07030',
            'Frais fixes'                 => '#3a8a5c',
            'Autre'                       => '#9c8d85',
          ];
          foreach ($expenses as $exp):
            $color = $catColors[$exp['category']] ?? '#9c8d85';
          ?>
          <div class="expense-row">
            <span class="expense-cat-dot" style="background:<?= htmlspecialchars($color) ?>"></span>
            <div class="expense-info">
              <div class="expense-label"><?= htmlspecialchars($exp['label']) ?></div>
              <div class="expense-meta">
                <?= htmlspecialchars($exp['category']) ?>
                <?php if ($exp['note']): ?> · <?= htmlspecialchars($exp['note']) ?><?php endif; ?>
                · <?= date('d/m/Y', strtotime($exp['created_at'])) ?>
              </div>
            </div>
            <div class="expense-amount"><?= fda((float)$exp['amount']) ?></div>
            <form method="POST" onsubmit="return confirm('Supprimer cette dépense ?')">
              <input type="hidden" name="action"     value="delete_expense">
              <input type="hidden" name="expense_id" value="<?= $exp['id'] ?>">
              <button type="submit" class="btn-delete" title="Supprimer">✕</button>
            </form>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="color:var(--muted);text-align:center;padding:28px 0;font-size:13px">Aucune dépense enregistrée</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Résumé par catégorie -->
    <div style="display:flex;flex-direction:column;gap:20px">

      <div class="card">
        <div class="card-head"><span class="card-title">Par catégorie</span></div>
        <div class="card-body">
          <?php
          $byCategory = [];
          foreach ($expenses as $exp) {
              $cat = $exp['category'] ?? 'Autre';
              $byCategory[$cat] = ($byCategory[$cat] ?? 0) + (float)$exp['amount'];
          }
          arsort($byCategory);
          $maxCat = $byCategory ? max($byCategory) : 1;
          if ($byCategory):
            foreach ($byCategory as $cat => $total):
              $color = $catColors[$cat] ?? '#9c8d85';
              $pct   = round($total / $maxCat * 100);
          ?>
          <div style="margin-bottom:14px">
            <div class="cat-row">
              <div class="cat-row-left">
                <span class="expense-cat-dot" style="background:<?= htmlspecialchars($color) ?>"></span>
                <?= htmlspecialchars($cat) ?>
              </div>
              <div class="cat-row-right"><?= fda($total) ?></div>
            </div>
            <div class="cat-bar-wrap">
              <div class="cat-bar-fill" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($color) ?>"></div>
            </div>
          </div>
          <?php endforeach; else: ?>
            <p style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0">Aucune donnée</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Bilan synthèse -->
      <div class="card">
        <div class="card-head"><span class="card-title">Bilan</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:0">
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px">
            <span style="color:var(--muted);font-weight:600">CA livrées</span>
            <span style="font-weight:800;color:var(--green)"><?= fda($caLivre) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px">
            <span style="color:var(--muted);font-weight:600">− Dépenses</span>
            <span style="font-weight:800;color:var(--red)"><?= fda($totalDepenses) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:14px 0 0;font-size:16px">
            <span style="font-weight:700;color:var(--text)">= Bénéfice net</span>
            <span style="font-weight:800;color:<?= $beneficeNet >= 0 ? 'var(--green)' : 'var(--red)' ?>;font-size:18px">
              <?= fda($beneficeNet) ?>
            </span>
          </div>
          <?php if ($caLivre > 0): ?>
          <div style="margin-top:10px;padding:8px 12px;background:var(--surface2);border-radius:8px;font-size:11.5px;color:var(--muted);text-align:center">
            Marge nette : <strong style="color:<?= $beneficeNet >= 0 ? 'var(--green)' : 'var(--red)' ?>">
              <?= round($beneficeNet / $caLivre * 100, 1) ?>%
            </strong>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

  <!-- ═══ CA MENSUEL LIVRÉES ════════════════════════════════════════════════ -->
  <div class="card mb-24">
    <div class="card-head">
      <span class="card-title">CA mensuel — commandes livrées</span>
    </div>
    <div class="card-body" style="padding:0 22px 4px">
      <?php if ($monthlyCA): ?>
      <div class="tbl-wrap">
        <table class="tbl">
          <thead>
            <tr>
              <th>Mois</th>
              <th>Commandes livrées</th>
              <th>CA</th>
              <th style="text-align:right">% du total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($monthlyCA as $row):
              $pct = $caLivre > 0 ? round((float)$row['total'] / $caLivre * 100, 1) : 0;
              $dt  = \DateTime::createFromFormat('Y-m', $row['mois']);
              $label = $dt ? $dt->format('F Y') : $row['mois'];
            ?>
            <tr>
              <td style="font-weight:700"><?= htmlspecialchars($label) ?></td>
              <td><?= $row['cnt'] ?> livr<?= $row['cnt'] > 1 ? 'ées' : 'ée' ?></td>
              <td style="font-weight:800;color:var(--green)"><?= fda((float)$row['total']) ?></td>
              <td style="text-align:right">
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                  <div style="width:80px;height:6px;background:var(--surface2);border-radius:3px;overflow:hidden;border:1px solid var(--border)">
                    <div style="height:100%;border-radius:3px;background:var(--rose);width:<?= $pct ?>%"></div>
                  </div>
                  <span style="font-size:12px;font-weight:700;color:var(--muted)"><?= $pct ?>%</span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
        <p style="color:var(--muted);text-align:center;padding:28px 0;font-size:13px">Aucune commande livrée</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- ═══ CALCULATEUR PRIX ══════════════════════════════════════════════════ -->
  <div class="card">
    <div class="card-head">
      <span class="card-title">🧮 Calculateur de prix produit</span>
      <span style="font-size:12px;color:var(--muted)">Prix de vente optimal à partir du coût d'achat</span>
    </div>
    <div class="card-body">
      <div class="calc-grid">

        <div class="calc-field">
          <label>Prix de gros (DA)</label>
          <input type="number" id="cPrixGros" placeholder="0" min="0" step="0.01" oninput="calcPrice()">
        </div>

        <div class="calc-field">
          <label>Frais de livraison / unité (DA)</label>
          <input type="number" id="cFraisLiv" placeholder="0" min="0" step="0.01" value="0" oninput="calcPrice()">
        </div>

        <div class="calc-field">
          <label>Autres frais / unité (DA)</label>
          <input type="number" id="cAutresFrais" placeholder="0" min="0" step="0.01" value="0" oninput="calcPrice()">
        </div>

        <div class="calc-field">
          <label>Marge souhaitée (%)</label>
          <input type="number" id="cMarge" placeholder="40" min="0" max="999" step="1" value="40" oninput="calcPrice()">
          <div class="margin-presets">
            <button class="preset-btn" onclick="setMarge(20)">×1.2 — 20%</button>
            <button class="preset-btn active" onclick="setMarge(40)">×1.4 — 40%</button>
            <button class="preset-btn" onclick="setMarge(60)">×1.6 — 60%</button>
            <button class="preset-btn" onclick="setMarge(100)">×2 — 100%</button>
            <button class="preset-btn" onclick="setMarge(150)">×2.5 — 150%</button>
          </div>
        </div>

      </div>

      <!-- Résultats -->
      <div class="calc-results" id="calcResults" style="display:none">
        <div class="calc-result-row">
          <span class="calc-result-label">Coût de revient total</span>
          <span class="calc-result-val" id="rCout">—</span>
        </div>
        <div class="calc-result-row">
          <span class="calc-result-label">Bénéfice / unité</span>
          <span class="calc-result-val green" id="rBenef">—</span>
        </div>
        <div class="calc-result-row">
          <span class="calc-result-label">Prix de vente recommandé</span>
          <span class="calc-result-val highlight" id="rPrix">—</span>
        </div>
        <div class="calc-result-row">
          <span class="calc-result-label">Marge réelle</span>
          <span class="calc-result-val" id="rMargeReelle">—</span>
        </div>
        <div class="calc-result-row" style="background:var(--rose-bg)">
          <span class="calc-result-label" style="font-weight:800;color:var(--text)">Prix arrondi suggéré</span>
          <span class="calc-result-val highlight" id="rPrixArrondi">—</span>
        </div>
      </div>

    </div>
  </div>

</main>

<script>
// ── Sidebar mobile ────────────────────────────────────────────────────────────
const sidebar   = document.getElementById('sidebar');
const hamburger = document.getElementById('hamburger');
const overlay   = document.getElementById('overlay');
const openSb    = () => { sidebar.classList.add('open'); overlay.classList.add('active'); hamburger.classList.add('open'); document.body.style.overflow='hidden'; };
const closeSb   = () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); hamburger.classList.remove('open'); document.body.style.overflow=''; };
hamburger.addEventListener('click', () => sidebar.classList.contains('open') ? closeSb() : openSb());
overlay.addEventListener('click', closeSb);
sidebar.querySelectorAll('.nav-link').forEach(l => l.addEventListener('click', () => { if (window.innerWidth <= 900) closeSb(); }));

// ── Calculateur prix ──────────────────────────────────────────────────────────
function fmt(n) {
  return Math.round(n).toLocaleString('fr') + ' DA';
}

function arrondi(n) {
  // Arrondit au palier psychologique le plus proche : 00, 50, 99, 90
  const paliers = [0, 50, 90, 99];
  const centaine = Math.ceil(n / 100) * 100;
  const base     = Math.floor(n / 100) * 100;
  let best = n;
  let bestDiff = Infinity;
  for (const p of paliers) {
    const candidate = base + p;
    const diff = Math.abs(candidate - n);
    if (candidate >= n && diff < bestDiff) { best = candidate; bestDiff = diff; }
  }
  // Si aucun palier >= n dans la même centaine, prendre la centaine suivante - 1
  if (best < n) best = centaine - 1;
  return best;
}

function calcPrice() {
  const gros   = parseFloat(document.getElementById('cPrixGros').value)     || 0;
  const liv    = parseFloat(document.getElementById('cFraisLiv').value)      || 0;
  const autres = parseFloat(document.getElementById('cAutresFrais').value)   || 0;
  const marge  = parseFloat(document.getElementById('cMarge').value)         || 0;

  if (gros <= 0) { document.getElementById('calcResults').style.display = 'none'; return; }

  const cout   = gros + liv + autres;
  const prix   = cout * (1 + marge / 100);
  const benef  = prix - cout;
  const margeR = cout > 0 ? (benef / cout * 100) : 0;
  const prixAr = arrondi(prix);
  const benefAr = prixAr - cout;
  const margeAr = cout > 0 ? (benefAr / cout * 100) : 0;

  document.getElementById('rCout').textContent      = fmt(cout);
  document.getElementById('rBenef').textContent     = fmt(benef);
  document.getElementById('rPrix').textContent      = fmt(prix);
  document.getElementById('rMargeReelle').textContent = margeR.toFixed(1) + '%';
  document.getElementById('rPrixArrondi').textContent = fmt(prixAr) + ' (bénef. ' + fmt(benefAr) + ')';

  // Couleur marge
  const margeEl = document.getElementById('rMargeReelle');
  margeEl.className = 'calc-result-val ' + (margeR >= 50 ? 'green' : margeR >= 25 ? 'amber' : 'red');

  document.getElementById('calcResults').style.display = '';
}

function setMarge(val) {
  document.getElementById('cMarge').value = val;
  document.querySelectorAll('.preset-btn').forEach(b => {
    b.classList.toggle('active', parseInt(b.textContent) === val || b.getAttribute('onclick') === `setMarge(${val})`);
  });
  calcPrice();
}

// Init calc au chargement
calcPrice();
</script>
</body>
</html>