<?php
// ============================================
//  SheGlamour — Gestion Produits v2
//  Formulaire unifié : produit + teintes + images + marque
// ============================================
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/config.php';

$b       = BASE_URL ?? '';
$success = '';
$error   = '';

// ─── HELPER : URL image ──────────────────────────────────
function imgUrl(string $b, ?string $raw): string {
    if (!$raw) return $b . '/images/placeholder.jpg';
    if (str_starts_with($raw, 'http')) return $raw;
    return $b . '/images/' . basename($raw);
}

// ─── ACTION : SUPPRIMER UNE IMAGE SUPPLÉMENTAIRE ─────────
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

// ─── ACTION : SUPPRIMER UNE TEINTE ───────────────────────
if (isset($_GET['delete_shade'])) {
    $sid = (int) $_GET['delete_shade'];
    $pid = (int) ($_GET['pid'] ?? 0);
    try {
        $pdo->prepare("DELETE FROM teintes WHERE id = ?")->execute([$sid]);
        header("Location: admin_products.php?edit=$pid&success=" . urlencode("Teinte supprimée.") . "#teintes");
        exit;
    } catch (Exception $e) { $error = "Erreur : " . $e->getMessage(); }
}

// ─── ACTION : SAUVEGARDER UNE TEINTE (en édition) ────────
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

// ─── ACTION : UPLOAD IMAGES (en édition) ─────────────────
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

// ─── ACTION : SUPPRIMER UN PRODUIT ───────────────────────
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

// ─── ACTION : CRÉER UN PRODUIT (formulaire unifié) ────────
// Reçoit : infos produit + teintes JSON + images multiples
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
        // Image principale
        $imageUrl = null;
        if (!empty($_FILES['image']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext, $allowed)) {
                $error = "Format d'image non supporté.";
            } else {
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
                // Création produit (avec colonne marque)
                $pdo->prepare("INSERT INTO products (name, description, price, stock, categorie, marque, has_shades, active, image_url) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$name, $description, $price, $stock, $categorie, $marque, $has_shades, $active, $imageUrl]);
                $newId = $pdo->lastInsertId();

                // Teintes depuis JSON
                $shades = json_decode($shadesJson, true) ?: [];
                foreach ($shades as $sh) {
                    $nom   = trim($sh['nom'] ?? '');
                    $code  = trim($sh['code'] ?? '#000000');
                    $stSh  = (int) ($sh['stock'] ?? 0);
                    if ($nom) {
                        $pdo->prepare("INSERT INTO teintes (product_id, nom_teinte, code_couleur, stock) VALUES (?,?,?,?)")
                            ->execute([$newId, $nom, $code, $stSh]);
                    }
                }
                if ($shades) {
                    $pdo->prepare("UPDATE products SET has_shades=1 WHERE id=?")->execute([$newId]);
                }

                // Images supplémentaires
                $uploadDir = __DIR__ . '/images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (!empty($_FILES['extra_images']['name'][0])) {
                    foreach ($_FILES['extra_images']['tmp_name'] as $i => $tmp) {
                        if ($_FILES['extra_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                        $ext = strtolower(pathinfo($_FILES['extra_images']['name'][$i], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
                        $fn = uniqid('img_') . '.' . $ext;
                        if (move_uploaded_file($tmp, $uploadDir . $fn)) {
                            $pdo->prepare("INSERT INTO product_images (product_id, image) VALUES (?,?)")->execute([$newId, $fn]);
                        }
                    }
                }

                header("Location: admin_products.php?edit=$newId&success=" . urlencode("Produit créé avec succès !"));
                exit;
            } catch (Exception $e) { $error = "Erreur BDD : " . $e->getMessage(); }
        }
    }
}

