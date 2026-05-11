<?php
// ============================================
//  SheGlamour — Gestion Produits v2 (Thème Clair)
//  /var/www/sheglamour/admin_products.php
// ============================================
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/config.php';

$b       = BASE_URL ?? '';
$success = '';
$error   = '';

function imgUrl(string $b, ?string $raw): string {
    if (!$raw) return $b . '/images/placeholder.jpg';
    if (str_starts_with($raw, 'http')) return $raw;
    return $b . '/images/' . basename($raw);
}

if (isset($_GET['delete_img'])) {
    $iid = (int) $_GET['delete_img'];
    $pid = (int) ($_GET['pid'] ?? 0);
    try {
        $row = $pdo->prepare("SELECT image FROM product_images WHERE id = ?");
        $row->execute([$iid]);
        $imgFile = $row->fetchColumn();
        if ($imgFile && !str_starts_with($imgFile, 'http')) {
            $full = __DIR__ . '/images/' . basename($imgFile);
            if (file_exists($full)) unlink($full);
        }
        $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$iid]);
        header("Location: admin_products.php?edit=$pid&success=" . urlencode("Image supprimée.") . "#gallery");
        exit;
    } catch (Exception $e) { $error = "Erreur suppression image : " . $e->getMessage(); }
}

if (isset($_GET['delete_shade'])) {
    $sid = (int) $_GET['delete_shade'];
    $pid = (int) ($_GET['pid'] ?? 0);
    try {
        $pdo->prepare("DELETE FROM teintes WHERE id = ?")->execute([$sid]);
        header("Location: admin_products.php?edit=$pid&success=" . urlencode("Teinte supprimée.") . "#teintes");
        exit;
    } catch (Exception $e) { $error = "Erreur : " . $e->getMessage(); }
}

if (isset($_POST['action']) && $_POST['action'] === 'save_shade') {
    $pid     = (int) $_POST['product_id'];
    $sid     = (int) ($_POST['shade_id'] ?? 0);
    $nom     = trim($_POST['nom_teinte'] ?? '');
    $code    = trim($_POST['code_couleur'] ?? '#000000');
    $stockSh = (int) ($_POST['stock_shade'] ?? 0);
    if (!$nom) { $error = "Le nom de la teinte est obligatoire."; }
    else {
        try {
            if ($sid) {
                $pdo->prepare("UPDATE teintes SET nom_teinte=?, code_couleur=?, stock=? WHERE id=?")->execute([$nom, $code, $stockSh, $sid]);
                $msg = "Teinte mise à jour.";
            } else {
                $pdo->prepare("INSERT INTO teintes (product_id, nom_teinte, code_couleur, stock) VALUES (?,?,?,?)")->execute([$pid, $nom, $code, $stockSh]);
                $msg = "Teinte ajoutée.";
            }
            header("Location: admin_products.php?edit=$pid&success=" . urlencode($msg) . "#teintes");
            exit;
        } catch (Exception $e) { $error = "Erreur teinte : " . $e->getMessage(); }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'upload_images') {
    $pid = (int) $_POST['product_id'];
    $uploaded = 0;
    $uploadDir = __DIR__ . '/images/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (!empty($_FILES['extra_images']['name'][0])) {
        foreach ($_FILES['extra_images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['extra_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = strtolower(pathinfo($_FILES['extra_images']['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
            $filename = uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($tmp, $uploadDir . $filename)) {
                $pdo->prepare("INSERT INTO product_images (product_id, image) VALUES (?,?)")->execute([$pid, $filename]);
                $uploaded++;
            }
        }
    }
    header("Location: admin_products.php?edit=$pid&success=" . urlencode("$uploaded image(s) ajoutée(s).") . "#gallery");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $imgRow = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $imgRow->execute([$id]);
        $imgPath = $imgRow->fetchColumn();
        if ($imgPath && !str_starts_with($imgPath, 'http')) {
            $full = __DIR__ . '/images/' . basename($imgPath);
            if (file_exists($full)) unlink($full);
        }
        $pdo->prepare("DELETE FROM teintes WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        header("Location: admin_products.php?success=" . urlencode("Produit supprimé."));
        exit;
    } catch (Exception $e) { $error = "Erreur suppression : " . $e->getMessage(); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_full') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float) str_replace(',', '.', $_POST['price'] ?? 0);
    $stock       = (int) ($_POST['stock'] ?? 0);
    $categorie   = trim($_POST['categorie'] ?? '');
    $marque      = trim($_POST['marque'] ?? '');
    $has_shades  = isset($_POST['has_shades']) ? 1 : 0;
    $active      = isset($_POST['active']) ? 1 : 0;
    $shadesJson  = $_POST['shades_data'] ?? '[]';
    if (!$name || $price <= 0) {
        $error = "Le nom et le prix sont obligatoires.";
    } else {
        $imageUrl = null;
        if (!empty($_FILES['image']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext, $allowed)) { $error = "Format d'image non supporté."; }
            else {
                $uploadDir = __DIR__ . '/images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = uniqid('prod_') . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $imageUrl = $filename;
                } else { $error = "Erreur upload image principale."; }
            }
        }
        if (!$error) {
            try {
                $pdo->prepare("INSERT INTO products (name, description, price, stock, categorie, marque, has_shades, active, image_url) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$name, $description, $price, $stock, $categorie, $marque, $has_shades, $active, $imageUrl]);
                $newId = $pdo->lastInsertId();
                $shades = json_decode($shadesJson, true) ?: [];
                foreach ($shades as $sh) {
                    $nom = trim($sh['nom'] ?? '');
                    $code = trim($sh['code'] ?? '#000000');
                    $stSh = (int) ($sh['stock'] ?? 0);
                    if ($nom) $pdo->prepare("INSERT INTO teintes (product_id, nom_teinte, code_couleur, stock) VALUES (?,?,?,?)")->execute([$newId, $nom, $code, $stSh]);
                }
                if ($shades) $pdo->prepare("UPDATE products SET has_shades=1 WHERE id=?")->execute([$newId]);
                $uploadDir = __DIR__ . '/images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (!empty($_FILES['extra_images']['name'][0])) {
                    foreach ($_FILES['extra_images']['tmp_name'] as $i => $tmp) {
                        if ($_FILES['extra_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                        $ext = strtolower(pathinfo($_FILES['extra_images']['name'][$i], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
                        $fn = uniqid('img_') . '.' . $ext;
                        if (move_uploaded_file($tmp, $uploadDir . $fn))
                            $pdo->prepare("INSERT INTO product_images (product_id, image) VALUES (?,?)")->execute([$newId, $fn]);
                    }
                }
                header("Location: admin_products.php?edit=$newId&success=" . urlencode("Produit créé avec succès !"));
                exit;
            } catch (Exception $e) { $error = "Erreur BDD : " . $e->getMessage(); }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_product') {
    $id          = (int) $_POST['id'];
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float) str_replace(',', '.', $_POST['price'] ?? 0);
    $stock       = (int) ($_POST['stock'] ?? 0);
    $categorie   = trim($_POST['categorie'] ?? '');
    $marque      = trim($_POST['marque'] ?? '');
    $has_shades  = isset($_POST['has_shades']) ? 1 : 0;
    $active      = isset($_POST['active']) ? 1 : 0;
    if (!$name || $price <= 0) { $error = "Le nom et le prix sont obligatoires."; }
    else {
        $imageUrl = $_POST['existing_image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                $uploadDir = __DIR__ . '/images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = uniqid('prod_') . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) $imageUrl = $filename;
            }
        }
        try {
            $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, categorie=?, marque=?, has_shades=?, active=?, image_url=? WHERE id=?")
                ->execute([$name, $description, $price, $stock, $categorie, $marque, $has_shades, $active, $imageUrl, $id]);
            header("Location: admin_products.php?edit=$id&success=" . urlencode("Produit mis à jour."));
            exit;
        } catch (Exception $e) { $error = "Erreur BDD : " . $e->getMessage(); }
    }
}

if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

$editProduct = null; $editTeintes = []; $extraImages = []; $editShade = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int) $_GET['edit']]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editProduct) {
        $tStmt = $pdo->prepare("SELECT * FROM teintes WHERE product_id = ? ORDER BY id ASC");
        $tStmt->execute([$editProduct['id']]);
        $editTeintes = $tStmt->fetchAll(PDO::FETCH_ASSOC);
        $iStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY id ASC");
        $iStmt->execute([$editProduct['id']]);
        $extraImages = $iStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    if (isset($_GET['edit_shade'])) {
        $shEdit = $pdo->prepare("SELECT * FROM teintes WHERE id = ?");
        $shEdit->execute([(int) $_GET['edit_shade']]);
        $editShade = $shEdit->fetch(PDO::FETCH_ASSOC);
    }
}

