<?php
$id = (int)($_GET["id"] ?? 0);

// Exemple si pas de DB (tu remplaceras par MatchSport->getById($id))
$match = $match ?? [
  "id"=>$id ?: 1,
  "team1"=>"Raja",
  "team2"=>"Wydad",
  "city"=>"Casablanca",
  "stadium"=>"Stade Mohammed V",
  "date"=>"2026-01-03 20:00",
  "duration"=>90,
  "status"=>"approved",
  "categories"=>[
    ["id"=>1,"name"=>"VIP","price"=>200,"quota"=>200],
    ["id"=>2,"name"=>"Cat 1","price"=>120,"quota"=>600],
    ["id"=>3,"name"=>"Cat 2","price"=>80,"quota"=>1200],
  ]
];

$badge = ($match["status"]==="approved") ? "ok" : (($match["status"]==="pending") ? "pending" : "bad");
$label = ($match["status"]==="approved") ? "Publié" : (($match["status"]==="pending") ? "En attente" : "Refusé");
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BuyMatch — Détails</title>
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
      <a href="home.php"><i class="fa-solid fa-house"></i> Accueil</a>
      <a href="matchs.php"><i class="fa-solid fa-futbol"></i> Matchs</a>
      <a href="../auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
    </nav>
    <div class="actions">
      <button class="iconbtn mobileToggle" data-nav-toggle aria-label="Menu">
        <i class="fa-solid fa-bars"></i>
      </button>
      <a class="btn ghost" href="matchs.php"><i class="fa-solid fa-arrow-left"></i> Retour</a>
    </div>
  </div>
</header>

<main class="container section">
  <div class="panel">
    <div class="sectionHead" style="margin:0 0 12px;">
      <div>
        <h2 class="h2"><?php echo htmlspecialchars($match["team1"]." vs ".$match["team2"]); ?></h2>
        <p class="sub">
          <span class="badge <?php echo $badge; ?>"><i class="fa-solid fa-circle-info"></i> <?php echo $label; ?></span>
        </p>
      </div>
      <div class="tools">
        <a class="btn primary" href="buy_ticket.php?match_id=<?php echo (int)$match["id"]; ?>">
          <i class="fa-solid fa-cart-shopping"></i> Acheter
        </a>
      </div>
    </div>

    <div class="meta" style="margin-bottom:10px;">
      <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($match["city"]); ?></span>
      <span><i class="fa-solid fa-building"></i> <?php echo htmlspecialchars($match["stadium"]); ?></span>
      <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($match["date"]); ?></span>
      <span><i class="fa-solid fa-hourglass-half"></i> <?php echo (int)$match["duration"]; ?> min</span>
    </div>

    <div class="alert">
      <i class="fa-solid fa-lock"></i>
      Achat disponible uniquement après connexion. Si tu n’es pas connecté, tu seras redirigé vers login.
    </div>
  </div>

  <div class="sectionHead" style="margin-top:18px;">
    <div>
      <h2 class="h2">Catégories & prix</h2>
      <p class="sub">Maximum 3 catégories. Exemple d’affichage.</p>
    </div>
  </div>

  <div class="grid">
    <?php foreach($match["categories"] as $c): ?>
      <article class="card">
        <div class="thumb">
          <i class="fa-solid fa-layer-group"></i>
          <span><?php echo htmlspecialchars($c["name"]); ?></span>
        </div>
        <div class="cardBody">
          <div class="meta">
            <span><i class="fa-solid fa-tag"></i> <?php echo (int)$c["price"]; ?> DH</span>
            <span><i class="fa-solid fa-chair"></i> Quota: <?php echo (int)$c["quota"]; ?></span>
          </div>
        </div>
        <div class="cardFoot">
          <a class="btn small" href="buy_ticket.php?match_id=<?php echo (int)$match["id"]; ?>&cat_id=<?php echo (int)$c["id"]; ?>">
            Choisir <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</main>

<footer class="footer">
  <div class="container row">
    <div>© <?php echo date("Y"); ?> BuyMatch</div>
    <div class="sub">Détails match · Catégories · Achat</div>
  </div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
