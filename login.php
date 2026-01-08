<?php
session_start();
include_once 'includes/db.php';

// Si l'utilisateur est déjà connecté → rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header('Location: login.php?error=Veuillez remplir tous les champs.');
        exit;
    }

    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT * FROM clientglam WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['motdepasse'])) {
            // Connexion réussie → créer session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];

            header('Location: index.php');
            exit;
        } else {
            header('Location: login.php?error=Mot de passe incorrect.');
            exit;
        }
    } else {
        header('Location: login.php?error=Email non trouvé.');
        exit;
    }
}

// Gestion de l'affichage du message d'erreur
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion - SheGlamour</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="login-container">
    <h2>Connexion</h2>

    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="login.php" method="post">
      <label for="email">Email :</label>
      <input type="email" name="email" id="email" required>

      <label for="password">Mot de passe :</label>
      <input type="password" name="password" id="password" required>

      <button type="submit">Se connecter</button>
    </form>

    <p>Pas encore inscrit ? <a href="signup.php">Créer un compte</a></p>
  </div>
</body>
</html>
