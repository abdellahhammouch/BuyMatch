<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "organisateur") {
    header("Location: ../auth/login.php");
    exit;
}

$pdo = Database::getInstance();
$orgId = (int) $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT id_user, nom_user, prenom_user, email_user, phone_user, photo_user
                       FROM users
                       WHERE id_user = ? AND role_user = 'organisateur'
                       LIMIT 1");
$stmt->execute([$orgId]);
$org = $stmt->fetch();




$stmtUser = $pdo->prepare("SELECT nom_user, prenom_user, email_user, photo_user 
                           FROM users 
                           WHERE id_user = ? 
                           LIMIT 1");
$stmtUser->execute([$orgId]);
$user = $stmtUser->fetch();

$userNom = $user ? $user["nom_user"] : "";
$userPrenom = $user ? $user["prenom_user"] : "";
$userEmail = $user ? $user["email_user"] : "";
$userPhoto = $user ? $user["photo_user"] : "";

$q = trim($_GET["q"] ?? "");
$statut = trim($_GET["statut"] ?? "");

$sql = "SELECT m.*,COUNT(t.id_ticket) AS billets_vendus,
            COALESCE(SUM(t.prix_ticket), 0) AS chiffre_affaires
        FROM matchs m
        LEFT JOIN tickets t ON t.match_id = m.id_match
        WHERE m.organisateur_id = ?";

$params = [$orgId];

if ($statut !== "" && in_array($statut, ["publie","en_attente","refuse"])) {
    $sql .= " AND m.statut_match = ? ";
    $params[] = $statut;
}

if ($q !== "") {
    $sql .= " AND (
        m.equipe1_nom LIKE ? OR 
        m.equipe2_nom LIKE ? OR
        m.lieu_match LIKE ?
    ) ";
    $params[] = "%".$q."%";
    $params[] = "%".$q."%";
    $params[] = "%".$q."%";
}

$sql .= " GROUP BY m.id_match
          ORDER BY m.date_match DESC, m.heure_match DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matchs = $stmt->fetchAll();

