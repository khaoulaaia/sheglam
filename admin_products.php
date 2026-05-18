<?php
// ============================================
//  SheGlamour — Gestion Produits v3.1
//  Mobile-first · Design unifié dashboard
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

function uploadImage(string $fileKey, ?string $oldFile = null): ?string {
    if (empty($_FILES[$fileKey]['name'])) return null;
    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) return null;
    $uploadDir = __DIR__ . '/images/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $filename = uniqid('img_') . '.' . $ext;
    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadDir . $filename)) return null;
    if ($oldFile && !str_starts_with($oldFile, 'http')) {
        $old = __DIR__ . '/images/' . basename($oldFile);
        if (file_exists($old)) unlink($old);
    }
    return $filename;
}

function uploadImageIndexed(string $fileKey, int $idx, ?string $oldFile = null): ?string {
    if (empty($_FILES[$fileKey]['tmp_name'][$idx])) return null;
    if ($_FILES[$fileKey]['error'][$idx] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'][$idx], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) return null;
    $uploadDir = __DIR__ . '/images/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $filename = uniqid('shade_') . '.' . $ext;
    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'][$idx], $uploadDir . $filename)) return null;
    if ($oldFile && !str_starts_with($oldFile, 'http')) {
        $old = __DIR__ . '/images/' . basename($oldFile);
        if (file_exists($old)) unlink($old);
    }
    return $filename;
}

function deleteFile(?string $filename): void {
    if (!$filename || str_starts_with($filename, 'http')) return;
    $full = __DIR__ . '/images/' . basename($filename);
    if (file_exists($full)) unlink($full);
}

// ── Suppression image galerie ────────────────────────────────────────────────
if (isset($_GET['delete_img'])) {
    $iid = (int) $_GET['delete_img'];
    $pid = (int) ($_GET['pid'] ?? 0);
    try {
        $row = $pdo->prepare("SELECT image FROM product_images WHERE id = ?");
        $row->execute([$iid]);
        $imgFile = $row->fetchColumn();
        deleteFile($imgFile);
        $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$iid]);
        header("Location: admin_products.php?edit=$pid&success=" . urlencode("Image supprimée.") . "#gallery");
        exit;
    } catch (Exception $e) { $error = "Erreur suppression image : " . $e->getMessage(); }
}

// ── Suppression teinte ───────────────────────────────────────────────────────
if (isset($_GET['delete_shade'])) {
    $sid = (int) $_GET['delete_shade'];
    $pid = (int) ($_GET['pid'] ?? 0);
    try {
        $imgRow = $pdo->prepare("SELECT image FROM teintes WHERE id = ?");
        $imgRow->execute([$sid]);
        $shImg = $imgRow->fetchColumn();
        deleteFile($shImg);
        $pdo->prepare("DELETE FROM teintes WHERE id = ?")->execute([$sid]);
        header("Location: admin_products.php?edit=$pid&success=" . urlencode("Teinte supprimée.") . "#teintes");
        exit;
    } catch (Exception $e) { $error = "Erreur : " . $e->getMessage(); }
}

// ── Sauvegarde teinte ────────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'save_shade') {
    $pid     = (int)   $_POST['product_id'];
    $sid     = (int)  ($_POST['shade_id']    ?? 0);
    $nom     =  trim($_POST['nom_teinte']    ?? '');
    $code    =  trim($_POST['code_couleur']  ?? '#000000');
    $stockSh = (int)  ($_POST['stock_shade'] ?? 0);
    $prixSh  = (float) str_replace(',', '.', $_POST['prix_shade'] ?? 0);
    $oldImg  =         $_POST['existing_shade_image'] ?? null;
    if (!$nom) {
        $error = "Le nom de la teinte est obligatoire.";
    } else {
        $shadeImage = $oldImg ?: null;
        $newImg = uploadImage('shade_image', $sid ? $oldImg : null);
        if ($newImg) $shadeImage = $newImg;
        try {
            if ($sid) {
                $pdo->prepare("UPDATE teintes SET nom_teinte=?,code_couleur=?,stock=?,prix=?,image=? WHERE id=?")
                    ->execute([$nom, $code, $stockSh, $prixSh ?: null, $shadeImage, $sid]);
                $msg = "Teinte mise à jour.";
            } else {
                $pdo->prepare("INSERT INTO teintes (product_id,nom_teinte,code_couleur,stock,prix,image) VALUES (?,?,?,?,?,?)")
                    ->execute([$pid, $nom, $code, $stockSh, $prixSh ?: null, $shadeImage]);
                $msg = "Teinte ajoutée.";
            }
            header("Location: admin_products.php?edit=$pid&success=" . urlencode($msg) . "#teintes");
            exit;
        } catch (Exception $e) { $error = "Erreur teinte : " . $e->getMessage(); }
    }
}

// ── Upload images galerie ─────────────────────────────────────────────────────
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
                $pdo->prepare("INSERT INTO product_images (product_id,image) VALUES (?,?)")->execute([$pid, $filename]);
                $uploaded++;
            }
        }
    }
    header("Location: admin_products.php?edit=$pid&success=" . urlencode("$uploaded image(s) ajoutée(s).") . "#gallery");
    exit;
}

// ── Suppression produit ───────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $imgRow = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $imgRow->execute([$id]);
        deleteFile($imgRow->fetchColumn());
        $shImgs = $pdo->prepare("SELECT image FROM teintes WHERE product_id = ?");
        $shImgs->execute([$id]);
        foreach ($shImgs->fetchAll(PDO::FETCH_COLUMN) as $si) deleteFile($si);
        $galImgs = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ?");
        $galImgs->execute([$id]);
        foreach ($galImgs->fetchAll(PDO::FETCH_COLUMN) as $gi) deleteFile($gi);
        $pdo->prepare("DELETE FROM teintes WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        header("Location: admin_products.php?success=" . urlencode("Produit supprimé."));
        exit;
    } catch (Exception $e) { $error = "Erreur suppression : " . $e->getMessage(); }
}

// ── Création produit complet ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_full') {
    $name           = trim($_POST['name']           ?? '');
    $description    = trim($_POST['description']    ?? '');
    $price          = (float) str_replace(',', '.', $_POST['price']      ?? 0);
    $old_price      = (float) str_replace(',', '.', $_POST['old_price']  ?? 0);
    $stock          = (int)  ($_POST['stock']        ?? 0);
    $categorie      = trim($_POST['categorie']       ?? '');
    $sous_categorie = trim($_POST['sous_categorie']  ?? '');
    $marque         = trim($_POST['marque']          ?? '');
    $has_shades     = isset($_POST['has_shades'])    ? 1 : 0;
    $active         = isset($_POST['active'])        ? 1 : 0;
    $shadesJson     = $_POST['shades_data']          ?? '[]';
    if (!$name || $price <= 0) {
        $error = "Le nom et le prix sont obligatoires.";
    } else {
        $imageUrl = uploadImage('image');
        if (!$error) {
            try {
                $pdo->prepare("INSERT INTO products (name,description,price,old_price,stock,categorie,sous_categorie,marque,has_shades,active,image_url) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
                    ->execute([$name, $description, $price, $old_price ?: null, $stock, $categorie, $sous_categorie, $marque, $has_shades, $active, $imageUrl]);
                $newId = $pdo->lastInsertId();
                $shades = json_decode($shadesJson, true) ?: [];
                foreach ($shades as $idx => $sh) {
                    $nom  = trim($sh['nom']   ?? '');
                    $code = trim($sh['code']  ?? '#000000');
                    $stSh = (int)   ($sh['stock'] ?? 0);
                    $pxSh = (float) ($sh['prix']  ?? 0);
                    $shImg = uploadImageIndexed('shade_images', $idx);
                    if ($nom) {
                        $pdo->prepare("INSERT INTO teintes (product_id,nom_teinte,code_couleur,stock,prix,image) VALUES (?,?,?,?,?,?)")
                            ->execute([$newId, $nom, $code, $stSh, $pxSh ?: null, $shImg]);
                    }
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
                            $pdo->prepare("INSERT INTO product_images (product_id,image) VALUES (?,?)")->execute([$newId, $fn]);
                    }
                }
                header("Location: admin_products.php?edit=$newId&success=" . urlencode("Produit créé avec succès !"));
                exit;
            } catch (Exception $e) { $error = "Erreur BDD : " . $e->getMessage(); }
        }
    }
}

