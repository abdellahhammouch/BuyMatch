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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_comment"])) {

    if (!$isLogged || $role !== "acheteur") {
        $_SESSION["error"] = "Vous devez être connecté en tant qu'acheteur.";
        header("Location: acheteur_dashboard.php");
        exit;
    }

    $matchIdPost = (int)($_POST["match_id"] ?? 0);
    $contenu = trim($_POST["contenu"] ?? "");
    $note = (int)($_POST["note"] ?? 0);

    $localErrors = [];

    if ($matchIdPost <= 0) $localErrors[] = "Match invalide.";
    if ($contenu === "") $localErrors[] = "Commentaire obligatoire.";
    if ($note < 1 || $note > 5) $localErrors[] = "Note invalide (1 à 5).";

    // Vérifier que l'acheteur a au moins 1 ticket dans ce match
    if (empty($localErrors)) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM tickets 
                                    WHERE acheteur_id = ? AND match_id = ?");
        $stmtCheck->execute([$_SESSION["user_id"], $matchIdPost]);
        $hasTicket = (int)$stmtCheck->fetchColumn();

        if ($hasTicket <= 0) {
            $localErrors[] = "Vous ne pouvez commenter que les matchs où vous avez un ticket.";
        }
    }

    if (!empty($localErrors)) {
        $_SESSION["errors"] = $localErrors;
        header("Location: acheteur_dashboard.php?open=" . $matchIdPost);
        exit;
    }

    // Insert comment
    $stmtIns = $pdo->prepare("INSERT INTO comments (match_id, user_id, note, contenu) VALUES (?, ?, ?, ?)");
    $stmtIns->execute([$matchIdPost, $_SESSION["user_id"], $note, $contenu]);

    $_SESSION["success"] = "Commentaire ajouté.";
    header("Location: acheteur_dashboard.php?open=" . $matchIdPost);
    exit;
}


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

$sql = "SELECT  m.id_match, m.equipe1_nom, m.equipe2_nom,
                m.equipe1_logo, m.equipe2_logo,
                m.date_match, m.heure_match, m.lieu_match,
                m.total_places,
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

/* ---------- catégories + tickets vendus (pour les modals) ---------- */
$catsByMatch = [];
$soldTotalByMatch = [];
$soldByMatchCat = [];

$matchIds = [];
foreach ($matchs as $m) {
    $matchIds[] = (int)$m["id_match"];
}

/* ---------- Peut commenter ? (acheteur + ticket) ---------- */
$canComment = [];
$commentsByMatch = [];

