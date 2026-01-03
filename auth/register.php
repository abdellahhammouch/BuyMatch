<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../classes/Auth.php";
require_once __DIR__ . "/../classes/Session.php";


?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Inscription</title>
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="home.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
      <div class="nav-actions">
        <a class="btn btn-ghost" href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
      </div>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Inscription</h2>
        <p>Formulaire statique</p>
      </div>
    </div>

    <form id="registerForm" action="register_handling.php" method="POST" enctype="multipart/form-data">

      <div class="avatar-upload">
        <div class="avatar-circle" id="avatarCircle">
          <i class="fas fa-user"></i>
          <img id="avatarPreview" src="" alt="Avatar">
        </div>
      </div>
      <div class="form-group">
          <label for="userType">Je m'inscris en tant que</label>
          <div class="input-group">
              <i class="fas fa-user-tag"></i>
              <select name="role" id="userType" class="form-control" required>
                  <option value="">Sélectionnez votre rôle</option>
                  <option value="organisateur">Organisateur</option>
                  <option value="acheteur">Acheteur</option>
              </select>
          </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
          <div class="form-group">
              <label for="firstName">Prénom</label>
              <div class="input-group">
                  <i class="fas fa-user"></i>
                  <input type="text" name="prenom" id="firstName" class="form-control" placeholder="Votre prénom" required>
              </div>
          </div>

          <div class="form-group">
              <label for="lastName">Nom</label>
              <div class="input-group">
                  <i class="fas fa-user"></i>
                  <input type="text" name="nom" id="lastName" class="form-control" placeholder="Votre nom" required>
              </div>
          </div>
      </div>

      <div class="form-group">
          <label for="email">Email</label>
          <div class="input-group">
              <i class="fas fa-envelope"></i>
              <input type="email" name="email" id="email" class="form-control" placeholder="votre@email.com" required>
          </div>
      </div>

      <div class="form-group">
          <label for="phone">Téléphone</label>
          <div class="input-group">
              <i class="fas fa-phone"></i>
              <input type="tel" name="phone" id="phone" class="form-control" placeholder="+212 6XX-XXXXXX" required>
          </div>
      </div>

      <div class="form-group">
          <label for="photo">Photo de profil</label>
          <div class="input-group">
              <i class="fas fa-image"></i>
              <input type="file" name="photo" id="photoInput" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp" required>
          </div>
          <small style="color: var(--text-gray);">Formats: JPG, PNG, WEBP (max 2MB)</small>
      </div>

      <div class="form-group">
          <label for="password">Mot de passe</label>
          <div class="input-group">
              <i class="fas fa-lock"></i>
              <input type="password" name="password" id="password" class="form-control" placeholder="Min. 8 caractères" required>
          </div>
      </div>

      <div class="form-group">
          <label for="confirmPassword">Confirmer le mot de passe</label>
          <div class="input-group">
              <i class="fas fa-lock"></i>
              <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" placeholder="Confirmez votre mot de passe" required>
          </div>
      </div>

      <!-- CHAMPS COACH -->
      <div id="coachFields" style="display:none;">
          <div class="form-group">
              <label>Vos Spécialités</label>

              <div class="tag-input" id="tags"></div>
              <input type="hidden" name="disciplines" id="hiddenInput">

              <div class="choices">
                  <span class="choice" data-value="Football"><i class="fas fa-futbol"></i> Football</span>
                  <span class="choice" data-value="Tennis"><i class="fas fa-table-tennis"></i> Tennis</span>
                  <span class="choice" data-value="Natation"><i class="fas fa-swimmer"></i> Natation</span>
                  <span class="choice" data-value="Boxe"><i class="fas fa-fist-raised"></i> Boxe</span>
                  <span class="choice" data-value="Preparation physique"><i class="fas fa-dumbbell"></i> Préparation physique</span>
              </div>
          </div>

          <div class="form-group">
              <label for="experience">Années d'expérience</label>
              <div class="input-group">
                  <i class="fas fa-medal"></i>
                  <input type="number" name="experience" id="experience" class="form-control" min="0">
              </div>
          </div>

          <div class="form-group">
              <label for="biographie">Biographie</label>
              <textarea name="biographie" id="biographie" class="form-control" rows="4"></textarea>
          </div>
      </div>

      <button type="submit" name="signup" class="btn-submit">
          <i class="fas fa-user-plus"></i> Créer mon compte
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