// ── Mise à jour produit ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_product') {
    $id             = (int)   $_POST['id'];
    $name           = trim($_POST['name']           ?? '');
    $description    = trim($_POST['description']    ?? '');
    $price          = (float) str_replace(',', '.', $_POST['price']     ?? 0);
    $old_price      = (float) str_replace(',', '.', $_POST['old_price'] ?? 0);
    $stock          = (int)  ($_POST['stock']        ?? 0);
    $categorie      = trim($_POST['categorie']       ?? '');
    $sous_categorie = trim($_POST['sous_categorie']  ?? '');
    $marque         = trim($_POST['marque']          ?? '');
    $has_shades     = isset($_POST['has_shades'])    ? 1 : 0;
    $active         = isset($_POST['active'])        ? 1 : 0;
    if (!$name || $price <= 0) { $error = "Le nom et le prix sont obligatoires."; }
    else {
        $existingImg = $_POST['existing_image'] ?? null;
        $newImg      = uploadImage('image', $existingImg);
        $imageUrl    = $newImg ?: $existingImg;
        try {
            $pdo->prepare("UPDATE products SET name=?,description=?,price=?,old_price=?,stock=?,categorie=?,sous_categorie=?,marque=?,has_shades=?,active=?,image_url=? WHERE id=?")
                ->execute([$name, $description, $price, $old_price ?: null, $stock, $categorie, $sous_categorie, $marque, $has_shades, $active, $imageUrl, $id]);
            header("Location: admin_products.php?edit=$id&success=" . urlencode("Produit mis à jour."));
            exit;
        } catch (Exception $e) { $error = "Erreur BDD : " . $e->getMessage(); }
    }
}

if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

// ── Chargement vue édition ────────────────────────────────────────────────────
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

// ── Liste produits ────────────────────────────────────────────────────────────
$search        = trim($_GET['q']        ?? '');
$catFilter     = trim($_GET['cat']      ?? '');
$sousCatFilter = trim($_GET['sous_cat'] ?? '');
$where = []; $params = [];
if ($search)        { $where[] = "(name ILIKE ? OR description ILIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilter)     { $where[] = "categorie = ?";       $params[] = $catFilter; }
if ($sousCatFilter) { $where[] = "sous_categorie = ?";  $params[] = $sousCatFilter; }
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

$categories      = $pdo->query("SELECT DISTINCT categorie      FROM products WHERE categorie      IS NOT NULL AND categorie      != '' ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);
$sous_categories = $pdo->query("SELECT DISTINCT sous_categorie FROM products WHERE sous_categorie IS NOT NULL AND sous_categorie != '' ORDER BY sous_categorie")->fetchAll(PDO::FETCH_COLUMN);
$marques         = $pdo->query("SELECT DISTINCT marque         FROM products WHERE marque         IS NOT NULL AND marque         != '' ORDER BY marque")->fetchAll(PDO::FETCH_COLUMN);
$totalProducts   = count($products);
$totalStock      = array_sum(array_column($products, 'stock'));
$activeCount     = count(array_filter($products, fn($p) => $p['active']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>SheGlamour — Produits</title>

<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%23c4697a'/%3E%3Cpath d='M16 4C10 4 5 9 5 15c0 4 2 7 5 9v8h12v-8c3-2 5-5 5-9 0-6-5-11-11-11z' fill='%23f5c6cd'/%3E%3Ccircle cx='16' cy='15' r='5' fill='%23fff'/%3E%3Crect x='12' y='23' width='8' height='2' rx='1' fill='%23fff' opacity='.8'/%3E%3C/svg%3E">

<!-- PWA — Écran d'accueil mobile -->
<link rel="manifest" href="/manifest.json">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="SheGlamour">
<meta name="theme-color" content="#c4697a">
<!-- Apple touch icon (180×180) encodé SVG -->
<link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 180'%3E%3Ccircle cx='90' cy='90' r='90' fill='%23c4697a'/%3E%3Cpath d='M90 26C62 26 40 48 40 76c0 16 7 30 19 40v40h62v-40c12-10 19-24 19-40 0-28-22-50-50-50z' fill='%23f5c6cd'/%3E%3Cpath d='M90 50c-14 0-26 12-26 26 0 9 4 17 11 22v35h30v-35c7-5 11-13 11-22 0-14-12-26-26-26z' fill='%23e8899a'/%3E%3Ccircle cx='90' cy='76' r='14' fill='%23fff'/%3E%3Crect x='72' y='130' width='36' height='8' rx='4' fill='%23fff' opacity='.8'/%3E%3C/svg%3E">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">

<style>
/* ── TOKENS ──────────────────────────────────────────────────────────────── */
:root {
  --bg: #f9f5f2;
  --surface: #ffffff;
  --surface2: #f4eeea;
  --surface3: #ede7e0;
  --border: #ede5de;
  --border2: #dfd4ca;

  --text: #16100e;
  --text2: #4a3c36;
  --muted: #9c8d85;
  --muted2: #c0afa6;

  --rose: #c4697a;  --rose-d: #a8505f;
  --rose-bg: #fdf0f2; --rose-lt: #f5d0d7;

  --plum: #8b5a8b;
  --plum-bg: #f5eef5; --plum-lt: #dfc8df;

  --green: #3a8a5c;
  --green-bg: #eef7f2; --green-lt: #b8dfc9;

  --amber: #b07030;
  --amber-bg: #fdf5eb; --amber-lt: #f0d4a8;

  --blue: #3a6db0;
  --blue-bg: #eef3fb; --blue-lt: #b8cff0;

  --red: #c0392b;
  --red-bg: #fdf0ee; --red-lt: #f0c0bb;

  --sidebar-w: 240px;
  --topbar-h: 60px;
  --r: 16px; --r-sm: 10px;
  --shadow: 0 2px 8px rgba(0,0,0,.06), 0 8px 24px rgba(0,0,0,.05);
  --shadow-sm: 0 1px 4px rgba(0,0,0,.05);
}

/* ── RESET ───────────────────────────────────────────────────────────────── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
  background: var(--bg); color: var(--text);
  font-family: 'DM Sans', sans-serif; font-size: 14px;
  line-height: 1.5; min-height: 100vh;
}

/* ── SIDEBAR ─────────────────────────────────────────────────────────────── */
.sidebar {
  position: fixed; top: 0; left: 0;
  width: var(--sidebar-w); height: 100vh;
  background: var(--surface); border-right: 1px solid var(--border);
  display: flex; flex-direction: column;
  z-index: 200; overflow-y: auto; overscroll-behavior: contain;
  transition: transform .3s cubic-bezier(.4,0,.2,1);
}
.sidebar-logo {
  padding: 28px 24px 22px;
  display: flex; align-items: center; gap: 11px;
  border-bottom: 1px solid var(--border); flex-shrink: 0;
}
.sidebar-logo svg { width: 34px; height: 34px; flex-shrink: 0; }
.logo-text { font-family: 'Cormorant Garamond', serif; font-size: 22px; color: var(--text); letter-spacing: -.01em; }
.logo-text span { color: var(--rose); }

.sidebar-section { padding: 20px 20px 7px; font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted2); }
.sidebar-nav { display: flex; flex-direction: column; gap: 2px; padding: 0 12px; flex: 1; }
.nav-link { display: flex; align-items: center; gap: 11px; padding: 10px 13px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 500; color: var(--muted); text-decoration: none; transition: all .15s; }
.nav-link:hover { background: var(--surface2); color: var(--text2); }
.nav-link.active { background: var(--rose-bg); color: var(--rose); font-weight: 600; }
.nav-link .ico { font-size: 15px; width: 20px; text-align: center; flex-shrink: 0; }
.nav-badge { margin-left: auto; background: var(--amber); color: #fff; border-radius: 10px; padding: 1px 7px; font-size: 10px; font-weight: 700; }

.sidebar-footer { padding: 16px 20px; border-top: 1px solid var(--border); font-size: 11px; color: var(--muted2); display: flex; justify-content: space-between; flex-shrink: 0; }
.logout-link { color: var(--muted); text-decoration: none; font-weight: 600; font-size: 11px; transition: color .15s; }
.logout-link:hover { color: var(--red); }

/* Overlay mobile */
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(22,16,14,.35); z-index: 190; backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px); }
.sidebar-overlay.active { display: block; }

/* ── TOPBAR MOBILE ───────────────────────────────────────────────────────── */
.topbar { display: none; position: fixed; top: 0; left: 0; right: 0; height: var(--topbar-h); background: var(--surface); border-bottom: 1px solid var(--border); align-items: center; justify-content: space-between; padding: 0 18px; z-index: 180; box-shadow: var(--shadow-sm); }
.topbar-logo { display: flex; align-items: center; gap: 9px; font-family: 'Cormorant Garamond', serif; font-size: 20px; color: var(--text); }
.topbar-logo svg { width: 28px; height: 28px; }
.topbar-logo span { color: var(--rose); }
.hamburger { width: 40px; height: 40px; border: 1.5px solid var(--border); border-radius: 10px; background: var(--surface); cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; transition: background .15s; }
.hamburger:hover { background: var(--surface2); }
.hamburger span { display: block; width: 18px; height: 1.5px; background: var(--text2); border-radius: 2px; transition: transform .3s, opacity .3s; }
.hamburger.open span:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
.hamburger.open span:nth-child(2) { opacity: 0; }
.hamburger.open span:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }

/* ── MAIN ────────────────────────────────────────────────────────────────── */
.main { margin-left: var(--sidebar-w); padding: 40px 36px; min-height: 100vh; }

/* ── PAGE HEADER ─────────────────────────────────────────────────────────── */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin-bottom: 28px; }
.page-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 36px; letter-spacing: -.02em; line-height: 1; }
.page-header p { color: var(--muted); font-size: 13px; margin-top: 4px; }

