<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../classes/Auth.php";
require_once __DIR__ . "/../classes/Session.php";

$success = $_SESSION["success"] ?? null;
$error   = $_SESSION["error"] ?? null;
$errors  = $_SESSION["errors"] ?? [];

unset($_SESSION["success"], $_SESSION["error"], $_SESSION["errors"]);
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Connexion</title>
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="../index.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
      <div class="nav-actions">
        <a class="btn btn-ghost" href="register.php"><i class="fa-solid fa-user-plus"></i> Inscription</a>
      </div>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Connexion</h2>
        <p>Formulaire statique</p>
      </div>
    </div>
    
    <?php if ($success): ?>
      <div class="success-message">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-triangle"></i>
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="login_handling.php" method="POST" style="max-width:520px; margin:0 auto;">
      <div class="form-group">
        <label for="role"><i class="fas fa-user-tag"></i> Rôle</label>
        <div class="input-group">
          <i class="fas fa-user-tag"></i>
          <select name="role" id="role" class="form-control" required>
            <option value="">Sélectionnez votre rôle</option>
            <option value="admin">Admin</option>
            <option value="organisateur">Organisateur</option>
            <option value="acheteur">Acheteur</option>
          </select>
        </div>
      </div>
      <div class="form-group">
          <label>Email</label>
          <div class="input-group">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" class="form-control" required>
          </div>
      </div>

      <div class="form-group">
        <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
        <div class="input-group password-group">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" id="password" class="form-control" placeholder="Votre mot de passe" required>

          <button type="button" class="toggle-password" id="togglePassword" aria-label="Afficher le mot de passe">
            <i class="fas fa-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" name="login" class="btn-submit">
          <i class="fas fa-right-to-bracket"></i> Se connecter
      </button>
    </form>

  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