$search    = trim($_GET['q'] ?? '');
$catFilter = trim($_GET['cat'] ?? '');
$where = []; $params = [];
if ($search)    { $where[] = "(name ILIKE ? OR description ILIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilter) { $where[] = "categorie = ?"; $params[] = $catFilter; }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$productsStmt = $pdo->prepare("SELECT * FROM products $whereSql ORDER BY id DESC");
$productsStmt->execute($params);
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);

$shadeCountMap = [];
if ($products) {
    $ids = array_column($products, 'id');
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $sc  = $pdo->prepare("SELECT product_id, COUNT(*) AS cnt FROM teintes WHERE product_id IN ($ph) GROUP BY product_id");
    $sc->execute($ids);
    foreach ($sc->fetchAll(PDO::FETCH_ASSOC) as $r) $shadeCountMap[$r['product_id']] = (int)$r['cnt'];
}

$categories    = $pdo->query("SELECT DISTINCT categorie FROM products WHERE categorie IS NOT NULL AND categorie != '' ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);
$marques       = $pdo->query("SELECT DISTINCT marque FROM products WHERE marque IS NOT NULL AND marque != '' ORDER BY marque")->fetchAll(PDO::FETCH_COLUMN);
$totalProducts = count($products);
$totalStock    = array_sum(array_column($products, 'stock'));
$activeCount   = count(array_filter($products, fn($p) => $p['active']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SheGlamour — Produits</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #f7f4f2;
  --surface: #ffffff;
  --surface2: #f2eee9;
  --surface3: #ede7e0;
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
  --sidebar: 230px;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body { background: var(--bg); color: var(--text); font-family: var(--font-body); font-size: 14px; line-height: 1.5; min-height: 100vh; }

/* ── SIDEBAR ── */
.sidebar { position: fixed; top: 0; left: 0; width: var(--sidebar); height: 100vh; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; z-index: 100; padding: 32px 0 24px; box-shadow: 2px 0 12px rgba(0,0,0,.04); }
.sidebar-logo { padding: 0 24px 32px; font-family: var(--font-display); font-size: 24px; color: var(--text); }
.sidebar-logo span { color: var(--rose); }
.sidebar-section { padding: 0 16px 8px; font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted2); margin-top: 8px; }
.sidebar-nav { display: flex; flex-direction: column; gap: 3px; padding: 0 12px; flex: 1; }
.nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-radius: var(--radius-sm); font-size: 13.5px; font-weight: 600; color: var(--muted); transition: all .18s; text-decoration: none; }
.nav-item:hover { background: var(--surface2); color: var(--text2); }
.nav-item.active { background: var(--rose-bg); color: var(--rose); }
.sidebar-footer { padding: 16px 24px 0; font-size: 11px; color: var(--muted2); border-top: 1px solid var(--border); }

/* ── MAIN ── */
.main { margin-left: var(--sidebar); padding: 40px 36px; min-height: 100vh; }

/* ── BUTTONS ── */
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--radius-sm); font-family: var(--font-body); font-size: 13px; font-weight: 700; cursor: pointer; border: none; transition: all .18s; text-decoration: none; white-space: nowrap; }
.btn-primary { background: var(--rose); color: #fff; }
.btn-primary:hover { background: #b05568; transform: translateY(-1px); }
.btn-ghost { background: var(--surface); color: var(--text2); border: 1.5px solid var(--border); }
.btn-ghost:hover { border-color: var(--rose); color: var(--rose); background: var(--rose-bg); }
.btn-danger { background: var(--red-bg); color: var(--red); border: 1.5px solid var(--red-lt); }
.btn-danger:hover { background: #f9dbd8; }
.btn-amber { background: var(--amber-bg); color: var(--amber); border: 1.5px solid var(--amber-lt); }
.btn-sm { padding: 5px 12px; font-size: 11px; border-radius: 7px; }

/* ── PAGE HEADER ── */
.page-header { margin-bottom: 28px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.page-header h1 { font-family: var(--font-display); font-size: 34px; color: var(--text); }
.page-header p { color: var(--muted); margin-top: 4px; font-size: 13px; }

/* ── TOAST ── */
.toast { padding: 12px 18px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 9px; }
.toast-ok  { background: var(--green-bg); border: 1px solid var(--green-lt); color: var(--green); }
.toast-err { background: var(--red-bg);   border: 1px solid var(--red-lt);   color: var(--red); }

/* ── KPI ── */
.kpi-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 24px; }
.kpi { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px 22px; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); transition: box-shadow .2s, transform .2s; }
.kpi:hover { box-shadow: var(--shadow); transform: translateY(-1px); }
.kpi-accent { position: absolute; top: 0; left: 0; width: 4px; height: 100%; border-radius: var(--radius) 0 0 var(--radius); }
.kpi-lbl { font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
.kpi-val { font-family: var(--font-display); font-size: 30px; font-weight: 700; color: var(--text); }
.kpi-icon { position: absolute; top: 16px; right: 16px; font-size: 22px; opacity: .15; }

/* ── CARD ── */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 22px; }
.card-hd { padding: 18px 24px 14px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); background: var(--surface2); flex-wrap: wrap; gap: 8px; }
.card-title { font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
.card-body { padding: 22px 24px; }

/* ── FORM ── */
.fg { display: flex; flex-direction: column; gap: 5px; }
.fg.full { grid-column: 1/-1; }
.fl { font-size: 10px; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: var(--muted); }
.fi { background: var(--surface); border: 1.5px solid var(--border); border-radius: 8px; padding: 9px 13px; color: var(--text); font-family: var(--font-body); font-size: 13px; transition: border-color .18s; width: 100%; }
.fi:focus { outline: none; border-color: var(--rose); }
.fi::placeholder { color: var(--muted2); }
textarea.fi { resize: vertical; min-height: 80px; }
.form-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }

/* ── TOGGLE ── */
.tog { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
.tog input { width: 0; height: 0; opacity: 0; position: absolute; }
.tog-track { width: 40px; height: 22px; background: var(--border); border: 1.5px solid var(--border2); border-radius: 11px; position: relative; transition: all .18s; flex-shrink: 0; }
.tog-track::after { content: ''; position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; background: var(--muted2); border-radius: 50%; transition: all .18s; }
.tog input:checked + .tog-track { background: var(--rose-bg); border-color: var(--rose-lt); }
.tog input:checked + .tog-track::after { left: 20px; background: var(--rose); }
.tog-lbl { font-size: 13px; color: var(--text2); font-weight: 500; }

/* ── UPLOAD ── */
.upzone { border: 2px dashed var(--border2); border-radius: var(--radius-sm); padding: 22px; text-align: center; cursor: pointer; transition: all .18s; position: relative; background: var(--surface2); }
.upzone:hover { border-color: var(--rose); background: var(--rose-bg); }
.upzone input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.up-icon { font-size: 26px; margin-bottom: 6px; opacity: .4; }
.up-txt { font-size: 11px; color: var(--muted); font-weight: 600; }
.up-prev { width: 100%; max-height: 130px; object-fit: contain; border-radius: 8px; margin-top: 10px; display: none; border: 1px solid var(--border); }
.strip { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
.strip img { width: 56px; height: 56px; object-fit: cover; border-radius: 7px; border: 1px solid var(--border); }

/* ── TOOLBAR ── */
.toolbar { display: flex; gap: 10px; margin-bottom: 18px; align-items: center; flex-wrap: wrap; }
.srch { position: relative; flex: 1; min-width: 160px; }
.srch .fi { padding-left: 34px; }
.srch-ico { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; }

/* ── TABLE ── */
.tbl-wrap { overflow-x: auto; }
.tbl { width: 100%; border-collapse: collapse; min-width: 780px; }
.tbl th { font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); padding: 0 12px 13px; text-align: left; border-bottom: 2px solid var(--border); }
.tbl th:first-child { padding-left: 0; }
.tbl td { padding: 12px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 13px; color: var(--text2); }
.tbl td:first-child { padding-left: 0; }
.tbl tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: var(--surface2); }
.pimg { width: 44px; height: 44px; border-radius: 9px; object-fit: cover; border: 1px solid var(--border); }
.pimg-ph { width: 44px; height: 44px; border-radius: 9px; background: var(--surface2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--muted); }
.bdg { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 800; letter-spacing: .05em; }
.bdg-ok    { background: var(--green-bg);  color: var(--green);  border: 1px solid var(--green-lt); }
.bdg-off   { background: var(--surface2);  color: var(--muted);  border: 1px solid var(--border); }
.bdg-sh    { background: var(--plum-bg);   color: var(--plum);   border: 1px solid var(--plum-lt); text-decoration: none; transition: background .15s; }
.bdg-sh:hover { background: var(--plum-lt); }
.bdg-brand { background: var(--blue-bg);   color: var(--blue);   border: 1px solid var(--blue-lt); }
.stk-lo { color: var(--red);   font-weight: 800; }
.stk-ok { color: var(--green); font-weight: 800; }
.stk-md { color: var(--amber); font-weight: 800; }
.acts { display: flex; gap: 7px; justify-content: flex-end; }

/* ── EDIT LAYOUT ── */
.edit-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; align-items: start; }
.edit-layout .card { margin-bottom: 0; }
.bc { display: flex; align-items: center; gap: 7px; font-size: 12px; color: var(--muted); margin-bottom: 18px; }
.bc a { color: var(--muted); text-decoration: none; font-weight: 600; }
.bc a:hover { color: var(--rose); }

