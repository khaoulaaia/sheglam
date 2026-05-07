<?php
// ============================================
//  SheGlamour — Dashboard Admin
//  /var/www/sheglamour/dashboard.php
// ============================================
include_once __DIR__ . '/includes/db.php';

// ─── KPIs ────────────────────────────────────
$kpis = [];

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
    WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', NOW())
      AND status != 'cancelled'
");
$kpis['revenue_month'] = (float) $r->fetchColumn();

$r = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURRENT_DATE");
$kpis['today_orders'] = (int) $r->fetchColumn();

$r = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'");
$delivered = (int) $r->fetchColumn();
$kpis['conversion'] = $kpis['orders'] > 0 ? round($delivered / $kpis['orders'] * 100, 1) : 0;

// ─── RÉPARTITION STATUTS ─────────────────────
$statusCounts = [];
$stRes = $pdo->query("SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status");
while ($row = $stRes->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['status']] = (int) $row['cnt'];
}
$totalOrders = array_sum($statusCounts);

// ─── CA PAR JOUR (30 derniers jours) ─────────
$chartData = $pdo->query("
    SELECT DATE(created_at) AS day, SUM(total) AS total
    FROM orders
    WHERE created_at >= NOW() - INTERVAL '30 days'
      AND status != 'cancelled'
    GROUP BY day
    ORDER BY day ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ─── TOP PRODUITS ─────────────────────────────
$topProducts = $pdo->query("
    SELECT oi.name, SUM(oi.quantity) AS qty, SUM(oi.quantity * oi.unit_price) AS revenue
    FROM order_items oi
    GROUP BY oi.name
    ORDER BY revenue DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// ─── WILAYAS ──────────────────────────────────
$wilayaData = $pdo->query("
    SELECT shipping->>'wilaya' AS wilaya, COUNT(*) AS cnt
    FROM orders
    WHERE shipping->>'wilaya' IS NOT NULL
    GROUP BY wilaya
    ORDER BY cnt DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// ─── MODES DE PAIEMENT ───────────────────────
$paymentData = $pdo->query("
    SELECT payment_method, COUNT(*) AS cnt
    FROM orders
    GROUP BY payment_method
")->fetchAll(PDO::FETCH_ASSOC);

// ─── COMMANDES RÉCENTES ───────────────────────
$recentOrders = $pdo->query("
    SELECT order_id, status, payment_method, total, shipping, created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// ─── HELPERS ─────────────────────────────────
function statusBadge($s) {
    $map = [
        'pending'   => ['label' => 'En attente', 'color' => '#f59e0b'],
        'confirmed' => ['label' => 'Confirmée',  'color' => '#6366f1'],
        'shipped'   => ['label' => 'Expédiée',   'color' => '#3b82f6'],
        'delivered' => ['label' => 'Livrée',     'color' => '#22c55e'],
        'cancelled' => ['label' => 'Annulée',    'color' => '#ef4444'],
    ];
    $d = $map[$s] ?? ['label' => $s, 'color' => '#aaa'];
    return "<span style='background:{$d['color']}1a;color:{$d['color']};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.05em'>{$d['label']}</span>";
}
function payLabel($p) {
    return match($p) {
        'cash'      => '💵 Livraison',
        'ccp'       => '🏦 CCP',
        'baridimob' => '📱 Baridi',
        default     => htmlspecialchars($p)
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SheGlamour — Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<style>
:root{
  --bg:#0d0d0f;--surface:#16161a;--surface2:#1e1e24;--border:#2a2a32;
  --text:#f0eff4;--muted:#7c7c8a;
  --accent:#e8b4c8;--accent2:#c9a0dc;
  --green:#22c55e;--amber:#f59e0b;--red:#ef4444;--blue:#6366f1;
  --radius:14px;
  --font-display:'DM Serif Display',serif;
  --font-body:'DM Sans',sans-serif;
}
*{margin:0;padding:0;box-sizing:border-box}
body{background:var(--bg);color:var(--text);font-family:var(--font-body);font-size:14px;min-height:100vh}

/* ── SIDEBAR ── */
.sidebar{position:fixed;top:0;left:0;width:220px;height:100vh;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;padding:32px 0 24px}
.sidebar-logo{padding:0 24px 32px;font-family:var(--font-display);font-size:22px;letter-spacing:-.02em}
.sidebar-logo span{color:var(--accent)}
.sidebar-nav{display:flex;flex-direction:column;gap:2px;padding:0 12px;flex:1}
.nav-item{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:500;color:var(--muted);transition:all .2s;text-decoration:none}
.nav-item:hover{background:var(--surface2);color:var(--text)}
.nav-item.active{background:var(--surface2);color:var(--accent)}
.nav-icon{font-size:16px;width:20px;text-align:center}
.sidebar-footer{padding:0 24px;font-size:11px;color:var(--muted)}

/* ── MAIN ── */
.main{margin-left:220px;padding:40px 36px;min-height:100vh}
.page-header{margin-bottom:36px}
.page-header h1{font-family:var(--font-display);font-size:32px;letter-spacing:-.02em;background:linear-gradient(135deg,var(--text) 40%,var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.page-header p{color:var(--muted);margin-top:4px;font-size:13px}

/* ── KPI GRIDS ── */
.kpi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
.kpi-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:24px;position:relative;overflow:hidden}
.kpi-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--accent-color,transparent) 0%,transparent 60%);opacity:.07;pointer-events:none}
.kpi-label{font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:12px}
.kpi-value{font-size:28px;font-weight:700;letter-spacing:-.02em}
.kpi-value small{font-size:14px;font-weight:400;color:var(--muted);margin-left:4px}
.kpi-sub{font-size:11px;color:var(--muted);margin-top:6px}
.kpi-icon{position:absolute;top:20px;right:20px;font-size:26px;opacity:.15}

/* ── LAYOUT ── */
.grid-2{display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px}
.mb-20{margin-bottom:20px}

/* ── CARD ── */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.card-header{padding:20px 24px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border)}
.card-title{font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)}
.card-body{padding:20px 24px}

/* ── STATUS BAR ── */
.status-bar{display:flex;height:8px;border-radius:4px;overflow:hidden;margin:8px 0 14px}
.status-bar-seg{height:100%}
.status-legend{display:flex;gap:14px;flex-wrap:wrap}
.status-legend-item{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted)}
.status-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}

/* ── MINI BARS ── */
.mini-bar-row{margin-bottom:13px}
.mini-bar-row:last-child{margin-bottom:0}
.mini-bar-label{display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px}
.mini-bar-label span:last-child{color:var(--muted)}
.mini-bar-track{height:6px;background:var(--surface2);border-radius:3px;overflow:hidden}
.mini-bar-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,var(--accent),var(--accent2));transition:width 1s ease}

