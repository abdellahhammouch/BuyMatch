<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Admin - Validation</title>
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="../home.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>
      <nav class="navlinks">
        <a href="dashboard.php">Dashboard</a>
        <a class="active" href="validate_match.php">Validation</a>
        <a href="../home.php">Site</a>
      </nav>
      <div class="nav-actions">
        <button class="iconbtn mobile-toggle" onclick="toggleMobileMenu()"><i class="fa-solid fa-bars"></i></button>
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
        <h2>Validation des demandes</h2>
        <p>Accepter ou refuser les matchs avant publication</p>
      </div>
    </div>

    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Match</th><th>Date</th><th>Lieu</th><th>Organisateur</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Raja vs Wydad</td><td>10/01/2026 20:45</td><td>Casablanca</td><td>Amine El Idrissi</td>
            <td style="display:flex; gap:10px; flex-wrap:wrap; border-bottom:none;">
              <button class="btn btn-primary" type="button"><i class="fa-solid fa-check"></i> Accepter</button>
              <button class="btn btn-danger" type="button"><i class="fa-solid fa-xmark"></i> Refuser</button>
            </td>
          </tr>
          <tr>
            <td>PSG vs OM</td><td>12/01/2026 21:00</td><td>Paris</td><td>Salma O.</td>
            <td style="display:flex; gap:10px; flex-wrap:wrap; border-bottom:none;">
              <button class="btn btn-primary" type="button"><i class="fa-solid fa-check"></i> Accepter</button>
              <button class="btn btn-danger" type="button"><i class="fa-solid fa-xmark"></i> Refuser</button>
            </td>
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