/* ── TEINTES ── */
.sh-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 9px; margin-bottom: 18px; }
.sh-card { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 10px 13px; display: flex; align-items: center; gap: 9px; }
.sh-sw { width: 30px; height: 30px; border-radius: 7px; flex-shrink: 0; border: 2px solid rgba(0,0,0,.08); }
.sh-info { flex: 1; min-width: 0; }
.sh-nm { font-weight: 700; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text); }
.sh-stk { font-size: 10px; color: var(--muted); }
.sh-acts { display: flex; gap: 5px; flex-shrink: 0; }
.sh-form { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 16px; margin-top: 4px; }
.col-row { display: flex; align-items: center; gap: 9px; }
.col-sw { width: 34px; height: 34px; border-radius: 7px; border: 2px solid rgba(0,0,0,.08); flex-shrink: 0; }

/* ── GALLERY ── */
.gal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 9px; margin-bottom: 14px; }
.gal-item { position: relative; border-radius: 9px; overflow: hidden; border: 1px solid var(--border); aspect-ratio: 1; background: var(--surface2); }
.gal-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gal-del { position: absolute; top: 4px; right: 4px; background: rgba(255,255,255,.9); color: var(--red); border: 1px solid var(--red-lt); border-radius: 5px; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer; text-decoration: none; transition: all .15s; font-weight: 800; }
.gal-del:hover { background: var(--red-bg); }
.gal-zone { border: 2px dashed var(--border2); border-radius: var(--radius-sm); padding: 18px; text-align: center; cursor: pointer; transition: all .18s; position: relative; background: var(--surface2); }
.gal-zone:hover { border-color: var(--rose); background: var(--rose-bg); }
.gal-zone input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }

/* ── MODAL ── */
.overlay { position: fixed; inset: 0; background: rgba(26,23,20,.55); z-index: 200; display: none; overflow-y: auto; padding: 30px 20px; backdrop-filter: blur(4px); }
.overlay.open { display: block; }
.modal { background: var(--surface); border: 1px solid var(--border); border-radius: 22px; width: 100%; max-width: 780px; margin: 0 auto; position: relative; box-shadow: var(--shadow); }
.m-hd { padding: 24px 28px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: var(--surface2); border-radius: 22px 22px 0 0; }
.m-title { font-family: var(--font-display); font-size: 26px; font-weight: 700; color: var(--text); }
.m-close { background: var(--surface); border: 1.5px solid var(--border); color: var(--muted); width: 32px; height: 32px; border-radius: 8px; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: all .18s; font-weight: 800; }
.m-close:hover { color: var(--red); border-color: var(--red-lt); background: var(--red-bg); }

/* ── TABS (modal) ── */
.tab-bar { display: flex; gap: 0; border-bottom: 1px solid var(--border); padding: 0 28px; background: var(--surface); }
.tab-btn { padding: 12px 18px; font-size: 13px; font-weight: 700; color: var(--muted); cursor: pointer; border: none; background: none; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all .18s; display: flex; align-items: center; gap: 7px; font-family: var(--font-body); }
.tab-btn.active { color: var(--rose); border-bottom-color: var(--rose); }
.tab-btn:hover:not(.active) { color: var(--text); }
.tab-panel { display: none; padding: 24px 28px; }
.tab-panel.active { display: block; }
.tab-count { background: var(--surface2); color: var(--muted); font-size: 10px; font-weight: 800; padding: 1px 7px; border-radius: 20px; min-width: 20px; text-align: center; border: 1px solid var(--border); }
.tab-count.has { background: var(--rose-bg); color: var(--rose); border-color: var(--rose-lt); }

/* ── SHADE ROW (modal) ── */
.m-sh-list { display: flex; flex-direction: column; gap: 7px; margin-bottom: 14px; }
.m-sh-row { display: grid; grid-template-columns: 30px 1fr 80px 70px auto; gap: 8px; align-items: center; background: var(--surface2); border: 1px solid var(--border); border-radius: 9px; padding: 9px 12px; }
.m-sh-sw { width: 28px; height: 28px; border-radius: 6px; border: 2px solid rgba(0,0,0,.08); }
.m-sh-nm { font-size: 13px; font-weight: 700; color: var(--text); }
.m-sh-stk { font-size: 12px; color: var(--muted); }
.m-sh-code { font-size: 11px; color: var(--muted); font-family: monospace; }

.step-hd { font-size: 10px; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
.info-box { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px 15px; font-size: 11px; color: var(--muted); line-height: 1.7; margin-top: 14px; }
.info-box strong { color: var(--text2); display: block; margin-bottom: 3px; font-weight: 700; }
code { color: var(--rose); font-size: 10px; font-family: monospace; }

.m-footer { padding: 14px 28px 22px; display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--border); }