/* ── CHART ── */
.chart-wrap{padding-top:16px;height:220px;position:relative}

/* ── TOP PRODUCTS ── */
.product-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)}
.product-row:last-child{border-bottom:none}
.product-rank{width:28px;height:28px;border-radius:50%;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--muted);flex-shrink:0}
.product-rank.gold{background:rgba(251,191,36,.15);color:#fbbf24}
.product-info{flex:1;min-width:0}
.product-name{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.product-qty{font-size:11px;color:var(--muted);margin-top:2px}
.product-revenue{font-size:13px;font-weight:700;color:var(--accent);white-space:nowrap}

/* ── PAYMENT PILLS ── */
.stat-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
.stat-pill{flex:1;min-width:80px;background:var(--surface2);border-radius:10px;padding:14px;text-align:center}
.stat-pill-val{font-size:20px;font-weight:700}
.stat-pill-lbl{font-size:10px;color:var(--muted);margin-top:3px;letter-spacing:.06em;text-transform:uppercase}

/* ── TABLE ── */
.table-scroll{overflow-y:auto;max-height:400px}
.table{width:100%;border-collapse:collapse}
.table th{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);padding:0 0 12px;text-align:left;border-bottom:1px solid var(--border)}
.table td{padding:13px 0;border-bottom:1px solid var(--border);vertical-align:middle;font-size:13px}
.table tr:last-child td{border-bottom:none}
.table td:last-child,.table th:last-child{text-align:right}
.order-id{font-family:monospace;font-size:11px;color:var(--accent);font-weight:700}

@media(max-width:900px){
  .sidebar{display:none}.main{margin-left:0;padding:20px}
  .kpi-grid{grid-template-columns:repeat(2,1fr)}
  .grid-2{grid-template-columns:1fr}
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">She<span>Glamour</span></div>
  <nav class="sidebar-nav">
    <a class="nav-item active" href="dashboard.php">
      <span class="nav-icon">◈</span> Tableau de bord
    </a>
    <a class="nav-item" href="admin_orders.php">
      <span class="nav-icon">◻</span> Commandes
    </a>
    <a class="nav-item" href="admin_products.php">
      <span class="nav-icon">✦</span> Produits
    </a>
    <a class="nav-item" href="index.php" target="_blank">
      <span class="nav-icon">↗</span> Voir la boutique
    </a>
  </nav>
  <div class="sidebar-footer">SheGlamour Admin · v1.0</div>
</aside>

<main class="main">

  <div class="page-header">
    <h1>Dashboard</h1>
    <p>Vue d'ensemble en temps réel — <?= date('d F Y, H:i') ?></p>
  </div>

  <!-- KPIs LIGNE 1 -->
  <div class="kpi-grid">
    <div class="kpi-card" style="--accent-color:var(--accent)">
      <div class="kpi-label">CA Total</div>
      <div class="kpi-value"><?= number_format($kpis['revenue'],0,',',' ') ?><small>DA</small></div>
      <div class="kpi-sub">Hors commandes annulées</div>
      <div class="kpi-icon">💰</div>
    </div>
    <div class="kpi-card" style="--accent-color:var(--accent2)">
      <div class="kpi-label">CA ce mois</div>
      <div class="kpi-value"><?= number_format($kpis['revenue_month'],0,',',' ') ?><small>DA</small></div>
      <div class="kpi-sub"><?= date('F Y') ?></div>
      <div class="kpi-icon">📅</div>
    </div>
    <div class="kpi-card" style="--accent-color:var(--green)">
      <div class="kpi-label">Taux livraison</div>
      <div class="kpi-value"><?= $kpis['conversion'] ?><small>%</small></div>
      <div class="kpi-sub">Commandes livrées / total</div>
      <div class="kpi-icon">✅</div>
    </div>
  </div>

  <!-- KPIs LIGNE 2 -->
  <div class="kpi-grid" style="margin-bottom:20px">
    <div class="kpi-card" style="--accent-color:var(--blue)">
      <div class="kpi-label">Commandes totales</div>
      <div class="kpi-value"><?= $kpis['orders'] ?></div>
      <div class="kpi-sub">Tous statuts confondus</div>
      <div class="kpi-icon">📦</div>
    </div>
    <div class="kpi-card" style="--accent-color:var(--amber)">
      <div class="kpi-label">En attente</div>
      <div class="kpi-value"><?= $kpis['pending'] ?></div>
      <div class="kpi-sub">À traiter en priorité</div>
      <div class="kpi-icon">⏳</div>
    </div>
    <div class="kpi-card" style="--accent-color:var(--green)">
      <div class="kpi-label">Aujourd'hui</div>
      <div class="kpi-value"><?= $kpis['today_orders'] ?></div>
      <div class="kpi-sub">Nouvelles commandes</div>
      <div class="kpi-icon">🛍</div>
    </div>
  </div>

  <!-- RÉPARTITION STATUTS -->
  <?php
    $statusColors = ['pending'=>'#f59e0b','confirmed'=>'#6366f1','shipped'=>'#3b82f6','delivered'=>'#22c55e','cancelled'=>'#ef4444'];
    $statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmée','shipped'=>'Expédiée','delivered'=>'Livrée','cancelled'=>'Annulée'];
  ?>
  <?php if ($totalOrders > 0): ?>
  <div class="card mb-20">
    <div class="card-header">
      <span class="card-title">Répartition des statuts</span>
      <span style="font-size:12px;color:var(--muted)"><?= $totalOrders ?> commandes au total</span>
    </div>
    <div class="card-body">
      <div class="status-bar">
        <?php foreach ($statusColors as $key => $color):
          $cnt = $statusCounts[$key] ?? 0;
          if (!$cnt) continue;
          $pct = round($cnt / $totalOrders * 100, 1);
        ?>
        <div class="status-bar-seg" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
        <?php endforeach; ?>
      </div>
      <div class="status-legend">
        <?php foreach ($statusColors as $key => $color):
          $cnt = $statusCounts[$key] ?? 0;
          if (!$cnt) continue;
          $pct = round($cnt / $totalOrders * 100, 1);
        ?>
        <div class="status-legend-item">
          <span class="status-dot" style="background:<?= $color ?>"></span>
          <?= $statusLabels[$key] ?> — <strong style="color:var(--text);margin:0 2px"><?= $cnt ?></strong>(<?= $pct ?>%)
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- GRAPHIQUE CA + WILAYAS -->
  <div class="grid-2">
    <div class="card">
      <div class="card-header">
        <span class="card-title">CA — 30 derniers jours</span>
        <span style="font-size:13px;font-weight:700;color:var(--accent)"><?= number_format($kpis['revenue_month'],0,',',' ') ?> DA ce mois</span>
      </div>
      <div class="card-body">
        <div class="chart-wrap">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Top Wilayas</span></div>
      <div class="card-body">
        <?php
          $maxW = $wilayaData ? max(array_column($wilayaData,'cnt')) : 1;
          foreach ($wilayaData as $w):
            $pct = round($w['cnt'] / $maxW * 100);
        ?>
        <div class="mini-bar-row">
          <div class="mini-bar-label">
            <span><?= htmlspecialchars($w['wilaya'] ?? '—') ?></span>
            <span><?= $w['cnt'] ?> cmd</span>
          </div>
          <div class="mini-bar-track">
            <div class="mini-bar-fill" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (!$wilayaData): ?>
          <p style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0">Aucune donnée</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- TOP PRODUITS + PAIEMENTS -->
  <div class="grid-2">
    <div class="card">
      <div class="card-header"><span class="card-title">Top Produits</span></div>
      <div class="card-body">
        <?php foreach ($topProducts as $i => $p): ?>
        <div class="product-row">
          <div class="product-rank <?= $i === 0 ? 'gold' : '' ?>"><?= $i + 1 ?></div>
          <div class="product-info">
            <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
            <div class="product-qty"><?= $p['qty'] ?> unités vendues</div>
          </div>
          <div class="product-revenue"><?= number_format($p['revenue'],0,',',' ') ?> DA</div>
        </div>
        <?php endforeach; ?>
        <?php if (!$topProducts): ?>
          <p style="color:var(--muted);font-size:13px;text-align:center;padding:20px 0">Aucune vente enregistrée</p>
        <?php endif; ?>
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
          <?php if (!$paymentData): ?>
            <p style="color:var(--muted);font-size:13px;width:100%;text-align:center">Aucune donnée</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- COMMANDES RÉCENTES -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Commandes récentes</span>
      <a href="admin_orders.php" style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600">Voir toutes →</a>
    </div>
    <div class="card-body" style="padding:0 24px">
      <div class="table-scroll">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Client</th>
              <th>Wilaya</th>
              <th>Paiement</th>
              <th>Statut</th>
              <th>Date</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentOrders as $o):
              $sh     = json_decode($o['shipping'] ?? '{}', true) ?: [];
              $prenom = $sh['prenom']    ?? ($sh['firstName'] ?? '—');
              $nom    = $sh['nom']       ?? ($sh['lastName']  ?? '');
              $wilaya = $sh['wilaya']    ?? '—';
            ?>
            <tr>
              <td><span class="order-id"><?= htmlspecialchars($o['order_id']) ?></span></td>
              <td style="font-weight:600"><?= htmlspecialchars(trim($prenom . ' ' . $nom)) ?></td>
              <td style="color:var(--muted);font-size:12px"><?= htmlspecialchars($wilaya) ?></td>
              <td><?= payLabel($o['payment_method']) ?></td>
              <td><?= statusBadge($o['status']) ?></td>
              <td style="color:var(--muted);font-size:12px"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
              <td style="font-weight:700;color:var(--accent)"><?= number_format($o['total'],2,',',' ') ?> DA</td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$recentOrders): ?>
              <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">Aucune commande pour le moment</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</main>

