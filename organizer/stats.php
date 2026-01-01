<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Organisateur — Statistiques</title>
  <link rel="stylesheet" href="../assets/style.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container nav">
    <a class="brand" href="../pages/home.php">
      <span class="logo"><i class="fa-solid fa-ticket"></i></span>
      <span>BuyMatch</span>
    </a>
    <nav class="navlinks" data-nav>
      <a href="create_match.php"><i class="fa-solid fa-square-plus"></i> Créer</a>
      <a class="active" href="stats.php"><i class="fa-solid fa-chart-line"></i> Stats</a>
      <a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
    </nav>
    <div class="actions">
      <button class="iconbtn mobileToggle" data-nav-toggle aria-label="Menu">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>
  </div>
</header>

<main class="container section">
  <div class="sectionHead">
    <div>
      <h2 class="h2">Statistiques organisateur</h2>
      <p class="sub">Billets vendus & chiffre d’affaires (exemple).</p>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <div class="thumb"><i class="fa-solid fa-ticket"></i><span>Billets</span></div>
      <div class="cardBody"><div class="stat"><div class="v">320</div><div class="l">Vendus</div></div></div>
      <div class="cardFoot"><span class="badge ok">OK</span><span class="price">320</span></div>
    </div>

    <div class="card">
      <div class="thumb"><i class="fa-solid fa-sack-dollar"></i><span>Chiffre d’affaires</span></div>
      <div class="cardBody"><div class="stat"><div class="v">41 200 DH</div><div class="l">Total</div></div></div>
      <div class="cardFoot"><span class="badge ok">CA</span><span class="price">+12%</span></div>
    </div>

    <div class="card">
      <div class="thumb"><i class="fa-solid fa-comment"></i><span>Avis</span></div>
      <div class="cardBody"><div class="stat"><div class="v">4.3</div><div class="l">Note moyenne</div></div></div>
      <div class="cardFoot"><span class="badge pending">Matches</span><span class="price">3</span></div>
    </div>
  </div>

  <div class="panel" style="margin-top:14px;">
    <h2 class="h2" style="margin:0 0 10px;">Détails par match</h2>
    <table class="table">
      <thead>
        <tr>
          <th>Match</th>
          <th>Billets vendus</th>
          <th>CA</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>Raja vs Wydad</td><td>210</td><td>28 900 DH</td><td><span class="badge ok">approved</span></td></tr>
        <tr><td>FAR vs IRT</td><td>110</td><td>12 300 DH</td><td><span class="badge ok">approved</span></td></tr>
      </tbody>
    </table>
  </div>
</main>

<script src="../assets/script.js"></script>
</body>
</html>
