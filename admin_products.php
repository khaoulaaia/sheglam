<?php
// ============================================
//  SheGlamour — Gestion Produits + Teintes
//  /var/www/sheglamour/admin_products.php
//
//  Schéma réel :
//    products  → id, name, description, price, stock,
//                categorie, has_shades, active, image_url, created_at
//    teintes   → id, product_id, nom_teinte, code_couleur, stock
// ============================================
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/config.php'; // BASE_URL

$b       = BASE_URL ?? '';
$success = '';
$error   = '';

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
    } catch (Exception $e) {
        $error = "Erreur suppression image : " . $e->getMessage();
    }
}

// ─── ACTION : UPLOAD IMAGES SUPPLÉMENTAIRES ───────────────
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
                $pdo->prepare("INSERT INTO product_images (product_id, image) VALUES (?,?)")
                    ->execute([$pid, $filename]);
                $uploaded++;
            }
        }
    }
    header("Location: admin_products.php?edit=$pid&success=" . urlencode("$uploaded image(s) ajoutée(s).") . "#gallery");
    exit;
}

// ─── ACTION : SUPPRIMER UNE TEINTE ───────────────────────
if (isset($_GET['delete_shade'])) {
    $sid = (int) $_GET['delete_shade'];
    $pid = (int) ($_GET['pid'] ?? 0);
    try {
        $pdo->prepare("DELETE FROM teintes WHERE id = ?")->execute([$sid]);
        header("Location: admin_products.php?edit=$pid&success=" . urlencode("Teinte supprimée.") . "#teintes");
        exit;
    } catch (Exception $e) {
        $error = "Erreur suppression teinte : " . $e->getMessage();
    }
}

// ─── ACTION : SAUVEGARDER UNE TEINTE ─────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'save_shade') {
    $pid     = (int) $_POST['product_id'];
    $sid     = (int) ($_POST['shade_id'] ?? 0);
    $nom     = trim($_POST['nom_teinte'] ?? '');
    $code    = trim($_POST['code_couleur'] ?? '#000000');
    $stockSh = (int) ($_POST['stock_shade'] ?? 0);

    if (!$nom) {
        $error = "Le nom de la teinte est obligatoire.";
    } else {
        try {
            if ($sid) {
                $pdo->prepare("UPDATE teintes SET nom_teinte=?, code_couleur=?, stock=? WHERE id=?")
                    ->execute([$nom, $code, $stockSh, $sid]);
                $msg = "Teinte mise à jour.";
            } else {
                $pdo->prepare("INSERT INTO teintes (product_id, nom_teinte, code_couleur, stock) VALUES (?,?,?,?)")
                    ->execute([$pid, $nom, $code, $stockSh]);
                $msg = "Teinte ajoutée.";
            }
            header("Location: admin_products.php?edit=$pid&success=" . urlencode($msg) . "#teintes");
            exit;
        } catch (Exception $e) {
            $error = "Erreur teinte : " . $e->getMessage();
        }
    }
}

// ─── ACTION : SUPPRIMER UN PRODUIT ───────────────────────
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $imgRow = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $imgRow->execute([$id]);
        $imgPath = $imgRow->fetchColumn();
        // Supprimer le fichier image si stocké localement
        if ($imgPath && !str_starts_with($imgPath, 'http')) {
            $full = __DIR__ . '/images/' . basename($imgPath);
            if (file_exists($full)) unlink($full);
        }
        $pdo->prepare("DELETE FROM teintes WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        header("Location: admin_products.php?success=" . urlencode("Produit supprimé."));
        exit;
    } catch (Exception $e) {
        $error = "Erreur suppression : " . $e->getMessage();
    }
}

