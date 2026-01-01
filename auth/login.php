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
      <a class="brand" href="home.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
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

    <div class="form" style="max-width:520px; margin:0 auto;">
      <div class="field">
        <label>Email</label>
        <input class="input" placeholder="email@exemple.com" />
      </div>
      <div class="field" style="margin-top:12px;">
        <label>Mot de passe</label>
        <input class="input" type="password" placeholder="••••••••" />
      </div>

      <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn btn-primary" type="button"><i class="fa-solid fa-right-to-bracket"></i> Se connecter</button>
        <a class="btn btn-ghost" href="home.php"><i class="fa-solid fa-arrow-left"></i> Retour</a>
      </div>

      <p style="margin:14px 0 0; color:var(--muted);">
        Pas de compte ? <a href="register.php" style="color:var(--text); font-weight:800;">Créer un compte</a>
      </p>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
