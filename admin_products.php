<?php
// ============================================
//  SheGlamour — Gestion Produits v3.6
//  Teintes : couleur OU image (au choix)
// ============================================
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/config.php';

$b       = BASE_URL ?? '';
$success = '';
$error   = '';

// ── Helpers ───────────────────────────────────────────────────────────────────
function imgUrl(string $b, ?string $raw): string {
    if (!$raw) return $b . '/images/placeholder.jpg';
    if (str_starts_with($raw, 'http')) return $raw;
    return $b . '/images/' . basename($raw);
}

function ensureUploadDir(): string {
    $uploadDir = __DIR__ . '/images/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    return $uploadDir;
}

function uploadImage(string $fileKey, ?string $oldFile = null): ?string {
    global $error;
    if (!isset($_FILES[$fileKey])) return null;
    if ($_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if (empty($_FILES[$fileKey]['tmp_name'])) return null;
    if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        $error = "Erreur upload (code " . $_FILES[$fileKey]['error'] . ").";
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
        $error = "Extension « $ext » refusée.";
        return null;
    }
    $uploadDir = ensureUploadDir();
    if (!is_writable($uploadDir)) { $error = "Dossier images/ non accessible."; return null; }
    $filename = uniqid('img_') . '.' . $ext;
    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadDir . $filename)) {
        $error = "Impossible de déplacer le fichier.";
        return null;
    }
    if ($oldFile && !str_starts_with($oldFile, 'http')) {
        $old = $uploadDir . basename($oldFile);
        if (file_exists($old)) unlink($old);
    }
    return $filename;
}