$stmtStats = $pdo->prepare("SELECT COUNT(*) AS total,
                                SUM(CASE WHEN statut_match='publie' THEN 1 ELSE 0 END) AS publies,
                                SUM(CASE WHEN statut_match='en_attente' THEN 1 ELSE 0 END) AS en_attente,
                                SUM(CASE WHEN statut_match='refuse' THEN 1 ELSE 0 END) AS refuses
                            FROM matchs
                            WHERE organisateur_id = ?");
$stmtStats->execute([$orgId]);
$stats = $stmtStats->fetch();

$totalMatchs  = (int)($stats["total"] ?? 0);
$publies      = (int)($stats["publies"] ?? 0);
$enAttente    = (int)($stats["en_attente"] ?? 0);
$refuses      = (int)($stats["refuses"] ?? 0);

function badgeClass($statut) {
    if ($statut === "publie") return "success";
    if ($statut === "en_attente") return "pending";
    return "danger";
}
function badgeText($statut) {
    if ($statut === "publie") return "Publié";
    if ($statut === "en_attente") return "En attente";
    return "Refusé";
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BuyMatch | Mes Matchs</title>
  <link rel="stylesheet" href="../assets/style.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="../index.php">
        <i class="fa-solid fa-ticket"></i>
        <span>BuyMatch</span>
      </a>

      <div class="navlinks">
        <a href="organisateur_dashboard.php">
          <i class="fa-solid fa-gauge"></i> Dashboard
        </a>

        <a href="create_match.php">
          <i class="fa-solid fa-circle-plus"></i> Créer un match
        </a>

        <a href="mes-matchs.php" class="active">
          <i class="fa-solid fa-futbol"></i> Mes matchs
        </a>

        <a href="stats.php">
          <i class="fa-solid fa-chart-column"></i> Statistiques
        </a>
      </div>

      <div class="nav-actions" style="display:flex; align-items:center; gap:10px;">

        <!-- Profil -->
        <a href="../pages/profile.php" class="iconbtn" title="Mon Profil" style="padding:0; overflow:hidden;">
          <?php if (!empty($org["photo_user"])): ?>
            <img
              src="../<?= $org["photo_user"] ?>"
              alt="Profil"
              style="width:42px; height:42px; object-fit:cover; border-radius:12px;"
            >
          <?php else: ?>
            <i class="fa-solid fa-user" style="font-size:14px; color:rgba(255,255,255,.8)"></i>
          <?php endif; ?>
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

    <div class="section-header">
      <div>
        <h2><i class="fa-solid fa-calendar-check"></i> Mes Matchs</h2>
        <p class="muted">Gérez tous vos événements sportifs</p>
      </div>
      <a href="create_match.php" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Nouveau Match
      </a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Total Matchs</div>
        <div class="stat-value"><?= $totalMatchs ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Publiés</div>
        <div class="stat-value"><?= $publies ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">En attente</div>
        <div class="stat-value"><?= $enAttente ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Refusés</div>
        <div class="stat-value"><?= $refuses ?></div>
      </div>
    </div>

    <!-- Filtres -->
    <form class="toolbar" method="GET" action="mes-matchs.php">
      <div class="toolbar-search">
        <i class="fa-solid fa-search"></i>
        <input class="input" type="text" name="q" placeholder="Rechercher un match..." value="<?= $q ?>">
      </div>

      <select class="select" name="statut">
        <option value="">Tous les statuts</option>
        <option value="publie" <?= $statut==="publie" ? "selected" : "" ?>>Publiés</option>
        <option value="en_attente" <?= $statut==="en_attente" ? "selected" : "" ?>>En attente</option>
        <option value="refuse" <?= $statut==="refuse" ? "selected" : "" ?>>Refusés</option>
      </select>

      <button class="btn btn-ghost" type="submit">
        <i class="fa-solid fa-filter"></i> Appliquer
      </button>
    </form>

    <!-- Liste -->
    <div class="cards-grid">
      <?php if (!$matchs || count($matchs) === 0): ?>
        <div class="empty-card">
          <p>Aucun match trouvé.</p>
          <a class="btn btn-primary" href="create_match.php"><i class="fa-solid fa-plus"></i> Créer un match</a>
        </div>
      <?php else: ?>
        <?php foreach ($matchs as $m): ?>
          <?php
            $matchTitle = $m["equipe1_nom"] . " vs " . $m["equipe2_nom"];
            $sold = (int)$m["billets_vendus"];
            $totalPlaces = (int)$m["total_places"];
            $stat = $m["statut_match"];
            $revenue = $m["chiffre_affaires"];

            $stmtCat = $pdo->prepare("SELECT nom_categorie, prix_categorie, places_max 
                                      FROM categories 
                                      WHERE match_id = ?
                                      ORDER BY id_categorie ASC");
            $stmtCat->execute([(int)$m["id_match"]]);
            $cats = $stmtCat->fetchAll();
          ?>

          <div class="match-card">
            <div class="match-card-top">
              <div class="match-title">
                <h3><?= $matchTitle ?></h3>
                <p class="muted"><?= $m["lieu_match"] ?></p>
              </div>

              <span class="badge <?= badgeClass($stat) ?>">
                <?= badgeText($stat) ?>
              </span>
            </div>

            <div class="match-meta">
              <span><i class="fa-solid fa-calendar-day"></i> <?= $m["date_match"] ?></span>
              <span><i class="fa-solid fa-clock"></i> <?= substr($m["heure_match"], 0, 5) ?></span>
              <span><i class="fa-solid fa-users"></i> <?= $sold ?> / <?= $totalPlaces ?> vendues</span>
              <span><i class="fa-solid fa-coins"></i> <?= number_format((float)$revenue, 2) ?> DH</span>
            </div>

            <?php if ($cats && count($cats) > 0): ?>
              <div class="cats-grid">
                <?php foreach ($cats as $c): ?>
                  <div class="cat-box">
                    <div class="cat-name"><?= $c["nom_categorie"] ?></div>
                    <div class="cat-info"><?= number_format((float)$c["prix_categorie"], 2) ?> DH</div>
                    <div class="cat-info"><?= (int)$c["places_max"] ?> places</div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <div class="match-actions">
              <button type="button" class="btn btn-ghost" data-open-modal="matchModal-<?= $m['id_match'] ?>">
                <i class="fa-solid fa-circle-info"></i> Détails
              </button>
            </div>
          </div>
          <div class="bm-modal" id="matchModal-<?= $m['id_match'] ?>" style="display:none;">
          <div class="bm-modal-backdrop" data-close-modal="matchModal-<?= $m['id_match'] ?>"></div>

          <div class="bm-modal-card" role="dialog" aria-modal="true">
            <div class="bm-modal-head">
              <div style="font-weight:900;">
                <i class="fa-solid fa-circle-info"></i> Détails du match
              </div>

              <button type="button" class="bm-modal-close" data-close-modal="matchModal-<?= $m['id_match'] ?>">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <div class="bm-modal-body">
              <h3 style="margin:0 0 10px;">
                <?= $m["equipe1_nom"] ?> vs <?= $m["equipe2_nom"] ?>
              </h3>

              <div class="meta" style="margin-bottom:12px;">
                <span><i class="fa-solid fa-calendar-day"></i> <?= $m["date_match"] ?></span>
                <span><i class="fa-solid fa-clock"></i> <?= substr($m["heure_match"], 0, 5) ?></span>
                <span><i class="fa-solid fa-location-dot"></i> <?= $m["lieu_match"] ?></span>
              </div>

              <div class="card" style="padding:12px; background:rgba(255,255,255,.03);">
                <strong>Statut</strong>
                <p style="margin:8px 0 0; color:var(--muted);"><?= $m["statut_match"] ?></p>
              </div>

              <div class="card" style="margin-top:12px; padding:12px; background:rgba(255,255,255,.03);">
                <strong>Billets & Chiffre d'affaires</strong>
                <div class="meta" style="margin-top:8px;">
                  <span><i class="fa-solid fa-ticket"></i> Billets vendus: <?= $m["billets_vendus"] ?? 0 ?></span>
                  <span><i class="fa-solid fa-coins"></i> CA: <?= $m["chiffre_affaires"] ?? 0 ?> DH</span>
                </div>
              </div>

              <div style="margin-top:12px;">
                <button type="button" class="btn btn-ghost" data-close-modal="matchModal-<?= $m['id_match'] ?>">
                  <i class="fa-solid fa-xmark"></i> Fermer
                </button>
              </div>
            </div>
          </div>
        </div>


        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