// ─── ACTION : SAUVEGARDER UN PRODUIT ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $id          = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float) str_replace(',', '.', $_POST['price'] ?? 0);
    $stock       = (int) ($_POST['stock'] ?? 0);
    $categorie   = trim($_POST['categorie'] ?? '');
    $has_shades  = isset($_POST['has_shades']) ? 1 : 0;
    $active      = isset($_POST['active']) ? 1 : 0;

    if (!$name || $price <= 0) {
        $error = "Le nom et le prix sont obligatoires.";
    } else {
        // Gestion image
        $imageUrl = $_POST['existing_image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $allowed)) {
                $error = "Format d'image non supporté (JPG, PNG, WEBP, GIF).";
            } else {
                $uploadDir = __DIR__ . '/images/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = uniqid('prod_') . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $imageUrl = $filename; // on stocke juste le nom de fichier
                } else {
                    $error = "Erreur upload image.";
                }
            }
        }

        if (!$error) {
            try {
                if ($id) {
                    // Mise à jour
                    if ($imageUrl !== null) {
                        $sql    = "UPDATE products SET name=?, description=?, price=?, stock=?, categorie=?, has_shades=?, active=?, image_url=? WHERE id=?";
                        $params = [$name, $description, $price, $stock, $categorie, $has_shades, $active, $imageUrl, $id];
                    } else {
                        $sql    = "UPDATE products SET name=?, description=?, price=?, stock=?, categorie=?, has_shades=?, active=? WHERE id=?";
                        $params = [$name, $description, $price, $stock, $categorie, $has_shades, $active, $id];
                    }
                    $pdo->prepare($sql)->execute($params);
                    header("Location: admin_products.php?edit=$id&success=" . urlencode("Produit mis à jour."));
                    exit;
                } else {
                    // Création
                    $pdo->prepare("INSERT INTO products (name, description, price, stock, categorie, has_shades, active, image_url) VALUES (?,?,?,?,?,?,?,?)")
                        ->execute([$name, $description, $price, $stock, $categorie, $has_shades, $active, $imageUrl]);
                    $newId = $pdo->lastInsertId();
                    $redir = $has_shades
                        ? "admin_products.php?edit=$newId&success=" . urlencode("Produit créé. Ajoutez maintenant les teintes.") . "#teintes"
                        : "admin_products.php?success=" . urlencode("Produit créé avec succès.");
                    header("Location: $redir");
                    exit;
                }
            } catch (Exception $e) {
                $error = "Erreur BDD : " . $e->getMessage();
            }
        }
    }
}

// ─── SUCCESS via GET ──────────────────────────────────────
if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

// ─── PRODUIT EN ÉDITION ───────────────────────────────────
$editProduct = null;
$editTeintes = [];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int) $_GET['edit']]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editProduct) {
        $tStmt = $pdo->prepare("SELECT * FROM teintes WHERE product_id = ? ORDER BY id ASC");
        $tStmt->execute([$editProduct['id']]);
        $editTeintes = $tStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ─── IMAGES SUPPLÉMENTAIRES EN ÉDITION ───────────────────
$extraImages = [];
if ($editProduct) {
    $iStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY id ASC");
    $iStmt->execute([$editProduct['id']]);
    $extraImages = $iStmt->fetchAll(PDO::FETCH_ASSOC);
}

// ─── TEINTE À PRÉ-REMPLIR ────────────────────────────────
$editShade = null;
if (isset($_GET['edit_shade'])) {
    $shEdit = $pdo->prepare("SELECT * FROM teintes WHERE id = ?");
    $shEdit->execute([(int) $_GET['edit_shade']]);
    $editShade = $shEdit->fetch(PDO::FETCH_ASSOC);
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

// Comptage teintes par produit
$shadeCountMap = [];
if ($products) {
    $ids = array_column($products, 'id');
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $sc  = $pdo->prepare("SELECT product_id, COUNT(*) AS cnt FROM teintes WHERE product_id IN ($ph) GROUP BY product_id");
    $sc->execute($ids);
    foreach ($sc->fetchAll(PDO::FETCH_ASSOC) as $r) $shadeCountMap[$r['product_id']] = (int)$r['cnt'];
}

$categories    = $pdo->query("SELECT DISTINCT categorie FROM products WHERE categorie IS NOT NULL AND categorie != '' ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);
$totalProducts = count($products);
$totalStock    = array_sum(array_column($products, 'stock'));
$activeCount   = count(array_filter($products, fn($p) => $p['active']));

// ─── HELPER : normalise l'URL image pour affichage ────────
function imgUrl(string $b, ?string $raw): string {
    if (!$raw) return $b . '/images/placeholder.jpg';
    if (str_starts_with($raw, 'http')) return $raw;
    return $b . '/images/' . basename($raw);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SheGlamour — Produits</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

/* SIDEBAR */
.sidebar{position:fixed;top:0;left:0;width:220px;height:100vh;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;padding:32px 0 24px}
.sidebar-logo{padding:0 24px 32px;font-family:var(--font-display);font-size:22px;letter-spacing:-.02em}
.sidebar-logo span{color:var(--accent)}
.sidebar-nav{display:flex;flex-direction:column;gap:2px;padding:0 12px;flex:1}
.nav-item{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:500;color:var(--muted);transition:all .2s;text-decoration:none}
.nav-item:hover{background:var(--surface2);color:var(--text)}
.nav-item.active{background:var(--surface2);color:var(--accent)}
.nav-icon{font-size:16px;width:20px;text-align:center}
.sidebar-footer{padding:0 24px;font-size:11px;color:var(--muted)}

/* MAIN */
.main{margin-left:220px;padding:40px 36px;min-height:100vh}
.page-header{margin-bottom:32px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
.page-header h1{font-family:var(--font-display);font-size:32px;letter-spacing:-.02em;background:linear-gradient(135deg,var(--text) 40%,var(--accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.page-header p{color:var(--muted);margin-top:4px;font-size:13px}

/* BTNS */
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .2s;text-decoration:none;white-space:nowrap}
.btn-primary{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#1a0a14}
.btn-primary:hover{opacity:.9;transform:translateY(-1px)}
.btn-ghost{background:var(--surface2);color:var(--text);border:1px solid var(--border)}
.btn-ghost:hover{border-color:var(--accent);color:var(--accent)}
.btn-danger{background:rgba(239,68,68,.12);color:var(--red);border:1px solid rgba(239,68,68,.2)}
.btn-danger:hover{background:rgba(239,68,68,.22)}
.btn-sm{padding:6px 12px;font-size:11px;border-radius:8px}

/* KPI */
.kpi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.kpi-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;position:relative;overflow:hidden}
.kpi-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--accent-color,transparent) 0%,transparent 60%);opacity:.07;pointer-events:none}
.kpi-label{font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.kpi-value{font-size:26px;font-weight:700}
.kpi-icon{position:absolute;top:18px;right:18px;font-size:24px;opacity:.15}

/* CARD */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:24px}
.card-header{padding:20px 24px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:8px}
.card-title{font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)}
.card-body{padding:24px}

/* FORM */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full{grid-column:1/-1}
.form-label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)}
.form-input{background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-family:var(--font-body);font-size:13px;transition:border-color .2s;width:100%}
.form-input:focus{outline:none;border-color:var(--accent)}
.form-input::placeholder{color:var(--muted)}
textarea.form-input{resize:vertical;min-height:90px}

/* TOGGLE */
.form-toggle{display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none}
.form-toggle input{width:0;height:0;opacity:0;position:absolute}
.toggle-track{width:42px;height:22px;background:var(--surface2);border:1px solid var(--border);border-radius:11px;position:relative;transition:background .2s;flex-shrink:0}
.toggle-track::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;background:var(--muted);border-radius:50%;transition:all .2s}
.form-toggle input:checked + .toggle-track{background:rgba(232,180,200,.2);border-color:var(--accent)}
.form-toggle input:checked + .toggle-track::after{left:23px;background:var(--accent)}

