<?php
// ============================================
//  SheGlamour — Landing Page Sky High Mascara
//  100% PHP — aucun HTML hors balises <?php
// ============================================
require_once __DIR__ . '/includes/db.php';

// ── Prix ──────────────────────────────────────────────────────────────────────
$PRICE_OLD   = 2800.00;
$PRICE_PROMO = 2350.00;

function priceTag(float $amount): string
{
    // unicode-bidi:isolate évite que l'espace du séparateur de milliers
    // ne soit réordonné par le moteur bidi dans un contexte RTL (ex: "1 990" -> "9901")
    $formatted = number_format($amount, 0, '', ' ');
    return '<span style="direction:ltr;unicode-bidi:isolate;display:inline-block">' . $formatted . ' DA</span>';
}

function discountPercent(float $old, float $promo): int
{
    if ($old <= 0) return 0;
    return (int) round((($old - $promo) / $old) * 100);
}

// ── Wilayas (code => nom) ───────────────────────────────────────────────────
$WILAYAS = [
    '44' => 'Aïn Defla',            '46' => 'Aïn Témouchent',
    '16' => 'Alger',                '23' => 'Annaba',
    '1'  => 'Adrar',                '5'  => 'Batna',
    '6'  => 'Béjaïa',               '52' => 'Béni Abbès',
    '7'  => 'Biskra',               '8'  => 'Béchar',
    '9'  => 'Blida',                '34' => 'Bordj Bou Arréridj',
    '50' => 'Bordj Badji Mokhtar',  '10' => 'Bouira',
    '35' => 'Boumerdès',            '2'  => 'Chlef',
    '25' => 'Constantine',          '17' => 'Djelfa',
    '24' => 'Guelma',               '47' => 'Ghardaïa',
    '33' => 'Illizi',               '18' => 'Jijel',
    '40' => 'Khenchela',            '3'  => 'Laghouat',
    '43' => 'Mila',                 '29' => 'Mascara',
    '26' => 'Médéa',                '28' => "M'Sila",
    '27' => 'Mostaganem',           '45' => 'Naâma',
    '30' => 'Ouargla',              '51' => 'Ouled Djellal',
    '31' => 'Oran',                 '4'  => 'Oum El Bouaghi',
    '48' => 'Relizane',             '20' => 'Saïda',
    '21' => 'Skikda',               '41' => 'Souk Ahras',
    '11' => 'Tamanrasset',          '37' => 'Tindouf',
    '14' => 'Tiaret',               '15' => 'Tizi Ouzou',
    '42' => 'Tipaza',               '49' => 'Timimoun',
    '12' => 'Tébessa',              '38' => 'Tissemsilt',
    '55' => 'Touggourt',            '19' => 'Sétif',
    '13' => 'Tlemcen',              '39' => 'El Oued',
    '36' => 'El Tarf',              '32' => 'El Bayadh',
    '57' => "El M'Ghair",           '58' => 'El Meniaa',
    '53' => 'In Salah',             '22' => 'Sidi Bel Abbès',
];

// ── Tarifs de livraison (Noest) — code wilaya => [domicile, stopDesk] ────────
$TARIFS_LIVRAISON = [
    '16' => ['domicile' => 500,  'stopDesk' => 250],
    '35' => ['domicile' => 500,  'stopDesk' => 300],
    '9'  => ['domicile' => 550,  'stopDesk' => 250],
    '42' => ['domicile' => 550,  'stopDesk' => 250],
    '15' => ['domicile' => 600,  'stopDesk' => 300],
    '10' => ['domicile' => 650,  'stopDesk' => 300],
    '26' => ['domicile' => 650,  'stopDesk' => 250],
    '2'  => ['domicile' => 700,  'stopDesk' => 350],
    '6'  => ['domicile' => 700,  'stopDesk' => 350],
    '14' => ['domicile' => 700,  'stopDesk' => 350],
    '19' => ['domicile' => 700,  'stopDesk' => 350],
    '25' => ['domicile' => 700,  'stopDesk' => 350],
    '31' => ['domicile' => 700,  'stopDesk' => 350],
    '4'  => ['domicile' => 750,  'stopDesk' => 350],
    '5'  => ['domicile' => 750,  'stopDesk' => 350],
    '13' => ['domicile' => 750,  'stopDesk' => 350],
    '18' => ['domicile' => 750,  'stopDesk' => 350],
    '21' => ['domicile' => 750,  'stopDesk' => 350],
    '22' => ['domicile' => 750,  'stopDesk' => 350],
    '23' => ['domicile' => 750,  'stopDesk' => 350],
    '27' => ['domicile' => 750,  'stopDesk' => 350],
    '28' => ['domicile' => 750,  'stopDesk' => 350],
    '29' => ['domicile' => 750,  'stopDesk' => 350],
    '34' => ['domicile' => 750,  'stopDesk' => 350],
    '38' => ['domicile' => 750,  'stopDesk' => 350],
    '41' => ['domicile' => 750,  'stopDesk' => 350],
    '43' => ['domicile' => 750,  'stopDesk' => 350],
    '44' => ['domicile' => 750,  'stopDesk' => 350],
    '46' => ['domicile' => 750,  'stopDesk' => 350],
    '48' => ['domicile' => 750,  'stopDesk' => 350],
    '12' => ['domicile' => 800,  'stopDesk' => 350],
    '20' => ['domicile' => 800,  'stopDesk' => 350],
    '24' => ['domicile' => 800,  'stopDesk' => 350],
    '36' => ['domicile' => 800,  'stopDesk' => 350],
    '40' => ['domicile' => 800,  'stopDesk' => 350],
    '7'  => ['domicile' => 900,  'stopDesk' => 350],
    '51' => ['domicile' => 900,  'stopDesk' => 350],
    '3'  => ['domicile' => 1000, 'stopDesk' => 500],
    '17' => ['domicile' => 1000, 'stopDesk' => 500],
    '30' => ['domicile' => 1000, 'stopDesk' => 500],
    '39' => ['domicile' => 1000, 'stopDesk' => 500],
    '47' => ['domicile' => 1000, 'stopDesk' => 500],
    '55' => ['domicile' => 1000, 'stopDesk' => 500],
    '57' => ['domicile' => 1000, 'stopDesk' => 500],
    '58' => ['domicile' => 1000, 'stopDesk' => 500],
    '8'  => ['domicile' => 1100, 'stopDesk' => 600],
    '32' => ['domicile' => 1100, 'stopDesk' => 600],
    '45' => ['domicile' => 1100, 'stopDesk' => 600],
    '52' => ['domicile' => 1100, 'stopDesk' => 600],
    '1'  => ['domicile' => 1400, 'stopDesk' => 700],
    '37' => ['domicile' => 1400, 'stopDesk' => 600],
    '49' => ['domicile' => 1400, 'stopDesk' => 700],
    '11' => ['domicile' => 1850, 'stopDesk' => 1000],
    '53' => ['domicile' => 1850, 'stopDesk' => 1000],
    '33' => ['domicile' => 2000, 'stopDesk' => 1000],
    // '50' (Bordj Badji Mokhtar) n'a pas de tarif fixe — confirmé par téléphone
];