// ─── ACTION : METTRE À JOUR UN PRODUIT ───────────────────
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

    if (!$name || $price <= 0) {
        $error = "Le nom et le prix sont obligatoires.";
    } else {
        $imageUrl = $_POST['existing_image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                $uploadDir = __DIR__ . '/images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = uniqid('prod_') . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $imageUrl = $filename;
                }
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

// ─── SUCCESS via GET ──────────────────────────────────────
if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

// ─── PRODUIT EN ÉDITION ───────────────────────────────────
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

// ─── LISTE PRODUITS ───────────────────────────────────────
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
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0a0a0c;--surface:#111114;--surface2:#18181e;--surface3:#1f1f27;
  --border:#252530;--border2:#30303e;
  --text:#ede9f4;--muted:#6b6b80;--muted2:#9898ae;
  --rose:#f0b8d0;--rose2:#d48aaa;--rose3:#a8607f;
  --lilac:#c5b8e8;--lilac2:#9e8fd4;
  --green:#4ade80;--amber:#fbbf24;--red:#f87171;--blue:#818cf8;
  --radius:12px;--radius-lg:18px;
  --font-serif:'Cormorant Garamond',serif;
  --font-body:'DM Sans',sans-serif;
  --sidebar:230px;
}
*{margin:0;padding:0;box-sizing:border-box}
body{background:var(--bg);color:var(--text);font-family:var(--font-body);font-size:14px;line-height:1.5;min-height:100vh}

/* ── SIDEBAR ── */
.sidebar{position:fixed;top:0;left:0;width:var(--sidebar);height:100vh;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;padding:28px 0 20px}
.logo{padding:0 20px 28px;font-family:var(--font-serif);font-size:24px;font-weight:600;letter-spacing:.01em}
.logo em{color:var(--rose);font-style:italic}
.nav{display:flex;flex-direction:column;gap:2px;padding:0 10px;flex:1}
.nav a{display:flex;align-items:center;gap:11px;padding:9px 12px;border-radius:10px;font-size:13px;font-weight:500;color:var(--muted2);transition:all .18s;text-decoration:none}
.nav a:hover{background:var(--surface2);color:var(--text)}
.nav a.active{background:var(--surface2);color:var(--rose)}
.nav-icon{font-size:15px;width:18px;text-align:center;flex-shrink:0}
.sidebar-foot{padding:0 20px;font-size:11px;color:var(--muted)}

/* ── MAIN ── */
.main{margin-left:var(--sidebar);padding:36px 32px;min-height:100vh}

/* ── BTN ── */
.btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:9px;font-family:var(--font-body);font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .18s;text-decoration:none;white-space:nowrap}
.btn-primary{background:linear-gradient(135deg,var(--rose),var(--lilac));color:#180c18}
.btn-primary:hover{opacity:.88;transform:translateY(-1px)}
.btn-ghost{background:var(--surface2);color:var(--text);border:1px solid var(--border2)}
.btn-ghost:hover{border-color:var(--rose);color:var(--rose)}
.btn-danger{background:rgba(248,113,113,.1);color:var(--red);border:1px solid rgba(248,113,113,.22)}
.btn-danger:hover{background:rgba(248,113,113,.2)}
.btn-sm{padding:5px 11px;font-size:11px;border-radius:7px}
.btn-amber{background:rgba(251,191,36,.1);color:var(--amber);border:1px solid rgba(251,191,36,.22)}
.btn-amber:hover{background:rgba(251,191,36,.18)}

/* ── PAGE HEADER ── */
.page-header{margin-bottom:28px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
.page-header h1{font-family:var(--font-serif);font-size:36px;font-weight:600;letter-spacing:-.01em;color:var(--text);line-height:1.1}
.page-header h1 em{color:var(--rose);font-style:italic}
.page-header p{color:var(--muted2);margin-top:4px;font-size:13px}

/* ── TOAST ── */
.toast{padding:12px 18px;border-radius:9px;font-size:13px;font-weight:500;margin-bottom:20px;display:flex;align-items:center;gap:9px}
.toast-ok{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.25);color:var(--green)}
.toast-err{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.25);color:var(--red)}

/* ── KPI ── */
.kpi-row{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px}
.kpi{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px 22px;position:relative;overflow:hidden}
.kpi::after{content:'';position:absolute;bottom:-30px;right:-20px;width:80px;height:80px;border-radius:50%;background:var(--kpi-color,var(--rose));opacity:.05;pointer-events:none}
.kpi-lbl{font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.kpi-val{font-family:var(--font-serif);font-size:30px;font-weight:600;color:var(--text)}
.kpi-icon{position:absolute;top:16px;right:16px;font-size:20px;opacity:.18}

/* ── CARD ── */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:22px}
.card-hd{padding:18px 22px 14px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:8px}
.card-title{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)}
.card-body{padding:22px}