<script>
Chart.defaults.color = '#7c7c8a';
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.font.size = 11;

// ── Revenue line chart ──
const chartDays   = <?= json_encode(array_column($chartData,'day')) ?>;
const chartTotals = <?= json_encode(array_map(fn($r) => (float)$r['total'], $chartData)) ?>;

new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: chartDays,
    datasets: [{
      label: 'CA (DA)',
      data: chartTotals,
      borderColor: '#e8b4c8',
      backgroundColor: 'rgba(232,180,200,.08)',
      borderWidth: 2.5,
      pointRadius: 3,
      pointBackgroundColor: '#e8b4c8',
      tension: .4,
      fill: true,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { maxTicksLimit: 8 } },
      y: { grid: { color: 'rgba(255,255,255,.04)' }, ticks: { callback: v => v.toLocaleString('fr') } }
    }
  }
});

// ── Payment donut ──
const pmLabels = <?= json_encode(array_map(fn($p) => strip_tags(payLabel($p['payment_method'])), $paymentData)) ?>;
const pmCounts = <?= json_encode(array_column($paymentData,'cnt')) ?>;

if (pmCounts.length) {
  new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
      labels: pmLabels,
      datasets: [{
        data: pmCounts,
        backgroundColor: ['#e8b4c8','#c9a0dc','#6366f1'],
        borderWidth: 0,
        hoverOffset: 8,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '72%',
      plugins: {
        legend: { position: 'bottom', labels: { padding: 16, boxWidth: 10, borderRadius: 5 } }
      }
    }
  });
}
</script>
</body>
</html>