if ($isLogged && $role === "acheteur" && count($matchIds) > 0) {
    $placeholders = implode(",", array_fill(0, count($matchIds), "?"));

    // Matches où l'acheteur a des tickets
    $paramsTickets = $matchIds;
    array_unshift($paramsTickets, $_SESSION["user_id"]);

    $stmtMyTickets = $pdo->prepare("SELECT match_id, COUNT(*) AS qty
                                    FROM tickets
                                    WHERE acheteur_id = ? AND match_id IN ($placeholders)
                                    GROUP BY match_id");
    $stmtMyTickets->execute($paramsTickets);

    foreach ($stmtMyTickets->fetchAll() as $r) {
        $canComment[(int)$r["match_id"]] = true;
    }

    // Charger commentaires des matchs affichés
    $stmtCom = $pdo->prepare("SELECT c.id_comment, c.match_id, c.note, c.contenu, c.created_at,
                                    u.nom_user, u.prenom_user
                              FROM comments c
                              JOIN users u ON u.id_user = c.user_id
                              WHERE c.match_id IN ($placeholders)
                              ORDER BY c.created_at DESC");
    $stmtCom->execute($matchIds);

    foreach ($stmtCom->fetchAll() as $cm) {
        $mid = (int)$cm["match_id"];
        if (!isset($commentsByMatch[$mid])) $commentsByMatch[$mid] = [];
        $commentsByMatch[$mid][] = $cm;
    }
}


if (count($matchIds) > 0) {
    $placeholders = implode(",", array_fill(0, count($matchIds), "?"));

    // 1) Catégories de tous les matchs affichés
    $stmtCats = $pdo->prepare("SELECT id_categorie, match_id, nom_categorie, prix_categorie, places_max
                              FROM categories
                              WHERE match_id IN ($placeholders)
                              ORDER BY prix_categorie ASC");
    $stmtCats->execute($matchIds);
    $allCats = $stmtCats->fetchAll();

    foreach ($allCats as $c) {
        $mid = (int)$c["match_id"];
        if (!isset($catsByMatch[$mid])) $catsByMatch[$mid] = [];
        $catsByMatch[$mid][] = $c;
    }

    // 2) Total billets vendus par match
    $stmtSoldTotal = $pdo->prepare("SELECT match_id, COUNT(*) AS sold_total
                                    FROM tickets
                                    WHERE match_id IN ($placeholders)
                                    GROUP BY match_id");
    $stmtSoldTotal->execute($matchIds);
    foreach ($stmtSoldTotal->fetchAll() as $r) {
        $soldTotalByMatch[(int)$r["match_id"]] = (int)$r["sold_total"];
    }

    // 3) Billets vendus par catégorie (par match)
    $stmtSoldCat = $pdo->prepare("SELECT match_id, categorie_id, COUNT(*) AS sold
                                  FROM tickets
                                  WHERE match_id IN ($placeholders)
                                  GROUP BY match_id, categorie_id");
    $stmtSoldCat->execute($matchIds);
    foreach ($stmtSoldCat->fetchAll() as $r) {
        $mId = (int)$r["match_id"];
        $cId = (int)$r["categorie_id"];
        if (!isset($soldByMatchCat[$mId])) $soldByMatchCat[$mId] = [];
        $soldByMatchCat[$mId][$cId] = (int)$r["sold"];
    }
    /* ---------- Historique achats (tickets acheteur) ---------- */
    $history = [];

    if ($isLogged && $role === "acheteur") {
        $stmtHist = $pdo->prepare("SELECT m.id_match,
                m.equipe1_nom, m.equipe2_nom,
                m.date_match, m.heure_match, m.lieu_match,
                c.nom_categorie,
                COUNT(*) AS qty,
                GROUP_CONCAT(t.place_numero ORDER BY t.place_numero SEPARATOR ', ') AS seats,
                SUM(t.prix_ticket) AS total_paid,
                MAX(t.id_ticket) AS last_ticket_id
            FROM tickets t
            JOIN matchs m ON m.id_match = t.match_id
            JOIN categories c ON c.id_categorie = t.categorie_id
            WHERE t.acheteur_id = ?
            GROUP BY m.id_match, c.id_categorie
            ORDER BY last_ticket_id DESC
            LIMIT 50
        ");
        $stmtHist->execute([$_SESSION["user_id"]]);
        $history = $stmtHist->fetchAll();
    }

}

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
        <a href="#" class="active" data-tab-link="matchs">
          <i class="fa-solid fa-futbol"></i> Matchs
        </a>

        <a href="#" data-tab-link="history">
          <i class="fa-solid fa-receipt"></i> Historique d’achats
        </a>
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
              <img src="../<?= $me["photo_user"] ?>" alt="Profil" style="width:42px;height:42px;object-fit:cover;border-radius:12px;">
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
    <div id="tab-matchs">
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
          <?php $soldTotal = $soldTotalByMatch[$mid] ?? 0;
                $remainingTotal = (int)$m["total_places"] - (int)$soldTotal;
                if ($remainingTotal < 0) $remainingTotal = 0;
                $catList = $catsByMatch[$mid] ?? [];
          ?>

          <div class="card">
            <div class="card-top">
              <div class="card-title" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:8px;">
                  <?php if (!empty($m["equipe1_logo"])): ?>
                    <img src="../<?= $m["equipe1_logo"] ?>" alt="Logo 1" style="width:28px;height:28px;object-fit:cover;border-radius:8px;">
                  <?php else: ?>
                    <i class="fa-solid fa-shield" style="opacity:.6"></i>
                  <?php endif; ?>
                  <span style="font-weight:900;"><?= $m["equipe1_nom"] ?></span>
                </div>

                <span style="opacity:.7; font-weight:900;">vs</span>

                <div style="display:flex; align-items:center; gap:8px;">
                  <?php if (!empty($m["equipe2_logo"])): ?>
                    <img src="../<?= $m["equipe2_logo"] ?>" alt="Logo 2" style="width:28px;height:28px;object-fit:cover;border-radius:8px;">
                  <?php else: ?>
                    <i class="fa-solid fa-shield" style="opacity:.6"></i>
                  <?php endif; ?>
                  <span style="font-weight:900;"><?= $m["equipe2_nom"] ?></span>
                </div>
              </div>
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

                <div style="text-align:center; margin-bottom:16px;">

                  <!-- Logos + Names -->
                  <div style="display:flex; align-items:center; justify-content:center; gap:18px; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:12px;">
                      <?php if (!empty($m["equipe1_logo"])): ?>
                        <img src="../<?= $m["equipe1_logo"] ?>" alt="Logo 1"
                            style="width:62px;height:62px;object-fit:cover;border-radius:16px;">
                      <?php else: ?>
                        <div style="width:62px;height:62px;border-radius:16px;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;">
                          <i class="fa-solid fa-shield" style="opacity:.7;font-size:22px;"></i>
                        </div>
                      <?php endif; ?>

                      <div style="font-weight:900; font-size:22px; line-height:1.1;">
                        <?= $m["equipe1_nom"] ?>
                      </div>
                    </div>

                    <div class="badge" style="font-weight:900; padding:10px 14px;">
                      VS
                    </div>

                    <div style="display:flex; align-items:center; gap:12px;">
                      <?php if (!empty($m["equipe2_logo"])): ?>
                        <img src="../<?= $m["equipe2_logo"] ?>" alt="Logo 2"
                            style="width:62px;height:62px;object-fit:cover;border-radius:16px;">
                      <?php else: ?>
                        <div style="width:62px;height:62px;border-radius:16px;border:1px solid var(--line);display:flex;align-items:center;justify-content:center;">
                          <i class="fa-solid fa-shield" style="opacity:.7;font-size:22px;"></i>
                        </div>
                      <?php endif; ?>

                      <div style="font-weight:900; font-size:22px; line-height:1.1;">
                        <?= $m["equipe2_nom"] ?>
                      </div>
                    </div>
                  </div>

                  <!-- Infos match centered -->
                  <div class="meta" style="justify-content:center; margin-top:10px;">
                    <span><i class="fa-solid fa-calendar"></i> <?= $m["date_match"] ?></span>
                    <span><i class="fa-solid fa-clock"></i> <?= substr($m["heure_match"], 0, 5) ?></span>
                    <span><i class="fa-solid fa-location-dot"></i> <?= $m["lieu_match"] ?></span>
                  </div>
                </div>

                <!-- Résumé global -->
                <div class="card" style="padding:12px; background:rgba(255,255,255,.03); margin-bottom:12px;">
                  <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <span class="badge"><i class="fa-solid fa-users"></i> Total: <?= (int)$m["total_places"] ?></span>
                    <span class="badge"><i class="fa-solid fa-ticket"></i> Vendus: <?= (int)$soldTotal ?></span>
                    <span class="badge"><i class="fa-solid fa-chair"></i> Restants: <?= (int)$remainingTotal ?></span>
                    <span class="badge"><i class="fa-solid fa-tag"></i> Prix min: <?= ($m["min_prix"] !== null ? $m["min_prix"]." DH" : "—") ?></span>
                  </div>
                </div>

                <!-- Catégories -->
                <div class="card" style="padding:12px; background:rgba(255,255,255,.03);">
                  <div style="font-weight:900; margin-bottom:10px;">
                    <i class="fa-solid fa-tags"></i> Catégories disponibles
                  </div>

                  <?php if (count($catList) === 0): ?>
                    <div style="color:var(--muted);">Aucune catégorie pour ce match.</div>
                  <?php else: ?>

                    <div style="display:grid; gap:10px;">
                      <?php foreach ($catList as $c): ?>
                        <?php
                          $cid = (int)$c["id_categorie"];
                          $soldCat = $soldByMatchCat[$mid][$cid] ?? 0;
                          $remainingCat = (int)$c["places_max"] - (int)$soldCat;
                          if ($remainingCat < 0) $remainingCat = 0;
                        ?>

                        <div style="border:1px solid var(--line); border-radius:12px; padding:10px;">
                          <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start;">
                            <div>
                              <div style="font-weight:900;"><?= $c["nom_categorie"] ?></div>
                              <div style="color:var(--muted); font-size:13px; margin-top:4px;">
                                Prix: <?= $c["prix_categorie"] ?> DH
                              </div>
                            </div>

                            <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                              <span class="badge"><i class="fa-solid fa-layer-group"></i> Total: <?= (int)$c["places_max"] ?></span>
                              <span class="badge"><i class="fa-solid fa-ticket"></i> Vendus: <?= (int)$soldCat ?></span>
                              <span class="badge"><i class="fa-solid fa-chair"></i> Restants: <?= (int)$remainingCat ?></span>
                            </div>
                          </div>
                        </div>

                      <?php endforeach; ?>
                    </div>

                  <?php endif; ?>
                </div>

                <!-- Commentaires -->
                <div class="card" style="padding:12px; background:rgba(255,255,255,.03); margin-top:12px;">
                  <div style="font-weight:900; margin-bottom:10px;">
                    <i class="fa-solid fa-comments"></i> Commentaires
                  </div>

                  <?php
                    $comList = $commentsByMatch[$mid] ?? [];
                  ?>

                  <?php if (count($comList) === 0): ?>
                    <div style="color:var(--muted);">Aucun commentaire pour le moment.</div>
                  <?php else: ?>
                    <div style="display:grid; gap:10px;">
                      <?php foreach ($comList as $cm): ?>
                        <div style="border:1px solid var(--line); border-radius:12px; padding:10px;">
                          <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                            <div style="font-weight:900;">
                              <?= $cm["prenom_user"] ?> <?= $cm["nom_user"] ?>
                            </div>
                            <div style="color:var(--muted); font-size:13px;">
                              <?= $cm["created_at"] ?>
                            </div>
                          </div>

                          <div style="margin-top:6px; display:flex; gap:8px; align-items:center;">
                            <span class="badge"><i class="fa-solid fa-star"></i> <?= (int)$cm["note"] ?>/5</span>
                          </div>

                          <div style="margin-top:8px; color:var(--muted); line-height:1.6;">
                            <?= $cm["contenu"] ?>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($isLogged && $role === "acheteur" && !empty($canComment[$mid])): ?>
                    <div style="margin-top:12px; border-top:1px solid var(--line); padding-top:12px;">
                      <div style="font-weight:900; margin-bottom:8px;">
                        Ajouter un commentaire
                      </div>

                      <form method="POST" action="acheteur_dashboard.php">
                        <input type="hidden" name="add_comment" value="1">
                        <input type="hidden" name="match_id" value="<?= $mid ?>">

                        <div class="form-row">
                          <div class="field">
                            <label>Note (1 à 5)</label>
                            <select class="select" name="note" required>
                              <option value="5">5</option>
                              <option value="4">4</option>
                              <option value="3">3</option>
                              <option value="2">2</option>
                              <option value="1">1</option>
                            </select>
                          </div>
                        </div>

                        <div class="field" style="margin-top:10px;">
                          <label>Commentaire</label>
                          <textarea class="input" name="contenu" rows="3" placeholder="Écrivez votre avis..." required></textarea>
                        </div>

                        <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">
                          <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-paper-plane"></i> Envoyer
                          </button>
                        </div>
                      </form>
                    </div>

                  <?php elseif ($isLogged && $role === "acheteur"): ?>
                    <div style="margin-top:12px; color:var(--muted); font-size:13px;">
                      Vous pouvez commenter seulement si vous avez acheté au moins 1 ticket pour ce match.
                    </div>
                  <?php endif; ?>
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
    <div id="tab-history" style="display:none;">

  <div class="section-head">
    <div>
      <h2>Historique d’achats</h2>
      <p>Vos billets achetés (par match et catégorie)</p>
    </div>
  </div>

  <?php if (!$isLogged): ?>
    <div class="card" style="color:var(--muted);">
      Vous devez vous connecter pour voir votre historique.
      <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn btn-ghost" href="../auth/login.php">
          <i class="fa-solid fa-right-to-bracket"></i> Connexion
        </a>
        <a class="btn btn-primary" href="../auth/register.php">
          <i class="fa-solid fa-user-plus"></i> Inscription
        </a>
      </div>
    </div>

  <?php else: ?>

    <?php if (count($history) === 0): ?>
      <div class="card" style="color:var(--muted);">
        Aucun achat pour le moment.
      </div>
    <?php else: ?>

      <div class="table-card">
        <table>
          <thead>
            <tr>
              <th>Match</th>
              <th>Date</th>
              <th>Lieu</th>
              <th>Catégorie</th>
              <th>Qté</th>
              <th>Places</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($history as $h): ?>
              <tr>
                <td><strong><?= $h["equipe1_nom"] ?> vs <?= $h["equipe2_nom"] ?></strong></td>
                <td><?= $h["date_match"] ?> <?= substr($h["heure_match"],0,5) ?></td>
                <td><?= $h["lieu_match"] ?></td>
                <td><?= $h["nom_categorie"] ?></td>
                <td><?= (int)$h["qty"] ?></td>
                <td><?= $h["seats"] ?></td>
                <td><strong><?= $h["total_paid"] ?> DH</strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php endif; ?>

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