/* ── FORM ── */
.fg{display:flex;flex-direction:column;gap:5px}
.fg.full{grid-column:1/-1}
.fl{font-size:10px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--muted)}
.fi{background:var(--surface2);border:1px solid var(--border2);border-radius:8px;padding:9px 13px;color:var(--text);font-family:var(--font-body);font-size:13px;transition:border-color .18s;width:100%}
.fi:focus{outline:none;border-color:var(--rose)}
.fi::placeholder{color:var(--muted)}
textarea.fi{resize:vertical;min-height:80px}
.form-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}

/* ── TOGGLE ── */
.tog{display:flex;align-items:center;gap:9px;cursor:pointer;user-select:none}
.tog input{width:0;height:0;opacity:0;position:absolute}
.tog-track{width:38px;height:20px;background:var(--surface3);border:1px solid var(--border2);border-radius:10px;position:relative;transition:background .18s;flex-shrink:0}
.tog-track::after{content:'';position:absolute;top:2px;left:2px;width:14px;height:14px;background:var(--muted);border-radius:50%;transition:all .18s}
.tog input:checked+.tog-track{background:rgba(240,184,208,.18);border-color:var(--rose)}
.tog input:checked+.tog-track::after{left:20px;background:var(--rose)}
.tog-lbl{font-size:13px;color:var(--muted2)}

/* ── UPLOAD ── */
.upzone{border:2px dashed var(--border2);border-radius:10px;padding:22px;text-align:center;cursor:pointer;transition:all .18s;position:relative}
.upzone:hover{border-color:var(--rose);background:rgba(240,184,208,.03)}
.upzone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.up-icon{font-size:26px;margin-bottom:6px;opacity:.35}
.up-txt{font-size:11px;color:var(--muted)}
.up-prev{width:100%;max-height:140px;object-fit:contain;border-radius:8px;margin-top:10px;display:none}
.strip{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.strip img{width:56px;height:56px;object-fit:cover;border-radius:6px;border:1px solid var(--border)}

/* ── TOOLBAR ── */
.toolbar{display:flex;gap:10px;margin-bottom:18px;align-items:center;flex-wrap:wrap}
.srch{position:relative;flex:1;min-width:160px}
.srch .fi{padding-left:34px}
.srch-ico{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:14px}

/* ── TABLE ── */
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse;min-width:720px}
.tbl th{font-size:10px;font-weight:700;letter-spacing:.11em;text-transform:uppercase;color:var(--muted);padding:0 10px 11px;text-align:left;border-bottom:1px solid var(--border)}
.tbl th:first-child{padding-left:0}
.tbl td{padding:12px 10px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:13px}
.tbl td:first-child{padding-left:0}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:rgba(255,255,255,.009)}
.pimg{width:42px;height:42px;border-radius:8px;object-fit:cover;background:var(--surface2)}
.pimg-ph{width:42px;height:42px;border-radius:8px;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--muted)}
.bdg{display:inline-block;padding:2px 9px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.06em}
.bdg-ok{background:rgba(74,222,128,.1);color:var(--green)}
.bdg-off{background:rgba(107,107,128,.1);color:var(--muted)}
.bdg-sh{background:rgba(197,184,232,.1);color:var(--lilac);text-decoration:none;transition:background .18s}
.bdg-sh:hover{background:rgba(197,184,232,.2)}
.bdg-brand{background:rgba(129,140,248,.1);color:var(--blue)}
.stk-lo{color:var(--red);font-weight:700}
.stk-ok{color:var(--green);font-weight:700}
.stk-md{color:var(--amber);font-weight:700}
.acts{display:flex;gap:7px;justify-content:flex-end}

/* ── EDIT LAYOUT ── */
.edit-layout{display:grid;grid-template-columns:1fr 1fr;gap:22px;align-items:start}
.edit-layout .card{margin-bottom:0}
.bc{display:flex;align-items:center;gap:7px;font-size:12px;color:var(--muted);margin-bottom:18px}
.bc a{color:var(--muted);text-decoration:none}
.bc a:hover{color:var(--rose)}

