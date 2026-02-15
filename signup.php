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
  /* signup.css */

/* ======= Base ======= */
body {
  font-family: 'Roboto', sans-serif;
  background: #f9f8f7; /* beige subtil */
  color: #1a1a1a;
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

/* ======= Container ======= */
.signup-container {
  background: #fff;
  padding: 40px 50px;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
  max-width: 450px;
  width: 90%;
  text-align: center;
  box-sizing: border-box;
}

/* ======= Titre ======= */
.signup-container h2 {
  font-family: 'Playfair Display', serif;
  font-weight: 500;
  font-size: 2em;
  margin-bottom: 30px;
  letter-spacing: 0.5px;
  color: #111;
}

/* ======= Messages ======= */
.error {
  color: #d32f2f;
  margin-bottom: 20px;
  font-size: 0.95em;
}

.success {
  color: #388e3c;
  margin-bottom: 20px;
  font-size: 0.95em;
}

/* ======= Formulaire ======= */
form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

label {
  text-align: left;
  font-weight: 400;
  font-size: 0.95em;
  color: #333;
}

input[type="text"],
input[type="email"],
input[type="password"],
textarea {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1em;
  font-family: 'Roboto', sans-serif;
  transition: all 0.2s;
  width: 100%;
  box-sizing: border-box;
}

input:focus,
textarea:focus {
  border-color: #111;
  outline: none;
}

textarea {
  resize: vertical;
  min-height: 60px;
}

/* ======= Bouton ======= */
button {
  background: #111;
  color: #fff;
  border: none;
  padding: 10px 0;
  font-weight: 500;
  font-size: 1em;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
}

button:hover {
  background: #333;
}

/* ======= Lien ======= */
p a {
  color: #111;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
}

p a:hover {
  color: #555;
}

/* ======= Responsive ======= */
@media (max-width: 480px) {
  .signup-container {
    padding: 30px 20px;
  }

  h2 {
    font-size: 1.8em;
  }

  input, textarea, button {
    font-size: 0.95em;
    padding: 8px 10px;
  }
}

</style>