@media(max-width: 960px) {
  .sidebar { display: none; } .main { margin-left: 0; padding: 18px; }
  .kpi-row, .edit-layout, .form-2, .form-3 { grid-template-columns: 1fr; }
  .fg.full { grid-column: 1; }
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">She<span>Glamour</span></div>
  <div class="sidebar-section">Navigation</div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="dashboard.php"><span>◈</span> Tableau de bord</a>
    <a class="nav-item" href="admin_orders.php"><span>📦</span> Commandes</a>
    <a class="nav-item active" href="admin_products.php"><span>✦</span> Produits</a>
    <a class="nav-item" href="index.php" target="_blank"><span>↗</span> Voir la boutique</a>
  </nav>
  <div class="sidebar-footer">SheGlamour Admin · v2.0</div>
</aside>

<main class="main">

<?php if ($editProduct): ?>
<!-- ══════ VUE ÉDITION ══════ -->

<div class="bc">
  <a href="admin_products.php">✦ Produits</a>
  <span>›</span>
  <span style="color:var(--text)"><?= htmlspecialchars($editProduct['name']) ?></span>
</div>

<?php if ($success): ?><div class="toast toast-ok">✓ <?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="toast toast-err">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="page-header">
  <div>
    <h1>Modifier le produit</h1>
    <p>ID #<?= $editProduct['id'] ?> · Créé le <?= date('d/m/Y', strtotime($editProduct['created_at'] ?? 'now')) ?></p>
  </div>
  <a href="admin_products.php" class="btn btn-ghost">← Retour</a>
</div>

<div class="edit-layout">

  <!-- FICHE PRODUIT -->
  <div class="card">
    <div class="card-hd"><span class="card-title">Informations produit</span></div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_product">
        <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editProduct['image_url'] ?? '') ?>">
        <div class="form-2" style="margin-bottom:14px">
          <div class="fg full">
            <label class="fl">Nom *</label>
            <input type="text" name="name" class="fi" value="<?= htmlspecialchars($editProduct['name']) ?>" required>
          </div>
          <div class="fg full">
            <label class="fl">Description</label>
            <textarea name="description" class="fi"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
          </div>
          <div class="fg">
            <label class="fl">Prix (DA) *</label>
            <input type="number" name="price" class="fi" step="0.01" min="0" value="<?= $editProduct['price'] ?>" required>
          </div>
          <div class="fg">
            <label class="fl">Stock global</label>
            <input type="number" name="stock" class="fi" min="0" value="<?= (int)$editProduct['stock'] ?>">
          </div>
          <div class="fg">
            <label class="fl">Catégorie</label>
            <input type="text" name="categorie" class="fi" value="<?= htmlspecialchars($editProduct['categorie'] ?? '') ?>" list="catList" placeholder="Ex: Lèvres…">
            <datalist id="catList"><?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg">
            <label class="fl">Marque</label>
            <input type="text" name="marque" class="fi" value="<?= htmlspecialchars($editProduct['marque'] ?? '') ?>" list="marqList" placeholder="Ex: L'Oréal…">
            <datalist id="marqList"><?php foreach ($marques as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg full" style="gap:12px">
            <label class="fl">Options</label>
            <label class="tog">
              <input type="checkbox" name="has_shades" value="1" <?= $editProduct['has_shades'] ? 'checked' : '' ?>>
              <span class="tog-track"></span><span class="tog-lbl">Produit avec teintes 🎨</span>
            </label>
            <label class="tog">
              <input type="checkbox" name="active" value="1" <?= $editProduct['active'] ? 'checked' : '' ?>>
              <span class="tog-track"></span><span class="tog-lbl">Actif sur la boutique</span>
            </label>
          </div>
          <div class="fg full">
            <label class="fl">Remplacer l'image</label>
            <div class="upzone">
              <input type="file" name="image" accept="image/*" onchange="previewImg(this,'eprev')">
              <div class="up-icon">📷</div>
              <div class="up-txt">Cliquez ou glissez · JPG PNG WEBP</div>
              <img id="eprev" class="up-prev" alt="">
            </div>
            <?php if ($editProduct['image_url']): ?>
            <div style="margin-top:9px;display:flex;align-items:center;gap:10px">
              <img src="<?= htmlspecialchars(imgUrl($b, $editProduct['image_url'])) ?>" style="height:52px;border-radius:8px;border:1px solid var(--border)" alt="">
              <span style="font-size:11px;color:var(--muted)">Image actuelle</span>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <button type="submit" class="btn btn-primary">✦ Enregistrer</button>
          <a href="admin_products.php?delete=<?= $editProduct['id'] ?>" class="btn btn-danger"
             onclick="return confirm('Supprimer ce produit et toutes ses données ?')">✕ Supprimer</a>
        </div>
      </form>
    </div>
  </div>

  <!-- TEINTES -->
  <div id="teintes">
    <div class="card">
      <div class="card-hd">
        <span class="card-title">Teintes (<?= count($editTeintes) ?>)</span>
        <?php if ($editProduct['has_shades']): ?>
          <span style="font-size:11px;color:var(--green);font-weight:700">✓ Actives</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if (!$editProduct['has_shades']): ?>
          <div style="text-align:center;padding:32px 0;color:var(--muted)">
            <div style="font-size:36px;margin-bottom:10px;opacity:.25">🎨</div>
            <p style="font-size:13px;line-height:1.6">Activez <strong style="color:var(--text2)">"Produit avec teintes"</strong><br>puis enregistrez.</p>
          </div>
        <?php else: ?>
          <?php if ($editTeintes): ?>
          <div class="sh-grid">
            <?php foreach ($editTeintes as $t): ?>
            <div class="sh-card">
              <div class="sh-sw" style="background:<?= htmlspecialchars($t['code_couleur']) ?>"></div>
              <div class="sh-info">
                <div class="sh-nm"><?= htmlspecialchars($t['nom_teinte']) ?></div>
                <div class="sh-stk">Stock : <?= (int)($t['stock'] ?? 0) ?></div>
              </div>
              <div class="sh-acts">
                <button class="btn btn-ghost btn-sm" onclick='loadShade(<?= json_encode($t, JSON_HEX_APOS) ?>)'>✎</button>
                <a href="admin_products.php?delete_shade=<?= $t['id'] ?>&pid=<?= $editProduct['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Supprimer cette teinte ?')">✕</a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
            <p style="color:var(--muted);font-size:13px;text-align:center;padding:10px 0 16px">Aucune teinte. Ajoutez-en une ci-dessous.</p>
          <?php endif; ?>
          <div class="sh-form">
            <div style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:12px" id="shLbl">+ Nouvelle teinte</div>
            <form method="POST">
              <input type="hidden" name="action" value="save_shade">
              <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
              <input type="hidden" name="shade_id" id="shId" value="<?= $editShade ? $editShade['id'] : '' ?>">
              <div class="form-3">
                <div class="fg">
                  <label class="fl">Nom *</label>
                  <input type="text" name="nom_teinte" id="shNom" class="fi" placeholder="Rose Poudré"
                         value="<?= $editShade ? htmlspecialchars($editShade['nom_teinte']) : '' ?>" required>
                </div>
                <div class="fg">
                  <label class="fl">Couleur</label>
                  <div class="col-row">
                    <input type="color" name="code_couleur" id="shCol" class="fi"
                           value="<?= $editShade ? htmlspecialchars($editShade['code_couleur']) : '#c4697a' ?>"
                           style="padding:3px;height:38px;cursor:pointer"
                           oninput="document.getElementById('colSw').style.background=this.value">
                    <div class="col-sw" id="colSw" style="background:<?= $editShade ? htmlspecialchars($editShade['code_couleur']) : '#c4697a' ?>"></div>
                  </div>
                </div>
                <div class="fg">
                  <label class="fl">Stock</label>
                  <input type="number" name="stock_shade" id="shStk" class="fi" value="<?= $editShade ? (int)$editShade['stock'] : 0 ?>" min="0">
                </div>
              </div>
              <div style="margin-top:12px;display:flex;gap:9px">
                <button type="submit" class="btn btn-primary btn-sm" id="shBtn"><?= $editShade ? '✦ Mettre à jour' : '✦ Ajouter' ?></button>
                <button type="button" class="btn btn-ghost btn-sm" onclick="resetShForm()">Annuler</button>
              </div>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="info-box">
      <strong>ℹ Intégration boutique</strong>
      Teintes lues via <code>get_shades.php?product_id=<?= $editProduct['id'] ?></code> · S'affiche si <code>has_shades=1</code>
    </div>
  </div>
</div>

<!-- GALERIE -->
<div id="gallery" class="card">
  <div class="card-hd">
    <span class="card-title">Galerie — images supplémentaires (<?= count($extraImages) ?>)</span>
    <span style="font-size:11px;color:var(--muted)">Miniatures page produit</span>
  </div>
  <div class="card-body">
    <?php if ($extraImages): ?>
    <div class="gal-grid">
      <?php foreach ($extraImages as $img): ?>
      <div class="gal-item">
        <img src="<?= htmlspecialchars(imgUrl($b, $img['image'])) ?>" alt="">
        <a href="admin_products.php?delete_img=<?= $img['id'] ?>&pid=<?= $editProduct['id'] ?>"
           class="gal-del" onclick="return confirm('Supprimer ?')">✕</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p style="color:var(--muted);font-size:13px;margin-bottom:14px">Aucune image supplémentaire.</p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="upload_images">
      <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
      <div class="gal-zone">
        <input type="file" name="extra_images[]" accept="image/*" multiple onchange="previewGallery(this)">
        <div class="up-icon">🖼️</div>
        <div class="up-txt">Cliquez ou glissez plusieurs images · JPG PNG WEBP</div>
      </div>
      <div class="strip" id="galStrip"></div>
      <div style="margin-top:12px"><button type="submit" class="btn btn-primary btn-sm">✦ Ajouter ces images</button></div>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ══════ VUE LISTE ══════ -->

<div class="page-header">
  <div>
    <h1>Produits</h1>
    <p><?= $totalProducts ?> produit<?= $totalProducts !== 1 ? 's' : '' ?> dans le catalogue</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()">✦ Nouveau produit</button>
</div>

<?php if ($success): ?><div class="toast toast-ok">✓ <?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="toast toast-err">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- KPIs -->
<div class="kpi-row">
  <div class="kpi">
    <div class="kpi-accent" style="background:var(--rose)"></div>
    <div class="kpi-lbl">Total produits</div>
    <div class="kpi-val"><?= $totalProducts ?></div>
    <div class="kpi-icon">✦</div>
  </div>
  <div class="kpi">
    <div class="kpi-accent" style="background:var(--green)"></div>
    <div class="kpi-lbl">Actifs</div>
    <div class="kpi-val"><?= $activeCount ?></div>
    <div class="kpi-icon">✅</div>
  </div>
  <div class="kpi">
    <div class="kpi-accent" style="background:var(--blue)"></div>
    <div class="kpi-lbl">Stock total</div>
    <div class="kpi-val"><?= number_format($totalStock) ?></div>
    <div class="kpi-icon">📦</div>
  </div>
</div>

<!-- TABLE -->
<div class="card">
  <div class="card-hd">
    <span class="card-title">Catalogue</span>
    <span style="font-size:12px;color:var(--muted);font-weight:600"><?= $totalProducts ?> résultat<?= $totalProducts !== 1 ? 's' : '' ?></span>
  </div>
  <div class="card-body">
    <form method="GET" style="display:contents">
      <div class="toolbar">
        <div class="srch">
          <span class="srch-ico">🔍</span>
          <input type="text" name="q" class="fi" placeholder="Rechercher un produit…" value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="cat" class="fi" style="width:170px" onchange="this.form.submit()">
          <option value="">Toutes catégories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $catFilter === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost">Filtrer</button>
        <?php if ($search || $catFilter): ?><a href="admin_products.php" class="btn btn-ghost">✕ Réinitialiser</a><?php endif; ?>
      </div>
    </form>
    <div class="tbl-wrap">
      <table class="tbl">
        <thead><tr>
          <th></th><th>Nom</th><th>Marque</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Teintes</th><th>Statut</th><th style="text-align:right">Actions</th>
        </tr></thead>
        <tbody>
          <?php foreach ($products as $p): ?>
          <tr>
            <td><?php if (!empty($p['image_url'])): ?>
              <img src="<?= htmlspecialchars(imgUrl($b, $p['image_url'])) ?>" class="pimg" alt="" onerror="this.style.display='none'">
            <?php else: ?><div class="pimg-ph">✦</div><?php endif; ?></td>
            <td>
              <div style="font-weight:700;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text)"><?= htmlspecialchars($p['name']) ?></div>
              <?php if ($p['description'] ?? ''): ?><div style="font-size:11px;color:var(--muted);margin-top:2px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($p['description']) ?></div><?php endif; ?>
            </td>
            <td><?php if (!empty($p['marque'])): ?><span class="bdg bdg-brand"><?= htmlspecialchars($p['marque']) ?></span><?php else: ?><span style="color:var(--muted2)">—</span><?php endif; ?></td>
            <td style="color:var(--muted);font-size:12px"><?= $p['categorie'] ? htmlspecialchars($p['categorie']) : '—' ?></td>
            <td style="font-weight:800;color:var(--rose);white-space:nowrap"><?= number_format($p['price'],2,',',' ') ?> DA</td>
            <td><?php $st=(int)$p['stock']; ?><span class="<?= $st<=0?'stk-lo':($st<=5?'stk-md':'stk-ok') ?>"><?= $st ?></span></td>
            <td><?php $sc=$shadeCountMap[$p['id']]??0; if($p['has_shades']): ?><a href="admin_products.php?edit=<?= $p['id'] ?>#teintes" class="bdg bdg-sh">🎨 <?= $sc ?></a><?php else: ?><span style="color:var(--muted2);font-size:12px">—</span><?php endif; ?></td>
            <td><span class="bdg <?= $p['active']?'bdg-ok':'bdg-off' ?>"><?= $p['active']?'Actif':'Inactif' ?></span></td>
            <td><div class="acts">
              <a href="admin_products.php?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">✎ Éditer</a>
              <a href="admin_products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce produit ?')">✕</a>
            </div></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$products): ?>
            <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--muted)">Aucun produit trouvé</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL CRÉATION -->