/* ── TEINTES ── */
.sh-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:9px;margin-bottom:18px}
.sh-card{background:var(--bg);border:1px solid var(--border2);border-radius:11px;padding:10px 13px;display:flex;align-items:center;gap:9px}
.sh-sw{width:30px;height:30px;border-radius:7px;flex-shrink:0;border:2px solid rgba(255,255,255,.08)}
.sh-info{flex:1;min-width:0}
.sh-nm{font-weight:600;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sh-stk{font-size:10px;color:var(--muted)}
.sh-acts{display:flex;gap:5px;flex-shrink:0}
.sh-form{background:var(--bg);border:1px solid var(--border2);border-radius:11px;padding:16px;margin-top:4px}
.col-row{display:flex;align-items:center;gap:9px}
.col-sw{width:34px;height:34px;border-radius:7px;border:2px solid rgba(255,255,255,.08);flex-shrink:0}

/* ── GALLERY ── */
.gal-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:9px;margin-bottom:14px}
.gal-item{position:relative;border-radius:9px;overflow:hidden;border:1px solid var(--border);aspect-ratio:1;background:var(--bg)}
.gal-item img{width:100%;height:100%;object-fit:cover;display:block}
.gal-del{position:absolute;top:4px;right:4px;background:rgba(0,0,0,.7);color:var(--red);border:1px solid rgba(248,113,113,.35);border-radius:5px;width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:10px;cursor:pointer;text-decoration:none;transition:background .18s}
.gal-del:hover{background:rgba(248,113,113,.3)}
.gal-zone{border:2px dashed var(--border2);border-radius:10px;padding:18px;text-align:center;cursor:pointer;transition:all .18s;position:relative}
.gal-zone:hover{border-color:var(--rose);background:rgba(240,184,208,.03)}
.gal-zone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}

/* ── MODAL (unifié) ── */
.overlay{position:fixed;inset:0;background:rgba(5,5,8,.82);z-index:200;display:none;overflow-y:auto;padding:30px 20px;backdrop-filter:blur(6px)}
.overlay.open{display:block}
.modal{background:var(--surface);border:1px solid var(--border2);border-radius:22px;width:100%;max-width:760px;margin:0 auto;position:relative}
.m-hd{padding:24px 28px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.m-title{font-family:var(--font-serif);font-size:26px;font-weight:600}
.m-close{background:var(--surface2);border:1px solid var(--border2);color:var(--muted2);width:30px;height:30px;border-radius:7px;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;transition:all .18s}
.m-close:hover{color:var(--text);border-color:var(--rose)}

/* Tabs dans modal */
.tab-bar{display:flex;gap:0;border-bottom:1px solid var(--border);padding:0 28px;background:var(--surface)}
.tab-btn{padding:12px 18px;font-size:13px;font-weight:600;color:var(--muted2);cursor:pointer;border:none;background:none;border-bottom:2px solid transparent;margin-bottom:-1px;transition:all .18s;display:flex;align-items:center;gap:7px}
.tab-btn.active{color:var(--rose);border-bottom-color:var(--rose)}
.tab-btn:hover:not(.active){color:var(--text)}
.tab-panel{display:none;padding:22px 28px}
.tab-panel.active{display:block}

/* Inline shade list in modal */
.m-sh-list{display:flex;flex-direction:column;gap:7px;margin-bottom:14px}
.m-sh-row{display:grid;grid-template-columns:32px 1fr 80px 70px auto;gap:8px;align-items:center;background:var(--bg);border:1px solid var(--border2);border-radius:8px;padding:9px 12px}
.m-sh-sw{width:30px;height:30px;border-radius:6px;border:2px solid rgba(255,255,255,.08)}
.m-sh-nm{font-size:13px;font-weight:500}
.m-sh-stk{font-size:12px;color:var(--muted)}
.m-sh-code{font-size:11px;color:var(--muted);font-family:monospace}

/* Drag indicator */
.tab-count{background:var(--surface3);color:var(--muted2);font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;min-width:20px;text-align:center}
.tab-count.has{background:rgba(240,184,208,.15);color:var(--rose)}

.m-footer{padding:14px 28px 22px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border)}

.info-box{background:var(--surface2);border:1px solid var(--border);border-radius:9px;padding:12px 15px;font-size:11px;color:var(--muted2);line-height:1.7;margin-top:14px}
.info-box strong{color:var(--text);display:block;margin-bottom:3px}
code{color:var(--rose);font-size:10px}

