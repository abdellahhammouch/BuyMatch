<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Organisateur - Créer match</title>
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="../home.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
      <nav class="navlinks">
        <a class="active" href="create_match.php"><i class="fa-solid fa-plus"></i> Demande match</a>
        <a href="stats.php"><i class="fa-solid fa-chart-column"></i> Statistiques</a>
        <a href="../home.php"><i class="fa-solid fa-house"></i> Site</a>
      </nav>
      <div class="nav-actions">
        <button class="iconbtn mobile-toggle" onclick="toggleMobileMenu()"><i class="fa-solid fa-bars"></i></button>
        <a class="btn btn-danger" href="../login.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion</a>
      </div>
    </div>
    <div class="mobile-menu" id="mobileMenu">
      <a href="create_match.php">Demande match</a>
      <a href="stats.php">Statistiques</a>
      <a href="../home.php">Site</a>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Créer une demande de match</h2>
        <p>Le match sera validé par l’administrateur avant publication</p>
      </div>
    </div>

    <div class="form">
      <div class="form-row">
        <div class="field">
          <label>Équipe 1</label>
          <input class="input" placeholder="Nom équipe 1" />
        </div>
        <div class="field">
          <label>Équipe 2</label>
          <input class="input" placeholder="Nom équipe 2" />
        </div>
      </div>

      <div class="form-row" style="margin-top:12px;">
        <div class="field">
          <label>Date</label>
          <input class="input" type="date" />
        </div>
        <div class="field">
          <label>Heure</label>
          <input class="input" type="time" />
        </div>
      </div>

      <div class="form-row" style="margin-top:12px;">
        <div class="field">
          <label>Lieu</label>
          <input class="input" placeholder="Stade + ville" />
        </div>
        <div class="field">
          <label>Places (max 2000)</label>
          <input class="input" type="number" min="1" max="2000" value="2000" />
        </div>
      </div>

      <div class="form-row" style="margin-top:12px;">
        <div class="field">
          <label>Catégorie 1 (prix)</label>
          <input class="input" placeholder="Ex: VIP - 250" />
        </div>
        <div class="field">
          <label>Catégorie 2 (prix)</label>
          <input class="input" placeholder="Ex: Standard - 120" />
        </div>
      </div>

      <div class="field" style="margin-top:12px;">
        <label>Catégorie 3 (prix)</label>
        <input class="input" placeholder="Ex: Économie - 60" />
      </div>

      <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn btn-primary" type="button"><i class="fa-solid fa-paper-plane"></i> Envoyer la demande</button>
        <a class="btn btn-ghost" href="../home.php"><i class="fa-solid fa-arrow-left"></i> Retour site</a>
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