/* IMAGE UPLOAD */
.upload-zone{border:2px dashed var(--border);border-radius:10px;padding:24px;text-align:center;cursor:pointer;transition:all .2s;position:relative}
.upload-zone:hover{border-color:var(--accent);background:rgba(232,180,200,.03)}
.upload-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.upload-icon{font-size:28px;margin-bottom:8px;opacity:.4}
.upload-text{font-size:12px;color:var(--muted)}
.upload-preview{width:100%;max-height:160px;object-fit:contain;border-radius:8px;margin-top:10px;display:none}

/* TOAST */
.toast{padding:12px 20px;border-radius:10px;font-size:13px;font-weight:500;margin-bottom:20px;display:flex;align-items:center;gap:10px}
.toast-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:var(--green)}
.toast-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:var(--red)}

/* TOOLBAR */
.toolbar{display:flex;gap:12px;margin-bottom:20px;align-items:center;flex-wrap:wrap}
.search-wrap{position:relative;flex:1;min-width:180px}
.search-wrap .form-input{padding-left:36px}
.search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:14px;pointer-events:none}

/* TABLE */
.table-scroll{overflow-x:auto}
.table{width:100%;border-collapse:collapse;min-width:740px}
.table th{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);padding:0 12px 12px;text-align:left;border-bottom:1px solid var(--border);white-space:nowrap}
.table th:first-child{padding-left:0}
.table td{padding:14px 12px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:13px}
.table td:first-child{padding-left:0}
.table tr:last-child td{border-bottom:none}
.table tr:hover td{background:rgba(255,255,255,.012)}
.prod-img{width:44px;height:44px;border-radius:8px;object-fit:cover;background:var(--surface2)}
.prod-img-placeholder{width:44px;height:44px;border-radius:8px;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--muted)}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.05em}
.badge-active{background:rgba(34,197,94,.12);color:var(--green)}
.badge-inactive{background:rgba(124,124,138,.1);color:var(--muted)}
.badge-shades{background:rgba(201,160,220,.12);color:var(--accent2);text-decoration:none;transition:background .2s}
.badge-shades:hover{background:rgba(201,160,220,.22)}
.stock-low{color:var(--red);font-weight:700}
.stock-ok{color:var(--green);font-weight:700}
.stock-mid{color:var(--amber);font-weight:700}
.actions{display:flex;gap:8px;justify-content:flex-end}

