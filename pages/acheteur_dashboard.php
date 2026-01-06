<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../config/database.php";

$isLogged = isset($_SESSION["user_id"]);
$role = $_SESSION["role"] ?? null;

if ($isLogged && $role === "organisateur") {
    header("Location: organizer/organisateur_dashboard.php");
    exit;
}
if ($isLogged && $role === "admin") {
    header("Location: Admin/dashboard.php");
    exit;
}

$success = $_SESSION["success"] ?? null;
$error   = $_SESSION["error"] ?? null;
$errors  = $_SESSION["errors"] ?? [];
unset($_SESSION["success"], $_SESSION["error"], $_SESSION["errors"]);

$pdo = Database::getInstance();

$me = null;
if ($isLogged) {
    $stmtMe = $pdo->prepare("SELECT id_user, nom_user, prenom_user, email_user, photo_user, role_user
                              FROM users
                              WHERE id_user = ?
                              LIMIT 1");
    $stmtMe->execute([$_SESSION["user_id"]]);
    $me = $stmtMe->fetch();
}

$q = trim($_GET["q"] ?? "");
$lieu = trim($_GET["lieu"] ?? "");

$stmtLieux = $pdo->query("SELECT DISTINCT lieu_match
                          FROM matchs
                          WHERE statut_match = 'publie'
                          ORDER BY lieu_match ASC");
$lieux = $stmtLieux->fetchAll();

$sql = "SELECT  m.id_match,m.equipe1_nom, m.equipe2_nom,
                m.date_match, m.heure_match,m.lieu_match,
                MIN(c.prix_categorie) AS min_prix
        FROM matchs m
        LEFT JOIN categories c ON c.match_id = m.id_match
        WHERE m.statut_match = 'publie'";

$params = [];

if ($q !== "") {
    $sql .= " AND (m.equipe1_nom LIKE ? OR m.equipe2_nom LIKE ? OR m.lieu_match LIKE ?)";
    $like = "%".$q."%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($lieu !== "") {
    $sql .= " AND m.lieu_match = ?";
    $params[] = $lieu;
}

$sql .= " GROUP BY m.id_match
          ORDER BY m.date_match ASC, m.heure_match ASC
          LIMIT 30";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matchs = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BuyMatch | Matchs</title>
  <link rel="stylesheet" href="../assets/style.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="acheteur_dashboard.php">
        <i class="fa-solid fa-ticket"></i>
        <span>BuyMatch</span>
      </a>

      <div class="navlinks">
        <a href="acheteur_dashboard.php" class="active"><i class="fa-solid fa-futbol"></i> Matchs</a>
      </div>

      <div class="nav-actions" style="display:flex; align-items:center; gap:10px;">
        <?php if (!$isLogged): ?>
          <a class="btn btn-ghost" href="../auth/login.php">
            <i class="fa-solid fa-right-to-bracket"></i> Connexion
          </a>
          <a class="btn btn-primary" href="../auth/register.php">
            <i class="fa-solid fa-user-plus"></i> Inscription
          </a>
        <?php else: ?>
          <!-- Photo => profile -->
          <a href="profile.php" class="iconbtn" title="Mon Profil" style="padding:0; overflow:hidden;">
            <?php if (!empty($me["photo_user"])): ?>
              <img src="<?= $me["photo_user"] ?>" alt="Profil" style="width:42px;height:42px;object-fit:cover;border-radius:12px;">
            <?php else: ?>
              <i class="fa-solid fa-user" style="font-size:14px; color:rgba(255,255,255,.8)"></i>
            <?php endif; ?>
          </a>

          <a class="btn btn-danger" href="../auth/logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">

    <div class="section-head">
      <div>
        <h2>Matchs disponibles</h2>
        <p>Recherchez un match et consultez les détails</p>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="success-message" style="margin-bottom:14px;">
        <i class="fa-solid fa-circle-check"></i> <?= $success ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="error-message" style="margin-bottom:14px;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="error-message" style="margin-bottom:14px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <ul style="margin:8px 0 0 18px;">
          <?php foreach ($errors as $e): ?>
            <li><?= $e ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Barre de recherche -->
    <form class="toolbar" method="GET" action="acheteur_dashboard.php" style="margin-bottom:16px;">
      <input class="input" type="text" name="q" placeholder="Rechercher (équipes, lieu...)" value="<?= htmlspecialchars($q) ?>">
      <select class="select" name="lieu">
        <option value="">Tous les lieux</option>
        <?php foreach ($lieux as $l): ?>
          <?php $val = $l["lieu_match"]; ?>
          <option value="<?= $val ?>" <?= ($lieu === $val ? "selected" : "") ?>>
            <?= $val ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary" type="submit">
        <i class="fa-solid fa-magnifying-glass"></i> Chercher
      </button>

      <a class="btn btn-ghost" href="acheteur_dashboard.php">
        <i class="fa-solid fa-rotate-left"></i> Réinitialiser
      </a>
    </form>

    <!-- Liste des matchs -->
    <div class="grid">
      <?php if (count($matchs) === 0): ?>
        <div class="card" style="color:var(--muted);">
          Aucun match trouvé.
        </div>
      <?php else: ?>
        <?php foreach ($matchs as $m): ?>
          <?php $mid = (int)$m["id_match"]; ?>

          <div class="card">
            <div class="card-top">
              <p class="card-title">
                <?= $m["equipe1_nom"] ?> vs <?= $m["equipe2_nom"] ?>
              </p>
              <span class="badge">
                <i class="fa-solid fa-tag"></i>
                <?= ($m["min_prix"] !== null ? $m["min_prix"]." DH" : "—") ?>
              </span>
            </div>

            <div class="meta">
              <span><i class="fa-solid fa-calendar"></i> <?= $m["date_match"] ?></span>
              <span><i class="fa-solid fa-clock"></i> <?= substr($m["heure_match"], 0, 5) ?></span>
              <span><i class="fa-solid fa-location-dot"></i> <?= $m["lieu_match"] ?></span>
            </div>

            <div class="card-actions">
              <button type="button" class="btn btn-ghost"
                      data-open-modal="matchModal-<?= $mid ?>">
                <i class="fa-solid fa-circle-info"></i> Détails
              </button>

              <?php if ($isLogged && ($role === "acheteur")): ?>
                <a class="btn btn-primary" href="buy_ticket.php?match_id=<?= $mid ?>">
                  <i class="fa-solid fa-ticket"></i> Réserver
                </a>
              <?php else: ?>
                <a class="btn btn-primary" href="../auth/login.php">
                  <i class="fa-solid fa-lock"></i> Se connecter pour réserver
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- MODAL -->
          <div class="bm-modal" id="matchModal-<?= $mid ?>" style="display:none;">
            <div class="bm-modal-backdrop" data-close-modal="matchModal-<?= $mid ?>"></div>

            <div class="bm-modal-card" role="dialog" aria-modal="true">
              <div class="bm-modal-head">
                <div style="font-weight:900;">
                  <i class="fa-solid fa-circle-info"></i> Détails du match
                </div>
                <button class="bm-modal-close" type="button" data-close-modal="matchModal-<?= $mid ?>">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>

              <div class="bm-modal-body">
                <h3 style="margin:0 0 10px;">
                  <?= $m["equipe1_nom"] ?> vs <?= $m["equipe2_nom"] ?>
                </h3>

                <div class="meta" style="margin-bottom:12px;">
                  <span><i class="fa-solid fa-calendar"></i> <?= $m["date_match"] ?></span>
                  <span><i class="fa-solid fa-clock"></i> <?= substr($m["heure_match"], 0, 5) ?></span>
                  <span><i class="fa-solid fa-location-dot"></i> <?= $m["lieu_match"] ?></span>
                </div>

                <div class="card" style="padding:12px; background:rgba(255,255,255,.03);">
                  <div style="font-weight:800; margin-bottom:8px;">Prix minimum</div>
                  <div class="badge">
                    <i class="fa-solid fa-tag"></i>
                    <?= ($m["min_prix"] !== null ? $m["min_prix"]." DH" : "Non défini") ?>
                  </div>
                </div>

                <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                  <?php if ($isLogged && ($role === "acheteur")): ?>
                    <a class="btn btn-primary" href="buy_ticket.php?match_id=<?= $mid ?>">
                      <i class="fa-solid fa-ticket"></i> Réserver
                    </a>
                  <?php else: ?>
                    <a class="btn btn-primary" href="../auth/login.php">
                      <i class="fa-solid fa-lock"></i> Se connecter pour réserver
                    </a>
                  <?php endif; ?>

                  <button type="button" class="btn btn-ghost" data-close-modal="matchModal-<?= $mid ?>">
                    <i class="fa-solid fa-xmark"></i> Fermer
                  </button>
                </div>

              </div>
            </div>
          </div>
          <!-- END MODAL -->

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