function uploadImageIndexed(string $fileKey, int $idx, ?string $oldFile = null): ?string {
    if (!isset($_FILES[$fileKey]['tmp_name'][$idx])) return null;
    if (empty($_FILES[$fileKey]['tmp_name'][$idx])) return null;
    if ($_FILES[$fileKey]['error'][$idx] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'][$idx], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) return null;
    $uploadDir = ensureUploadDir();
    if (!is_writable($uploadDir)) return null;
    $filename = uniqid('shade_') . '.' . $ext;
    if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'][$idx], $uploadDir . $filename)) return null;
    if ($oldFile && !str_starts_with($oldFile, 'http')) {
        $old = $uploadDir . basename($oldFile);
        if (file_exists($old)) unlink($old);
    }
    return $filename;
}

function uploadGalleryImages(int $productId, PDO $pdo): int {
    $uploaded  = 0;
    $uploadDir = ensureUploadDir();
    if (!isset($_FILES['extra_images']['tmp_name'])) return 0;
    foreach ($_FILES['extra_images']['tmp_name'] as $i => $tmp) {
        if (empty($tmp)) continue;
        if ($_FILES['extra_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $ext = strtolower(pathinfo($_FILES['extra_images']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
        $filename = uniqid('gal_') . '.' . $ext;
        if (move_uploaded_file($tmp, $uploadDir . $filename)) {
            $pdo->prepare("INSERT INTO product_images (product_id, image) VALUES (?, ?)")
                ->execute([$productId, $filename]);
            $uploaded++;
        }
    }
    return $uploaded;
}

function deleteFile(?string $filename): void {
    if (!$filename || str_starts_with($filename, 'http')) return;
    $full = __DIR__ . '/images/' . basename($filename);
    if (file_exists($full)) unlink($full);
}

/**
 * Rendu visuel d'une teinte : image OU pastille couleur.
 * Utilisé dans la liste produits, les formulaires, et le dashboard.
 *
 * @param string|null $image      Nom de fichier dans /images/
 * @param string|null $couleur    Code hex (#rrggbb)
 * @param string      $baseUrl    BASE_URL
 * @param int         $size       Taille en px (défaut 28)
 * @param string      $extraCss   Classes/styles supplémentaires
 */
function shadeVisual(?string $image, ?string $couleur, string $baseUrl = '', int $size = 28, string $extraCss = ''): string {
    $s = "width:{$size}px;height:{$size}px;border-radius:50%;border:2px solid #ede5de;display:inline-block;flex-shrink:0;overflow:hidden;vertical-align:middle;{$extraCss}";
    if ($image) {
        $src = str_starts_with($image, 'http') ? $image : $baseUrl . '/images/' . basename($image);
        return "<span style=\"{$s}\"><img src=\"" . htmlspecialchars($src) . "\" alt=\"\" style=\"width:100%;height:100%;object-fit:cover;\"></span>";
    }
    $bg = htmlspecialchars($couleur ?? '#cccccc');
    return "<span style=\"{$s}background:{$bg}\"></span>";
}

// ── Suppression image galerie ─────────────────────────────────────────────────
if (isset($_GET['delete_img'])) {
    $iid = (int) $_GET['delete_img'];
    $pid = (int) ($_GET['pid'] ?? 0);
    try {
        $row = $pdo->prepare("SELECT image FROM product_images WHERE id = ?");
        $row->execute([$iid]);
        deleteFile($row->fetchColumn());
        $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$iid]);
        header("Location: admin_products.php?edit=$pid&success=" . urlencode("Image supprimée.") . "#gallery");
        exit;
    } catch (Exception $e) { $error = "Erreur suppression image : " . $e->getMessage(); }
}

// ── Suppression teinte ────────────────────────────────────────────────────────
if (isset($_GET['delete_shade'])) {
    $sid = (int) $_GET['delete_shade'];
    $pid = (int) ($_GET['pid'] ?? 0);
    try {
        $imgRow = $pdo->prepare("SELECT image FROM teintes WHERE id = ?");
        $imgRow->execute([$sid]);
        deleteFile($imgRow->fetchColumn());
        $pdo->prepare("DELETE FROM teintes WHERE id = ?")->execute([$sid]);
        header("Location: admin_products.php?edit=$pid&success=" . urlencode("Teinte supprimée.") . "#teintes");
        exit;
    } catch (Exception $e) { $error = "Erreur : " . $e->getMessage(); }
}

// ── Sauvegarde teinte (COULEUR ou IMAGE) ─────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'save_shade') {
    $pid       = (int)    $_POST['product_id'];
    $sid       = (int)   ($_POST['shade_id']            ?? 0);
    $nom       =  trim($_POST['nom_teinte']              ?? '');
    $stockSh   = (int)   ($_POST['stock_shade']          ?? 0);
    $prixSh    = (float)  str_replace(',', '.', $_POST['prix_shade'] ?? 0);
    $shadeMode =          ($_POST['shade_mode']          ?? 'color'); // 'color' | 'image'
    $oldImg    =          ($_POST['existing_shade_image']  ?? '') ?: null;
    $code      =  trim($_POST['code_couleur']            ?? '#000000');

    if (!$nom) {
        $error = "Le nom de la teinte est obligatoire.";
    } else {
        $finalImage  = null;
        $finalColor  = null;

        if ($shadeMode === 'image') {
            // ─ Mode IMAGE ─
            $newImg = uploadImage('shade_image', $oldImg);
            if ($newImg) {
                $finalImage = $newImg;
                $finalColor = null; // on efface la couleur
            } elseif ($oldImg) {
                // pas de nouvelle image uploadée → on garde l'ancienne
                $finalImage = $oldImg;
                $finalColor = null;
            } else {
                $error = "Veuillez sélectionner une image pour la teinte.";
            }
        } else {
            // ─ Mode COULEUR ─
            // Si une ancienne image existait, on la supprime
            if ($oldImg) deleteFile($oldImg);
            $finalImage = null;
            $finalColor = $code ?: '#000000';
        }

        if (!$error) {
            try {
                if ($sid) {
                    $pdo->prepare("
                        UPDATE teintes
                        SET nom_teinte=?, code_couleur=?, stock=?, prix=?, image=?
                        WHERE id=?
                    ")->execute([$nom, $finalColor, $stockSh, $prixSh ?: null, $finalImage, $sid]);
                    $success = "Teinte mise à jour.";
                } else {
                    $pdo->prepare("
                        INSERT INTO teintes (product_id, nom_teinte, code_couleur, stock, prix, image)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ")->execute([$pid, $nom, $finalColor, $stockSh, $prixSh ?: null, $finalImage]);
                    $success = "Teinte ajoutée.";
                }
                header("Location: admin_products.php?edit=$pid&success=" . urlencode($success) . "#teintes");
                exit;
            } catch (Exception $e) { $error = "Erreur teinte : " . $e->getMessage(); }
        }
    }
}

// ── Upload images galerie ─────────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'upload_images') {
    $pid      = (int) $_POST['product_id'];
    $uploaded = uploadGalleryImages($pid, $pdo);
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
    $name           = trim($_POST['name']          ?? '');
    $description    = trim($_POST['description']   ?? '');
    $price          = (float) str_replace(',', '.', $_POST['price']     ?? 0);
    $old_price      = (float) str_replace(',', '.', $_POST['old_price'] ?? 0);
    $stock          = (int)  ($_POST['stock']       ?? 0);
    $categorie      = trim($_POST['categorie']      ?? '');
    $sous_categorie = trim($_POST['sous_categorie'] ?? '');
    $marque         = trim($_POST['marque']         ?? '');
    $has_shades     = isset($_POST['has_shades'])   ? true : false;
    $active         = isset($_POST['active'])       ? true : false; // CORRECTION : false par défaut
    $is_sale        = isset($_POST['is_sale'])       ? true : false;
    $is_pack        = isset($_POST['is_pack'])       ? true : false;
    $shadesJson     = $_POST['shades_data']         ?? '[]';

    if ($is_sale && $old_price <= 0) {
        $error = "Renseignez l'ancien prix pour un article en solde.";
    } elseif (!$name || $price <= 0) {
        $error = "Le nom et le prix sont obligatoires.";
    } else {
        $imageUrl    = uploadImage('image');
        $uploadError = $error;
        $error       = '';

        try {
            $pdo->prepare("
                INSERT INTO products
                    (name, description, price, old_price, stock,
                     categorie, sous_categorie, marque,
                     has_shades, active, image_url, is_sale, is_pack)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
            ")->execute([
                $name, $description, $price,
                ($old_price > 0 ? $old_price : null), $stock,
                $categorie ?: null, $sous_categorie ?: null, $marque ?: null,
                $has_shades ? 'true' : 'false',
                $active     ? 'true' : 'false',
                $imageUrl,
                $is_sale    ? 'true' : 'false',
                $is_pack    ? 'true' : 'false',
            ]);
            $newId = $pdo->lastInsertId();

            // Teintes avec mode couleur/image
            $shades          = json_decode($shadesJson, true) ?: [];
            $hasActualShades = false;
            foreach ($shades as $idx => $sh) {
                $nom       = trim($sh['nom']   ?? '');
                $shadeMode = trim($sh['mode']  ?? 'color'); // 'color' | 'image'
                $stSh      = (int)   ($sh['stock'] ?? 0);
                $pxSh      = (float) ($sh['prix']  ?? 0);

                if (!$nom) continue;

                $finalColor = null;
                $finalImage = null;

                if ($shadeMode === 'image') {
                    $finalImage = uploadImageIndexed('shade_images', $idx);
                } else {
                    $finalColor = trim($sh['code'] ?? '#000000');
                }

                $pdo->prepare("
                    INSERT INTO teintes (product_id, nom_teinte, code_couleur, stock, prix, image)
                    VALUES (?,?,?,?,?,?)
                ")->execute([$newId, $nom, $finalColor, $stSh, $pxSh ?: null, $finalImage]);
                $hasActualShades = true;
            }
            if ($hasActualShades) {
                $pdo->prepare("UPDATE products SET has_shades = true WHERE id = ?")->execute([$newId]);
            }

            uploadGalleryImages($newId, $pdo);

            $msg = $uploadError
                ? "Produit créé mais image ignorée : $uploadError"
                : "Produit créé avec succès !";
            header("Location: admin_products.php?edit=$newId&success=" . urlencode($msg));
            exit;
        } catch (Exception $e) { $error = "Erreur BDD : " . $e->getMessage(); }
    }
}

// ── Mise à jour produit ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_product') {
    $id             = (int)   $_POST['id'];
    $name           = trim($_POST['name']          ?? '');
    $description    = trim($_POST['description']   ?? '');
    $price          = (float) str_replace(',', '.', $_POST['price']     ?? 0);
    $old_price      = (float) str_replace(',', '.', $_POST['old_price'] ?? 0);
    $stock          = (int)  ($_POST['stock']       ?? 0);
    $categorie      = trim($_POST['categorie']      ?? '');
    $sous_categorie = trim($_POST['sous_categorie'] ?? '');
    $marque         = trim($_POST['marque']         ?? '');
    $has_shades     = isset($_POST['has_shades'])   ? true : false;
    $active         = isset($_POST['active'])       ? true : false;
    $is_sale        = isset($_POST['is_sale'])       ? true : false;
    $is_pack        = isset($_POST['is_pack'])       ? true : false;

    if (!$name || $price <= 0) {
        $error = "Le nom et le prix sont obligatoires.";
    } else {
        $existingImg = (($_POST['existing_image'] ?? '') !== '') ? $_POST['existing_image'] : null;
        $newImg      = uploadImage('image', $existingImg);
        $uploadError = $error;
        $error       = '';
        $imageUrl    = $newImg ?? $existingImg;

        try {
            $pdo->prepare("
                UPDATE products SET
                    name=?, description=?, price=?, old_price=?, stock=?,
                    categorie=?, sous_categorie=?, marque=?,
                    has_shades=?, active=?, image_url=?, is_sale=?, is_pack=?
                WHERE id=?
            ")->execute([
                $name, $description, $price,
                ($old_price > 0 ? $old_price : null), $stock,
                $categorie ?: null, $sous_categorie ?: null, $marque ?: null,
                $has_shades ? 'true' : 'false',
                $active     ? 'true' : 'false',
                $imageUrl,
                $is_sale    ? 'true' : 'false',
                $is_pack    ? 'true' : 'false',
                $id,
            ]);

            $msg = $uploadError
                ? "Produit mis à jour (image ignorée : $uploadError)"
                : "Produit mis à jour.";
            header("Location: admin_products.php?edit=$id&success=" . urlencode($msg));
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
$saleFilter    = isset($_GET['is_sale']) ? (bool)$_GET['is_sale'] : null;
$packFilter    = isset($_GET['is_pack']) ? (bool)$_GET['is_pack'] : null;

$where = []; $params = [];
if ($search)        { $where[] = "(name ILIKE ? OR description ILIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilter)     { $where[] = "categorie = ?";      $params[] = $catFilter; }
if ($sousCatFilter) { $where[] = "sous_categorie = ?"; $params[] = $sousCatFilter; }
if ($saleFilter !== null) { $where[] = "is_sale = ?";  $params[] = $saleFilter ? 'true' : 'false'; }
if ($packFilter !== null) { $where[] = "is_pack = ?";  $params[] = $packFilter ? 'true' : 'false'; }

$whereSql     = $where ? 'WHERE ' . implode(' AND ', $where) : '';
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

try {
    $categories = $pdo->query("
        SELECT DISTINCT categorie FROM products
        WHERE categorie IS NOT NULL AND categorie != ''
        ORDER BY categorie
    ")->fetchAll(PDO::FETCH_COLUMN);

    $sous_categories = $pdo->query("
        SELECT DISTINCT sous_categorie FROM products
        WHERE sous_categorie IS NOT NULL AND sous_categorie != ''
        ORDER BY sous_categorie
    ")->fetchAll(PDO::FETCH_COLUMN);

    $marques = $pdo->query("
        SELECT DISTINCT marque FROM products
        WHERE marque IS NOT NULL AND marque != ''
        ORDER BY marque
    ")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = $sous_categories = $marques = [];
    $error = "Erreur chargement filtres : " . $e->getMessage();
}

$totalProducts = count($products);
$totalStock    = array_sum(array_column($products, 'stock'));
$activeCount   = count(array_filter($products, fn($p) => $p['active']));
$saleCount     = count(array_filter($products, fn($p) => $p['is_sale']));
$packCount     = count(array_filter($products, fn($p) => $p['is_pack']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>SheGlamour — Produits</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --bg:#f9f5f2; --surface:#fff; --surface2:#f4eeea; --border:#ede5de; --border2:#e0d5cc;
  --text:#16100e; --text2:#4a3c36; --muted:#9c8d85; --muted2:#c0afa6;
  --rose:#c4697a; --rose-d:#a8505f; --rose-bg:#fdf0f2; --rose-lt:#f5d0d7;
  --plum:#8b5a8b; --plum-bg:#f5eef5; --plum-lt:#dfc8df;
  --green:#3a8a5c; --green-bg:#eef7f2; --green-lt:#b8dfc9;
  --amber:#b07030; --amber-bg:#fdf5eb; --amber-lt:#f0d4a8;
  --blue:#3a6db0; --blue-bg:#eef3fb; --blue-lt:#b8cff0;
  --red:#c0392b; --red-bg:#fdf0ee; --red-lt:#f0c0bb;
  --r:14px; --shadow:0 2px 8px rgba(0,0,0,.06),0 8px 24px rgba(0,0,0,.05);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;font-size:14px;line-height:1.5}

/* ── LAYOUT ── */
.page{max-width:1100px;margin:0 auto;padding:36px 24px 60px}
.page-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:28px;gap:12px;flex-wrap:wrap}
.page-header h1{font-family:'Cormorant Garamond',serif;font-size:32px;letter-spacing:-.02em;color:var(--text)}
.page-header p{color:var(--muted);font-size:13px;margin-top:4px}

/* ── ALERTS ── */
.alert{padding:12px 16px;border-radius:var(--r);font-size:13px;font-weight:600;margin-bottom:20px;display:flex;gap:8px;align-items:center}
.alert-success{background:var(--green-bg);color:var(--green);border:1px solid var(--green-lt)}
.alert-error  {background:var(--red-bg);  color:var(--red);  border:1px solid var(--red-lt)}

/* ── CARDS ── */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.card-head{padding:14px 20px;background:var(--surface2);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.card-title{font-size:10.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
.card-body{padding:20px}

/* ── FORM ── */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
.form-full{grid-column:1/-1}
.form-group{display:flex;flex-direction:column;gap:6px}
label{font-size:11.5px;font-weight:700;letter-spacing:.04em;color:var(--text2)}
input,select,textarea{
  width:100%;padding:10px 13px;border:1.5px solid var(--border);
  border-radius:10px;font-size:13.5px;font-family:'DM Sans',sans-serif;
  outline:none;transition:border-color .15s,background .15s;
  background:#faf8f6;color:var(--text)
}
input:focus,select:focus,textarea:focus{border-color:var(--rose);background:#fff}
textarea{resize:vertical;min-height:80px}
input[type=color]{padding:3px 6px;height:40px;cursor:pointer}
input[type=checkbox]{width:auto;accent-color:var(--rose)}
.hint{font-size:11px;color:var(--muted2)}

/* ── SHADE MODE TOGGLE ── */
.shade-mode-wrap{display:flex;gap:0;border:1.5px solid var(--border);border-radius:10px;overflow:hidden;background:var(--surface2)}
.shade-mode-btn{
  flex:1;padding:9px 14px;font-size:12.5px;font-weight:700;font-family:'DM Sans',sans-serif;
  background:none;border:none;cursor:pointer;color:var(--muted);
  transition:background .15s,color .15s;display:flex;align-items:center;justify-content:center;gap:7px
}
.shade-mode-btn.active{background:var(--rose);color:#fff}
.shade-mode-btn:first-child{border-right:1px solid var(--border)}

/* Preview couleur inline */
.color-preview-wrap{display:flex;gap:8px;align-items:center}
.color-preview-swatch{width:38px;height:38px;border-radius:50%;border:2px solid var(--border);flex-shrink:0;transition:background .2s}
input[type=color]#shade_color_input,input[type=color].shade-color-picker{width:calc(100% - 46px)}

/* Preview image sélectionnée */
.img-preview-wrap{position:relative;display:inline-block}
.img-preview-thumb{width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--border);display:block}
.img-preview-remove{position:absolute;top:-4px;right:-4px;width:18px;height:18px;border-radius:50%;background:var(--red);color:#fff;border:none;cursor:pointer;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:800}

/* ── TEINTES LIST ── */
.teinte-item{
  display:flex;align-items:center;gap:12px;padding:11px 16px;
  border:1px solid var(--border);border-radius:12px;background:var(--surface);
  margin-bottom:8px;transition:box-shadow .15s
}
.teinte-item:hover{box-shadow:0 2px 8px rgba(0,0,0,.08)}
.teinte-visual{width:36px;height:36px;border-radius:50%;border:2px solid var(--border);flex-shrink:0;overflow:hidden}
.teinte-visual img{width:100%;height:100%;object-fit:cover}
.teinte-info{flex:1;min-width:0}
.teinte-name{font-size:13.5px;font-weight:700}
.teinte-meta{font-size:11px;color:var(--muted);margin-top:2px}
.teinte-type{font-size:10px;font-weight:700;padding:2px 7px;border-radius:8px}
.teinte-type.img{background:var(--blue-bg);color:var(--blue);border:1px solid var(--blue-lt)}
.teinte-type.color{background:var(--plum-bg);color:var(--plum);border:1px solid var(--plum-lt)}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:9px;font-size:12.5px;font-weight:700;font-family:'DM Sans',sans-serif;cursor:pointer;transition:all .15s;border:none;text-decoration:none;white-space:nowrap}
.btn-rose{background:var(--rose);color:#fff}.btn-rose:hover{background:var(--rose-d)}
.btn-outline{background:none;color:var(--text2);border:1.5px solid var(--border)}.btn-outline:hover{background:var(--surface2)}
.btn-red{background:var(--red-bg);color:var(--red);border:1px solid var(--red-lt)}.btn-red:hover{background:var(--red);color:#fff}
.btn-sm{padding:5px 12px;font-size:11.5px;border-radius:7px}
.btn-ghost{background:none;border:none;cursor:pointer;color:var(--muted);transition:color .15s;padding:4px;display:inline-flex}
.btn-ghost:hover{color:var(--red)}

/* ── TABLE produits ── */
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.tbl{width:100%;border-collapse:collapse;min-width:700px}
.tbl th{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);padding:0 10px 11px 0;text-align:left;border-bottom:2px solid var(--border)}
.tbl td{padding:11px 10px 11px 0;border-bottom:1px solid var(--border);font-size:13px;color:var(--text2);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:var(--surface2)}
.prod-img{width:40px;height:40px;border-radius:8px;object-fit:cover;border:1px solid var(--border)}

/* ── BADGES ── */
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.03em}
.bdg-green{background:var(--green-bg);color:var(--green);border:1px solid var(--green-lt)}
.bdg-red  {background:var(--red-bg);  color:var(--red);  border:1px solid var(--red-lt)}
.bdg-rose {background:var(--rose-bg); color:var(--rose); border:1px solid var(--rose-lt)}
.bdg-amber{background:var(--amber-bg);color:var(--amber);border:1px solid var(--amber-lt)}
.bdg-gray {background:var(--surface2);color:var(--muted);border:1px solid var(--border)}

/* ── SHADE PREVIEW CLUSTERS ── */
.shade-cluster{display:flex;gap:-4px}
.shade-cluster > *{margin-right:-6px;box-shadow:0 0 0 2px #fff}

/* ── SECTION ANCHOR ── */
.section-anchor{scroll-margin-top:20px}

@media(max-width:680px){
  .form-grid,.form-grid-3{grid-template-columns:1fr}
  .page{padding:20px 14px 50px}
}
</style>
</head>
<body>
<div class="page">

<?php if ($success): ?>
  <div class="alert alert-success">✓ <?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-error">⚠ <?= $error ?></div>
<?php endif; ?>

<?php if ($editProduct): ?>
<!-- ════════════════════════════════════════════════════════════════════════════
     VUE ÉDITION PRODUIT
═════════════════════════════════════════════════════════════════════════════ -->
<div class="page-header">
  <div>
    <h1><?= htmlspecialchars($editProduct['name']) ?></h1>
    <p><a href="admin_products.php" style="color:var(--rose);text-decoration:none">← Retour à la liste</a></p>
  </div>
  <form method="GET" action="admin_products.php" onsubmit="return confirm('Supprimer ce produit ?')">
    <input type="hidden" name="delete" value="<?= $editProduct['id'] ?>">
    <button class="btn btn-red">🗑 Supprimer le produit</button>
  </form>
</div>

<!-- ─ Infos produit ─────────────────────────────────────────────────────────── -->
<div class="card">
  <div class="card-head"><span class="card-title">Informations produit</span></div>
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="update_product">
      <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
      <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editProduct['image_url'] ?? '') ?>">

      <div class="form-grid" style="margin-bottom:14px">
        <div class="form-group form-full">
          <label>Nom du produit *</label>
          <input type="text" name="name" value="<?= htmlspecialchars($editProduct['name']) ?>" required>
        </div>
        <div class="form-group">
          <label>Prix (DA) *</label>
          <input type="number" name="price" step="0.01" value="<?= $editProduct['price'] ?>" required>
        </div>
        <div class="form-group">
          <label>Ancien prix (DA)</label>
          <input type="number" name="old_price" step="0.01" value="<?= $editProduct['old_price'] ?? '' ?>">
        </div>
        <div class="form-group">
          <label>Stock</label>
          <input type="number" name="stock" value="<?= $editProduct['stock'] ?? 0 ?>">
        </div>
        <div class="form-group">
          <label>Catégorie</label>
          <input type="text" name="categorie" list="cats" value="<?= htmlspecialchars($editProduct['categorie'] ?? '') ?>">
          <datalist id="cats"><?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?></datalist>
        </div>
        <div class="form-group">
          <label>Sous-catégorie</label>
          <input type="text" name="sous_categorie" list="scats" value="<?= htmlspecialchars($editProduct['sous_categorie'] ?? '') ?>">
          <datalist id="scats"><?php foreach ($sous_categories as $sc): ?><option value="<?= htmlspecialchars($sc) ?>"><?php endforeach; ?></datalist>
        </div>
        <div class="form-group">
          <label>Marque</label>
          <input type="text" name="marque" list="marqs" value="<?= htmlspecialchars($editProduct['marque'] ?? '') ?>">
          <datalist id="marqs"><?php foreach ($marques as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?></datalist>
        </div>
        <div class="form-group form-full">
          <label>Description</label>
          <textarea name="description"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group form-full">
          <label>Image principale</label>
          <?php if ($editProduct['image_url']): ?>
            <img src="<?= imgUrl($b, $editProduct['image_url']) ?>" style="width:80px;height:80px;border-radius:10px;object-fit:cover;border:1px solid var(--border);margin-bottom:8px">
          <?php endif; ?>
          <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group" style="gap:12px;flex-direction:row;flex-wrap:wrap;align-items:center">
          <label style="display:flex;gap:7px;align-items:center;cursor:pointer">
            <input type="checkbox" name="active" <?= $editProduct['active'] ? 'checked' : '' ?>> Actif
          </label>
          <label style="display:flex;gap:7px;align-items:center;cursor:pointer">
            <input type="checkbox" name="has_shades" <?= $editProduct['has_shades'] ? 'checked' : '' ?>> A des teintes
          </label>
          <label style="display:flex;gap:7px;align-items:center;cursor:pointer">
            <input type="checkbox" name="is_sale" <?= $editProduct['is_sale'] ? 'checked' : '' ?>> En solde
          </label>
          <label style="display:flex;gap:7px;align-items:center;cursor:pointer">
            <input type="checkbox" name="is_pack" <?= $editProduct['is_pack'] ? 'checked' : '' ?>> Pack
          </label>
        </div>
      </div>
      <button class="btn btn-rose" type="submit">💾 Enregistrer les modifications</button>
    </form>
  </div>
</div>

<!-- ─ TEINTES ────────────────────────────────────────────────────────────────── -->
<div class="card section-anchor" id="teintes">
  <div class="card-head">
    <span class="card-title">🎨 Teintes (<?= count($editTeintes) ?>)</span>
    <button class="btn btn-rose btn-sm" onclick="openShadeModal()">+ Ajouter une teinte</button>
  </div>
  <div class="card-body">
    <?php if ($editTeintes): foreach ($editTeintes as $t): ?>
    <div class="teinte-item">
      <!-- Visuel : image OU couleur -->
      <div class="teinte-visual">
        <?php if ($t['image']): ?>
          <img src="<?= imgUrl($b, $t['image']) ?>" alt="">
        <?php else: ?>
          <div style="width:100%;height:100%;background:<?= htmlspecialchars($t['code_couleur'] ?? '#ccc') ?>"></div>
        <?php endif; ?>
      </div>
      <div class="teinte-info">
        <div class="teinte-name"><?= htmlspecialchars($t['nom_teinte']) ?></div>
        <div class="teinte-meta">
          Stock : <strong><?= $t['stock'] ?? 0 ?></strong>
          <?php if ($t['prix']): ?> · <?= number_format($t['prix'], 2, ',', ' ') ?> DA<?php endif; ?>
          <?php if (!$t['image'] && $t['code_couleur']): ?>
            · <span style="font-size:11px;font-weight:600;color:var(--muted)"><?= htmlspecialchars($t['code_couleur']) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <!-- Badge type -->
      <span class="teinte-type <?= $t['image'] ? 'img' : 'color' ?>">
        <?= $t['image'] ? '🖼 Image' : '🎨 Couleur' ?>
      </span>
      <!-- Actions -->
      <button class="btn btn-sm btn-outline" onclick='editShade(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)'>✏</button>
      <a href="admin_products.php?delete_shade=<?= $t['id'] ?>&pid=<?= $editProduct['id'] ?>"
         class="btn btn-sm btn-red"
         onclick="return confirm('Supprimer cette teinte ?')">🗑</a>
    </div>
    <?php endforeach; else: ?>
      <p style="color:var(--muted);text-align:center;padding:20px 0">Aucune teinte. Cliquez sur « Ajouter une teinte ».</p>
    <?php endif; ?>
  </div>
</div>

<!-- ─ Galerie ────────────────────────────────────────────────────────────────── -->
<div class="card section-anchor" id="gallery">
  <div class="card-head"><span class="card-title">📸 Galerie (<?= count($extraImages) ?>)</span></div>
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data" style="margin-bottom:16px">
      <input type="hidden" name="action" value="upload_images">
      <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
      <div class="form-group" style="margin-bottom:10px">
        <input type="file" name="extra_images[]" multiple accept="image/*">
      </div>
      <button class="btn btn-rose btn-sm" type="submit">⬆ Uploader</button>
    </form>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <?php foreach ($extraImages as $img): ?>
      <div style="position:relative">
        <img src="<?= imgUrl($b, $img['image']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
        <a href="admin_products.php?delete_img=<?= $img['id'] ?>&pid=<?= $editProduct['id'] ?>"
           onclick="return confirm('Supprimer ?')"
           style="position:absolute;top:-5px;right:-5px;width:20px;height:20px;background:var(--red);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;text-decoration:none">×</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     MODAL TEINTE — Couleur OU Image
══════════════════════════════════════════════════════════════════════ -->
<div id="shadeModal" style="display:none;position:fixed;inset:0;z-index:900;background:rgba(22,16,14,.45);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px">
  <div style="background:var(--surface);border-radius:20px;width:100%;max-width:460px;box-shadow:0 24px 80px rgba(0,0,0,.2);overflow:hidden">

    <!-- Entête modal -->
    <div style="padding:20px 24px;background:var(--surface2);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <span style="font-size:13px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)" id="modalTitle">Ajouter une teinte</span>
      <button onclick="closeShadeModal()" style="background:none;border:none;cursor:pointer;color:var(--muted);font-size:18px;line-height:1">✕</button>
    </div>

    <!-- Corps modal -->
    <form method="POST" enctype="multipart/form-data" id="shadeForm" style="padding:22px 24px 24px">
      <input type="hidden" name="action" value="save_shade">
      <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>">
      <input type="hidden" name="shade_id" id="modal_shade_id" value="0">
      <input type="hidden" name="existing_shade_image" id="modal_existing_image" value="">

      <!-- Nom -->
      <div class="form-group" style="margin-bottom:14px">
        <label>Nom de la teinte *</label>
        <input type="text" name="nom_teinte" id="modal_nom" required placeholder="ex: Rouge Passion">
      </div>

      <!-- Stock + Prix -->
      <div class="form-grid" style="margin-bottom:14px">
        <div class="form-group">
          <label>Stock</label>
          <input type="number" name="stock_shade" id="modal_stock" value="0" min="0">
        </div>
        <div class="form-group">
          <label>Prix spécifique (DA)</label>
          <input type="number" name="prix_shade" id="modal_prix" step="0.01" placeholder="Optionnel">
        </div>
      </div>

      <!-- ─ TOGGLE MODE ─────────────────────────────────────────── -->
      <div class="form-group" style="margin-bottom:16px">
        <label>Type de visuel</label>
        <div class="shade-mode-wrap">
          <button type="button" class="shade-mode-btn active" id="btnModeColor" onclick="setShadeMode('color')">
            🎨 Couleur
          </button>
          <button type="button" class="shade-mode-btn" id="btnModeImage" onclick="setShadeMode('image')">
            🖼 Image
          </button>
        </div>
        <input type="hidden" name="shade_mode" id="modal_shade_mode" value="color">
      </div>

      <!-- ─ SECTION COULEUR ──────────────────────────────────────── -->
      <div id="sectionColor" style="margin-bottom:16px">
        <div class="form-group">
          <label>Couleur</label>
          <div class="color-preview-wrap">
            <div class="color-preview-swatch" id="colorPreviewSwatch" style="background:#c4697a"></div>
            <input type="color" name="code_couleur" id="modal_color"
                   class="shade-color-picker"
                   value="#c4697a"
                   oninput="document.getElementById('colorPreviewSwatch').style.background=this.value">
          </div>
        </div>
      </div>

      <!-- ─ SECTION IMAGE ────────────────────────────────────────── -->
      <div id="sectionImage" style="display:none;margin-bottom:16px">
        <div class="form-group">
          <label>Image de la teinte</label>
          <!-- Preview image existante -->
          <div id="existingImgPreview" style="display:none;margin-bottom:10px">
            <div class="img-preview-wrap">
              <img id="existingImgThumb" src="" alt="" class="img-preview-thumb">
              <button type="button" class="img-preview-remove" onclick="removeExistingImage()" title="Remplacer">✕</button>
            </div>
            <p class="hint" style="margin-top:6px">Image actuelle — sélectionnez un fichier pour remplacer</p>
          </div>
          <!-- Input file -->
          <div id="fileInputWrap">
            <input type="file" name="shade_image" id="modal_image_file" accept="image/*"
                   onchange="previewNewImage(this)">
            <!-- Preview du fichier sélectionné -->
            <div id="newImgPreview" style="display:none;margin-top:10px;display:flex;align-items:center;gap:10px">
              <img id="newImgThumb" src="" alt="" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid var(--border)">
              <span id="newImgName" style="font-size:12px;color:var(--muted)"></span>
            </div>
          </div>
          <p class="hint">JPG, PNG, WebP — max 5 Mo</p>
        </div>
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:4px">
        <button type="button" class="btn btn-outline" onclick="closeShadeModal()">Annuler</button>
        <button type="submit" class="btn btn-rose" id="modalSubmitBtn">Ajouter la teinte</button>
      </div>
    </form>
  </div>
</div>

<script>
// ─ Modal teinte ───────────────────────────────────────────────────────────────
const modal = document.getElementById('shadeModal');

function openShadeModal() {
  resetShadeModal();
  document.getElementById('modalTitle').textContent = 'Ajouter une teinte';
  document.getElementById('modalSubmitBtn').textContent = 'Ajouter la teinte';
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeShadeModal() {
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

function resetShadeModal() {
  document.getElementById('modal_shade_id').value    = '0';
  document.getElementById('modal_existing_image').value = '';
  document.getElementById('modal_nom').value         = '';
  document.getElementById('modal_stock').value       = '0';
  document.getElementById('modal_prix').value        = '';
  document.getElementById('modal_color').value       = '#c4697a';
  document.getElementById('colorPreviewSwatch').style.background = '#c4697a';
  document.getElementById('modal_image_file').value  = '';
  document.getElementById('newImgPreview').style.display    = 'none';
  document.getElementById('existingImgPreview').style.display = 'none';
  setShadeMode('color');
}

function editShade(t) {
  document.getElementById('modal_shade_id').value = t.id;
  document.getElementById('modal_nom').value      = t.nom_teinte || '';
  document.getElementById('modal_stock').value    = t.stock || 0;
  document.getElementById('modal_prix').value     = t.prix || '';

  if (t.image) {
    // Mode image
    document.getElementById('modal_existing_image').value = t.image;
    const baseUrl = '<?= htmlspecialchars($b) ?>';
    const src = t.image.startsWith('http') ? t.image : baseUrl + '/images/' + t.image;
    document.getElementById('existingImgThumb').src = src;
    document.getElementById('existingImgPreview').style.display = 'block';
    document.getElementById('modal_image_file').value = '';
    document.getElementById('newImgPreview').style.display = 'none';
    setShadeMode('image');
  } else {
    // Mode couleur
    document.getElementById('modal_existing_image').value = '';
    const col = t.code_couleur || '#c4697a';
    document.getElementById('modal_color').value = col;
    document.getElementById('colorPreviewSwatch').style.background = col;
    setShadeMode('color');
  }

  document.getElementById('modalTitle').textContent     = 'Modifier la teinte';
  document.getElementById('modalSubmitBtn').textContent = 'Enregistrer';
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

// ─ Toggle couleur / image ─────────────────────────────────────────────────────
function setShadeMode(mode) {
  document.getElementById('modal_shade_mode').value = mode;

  const btnColor = document.getElementById('btnModeColor');
  const btnImage = document.getElementById('btnModeImage');
  const secColor = document.getElementById('sectionColor');
  const secImage = document.getElementById('sectionImage');

  if (mode === 'color') {
    btnColor.classList.add('active');
    btnImage.classList.remove('active');
    secColor.style.display = 'block';
    secImage.style.display = 'none';
  } else {
    btnImage.classList.add('active');
    btnColor.classList.remove('active');
    secColor.style.display = 'none';
    secImage.style.display = 'block';
  }
}

// ─ Preview image sélectionnée ─────────────────────────────────────────────────
function previewNewImage(input) {
  const wrap = document.getElementById('newImgPreview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('newImgThumb').src = e.target.result;
      document.getElementById('newImgName').textContent = input.files[0].name;
      wrap.style.display = 'flex';
    };
    reader.readAsDataURL(input.files[0]);
  } else {
    wrap.style.display = 'none';
  }
}

// ─ Supprimer référence image existante ───────────────────────────────────────
function removeExistingImage() {
  document.getElementById('modal_existing_image').value = '';
  document.getElementById('existingImgPreview').style.display = 'none';
}

// Fermer modal en cliquant sur l'overlay
modal.addEventListener('click', e => { if (e.target === modal) closeShadeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeShadeModal(); });
</script>

<?php else: ?>
<!-- ════════════════════════════════════════════════════════════════════════════
     LISTE DES PRODUITS
═════════════════════════════════════════════════════════════════════════════ -->
<div class="page-header">
  <div>
    <h1>Produits</h1>
    <p><?= $totalProducts ?> produit(s) · <?= $totalStock ?> unités en stock</p>
  </div>
  <button class="btn btn-rose" onclick="openCreateModal()">+ Nouveau produit</button>
</div>

<!-- Filtres -->
<div class="card" style="margin-bottom:18px">
  <div class="card-body" style="padding:14px 16px">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
      <div class="form-group" style="flex:1;min-width:160px">
        <label>Recherche</label>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Nom, description…">
      </div>
      <div class="form-group" style="min-width:130px">
        <label>Catégorie</label>
        <select name="cat">
          <option value="">Toutes</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $catFilter === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="min-width:130px">
        <label>Sous-catégorie</label>
        <select name="sous_cat">
          <option value="">Toutes</option>
          <?php foreach ($sous_categories as $sc): ?>
            <option value="<?= htmlspecialchars($sc) ?>" <?= $sousCatFilter === $sc ? 'selected' : '' ?>><?= htmlspecialchars($sc) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-rose" type="submit">Filtrer</button>
      <a href="admin_products.php" class="btn btn-outline">Réinitialiser</a>
    </form>
  </div>
</div>

<!-- Table produits -->
<div class="card">
  <div class="card-body" style="padding:0 20px">
    <div class="tbl-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Image</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Teintes</th>
            <th>Statut</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p):
            // Récupérer les teintes pour le cluster visuel
            $nbShades = $shadeCountMap[$p['id']] ?? 0;
          ?>
          <tr>
            <td>
              <img src="<?= imgUrl($b, $p['image_url']) ?>" class="prod-img" alt="">
            </td>
            <td>
              <div style="font-weight:700;color:var(--text)"><?= htmlspecialchars($p['name']) ?></div>
              <?php if ($p['categorie']): ?>
                <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($p['categorie']) ?></div>
              <?php endif; ?>
              <?php if ($p['is_sale']): ?><span class="badge bdg-rose" style="margin-top:3px;font-size:10px">Solde</span><?php endif; ?>
              <?php if ($p['is_pack']): ?><span class="badge bdg-amber" style="margin-top:3px;font-size:10px">Pack</span><?php endif; ?>
            </td>
            <td style="font-weight:700;color:var(--rose)"><?= number_format($p['price'], 0, ',', ' ') ?> DA</td>
            <td>
              <?php if ((int)$p['stock'] === 0): ?>
                <span class="badge bdg-red">0</span>
              <?php elseif ((int)$p['stock'] <= 5): ?>
                <span class="badge bdg-amber"><?= $p['stock'] ?></span>
              <?php else: ?>
                <span style="font-weight:700"><?= $p['stock'] ?></span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($nbShades > 0): ?>
                <span class="badge bdg-gray">🎨 <?= $nbShades ?></span>
              <?php else: ?>
                <span style="color:var(--muted2)">—</span>
              <?php endif; ?>
            </td>
            <td><?= $p['active'] ? '<span class="badge bdg-green">Actif</span>' : '<span class="badge bdg-gray">Inactif</span>' ?></td>
            <td style="text-align:right">
              <a href="admin_products.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline">✏ Éditer</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$products): ?>
            <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">Aucun produit trouvé.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════
     MODAL CRÉATION PRODUIT
══════════════════════════════════════════════════════════════════════ -->
<div id="createModal" style="display:none;position:fixed;inset:0;z-index:900;background:rgba(22,16,14,.45);backdrop-filter:blur(4px);overflow-y:auto;padding:30px 16px">
  <div style="background:var(--surface);border-radius:20px;width:100%;max-width:620px;margin:0 auto;box-shadow:0 24px 80px rgba(0,0,0,.2);overflow:hidden">
    <div style="padding:18px 24px;background:var(--surface2);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <span style="font-size:13px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--muted)">Nouveau produit</span>
      <button onclick="closeCreateModal()" style="background:none;border:none;cursor:pointer;color:var(--muted);font-size:18px">✕</button>
    </div>
    <form method="POST" enctype="multipart/form-data" style="padding:22px 24px 24px">
      <input type="hidden" name="action" value="create_full">
      <input type="hidden" name="shades_data" id="shades_data_input" value="[]">

      <div class="form-grid" style="margin-bottom:14px">
        <div class="form-group form-full">
          <label>Nom du produit *</label>
          <input type="text" name="name" required placeholder="ex: Rouge à Lèvres Mat">
        </div>
        <div class="form-group">
          <label>Prix (DA) *</label>
          <input type="number" name="price" step="0.01" required placeholder="0">
        </div>
        <div class="form-group">
          <label>Ancien prix (DA)</label>
          <input type="number" name="old_price" step="0.01" placeholder="0">
        </div>
        <div class="form-group">
          <label>Stock global</label>
          <input type="number" name="stock" value="0" min="0">
        </div>
        <div class="form-group">
          <label>Catégorie</label>
          <input type="text" name="categorie" list="cats2" placeholder="Lèvres, Yeux…">
          <datalist id="cats2"><?php foreach ($categories as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?></datalist>
        </div>
        <div class="form-group">
          <label>Sous-catégorie</label>
          <input type="text" name="sous_categorie" list="scats2">
          <datalist id="scats2"><?php foreach ($sous_categories as $sc): ?><option value="<?= htmlspecialchars($sc) ?>"><?php endforeach; ?></datalist>
        </div>
        <div class="form-group">
          <label>Marque</label>
          <input type="text" name="marque" list="marqs2">
          <datalist id="marqs2"><?php foreach ($marques as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?></datalist>
        </div>
        <div class="form-group form-full">
          <label>Description</label>
          <textarea name="description" placeholder="Description du produit…"></textarea>
        </div>
        <div class="form-group form-full">
          <label>Image principale</label>
          <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group form-full">
          <label>Images supplémentaires <span style="font-weight:400;color:var(--muted2)">(galerie)</span></label>
          <input type="file" name="extra_images[]" accept="image/*" multiple
                 onchange="previewGallery(this)">
          <p class="hint">Sélectionnez plusieurs fichiers à la fois — JPG, PNG, WebP</p>
          <!-- Preview grille galerie -->
          <div id="galleryPreview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px"></div>
        </div>
        <div class="form-group form-full" style="flex-direction:row;flex-wrap:wrap;gap:14px;align-items:center">
          <label style="display:flex;gap:6px;align-items:center;cursor:pointer">
            <input type="checkbox" name="active" checked> Actif
          </label>
          <label style="display:flex;gap:6px;align-items:center;cursor:pointer">
            <input type="checkbox" name="is_sale"> En solde
          </label>
          <label style="display:flex;gap:6px;align-items:center;cursor:pointer">
            <input type="checkbox" name="is_pack"> Pack
          </label>
        </div>
      </div>

      <!-- ─ Teintes inline (création) ─────────────────────────────── -->
      <div style="border-top:1px solid var(--border);margin-top:4px;padding-top:16px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
          <span style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)">Teintes</span>
          <button type="button" class="btn btn-sm btn-outline" onclick="addCreateShadeRow()">+ Ajouter</button>
        </div>
        <div id="createShadesContainer"></div>
        <p class="hint" style="margin-top:6px">Les teintes peuvent être ajoutées/modifiées après création.</p>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:18px">
        <button type="button" class="btn btn-outline" onclick="closeCreateModal()">Annuler</button>
        <button type="submit" class="btn btn-rose">Créer le produit →</button>
      </div>
    </form>
  </div>
</div>

<script>
// ─ Modal création ─────────────────────────────────────────────────────────────
function openCreateModal()  { document.getElementById('createModal').style.display = 'block'; document.body.style.overflow = 'hidden'; }
function closeCreateModal() { document.getElementById('createModal').style.display = 'none';  document.body.style.overflow = ''; }
document.getElementById('createModal').addEventListener('click', e => {
  if (e.target === document.getElementById('createModal')) closeCreateModal();
});

// ─ Lignes teinte inline (création) ───────────────────────────────────────────
let createShades = [];
let createShadeIndex = 0;

function addCreateShadeRow() {
  const idx = createShadeIndex++;
  const wrap = document.getElementById('createShadesContainer');
  const div = document.createElement('div');
  div.id = `shade-row-${idx}`;
  div.style.cssText = 'display:grid;grid-template-columns:1fr 80px 80px auto;gap:8px;align-items:end;margin-bottom:10px;padding:12px;background:var(--surface2);border:1px solid var(--border);border-radius:10px';
  div.innerHTML = `
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text2);margin-bottom:5px">Nom</div>
      <input type="text" id="sn_${idx}" placeholder="ex: Rouge Passion" style="width:100%">
    </div>
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text2);margin-bottom:5px">Stock</div>
      <input type="number" id="ss_${idx}" value="0" min="0">
    </div>
    <div>
      <div style="font-size:11px;font-weight:700;color:var(--text2);margin-bottom:5px">Prix DA</div>
      <input type="number" id="sp_${idx}" step="0.01" placeholder="—">
    </div>
    <button type="button" onclick="removeCreateShade(${idx})" style="background:var(--red-bg);border:1px solid var(--red-lt);color:var(--red);border-radius:7px;width:32px;height:32px;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center">✕</button>
    <!-- Mode toggle couleur/image -->
    <div style="grid-column:1/-1;display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-top:4px">
      <div style="display:flex;gap:0;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;background:var(--bg)">
        <button type="button" class="shade-mode-btn active" id="bmc_${idx}" onclick="setCreateMode(${idx},'color')" style="padding:6px 12px;font-size:11.5px;font-weight:700;font-family:inherit;background:var(--rose);color:#fff;border:none;cursor:pointer">🎨 Couleur</button>
        <button type="button" class="shade-mode-btn" id="bmi_${idx}" onclick="setCreateMode(${idx},'image')" style="padding:6px 12px;font-size:11.5px;font-weight:700;font-family:inherit;background:none;color:var(--muted);border:none;border-left:1px solid var(--border);cursor:pointer">🖼 Image</button>
      </div>
      <!-- Couleur -->
      <div id="sc_wrap_${idx}" style="display:flex;align-items:center;gap:8px">
        <div id="sc_dot_${idx}" style="width:28px;height:28px;border-radius:50%;border:2px solid var(--border);background:#c4697a;flex-shrink:0"></div>
        <input type="color" id="sc_${idx}" value="#c4697a" style="width:70px;height:32px;border:1.5px solid var(--border);border-radius:8px;padding:2px 4px;cursor:pointer"
               oninput="document.getElementById('sc_dot_${idx}').style.background=this.value">
      </div>
      <!-- Image -->
      <div id="si_wrap_${idx}" style="display:none">
        <input type="file" name="shade_images[]" id="si_${idx}" accept="image/*" style="font-size:12px"
               onchange="previewCreateShadeImg(${idx},this)">
        <img id="si_prev_${idx}" src="" style="display:none;width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--border);margin-top:6px;vertical-align:middle">
      </div>
    </div>
    <input type="hidden" id="sm_${idx}" value="color">
  `;
  wrap.appendChild(div);
  syncShadesData();
}

function removeCreateShade(idx) {
  const el = document.getElementById(`shade-row-${idx}`);
  if (el) el.remove();
  syncShadesData();
}

function setCreateMode(idx, mode) {
  document.getElementById(`sm_${idx}`).value = mode;
  const bc = document.getElementById(`bmc_${idx}`);
  const bi = document.getElementById(`bmi_${idx}`);
  const sc = document.getElementById(`sc_wrap_${idx}`);
  const si = document.getElementById(`si_wrap_${idx}`);
  if (mode === 'color') {
    bc.style.background = 'var(--rose)'; bc.style.color = '#fff';
    bi.style.background = 'none'; bi.style.color = 'var(--muted)';
    sc.style.display = 'flex'; si.style.display = 'none';
  } else {
    bi.style.background = 'var(--rose)'; bi.style.color = '#fff';
    bc.style.background = 'none'; bc.style.color = 'var(--muted)';
    si.style.display = 'block'; sc.style.display = 'none';
  }
  syncShadesData();
}

function previewCreateShadeImg(idx, input) {
  const prev = document.getElementById(`si_prev_${idx}`);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { prev.src = e.target.result; prev.style.display = 'inline-block'; };
    reader.readAsDataURL(input.files[0]);
  }
  syncShadesData();
}

function syncShadesData() {
  const container = document.getElementById('createShadesContainer');
  const shades = [];
  container.querySelectorAll('[id^="shade-row-"]').forEach(row => {
    const idx = row.id.replace('shade-row-', '');
    shades.push({
      nom:   document.getElementById(`sn_${idx}`)?.value || '',
      stock: document.getElementById(`ss_${idx}`)?.value || '0',
      prix:  document.getElementById(`sp_${idx}`)?.value || '',
      code:  document.getElementById(`sc_${idx}`)?.value || '#000000',
      mode:  document.getElementById(`sm_${idx}`)?.value || 'color',
    });
  });
  document.getElementById('shades_data_input').value = JSON.stringify(shades);
}

// Synchro à chaque interaction
document.getElementById('createShadesContainer').addEventListener('input', syncShadesData);

// ─ Preview galerie ────────────────────────────────────────────────────────────
function previewGallery(input) {
  const wrap = document.getElementById('galleryPreview');
  wrap.innerHTML = '';
  if (!input.files || !input.files.length) return;
  Array.from(input.files).forEach(file => {
    if (!file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.style.cssText = 'position:relative;display:inline-block';
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.cssText = 'width:56px;height:56px;border-radius:8px;object-fit:cover;border:1.5px solid var(--border)';
      img.title = file.name;
      div.appendChild(img);
      wrap.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}
</script>

<?php endif; ?>
</div>
</body>
</html>