/* Step header in modal */
.step-hd{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:12px}

/* Shade add row */
.sh-add-row{display:grid;grid-template-columns:1fr auto 80px auto;gap:9px;align-items:end;margin-top:12px;padding-top:12px;border-top:1px solid var(--border)}

@media(max-width:960px){
  .sidebar{display:none}.main{margin-left:0;padding:18px}
  .kpi-row,.edit-layout,.form-2,.form-3{grid-template-columns:1fr}
  .fg.full{grid-column:1}
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="logo">She<em>Glamour</em></div>
  <nav class="nav">
    <a href="dashboard.php"><span class="nav-icon">⬡</span> Tableau de bord</a>
    <a href="admin_orders.php"><span class="nav-icon">◻</span> Commandes</a>
    <a href="admin_products.php" class="active"><span class="nav-icon">✦</span> Produits</a>
    <a href="index.php" target="_blank"><span class="nav-icon">↗</span> Voir la boutique</a>
  </nav>
  <div class="sidebar-foot">SheGlamour Admin · v2.0</div>
</aside>

<main class="main">

<?php if ($editProduct): ?>
<!-- ══════════════════════════════════════
     VUE ÉDITION PRODUIT
══════════════════════════════════════ -->

<div class="bc">
  <a href="admin_products.php">✦ Produits</a>
  <span>›</span>
  <span style="color:var(--text)"><?= htmlspecialchars($editProduct['name']) ?></span>
</div>

<?php if ($success): ?><div class="toast toast-ok">✓ <?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="toast toast-err">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="page-header">
  <div>
    <h1>Modifier le <em>produit</em></h1>
    <p>ID #<?= $editProduct['id'] ?> · Créé le <?= date('d/m/Y', strtotime($editProduct['created_at'] ?? 'now')) ?></p>
  </div>
  <a href="admin_products.php" class="btn btn-ghost">← Retour</a>
</div>

<div class="edit-layout">

  <!-- FICHE -->
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
              <img src="<?= htmlspecialchars(imgUrl($b, $editProduct['image_url'])) ?>" style="height:52px;border-radius:7px;border:1px solid var(--border)" alt="">
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
          <span style="font-size:11px;color:var(--muted)">Affichées en boutique</span>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if (!$editProduct['has_shades']): ?>
          <div style="text-align:center;padding:28px 0;color:var(--muted)">
            <div style="font-size:32px;margin-bottom:10px;opacity:.2">🎨</div>
            <p style="font-size:12px;line-height:1.6">Activez <strong style="color:var(--text)">"Produit avec teintes"</strong><br>puis enregistrez.</p>
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

          <!-- Formulaire teinte -->
          <div class="sh-form">
            <div style="font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:12px" id="shLbl">+ Nouvelle teinte</div>
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
                           value="<?= $editShade ? htmlspecialchars($editShade['code_couleur']) : '#f0b8d0' ?>"
                           style="padding:3px;height:38px;cursor:pointer"
                           oninput="document.getElementById('colSw').style.background=this.value">
                    <div class="col-sw" id="colSw" style="background:<?= $editShade ? htmlspecialchars($editShade['code_couleur']) : '#f0b8d0' ?>"></div>
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
      Teintes lues via <code>get_shades.php?product_id=<?= $editProduct['id'] ?></code><br>
      S'affiche si <code>has_shades=1</code> et au moins une teinte existe.
    </div>
  </div>

</div><!-- /edit-layout -->

<!-- GALERIE -->
<div id="gallery" class="card" style="margin-top:22px">
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
<!-- ══════════════════════════════════════
     VUE LISTE PRODUITS
══════════════════════════════════════ -->

<div class="page-header">
  <div>
    <h1>Produits <em>SheGlamour</em></h1>
    <p><?= $totalProducts ?> produit<?= $totalProducts !== 1 ? 's' : '' ?> dans le catalogue</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()">✦ Nouveau produit</button>
</div>

<?php if ($success): ?><div class="toast toast-ok">✓ <?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="toast toast-err">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- KPIs -->
<div class="kpi-row">
  <div class="kpi" style="--kpi-color:var(--rose)">
    <div class="kpi-lbl">Total produits</div>
    <div class="kpi-val"><?= $totalProducts ?></div>
    <div class="kpi-icon">✦</div>
  </div>
  <div class="kpi" style="--kpi-color:var(--green)">
    <div class="kpi-lbl">Actifs</div>
    <div class="kpi-val"><?= $activeCount ?></div>
    <div class="kpi-icon">✅</div>
  </div>
  <div class="kpi" style="--kpi-color:var(--blue)">
    <div class="kpi-lbl">Stock total</div>
    <div class="kpi-val"><?= number_format($totalStock) ?></div>
    <div class="kpi-icon">📦</div>
  </div>
</div>

<!-- TABLE -->
<div class="card">
  <div class="card-hd">
    <span class="card-title">Catalogue</span>
    <span style="font-size:12px;color:var(--muted)"><?= $totalProducts ?> résultat<?= $totalProducts !== 1 ? 's' : '' ?></span>
  </div>
  <div class="card-body">
    <form method="GET" style="display:contents">
      <div class="toolbar">
        <div class="srch">
          <span class="srch-ico">🔍</span>
          <input type="text" name="q" class="fi" placeholder="Rechercher…" value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="cat" class="fi" style="width:160px" onchange="this.form.submit()">
          <option value="">Toutes catégories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $catFilter === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost">Filtrer</button>
        <?php if ($search || $catFilter): ?><a href="admin_products.php" class="btn btn-ghost">✕</a><?php endif; ?>
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
            <td>
              <?php if (!empty($p['image_url'])): ?>
                <img src="<?= htmlspecialchars(imgUrl($b, $p['image_url'])) ?>" class="pimg" alt="" onerror="this.style.display='none'">
              <?php else: ?>
                <div class="pimg-ph">✦</div>
              <?php endif; ?>
            </td>
            <td>
              <div style="font-weight:600;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($p['name']) ?></div>
              <?php if ($p['description'] ?? ''): ?>
                <div style="font-size:11px;color:var(--muted);margin-top:2px;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($p['description']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($p['marque'])): ?>
                <span class="bdg bdg-brand"><?= htmlspecialchars($p['marque']) ?></span>
              <?php else: ?><span style="color:var(--muted)">—</span><?php endif; ?>
            </td>
            <td style="color:var(--muted2);font-size:12px"><?= $p['categorie'] ? htmlspecialchars($p['categorie']) : '—' ?></td>
            <td style="font-weight:700;color:var(--rose);white-space:nowrap"><?= number_format($p['price'],2,',',' ') ?> DA</td>
            <td>
              <?php $st = (int)$p['stock']; ?>
              <span class="<?= $st <= 0 ? 'stk-lo' : ($st <= 5 ? 'stk-md' : 'stk-ok') ?>"><?= $st ?></span>
            </td>
            <td>
              <?php $sc = $shadeCountMap[$p['id']] ?? 0; ?>
              <?php if ($p['has_shades']): ?>
                <a href="admin_products.php?edit=<?= $p['id'] ?>#teintes" class="bdg bdg-sh">🎨 <?= $sc ?></a>
              <?php else: ?><span style="color:var(--muted);font-size:12px">—</span><?php endif; ?>
            </td>
            <td><span class="bdg <?= $p['active'] ? 'bdg-ok' : 'bdg-off' ?>"><?= $p['active'] ? 'Actif' : 'Inactif' ?></span></td>
            <td>
              <div class="acts">
                <a href="admin_products.php?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">✎ Éditer</a>
                <a href="admin_products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Supprimer ce produit et toutes ses données ?')">✕</a>
              </div>
            </td>
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

<!-- ══ MODAL CRÉATION UNIFIÉE ══ -->
<div class="overlay" id="addOverlay" onclick="if(event.target===this)closeModal()">
  <div class="modal">
    <div class="m-hd">
      <div class="m-title">Nouveau <em style="color:var(--rose);font-style:italic">produit</em></div>
      <button class="m-close" onclick="closeModal()">✕</button>
    </div>

    <!-- TABS -->
    <div class="tab-bar">
      <button class="tab-btn active" onclick="showTab('t-info',this)">
        ① Infos <span class="tab-count" id="cnt-info">—</span>
      </button>
      <button class="tab-btn" onclick="showTab('t-teintes',this)">
        🎨 Teintes <span class="tab-count" id="cnt-sh">0</span>
      </button>
      <button class="tab-btn" onclick="showTab('t-images',this)">
        🖼 Images <span class="tab-count" id="cnt-img">0</span>
      </button>
    </div>

    <form method="POST" enctype="multipart/form-data" id="createForm">
      <input type="hidden" name="action" value="create_full">
      <input type="hidden" name="shades_data" id="shadesData" value="[]">

      <!-- TAB 1 : Infos produit -->
      <div class="tab-panel active" id="t-info">
        <div class="form-2" style="gap:14px">
          <div class="fg full">
            <label class="fl">Nom du produit *</label>
            <input type="text" name="name" class="fi" placeholder="Ex: Rouge à lèvres Velvet" required>
          </div>
          <div class="fg full">
            <label class="fl">Description</label>
            <textarea name="description" class="fi" placeholder="Description courte…"></textarea>
          </div>
          <div class="fg">
            <label class="fl">Prix (DA) *</label>
            <input type="number" name="price" class="fi" placeholder="0" step="0.01" min="0" required>
          </div>
          <div class="fg">
            <label class="fl">Stock initial</label>
            <input type="number" name="stock" class="fi" value="0" min="0">
          </div>
          <div class="fg">
            <label class="fl">Catégorie</label>
            <input type="text" name="categorie" class="fi" placeholder="Ex: Lèvres, Yeux…" list="addCatList">
            <datalist id="addCatList"><?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg">
            <label class="fl">Marque</label>
            <input type="text" name="marque" class="fi" placeholder="Ex: L'Oréal, NYX…" list="addMarqList">
            <datalist id="addMarqList"><?php foreach ($marques as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?></datalist>
          </div>
          <div class="fg full" style="gap:11px">
            <label class="fl">Options</label>
            <label class="tog">
              <input type="checkbox" name="has_shades" value="1" id="hasShadesCb">
              <span class="tog-track"></span><span class="tog-lbl">Produit avec teintes — ajoutez-les dans l'onglet 🎨</span>
            </label>
            <label class="tog">
              <input type="checkbox" name="active" value="1" checked>
              <span class="tog-track"></span><span class="tog-lbl">Actif dès la création</span>
            </label>
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

      <!-- TAB 2 : Teintes -->
      <div class="tab-panel" id="t-teintes">
        <div class="step-hd">Teintes du produit</div>
        <p style="font-size:12px;color:var(--muted2);margin-bottom:14px">Ajoutez autant de teintes que nécessaire. Elles seront créées avec le produit.</p>

        <!-- Liste teintes en cours -->
        <div id="mShList" class="m-sh-list"></div>
        <p id="noShMsg" style="font-size:12px;color:var(--muted);text-align:center;padding:10px 0">Aucune teinte ajoutée pour l'instant.</p>

        <!-- Formulaire ajout teinte rapide -->
        <div style="background:var(--bg);border:1px solid var(--border2);border-radius:10px;padding:14px;margin-top:4px">
          <div class="step-hd" style="margin-bottom:10px">+ Ajouter une teinte</div>
          <div class="form-3">
            <div class="fg">
              <label class="fl">Nom</label>
              <input type="text" id="mShNom" class="fi" placeholder="Rose Poudré">
            </div>
            <div class="fg">
              <label class="fl">Couleur</label>
              <div class="col-row">
                <input type="color" id="mShCol" class="fi" value="#f0b8d0" style="padding:3px;height:38px;cursor:pointer" oninput="document.getElementById('mColSw').style.background=this.value">
                <div class="col-sw" id="mColSw" style="background:#f0b8d0"></div>
              </div>
            </div>
            <div class="fg">
              <label class="fl">Stock</label>
              <input type="number" id="mShStk" class="fi" value="0" min="0">
            </div>
          </div>
          <div style="margin-top:10px">
            <button type="button" class="btn btn-primary btn-sm" onclick="addShade()">✦ Ajouter cette teinte</button>
          </div>
        </div>

        <div style="margin-top:18px;display:flex;justify-content:space-between">
          <button type="button" class="btn btn-ghost" onclick="showTab('t-info',null)">← Infos</button>
          <button type="button" class="btn btn-primary" onclick="showTab('t-images',null)">Suivant → Images</button>
        </div>
      </div>

      <!-- TAB 3 : Images supplémentaires -->
      <div class="tab-panel" id="t-images">
        <div class="step-hd">Images supplémentaires (galerie)</div>
        <p style="font-size:12px;color:var(--muted2);margin-bottom:14px">Ces images seront affichées comme miniatures sur la page produit.</p>

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
/* ── MODAL ── */
function openModal(){document.getElementById('addOverlay').classList.add('open');document.body.style.overflow='hidden'}
function closeModal(){document.getElementById('addOverlay').classList.remove('open');document.body.style.overflow=''}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal()});