/* ── TOASTS ──────────────────────────────────────────────────────────────── */
.toast { padding: 12px 18px; border-radius: var(--r-sm); font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 9px; animation: fadeUp .3s both; }
.toast-ok  { background: var(--green-bg); border: 1px solid var(--green-lt); color: var(--green); }
.toast-err { background: var(--red-bg);   border: 1px solid var(--red-lt);   color: var(--red); }

/* ── KPI ─────────────────────────────────────────────────────────────────── */
.kpi-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; margin-bottom: 22px; }
.kpi-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 20px 22px 18px; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); transition: box-shadow .2s, transform .2s; }
.kpi-card:hover { box-shadow: var(--shadow); transform: translateY(-2px); }
.kpi-accent { position: absolute; top: 0; left: 0; width: 3px; height: 100%; border-radius: 16px 0 0 16px; }
.kpi-label { font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
.kpi-value { font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: var(--text); line-height: 1; }
.kpi-icon { position: absolute; top: 16px; right: 16px; font-size: 24px; opacity: .14; }

/* ── CARD ────────────────────────────────────────────────────────────────── */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 22px; }
.card-head { padding: 16px 22px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); background: var(--surface2); flex-wrap: wrap; gap: 8px; }
.card-title { font-size: 10.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
.card-body { padding: 20px 22px; }

/* ── BREADCRUMB ──────────────────────────────────────────────────────────── */
.bc { display: flex; align-items: center; gap: 7px; font-size: 12px; color: var(--muted); margin-bottom: 18px; flex-wrap: wrap; }
.bc a { color: var(--muted); text-decoration: none; font-weight: 600; transition: color .15s; }
.bc a:hover { color: var(--rose); }

/* ── BUTTONS ─────────────────────────────────────────────────────────────── */
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--r-sm); font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 700; cursor: pointer; border: none; transition: all .15s; text-decoration: none; white-space: nowrap; }
.btn-primary { background: var(--rose); color: #fff; }
.btn-primary:hover { background: var(--rose-d); transform: translateY(-1px); }
.btn-ghost { background: var(--surface); color: var(--text2); border: 1.5px solid var(--border); }
.btn-ghost:hover { border-color: var(--rose); color: var(--rose); background: var(--rose-bg); }
.btn-danger { background: var(--red-bg); color: var(--red); border: 1.5px solid var(--red-lt); }
.btn-danger:hover { background: #f9dbd8; }
.btn-sm { padding: 5px 12px; font-size: 11px; border-radius: 7px; }

/* ── FORMS ───────────────────────────────────────────────────────────────── */
.fg { display: flex; flex-direction: column; gap: 5px; }
.fg.full { grid-column: 1 / -1; }
.fl { font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
.fi { background: var(--surface); border: 1.5px solid var(--border); border-radius: 9px; padding: 9px 13px; color: var(--text); font-family: 'DM Sans', sans-serif; font-size: 13px; width: 100%; transition: border-color .15s; }
.fi:focus { outline: none; border-color: var(--rose); }
.fi::placeholder { color: var(--muted2); }
textarea.fi { resize: vertical; min-height: 80px; }
.form-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }

/* ── TOGGLE ──────────────────────────────────────────────────────────────── */
.tog { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
.tog input { width: 0; height: 0; opacity: 0; position: absolute; }
.tog-track { width: 40px; height: 22px; background: var(--border); border: 1.5px solid var(--border2); border-radius: 11px; position: relative; transition: all .18s; flex-shrink: 0; }
.tog-track::after { content: ''; position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; background: var(--muted2); border-radius: 50%; transition: all .18s; }
.tog input:checked + .tog-track { background: var(--rose-bg); border-color: var(--rose-lt); }
.tog input:checked + .tog-track::after { left: 20px; background: var(--rose); }
.tog-lbl { font-size: 13px; color: var(--text2); font-weight: 500; }

/* ── UPLOAD ZONE ─────────────────────────────────────────────────────────── */
.upzone { border: 2px dashed var(--border2); border-radius: var(--r-sm); padding: 22px; text-align: center; cursor: pointer; transition: all .18s; position: relative; background: var(--surface2); }
.upzone:hover { border-color: var(--rose); background: var(--rose-bg); }
.upzone input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.up-icon { font-size: 24px; margin-bottom: 5px; opacity: .4; }
.up-txt { font-size: 11px; color: var(--muted); font-weight: 600; }
.up-prev { width: 100%; max-height: 120px; object-fit: contain; border-radius: 8px; margin-top: 10px; display: none; border: 1px solid var(--border); }

/* ── TOOLBAR ─────────────────────────────────────────────────────────────── */
.toolbar { display: flex; gap: 10px; margin-bottom: 18px; align-items: center; flex-wrap: wrap; }
.srch { position: relative; flex: 1; min-width: 150px; }
.srch .fi { padding-left: 34px; }
.srch-ico { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; pointer-events: none; }

/* ── TABLE ───────────────────────────────────────────────────────────────── */
.tbl-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.tbl { width: 100%; border-collapse: collapse; min-width: 860px; }
.tbl th { font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); padding: 0 10px 13px; text-align: left; border-bottom: 2px solid var(--border); white-space: nowrap; }
.tbl th:first-child { padding-left: 0; }
.tbl td { padding: 11px 10px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 13px; color: var(--text2); }
.tbl td:first-child { padding-left: 0; }
.tbl tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: var(--surface2); }
.pimg { width: 42px; height: 42px; border-radius: 9px; object-fit: cover; border: 1px solid var(--border); }
.pimg-ph { width: 42px; height: 42px; border-radius: 9px; background: var(--surface2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--muted); }
.acts { display: flex; gap: 6px; justify-content: flex-end; }

/* ── BADGES ──────────────────────────────────────────────────────────────── */
.bdg { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 800; letter-spacing: .04em; }
.bdg-ok    { background: var(--green-bg); color: var(--green); border: 1px solid var(--green-lt); }
.bdg-off   { background: var(--surface2); color: var(--muted); border: 1px solid var(--border); }
.bdg-sh    { background: var(--plum-bg);  color: var(--plum);  border: 1px solid var(--plum-lt);  text-decoration: none; transition: background .15s; }
.bdg-sh:hover { background: var(--plum-lt); }
.bdg-brand { background: var(--blue-bg);  color: var(--blue);  border: 1px solid var(--blue-lt); }
.bdg-scat  { background: var(--amber-bg); color: var(--amber); border: 1px solid var(--amber-lt); }
.stk-lo { color: var(--red);   font-weight: 800; }
.stk-md { color: var(--amber); font-weight: 800; }
.stk-ok { color: var(--green); font-weight: 800; }

/* ── EDIT LAYOUT ─────────────────────────────────────────────────────────── */
.edit-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; align-items: start; margin-bottom: 22px; }
.edit-layout .card { margin-bottom: 0; }

/* ── TEINTES ─────────────────────────────────────────────────────────────── */
.sh-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px,1fr)); gap: 8px; margin-bottom: 16px; }
.sh-card { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--r-sm); padding: 10px 12px; display: flex; align-items: center; gap: 9px; transition: box-shadow .15s; }
.sh-card:hover { box-shadow: var(--shadow-sm); }
.sh-sw  { width: 30px; height: 30px; border-radius: 7px; flex-shrink: 0; border: 2px solid rgba(0,0,0,.08); }
.sh-img { width: 30px; height: 30px; border-radius: 7px; object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; }
.sh-info { flex: 1; min-width: 0; }
.sh-nm   { font-weight: 700; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text); }
.sh-meta { font-size: 10px; color: var(--muted); margin-top: 2px; }
.sh-acts { display: flex; gap: 4px; flex-shrink: 0; }

.sh-form { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--r-sm); padding: 18px; }
.sh-form-title { font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
.col-row { display: flex; align-items: center; gap: 9px; }
.col-sw  { width: 34px; height: 34px; border-radius: 7px; border: 2px solid rgba(0,0,0,.08); flex-shrink: 0; }
.sh-img-preview { display: flex; align-items: center; gap: 8px; margin-top: 8px; padding: 8px 10px; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; }
.sh-img-preview img { width: 38px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); }
.sh-img-preview span { font-size: 11px; color: var(--muted); }

