<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Admin - Dashboard</title>
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="../home.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
      <nav class="navlinks">
        <a class="active" href="dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        <a href="validate_match.php"><i class="fa-solid fa-check"></i> Validation</a>
        <a href="../home.php"><i class="fa-solid fa-house"></i> Site</a>
      </nav>
      <div class="nav-actions">
        <button class="iconbtn mobile-toggle" onclick="toggleMobileMenu()"><i class="fa-solid fa-bars"></i></button>
        <a class="btn btn-danger" href="../login.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion</a>
      </div>
    </div>
    <div class="mobile-menu" id="mobileMenu">
      <a href="dashboard.php">Dashboard</a>
      <a href="validate_match.php">Validation</a>
      <a href="../home.php">Site</a>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <h2>Administration</h2>
        <p>Statistiques globales (statique)</p>
      </div>
    </div>

    <div class="kpi" style="margin-bottom:14px;">
      <div class="k"><div class="label">Utilisateurs</div><div class="value"><i class="fa-solid fa-users"></i> 120</div></div>
      <div class="k"><div class="label">Matchs publiés</div><div class="value"><i class="fa-solid fa-calendar-check"></i> 18</div></div>
      <div class="k"><div class="label">Billets vendus</div><div class="value"><i class="fa-solid fa-ticket"></i> 940</div></div>
      <div class="k"><div class="label">Chiffre d’affaires</div><div class="value"><i class="fa-solid fa-coins"></i> 132k</div></div>
    </div>

    <div class="card">
      <div class="card-top">
        <h3 class="card-title">Utilisateurs</h3>
        <span class="badge"><i class="fa-solid fa-user-gear"></i> Activer / Désactiver</span>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th>Nom</th><th>Rôle</th><th>Email</th><th>Statut</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Amine El Idrissi</td><td>Organisateur</td><td>amine@exemple.com</td>
            <td><span class="badge"><i class="fa-solid fa-circle-check"></i> Actif</span></td>
            <td><button class="btn btn-danger" type="button"><i class="fa-solid fa-ban"></i> Désactiver</button></td>
          </tr>
          <tr>
            <td>Salma B.</td><td>Acheteur</td><td>salma@exemple.com</td>
            <td><span class="badge"><i class="fa-solid fa-circle-xmark"></i> Inactif</span></td>
            <td><button class="btn btn-primary" type="button"><i class="fa-solid fa-check"></i> Activer</button></td>
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