/* ── TABS ── */
function showTab(id,btn){
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById(id)?.classList.add('active');
  if(btn) btn.classList.add('active');
  else{
    // find active by panel id
    const idx=['t-info','t-teintes','t-images'].indexOf(id);
    document.querySelectorAll('.tab-btn')[idx]?.classList.add('active');
  }
}

/* ── SHADE MANAGEMENT (modal) ── */
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
  // auto-cocher has_shades
  document.getElementById('hasShadesCb').checked=true;
}

function removeShade(i){
  shades.splice(i,1);
  renderShades();
  if(!shades.length) document.getElementById('hasShadesCb').checked=false;
}

function renderShades(){
  const list=document.getElementById('mShList');
  const msg=document.getElementById('noShMsg');
  const cnt=document.getElementById('cnt-sh');
  document.getElementById('shadesData').value=JSON.stringify(shades);
  cnt.textContent=shades.length;
  cnt.className=shades.length?'tab-count has':'tab-count';
  if(!shades.length){list.innerHTML='';msg.style.display='block';return;}
  msg.style.display='none';
  list.innerHTML=shades.map((s,i)=>`
    <div class="m-sh-row">
      <div class="m-sh-sw" style="background:${s.code}"></div>
      <span class="m-sh-nm">${s.nom}</span>
      <span class="m-sh-stk">Stock : ${s.stock}</span>
      <span class="m-sh-code">${s.code}</span>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeShade(${i})">✕</button>
    </div>`).join('');
}

