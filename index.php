<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Accueil</title>
  <link rel="stylesheet" href="assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="index.php">
        <i class="fa-solid fa-ticket"></i><span>BuyMatch</span>
      </a>

      <nav class="navlinks">
        <a class="active" href="index.php"><i class="fa-solid fa-house"></i> Accueil</a>
        <a href="pages/acheteur_dashboard.php"><i class="fa-solid fa-calendar-days"></i> Matchs</a>
        <a href="auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
        <a href="auth/register.php"><i class="fa-solid fa-user-plus"></i> Inscription</a>
      </nav>

      <div class="nav-actions">
        <button class="iconbtn mobile-toggle" onclick="toggleMobileMenu()" aria-label="Menu">
          <i class="fa-solid fa-bars"></i>
        </button>
        <a class="btn btn-ghost" href="auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
        <a class="btn btn-primary" href="auth/register.php"><i class="fa-solid fa-user-plus"></i> Inscription</a>
      </div>
    </div>

    <div class="mobile-menu" id="mobileMenu">
      <a href="index.php"><i class="fa-solid fa-house"></i> Accueil</a>
      <a href="pages/acheteur_dashboard.php"><i class="fa-solid fa-calendar-days"></i> Matchs</a>
      <a href="auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Connexion</a>
      <a href="auth/register.php"><i class="fa-solid fa-user-plus"></i> Inscription</a>
    </div>
  </div>
</header>

<section class="hero">
  <div class="container">
    <div class="hero-grid">
      <div class="hero-card">
        <span class="badge"><i class="fa-solid fa-shield"></i> Réservation simple et rapide</span>
        <h1>Réservez vos billets sportifs en toute simplicité</h1>
        <p>
          BuyMatch permet aux visiteurs de consulter les matchs, aux acheteurs de réserver jusqu’à 4 billets par match,
          aux organisateurs de proposer des événements et à l’administrateur de valider la publication.
        </p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="pages/acheteur_dashboard.php"><i class="fa-solid fa-magnifying-glass"></i> Voir les matchs</a>
          <a class="btn btn-ghost" href="auth/register.php"><i class="fa-solid fa-user-plus"></i> Créer un compte</a>
        </div>
      </div>

      <div class="quick">
        <div class="mini">
          <div class="mini-title">
            <strong>Accès rapide</strong>
            <span class="badge"><i class="fa-solid fa-circle-info"></i> Démo statique</span>
          </div>
          <div class="meta">
            <span><i class="fa-solid fa-user"></i> Visiteur : liste + détails</span>
            <span><i class="fa-solid fa-ticket"></i> Acheteur : achat billet</span>
            <span><i class="fa-solid fa-flag"></i> Organisateur : demande match</span>
            <span><i class="fa-solid fa-user-gear"></i> Admin : validation</span>
          </div>
        </div>

        <div class="mini">
          <div class="mini-title">
            <strong>Prochain match</strong>
            <span class="badge"><i class="fa-solid fa-clock"></i> 20:45</span>
          </div>
          <p style="margin:0; color:var(--muted); line-height:1.7">
            Raja Casablanca vs Wydad AC<br>
            Stade Mohammed V — Casablanca
          </p>
          <div style="margin-top:12px">
            <a class="btn btn-danger" href="match_details.php"><i class="fa-solid fa-circle-chevron-right"></i> Détails</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Matchs à la une</h2>
        <p>Exemples de cartes (données statiques)</p>
      </div>
      <a class="btn btn-ghost" href="matchs.php"><i class="fa-solid fa-list"></i> Voir tout</a>
    </div>

    <div class="grid">
      <article class="card">
        <div class="card-top">
          <h3 class="card-title">Raja Casablanca vs Wydad AC</h3>
          <span class="badge"><i class="fa-solid fa-circle"></i> En attente</span>
        </div>
        <div class="meta">
          <span><i class="fa-solid fa-location-dot"></i> Casablanca</span>
          <span><i class="fa-solid fa-calendar-day"></i> 10/01/2026</span>
          <span><i class="fa-solid fa-clock"></i> 20:45</span>
        </div>
        <div class="card-actions">
          <a class="btn btn-ghost" href="match_details.php"><i class="fa-solid fa-eye"></i> Détails</a>
          <a class="btn btn-primary" href="buy_ticket.php"><i class="fa-solid fa-ticket"></i> Acheter</a>
        </div>
      </article>

      <article class="card">
        <div class="card-top">
          <h3 class="card-title">PSG vs OM</h3>
          <span class="badge"><i class="fa-solid fa-circle-check"></i> Publié</span>
        </div>
        <div class="meta">
          <span><i class="fa-solid fa-location-dot"></i> Paris</span>
          <span><i class="fa-solid fa-calendar-day"></i> 12/01/2026</span>
          <span><i class="fa-solid fa-clock"></i> 21:00</span>
        </div>
        <div class="card-actions">
          <a class="btn btn-ghost" href="match_details.php"><i class="fa-solid fa-eye"></i> Détails</a>
          <a class="btn btn-primary" href="buy_ticket.php"><i class="fa-solid fa-ticket"></i> Acheter</a>
        </div>
      </article>

      <article class="card">
        <div class="card-top">
          <h3 class="card-title">FC Barcelone vs Real Madrid</h3>
          <span class="badge"><i class="fa-solid fa-circle-check"></i> Publié</span>
        </div>
        <div class="meta">
          <span><i class="fa-solid fa-location-dot"></i> Barcelone</span>
          <span><i class="fa-solid fa-calendar-day"></i> 15/01/2026</span>
          <span><i class="fa-solid fa-clock"></i> 19:30</span>
        </div>
        <div class="card-actions">
          <a class="btn btn-ghost" href="match_details.php"><i class="fa-solid fa-eye"></i> Détails</a>
          <a class="btn btn-primary" href="buy_ticket.php"><i class="fa-solid fa-ticket"></i> Acheter</a>
        </div>
      </article>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap;">
    <div>© 2026 BuyMatch</div>
    <div style="display:flex; gap:14px;">
      <a href="pages/acheteur_dashboard.php">Matchs</a>
      <a href="auth/login.php">Connexion</a>
      <a href="auth/register.php">Inscription</a>
    </div>
  </div>
</footer>

<script src="assets/script.js"></script>
</body>
</html>
