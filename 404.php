<?php http_response_code(404); ?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | 404</title>
  <link rel="stylesheet" href="assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="/pages/acheteur_dashboard.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
      <div class="nav-actions">
        <a class="btn btn-ghost" href="/pages/acheteur_dashboard.php"><i class="fa-solid fa-house"></i> Accueil</a>
      </div>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="hero-card" style="text-align:center;">
      <div style="font-size:56px; font-weight:900; letter-spacing:1px;">404</div>
      <h2 style="margin:8px 0 8px;">Page introuvable</h2>
      <p style="margin:0; color:var(--muted); line-height:1.8;">
        La page que vous cherchez n’existe pas ou a été déplacée.
      </p>
      <div style="margin-top:14px; display:flex; justify-content:center; gap:10px; flex-wrap:wrap;">
        <a class="btn btn-primary" href="/pages/acheteur_dashboard.php"><i class="fa-solid fa-house"></i> Retour accueil</a>
        <a class="btn btn-ghost" href="/pages/matchs.php"><i class="fa-solid fa-calendar-days"></i> Voir les matchs</a>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

<script src="assets/script.js"></script>
</body>
</html>
