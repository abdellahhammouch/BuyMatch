<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Détails du match</title>
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="home.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
      <nav class="navlinks">
        <a href="home.php">Accueil</a>
        <a class="active" href="matchs.php">Matchs</a>
        <a href="login.php">Connexion</a>
      </nav>
      <div class="nav-actions">
        <button class="iconbtn mobile-toggle" onclick="toggleMobileMenu()"><i class="fa-solid fa-bars"></i></button>
        <a class="btn btn-primary" href="buy_ticket.php"><i class="fa-solid fa-ticket"></i> Acheter</a>
      </div>
    </div>
    <div class="mobile-menu" id="mobileMenu">
      <a href="home.php">Accueil</a>
      <a href="matchs.php">Matchs</a>
      <a href="login.php">Connexion</a>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="hero-card" style="padding:18px;">
      <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div>
          <h2 style="margin:0 0 8px;">Raja Casablanca vs Wydad AC</h2>
          <div class="meta">
            <span><i class="fa-solid fa-location-dot"></i> Stade Mohammed V — Casablanca</span>
            <span><i class="fa-solid fa-calendar-day"></i> 10/01/2026</span>
            <span><i class="fa-solid fa-clock"></i> 20:45</span>
            <span><i class="fa-solid fa-hourglass-half"></i> 90 min</span>
          </div>
        </div>
        <div style="display:flex; gap:10px; align-items:flex-start;">
          <span class="badge"><i class="fa-solid fa-circle-check"></i> Publié</span>
          <a class="btn btn-primary" href="buy_ticket.php"><i class="fa-solid fa-ticket"></i> Acheter</a>
        </div>
      </div>

      <div style="margin-top:16px;">
        <h3 style="margin:0 0 10px;">Catégories et prix</h3>
        <table class="table">
          <thead>
            <tr>
              <th>Catégorie</th>
              <th>Prix</th>
              <th>Places restantes</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>VIP</td>
              <td>250 DH</td>
              <td>120</td>
            </tr>
            <tr>
              <td>Standard</td>
              <td>120 DH</td>
              <td>640</td>
            </tr>
            <tr>
              <td>Économie</td>
              <td>60 DH</td>
              <td>980</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div style="margin-top:16px;">
        <h3 style="margin:0 0 10px;">Informations</h3>
        <div class="card" style="background:rgba(255,255,255,.03)">
          <p style="margin:0; color:var(--muted); line-height:1.8">
            Exemple de description : match de championnat, accès porte A/B selon catégorie,
            billet électronique envoyé par email après paiement (à rendre dynamique plus tard).
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap;">
    <div>© 2026 BuyMatch</div>
    <div style="display:flex; gap:14px;">
      <a href="matchs.php">Matchs</a>
      <a href="login.php">Connexion</a>
    </div>
  </div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
