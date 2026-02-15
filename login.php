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
<style>
  /* login.css */

/* ======= Reset & Base ======= */
body {
  font-family: 'Roboto', sans-serif;
  background: #f9f8f7; /* beige subtil */
  margin: 0;
  padding: 0;
  color: #1a1a1a;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

/* ======= Container ======= */
.login-container {
  background: #fff;
  padding: 40px 50px;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
  max-width: 400px;
  width: 90%;
  text-align: center;
  box-sizing: border-box;
}

/* ======= Titre ======= */
.login-container h2 {
  font-family: 'Playfair Display', serif;
  font-weight: 500;
  font-size: 2em;
  margin-bottom: 30px;
  letter-spacing: 0.5px;
  color: #111;
}

/* ======= Message d'erreur ======= */
.error {
  color: #d32f2f; /* rouge discret */
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

input[type="email"],
input[type="password"] {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1em;
  font-family: 'Roboto', sans-serif;
  transition: all 0.2s;
}

input[type="email"]:focus,
input[type="password"]:focus {
  border-color: #111;
  outline: none;
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
  .login-container {
    padding: 30px 20px;
  }

  h2 {
    font-size: 1.8em;
  }

  input[type="email"],
  input[type="password"] {
    font-size: 0.95em;
    padding: 8px 10px;
  }

  button {
    font-size: 0.95em;
    padding: 8px 0;
  }
}

</style>