/* EDIT PAGE */
.edit-layout{display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start}
.edit-layout .card{margin-bottom:0}

/* BREADCRUMB */
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--muted);margin-bottom:20px}
.breadcrumb a{color:var(--muted);text-decoration:none}
.breadcrumb a:hover{color:var(--accent)}

/* TEINTES */
.shades-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:20px}
.shade-card{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:12px 14px;display:flex;align-items:center;gap:10px}
.shade-swatch{width:32px;height:32px;border-radius:8px;flex-shrink:0;border:2px solid rgba(255,255,255,.1)}
.shade-info{flex:1;min-width:0}
.shade-name{font-weight:600;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.shade-stock-label{font-size:11px;color:var(--muted);margin-top:2px}
.shade-actions{display:flex;gap:6px;flex-shrink:0}

/* SHADE FORM */
.shade-form-box{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:18px;margin-top:4px}
.color-row{display:flex;align-items:center;gap:10px}
.color-swatch{width:36px;height:36px;border-radius:8px;border:2px solid rgba(255,255,255,.1);flex-shrink:0}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.72);z-index:200;display:none;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.modal-overlay.open{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:20px;width:90%;max-width:640px;max-height:92vh;overflow-y:auto}
.modal-header{padding:24px 28px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-title{font-family:var(--font-display);font-size:22px}
.modal-close{background:var(--surface2);border:1px solid var(--border);color:var(--muted);width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;transition:all .2s}
.modal-close:hover{color:var(--text);border-color:var(--text)}
.modal-body{padding:24px 28px}
.modal-footer{padding:14px 28px 22px;display:flex;gap:12px;justify-content:flex-end;border-top:1px solid var(--border)}

/* GALLERY */
.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;margin-bottom:16px}
.gallery-item{position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--border);aspect-ratio:1;background:var(--bg)}
.gallery-item img{width:100%;height:100%;object-fit:cover;display:block}
.gallery-item-del{position:absolute;top:5px;right:5px;background:rgba(0,0,0,.7);color:var(--red);border:1px solid rgba(239,68,68,.4);border-radius:6px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:11px;cursor:pointer;text-decoration:none;transition:background .2s}
.gallery-item-del:hover{background:rgba(239,68,68,.3)}
.gallery-upload-zone{border:2px dashed var(--border);border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:all .2s;position:relative}
.gallery-upload-zone:hover{border-color:var(--accent);background:rgba(232,180,200,.03)}
.gallery-upload-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.gallery-preview-strip{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.gallery-preview-strip img{width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid var(--border)}

/* Info box */
.info-box{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px 16px;font-size:11px;color:var(--muted);line-height:1.7;margin-top:16px}
.info-box strong{color:var(--text);display:block;margin-bottom:4px}
code{color:var(--accent)}

@media(max-width:960px){
  .sidebar{display:none}.main{margin-left:0;padding:20px}
  .kpi-grid,.edit-layout,.form-grid{grid-template-columns:1fr}
  .form-group.full{grid-column:1}
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-logo">She<span>Glamour</span></div>
  <nav class="sidebar-nav">
    <a class="nav-item" href="dashboard.php"><span class="nav-icon">◈</span> Tableau de bord</a>
    <a class="nav-item" href="admin_orders.php"><span class="nav-icon">◻</span> Commandes</a>
    <a class="nav-item active" href="admin_products.php"><span class="nav-icon">✦</span> Produits</a>
    <a class="nav-item" href="index.php" target="_blank"><span class="nav-icon">↗</span> Voir la boutique</a>
  </nav>
  <div class="sidebar-footer">SheGlamour Admin · v1.0</div>
</aside>

<main class="main">

<?php if ($editProduct): ?>
<!-- ══════════════════════════════════
     VUE ÉDITION PRODUIT + TEINTES
══════════════════════════════════ -->

  <div class="breadcrumb">
    <a href="admin_products.php">✦ Produits</a>
    <span>›</span>
    <span style="color:var(--text)"><?= htmlspecialchars($editProduct['name']) ?></span>
  </div>

  <?php if ($success): ?><div class="toast toast-success">✓ <?= $success ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="toast toast-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="page-header">
    <div>
      <h1>Modifier le produit</h1>
      <p>ID #<?= $editProduct['id'] ?></p>
    </div>
    <a href="admin_products.php" class="btn btn-ghost">← Retour à la liste</a>
  </div>

  <div class="edit-layout">

    <!-- ── FICHE PRODUIT ── -->
    <div class="card">
      <div class="card-header"><span class="card-title">Informations produit</span></div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
          <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editProduct['image_url'] ?? '') ?>">
          <div class="form-grid">

            <div class="form-group full">
              <label class="form-label">Nom *</label>
              <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($editProduct['name']) ?>" required>
            </div>

            <div class="form-group full">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-input"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">Prix (DA) *</label>
              <input type="number" name="price" class="form-input" step="0.01" min="0" value="<?= $editProduct['price'] ?>" required>
            </div>

            <div class="form-group">
              <label class="form-label">Stock global</label>
              <input type="number" name="stock" class="form-input" min="0" value="<?= (int)$editProduct['stock'] ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Catégorie</label>
              <input type="text" name="categorie" class="form-input"
                     value="<?= htmlspecialchars($editProduct['categorie'] ?? '') ?>"
                     list="catList" placeholder="Ex: Lèvres, Yeux, Teint…">
              <datalist id="catList">
                <?php foreach ($categories as $c): ?>
                  <option value="<?= htmlspecialchars($c) ?>">
                <?php endforeach; ?>
              </datalist>
            </div>

            <div class="form-group" style="gap:12px">
              <label class="form-label">Options</label>
              <label class="form-toggle">
                <input type="checkbox" name="has_shades" value="1" <?= $editProduct['has_shades'] ? 'checked' : '' ?>>
                <span class="toggle-track"></span>
                <span style="font-size:13px;color:var(--muted)">Produit avec teintes 🎨</span>
              </label>
              <label class="form-toggle">
                <input type="checkbox" name="active" value="1" <?= $editProduct['active'] ? 'checked' : '' ?>>
                <span class="toggle-track"></span>
                <span style="font-size:13px;color:var(--muted)">Actif sur la boutique</span>
              </label>
            </div>

            <div class="form-group full">
              <label class="form-label">Image</label>
              <div class="upload-zone">
                <input type="file" name="image" accept="image/*" onchange="previewImg(this,'eprev')">
                <div class="upload-icon">📷</div>
                <div class="upload-text">Nouvelle image pour remplacer l'actuelle<br><span style="font-size:11px">JPG, PNG, WEBP</span></div>
                <img id="eprev" class="upload-preview" alt="">
              </div>
              <?php if ($editProduct['image_url']): ?>
              <div style="margin-top:10px;display:flex;align-items:center;gap:10px">
                <img src="<?= htmlspecialchars(imgUrl($b, $editProduct['image_url'])) ?>"
                     style="height:56px;border-radius:8px;border:1px solid var(--border)" alt="">
                <span style="font-size:11px;color:var(--muted)">Image actuelle</span>
              </div>
              <?php endif; ?>
            </div>

          </div>
          <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap">
            <button type="submit" class="btn btn-primary">✦ Enregistrer les modifications</button>
            <a href="admin_products.php?delete=<?= $editProduct['id'] ?>" class="btn btn-danger"
               onclick="return confirm('Supprimer ce produit et toutes ses teintes ?')">✕ Supprimer</a>
          </div>
        </form>
      </div>
    </div>

    <!-- ── TEINTES ── -->
    <div id="teintes">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Teintes (<?= count($editTeintes) ?>)</span>
          <?php if ($editProduct['has_shades']): ?>
            <span style="font-size:11px;color:var(--muted)">Affichées dans la boutique</span>
          <?php endif; ?>
        </div>
        <div class="card-body">

          <?php if (!$editProduct['has_shades']): ?>
            <div style="text-align:center;padding:32px 0;color:var(--muted)">
              <div style="font-size:36px;margin-bottom:12px;opacity:.25">🎨</div>
              <p style="font-size:13px;line-height:1.6">Activez <strong style="color:var(--text)">"Produit avec teintes"</strong><br>dans le formulaire et enregistrez d'abord.</p>
            </div>

          <?php else: ?>

            <!-- Liste teintes existantes -->
            <?php if ($editTeintes): ?>
            <div class="shades-grid">
              <?php foreach ($editTeintes as $t): ?>
              <div class="shade-card">
                <div class="shade-swatch" style="background:<?= htmlspecialchars($t['code_couleur']) ?>"></div>
                <div class="shade-info">
                  <div class="shade-name"><?= htmlspecialchars($t['nom_teinte']) ?></div>
                  <div class="shade-stock-label">Stock : <strong><?= (int)($t['stock'] ?? 0) ?></strong></div>
                </div>
                <div class="shade-actions">
                  <button class="btn btn-ghost btn-sm"
                          onclick='loadShade(<?= json_encode($t, JSON_HEX_APOS) ?>)'
                          title="Modifier">✎</button>
                  <a href="admin_products.php?delete_shade=<?= $t['id'] ?>&pid=<?= $editProduct['id'] ?>"
                     class="btn btn-danger btn-sm"
                     onclick="return confirm('Supprimer «<?= htmlspecialchars(addslashes($t['nom_teinte'])) ?>» ?')"
                     title="Supprimer">✕</a>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php else: ?>
              <p style="color:var(--muted);font-size:13px;text-align:center;padding:12px 0 18px">Aucune teinte. Ajoutez-en une ci-dessous.</p>
            <?php endif; ?>

            <!-- Formulaire ajout / édition teinte -->
            <div class="shade-form-box">
              <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:14px" id="shadeFormLabel">+ Nouvelle teinte</div>
              <form method="POST">
                <input type="hidden" name="action" value="save_shade">
                <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
                <input type="hidden" name="shade_id" id="shadeId" value="<?= $editShade ? $editShade['id'] : '' ?>">

                <div class="form-grid" style="grid-template-columns:1fr 1fr 90px">

                  <div class="form-group">
                    <label class="form-label">Nom de la teinte *</label>
                    <input type="text" name="nom_teinte" id="shadeNom" class="form-input"
                           placeholder="Ex: Rose Poudré"
                           value="<?= $editShade ? htmlspecialchars($editShade['nom_teinte']) : '' ?>" required>
                  </div>

                  <div class="form-group">
                    <label class="form-label">Couleur (hex)</label>
                    <div class="color-row">
                      <input type="color" name="code_couleur" id="shadeColor" class="form-input"
                             value="<?= $editShade ? htmlspecialchars($editShade['code_couleur']) : '#e8b4c8' ?>"
                             style="padding:4px;height:40px;cursor:pointer"
                             oninput="document.getElementById('colorSwatch').style.background=this.value">
                      <div class="color-swatch" id="colorSwatch"
                           style="background:<?= $editShade ? htmlspecialchars($editShade['code_couleur']) : '#e8b4c8' ?>"></div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock_shade" id="shadeStock" class="form-input"
                           value="<?= $editShade ? (int)$editShade['stock'] : 0 ?>" min="0">
                  </div>

                </div>

                <div style="margin-top:14px;display:flex;gap:10px;align-items:center">
                  <button type="submit" class="btn btn-primary btn-sm" id="shadeSubmitBtn">
                    <?= $editShade ? '✦ Mettre à jour' : '✦ Ajouter la teinte' ?>
                  </button>
                  <button type="button" class="btn btn-ghost btn-sm" onclick="resetShadeForm()">Annuler</button>
                </div>
              </form>
            </div>

          <?php endif; ?>

        </div>
      </div>

      <div class="info-box">
        <strong>ℹ Intégration boutique</strong>
        Les teintes sont lues via <code>includes/get_shades.php?product_id=<?= $editProduct['id'] ?></code><br>
        Le bouton "Choisir une teinte" s'affiche si <code>has_shades = 1</code> et qu'au moins une teinte existe dans la table <code>teintes</code>.
      </div>
    </div>

  </div><!-- /edit-layout -->

  <!-- ── GALERIE IMAGES SUPPLÉMENTAIRES ── -->
  <div id="gallery" class="card" style="margin-top:24px">
    <div class="card-header">
      <span class="card-title">Galerie — Images supplémentaires (<?= count($extraImages) ?>)</span>
      <span style="font-size:11px;color:var(--muted)">Affichées comme miniatures sur la page produit</span>
    </div>
    <div class="card-body">

      <!-- Images existantes -->
      <?php if ($extraImages): ?>
      <div class="gallery-grid">
        <?php foreach ($extraImages as $img): ?>
        <div class="gallery-item">
          <img src="<?= htmlspecialchars(imgUrl($b, $img['image'])) ?>" alt="">
          <a href="admin_products.php?delete_img=<?= $img['id'] ?>&pid=<?= $editProduct['id'] ?>"
             class="gallery-item-del"
             onclick="return confirm('Supprimer cette image ?')">✕</a>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <p style="color:var(--muted);font-size:13px;margin-bottom:16px">Aucune image supplémentaire. Ajoutez-en ci-dessous.</p>
      <?php endif; ?>

      <!-- Upload nouvelles images -->
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_images">
        <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">

        <div class="gallery-upload-zone">
          <input type="file" name="extra_images[]" accept="image/*" multiple
                 onchange="previewGallery(this)">
          <div class="upload-icon">🖼️</div>
          <div class="upload-text">Cliquez ou glissez plusieurs images<br>
            <span style="font-size:11px">JPG, PNG, WEBP · Sélection multiple possible</span>
          </div>
        </div>

        <div class="gallery-preview-strip" id="galleryPreviewStrip"></div>

        <div style="margin-top:14px">
          <button type="submit" class="btn btn-primary btn-sm">✦ Ajouter ces images</button>
        </div>
      </form>

    </div>
  </div>

<?php else: ?>
<!-- ══════════════════════════════════
     VUE LISTE PRODUITS
══════════════════════════════════ -->

  <div class="page-header">
    <div>
      <h1>Produits</h1>
      <p>Catalogue — <?= $totalProducts ?> produit<?= $totalProducts !== 1 ? 's' : '' ?></p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addModal').classList.add('open')">✦ Nouveau produit</button>
  </div>

  <?php if ($success): ?><div class="toast toast-success">✓ <?= $success ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="toast toast-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <!-- KPIs -->
  <div class="kpi-grid">
    <div class="kpi-card" style="--accent-color:var(--accent)">
      <div class="kpi-label">Total produits</div>
      <div class="kpi-value"><?= $totalProducts ?></div>
      <div class="kpi-icon">✦</div>
    </div>
    <div class="kpi-card" style="--accent-color:var(--green)">
      <div class="kpi-label">Actifs</div>
      <div class="kpi-value"><?= $activeCount ?></div>
      <div class="kpi-icon">✅</div>
    </div>
    <div class="kpi-card" style="--accent-color:var(--blue)">
      <div class="kpi-label">Stock total</div>
      <div class="kpi-value"><?= number_format($totalStock) ?></div>
      <div class="kpi-icon">📦</div>
    </div>
  </div>

  <!-- TABLE -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Catalogue</span>
      <span style="font-size:12px;color:var(--muted)"><?= $totalProducts ?> résultat<?= $totalProducts !== 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body">

      <form method="GET" style="display:contents">
        <div class="toolbar">
          <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" name="q" class="form-input" placeholder="Rechercher…" value="<?= htmlspecialchars($search) ?>">
          </div>
          <select name="cat" class="form-input" style="width:180px" onchange="this.form.submit()">
            <option value="">Toutes catégories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $catFilter === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-ghost">Filtrer</button>
          <?php if ($search || $catFilter): ?><a href="admin_products.php" class="btn btn-ghost">✕ Reset</a><?php endif; ?>
        </div>
      </form>

      <div class="table-scroll">
        <table class="table">
          <thead>
            <tr>
              <th></th>
              <th>Nom</th>
              <th>Catégorie</th>
              <th>Prix</th>
              <th>Stock</th>
              <th>Teintes</th>
              <th>Statut</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <?php $thumb = imgUrl($b, $p['image_url'] ?? null); ?>
                <?php if (!empty($p['image_url'])): ?>
                  <img src="<?= htmlspecialchars($thumb) ?>" class="prod-img" alt="" onerror="this.style.display='none'">
                <?php else: ?>
                  <div class="prod-img-placeholder">✦</div>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($p['name']) ?></div>
                <?php if ($p['description']): ?>
                  <div style="font-size:11px;color:var(--muted);margin-top:2px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($p['description']) ?></div>
                <?php endif; ?>
              </td>
              <td style="color:var(--muted);font-size:12px"><?= $p['categorie'] ? htmlspecialchars($p['categorie']) : '—' ?></td>
              <td style="font-weight:700;color:var(--accent);white-space:nowrap"><?= number_format($p['price'],2,',',' ') ?> DA</td>
              <td>
                <?php $st = (int)$p['stock']; ?>
                <span class="<?= $st <= 0 ? 'stock-low' : ($st <= 5 ? 'stock-mid' : 'stock-ok') ?>"><?= $st ?></span>
              </td>
              <td>
                <?php $sc = $shadeCountMap[$p['id']] ?? 0; ?>
                <?php if ($p['has_shades']): ?>
                  <a href="admin_products.php?edit=<?= $p['id'] ?>#teintes" class="badge badge-shades">🎨 <?= $sc ?> teinte<?= $sc !== 1 ? 's' : '' ?></a>
                <?php else: ?>
                  <span style="color:var(--muted);font-size:12px">—</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $p['active'] ? 'badge-active' : 'badge-inactive' ?>"><?= $p['active'] ? 'Actif' : 'Inactif' ?></span>
              </td>
              <td>
                <div class="actions">
                  <a href="admin_products.php?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">✎ Éditer</a>
                  <a href="admin_products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm"
                     onclick="return confirm('Supprimer ce produit et ses teintes ?')">✕</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$products): ?>
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">Aucun produit trouvé</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <!-- ══ MODAL NOUVEAU PRODUIT ══ -->
  <div class="modal-overlay" id="addModal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal">
      <div class="modal-header">
        <div class="modal-title">Nouveau produit</div>
        <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">✕</button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="form-grid">

            <div class="form-group full">
              <label class="form-label">Nom *</label>
              <input type="text" name="name" class="form-input" placeholder="Ex: Rouge à lèvres Velvet" required>
            </div>

            <div class="form-group full">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-input" placeholder="Description courte du produit…"></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">Prix (DA) *</label>
              <input type="number" name="price" class="form-input" placeholder="0" step="0.01" min="0" required>
            </div>

            <div class="form-group">
              <label class="form-label">Stock initial</label>
              <input type="number" name="stock" class="form-input" value="0" min="0">
            </div>

            <div class="form-group">
              <label class="form-label">Catégorie</label>
              <input type="text" name="categorie" class="form-input"
                     placeholder="Ex: Lèvres, Yeux, Teint…" list="addCatList">
              <datalist id="addCatList">
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>">
                <?php endforeach; ?>
              </datalist>
            </div>

            <div class="form-group" style="gap:12px;justify-content:flex-end">
              <label class="form-label">Options</label>
              <label class="form-toggle" style="margin-top:4px">
                <input type="checkbox" name="has_shades" value="1">
                <span class="toggle-track"></span>
                <span style="font-size:13px;color:var(--muted)">Avec teintes 🎨</span>
              </label>
              <label class="form-toggle">
                <input type="checkbox" name="active" value="1" checked>
                <span class="toggle-track"></span>
                <span style="font-size:13px;color:var(--muted)">Actif dès la création</span>
              </label>
            </div>

            <div class="form-group full">
              <label class="form-label">Image produit</label>
              <div class="upload-zone">
                <input type="file" name="image" accept="image/*" onchange="previewImg(this,'addPrev')">
                <div class="upload-icon">📷</div>
                <div class="upload-text">Cliquez ou glissez une image<br><span style="font-size:11px">JPG, PNG, WEBP · Max 5 Mo</span></div>
                <img id="addPrev" class="upload-preview" alt="">
              </div>
              <p style="font-size:11px;color:var(--muted);margin-top:6px">
                L'image sera copiée dans <code>/images/</code> et référencée dans la BDD.<br>
                Si le produit a des teintes, ajoutez-les après création.
              </p>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" onclick="document.getElementById('addModal').classList.remove('open')">Annuler</button>
          <button type="submit" class="btn btn-primary">✦ Créer le produit</button>
        </div>
      </form>
    </div>
  </div>