/* ── GALLERY PREVIEW MODAL ── */
function previewGalleryModal(input){
  const strip=document.getElementById('galStripModal');
  const cnt=document.getElementById('cnt-img');
  if(!strip)return;
  strip.innerHTML='';
  Array.from(input.files).forEach(f=>{
    const r=new FileReader();
    r.onload=e=>{const img=document.createElement('img');img.src=e.target.result;strip.appendChild(img);};
    r.readAsDataURL(f);
  });
  cnt.textContent=input.files.length;
  cnt.className=input.files.length?'tab-count has':'tab-count';
}

/* ── GALLERY PREVIEW (édition) ── */
function previewGallery(input){
  const strip=document.getElementById('galStrip');
  if(!strip)return;
  strip.innerHTML='';
  Array.from(input.files).forEach(f=>{
    const r=new FileReader();
    r.onload=e=>{const img=document.createElement('img');img.src=e.target.result;strip.appendChild(img);};
    r.readAsDataURL(f);
  });
}

/* ── IMAGE PREVIEW ── */
function previewImg(input,id){
  const el=document.getElementById(id);
  if(!el||!input.files?.[0])return;
  const r=new FileReader();
  r.onload=e=>{el.src=e.target.result;el.style.display='block';};
  r.readAsDataURL(input.files[0]);
}

/* ── SHADE EDIT (page édition) ── */
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
  document.getElementById('shCol').value='#f0b8d0';
  document.getElementById('shStk').value='0';
  document.getElementById('colSw').style.background='#f0b8d0';
  document.getElementById('shLbl').textContent='+ Nouvelle teinte';
  document.getElementById('shBtn').textContent='✦ Ajouter';
}

/* ── SCROLL ANCHORS ── */
if(location.hash==='#teintes') setTimeout(()=>document.getElementById('teintes')?.scrollIntoView({behavior:'smooth'}),250);
if(location.hash==='#gallery') setTimeout(()=>document.getElementById('gallery')?.scrollIntoView({behavior:'smooth'}),250);
</script>
</body>
</html>