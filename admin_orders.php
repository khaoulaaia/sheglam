<?php
// ============================================
//  SheGlamour — Gestion des Commandes
//  /var/www/sheglamour/admin_orders.php
// ============================================
include_once __DIR__ . '/includes/db.php';

// ─── ACTION : CHANGEMENT DE STATUT ───────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['order_id'])) {
    $allowed = ['confirmed', 'shipped', 'delivered', 'cancelled', 'pending'];
    $newStatus = $_POST['action'];
    $orderId   = $_POST['order_id'];
    if (in_array($newStatus, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
        $stmt->execute([$newStatus, $orderId]);
    }
    header('Location: admin_orders.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

// ─── FILTRES ──────────────────────────────────
$filterStatus  = $_GET['status']  ?? '';
$filterWilaya  = $_GET['wilaya']  ?? '';
$filterPayment = $_GET['payment'] ?? '';
$search        = trim($_GET['q']  ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 20;

// Build WHERE
$conditions = [];
$params     = [];

if ($filterStatus)  { $conditions[] = "status = ?";                    $params[] = $filterStatus; }
if ($filterWilaya)  { $conditions[] = "shipping->>'wilaya' = ?";       $params[] = $filterWilaya; }
if ($filterPayment) { $conditions[] = "payment_method = ?";            $params[] = $filterPayment; }
if ($search) {
    $conditions[] = "(order_id ILIKE ? OR shipping->>'prenom' ILIKE ? OR shipping->>'nom' ILIKE ? OR shipping->>'tel' ILIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders $where");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pages = (int) ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Fetch
$stmt = $pdo->prepare("
    SELECT order_id, status, payment_method, total, shipping, created_at, updated_at
    FROM orders $where
    ORDER BY created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ─── STATS RAPIDES ────────────────────────────
$statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
$statusCounts = [];
foreach ($statuses as $s) {
    $r = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = '$s'");
    $statusCounts[$s] = (int) $r->fetchColumn();
}

// ─── WILAYAS DISTINCTES (pour filtre) ────────
$wilayas = $pdo->query("
    SELECT DISTINCT shipping->>'wilaya' AS w FROM orders
    WHERE shipping->>'wilaya' IS NOT NULL ORDER BY w
")->fetchAll(PDO::FETCH_COLUMN);

// ─── HELPERS ─────────────────────────────────
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
    return match($p) {
        'cash'      => '💵 Livraison',
        'ccp'       => '🏦 CCP',
        'baridimob' => '📱 Baridi',
        default     => htmlspecialchars($p)
    };
}
function qs($extra = []) {
    $params = array_merge($_GET, $extra);
    unset($params['page']);
    $q = http_build_query(array_filter($params, fn($v) => $v !== ''));
    return $q ? '?' . $q : '?';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SheGlamour — Commandes</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #f7f4f2;
  --surface: #ffffff;
  --surface2: #f2eee9;
  --border: #e8e0d8;
  --border2: #d4c9bf;
  --text: #1a1714;
  --text2: #4a3f38;
  --muted: #8c7d73;
  --muted2: #b5a99f;

  --rose:    #c4697a; --rose-bg: #fdf0f2; --rose-lt: #f5d0d7;
  --plum:    #8b5a8b; --plum-bg: #f5eef5; --plum-lt: #dfc8df;
  --green:   #3a8a5c; --green-bg: #eef7f2; --green-lt: #b8dfc9;
  --amber:   #b07030; --amber-bg: #fdf5eb; --amber-lt: #f0d4a8;
  --blue:    #3a6db0; --blue-bg: #eef3fb; --blue-lt: #b8cff0;
  --red:     #c0392b; --red-bg: #fdf0ee; --red-lt: #f0c0bb;

  --radius: 16px; --radius-sm: 10px;
  --shadow: 0 1px 4px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.05);
  --shadow-sm: 0 1px 3px rgba(0,0,0,.05);
  --font-display: 'Playfair Display', serif;
  --font-body: 'Nunito', sans-serif;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body { background: var(--bg); color: var(--text); font-family: var(--font-body); font-size: 14px; min-height: 100vh; }

/* ── SIDEBAR ── */
.sidebar { position: fixed; top: 0; left: 0; width: 230px; height: 100vh; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; z-index: 100; padding: 32px 0 24px; box-shadow: 2px 0 12px rgba(0,0,0,.04); }
.sidebar-logo { padding: 0 24px 32px; font-family: var(--font-display); font-size: 24px; color: var(--text); }
.sidebar-logo span { color: var(--rose); }
.sidebar-section { padding: 0 16px 8px; font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted2); margin-top: 8px; }
.sidebar-nav { display: flex; flex-direction: column; gap: 3px; padding: 0 12px; flex: 1; }
.nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: var(--radius-sm); font-size: 13.5px; font-weight: 600; color: var(--muted); transition: all .18s; text-decoration: none; cursor: pointer; }
.nav-item:hover { background: var(--surface2); color: var(--text2); }
.nav-item.active { background: var(--rose-bg); color: var(--rose); }
.sidebar-footer { padding: 16px 24px 0; font-size: 11px; color: var(--muted2); border-top: 1px solid var(--border); }

/* ── MAIN ── */
.main { margin-left: 230px; padding: 40px 36px; min-height: 100vh; }
.page-header { margin-bottom: 28px; display: flex; align-items: flex-end; justify-content: space-between; }
.page-header h1 { font-family: var(--font-display); font-size: 32px; color: var(--text); }
.page-header p { color: var(--muted); margin-top: 4px; font-size: 13px; }

/* ── STATUS TABS ── */
.status-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
.status-tab { display: flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 30px; font-size: 12.5px; font-weight: 700; text-decoration: none; transition: all .18s; border: 1.5px solid var(--border); color: var(--muted); background: var(--surface); }
.status-tab:hover { border-color: var(--border2); color: var(--text2); background: var(--surface2); }
.status-tab.active { border-color: var(--rose); color: var(--rose); background: var(--rose-bg); }
.tab-count { background: rgba(0,0,0,.08); border-radius: 10px; padding: 1px 7px; font-size: 11px; }
.status-tab.active .tab-count { background: rgba(196,105,122,.15); }

/* ── FILTERS ── */
.filters-bar { display: flex; gap: 10px; align-items: center; margin-bottom: 20px; flex-wrap: wrap; }
.filters-bar input, .filters-bar select {
  background: var(--surface); border: 1.5px solid var(--border);
  border-radius: var(--radius-sm); padding: 9px 14px;
  font-family: var(--font-body); font-size: 13px; color: var(--text);
  outline: none; transition: border-color .15s;
}
.filters-bar input:focus, .filters-bar select:focus { border-color: var(--rose); }
.filters-bar input { min-width: 220px; }
.filters-bar select { cursor: pointer; }
.btn-filter { padding: 9px 18px; background: var(--rose); color: #fff; border: none; border-radius: var(--radius-sm); font-family: var(--font-body); font-size: 13px; font-weight: 700; cursor: pointer; transition: background .15s; }
.btn-filter:hover { background: #b05568; }
.btn-reset { padding: 9px 14px; background: var(--surface); color: var(--muted); border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-family: var(--font-body); font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .15s; }
.btn-reset:hover { background: var(--surface2); }

/* ── CARD / TABLE ── */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-sm); }
.card-header { padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); background: var(--surface2); }
.card-title { font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
.result-count { font-size: 12px; color: var(--muted); font-weight: 600; }

.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; min-width: 860px; }
.table th { font-size: 10.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); padding: 14px 16px; text-align: left; border-bottom: 2px solid var(--border); white-space: nowrap; }
.table td { padding: 14px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 13px; color: var(--text2); }
.table tbody tr:last-child td { border-bottom: none; }
.table tbody tr { transition: background .1s; }
.table tbody tr:hover td { background: #fdf9f6; }

.order-id { font-family: 'Courier New', monospace; font-size: 11px; color: var(--rose); font-weight: 700; background: var(--rose-bg); padding: 3px 8px; border-radius: 6px; border: 1px solid var(--rose-lt); white-space: nowrap; }
.client-name { font-weight: 700; color: var(--text); }
.client-tel { font-size: 11px; color: var(--muted); margin-top: 2px; }
.total-cell { font-weight: 800; color: var(--rose); white-space: nowrap; }

/* ── BADGES ── */
.badge { display: inline-flex; align-items: center; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: .04em; white-space: nowrap; }
.badge-amber  { background: var(--amber-bg);  color: var(--amber);  border: 1px solid var(--amber-lt); }
.badge-indigo { background: var(--plum-bg);   color: var(--plum);   border: 1px solid var(--plum-lt); }
.badge-blue   { background: var(--blue-bg);   color: var(--blue);   border: 1px solid var(--blue-lt); }
.badge-green  { background: var(--green-bg);  color: var(--green);  border: 1px solid var(--green-lt); }
.badge-red    { background: var(--red-bg);    color: var(--red);    border: 1px solid var(--red-lt); }
.badge-gray   { background: var(--surface2);  color: var(--muted);  border: 1px solid var(--border); }

/* ── ACTION BUTTONS ── */
.action-group { display: flex; gap: 5px; flex-wrap: wrap; }
.btn-action { display: inline-flex; align-items: center; gap: 4px; padding: 5px 11px; border-radius: 7px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all .15s; border: 1.5px solid; font-family: var(--font-body); }
.btn-confirm { background: var(--plum-bg);  color: var(--plum);  border-color: var(--plum-lt); }
.btn-ship    { background: var(--blue-bg);  color: var(--blue);  border-color: var(--blue-lt); }
.btn-deliver { background: var(--green-bg); color: var(--green); border-color: var(--green-lt); }
.btn-cancel  { background: var(--red-bg);   color: var(--red);   border-color: var(--red-lt); }
.btn-pending { background: var(--amber-bg); color: var(--amber); border-color: var(--amber-lt); }
.btn-action:hover { filter: brightness(.92); transform: scale(.98); }

/* ── NOTE TOOLTIP ── */
.note-cell { max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 11px; color: var(--muted); font-style: italic; }

/* ── PAGINATION ── */
.pagination { display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 24px; flex-wrap: wrap; }
.page-btn { display: flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 12px; border-radius: 9px; font-size: 13px; font-weight: 700; text-decoration: none; transition: all .15s; border: 1.5px solid var(--border); background: var(--surface); color: var(--muted); }
.page-btn:hover { background: var(--surface2); color: var(--text); }
.page-btn.active { background: var(--rose); border-color: var(--rose); color: #fff; }
.page-btn.disabled { opacity: .4; pointer-events: none; }

/* ── EMPTY ── */
.empty-state { text-align: center; padding: 60px 20px; }
.empty-icon { font-size: 48px; opacity: .3; margin-bottom: 12px; }
.empty-text { color: var(--muted); font-size: 15px; }

/* ── MODAL ── */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.35); z-index: 500; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.modal-overlay.open { display: flex; }
.modal { background: var(--surface); border-radius: var(--radius); padding: 32px; max-width: 480px; width: 90%; box-shadow: 0 8px 40px rgba(0,0,0,.15); }
.modal h2 { font-family: var(--font-display); font-size: 22px; margin-bottom: 6px; }
.modal p { color: var(--muted); font-size: 13px; margin-bottom: 20px; }
.modal-info { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 16px; margin-bottom: 20px; }
.modal-info-row { display: flex; justify-content: space-between; font-size: 13px; padding: 4px 0; }
.modal-info-row span:first-child { color: var(--muted); }
.modal-info-row span:last-child { font-weight: 700; color: var(--text); }
.modal-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.modal-close { position: absolute; top: 16px; right: 20px; background: none; border: none; font-size: 22px; cursor: pointer; color: var(--muted); }

@media(max-width: 900px) {
  .sidebar { display: none; }
  .main { margin-left: 0; padding: 20px; }
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">She<span>Glamour</span></div>
  <div class="sidebar-section">Navigation</div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="dashboard.php"><span>◈</span> Tableau de bord</a>
    <a class="nav-item active" href="admin_orders.php">
      <span>📦</span> Commandes
      <?php if($statusCounts['pending'] > 0): ?>
      <span style="margin-left:auto;background:var(--amber);color:#fff;border-radius:10px;padding:1px 7px;font-size:10px"><?= $statusCounts['pending'] ?></span>
      <?php endif; ?>
    </a>
    <a class="nav-item" href="admin_products.php"><span>✦</span> Produits</a>
    <a class="nav-item" href="index.php" target="_blank"><span>↗</span> Voir la boutique</a>
  </nav>
  <div class="sidebar-footer">SheGlamour Admin · v1.0</div>
</aside>

<main class="main">

  <div class="page-header">
    <div>
      <h1>Commandes</h1>
      <p><?= $total ?> commande<?= $total > 1 ? 's' : '' ?> trouvée<?= $total > 1 ? 's' : '' ?></p>
    </div>
  </div>

  <!-- ONGLETS STATUT -->
  <div class="status-tabs">
    <a href="<?= qs(['status'=>'']) ?>" class="status-tab <?= $filterStatus==='' ? 'active' : '' ?>">
      Toutes <span class="tab-count"><?= array_sum($statusCounts) ?></span>
    </a>
    <?php
    $tabDef = [
      'pending'   => 'En attente',
      'confirmed' => 'Confirmées',
      'shipped'   => 'Expédiées',
      'delivered' => 'Livrées',
      'cancelled' => 'Annulées',
    ];
    foreach ($tabDef as $key => $label): ?>
    <a href="<?= qs(['status'=>$key]) ?>" class="status-tab <?= $filterStatus===$key ? 'active' : '' ?>">
      <?= $label ?> <span class="tab-count"><?= $statusCounts[$key] ?? 0 ?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- FILTRES -->
  <form method="GET" action="admin_orders.php">
    <?php if($filterStatus): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
    <div class="filters-bar">
      <input type="text" name="q" placeholder="🔍 ID, nom, téléphone…" value="<?= htmlspecialchars($search) ?>">
      <select name="wilaya">
        <option value="">Toutes les wilayas</option>
        <?php foreach ($wilayas as $w): ?>
        <option value="<?= htmlspecialchars($w) ?>" <?= $filterWilaya===$w ? 'selected' : '' ?>><?= htmlspecialchars($w) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="payment">
        <option value="">Tous les paiements</option>
        <option value="cash"      <?= $filterPayment==='cash'      ? 'selected' : '' ?>>💵 Livraison</option>
        <option value="ccp"       <?= $filterPayment==='ccp'       ? 'selected' : '' ?>>🏦 CCP</option>
        <option value="baridimob" <?= $filterPayment==='baridimob' ? 'selected' : '' ?>>📱 Baridi</option>
      </select>
      <button type="submit" class="btn-filter">Filtrer</button>
      <a href="admin_orders.php" class="btn-reset">Réinitialiser</a>
    </div>
  </form>

  <!-- TABLEAU -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Liste des commandes</span>
      <span class="result-count">Page <?= $page ?> / <?= max(1,$pages) ?></span>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Commande</th>
            <th>Client</th>
            <th>Wilaya / Adresse</th>
            <th>Paiement</th>
            <th>Note</th>
            <th>Statut</th>
            <th>Date</th>
            <th style="text-align:right">Total</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($orders): ?>
          <?php foreach ($orders as $o):
            $sh     = json_decode($o['shipping'] ?? '{}', true) ?: [];
            $prenom = $sh['prenom']  ?? ($sh['firstName'] ?? '');
            $nom    = $sh['nom']     ?? ($sh['lastName']  ?? '');
            $tel    = $sh['tel']     ?? '';
            $wilaya = $sh['wilaya']  ?? '—';
            $adresse= $sh['adresse'] ?? '';
            $note   = trim($sh['note'] ?? '');
            $fullName = trim("$prenom $nom") ?: '—';
          ?>
          <tr>
            <td><span class="order-id"><?= htmlspecialchars($o['order_id']) ?></span></td>
            <td>
              <div class="client-name"><?= htmlspecialchars($fullName) ?></div>
              <?php if($tel): ?><div class="client-tel">📞 <?= htmlspecialchars($tel) ?></div><?php endif; ?>
            </td>
            <td>
              <div style="font-weight:600;font-size:12.5px"><?= htmlspecialchars($wilaya) ?></div>
              <?php if($adresse): ?><div style="font-size:11px;color:var(--muted);margin-top:2px"><?= htmlspecialchars(mb_strimwidth($adresse,0,40,'…')) ?></div><?php endif; ?>
            </td>
            <td><?= payLabel($o['payment_method']) ?></td>
            <td><div class="note-cell" title="<?= htmlspecialchars($note) ?>"><?= $note ? htmlspecialchars($note) : '<span style="color:var(--muted2)">—</span>' ?></div></td>
            <td><?= statusBadge($o['status']) ?></td>
            <td style="font-size:12px;color:var(--muted);white-space:nowrap"><?= date('d/m/Y', strtotime($o['created_at'])) ?><br><?= date('H:i', strtotime($o['created_at'])) ?></td>
            <td class="total-cell" style="text-align:right"><?= number_format($o['total'],2,',',' ') ?> DA</td>
            <td>
              <div class="action-group">
                <?php
                  $nextActions = match($o['status']) {
                    'pending'   => [['confirmed','✔ Confirmer','btn-confirm'],['cancelled','✗ Annuler','btn-cancel']],
                    'confirmed' => [['shipped','🚚 Expédier','btn-ship'],['cancelled','✗ Annuler','btn-cancel']],
                    'shipped'   => [['delivered','✅ Livré','btn-deliver'],['pending','↩ En attente','btn-pending']],
                    'delivered' => [],
                    'cancelled' => [['pending','↩ Réactiver','btn-pending']],
                    default     => [],
                  };
                  foreach ($nextActions as [$action, $label, $cls]):
                ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Confirmer : <?= htmlspecialchars($label) ?> ?')">
                  <input type="hidden" name="action"   value="<?= $action ?>">
                  <input type="hidden" name="order_id" value="<?= htmlspecialchars($o['order_id']) ?>">
                  <?php if($filterStatus): ?><input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>"><?php endif; ?>
                  <button type="submit" class="btn-action <?= $cls ?>"><?= $label ?></button>
                </form>
                <?php endforeach; ?>
                <button class="btn-action btn-pending" onclick="openDetail(<?= htmlspecialchars(json_encode($o)) ?>)">👁 Détail</button>
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
  </div>

  <!-- PAGINATION -->
  <?php if ($pages > 1): ?>
  <div class="pagination">
    <a href="<?= qs(['page'=>$page-1]) ?>" class="page-btn <?= $page<=1 ? 'disabled' : '' ?>">← Préc</a>
    <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
    <a href="<?= qs(['page'=>$i]) ?>" class="page-btn <?= $i===$page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <a href="<?= qs(['page'=>$page+1]) ?>" class="page-btn <?= $page>=$pages ? 'disabled' : '' ?>">Suiv →</a>
  </div>
  <?php endif; ?>

</main>

<!-- MODAL DÉTAIL -->
<div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this)closeDetail()">
  <div class="modal" style="position:relative">
    <button class="modal-close" onclick="closeDetail()">×</button>
    <h2 id="modalTitle">Commande</h2>
    <p id="modalId" style="font-family:monospace;font-size:12px;color:var(--rose)"></p>
    <div class="modal-info" id="modalInfo"></div>
    <div id="modalStatus"></div>
  </div>
</div>

<script>
function openDetail(o) {
  const sh = typeof o.shipping === 'string' ? JSON.parse(o.shipping) : o.shipping;
  document.getElementById('modalTitle').textContent =
    [(sh.prenom||''), (sh.nom||'')].join(' ').trim() || 'Client inconnu';
  document.getElementById('modalId').textContent = o.order_id;

  const rows = [
    ['Téléphone',   sh.tel     || '—'],
    ['Wilaya',      sh.wilaya  || '—'],
    ['Adresse',     sh.adresse || '—'],
    ['Note client', sh.note    || '—'],
    ['Paiement',    o.payment_method],
    ['Total',       parseFloat(o.total).toFixed(2) + ' DA'],
    ['Date',        o.created_at.substring(0,16).replace('T',' ')],
  ];

  document.getElementById('modalInfo').innerHTML = rows.map(([k,v]) =>
    `<div class="modal-info-row"><span>${k}</span><span>${v}</span></div>`
  ).join('');

  const statusMap = {
    pending:'En attente', confirmed:'Confirmée', shipped:'Expédiée',
    delivered:'Livrée', cancelled:'Annulée'
  };
  const colorMap = {
    pending:'badge-amber', confirmed:'badge-indigo', shipped:'badge-blue',
    delivered:'badge-green', cancelled:'badge-red'
  };
  document.getElementById('modalStatus').innerHTML =
    `<span class="badge ${colorMap[o.status]||'badge-gray'}">${statusMap[o.status]||o.status}</span>`;

  document.getElementById('modalOverlay').classList.add('open');
}
function closeDetail() {
  document.getElementById('modalOverlay').classList.remove('open');
}
</script>
</body>
</html>