<div class="overlay" id="addOverlay" onclick="if(event.target===this)closeModal()">
  <div class="modal">
    <div class="m-hd">
      <div class="m-title">Nouveau produit</div>
      <button class="m-close" onclick="closeModal()">✕</button>
    </div>
    <div class="tab-bar">
      <button class="tab-btn active" onclick="showTab('t-info',this)">① Infos <span class="tab-count" id="cnt-info">—</span></button>
      <button class="tab-btn" onclick="showTab('t-teintes',this)">🎨 Teintes <span class="tab-count" id="cnt-sh">0</span></button>
      <button class="tab-btn" onclick="showTab('t-images',this)">🖼 Images <span class="tab-count" id="cnt-img">0</span></button>
    </div>
    <form method="POST" enctype="multipart/form-data" id="createForm">
      <input type="hidden" name="action" value="create_full">
      <input type="hidden" name="shades_data" id="shadesData" value="[]">

      <!-- TAB 1 -->
      <div class="tab-panel active" id="t-info">
        <div class="form-2" style="gap:14px">
          <div class="fg full"><label class="fl">Nom du produit *</label><input type="text" name="name" class="fi" placeholder="Ex: Rouge à lèvres Velvet" required></div>
          <div class="fg full"><label class="fl">Description</label><textarea name="description" class="fi" placeholder="Description courte…"></textarea></div>
          <div class="fg"><label class="fl">Prix (DA) *</label><input type="number" name="price" class="fi" placeholder="0" step="0.01" min="0" required></div>
          <div class="fg"><label class="fl">Stock initial</label><input type="number" name="stock" class="fi" value="0" min="0"></div>
          <div class="fg"><label class="fl">Catégorie</label><input type="text" name="categorie" class="fi" placeholder="Ex: Lèvres, Yeux…" list="addCatList"><datalist id="addCatList"><?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?></datalist></div>
          <div class="fg"><label class="fl">Marque</label><input type="text" name="marque" class="fi" placeholder="Ex: L'Oréal, NYX…" list="addMarqList"><datalist id="addMarqList"><?php foreach ($marques as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?></datalist></div>
          <div class="fg full" style="gap:11px">
            <label class="fl">Options</label>
            <label class="tog"><input type="checkbox" name="has_shades" value="1" id="hasShadesCb"><span class="tog-track"></span><span class="tog-lbl">Produit avec teintes — ajoutez-les dans l'onglet 🎨</span></label>
            <label class="tog"><input type="checkbox" name="active" value="1" checked><span class="tog-track"></span><span class="tog-lbl">Actif dès la création</span></label>
          </div>
          <div class="fg full">
            <label class="fl">Image principale</label>
            <div class="upzone">
              <input type="file" name="image" accept="image/*" onchange="previewImg(this,'addPrev')">
              <div class="up-icon">📷</div>
              <div class="up-txt">Cliquez ou glissez · JPG PNG WEBP · Max 5 Mo</div>
              <img id="addPrev" class="up-prev" alt="">
            </div>
          </div>
        </div>
        <div style="margin-top:18px;display:flex;justify-content:flex-end">
          <button type="button" class="btn btn-primary" onclick="showTab('t-teintes',null)">Suivant → Teintes</button>
        </div>
      </div>

      <!-- TAB 2 -->
      <div class="tab-panel" id="t-teintes">
        <div class="step-hd">Teintes du produit</div>
        <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Ajoutez autant de teintes que nécessaire.</p>
        <div id="mShList" class="m-sh-list"></div>
        <p id="noShMsg" style="font-size:12px;color:var(--muted2);text-align:center;padding:10px 0">Aucune teinte ajoutée.</p>
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:14px;margin-top:4px">
          <div class="step-hd" style="margin-bottom:10px">+ Ajouter une teinte</div>
          <div class="form-3">
            <div class="fg"><label class="fl">Nom</label><input type="text" id="mShNom" class="fi" placeholder="Rose Poudré"></div>
            <div class="fg"><label class="fl">Couleur</label><div class="col-row"><input type="color" id="mShCol" class="fi" value="#c4697a" style="padding:3px;height:38px;cursor:pointer" oninput="document.getElementById('mColSw').style.background=this.value"><div class="col-sw" id="mColSw" style="background:#c4697a"></div></div></div>
            <div class="fg"><label class="fl">Stock</label><input type="number" id="mShStk" class="fi" value="0" min="0"></div>
          </div>
          <div style="margin-top:10px"><button type="button" class="btn btn-primary btn-sm" onclick="addShade()">✦ Ajouter cette teinte</button></div>
        </div>
        <div style="margin-top:18px;display:flex;justify-content:space-between">
          <button type="button" class="btn btn-ghost" onclick="showTab('t-info',null)">← Infos</button>
          <button type="button" class="btn btn-primary" onclick="showTab('t-images',null)">Suivant → Images</button>
        </div>
      </div>

      <!-- TAB 3 -->
      <div class="tab-panel" id="t-images">
        <div class="step-hd">Images supplémentaires (galerie)</div>
        <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Ces images seront affichées comme miniatures sur la page produit.</p>
        <div class="gal-zone">
          <input type="file" name="extra_images[]" accept="image/*" multiple onchange="previewGalleryModal(this)">
          <div class="up-icon">🖼️</div>
          <div class="up-txt">Cliquez ou glissez plusieurs images · Sélection multiple</div>
        </div>
        <div class="strip" id="galStripModal"></div>
        <div style="margin-top:18px;display:flex;justify-content:space-between">
          <button type="button" class="btn btn-ghost" onclick="showTab('t-teintes',null)">← Teintes</button>
          <button type="submit" class="btn btn-primary">✦ Créer le produit</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php endif; ?>
</main>

<script>
function openModal(){document.getElementById('addOverlay').classList.add('open');document.body.style.overflow='hidden';}
function closeModal(){document.getElementById('addOverlay').classList.remove('open');document.body.style.overflow='';}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});

