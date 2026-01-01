<?php
// pages/home.php
// Option backend (si tu as déjà $matches depuis DB) : remplace le tableau ci-dessous.
$matches = $matches ?? [
  ["id"=>1,"team1"=>"Raja","team2"=>"Wydad","city"=>"Casablanca","stadium"=>"Stade Mohammed V","date"=>"2026-01-03 20:00","price"=>80,"status"=>"approved"],
  ["id"=>2,"team1"=>"FAR","team2"=>"IRT","city"=>"Rabat","stadium"=>"Stade Moulay Abdellah","date"=>"2026-01-05 18:30","price"=>60,"status"=>"approved"],
  ["id"=>3,"team1"=>"OCS","team2"=>"MAS","city"=>"Safi","stadium"=>"Stade El Massira","date"=>"2026-01-08 19:00","price"=>50,"status"=>"pending"],
];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BuyMatch — Accueil</title>
  <link rel="stylesheet" href="../assets/style.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container nav">
    <a class="brand" href="home.php">
      <span class="logo"><i class="fa-solid fa-ticket"></i></span>
      <span>BuyMatch</span>
    </a>

    <nav class="navlinks" data-nav>
      <a class="active" href="home.php"><i class="fa-solid fa-house"></i> Accueil</a>
      <a href="matchs.php"><i class="fa-solid fa-futbol"></i> Matchs</a>
      <a href="../auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
      <a href="../auth/register.php"><i class="fa-solid fa-user-plus"></i> Inscription</a>
    </nav>

    <div class="actions">
      <button class="iconbtn mobileToggle" data-nav-toggle aria-label="Menu">
        <i class="fa-solid fa-bars"></i>
      </button>
      <a class="btn ghost" href="matchs.php"><i class="fa-solid fa-magnifying-glass"></i> Explorer</a>
      <a class="btn primary" href="../auth/register.php"><i class="fa-solid fa-bolt"></i> Commencer</a>
    </div>
  </div>
</header>

<main class="container">

  <section class="hero">
    <div class="heroGrid">
      <div class="heroCard">
        <span class="kicker"><i class="fa-solid fa-shield"></i> Plateforme de billetterie sportive</span>
        <h1 class="heroTitle">Réservez des billets avec une expérience premium.</h1>
        <p class="heroText">
          Parcourez les matchs publiés, consultez les détails, puis achetez jusqu’à 4 billets par match
          après connexion. Design moderne, rapide, responsive.
        </p>
        <div class="heroActions">
          <a class="btn primary" href="matchs.php"><i class="fa-solid fa-futbol"></i> Voir les matchs</a>
          <a class="btn" href="../auth/login.php"><i class="fa-solid fa-user-lock"></i> Se connecter</a>
        </div>
      </div>

      <div class="statsCard">
        <div class="kicker"><i class="fa-solid fa-chart-line"></i> Aperçu</div>
        <div class="statsGrid">
          <div class="stat">
            <div class="v"><?php echo count($matches); ?></div>
            <div class="l">Matchs visibles</div>
          </div>
          <div class="stat">
            <div class="v">Jusqu’à 4</div>
            <div class="l">Billets par match</div>
          </div>
          <div class="stat">
            <div class="v">PDF</div>
            <div class="l">Billet généré</div>
          </div>
          <div class="stat">
            <div class="v">Email</div>
            <div class="l">Envoi billet</div>
          </div>
        </div>
        <p class="sub" style="margin-top:14px;">
          Accès visiteur : liste + détails. Achat réservé aux comptes.
        </p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="sectionHead">
      <div>
        <h2 class="h2">Matchs en vedette</h2>
        <p class="sub">Sélection de matchs récents (exemple). Clique pour voir les détails.</p>
      </div>
      <div class="tools">
        <a class="btn small" href="matchs.php"><i class="fa-solid fa-layer-group"></i> Tous les matchs</a>
      </div>
    </div>

    <div class="grid">
      <?php foreach ($matches as $m): ?>
        <article class="card">
          <div class="thumb">
            <i class="fa-solid fa-futbol"></i>
            <span><?php echo htmlspecialchars($m["team1"]." vs ".$m["team2"]); ?></span>
          </div>
          <div class="cardBody">
            <h3 class="cardTitle"><?php echo htmlspecialchars($m["team1"]." vs ".$m["team2"]); ?></h3>
            <div class="meta">
              <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($m["city"]); ?></span>
              <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($m["date"]); ?></span>
            </div>
          </div>
          <div class="cardFoot">
            <div class="price"><i class="fa-solid fa-tag"></i> À partir de <?php echo (int)$m["price"]; ?> DH</div>
            <a class="btn small" href="match_details.php?id=<?php echo (int)$m["id"]; ?>">
              Détails <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

</main>

<footer class="footer">
  <div class="container row">
    <div>© <?php echo date("Y"); ?> BuyMatch — Billetterie sportive</div>
    <div class="sub">PHP OOP · MySQL · PDF · Mail</div>
  </div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