function shippingCost(array $tarifs, string $wilayaCode, string $deliveryType): ?int
{
    return $tarifs[$wilayaCode][$deliveryType] ?? null;
}

$success     = false;
$order_id    = '';
$error_msg   = '';
$fieldErrors = [];

// ── Confirmation après redirect PRG ─────────────────────────────────────────
$confirmedOrder = null;
if (isset($_GET['merci']) && $_GET['merci'] !== '') {
    $success  = true;
    $order_id = $_GET['merci'];

    try {
        $stmt = $pdo->prepare("SELECT total, shipping FROM orders WHERE order_id = ? LIMIT 1");
        $stmt->execute([$order_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $confirmedOrder = [
                'total'    => (float) $row['total'],
                'shipping' => json_decode($row['shipping'], true) ?: [],
            ];
        }
    } catch (PDOException $e) {
        error_log('[SheGlamour LP] confirm fetch: ' . $e->getMessage());
    }
}

// ── Traitement POST ──────────────────────────────────────────────────────────
if (!$success && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName    = trim($_POST['firstName'] ?? '');
    $lastName     = trim($_POST['lastName']  ?? '');
    $phone        = trim($_POST['phone']     ?? '');
    $wilayaCode   = trim($_POST['wilaya']    ?? '');
    $deliveryType = trim($_POST['deliveryType'] ?? 'domicile');
    $address      = trim($_POST['address']   ?? '');

    $errors = [];
    if (mb_strlen($firstName) < 2)               $errors[] = 'firstName';
    if (mb_strlen($lastName)  < 2)               $errors[] = 'lastName';
    if (!preg_match('/^\d[\d\s]{8,}$/', $phone)) $errors[] = 'phone';
    if (!isset($WILAYAS[$wilayaCode]))            $errors[] = 'wilaya';
    if (!in_array($deliveryType, ['domicile', 'stopDesk'], true)) $deliveryType = 'domicile';
    if (mb_strlen($address)   < 5)               $errors[] = 'address';

    $shipping_cost = isset($WILAYAS[$wilayaCode])
        ? shippingCost($TARIFS_LIVRAISON, $wilayaCode, $deliveryType)
        : null;

    if (empty($errors)) {
        $wilayaName = $WILAYAS[$wilayaCode];
        $shippingAmount = $shipping_cost ?? 0;
        $orderTotal     = $PRICE_PROMO + $shippingAmount;

        $ts       = strtoupper(base_convert(time(), 10, 36));
        $rand     = strtoupper(substr(base_convert(rand(0, 1679616), 10, 36), 0, 4));
        $order_id = "SHG-{$ts}-{$rand}";

        $shipping = json_encode([
            'firstName'      => $firstName,
            'lastName'       => $lastName,
            'phone'          => $phone,
            'wilayaCode'     => $wilayaCode,
            'wilaya'         => $wilayaName,
            'deliveryType'   => $deliveryType,
            'shippingCost'   => $shipping_cost, // null = à confirmer par téléphone
            'address'        => $address,
        ], JSON_UNESCAPED_UNICODE);

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO orders (order_id, status, payment_method, total, shipping, created_at, updated_at)
                VALUES (?, 'pending', 'cash', ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$order_id, $orderTotal, $shipping]);
            $dbId = (int) $pdo->lastInsertId();

            $item = $pdo->prepare("
                INSERT INTO order_items (order_db_id, name, shade, quantity, unit_price)
                VALUES (?, 'Sky High Waterproof Mascara', 'Very Black', 1, ?)
            ");
            $item->execute([$dbId, $PRICE_PROMO]);

            $pdo->commit();

            $base = strtok($_SERVER['REQUEST_URI'], '?');
            header('Location: ' . $base . '?merci=' . urlencode($order_id) . '#order');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('[SheGlamour LP] ' . $e->getMessage());
            $error_msg = 'حدث خطأ تقني، حاولي مجدداً.';
        }
    } else {
        $fieldErrors = array_flip($errors);
    }
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function field(string $name, string $label, string $type, string $placeholder, array $fieldErrors, array $extra = []): void
{
    $hasError = isset($fieldErrors[$name]);
    $val      = e($_POST[$name] ?? '');
    $cls      = $hasError ? ' has-error' : '';
    $dir      = $extra['dir'] ?? '';
    $style    = $extra['style'] ?? '';
    $dirAttr  = $dir   ? " dir=\"{$dir}\""     : '';
    $styleAttr= $style ? " style=\"{$style}\"" : '';
    echo "<div class=\"field{$cls}\">\n";
    echo "  <label>" . e($label) . "</label>\n";
    echo "  <input name=\"{$name}\" type=\"{$type}\" placeholder=\"" . e($placeholder) . "\"{$dirAttr}{$styleAttr} value=\"{$val}\">\n";
    if ($hasError) echo "  <div class=\"field-error\">" . e($extra['err'] ?? '') . "</div>\n";
    echo "</div>\n";
}

// ── CSS ───────────────────────────────────────────────────────────────────────
$css = <<<'CSS'
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#0a0a0a;--cream:#f2f2f2;--border:#d8d8d8;
  --text-main:#1a1a1a;--text-mid:#333;--text-soft:#555;
  --text-ghost:#888;--text-light:#bbb;--white:#fff;
  --serif:'Cormorant Garamond',Georgia,serif;
  --arabic:'Noto Naskh Arabic',serif;
  --sans:'Inter',sans-serif;
}
body{font-family:var(--arabic);color:var(--text-main);background:var(--white);direction:rtl}
.top-banner{background:#000;color:#f5f0ea;padding:10px 16px;display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap;font-family:var(--sans);font-size:12px;letter-spacing:.06em;text-align:center;border-bottom:1px solid #222}
.top-banner .flag{font-size:15px;line-height:1}
.top-banner strong{color:#fff;font-weight:700}
.top-banner .sep{color:#555}
.hero{background:var(--ink);display:grid;grid-template-columns:1fr 1fr;min-height:560px;overflow:hidden}
.hero-copy{display:flex;flex-direction:column;justify-content:center;padding:52px 48px;order:1}
.hero-img{position:relative;overflow:hidden;order:2;background:#111;display:flex;align-items:center;justify-content:center}
.hero-img>img{width:100%;height:100%;object-fit:cover;object-position:center top;display:block;filter:brightness(.92)}
.packshot-wrap{position:absolute;bottom:0;left:50%;transform:translateX(-50%);width:220px;z-index:2}
.packshot-wrap img{width:100%;height:auto;display:block;filter:drop-shadow(0 8px 32px rgba(0,0,0,.7))}
.eyebrow{font-family:var(--sans);font-size:10px;letter-spacing:.22em;text-transform:uppercase;color:#aaa;margin-bottom:16px}
.hero-title{font-family:var(--serif);font-size:46px;font-weight:400;color:#f5f0ea;line-height:1.1;margin-bottom:6px;direction:ltr;unicode-bidi:isolate;text-align:right}
.hero-title em{font-style:italic;color:#ccc}
.hero-sub{font-family:var(--sans);font-size:11px;color:var(--text-ghost);letter-spacing:.12em;text-transform:uppercase;margin-bottom:24px}
.hero-tagline{font-size:16px;color:var(--text-light);line-height:1.75;margin-bottom:32px;max-width:340px}
.hero-tagline strong{color:#f5f0ea;font-weight:600}
.price-badge{display:flex;align-items:flex-start;gap:16px;margin-bottom:28px;flex-direction:row-reverse;justify-content:flex-end;flex-wrap:wrap}
.price-stack{display:flex;flex-direction:column;gap:4px}
.price-row{display:flex;align-items:center;gap:10px;flex-direction:row-reverse}
.price-main{font-family:var(--serif);font-size:38px;color:#f5f0ea;font-weight:600;direction:ltr;unicode-bidi:isolate;display:inline-block;line-height:1}
.price-old{font-family:var(--sans);font-size:15px;color:#888;text-decoration:line-through;text-decoration-color:#c0392b;direction:ltr;unicode-bidi:isolate;display:inline-block}
.price-note{font-family:var(--sans);font-size:11px;color:var(--text-ghost);letter-spacing:.1em;margin-top:6px}
.discount-badge{background:#c0392b;color:#fff;font-family:var(--sans);font-size:12px;font-weight:800;letter-spacing:.02em;padding:5px 9px;line-height:1;flex-shrink:0}
.discount-badge.sm{font-size:10px;padding:3px 7px}
.promo-badge{background:#c0392b;color:#fff;font-family:var(--sans);font-size:10px;font-weight:700;letter-spacing:.1em;padding:4px 10px;text-transform:uppercase}
.btn-hero{display:inline-block;background:#fff;color:#000;font-family:var(--sans);font-size:11px;letter-spacing:.18em;text-transform:uppercase;padding:14px 36px;border:none;cursor:pointer;font-weight:700;transition:all .2s;width:fit-content;box-shadow:0 6px 20px rgba(0,0,0,.3)}
.btn-hero:hover{background:#eee;transform:translateY(-1px)}
.spotlight{background:var(--ink);display:grid;grid-template-columns:1fr 1fr}
.spotlight-img{position:relative;min-height:480px;overflow:hidden}
.spotlight-img img{width:100%;height:100%;object-fit:cover;display:block}
.spotlight-packshots{display:flex;flex-direction:column;justify-content:center;align-items:center;padding:48px 40px;gap:24px;background:#111}
.packshot-main{width:200px;height:auto;display:block;filter:drop-shadow(0 12px 40px rgba(0,0,0,.8));transition:transform .3s ease}
.packshot-main:hover{transform:scale(1.04)}
.packshot-label{font-family:var(--sans);font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#aaa;text-align:center;margin-top:8px}
.benefits{padding:72px 48px;background:var(--cream);border-top:1px solid var(--border)}
.section-eyebrow{font-family:var(--sans);font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:#aaa;margin-bottom:10px}
.section-title{font-family:var(--serif);font-size:32px;font-weight:400;color:var(--text-main);margin-bottom:44px;line-height:1.2}
.benefit-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.benefit-card{background:var(--white);border:1px solid var(--border);padding:32px 28px;position:relative}
.benefit-card::after{content:'';position:absolute;bottom:0;right:0;width:0;height:2px;background:#1a1a1a;transition:width .3s ease}
.benefit-card:hover::after{width:100%}
.benefit-icon{font-size:24px;margin-bottom:16px;display:block}
.benefit-title{font-size:18px;font-weight:700;color:var(--text-main);margin-bottom:10px}
.benefit-desc{font-size:14px;color:var(--text-soft);line-height:1.7}
.howto{padding:72px 48px;background:var(--white);display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center}
.howto-img img{width:100%;display:block;border:1px solid var(--border)}
.steps{list-style:none;margin-top:28px;display:flex;flex-direction:column;gap:16px}
.steps li{display:flex;gap:16px;align-items:flex-start;font-size:14px;color:var(--text-mid);line-height:1.65}
.step-num{flex-shrink:0;width:28px;height:28px;background:var(--ink);color:#f5f0ea;font-family:var(--serif);font-size:14px;display:flex;align-items:center;justify-content:center;margin-top:1px}
.testimonial{background:var(--ink);padding:56px 48px;text-align:center;position:relative;overflow:hidden}
.testimonial::before{content:'\201C';position:absolute;top:-20px;left:50%;transform:translateX(-50%);font-family:var(--serif);font-size:200px;color:rgba(255,255,255,.04);line-height:1;pointer-events:none}
.stars{color:#fff;font-size:16px;letter-spacing:4px;margin-bottom:20px}
.testimonial-text{font-family:var(--arabic);font-size:20px;font-style:italic;color:var(--text-light);max-width:560px;margin:0 auto 10px;line-height:1.7}
.testimonial-author{font-family:var(--sans);font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--text-ghost)}
.order-section{padding:72px 48px;background:var(--cream);border-top:1px solid var(--border)}
.order-inner{max-width:580px;margin:0 auto}
.form-wrap{display:flex;flex-direction:column;gap:16px;margin-top:28px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.field label{display:block;font-family:var(--sans);font-size:10px;text-transform:uppercase;letter-spacing:.15em;color:var(--text-soft);margin-bottom:7px;font-weight:500}
.field input,.field select{width:100%;border:1px solid #d6cfc8;border-radius:0;padding:12px 14px;font-size:14px;font-family:var(--arabic);background:var(--white);color:var(--text-main);outline:none;transition:border-color .2s;direction:rtl}
.field input:focus,.field select:focus{border-color:#1a1a1a}
.field input::placeholder{color:#aaa;font-family:var(--arabic)}
.field.has-error input,.field.has-error select{border-color:#c0392b}
.field-error{font-size:11px;color:#c0392b;margin-top:4px;font-family:var(--arabic)}
.alert-error{background:#fdf0ee;border:1px solid #f0c0bb;padding:14px 18px;font-size:13px;color:#c0392b;margin-bottom:16px;font-family:var(--arabic)}
.order-total{background:var(--white);border:1px solid var(--border);padding:20px 22px;margin:20px 0;display:flex;flex-direction:column;gap:14px}
.total-lines{display:flex;flex-direction:column;gap:8px}
.total-line{display:flex;justify-content:space-between;align-items:center;font-family:var(--sans);font-size:13px;color:var(--text-soft)}
.total-line-price{display:flex;align-items:center;gap:8px;direction:ltr}
.total-final{display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid var(--border)}
.total-label{font-family:var(--sans);font-size:11px;text-transform:uppercase;letter-spacing:.14em;color:var(--text-soft)}
.total-amounts{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.total-amount{font-family:var(--serif);font-size:26px;color:var(--text-main);font-weight:600;direction:ltr;unicode-bidi:isolate;display:inline-block;line-height:1}
.total-old{font-family:var(--sans);font-size:14px;color:#999;text-decoration:line-through;text-decoration-color:#c0392b;direction:ltr;unicode-bidi:isolate;display:inline-block}
.delivery-options{display:flex;flex-direction:column;gap:8px}
.delivery-option{display:flex;align-items:center;justify-content:space-between;gap:10px;border:1px solid #d6cfc8;padding:12px 14px;cursor:pointer;transition:border-color .15s,background .15s;background:var(--white)}
.delivery-option.active{border-color:#1a1a1a;background:var(--cream)}
.delivery-option input{accent-color:#1a1a1a;flex-shrink:0}
.delivery-option-name{flex:1;font-size:13px;color:var(--text-main);font-family:var(--arabic)}
.delivery-option-price{font-family:var(--sans);font-size:12px;color:var(--text-soft);direction:ltr;unicode-bidi:isolate;display:inline-block;white-space:nowrap}
.btn-order{width:100%;padding:20px;background:#000;color:#fff;border:2px solid #000;font-family:var(--sans);font-size:13px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;cursor:pointer;transition:all .2s;box-shadow:0 10px 30px rgba(0,0,0,.28)}
.btn-order:hover{background:#fff;color:#000;box-shadow:0 10px 30px rgba(0,0,0,.4)}
.confirm-box{text-align:center;padding:48px 24px;background:var(--white);border:1px solid var(--border);margin-top:20px}
.confirm-icon{width:52px;height:52px;border-radius:50%;background:var(--ink);color:#f5f0ea;display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 20px}
.confirm-box h3{font-family:var(--serif);font-size:28px;color:var(--text-main);margin-bottom:12px;font-weight:600}
.confirm-box p{font-size:14px;color:var(--text-soft);line-height:1.75;max-width:400px;margin:0 auto}
.confirm-subnote{font-family:var(--sans);font-size:11px;color:#aaa;margin-top:8px;letter-spacing:.02em}
.confirm-summary{background:var(--cream);border:1px solid var(--border);padding:18px 20px;margin-top:24px;display:flex;flex-direction:column;gap:10px;text-align:right}
.confirm-summary-line{display:flex;justify-content:space-between;align-items:center;gap:12px;font-family:var(--sans);font-size:13px;color:var(--text-mid)}
.confirm-summary-line span:last-child{direction:ltr;unicode-bidi:isolate;display:inline-block;white-space:nowrap}
.confirm-tbd{font-family:var(--arabic);color:#c0392b;font-size:12px;direction:rtl!important;unicode-bidi:normal!important}
.confirm-summary-total{display:flex;justify-content:space-between;align-items:center;padding-top:10px;border-top:1px solid var(--border);font-family:var(--sans);font-size:12px;text-transform:uppercase;letter-spacing:.1em;color:var(--text-soft)}
.confirm-summary-total-price{font-family:var(--serif);font-size:20px;color:var(--text-main);font-weight:600;text-transform:none;letter-spacing:0;direction:ltr;unicode-bidi:isolate;display:inline-block}
.confirm-steps{display:flex;justify-content:center;gap:28px;margin-top:28px;flex-wrap:wrap}
.confirm-step{display:flex;flex-direction:column;align-items:center;gap:6px;font-family:var(--sans);font-size:11px;color:var(--text-ghost);max-width:110px;text-align:center}
.confirm-step-icon{width:34px;height:34px;border:1px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;background:var(--white)}
.order-id-badge{display:inline-block;background:var(--cream);border:1px solid var(--border);padding:9px 24px;font-family:var(--sans);font-size:12px;letter-spacing:.14em;color:var(--text-mid);margin:16px 0;font-weight:500}
.trust-bar{display:flex;gap:24px;margin-top:24px;justify-content:center;flex-wrap:wrap}
.trust-item{font-size:12px;color:var(--text-ghost);display:flex;align-items:center;gap:6px}
.sticky-cta{position:fixed;bottom:0;left:0;right:0;background:#000;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:14px;z-index:999;transform:translateY(100%);transition:transform .3s ease;box-shadow:0 -6px 24px rgba(0,0,0,.35);flex-wrap:nowrap}
.sticky-cta.visible{transform:translateY(0)}
.sticky-cta-info{display:flex;flex-direction:column;gap:2px;min-width:0}
.sticky-cta-price-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.sticky-cta-price{font-family:var(--serif);color:#f5f0ea;font-size:19px;font-weight:600;white-space:nowrap;direction:ltr;unicode-bidi:isolate;display:inline-block;line-height:1}
.sticky-cta-old{font-size:12px;color:#888;text-decoration:line-through;text-decoration-color:#c0392b;font-weight:400;direction:ltr;unicode-bidi:isolate;display:inline-block}
.sticky-cta-note{font-family:var(--sans);font-size:10px;color:#999;font-weight:400;letter-spacing:.06em}
.sticky-cta-btn{background:#fff;color:#000;border:none;padding:14px 26px;font-family:var(--sans);font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;white-space:nowrap;flex-shrink:0;transition:transform .15s}
.sticky-cta-btn:hover{transform:scale(1.03)}
@media(max-width:1024px){
  .hero{min-height:520px}
  .hero-copy{padding:44px 36px}
  .hero-title{font-size:40px}
  .price-main{font-size:34px}
  .benefit-grid{grid-template-columns:repeat(3,1fr);gap:14px}
  .benefit-card{padding:26px 20px}
  .howto{gap:44px;padding:60px 36px}
  .benefits,.order-section{padding:60px 36px}
}
@media(max-width:768px){
  .top-banner{font-size:11px;padding:8px 12px}
  .hero{grid-template-columns:1fr;min-height:auto}
  .hero-copy{order:2;padding:40px 24px}
  .hero-img{order:1;min-height:300px}
  .hero-title{font-size:34px}
  .packshot-wrap{width:160px}
  .price-main{font-size:30px}
  .benefit-grid{grid-template-columns:1fr}
  .howto{grid-template-columns:1fr;gap:36px;padding:48px 24px}
  .howto-img{order:2}
  .spotlight{grid-template-columns:1fr}
  .spotlight-img{min-height:260px}
  .benefits,.order-section{padding:48px 24px}
  .form-row{grid-template-columns:1fr}
  .testimonial{padding:48px 24px}
  .total-final{flex-wrap:wrap;gap:8px}
  .sticky-cta{padding:12px 16px;gap:10px}
  .sticky-cta-price{font-size:16px}
  .sticky-cta-btn{padding:12px 18px;font-size:11px}
}
@media(max-width:480px){
  .top-banner{font-size:10px;gap:6px}
  .hero-copy{padding:32px 18px}
  .hero-title{font-size:28px}
  .hero-tagline{font-size:14px}
  .price-badge{gap:10px}
  .price-main{font-size:26px}
  .price-old{font-size:13px}
  .section-title{font-size:26px}
  .benefit-card{padding:24px 18px}
  .order-inner{max-width:100%}
  .sticky-cta{padding:10px 12px}
  .sticky-cta-note{display:none}
  .sticky-cta-price{font-size:14px}
  .sticky-cta-old{font-size:10px}
  .sticky-cta-btn{padding:11px 16px;font-size:10px}
  .discount-badge{font-size:10px;padding:4px 6px}
}
CSS;

// ── OUTPUT ────────────────────────────────────────────────────────────────────
echo '<!DOCTYPE html>' . PHP_EOL;
echo '<html lang="ar" dir="rtl">' . PHP_EOL;
echo '<head>' . PHP_EOL;
echo '<meta charset="UTF-8">' . PHP_EOL;
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;
echo '<title>Sky High Mascara — SheGlamour</title>' . PHP_EOL;
echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . PHP_EOL;
echo '<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Inter:wght@300;400;500&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">' . PHP_EOL;
echo '<style>' . $css . '</style>' . PHP_EOL;
echo '</head>' . PHP_EOL;
echo '<body>' . PHP_EOL;

// ── TOP BANNER ────────────────────────────────────────────────────────────────
echo '<div class="top-banner">';
echo '<span class="flag">&#127465;&#127487;</span>';
echo '<span><strong>توصيل لكل الـ58 ولاية</strong> عبر كامل التراب الجزائري</span>';
echo '<span class="sep">&middot;</span>';
echo '<span>الدفع عند الاستلام</span>';
echo '</div>';

// ── HERO ─────────────────────────────────────────────────────────────────────
echo '<section class="hero">';
echo '<div class="hero-copy">';
echo '<div class="eyebrow">Maybelline New York &times; SheGlamour</div>';
echo '<h1 class="hero-title">Lash Sensational<br><em>Sky High Waterproof &reg;</em></h1>';
echo '<div class="hero-sub">Waterproof Mascara &nbsp; <span class="promo-badge">Promo</span></div>';
echo '<p class="hero-tagline">رموشك تتكلم قبل ما تحكي.<br>فرشاة تتعوج مع كل رمشة، وتديلك رموش <strong>من الأصل للرأس</strong> — بلا كتل، بلا فلشة.</p>';
echo '<div class="price-badge">';
echo '<div class="price-stack">';
echo '<div class="price-row">';
echo '<span class="price-main">' . priceTag($PRICE_PROMO) . '</span>';
echo '<span class="discount-badge">-' . discountPercent($PRICE_OLD, $PRICE_PROMO) . '%</span>';
echo '</div>';
echo '<span class="price-old">' . priceTag($PRICE_OLD) . '</span>';
echo '</div>';
echo '<span class="price-note">التوصيل مشمول</span>';
echo '</div>';
echo '<button class="btn-hero" onclick="document.getElementById(\'order\').scrollIntoView({behavior:\'smooth\'})">اطلبيها الآن &larr;</button>';
echo '</div>';
echo '<div class="hero-img">';
echo '<img src="https://www.maybelline.com/-/media/project/loreal/brand-sites/mny/americas/us/eye-makeup/mascara/lash-sensational-sky-high-washable-mascara-makeup/new-2025/mny-eye-mascara-ls-sky-high-2026-atf-full-beauty-3_7863360.jpg" alt="Sky High Mascara" loading="eager">';
echo '<div class="packshot-wrap"><img src="https://www.maybelline.com/-/media/project/loreal/brand-sites/mny/americas/us/eye-makeup/mascara/lash-sensational-sky-high-washable-mascara-makeup/new-2025/041554590906-maybelline-mascara-lash-sensational-lash-sensational-sky-high-mascara-atf-av49_7863356.jpg" alt="Sky High Packshot"></div>';
echo '</div>';
echo '</section>';

// ── SPOTLIGHT ─────────────────────────────────────────────────────────────────
echo '<section class="spotlight">';
echo '<div class="spotlight-img"><img src="https://www.maybelline.com/-/media/project/loreal/brand-sites/mny/americas/us/eye-makeup/mascara/lash-sensational-sky-high-washable-mascara-makeup/new-2025/mny-eye-mascara-ls-sky-high-2026-atf-application_7863359.jpg" alt="Application Sky High"></div>';
echo '<div class="spotlight-packshots">';
echo '<img class="packshot-main" src="https://www.maybelline.com/-/media/project/loreal/brand-sites/mny/americas/us/eye-makeup/mascara/lash-sensational-sky-high-washable-mascara-makeup/new-2025/maybelline-lash-sensational-sky-high-wsh-801-very-black-041554590500-av12_7859305.jpg" alt="Sky High Very Black">';
echo '<div class="packshot-label">Very Black &middot; Waterproof</div>';
echo '<p style="font-size:13px;color:#888;text-align:center;line-height:1.7;max-width:260px">فورمولا بـ bamboo extract وألياف خاصة<br>— رموش ممتلئة، خفيفة، تبقى طول النهار.</p>';
echo '</div>';
echo '</section>';

// ── BENEFITS ─────────────────────────────────────────────────────────────────
echo '<section class="benefits">';
echo '<div class="section-eyebrow">ليش Sky High</div>';
echo '<h2 class="section-title">اختاريها… وما تندميش</h2>';
echo '<div class="benefit-grid">';
$benefits = [
    ['🪶', 'خفيفة كالريشة',   'فورمولا بـ bamboo extract وألياف خاصة — رموش مملوءة وخفيفة، مش ثقيلة ولا مكتلة.'],
    ['💧', 'Waterproof 100%', 'قيظ جزائري، عرق، دموع فرح — Sky High ما تزيدش. تتحمل كل شيء طول النهار.'],
    ['✨', 'Flex Tower Brush', 'الفرشاة المرنة تلتف مع شكل عيونك وتلحق كل رمشة من الجذر للرأس — حتى الرموش الصغيرة.'],
];
foreach ($benefits as [$icon, $title, $desc]) {
    echo '<div class="benefit-card">';
    echo '<span class="benefit-icon">' . $icon . '</span>';
    echo '<div class="benefit-title">' . e($title) . '</div>';
    echo '<p class="benefit-desc">' . e($desc) . '</p>';
    echo '</div>';
}
echo '</div></section>';

// ── HOW TO ────────────────────────────────────────────────────────────────────
echo '<section class="howto">';
echo '<div class="howto-img"><img src="https://www.maybelline.com/-/media/project/loreal/brand-sites/mny/americas/us/eye-makeup/mascara/lash-sensational-sky-high-washable-mascara-makeup/new-2025/maybelline-mascara-lash-sensational-lash-sensational-sky-high-mascara-atf-av14_7863355.jpg" alt="كيفاش تطبقي Sky High"></div>';
echo '<div>';
echo '<div class="section-eyebrow">كيفاش تديها صح</div>';
echo '<h2 class="section-title">4 خطوات وخالص</h2>';
echo '<ul class="steps">';
$steps = [
    ['١', 'ابدي من الجذر وروحي لفوق ببطء — الفرشاة هي اللي تشتغل.'],
    ['٢', 'كرري 2 إلى 3 مرات باش تضاعفي الحجم على حسب ما تحبي.'],
    ['٣', 'اتركيها تجف 10 ثواني وثقي — رموشك بانت من أول مرة.'],
    ['٤', 'للإزالة: micellar water زيتي واحتفظي برموشك سليمة.'],
];
foreach ($steps as [$num, $text]) {
    echo '<li><div class="step-num">' . $num . '</div> ' . e($text) . '</li>';
}
echo '</ul></div></section>';

// ── TESTIMONIAL ───────────────────────────────────────────────────────────────
echo '<section class="testimonial">';
echo '<div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>';
echo '<p class="testimonial-text">والله ما جبت رموشي هكاك — هذا ماسكارا حياتي دروك.</p>';
echo '<div class="testimonial-author">— Cliente SheGlamour &middot; Alger</div>';
echo '</section>';

// ── ORDER ─────────────────────────────────────────────────────────────────────
echo '<section class="order-section" id="order">';
echo '<div class="order-inner">';
echo '<div class="section-eyebrow">اطلبي الآن</div>';
echo '<h2 class="section-title">واحدة click وSky High عندك</h2>';

if ($success) {
    // ── Confirmation ──────────────────────────────────────────────────────────
    echo '<div class="confirm-box">';
    echo '<div class="confirm-icon">&#10003;</div>';
    echo '<h3>تم تأكيد طلبك بنجاح</h3>';
    echo '<p>شكراً لثقتك في SheGlamour. طلبك قيد التحضير الآن، وسيصلك خلال 3 إلى 5 أيام عمل عبر خدمة التوصيل لكل الـ58 ولاية، مع إمكانية الدفع عند الاستلام.</p>';
    echo '<div class="order-id-badge">' . e($order_id) . '</div>';
    echo '<p class="confirm-subnote">يرجى الاحتفاظ برقم الطلب لمتابعة حالة الشحنة</p>';

    $shippingInfo = $confirmedOrder['shipping'] ?? [];
    $orderTotal   = $confirmedOrder['total'] ?? $PRICE_PROMO;
    $wilayaName   = $shippingInfo['wilaya'] ?? null;
    $deliveryType = $shippingInfo['deliveryType'] ?? 'domicile';
    $shippingCost = array_key_exists('shippingCost', $shippingInfo) ? $shippingInfo['shippingCost'] : null;
    $deliveryLabel = $deliveryType === 'stopDesk' ? 'توصيل لمكتب Stop Desk' : 'توصيل للمنزل';
    $deliveryText  = $wilayaName ? ($deliveryLabel . ' — ' . $wilayaName) : $deliveryLabel;

    echo '<div class="confirm-summary">';
    echo '<div class="confirm-summary-line"><span>Sky High Waterproof Mascara &middot; Very Black</span><span>' . priceTag($PRICE_PROMO) . '</span></div>';
    if ($shippingCost !== null) {
        echo '<div class="confirm-summary-line"><span>' . e($deliveryText) . '</span><span>' . priceTag((float) $shippingCost) . '</span></div>';
    } else {
        echo '<div class="confirm-summary-line"><span>' . e($deliveryText) . '</span><span class="confirm-tbd">تُحدد عند الاتصال بك</span></div>';
    }
    echo '<div class="confirm-summary-total"><span>المجموع</span><span class="confirm-summary-total-price">' . priceTag($orderTotal) . '</span></div>';
    echo '</div>';

    echo '<div class="confirm-steps">';
    echo '<div class="confirm-step"><span class="confirm-step-icon">&#128203;</span>تجهيز الطلب</div>';
    echo '<div class="confirm-step"><span class="confirm-step-icon">&#128666;</span>الشحن</div>';
    echo '<div class="confirm-step"><span class="confirm-step-icon">&#127968;</span>التسليم</div>';
    echo '</div>';
    echo '</div>';
} else {
    // ── Formulaire ────────────────────────────────────────────────────────────
    if ($error_msg) {
        echo '<div class="alert-error">' . e($error_msg) . '</div>';
    }

    echo '<form class="form-wrap" method="POST" action="#order" novalidate>';
    echo '<div class="form-row">';
    field('firstName', 'الاسم الأول *', 'text', 'أميرة',  $fieldErrors, ['err' => 'اكتبي اسمك (حرفين على الأقل)']);
    field('lastName',  'اللقب *',       'text', 'بن علي', $fieldErrors, ['err' => 'اكتبي لقبك']);
    echo '</div>';

    field('phone', 'رقم الهاتف *', 'tel', '0550 000 000', $fieldErrors, [
        'dir'   => 'ltr',
        'style' => 'text-align:right',
        'err'   => 'رقم غير صحيح (9 أرقام على الأقل)',
    ]);

    // Wilaya select (triée alphabétiquement, valeur = code officiel)
    $wilayasSorted = $WILAYAS;
    asort($wilayasSorted);
    $wErr = isset($fieldErrors['wilaya']) ? ' has-error' : '';
    echo '<div class="field' . $wErr . '">';
    echo '<label>الولاية *</label>';
    echo '<select name="wilaya" id="wilayaSelect">';
    echo '<option value="">— اختاري —</option>';
    foreach ($wilayasSorted as $code => $name) {
        $sel = (($_POST['wilaya'] ?? '') === $code) ? ' selected' : '';
        $d   = $TARIFS_LIVRAISON[$code]['domicile'] ?? '';
        $s   = $TARIFS_LIVRAISON[$code]['stopDesk'] ?? '';
        echo '<option value="' . e($code) . '" data-domicile="' . e((string)$d) . '" data-stopdesk="' . e((string)$s) . '"' . $sel . '>' . e($name) . ' (' . e($code) . ')</option>';
    }
    echo '</select>';
    if (isset($fieldErrors['wilaya'])) echo '<div class="field-error">اختاري الولاية</div>';
    echo '</div>';

    // Type de livraison
    $selectedDelivery = $_POST['deliveryType'] ?? 'domicile';
    echo '<div class="field">';
    echo '<label>نوع التوصيل *</label>';
    echo '<div class="delivery-options">';
    echo '<label class="delivery-option' . ($selectedDelivery === 'domicile' ? ' active' : '') . '">';
    echo '<input type="radio" name="deliveryType" value="domicile"' . ($selectedDelivery === 'domicile' ? ' checked' : '') . '>';
    echo '<span class="delivery-option-name">توصيل للمنزل</span>';
    echo '<span class="delivery-option-price" id="priceDomicile">—</span>';
    echo '</label>';
    echo '<label class="delivery-option' . ($selectedDelivery === 'stopDesk' ? ' active' : '') . '">';
    echo '<input type="radio" name="deliveryType" value="stopDesk"' . ($selectedDelivery === 'stopDesk' ? ' checked' : '') . '>';
    echo '<span class="delivery-option-name">توصيل لمكتب Stop Desk</span>';
    echo '<span class="delivery-option-price" id="priceStopDesk">—</span>';
    echo '</label>';
    echo '</div>';
    echo '</div>';

    field('address', 'العنوان *', 'text', 'الشارع، الرقم، البلدية', $fieldErrors, ['err' => 'العنوان قصير جداً (5 أحرف على الأقل)']);

    echo '<div class="order-total">';
    echo '<div class="total-lines">';
    echo '<div class="total-line"><span>المنتج</span><span class="total-line-price"><span class="total-old">' . priceTag($PRICE_OLD) . '</span>' . priceTag($PRICE_PROMO) . '<span class="discount-badge sm">-' . discountPercent($PRICE_OLD, $PRICE_PROMO) . '%</span></span></div>';
    echo '<div class="total-line"><span>التوصيل</span><span id="shippingLine">اختاري الولاية</span></div>';
    echo '</div>';
    echo '<div class="total-final">';
    echo '<span class="total-label">المجموع</span>';
    echo '<span class="total-amounts">';
    echo '<span class="total-amount" id="totalAmount">' . priceTag($PRICE_PROMO) . '</span>';
    echo '</span>';
    echo '</div>';
    echo '</div>';

    echo '<button type="submit" class="btn-order">تأكيد الطلب — <span id="btnPrice">' . priceTag($PRICE_PROMO) . '</span></button>';
    echo '</form>';

    $tarifsJson = json_encode($TARIFS_LIVRAISON, JSON_UNESCAPED_UNICODE);
    echo '<script>
(function(){
  var TARIFS = ' . $tarifsJson . ';
  var PRODUCT_PRICE = ' . (int) $PRICE_PROMO . ';
  var wilayaSelect  = document.getElementById("wilayaSelect");
  var shippingLine  = document.getElementById("shippingLine");
  var totalAmount   = document.getElementById("totalAmount");
  var btnPrice      = document.getElementById("btnPrice");
  var priceDomicile = document.getElementById("priceDomicile");
  var priceStopDesk = document.getElementById("priceStopDesk");
  var radios        = document.querySelectorAll(\'input[name="deliveryType"]\');
  if (!wilayaSelect) return;

  function fmt(n){ return n.toLocaleString("fr-FR").replace(/\\u202F|,/g, " ") + " DA"; }

  function selectedType(){
    for (var i = 0; i < radios.length; i++) if (radios[i].checked) return radios[i].value;
    return "domicile";
  }

  function update(){
    var code   = wilayaSelect.value;
    var type   = selectedType();
    var tarif  = TARIFS[code];

    radios.forEach(function(r){ r.closest(".delivery-option").classList.toggle("active", r.checked); });

    if (!code) {
      shippingLine.textContent = "اختاري الولاية";
      priceDomicile.textContent = "—";
      priceStopDesk.textContent = "—";
      totalAmount.innerHTML = btnPrice.innerHTML = "<span style=\\"direction:ltr;unicode-bidi:isolate;display:inline-block\\">" + fmt(PRODUCT_PRICE) + "</span>";
      return;
    }

    if (!tarif) {
      shippingLine.textContent = "تُحدد عند الاتصال بك";
      priceDomicile.textContent = "—";
      priceStopDesk.textContent = "—";
      totalAmount.innerHTML = btnPrice.innerHTML = "<span style=\\"direction:ltr;unicode-bidi:isolate;display:inline-block\\">" + fmt(PRODUCT_PRICE) + "</span>";
      return;
    }

    priceDomicile.textContent = fmt(tarif.domicile);
    priceStopDesk.textContent = fmt(tarif.stopDesk);

    var shippingCost = tarif[type];
    shippingLine.innerHTML = "<span style=\\"direction:ltr;unicode-bidi:isolate;display:inline-block\\">" + fmt(shippingCost) + "</span>";

    var total = PRODUCT_PRICE + shippingCost;
    var html = "<span style=\\"direction:ltr;unicode-bidi:isolate;display:inline-block\\">" + fmt(total) + "</span>";
    totalAmount.innerHTML = html;
    btnPrice.innerHTML = html;
  }

  wilayaSelect.addEventListener("change", update);
  radios.forEach(function(r){ r.addEventListener("change", update); });
  update();
})();
</script>';

    if (!empty($fieldErrors)) {
        echo '<script>document.getElementById("order").scrollIntoView({behavior:"smooth"});</script>';
    }
}

echo '<div class="trust-bar">';
echo '<span class="trust-item"><span>&#128274;</span> دفع عند التسليم</span>';
echo '<span class="trust-item"><span>&#128666;</span> توصيل لكل الولايات</span>';
echo '<span class="trust-item"><span>&#9989;</span> طلبك آمن 100%</span>';
echo '</div>';

echo '</div></section>';

// ── STICKY CTA ────────────────────────────────────────────────────────────────
if (!$success) {
    echo '<div class="sticky-cta" id="stickyCta">';
    echo '<div class="sticky-cta-info">';
    echo '<div class="sticky-cta-price-row">';
    echo '<span class="sticky-cta-old">' . priceTag($PRICE_OLD) . '</span>';
    echo '<span class="sticky-cta-price">' . priceTag($PRICE_PROMO) . '</span>';
    echo '<span class="discount-badge sm">-' . discountPercent($PRICE_OLD, $PRICE_PROMO) . '%</span>';
    echo '</div>';
    echo '<span class="sticky-cta-note">التوصيل مشمول &middot; 58 ولاية</span>';
    echo '</div>';
    echo '<button class="sticky-cta-btn" onclick="document.getElementById(\'order\').scrollIntoView({behavior:\'smooth\'})">اطلبيها الآن</button>';
    echo '</div>';

    echo '<script>
(function(){
  var sticky = document.getElementById("stickyCta");
  var hero   = document.querySelector(".hero");
  var order  = document.getElementById("order");
  if (!sticky || !hero || !order) return;

  var heroObs = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if (!entry.isIntersecting && entry.boundingClientRect.top < 0) {
        sticky.classList.add("visible");
      } else {
        sticky.classList.remove("visible");
      }
    });
  }, { threshold: 0 });
  heroObs.observe(hero);

  var orderObs = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if (entry.isIntersecting) sticky.classList.remove("visible");
    });
  }, { threshold: 0.25 });
  orderObs.observe(order);
})();
</script>';
}

echo '</body></html>';