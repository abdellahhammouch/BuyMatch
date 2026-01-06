<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../config/database.php";

// sécurité: seul acheteur
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "acheteur") {
    header("Location: ../auth/login.php");
    exit;
}

$pdo = Database::getInstance();
$userId = (int) $_SESSION["user_id"];

/* 1) Récupérer infos acheteur */
$stmt = $pdo->prepare("SELECT id_user, nom_user, prenom_user, email_user, phone_user, photo_user, role_user
                       FROM users
                       WHERE id_user = ?
                       LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}

/* 2) KPIs */
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE acheteur_id = ?");
$stmtCount->execute([$userId]);
$totalTickets = (int) $stmtCount->fetchColumn();

$stmtSum = $pdo->prepare("SELECT COALESCE(SUM(prix_ticket), 0) FROM tickets WHERE acheteur_id = ?");
$stmtSum->execute([$userId]);
$totalSpent = (float) $stmtSum->fetchColumn();

$stmtTickets = $pdo->prepare("SELECT t.id_ticket, t.place_numero, t.prix_ticket, t.code_ticket,
                                    m.equipe1_nom, m.equipe2_nom, m.date_match, m.heure_match, m.lieu_match,
                                    c.nom_categorie
                              FROM tickets t
                              JOIN matchs m ON m.id_match = t.match_id
                              JOIN categories c ON c.id_categorie = t.categorie_id
                              WHERE t.acheteur_id = ?
                              ORDER BY t.id_ticket DESC
                              LIMIT 8");
$stmtTickets->execute([$userId]);
$recentTickets = $stmtTickets->fetchAll();

$photoUrl = !empty($user["photo_user"]) ? "../" . ltrim($user["photo_user"], "/") : "";
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BuyMatch | Dashboard Acheteur</title>
  <link rel="stylesheet" href="../assets/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="acheteur_dashboard.php">
        <i class="fa-solid fa-ticket"></i><span>BuyMatch</span>
      </a>

      <div class="nav-actions">
        <a class="btn btn-ghost" href="profile.php">
          <i class="fa-solid fa-user"></i> Mon profil
        </a>
        <a class="btn btn-danger" href="../auth/logout.php">
          <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
        </a>
      </div>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">

    <div class="hero" style="padding-top: 10px;">
      <div class="hero-grid">

        <div class="hero-card">
          <h1 style="margin-bottom:12px;">Dashboard Acheteur</h1>
          <p>Bienvenue <strong><?= $user["prenom_user"] ?></strong>. Ici tu peux voir tes billets et tes infos.</p>

          <div style="display:flex; gap:16px; align-items:center; margin-top:18px;">
            <?php if ($photoUrl): ?>
              <img src="<?= $photoUrl ?>" alt="Photo" style="width:78px;height:78px;border-radius:999px;object-fit:cover;border:1px solid rgba(255,255,255,.12);">
            <?php else: ?>
              <div style="width:78px;height:78px;border-radius:999px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);">
                <i class="fa-solid fa-user" style="font-size:26px;opacity:.7;"></i>
              </div>
            <?php endif; ?>

            <div>
              <div style="font-weight:900; font-size:18px;">
                <?= $user["prenom_user"] . " " . $user["nom_user"] ?>
              </div>
              <div style="color: var(--muted); margin-top:4px;">
                <i class="fa-solid fa-envelope"></i>
                <?= $user["email_user"] ?>
              </div>
            </div>
          </div>

          <div class="hero-actions" style="margin-top:18px;">
            <a class="btn btn-primary" href="../index.php">
              <i class="fa-solid fa-magnifying-glass"></i> Voir les matchs
            </a>
            <a class="btn btn-ghost" href="profile.php">
              <i class="fa-solid fa-pen"></i> Modifier profil
            </a>
          </div>
        </div>

        <div class="quick">
          <div class="mini">
            <div class="mini-title">
              <strong>Mes statistiques</strong>
              <span class="badge"><i class="fa-solid fa-chart-column"></i> Acheteur</span>
            </div>

            <div class="kpi" style="grid-template-columns:1fr 1fr;">
              <div class="k">
                <div class="label">BILLETS</div>
                <div class="value"><?= $totalTickets ?></div>
              </div>
              <div class="k">
                <div class="label">TOTAL DÉPENSÉ</div>
                <div class="value"><?= number_format($totalSpent, 2) ?> DH</div>
              </div>
            </div>
          </div>

          <div class="mini">
            <div class="mini-title">
              <strong>Accès rapide</strong>
            </div>
            <div class="card-actions">
              <a class="btn btn-ghost" href="../index.php"><i class="fa-solid fa-house"></i> Accueil</a>
              <a class="btn btn-ghost" href="profile.php"><i class="fa-solid fa-user"></i> Profil</a>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="section-head" style="margin-top:6px;">
      <div>
        <h2>Mes billets récents</h2>
        <p>Derniers tickets achetés</p>
      </div>
    </div>

    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Match</th>
            <th>Date</th>
            <th>Lieu</th>
            <th>Catégorie</th>
            <th>Place</th>
            <th>Prix</th>
            <th>Code</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($recentTickets) === 0): ?>
            <tr>
              <td colspan="7" style="color:var(--muted); padding:16px;">
                Aucun billet pour le moment.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentTickets as $t): ?>
              <tr>
                <td><strong><?= $t["equipe1_nom"] . " vs " . $t["equipe2_nom"] ?></strong></td>
                <td><?= $t["date_match"] . " " . substr($t["heure_match"], 0, 5) ?></td>
                <td><?= $t["lieu_match"] ?></td>
                <td><?= $t["nom_categorie"] ?></td>
                <td>#<?= (int)$t["place_numero"] ?></td>
                <td><?= number_format((float)$t["prix_ticket"], 2) ?> DH</td>
                <td><?= $t["code_ticket"] ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

</body>
</html>
