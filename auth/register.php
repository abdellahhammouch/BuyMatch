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

    <div class="form" style="max-width:720px; margin:0 auto;">
      <div class="form-row">
        <div class="field">
          <label>Nom</label>
          <input class="input" placeholder="Votre nom" />
        </div>
        <div class="field">
          <label>Prénom</label>
          <input class="input" placeholder="Votre prénom" />
        </div>
      </div>

      <div class="form-row" style="margin-top:12px;">
        <div class="field">
          <label>Email</label>
          <input class="input" placeholder="email@exemple.com" />
        </div>
        <div class="field">
          <label>Téléphone</label>
          <input class="input" placeholder="+212 6XX-XXXXXX" />
        </div>
      </div>

      <div class="form-row" style="margin-top:12px;">
        <div class="field">
          <label>Mot de passe</label>
          <input class="input" type="password" placeholder="••••••••" />
        </div>
        <div class="field">
          <label>Rôle</label>
          <select class="select">
            <option>Acheteur</option>
            <option>Organisateur</option>
          </select>
        </div>
      </div>

      <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn btn-primary" type="button"><i class="fa-solid fa-user-plus"></i> Créer le compte</button>
        <a class="btn btn-ghost" href="home.php"><i class="fa-solid fa-arrow-left"></i> Retour</a>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