function showTab(id,btn){
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById(id)?.classList.add('active');
  if(btn){btn.classList.add('active');}
  else{const idx=['t-info','t-teintes','t-images'].indexOf(id);document.querySelectorAll('.tab-btn')[idx]?.classList.add('active');}
}

let shades=[];
function addShade(){
  const nom=document.getElementById('mShNom').value.trim();
  const code=document.getElementById('mShCol').value;
  const stk=parseInt(document.getElementById('mShStk').value)||0;
  if(!nom){alert('Entrez un nom pour la teinte.');return;}
  shades.push({nom,code,stock:stk});
  document.getElementById('mShNom').value='';
  document.getElementById('mShStk').value='0';
  renderShades();
  document.getElementById('hasShadesCb').checked=true;
}
function removeShade(i){
  shades.splice(i,1);renderShades();
  if(!shades.length)document.getElementById('hasShadesCb').checked=false;
}
function renderShades(){
  const list=document.getElementById('mShList');
  const msg=document.getElementById('noShMsg');
  const cnt=document.getElementById('cnt-sh');
  document.getElementById('shadesData').value=JSON.stringify(shades);
  cnt.textContent=shades.length;cnt.className=shades.length?'tab-count has':'tab-count';
  if(!shades.length){list.innerHTML='';msg.style.display='block';return;}
  msg.style.display='none';
  list.innerHTML=shades.map((s,i)=>`<div class="m-sh-row">
    <div class="m-sh-sw" style="background:${s.code}"></div>
    <span class="m-sh-nm">${s.nom}</span>
    <span class="m-sh-stk">Stock : ${s.stock}</span>
    <span class="m-sh-code">${s.code}</span>
    <button type="button" class="btn btn-danger btn-sm" onclick="removeShade(${i})">✕</button>
  </div>`).join('');
}