/* ── INFO BOX ────────────────────────────────────────────────────────────── */
.info-box { background: var(--surface2); border: 1px solid var(--border); border-radius: var(--r-sm); padding: 12px 15px; font-size: 11px; color: var(--muted); line-height: 1.7; margin-top: 14px; }
.info-box strong { color: var(--text2); display: block; margin-bottom: 3px; font-weight: 700; }
code { color: var(--rose); font-size: 10px; font-family: monospace; }

/* ── GALERIE ─────────────────────────────────────────────────────────────── */
.gal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px,1fr)); gap: 8px; margin-bottom: 14px; }
.gal-item { position: relative; border-radius: 9px; overflow: hidden; border: 1px solid var(--border); aspect-ratio: 1; background: var(--surface2); }
.gal-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gal-del { position: absolute; top: 4px; right: 4px; background: rgba(255,255,255,.9); color: var(--red); border: 1px solid var(--red-lt); border-radius: 5px; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer; text-decoration: none; transition: all .15s; font-weight: 800; }
.gal-del:hover { background: var(--red-bg); }
.gal-zone { border: 2px dashed var(--border2); border-radius: var(--r-sm); padding: 18px; text-align: center; cursor: pointer; transition: all .18s; position: relative; background: var(--surface2); }
.gal-zone:hover { border-color: var(--rose); background: var(--rose-bg); }
.gal-zone input { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }

/* ── MODAL ───────────────────────────────────────────────────────────────── */
/* Overlay — pas de backdrop-filter (casse les touch events iOS sur les enfants fixed) */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(22,16,14,.5);
  z-index: 300;
  display: none;
  overflow-y: auto;
  padding: 28px 16px 40px;
}
.modal-overlay.open { display: block; }

.modal {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 22px; width: 100%; max-width: 820px;
  margin: 0 auto; position: relative;
  box-shadow: var(--shadow); animation: fadeUp .3s both;
}
.modal-head {
  padding: 22px 26px 18px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  background: var(--surface2); border-radius: 22px 22px 0 0;
  /* sticky : le bouton ✕ reste visible quand on scrolle la modal */
  position: sticky; top: 0; z-index: 10;
}
.modal-title { font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 700; color: var(--text); }
.modal-close {
  background: var(--surface); border: 1.5px solid var(--border);
  color: var(--muted); width: 40px; height: 40px; border-radius: 8px;
  cursor: pointer; font-size: 16px;
  display: flex; align-items: center; justify-content: center;
  transition: all .15s; font-weight: 800;
  /* surface tactile suffisante sur mobile */
  touch-action: manipulation; -webkit-tap-highlight-color: transparent;
}
.modal-close:hover, .modal-close:active { color: var(--red); border-color: var(--red-lt); background: var(--red-bg); }

/* ── TABS ────────────────────────────────────────────────────────────────── */
.tab-bar { display: flex; border-bottom: 1px solid var(--border); padding: 0 26px; background: var(--surface); overflow-x: auto; }
.tab-btn { padding: 12px 16px; font-size: 13px; font-weight: 700; color: var(--muted); cursor: pointer; border: none; background: none; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all .15s; display: flex; align-items: center; gap: 7px; font-family: 'DM Sans', sans-serif; white-space: nowrap; }
.tab-btn.active { color: var(--rose); border-bottom-color: var(--rose); }
.tab-btn:hover:not(.active) { color: var(--text); }
.tab-panel { display: none; padding: 22px 26px; }
.tab-panel.active { display: block; }
.tab-count { background: var(--surface2); color: var(--muted); font-size: 10px; font-weight: 800; padding: 1px 7px; border-radius: 20px; border: 1px solid var(--border); }
.tab-count.has { background: var(--rose-bg); color: var(--rose); border-color: var(--rose-lt); }

/* ── SHADE LIST MODAL ────────────────────────────────────────────────────── */
.m-sh-list { display: flex; flex-direction: column; gap: 7px; margin-bottom: 14px; }
.m-sh-row { display: grid; grid-template-columns: 34px 1fr auto auto auto; gap: 8px; align-items: center; background: var(--surface2); border: 1px solid var(--border); border-radius: 9px; padding: 9px 12px; }
.m-sh-thumb { width: 30px; height: 30px; border-radius: 6px; object-fit: cover; border: 1px solid var(--border); }
.m-sh-sw   { width: 30px; height: 30px; border-radius: 6px; border: 2px solid rgba(0,0,0,.08); flex-shrink: 0; }
.m-sh-nm   { font-size: 13px; font-weight: 700; color: var(--text); }
.m-sh-stk  { font-size: 11px; color: var(--muted); }
.m-sh-code { font-size: 10px; color: var(--muted); font-family: monospace; }

