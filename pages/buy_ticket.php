<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../config/database.php";

/* 1) Sécurité: seulement acheteur */
if (!isset($_SESSION["user_id"])) {
    $_SESSION["error"] = "Veuillez vous connecter pour acheter un billet.";
    header("Location: ../auth/login.php");
    exit;
}

if (($_SESSION["role"] ?? "") !== "acheteur") {
    $_SESSION["error"] = "Accès refusé (réservé aux acheteurs).";
    header("Location: acheteur_dashboard.php");
    exit;
}

$pdo = Database::getInstance();

/* 2) Infos user (photo + nom dans nav) */
$stmtMe = $pdo->prepare("SELECT id_user, nom_user, prenom_user, photo_user FROM users WHERE id_user = ? LIMIT 1");
$stmtMe->execute([$_SESSION["user_id"]]);
$me = $stmtMe->fetch();

/* 3) Récupérer match_id */
$matchId = (int)($_GET["match_id"] ?? 0);
if ($matchId <= 0) {
    header("Location: acheteur_dashboard.php");
    exit;
}

/* 4) Charger match (doit être publié) */
$stmtMatch = $pdo->prepare("SELECT id_match, equipe1_nom, equipe2_nom, date_match, heure_match, lieu_match, total_places
                            FROM matchs
                            WHERE id_match = ? AND statut_match = 'publie'
                            LIMIT 1");
$stmtMatch->execute([$matchId]);
$match = $stmtMatch->fetch();

if (!$match) {
    $_SESSION["error"] = "Match introuvable ou non publié.";
    header("Location: acheteur_dashboard.php");
    exit;
}

/* 5) Charger catégories du match */
$stmtCats = $pdo->prepare("SELECT id_categorie, nom_categorie, prix_categorie, places_max
                           FROM categories
                           WHERE match_id = ?
                           ORDER BY prix_categorie ASC");
$stmtCats->execute([$matchId]);
$categories = $stmtCats->fetchAll();

if (!$categories || count($categories) === 0) {
    $_SESSION["error"] = "Aucune catégorie disponible pour ce match.";
    header("Location: acheteur_dashboard.php");
    exit;
}

/* 6) Places restantes globales (match) */
$stmtSale = $pdo->prepare("CALL sp_total_ventes_match(?)");
$stmtSale->execute([$matchId]);
$sale = $stmtSale->fetch();
$stmtSale->closeCursor();

$soldTotal = (int)($sale["billets_vendus"] ?? 0);
$remainingTotal = (int)$match["total_places"] - $soldTotal;
if ($remainingTotal < 0) $remainingTotal = 0;

$success = $_SESSION["success"] ?? null;
$error   = $_SESSION["error"] ?? null;
$errors  = $_SESSION["errors"] ?? [];
unset($_SESSION["success"], $_SESSION["error"], $_SESSION["errors"]);

$stmtMine = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE match_id = ? AND acheteur_id = ?");
$stmtMine->execute([$matchId, $_SESSION["user_id"]]);
$alreadyBought = (int)$stmtMine->fetchColumn();

$maxPerMatch = 4;
$remainingForUser = $maxPerMatch - $alreadyBought;
if ($remainingForUser < 0) $remainingForUser = 0;

/* 7) Traitement achat */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $categorieId = (int)($_POST["categorie_id"] ?? 0);
    $qty = (int)($_POST["qty"] ?? 0);

    $localErrors = [];

    if ($alreadyBought >= 4) {
        $localErrors[] = "Vous avez déjà acheté 4 billets pour ce match.";
    } else {
        $maxNow = 4 - $alreadyBought;
        if ($qty > $maxNow) {
            $localErrors[] = "Vous avez déjà $alreadyBought billet(s) pour ce match. Vous pouvez encore acheter $maxNow billet(s) max.";
        }
    }

    if ($categorieId <= 0) $localErrors[] = "Veuillez choisir une catégorie.";
    if ($qty < 1 || $qty > 4) $localErrors[] = "Quantité invalide (1 à 4).";

    /* vérifier la catégorie existe et appartient au match */
    $cat = null;
    foreach ($categories as $c) {
        if ((int)$c["id_categorie"] === $categorieId) {
            $cat = $c;
            break;
        }
    }
    if (!$cat) $localErrors[] = "Catégorie invalide.";

    if ($remainingTotal <= 0) {
        $localErrors[] = "Plus de places disponibles pour ce match.";
    } elseif ($qty > $remainingTotal) {
        $localErrors[] = "Il ne reste que $remainingTotal place(s) au total.";
    }

    /* vérifier places restantes dans la catégorie */
    if ($cat) {
        $stmtSoldCat = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE match_id = ? AND categorie_id = ?");
        $stmtSoldCat->execute([$matchId, $categorieId]);
        $soldCat = (int)$stmtSoldCat->fetchColumn();

        $remainingCat = (int)$cat["places_max"] - $soldCat;

        if ($remainingCat <= 0) {
            $localErrors[] = "Plus de places disponibles dans cette catégorie.";
        } elseif ($qty > $remainingCat) {
            $localErrors[] = "Il ne reste que $remainingCat place(s) dans cette catégorie.";
        }
    }

    if (!empty($localErrors)) {
        $_SESSION["errors"] = $localErrors;
        header("Location: buy_ticket.php?match_id=" . $matchId);
        exit;
    }

    /* Achat (transaction) */
    try {
        $pdo->beginTransaction();

        /* reprendre max place actuelle du match */
        $stmtMaxSeat = $pdo->prepare("SELECT COALESCE(MAX(place_numero), 0) FROM tickets WHERE match_id = ?");
        $stmtMaxSeat->execute([$matchId]);
        $lastSeat = (int)$stmtMaxSeat->fetchColumn();

        $createdSeats = [];
        $createdCodes = [];

        for ($i = 1; $i <= $qty; $i++) {
            $seat = $lastSeat + $i;

            /* sécurité: ne pas dépasser total_places */
            if ($seat > (int)$match["total_places"]) {
                throw new Exception("Capacité du match dépassée.");
            }

            $code = uniqid("TKT_", true);

            $stmtInsert = $pdo->prepare("INSERT INTO tickets
                (acheteur_id, match_id, categorie_id, place_numero, prix_ticket, code_ticket)
                VALUES (?, ?, ?, ?, ?, ?)");

            $stmtInsert->execute([
                $_SESSION["user_id"],
                $matchId,
                $categorieId,
                $seat,
                $cat["prix_categorie"],
                $code
            ]);

            $createdSeats[] = $seat;
            $createdCodes[] = $code;
        }

        $pdo->commit();

        /* message + info tickets */
        $_SESSION["success"] = "Achat réussi. Places: " . implode(", ", $createdSeats);
        $_SESSION["last_tickets"] = [
            "match_id" => $matchId,
            "seats" => $createdSeats,
            "codes" => $createdCodes
        ];

        header("Location: acheteur_dashboard.php");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION["error"] = "Erreur pendant l'achat: " . $e->getMessage();
        header("Location: buy_ticket.php?match_id=" . $matchId);
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION["error"] = "Erreur base de données.";
        header("Location: buy_ticket.php?match_id=" . $matchId);
        exit;
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BuyMatch | Acheter billet</title>
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
        <a href="acheteur_dashboard.php">
          <i class="fa-solid fa-futbol"></i> Matchs
        </a>
        <a class="active" href="buy_ticket.php?match_id=<?= (int)$matchId ?>">
          <i class="fa-solid fa-cart-shopping"></i> Acheter
        </a>
      </div>

      <div class="nav-actions" style="display:flex; align-items:center; gap:10px;">
        <a href="profile.php" class="iconbtn" title="Mon Profil" style="padding:0; overflow:hidden;">
          <?php if (!empty($me["photo_user"])): ?>
            <img src="../<?= $me["photo_user"] ?>" alt="Profil" style="width:42px;height:42px;object-fit:cover;border-radius:12px;">
          <?php else: ?>
            <i class="fa-solid fa-user" style="font-size:14px; color:rgba(255,255,255,.8)"></i>
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
        <h2>Acheter un billet</h2>
        <p>
          <?= $match["equipe1_nom"] ?> vs <?= $match["equipe2_nom"] ?> |
          <?= $match["date_match"] ?> <?= substr($match["heure_match"],0,5) ?> |
          <?= $match["lieu_match"] ?>
        </p>
      </div>
      <span class="badge">
        <i class="fa-solid fa-chair"></i>
        Places restantes: <?= (int)$remainingTotal ?>
      </span>
    </div>

    <?php if ($success): ?>
      <div class="success-message" style="margin-bottom:14px;">
        <i class="fa-solid fa-circle-check"></i> <?= $success ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="error-message" style="margin-bottom:14px;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= ($error) ?>
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

    <div class="card" style="max-width:720px; margin:0 auto;">
      <h3 style="margin:0 0 12px;"><i class="fa-solid fa-ticket"></i> Choix du billet</h3>

      <form method="POST" action="buy_ticket.php?match_id=<?= (int)$matchId ?>">
        <div class="form-row">
          <div class="field">
            <label>Catégorie</label>
            <select class="select" name="categorie_id" required>
              <option value="">Choisir...</option>
              <?php foreach ($categories as $c): ?>
                <?php
                  // places restantes catégorie (info simple)
                  $stmtTmp = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE match_id = ? AND categorie_id = ?");
                  $stmtTmp->execute([$matchId, $c["id_categorie"]]);
                  $soldCatTmp = (int)$stmtTmp->fetchColumn();
                  $remainCatTmp = (int)$c["places_max"] - $soldCatTmp;
                ?>
                <option value="<?= (int)$c["id_categorie"] ?>">
                  <?= $c["nom_categorie"] ?> - <?= $c["prix_categorie"] ?> DH (reste: <?= (int)$remainCatTmp ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Quantité (max 4)</label>
            <input class="input" type="number" name="qty" min="1" max="<?= $remainingForUser ?>" value="1" required>
            <span class="badge">
              <i class="fa-solid fa-user"></i>
              Déjà acheté: <?= $alreadyBought ?> / 4
            </span>
          </div>
        </div>

        <div class="card-actions" style="margin-top:14px;">
          <button class="btn btn-primary" type="submit">
            <i class="fa-solid fa-credit-card"></i> Confirmer l'achat
          </button>

          <a class="btn btn-ghost" href="acheteur_dashboard.php">
            <i class="fa-solid fa-arrow-left"></i> Retour
          </a>
        </div>

        <p style="margin:12px 0 0; color:var(--muted); font-size:13px; line-height:1.6;">
          Les places sont attribuées automatiquement (numéro de place unique).
        </p>
      </form>
    </div>

  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

<script src="assets/script.js"></script>
</body>
</html>