function previewGalleryModal(input){
  const strip=document.getElementById('galStripModal');
  const cnt=document.getElementById('cnt-img');
  if(!strip)return;strip.innerHTML='';
  Array.from(input.files).forEach(f=>{const r=new FileReader();r.onload=e=>{const img=document.createElement('img');img.src=e.target.result;strip.appendChild(img);};r.readAsDataURL(f);});
  cnt.textContent=input.files.length;cnt.className=input.files.length?'tab-count has':'tab-count';
}
function previewGallery(input){
  const strip=document.getElementById('galStrip');if(!strip)return;strip.innerHTML='';
  Array.from(input.files).forEach(f=>{const r=new FileReader();r.onload=e=>{const img=document.createElement('img');img.src=e.target.result;strip.appendChild(img);};r.readAsDataURL(f);});
}
function previewImg(input,id){
  const el=document.getElementById(id);if(!el||!input.files?.[0])return;
  const r=new FileReader();r.onload=e=>{el.src=e.target.result;el.style.display='block';};r.readAsDataURL(input.files[0]);
}
function loadShade(sh){
  document.getElementById('shId').value=sh.id;
  document.getElementById('shNom').value=sh.nom_teinte;
  document.getElementById('shCol').value=sh.code_couleur;
  document.getElementById('shStk').value=sh.stock||0;
  document.getElementById('colSw').style.background=sh.code_couleur;
  document.getElementById('shLbl').textContent='✎ Modifier — '+sh.nom_teinte;
  document.getElementById('shBtn').textContent='✦ Mettre à jour';
  document.querySelector('.sh-form')?.scrollIntoView({behavior:'smooth',block:'center'});
}
function resetShForm(){
  document.getElementById('shId').value='';
  document.getElementById('shNom').value='';
  document.getElementById('shCol').value='#c4697a';
  document.getElementById('shStk').value='0';
  document.getElementById('colSw').style.background='#c4697a';
  document.getElementById('shLbl').textContent='+ Nouvelle teinte';
  document.getElementById('shBtn').textContent='✦ Ajouter';
}
if(location.hash==='#teintes')setTimeout(()=>document.getElementById('teintes')?.scrollIntoView({behavior:'smooth'}),250);
if(location.hash==='#gallery')setTimeout(()=>document.getElementById('gallery')?.scrollIntoView({behavior:'smooth'}),250);
</script>
</body>
</html>