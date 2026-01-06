<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "organisateur") {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

$pdo = Database::getInstance();
$organisateurId = (int)$_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT id_user, nom_user, prenom_user, email_user, phone_user, photo_user
                       FROM users
                       WHERE id_user = ? AND role_user = 'organisateur'
                       LIMIT 1");
$stmt->execute([$organisateurId]);
$org = $stmt->fetch();

if (!$org) {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM matchs WHERE organisateur_id = ?");
$stmtTotal->execute([$organisateurId]);
$totalMatchs = (int)$stmtTotal->fetchColumn();

$stmtPending = $pdo->prepare("SELECT COUNT(*) FROM matchs WHERE organisateur_id = ? AND statut_match = 'en_attente'");
$stmtPending->execute([$organisateurId]);
$pendingMatchs = (int)$stmtPending->fetchColumn();

$stmtPub = $pdo->prepare("SELECT COUNT(*) FROM matchs WHERE organisateur_id = ? AND statut_match = 'publie'");
$stmtPub->execute([$organisateurId]);
$publishedMatchs = (int)$stmtPub->fetchColumn();

$stmtRef = $pdo->prepare("SELECT COUNT(*) FROM matchs WHERE organisateur_id = ? AND statut_match = 'refuse'");
$stmtRef->execute([$organisateurId]);
$refusedMatchs = (int)$stmtRef->fetchColumn();

$stmtSales = $pdo->prepare("SELECT COUNT(t.id_ticket) AS billets_vendus,
                                  COALESCE(SUM(t.prix_ticket), 0) AS chiffre_affaires
                            FROM matchs m
                            LEFT JOIN tickets t ON t.match_id = m.id_match
                            WHERE m.organisateur_id = ?");
$stmtSales->execute([$organisateurId]);
$sales = $stmtSales->fetch();

$billetsVendus = (int)($sales["billets_vendus"] ?? 0);
$chiffreAffaires = (float)($sales["chiffre_affaires"] ?? 0);

$stmtRating = $pdo->prepare("SELECT COALESCE(AVG(c.note), 0) AS note_moy,
                                    COUNT(c.id_comment) AS nb_avis
                              FROM comments c
                              JOIN matchs m ON m.id_match = c.match_id
                              WHERE m.organisateur_id = ? AND c.note IS NOT NULL");
$stmtRating->execute([$organisateurId]);
$rating = $stmtRating->fetch();

$noteMoy = (float)($rating["note_moy"] ?? 0);
$nbAvis  = (int)($rating["nb_avis"] ?? 0);

$stmtPlaces = $pdo->prepare("SELECT COALESCE(SUM(total_places),0) FROM matchs WHERE organisateur_id = ?");
$stmtPlaces->execute([$organisateurId]);
$totalPlaces = (int)$stmtPlaces->fetchColumn();

$tauxRemplissage = 0;
if ($totalPlaces > 0) {
    $tauxRemplissage = ($billetsVendus / $totalPlaces) * 100;
}

$stmtCats = $pdo->prepare("SELECT c.id_categorie, c.nom_categorie, c.places_max,
                                  COUNT(t.id_ticket) AS billets_vendus,
                                  COALESCE(SUM(t.prix_ticket), 0) AS revenus
                            FROM categories c
                            JOIN matchs m ON m.id_match = c.match_id
                            LEFT JOIN tickets t ON t.categorie_id = c.id_categorie
                            WHERE m.organisateur_id = ?
                            GROUP BY c.id_categorie, c.nom_categorie, c.places_max
                            ORDER BY revenus DESC");
$stmtCats->execute([$organisateurId]);
$categoriesPerf = $stmtCats->fetchAll();

$stmtTop = $pdo->prepare("SELECT m.id_match, m.equipe1_nom, m.equipe2_nom, m.date_match, m.heure_match,
                                m.total_places,
                                COUNT(t.id_ticket) AS billets_vendus,
                                COALESCE(SUM(t.prix_ticket), 0) AS revenus,
                                COALESCE(AVG(c.note), 0) AS note_moy
                          FROM matchs m
                          LEFT JOIN tickets t ON t.match_id = m.id_match
                          LEFT JOIN comments c ON c.match_id = m.id_match AND c.note IS NOT NULL
                          WHERE m.organisateur_id = ?
                          GROUP BY m.id_match, m.equipe1_nom, m.equipe2_nom, m.date_match, m.heure_match, m.total_places
                          ORDER BY revenus DESC
                          LIMIT 5");
$stmtTop->execute([$organisateurId]);
$topMatchs = $stmtTop->fetchAll();

$stmtAll = $pdo->prepare("SELECT m.id_match, m.equipe1_nom, m.equipe2_nom, m.date_match, m.heure_match, m.lieu_match,
                                m.total_places, m.statut_match,
                                COUNT(t.id_ticket) AS billets_vendus,
                                COALESCE(SUM(t.prix_ticket), 0) AS revenus,
                                COALESCE(AVG(c.note), 0) AS note_moy
                          FROM matchs m
                          LEFT JOIN tickets t ON t.match_id = m.id_match
                          LEFT JOIN comments c ON c.match_id = m.id_match AND c.note IS NOT NULL
                          WHERE m.organisateur_id = ?
                          GROUP BY m.id_match, m.equipe1_nom, m.equipe2_nom, m.date_match, m.heure_match, m.lieu_match,
                                  m.total_places, m.statut_match
                          ORDER BY m.date_match DESC, m.heure_match DESC");
$stmtAll->execute([$organisateurId]);
$allMatchs = $stmtAll->fetchAll();

function statutLabel($statut) {
    if ($statut === "publie") return "Publié";
    if ($statut === "refuse") return "Refusé";
    return "En attente";
}

function statutBadgeClass($statut) {
    if ($statut === "publie") return "success";
    if ($statut === "refuse") return "danger";
    return "pending";
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BuyMatch | Organisateur - Statistiques</title>
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

        <a href="mes-matchs.php">
          <i class="fa-solid fa-futbol"></i> Mes matchs
        </a>

        <a href="stats.php" class="active">
          <i class="fa-solid fa-chart-column"></i> Statistiques
        </a>
      </div>

      <div class="nav-actions" style="display:flex; align-items:center; gap:10px;">
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

    <div class="section-head">
      <div>
        <h2>Statistiques</h2>
        <p style="color:var(--muted); margin-top:6px;">
          Bienvenue, <?= $org["prenom_user"] . " " . $org["nom_user"] ?>
        </p>
      </div>
    </div>

    <!-- KPIs -->
    <div class="kpi" style="margin-bottom:14px;">
      <div class="k">
        <div class="label">Total matchs</div>
        <div class="value"><i class="fa-solid fa-futbol"></i><?= $totalMatchs ?></div>
      </div>

      <div class="k">
        <div class="label">Billets vendus</div>
        <div class="value"><i class="fa-solid fa-ticket"></i><?= $billetsVendus ?></div>
      </div>

      <div class="k">
        <div class="label">Chiffre d'affaires</div>
        <div class="value"><i class="fa-solid fa-coins"></i><?= $chiffreAffaires ?> DH</div>
      </div>

      <div class="k">
        <div class="label">Note moyenne</div>
        <div class="value"><i class="fa-solid fa-star"></i><?= number_format($noteMoy, 1) ?>/5</div>
      </div>
    </div>

    <div class="card" style="margin-bottom:14px;">
      <div class="meta">
        <span><i class="fa-solid fa-circle-check"></i> Publiés: <?= $publishedMatchs ?></span>
        <span><i class="fa-solid fa-clock"></i> En attente: <?= $pendingMatchs ?></span>
        <span><i class="fa-solid fa-circle-xmark"></i> Refusés: <?= $refusedMatchs ?></span>
        <span><i class="fa-solid fa-chart-simple"></i> Remplissage global: <?= number_format($tauxRemplissage, 1) ?>%</span>
        <span><i class="fa-solid fa-message"></i> Avis: <?= $nbAvis ?></span>
      </div>
    </div>

    <!-- Performance par catégorie -->
    <div class="section-head" style="margin-top:10px;">
      <div>
        <h2>Performance par catégorie</h2>
        <p style="color:var(--muted);">Billets vendus et revenus</p>
      </div>
    </div>

    <div class="grid">
      <?php if (count($categoriesPerf) === 0): ?>
        <div class="card" style="color:var(--muted);">Aucune catégorie trouvée.</div>
      <?php else: ?>
        <?php foreach ($categoriesPerf as $cat): ?>
          <?php
            $max = (int)$cat["places_max"];
            $sold = (int)$cat["billets_vendus"];
            $rev  = (float)$cat["revenus"];
            $pct = 0;
            if ($max > 0) $pct = ($sold / $max) * 100;
            if ($pct > 100) $pct = 100;
          ?>
          <div class="card">
            <div class="card-top">
              <div class="card-title"><?= $cat["nom_categorie"] ?></div>
              <div class="badge"><i class="fa-solid fa-ticket"></i><?= $sold ?>/<?= $max ?></div>
            </div>

            <div style="margin:12px 0;">
              <div style="height:8px;background:rgba(255,255,255,.05);border-radius:10px;overflow:hidden">
                <div style="width:<?= $pct ?>%;height:100%;background:linear-gradient(90deg, var(--accent), var(--accent-2));border-radius:10px"></div>
              </div>
              <div style="margin-top:10px;color:var(--muted);font-size:13px;">
                Remplissage: <?= number_format($pct, 1) ?>%
              </div>
            </div>

            <div style="display:flex; justify-content:space-between; padding-top:12px; border-top:1px solid var(--line);">
              <span style="color:var(--muted); font-size:13px;">Revenus</span>
              <strong style="color:var(--gold);"><?= $rev ?> DH</strong>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Top matchs -->
    <div class="section-head" style="margin-top:18px;">
      <div>
        <h2>Top matchs</h2>
        <p style="color:var(--muted);">Top 5 par revenus</p>
      </div>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Match</th>
          <th>Date</th>
          <th>Billets</th>
          <th>Revenus</th>
          <th>Note</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($topMatchs) === 0): ?>
          <tr><td colspan="5" style="color:var(--muted);">Aucun match pour le moment.</td></tr>
        <?php else: ?>
          <?php foreach ($topMatchs as $m): ?>
            <?php
              $places = (int)$m["total_places"];
              $sold   = (int)$m["billets_vendus"];
              $rev    = (float)$m["revenus"];
              $note   = (float)$m["note_moy"];
            ?>
            <tr>
              <td><strong><?= $m["equipe1_nom"] ?> vs <?= $m["equipe2_nom"] ?></strong></td>
              <td><?= $m["date_match"] ?> <?= substr($m["heure_match"], 0, 5) ?></td>
              <td><?= $sold ?> / <?= $places ?></td>
              <td><?= $rev ?> DH</td>
              <td><?= number_format($note, 1) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Tous les matchs -->
    <div class="section-head" style="margin-top:18px;">
      <div>
        <h2>Tous les matchs</h2>
        <p style="color:var(--muted);">Historique complet</p>
      </div>
    </div>

    <table class="table">
      <thead>
        <tr>
          <th>Match</th>
          <th>Date</th>
          <th>Lieu</th>
          <th>Billets</th>
          <th>CA</th>
          <th>Note</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($allMatchs) === 0): ?>
          <tr><td colspan="7" style="color:var(--muted);">Aucun match.</td></tr>
        <?php else: ?>
          <?php foreach ($allMatchs as $m): ?>
            <?php
              $places = (int)$m["total_places"];
              $sold   = (int)$m["billets_vendus"];
              $rev    = (float)$m["revenus"];
              $note   = (float)$m["note_moy"];
              $statut = $m["statut_match"];
              $badgeClass = statutBadgeClass($statut);
            ?>
            <tr>
              <td><strong><?= $m["equipe1_nom"] ?> vs <?= $m["equipe2_nom"] ?></strong></td>
              <td><?= $m["date_match"] ?> <?= substr($m["heure_match"], 0, 5) ?></td>
              <td><?= $m["lieu_match"] ?></td>
              <td><?= $sold ?> / <?= $places ?></td>
              <td><?= $rev ?> DH</td>
              <td><?= number_format($note, 1) ?></td>
              <td>
                <span class="badge <?= $badgeClass ?>">
                  <?= statutLabel($statut) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

</body>
</html>