.shade-file-strip { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px; }
.shade-file-strip .sf-item { position: relative; width: 52px; }
.shade-file-strip .sf-item img { width: 52px; height: 52px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); }
.shade-file-strip .sf-item span { display: block; font-size: 9px; color: var(--muted); text-align: center; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.img-strip { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
.img-strip img { width: 54px; height: 54px; object-fit: cover; border-radius: 7px; border: 1px solid var(--border); }

/* ── MOBILE PRODUCT CARDS ────────────────────────────────────────────────── */
.mobile-products { display: none; }
.m-prod-card { border: 1px solid var(--border); border-radius: 12px; padding: 14px 14px; margin-bottom: 10px; background: var(--surface); display: flex; gap: 12px; align-items: flex-start; }
.m-prod-img  { width: 52px; height: 52px; border-radius: 9px; object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; }
.m-prod-ph   { width: 52px; height: 52px; border-radius: 9px; background: var(--surface2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--muted); flex-shrink: 0; }
.m-prod-body { flex: 1; min-width: 0; }
.m-prod-name { font-size: 14px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.m-prod-meta { font-size: 12px; color: var(--muted); margin-top: 3px; }
.m-prod-foot { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; gap: 8px; flex-wrap: wrap; }
.m-prod-price { font-size: 14px; font-weight: 800; color: var(--rose); }
.m-prod-acts { display: flex; gap: 6px; }

/* ── ANIMATIONS ──────────────────────────────────────────────────────────── */
@keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.card, .kpi-card { animation: fadeUp .4s both; }
.kpi-card:nth-child(1) { animation-delay: .05s; }
.kpi-card:nth-child(2) { animation-delay: .10s; }
.kpi-card:nth-child(3) { animation-delay: .15s; }

/* ── RESPONSIVE ──────────────────────────────────────────────────────────── */
@media (max-width: 900px) {
  .sidebar { transform: translateX(calc(-1 * var(--sidebar-w))); box-shadow: 4px 0 24px rgba(0,0,0,.12); }
  .sidebar.open { transform: translateX(0); }
  .topbar { display: flex; }
  .main { margin-left: 0; padding: calc(var(--topbar-h) + 20px) 16px 32px; }

  .kpi-grid     { grid-template-columns: 1fr 1fr; gap: 10px; }
  .kpi-value    { font-size: 26px; }
  .kpi-icon     { display: none; }
  .edit-layout  { grid-template-columns: 1fr; }
  .form-2, .form-3 { grid-template-columns: 1fr; }
  .fg.full      { grid-column: 1; }

  /* Table → mobile cards */
  .desktop-tbl      { display: none; }
  .mobile-products  { display: block; }

  .toolbar select { min-width: 0; flex: 1; }
  .page-header h1 { font-size: 28px; }
}

@media (max-width: 580px) {
  /* Bottom sheet : l'overlay est le conteneur flex, la modal est un enfant normal (pas fixed) */
  .modal-overlay {
    padding: 0;
    display: none;
    flex-direction: column;
    justify-content: flex-end;
    overflow: hidden; /* pas de scroll sur l'overlay, scroll interne à la modal */
  }
  .modal-overlay.open { display: flex; }

  .modal {
    /* NE PAS mettre position:fixed ici — c'est ce qui cassait les touch events iOS */
    border-radius: 20px 20px 0 0;
    max-width: 100%;
    max-height: 92dvh;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    animation: slideUp .3s cubic-bezier(.4,0,.2,1) both;
    margin: 0; /* annule le margin:auto du desktop */
  }
  .modal-head {
    border-radius: 20px 20px 0 0;
    padding: 18px 18px 14px;
  }
  .tab-panel { padding: 16px 18px; }
  .tab-bar { padding: 0 18px; }

  .m-sh-row { grid-template-columns: 34px 1fr auto auto; }
  .m-sh-code { display: none; }
  .form-3 { grid-template-columns: 1fr 1fr; }
}

@keyframes slideUp {
  from { transform: translateY(100%); opacity: .6; }
  to   { transform: translateY(0);    opacity: 1;  }
}

@media (max-width: 420px) {
  .kpi-grid { grid-template-columns: 1fr 1fr; }
  .kpi-value { font-size: 22px; }
  .main { padding-left: 12px; padding-right: 12px; }
}
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
    <span class="logo-text">She<span>Glamour</span></span>
  </div>

  <p class="sidebar-section">Navigation</p>
  <nav class="sidebar-nav">
    <a class="nav-link" href="dashboard.php"><span class="ico">◈</span> Tableau de bord</a>
    <a class="nav-link" href="admin_orders.php"><span class="ico">📦</span> Commandes</a>
    <a class="nav-link active" href="admin_products.php"><span class="ico">✦</span> Produits</a>
    <a class="nav-link" href="index.php" target="_blank"><span class="ico">↗</span> Voir la boutique</a>
  </nav>
  <div class="sidebar-footer">
    <span>v3.1</span>
    <a href="dashboard.php?logout=1" class="logout-link">Déconnexion</a>
  </div>
</aside>

<!-- ── MAIN ───────────────────────────────────────────────────────────────── -->
<main class="main">

<?php if ($editProduct): ?>
<!-- ══════════════════════════════════════════
     VUE ÉDITION
══════════════════════════════════════════ -->
<nav class="bc">
  <a href="admin_products.php">✦ Produits</a>
  <span>›</span>
  <span style="color:var(--text)"><?= htmlspecialchars($editProduct['name']) ?></span>
</nav>

<?php if ($success): ?><div class="toast toast-ok">✓ <?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="toast toast-err">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="page-header">
  <div>
    <h1>Modifier le produit</h1>
    <p>ID #<?= $editProduct['id'] ?> · créé le <?= date('d/m/Y', strtotime($editProduct['created_at'] ?? 'now')) ?></p>
  </div>
  <a href="admin_products.php" class="btn btn-ghost">← Retour</a>
</div>

<!-- GRILLE ÉDITION -->
<div class="edit-layout">

  <!-- Fiche produit -->
  <div class="card">
    <div class="card-head"><span class="card-title">Informations produit</span></div>
    <div class="card-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action"         value="update_product">
        <input type="hidden" name="id"             value="<?= $editProduct['id'] ?>">
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
            <label class="fl">Ancien prix (DA)</label>
            <input type="number" name="old_price" class="fi" step="0.01" min="0" value="<?= $editProduct['old_price'] ?? '' ?>" placeholder="Barré si > prix">
          </div>
          <div class="fg">
            <label class="fl">Stock global</label>
            <input type="number" name="stock" class="fi" min="0" value="<?= (int)$editProduct['stock'] ?>">
          </div>
          <div class="fg">
            <label class="fl">Marque</label>
            <input type="text" name="marque" class="fi" value="<?= htmlspecialchars($editProduct['marque'] ?? '') ?>" list="marqList">
            <datalist id="marqList"><?php foreach ($marques as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg">
            <label class="fl">Catégorie</label>
            <input type="text" name="categorie" class="fi" value="<?= htmlspecialchars($editProduct['categorie'] ?? '') ?>" list="catList">
            <datalist id="catList"><?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg">
            <label class="fl">Sous-catégorie</label>
            <input type="text" name="sous_categorie" class="fi" value="<?= htmlspecialchars($editProduct['sous_categorie'] ?? '') ?>" list="sousCatList">
            <datalist id="sousCatList"><?php foreach ($sous_categories as $sc): ?><option value="<?= htmlspecialchars($sc) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg full" style="gap:12px">
            <label class="fl">Options</label>
            <label class="tog"><input type="checkbox" name="has_shades" value="1" <?= $editProduct['has_shades'] ? 'checked' : '' ?>><span class="tog-track"></span><span class="tog-lbl">Produit avec teintes 🎨</span></label>
            <label class="tog"><input type="checkbox" name="active" value="1" <?= $editProduct['active'] ? 'checked' : '' ?>><span class="tog-track"></span><span class="tog-lbl">Actif sur la boutique</span></label>
          </div>
          <div class="fg full">
            <label class="fl">Remplacer l'image principale</label>
            <div class="upzone">
              <input type="file" name="image" accept="image/*" onchange="previewImg(this,'eprev')">
              <div class="up-icon">📷</div>
              <div class="up-txt">Cliquer ou glisser · JPG PNG WEBP</div>
              <img id="eprev" class="up-prev" alt="">
            </div>
            <?php if ($editProduct['image_url']): ?>
            <div style="margin-top:8px;display:flex;align-items:center;gap:10px">
              <img src="<?= htmlspecialchars(imgUrl($b, $editProduct['image_url'])) ?>" style="height:48px;border-radius:8px;border:1px solid var(--border)" alt="">
              <span style="font-size:11px;color:var(--muted)">Image actuelle</span>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <button type="submit" class="btn btn-primary">✦ Enregistrer</button>
          <a href="admin_products.php?delete=<?= $editProduct['id'] ?>" class="btn btn-danger" onclick="return confirm('Supprimer ce produit et toutes ses données ?')">✕ Supprimer</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Teintes -->
  <div id="teintes">
    <div class="card">
      <div class="card-head">
        <span class="card-title">Teintes (<?= count($editTeintes) ?>)</span>
        <?php if ($editProduct['has_shades']): ?><span style="font-size:11px;color:var(--green);font-weight:700">✓ Actives</span><?php endif; ?>
      </div>
      <div class="card-body">
        <?php if (!$editProduct['has_shades']): ?>
          <div style="text-align:center;padding:28px 0;color:var(--muted)">
            <div style="font-size:34px;margin-bottom:10px;opacity:.2">🎨</div>
            <p style="font-size:13px;line-height:1.6">Activez <strong style="color:var(--text2)">"Produit avec teintes"</strong><br>puis enregistrez.</p>
          </div>
        <?php else: ?>
          <?php if ($editTeintes): ?>
          <div class="sh-grid">
            <?php foreach ($editTeintes as $t):
              $tImgUrl = !empty($t['image']) ? imgUrl($b, $t['image']) : null;
            ?>
            <div class="sh-card">
              <?php if ($tImgUrl): ?>
                <img src="<?= htmlspecialchars($tImgUrl) ?>" class="sh-img" alt="<?= htmlspecialchars($t['nom_teinte']) ?>">
              <?php else: ?>
                <div class="sh-sw" style="background:<?= htmlspecialchars($t['code_couleur'] ?? '#ccc') ?>"></div>
              <?php endif; ?>
              <div class="sh-info">
                <div class="sh-nm"><?= htmlspecialchars($t['nom_teinte']) ?></div>
                <div class="sh-meta">Stock : <?= (int)($t['stock'] ?? 0) ?><?php if (!empty($t['prix']) && $t['prix'] > 0): ?> · <?= number_format((float)$t['prix'],2,',',' ') ?> DA<?php endif; ?><?php if ($tImgUrl): ?> · <span style="color:var(--plum)">📷</span><?php endif; ?></div>
              </div>
              <div class="sh-acts">
                <button class="btn btn-ghost btn-sm" onclick='loadShade(<?= json_encode($t, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>✎</button>
                <a href="admin_products.php?delete_shade=<?= $t['id'] ?>&pid=<?= $editProduct['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette teinte ?')">✕</a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
            <p style="color:var(--muted);font-size:13px;text-align:center;padding:8px 0 16px">Aucune teinte. Ajoutez-en une ci-dessous.</p>
          <?php endif; ?>

          <!-- Formulaire teinte -->
          <div class="sh-form">
            <div class="sh-form-title" id="shLbl">+ Nouvelle teinte</div>
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="action"               value="save_shade">
              <input type="hidden" name="product_id"           value="<?= $editProduct['id'] ?>">
              <input type="hidden" name="shade_id"             id="shId"          value="<?= $editShade ? $editShade['id'] : '' ?>">
              <input type="hidden" name="existing_shade_image" id="shExistingImg" value="<?= $editShade ? htmlspecialchars($editShade['image'] ?? '') : '' ?>">

              <div class="form-3">
                <div class="fg">
                  <label class="fl">Nom *</label>
                  <input type="text" name="nom_teinte" id="shNom" class="fi" placeholder="Rose Poudré" value="<?= $editShade ? htmlspecialchars($editShade['nom_teinte']) : '' ?>" required>
                </div>
                <div class="fg">
                  <label class="fl">Couleur</label>
                  <div class="col-row">
                    <input type="color" name="code_couleur" id="shCol" class="fi" value="<?= $editShade ? htmlspecialchars($editShade['code_couleur']) : '#c4697a' ?>" style="padding:3px;height:38px;cursor:pointer" oninput="document.getElementById('colSw').style.background=this.value">
                    <div class="col-sw" id="colSw" style="background:<?= $editShade ? htmlspecialchars($editShade['code_couleur']) : '#c4697a' ?>"></div>
                  </div>
                </div>
                <div class="fg">
                  <label class="fl">Stock</label>
                  <input type="number" name="stock_shade" id="shStk" class="fi" min="0" value="<?= $editShade ? (int)$editShade['stock'] : 0 ?>">
                </div>
                <div class="fg">
                  <label class="fl">Prix spécifique (DA)</label>
                  <input type="number" name="prix_shade" id="shPrix" class="fi" step="0.01" min="0" value="<?= $editShade ? (float)($editShade['prix'] ?? 0) : 0 ?>" placeholder="0 = prix produit">
                </div>
                <div class="fg" style="grid-column:span 2">
                  <label class="fl">Image de la teinte</label>
                  <div class="upzone" style="padding:14px">
                    <input type="file" name="shade_image" accept="image/*" onchange="previewImg(this,'shImgPrev')">
                    <div class="up-icon" style="font-size:18px">🎨</div>
                    <div class="up-txt">Photo associée · JPG PNG WEBP</div>
                    <img id="shImgPrev" class="up-prev" alt="">
                  </div>
                  <?php if ($editShade && !empty($editShade['image'])): ?>
                  <div class="sh-img-preview" id="shCurrentImgWrap">
                    <img src="<?= htmlspecialchars(imgUrl($b, $editShade['image'])) ?>" alt="">
                    <span>Image actuelle</span>
                  </div>
                  <?php else: ?>
                  <div class="sh-img-preview" id="shCurrentImgWrap" style="display:none">
                    <img id="shCurrentImg" src="" alt="">
                    <span>Image actuelle</span>
                  </div>
                  <?php endif; ?>
                </div>
              </div>

              <div style="margin-top:14px;display:flex;gap:9px">
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
      Teintes lues via <code>get_shades.php?product_id=<?= $editProduct['id'] ?></code> · S'affiche si <code>has_shades=1</code><br>
      L'image de chaque teinte remplace l'image principale quand la teinte est sélectionnée.
    </div>
  </div>
</div>

<!-- Galerie -->
<div id="gallery" class="card">
  <div class="card-head">
    <span class="card-title">Galerie — images supplémentaires (<?= count($extraImages) ?>)</span>
    <span style="font-size:11px;color:var(--muted)">Miniatures page produit</span>
  </div>
  <div class="card-body">
    <?php if ($extraImages): ?>
    <div class="gal-grid">
      <?php foreach ($extraImages as $img): ?>
      <div class="gal-item">
        <img src="<?= htmlspecialchars(imgUrl($b, $img['image'])) ?>" alt="">
        <a href="admin_products.php?delete_img=<?= $img['id'] ?>&pid=<?= $editProduct['id'] ?>" class="gal-del" onclick="return confirm('Supprimer ?')">✕</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p style="color:var(--muted);font-size:13px;margin-bottom:14px">Aucune image supplémentaire.</p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action"     value="upload_images">
      <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
      <div class="gal-zone">
        <input type="file" name="extra_images[]" accept="image/*" multiple onchange="previewGallery(this)">
        <div class="up-icon">🖼️</div>
        <div class="up-txt">Cliquer ou glisser plusieurs images · sélection multiple</div>
      </div>
      <div class="img-strip" id="galStrip"></div>
      <div style="margin-top:12px">
        <button type="submit" class="btn btn-primary btn-sm">✦ Ajouter ces images</button>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ══════════════════════════════════════════
     VUE LISTE
══════════════════════════════════════════ -->

<?php if ($success): ?><div class="toast toast-ok">✓ <?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="toast toast-err">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="page-header">
  <div>
    <h1>Produits</h1>
    <p><?= $totalProducts ?> produit<?= $totalProducts !== 1 ? 's' : '' ?> dans le catalogue</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()">✦ Nouveau produit</button>
</div>

<!-- KPIs -->
<div class="kpi-grid">
  <div class="kpi-card">
    <div class="kpi-accent" style="background:var(--rose)"></div>
    <div class="kpi-label">Total produits</div>
    <div class="kpi-value"><?= $totalProducts ?></div>
    <div class="kpi-icon">✦</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-accent" style="background:var(--green)"></div>
    <div class="kpi-label">Actifs</div>
    <div class="kpi-value"><?= $activeCount ?></div>
    <div class="kpi-icon">✅</div>
  </div>
  <div class="kpi-card">
    <div class="kpi-accent" style="background:var(--blue)"></div>
    <div class="kpi-label">Stock total</div>
    <div class="kpi-value"><?= number_format($totalStock) ?></div>
    <div class="kpi-icon">📦</div>
  </div>
</div>

<!-- Catalogue -->
<div class="card">
  <div class="card-head">
    <span class="card-title">Catalogue</span>
    <span style="font-size:12px;color:var(--muted);font-weight:600"><?= $totalProducts ?> résultat<?= $totalProducts !== 1 ? 's' : '' ?></span>
  </div>
  <div class="card-body">

    <form method="GET" style="display:contents">
      <div class="toolbar">
        <div class="srch">
          <span class="srch-ico">🔍</span>
          <input type="text" name="q" class="fi" placeholder="Rechercher…" value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="cat" class="fi" style="min-width:140px" onchange="this.form.submit()">
          <option value="">Toutes catégories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $catFilter === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="sous_cat" class="fi" style="min-width:140px" onchange="this.form.submit()">
          <option value="">Toutes sous-catégories</option>
          <?php foreach ($sous_categories as $sc): ?>
            <option value="<?= htmlspecialchars($sc) ?>" <?= $sousCatFilter === $sc ? 'selected' : '' ?>><?= htmlspecialchars($sc) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost">Filtrer</button>
        <?php if ($search || $catFilter || $sousCatFilter): ?>
          <a href="admin_products.php" class="btn btn-ghost">✕</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Desktop table -->
    <div class="desktop-tbl tbl-scroll">
      <table class="tbl">
        <thead><tr>
          <th></th><th>Nom</th><th>Marque</th><th>Catégorie</th>
          <th>Sous-cat.</th><th>Prix</th><th>Stock</th>
          <th>Teintes</th><th>Statut</th>
          <th style="text-align:right">Actions</th>
        </tr></thead>
        <tbody>
          <?php foreach ($products as $p):
            $st = (int)$p['stock'];
            $sc = $shadeCountMap[$p['id']] ?? 0;
          ?>
          <tr>
            <td>
              <?php if (!empty($p['image_url'])): ?>
                <img src="<?= htmlspecialchars(imgUrl($b, $p['image_url'])) ?>" class="pimg" alt="" onerror="this.style.display='none'">
              <?php else: ?>
                <div class="pimg-ph">✦</div>
              <?php endif; ?>
            </td>
            <td>
              <div style="font-weight:700;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text)"><?= htmlspecialchars($p['name']) ?></div>
              <?php if ($p['description'] ?? ''): ?>
              <div style="font-size:11px;color:var(--muted);max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:2px"><?= htmlspecialchars($p['description']) ?></div>
              <?php endif; ?>
            </td>
            <td><?php if (!empty($p['marque'])): ?><span class="bdg bdg-brand"><?= htmlspecialchars($p['marque']) ?></span><?php else: ?><span style="color:var(--muted2)">—</span><?php endif; ?></td>
            <td style="color:var(--muted);font-size:12px"><?= $p['categorie'] ? htmlspecialchars($p['categorie']) : '—' ?></td>
            <td><?php if (!empty($p['sous_categorie'])): ?><span class="bdg bdg-scat"><?= htmlspecialchars($p['sous_categorie']) ?></span><?php else: ?><span style="color:var(--muted2)">—</span><?php endif; ?></td>
            <td style="font-weight:800;color:var(--rose);white-space:nowrap"><?= number_format($p['price'],2,',',' ') ?> DA</td>
            <td><span class="<?= $st <= 0 ? 'stk-lo' : ($st <= 5 ? 'stk-md' : 'stk-ok') ?>"><?= $st ?></span></td>
            <td>
              <?php if ($p['has_shades']): ?>
                <a href="admin_products.php?edit=<?= $p['id'] ?>#teintes" class="bdg bdg-sh">🎨 <?= $sc ?></a>
              <?php else: ?><span style="color:var(--muted2);font-size:12px">—</span><?php endif; ?>
            </td>
            <td><span class="bdg <?= $p['active'] ? 'bdg-ok' : 'bdg-off' ?>"><?= $p['active'] ? 'Actif' : 'Inactif' ?></span></td>
            <td>
              <div class="acts">
                <a href="admin_products.php?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">✎</a>
                <a href="admin_products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce produit ?')">✕</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$products): ?>
            <tr><td colspan="10" style="text-align:center;padding:40px;color:var(--muted)">Aucun produit trouvé</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Mobile cards -->
    <div class="mobile-products">
      <?php foreach ($products as $p):
        $st = (int)$p['stock'];
        $sc = $shadeCountMap[$p['id']] ?? 0;
      ?>
      <div class="m-prod-card">
        <?php if (!empty($p['image_url'])): ?>
          <img src="<?= htmlspecialchars(imgUrl($b, $p['image_url'])) ?>" class="m-prod-img" alt="" onerror="this.style.display='none'">
        <?php else: ?>
          <div class="m-prod-ph">✦</div>
        <?php endif; ?>
        <div class="m-prod-body">
          <div class="m-prod-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="m-prod-meta">
            <?php if (!empty($p['marque'])): ?><span class="bdg bdg-brand" style="font-size:9px;padding:2px 7px"><?= htmlspecialchars($p['marque']) ?></span> <?php endif; ?>
            <?php if (!empty($p['categorie'])): ?><?= htmlspecialchars($p['categorie']) ?><?php endif; ?>
            <?php if (!empty($p['sous_categorie'])): ?> · <?= htmlspecialchars($p['sous_categorie']) ?><?php endif; ?>
          </div>
          <div class="m-prod-foot">
            <span class="m-prod-price"><?= number_format($p['price'],2,',',' ') ?> DA</span>
            <div style="display:flex;align-items:center;gap:7px">
              <span class="<?= $st <= 0 ? 'stk-lo' : ($st <= 5 ? 'stk-md' : 'stk-ok') ?>" style="font-size:12px">Stock : <?= $st ?></span>
              <span class="bdg <?= $p['active'] ? 'bdg-ok' : 'bdg-off' ?>"><?= $p['active'] ? 'Actif' : 'Inactif' ?></span>
            </div>
            <div class="m-prod-acts">
              <a href="admin_products.php?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">✎ Éditer</a>
              <a href="admin_products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">✕</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (!$products): ?><p style="color:var(--muted);text-align:center;padding:32px 0;font-size:13px">Aucun produit trouvé</p><?php endif; ?>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL CRÉATION PRODUIT
══════════════════════════════════════════ -->
<div class="modal-overlay" id="addOverlay">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">Nouveau produit</div>
      <button type="button" class="modal-close" id="modalCloseBtn">✕</button>
    </div>

    <div class="tab-bar">
      <button class="tab-btn active" onclick="showTab('t-info',this)">① Infos <span class="tab-count" id="cnt-info">—</span></button>
      <button class="tab-btn" onclick="showTab('t-teintes',this)">🎨 Teintes <span class="tab-count" id="cnt-sh">0</span></button>
      <button class="tab-btn" onclick="showTab('t-images',this)">🖼 Images <span class="tab-count" id="cnt-img">0</span></button>
    </div>

    <form method="POST" enctype="multipart/form-data" id="createForm">
      <input type="hidden" name="action"      value="create_full">
      <input type="hidden" name="shades_data" id="shadesData" value="[]">

      <!-- TAB 1 : INFOS -->
      <div class="tab-panel active" id="t-info">
        <div class="form-2" style="gap:14px">
          <div class="fg full"><label class="fl">Nom du produit *</label><input type="text" name="name" class="fi" placeholder="Ex: Rouge à lèvres Velvet" required></div>
          <div class="fg full"><label class="fl">Description</label><textarea name="description" class="fi" placeholder="Description courte…"></textarea></div>
          <div class="fg"><label class="fl">Prix (DA) *</label><input type="number" name="price" class="fi" placeholder="0" step="0.01" min="0" required></div>
          <div class="fg"><label class="fl">Ancien prix (DA)</label><input type="number" name="old_price" class="fi" step="0.01" min="0" placeholder="Barré sur la fiche"></div>
          <div class="fg"><label class="fl">Stock initial</label><input type="number" name="stock" class="fi" value="0" min="0"></div>
          <div class="fg">
            <label class="fl">Marque</label>
            <input type="text" name="marque" class="fi" placeholder="L'Oréal, NYX…" list="addMarqList">
            <datalist id="addMarqList"><?php foreach ($marques as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg">
            <label class="fl">Catégorie</label>
            <input type="text" name="categorie" class="fi" placeholder="Lèvres, Yeux…" list="addCatList">
            <datalist id="addCatList"><?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg">
            <label class="fl">Sous-catégorie</label>
            <input type="text" name="sous_categorie" class="fi" placeholder="Rouge à lèvres…" list="addSousCatList">
            <datalist id="addSousCatList"><?php foreach ($sous_categories as $sc): ?><option value="<?= htmlspecialchars($sc) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg full" style="gap:11px">
            <label class="fl">Options</label>
            <label class="tog"><input type="checkbox" name="has_shades" value="1" id="hasShadesCb"><span class="tog-track"></span><span class="tog-lbl">Produit avec teintes — ajoutez-les dans l'onglet 🎨</span></label>
            <label class="tog"><input type="checkbox" name="active" value="1" checked><span class="tog-track"></span><span class="tog-lbl">Actif dès la création</span></label>
          </div>
          <div class="fg full">
            <label class="fl">Image principale</label>
            <div class="upzone"><input type="file" name="image" accept="image/*" onchange="previewImg(this,'addPrev')"><div class="up-icon">📷</div><div class="up-txt">Cliquer ou glisser · JPG PNG WEBP</div><img id="addPrev" class="up-prev" alt=""></div>
          </div>
        </div>
        <div style="margin-top:16px;display:flex;justify-content:flex-end">
          <button type="button" class="btn btn-primary" onclick="showTab('t-teintes',null)">Suivant → Teintes</button>
        </div>
      </div>

      <!-- TAB 2 : TEINTES -->
      <div class="tab-panel" id="t-teintes">
        <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Ajoutez les teintes. Chaque teinte peut avoir sa propre image, stock et prix.</p>
        <div id="mShList" class="m-sh-list"></div>
        <p id="noShMsg" style="font-size:12px;color:var(--muted2);text-align:center;padding:8px 0">Aucune teinte ajoutée.</p>
        <div style="background:var(--surface2);border:1px solid var(--border);border-radius:var(--r-sm);padding:16px">
          <div class="sh-form-title">+ Ajouter une teinte</div>
          <div class="form-3">
            <div class="fg"><label class="fl">Nom</label><input type="text" id="mShNom" class="fi" placeholder="Rose Poudré"></div>
            <div class="fg">
              <label class="fl">Couleur</label>
              <div class="col-row">
                <input type="color" id="mShCol" class="fi" value="#c4697a" style="padding:3px;height:38px;cursor:pointer" oninput="document.getElementById('mColSw').style.background=this.value">
                <div class="col-sw" id="mColSw" style="background:#c4697a"></div>
              </div>
            </div>
            <div class="fg"><label class="fl">Stock</label><input type="number" id="mShStk" class="fi" value="0" min="0"></div>
            <div class="fg"><label class="fl">Prix (DA)</label><input type="number" id="mShPrix" class="fi" step="0.01" value="0" placeholder="0 = prix produit"></div>
            <div class="fg" style="grid-column:span 2">
              <label class="fl">Image de la teinte</label>
              <div class="upzone" style="padding:12px">
                <input type="file" id="mShImg" accept="image/*" onchange="previewImg(this,'mShImgPrev')">
                <div class="up-icon" style="font-size:18px">🎨</div>
                <div class="up-txt">Photo associée à cette teinte (facultatif)</div>
                <img id="mShImgPrev" class="up-prev" alt="">
              </div>
            </div>
          </div>
          <div style="margin-top:12px">
            <button type="button" class="btn btn-primary btn-sm" onclick="addShade()">✦ Ajouter</button>
          </div>
        </div>
        <div class="shade-file-strip" id="shadeFileStrip"></div>
        <input type="file" name="shade_images[]" id="shadeImagesHidden" multiple accept="image/*" style="display:none">
        <div style="margin-top:16px;display:flex;justify-content:space-between">
          <button type="button" class="btn btn-ghost" onclick="showTab('t-info',null)">← Infos</button>
          <button type="button" class="btn btn-primary" onclick="showTab('t-images',null)">Suivant → Images</button>
        </div>
      </div>

      <!-- TAB 3 : IMAGES GALERIE -->
      <div class="tab-panel" id="t-images">
        <p style="font-size:12px;color:var(--muted);margin-bottom:14px">Miniatures affichées sur la page produit.</p>
        <div class="gal-zone">
          <input type="file" name="extra_images[]" accept="image/*" multiple onchange="previewGalleryModal(this)">
          <div class="up-icon">🖼️</div>
          <div class="up-txt">Cliquer ou glisser · sélection multiple</div>
        </div>
        <div class="img-strip" id="galStripModal"></div>
        <div style="margin-top:16px;display:flex;justify-content:space-between">
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
/* ── Sidebar mobile ──────────────────────────────────────────────────────── */
const sidebar   = document.getElementById('sidebar');
const hamburger = document.getElementById('hamburger');
const overlay   = document.getElementById('overlay');

function openSidebar()  { sidebar.classList.add('open');    overlay.classList.add('active');    hamburger.classList.add('open');    document.body.style.overflow = 'hidden'; }
function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); hamburger.classList.remove('open'); document.body.style.overflow = ''; }

hamburger?.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
overlay?.addEventListener('click', closeSidebar);
sidebar?.querySelectorAll('.nav-link').forEach(l => l.addEventListener('click', () => { if (window.innerWidth <= 900) closeSidebar(); }));

/* ── Modal création ──────────────────────────────────────────────────────── */
const addOverlay = document.getElementById('addOverlay');

function openModal() {
  addOverlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  addOverlay.classList.remove('open');
  document.body.style.overflow = '';
}

// Bouton ✕ — type="button" + stopPropagation
document.getElementById('modalCloseBtn')?.addEventListener('click', e => {
  e.preventDefault();
  e.stopPropagation();
  closeModal();
});

// Clic sur le fond de l'overlay (pas sur la modal)
addOverlay?.addEventListener('click', e => {
  if (e.target === addOverlay) closeModal();
});

// Fermeture au swipe bas sur mobile
let touchStartY = 0;
addOverlay?.addEventListener('touchstart', e => {
  touchStartY = e.touches[0].clientY;
}, { passive: true });
addOverlay?.addEventListener('touchend', e => {
  const delta = e.changedTouches[0].clientY - touchStartY;
  if (delta > 80 && e.target === addOverlay) closeModal();
}, { passive: true });

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

function showTab(id, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(id)?.classList.add('active');
  if (btn) { btn.classList.add('active'); }
  else {
    const idx = ['t-info','t-teintes','t-images'].indexOf(id);
    document.querySelectorAll('.tab-btn')[idx]?.classList.add('active');
  }
}

/* ── Prévisualisations ───────────────────────────────────────────────────── */
function previewImg(input, id) {
  const el = document.getElementById(id);
  if (!el || !input.files?.[0]) return;
  const r = new FileReader();
  r.onload = e => { el.src = e.target.result; el.style.display = 'block'; };
  r.readAsDataURL(input.files[0]);
}
function previewGallery(input) {
  const strip = document.getElementById('galStrip');
  if (!strip) return;
  strip.innerHTML = '';
  Array.from(input.files).forEach(f => {
    const r = new FileReader();
    r.onload = e => { const img = document.createElement('img'); img.src = e.target.result; strip.appendChild(img); };
    r.readAsDataURL(f);
  });
}
function previewGalleryModal(input) {
  const strip = document.getElementById('galStripModal');
  const cnt   = document.getElementById('cnt-img');
  if (!strip) return;
  strip.innerHTML = '';
  Array.from(input.files).forEach(f => {
    const r = new FileReader();
    r.onload = e => { const img = document.createElement('img'); img.src = e.target.result; strip.appendChild(img); };
    r.readAsDataURL(f);
  });
  if (cnt) { cnt.textContent = input.files.length; cnt.className = input.files.length ? 'tab-count has' : 'tab-count'; }
}

/* ── Teintes (modal création) ────────────────────────────────────────────── */
let shades = [], shadeFiles = {};

function addShade() {
  const nom  = document.getElementById('mShNom').value.trim();
  const code = document.getElementById('mShCol').value;
  const stk  = parseInt(document.getElementById('mShStk').value)   || 0;
  const prix = parseFloat(document.getElementById('mShPrix').value) || 0;
  const fi   = document.getElementById('mShImg');
  if (!nom) { alert('Entrez un nom pour la teinte.'); return; }
  const idx = shades.length;
  const hasImg = !!(fi && fi.files[0]);
  shades.push({ nom, code, stock: stk, prix, hasImg });
  if (hasImg) shadeFiles[idx] = fi.files[0];
  document.getElementById('mShNom').value  = '';
  document.getElementById('mShStk').value  = '0';
  document.getElementById('mShPrix').value = '0';
  if (fi) fi.value = '';
  const prev = document.getElementById('mShImgPrev');
  if (prev) { prev.src = ''; prev.style.display = 'none'; }
  renderShades();
  document.getElementById('hasShadesCb').checked = true;
}

function removeShade(i) {
  shades.splice(i, 1);
  const nf = {};
  Object.entries(shadeFiles).forEach(([k, v]) => { const ki = parseInt(k); if (ki < i) nf[ki] = v; else if (ki > i) nf[ki-1] = v; });
  shadeFiles = nf;
  renderShades();
  if (!shades.length) document.getElementById('hasShadesCb').checked = false;
}

function renderShades() {
  const list = document.getElementById('mShList');
  const msg  = document.getElementById('noShMsg');
  const cnt  = document.getElementById('cnt-sh');
  const strip = document.getElementById('shadeFileStrip');
  document.getElementById('shadesData').value = JSON.stringify(shades);
  cnt.textContent = shades.length;
  cnt.className   = shades.length ? 'tab-count has' : 'tab-count';
  if (!shades.length) { list.innerHTML = ''; msg.style.display = 'block'; strip.innerHTML = ''; syncShadeFiles(); return; }
  msg.style.display = 'none';
  list.innerHTML = shades.map((s, i) => {
    const imgTag = s.hasImg
      ? `<img class="m-sh-thumb" id="shThumb_${i}" src="" alt="">`
      : `<div class="m-sh-sw" style="background:${s.code}"></div>`;
    return `<div class="m-sh-row">${imgTag}<span class="m-sh-nm">${s.nom}</span><span class="m-sh-stk">Stock : ${s.stock}${s.prix ? ' · '+s.prix+' DA' : ''}</span><span class="m-sh-code">${s.code}</span><button type="button" class="btn btn-danger btn-sm" onclick="removeShade(${i})">✕</button></div>`;
  }).join('');
  Object.entries(shadeFiles).forEach(([k, file]) => {
    const thumb = document.getElementById('shThumb_' + k);
    if (!thumb || !file) return;
    const r = new FileReader(); r.onload = e => { thumb.src = e.target.result; }; r.readAsDataURL(file);
  });
  strip.innerHTML = '';
  Object.entries(shadeFiles).forEach(([k, file]) => {
    if (!file) return;
    const div = document.createElement('div'); div.className = 'sf-item';
    const img = document.createElement('img');
    const sp  = document.createElement('span'); sp.textContent = shades[parseInt(k)]?.nom || '';
    div.appendChild(img); div.appendChild(sp); strip.appendChild(div);
    const r = new FileReader(); r.onload = e => { img.src = e.target.result; }; r.readAsDataURL(file);
  });
  syncShadeFiles();
}

function syncShadeFiles() {
  const input = document.getElementById('shadeImagesHidden');
  if (!input || typeof DataTransfer === 'undefined') return;
  const dt = new DataTransfer();
  shades.forEach((_, idx) => { if (shadeFiles[idx]) dt.items.add(shadeFiles[idx]); });
  try { input.files = dt.files; } catch(e) {}
}

/* ── Formulaire teinte (édition) ─────────────────────────────────────────── */
function loadShade(sh) {
  document.getElementById('shId').value          = sh.id;
  document.getElementById('shNom').value         = sh.nom_teinte;
  document.getElementById('shCol').value         = sh.code_couleur;
  document.getElementById('shStk').value         = sh.stock  || 0;
  document.getElementById('shPrix').value        = sh.prix   || 0;
  document.getElementById('shExistingImg').value = sh.image  || '';
  document.getElementById('colSw').style.background = sh.code_couleur;
  const prev = document.getElementById('shImgPrev');
  if (prev) { prev.src = ''; prev.style.display = 'none'; }
  const wrap = document.getElementById('shCurrentImgWrap');
  if (wrap) wrap.style.display = sh.image ? 'flex' : 'none';
  document.getElementById('shLbl').textContent = '✎ Modifier — ' + sh.nom_teinte;
  document.getElementById('shBtn').textContent = '✦ Mettre à jour';
  document.querySelector('.sh-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function resetShForm() {
  ['shId','shExistingImg'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('shNom').value  = '';
  document.getElementById('shCol').value  = '#c4697a';
  document.getElementById('shStk').value  = '0';
  document.getElementById('shPrix').value = '0';
  document.getElementById('colSw').style.background = '#c4697a';
  const prev = document.getElementById('shImgPrev');
  if (prev) { prev.src = ''; prev.style.display = 'none'; }
  const wrap = document.getElementById('shCurrentImgWrap');
  if (wrap) wrap.style.display = 'none';
  document.getElementById('shLbl').textContent = '+ Nouvelle teinte';
  document.getElementById('shBtn').textContent = '✦ Ajouter';
}

/* ── Ancres ──────────────────────────────────────────────────────────────── */
if (location.hash === '#teintes') setTimeout(() => document.getElementById('teintes')?.scrollIntoView({ behavior: 'smooth' }), 250);
if (location.hash === '#gallery') setTimeout(() => document.getElementById('gallery')?.scrollIntoView({ behavior: 'smooth' }), 250);
</script>
</body>
</html>