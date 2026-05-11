<?php
session_start();
include_once 'includes/db.php';

// Si l'utilisateur est déjà connecté → rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Gestion des messages d'erreur ou de succès
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $numerotel = trim($_POST['numerotel']);
    $adresse = trim($_POST['adresse']);

    // Vérifications de base
    if (!$prenom || !$nom || !$email || !$password || !$password_confirm || !$numerotel || !$adresse) {
        header('Location: signup.php?error=Veuillez remplir tous les champs.');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: signup.php?error=Email invalide.');
        exit;
    }

    if ($password !== $password_confirm) {
        header('Location: signup.php?error=Les mots de passe ne correspondent pas.');
        exit;
    }

    if (!preg_match('/^\d{8,15}$/', $numerotel)) {
        header('Location: signup.php?error=Numéro de téléphone invalide.');
        exit;
    }

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT * FROM clientglam WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: signup.php?error=Email déjà utilisé.');
        exit;
    }

    // Hash du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insérer l'utilisateur
    $insertStmt = $pdo->prepare("
        INSERT INTO clientglam (prenom, nom, email, motdepasse, numerotel, adresse)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insertStmt->execute([$prenom, $nom, $email, $hashedPassword, $numerotel, $adresse]);

    // Rediriger vers login avec message succès
    header('Location: login.php?success=Compte créé avec succès. Vous pouvez maintenant vous connecter.');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Créer un compte - SheGlamour</title>
  <link rel="stylesheet" href="signup.css">
</head>
<body>
  <div class="signup-container">
    <h2>Créer un compte</h2>

    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form action="signup.php" method="post">
      <label for="prenom">Prénom :</label>
      <input type="text" name="prenom" id="prenom" required>

      <label for="nom">Nom :</label>
      <input type="text" name="nom" id="nom" required>

      <label for="email">Email :</label>
      <input type="email" name="email" id="email" required>

      <label for="password">Mot de passe :</label>
      <input type="password" name="password" id="password" required>

      <label for="password_confirm">Confirmer le mot de passe :</label>
      <input type="password" name="password_confirm" id="password_confirm" required>

      <label for="numerotel">Numéro de téléphone :</label>
      <input type="text" name="numerotel" id="numerotel" required>

      <label for="adresse">Adresse :</label>
      <textarea name="adresse" id="adresse" required></textarea>

      <button type="submit">Créer mon compte</button>
    </form>

    <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
  </div>
</body>
</html>
<style>
 /* ══════════════════════════════════════
   SIGNUP — Palette #F5F1EE × #440B19
══════════════════════════════════════ */

@import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap');

/* ── Variables ── */
:root {
  --bordeaux:   #440B19;
  --bordeaux-s: #5c1022;
  --bordeaux-l: #6e1a2e;
  --cream:      #F5F1EE;
  --border:     rgba(68, 11, 25, 0.15);
  --serif:      'Syne', 'Helvetica Neue', sans-serif;
  --sans:       'DM Sans', system-ui, sans-serif;
  --ease:       cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* ── Reset ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Body ── */
body {
  font-family: var(--sans);
  background: var(--cream);
  color: var(--bordeaux);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 16px;
  -webkit-font-smoothing: antialiased;
}

/* ── Container ── */
.signup-container {
  width: 100%;
  max-width: 480px;
  background: var(--cream);
  border: 1px solid var(--border);
  padding: 52px 48px;
  box-shadow: 0 24px 64px rgba(68, 11, 25, 0.10);
  text-align: center;
}

/* ── Titre ── */
.signup-container h2 {
  font-family: var(--serif);
  font-size: 1.85rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--bordeaux);
  margin-bottom: 8px;
}

/* Trait ornemental sous le titre */
.signup-container h2::after {
  content: '';
  display: block;
  width: 40px;
  height: 1.5px;
  background: var(--bordeaux);
  margin: 14px auto 32px;
}

/* ── Messages ── */
.error {
  font-size: 0.82rem;
  font-weight: 500;
  letter-spacing: 0.04em;
  color: var(--bordeaux);
  background: rgba(68, 11, 25, 0.07);
  border: 1px solid rgba(68, 11, 25, 0.20);
  padding: 10px 14px;
  margin-bottom: 22px;
}

.success {
  font-size: 0.82rem;
  font-weight: 500;
  letter-spacing: 0.04em;
  color: #1a4a1a;
  background: rgba(26, 74, 26, 0.07);
  border: 1px solid rgba(26, 74, 26, 0.20);
  padding: 10px 14px;
  margin-bottom: 22px;
}

/* ── Formulaire ── */
form {
  display: flex;
  flex-direction: column;
  gap: 0;
  text-align: left;
}

label {
  display: block;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.20em;
  text-transform: uppercase;
  color: var(--bordeaux-l);
  margin-bottom: 7px;
  margin-top: 18px;
}

label:first-of-type { margin-top: 0; }

input[type="text"],
input[type="email"],
input[type="password"],
textarea {
  width: 100%;
  padding: 11px 14px;
  background: var(--cream);
  border: 1px solid var(--border);
  color: var(--bordeaux);
  font-family: var(--sans);
  font-size: 0.9rem;
  font-weight: 400;
  outline: none;
  transition:
    border-color 0.25s var(--ease),
    box-shadow   0.25s var(--ease);
}

input:focus,
textarea:focus {
  border-color: var(--bordeaux);
  box-shadow: 0 0 0 3px rgba(68, 11, 25, 0.08);
}

textarea {
  resize: vertical;
  min-height: 72px;
}

/* ── Bouton ── */
button[type="submit"] {
  margin-top: 30px;
  width: 100%;
  padding: 14px;
  background: var(--bordeaux);
  color: var(--cream);
  border: 1.5px solid var(--bordeaux);
  font-family: var(--sans);
  font-size: 0.76rem;
  font-weight: 600;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition:
    background 0.28s var(--ease),
    color      0.28s var(--ease),
    transform  0.2s var(--ease),
    box-shadow 0.28s var(--ease);
  box-shadow: 0 12px 28px rgba(68, 11, 25, 0.20);
}

button[type="submit"]:hover {
  background: var(--bordeaux-s);
  transform: translateY(-2px);
  box-shadow: 0 18px 36px rgba(68, 11, 25, 0.28);
}

button[type="submit"]:active {
  transform: scale(0.98);
}

/* ── Lien bas de page ── */
.signup-container > p {
  margin-top: 24px;
  font-size: 0.80rem;
  letter-spacing: 0.04em;
  color: var(--bordeaux-l);
}

.signup-container > p a {
  color: var(--bordeaux);
  text-decoration: underline;
  font-weight: 600;
  transition: opacity 0.2s;
}

.signup-container > p a:hover { opacity: 0.65; }

/* ══ RESPONSIVE ══════════════════════════════════════════════ */

/* Tablette (601 → 900 px) */
@media (min-width: 601px) and (max-width: 900px) {
  .signup-container {
    padding: 44px 38px;
    max-width: 440px;
  }
}

/* Mobile (≤ 600 px) */
@media (max-width: 600px) {
  body {
    align-items: flex-start;
    padding: 24px 12px 48px;
  }

  .signup-container {
    padding: 36px 22px;
    border: none;
    box-shadow: none;
    max-width: 100%;
  }

  .signup-container h2 {
    font-size: 1.55rem;
  }

  label {
    font-size: 0.68rem;
    margin-top: 14px;
  }

  input[type="text"],
  input[type="email"],
  input[type="password"],
  textarea {
    font-size: 0.88rem;
    padding: 10px 12px;
  }

  button[type="submit"] {
    padding: 13px;
    font-size: 0.72rem;
  }
}

</style>