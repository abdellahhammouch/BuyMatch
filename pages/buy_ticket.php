<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Acheter billet</title>
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="home.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
      <nav class="navlinks">
        <a href="matchs.php">Matchs</a>
        <a href="profile.php">Mon espace</a>
      </nav>
      <div class="nav-actions">
        <button class="iconbtn mobile-toggle" onclick="toggleMobileMenu()"><i class="fa-solid fa-bars"></i></button>
        <a class="btn btn-ghost" href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
      </div>
    </div>
    <div class="mobile-menu" id="mobileMenu">
      <a href="matchs.php">Matchs</a>
      <a href="profile.php">Mon espace</a>
      <a href="login.php">Connexion</a>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Acheter un billet</h2>
        <p>Formulaire statique (à connecter au backend ensuite)</p>
      </div>
    </div>

    <div class="form">
      <div class="form-row">
        <div class="field">
          <label>Match</label>
          <input class="input" value="Raja Casablanca vs Wydad AC" disabled />
        </div>
        <div class="field">
          <label>Catégorie</label>
          <select class="select">
            <option>VIP - 250 DH</option>
            <option>Standard - 120 DH</option>
            <option>Économie - 60 DH</option>
          </select>
        </div>
      </div>

      <div class="form-row" style="margin-top:12px;">
        <div class="field">
          <label>Place numérotée</label>
          <input class="input" placeholder="Ex: A-12" />
        </div>
        <div class="field">
          <label>Quantité (max 4)</label>
          <input id="qty" class="input" type="number" min="1" max="4" value="1" />
        </div>
      </div>

      <div style="margin-top:12px;">
        <div class="field">
          <label>Note</label>
          <div class="meta" style="gap:12px;">
            <span><i class="fa-solid fa-circle-info"></i> Paiement, PDF, email : à implémenter côté PHP plus tard</span>
          </div>
        </div>
      </div>

      <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn btn-ghost" href="match_details.php"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        <button class="btn btn-primary" type="button"><i class="fa-solid fa-credit-card"></i> Confirmer l’achat</button>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap;">
    <div>© 2026 BuyMatch</div>
    <div style="display:flex; gap:14px;">
      <a href="matchs.php">Matchs</a>
      <a href="profile.php">Mon espace</a>
    </div>
  </div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