<?php endif; ?>
</main>

<script>
function previewGallery(input) {
  const strip = document.getElementById('galleryPreviewStrip');
  if (!strip) return;
  strip.innerHTML = '';
  Array.from(input.files).forEach(file => {
    const r = new FileReader();
    r.onload = e => {
      const img = document.createElement('img');
      img.src = e.target.result;
      strip.appendChild(img);
    };
    r.readAsDataURL(file);
  });
}

function previewImg(input, id) {
  const el = document.getElementById(id);
  if (!el || !input.files?.[0]) return;
  const r = new FileReader();
  r.onload = e => { el.src = e.target.result; el.style.display = 'block'; };
  r.readAsDataURL(input.files[0]);
}

function loadShade(sh) {
  document.getElementById('shadeId').value    = sh.id;
  document.getElementById('shadeNom').value   = sh.nom_teinte;
  document.getElementById('shadeColor').value = sh.code_couleur;
  document.getElementById('shadeStock').value = sh.stock || 0;
  document.getElementById('colorSwatch').style.background  = sh.code_couleur;
  document.getElementById('shadeFormLabel').textContent    = '✎ Modifier — ' + sh.nom_teinte;
  document.getElementById('shadeSubmitBtn').textContent    = '✦ Mettre à jour';
  document.querySelector('.shade-form-box')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function resetShadeForm() {
  document.getElementById('shadeId').value    = '';
  document.getElementById('shadeNom').value   = '';
  document.getElementById('shadeColor').value = '#e8b4c8';
  document.getElementById('shadeStock').value = '0';
  document.getElementById('colorSwatch').style.background = '#e8b4c8';
  document.getElementById('shadeFormLabel').textContent   = '+ Nouvelle teinte';
  document.getElementById('shadeSubmitBtn').textContent   = '✦ Ajouter la teinte';
}

if (location.hash === '#teintes') {
  setTimeout(() => document.getElementById('teintes')?.scrollIntoView({ behavior: 'smooth' }), 250);
}
if (location.hash === '#gallery') {
  setTimeout(() => document.getElementById('gallery')?.scrollIntoView({ behavior: 'smooth' }), 250);
}
</script>
</body>
</html>