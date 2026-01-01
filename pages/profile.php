<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Mon profil</title>
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="home.phpl"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
      <nav class="navlinks">
        <a href="matchs.php">Matchs</a>
        <a class="active" href="profile.php">Mon espace</a>
      </nav>
      <div class="nav-actions">
        <button class="iconbtn mobile-toggle" onclick="toggleMobileMenu()"><i class="fa-solid fa-bars"></i></button>
        <a class="btn btn-danger" href="login.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion</a>
      </div>
    </div>
    <div class="mobile-menu" id="mobileMenu">
      <a href="matchs.php">Matchs</a>
      <a href="profile.php">Mon espace</a>
      <a href="login.php">Déconnexion</a>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Mon profil</h2>
        <p>Informations et historique (statique)</p>
      </div>
    </div>

    <div class="kpi" style="margin-bottom:14px;">
      <div class="k">
        <div class="label">Billets achetés</div>
        <div class="value"><i class="fa-solid fa-ticket"></i> 6</div>
      </div>
      <div class="k">
        <div class="label">Dernier achat</div>
        <div class="value"><i class="fa-solid fa-calendar-day"></i> 10/01</div>
      </div>
      <div class="k">
        <div class="label">Statut</div>
        <div class="value"><i class="fa-solid fa-circle-check"></i> Actif</div>
      </div>
      <div class="k">
        <div class="label">PDF</div>
        <div class="value"><i class="fa-solid fa-file-pdf"></i> Disponible</div>
      </div>
    </div>

    <div class="grid">
      <div class="card">
        <div class="card-top">
          <h3 class="card-title">Mes informations</h3>
          <span class="badge"><i class="fa-solid fa-user"></i> Acheteur</span>
        </div>
        <div class="meta" style="flex-direction:column; align-items:flex-start;">
          <span><i class="fa-solid fa-id-card"></i> Nom : Ben Ali</span>
          <span><i class="fa-solid fa-at"></i> Email : benali@example.com</span>
          <span><i class="fa-solid fa-phone"></i> Téléphone : +212 6XX-XXXXXX</span>
        </div>
      </div>

      <div class="card">
        <div class="card-top">
          <h3 class="card-title">Téléchargements</h3>
          <span class="badge"><i class="fa-solid fa-download"></i> PDF</span>
        </div>
        <p style="margin:0; color:var(--muted); line-height:1.8">
          Plus tard : bouton pour télécharger un PDF récapitulatif des billets achetés.
        </p>
        <div class="card-actions">
          <button class="btn btn-primary" type="button"><i class="fa-solid fa-file-pdf"></i> Télécharger PDF</button>
        </div>
      </div>

      <div class="card">
        <div class="card-top">
          <h3 class="card-title">Laisser un avis</h3>
          <span class="badge"><i class="fa-solid fa-star"></i> Après match</span>
        </div>
        <div class="field">
          <label>Commentaire</label>
          <textarea placeholder="Votre avis..." style="width:100%;"></textarea>
        </div>
        <div style="margin-top:10px; display:flex; gap:10px;">
          <button class="btn btn-ghost" type="button"><i class="fa-solid fa-star"></i> Noter 1-5</button>
          <button class="btn btn-primary" type="button"><i class="fa-solid fa-paper-plane"></i> Envoyer</button>
        </div>
      </div>
    </div>

    <div style="margin-top:14px;" class="card">
      <div class="card-top">
        <h3 class="card-title">Historique des billets</h3>
        <a class="btn btn-ghost" href="matchs.php"><i class="fa-solid fa-plus"></i> Acheter</a>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th>Match</th>
            <th>Date</th>
            <th>Catégorie</th>
            <th>Place</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Raja vs Wydad</td>
            <td>10/01/2026</td>
            <td>Standard</td>
            <td>A-12</td>
            <td><span class="badge"><i class="fa-solid fa-circle-check"></i> Envoyé</span></td>
          </tr>
          <tr>
            <td>PSG vs OM</td>
            <td>12/01/2026</td>
            <td>VIP</td>
            <td>V-02</td>
            <td><span class="badge"><i class="fa-solid fa-circle-check"></i> Envoyé</span></td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
