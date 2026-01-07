<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../classes/MatchSport.php";

$ms = new MatchSport($pdo);

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

$pdo = Database::getInstance();

$success = $_SESSION["success"] ?? null;
$error   = $_SESSION["error"] ?? null;
unset($_SESSION["success"], $_SESSION["error"]);

// Vérifier si tickets.created_at existe (pour ventes d'aujourd'hui)
$hasTicketCreatedAt = false;
try {
    $col = $pdo->query("SHOW COLUMNS FROM tickets LIKE 'created_at'")->fetch();
    if ($col) $hasTicketCreatedAt = true;
} catch (Throwable $e) {
    $hasTicketCreatedAt = false;
}

// Actions POST (valider/refuser match, activer/désactiver user)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1) Valider / Refuser match
    if (isset($_POST["action"]) && $_POST["action"] === "match_status") {
        $matchId = (int)($_POST["match_id"] ?? 0);
        $status  = $_POST["status"] ?? ""; // publie | refuse

        if ($matchId > 0 && ($status === "publie" || $status === "refuse")) {
            $stmt = $pdo->prepare("UPDATE matchs SET statut_match = ? WHERE id_match = ?");
            $stmt->execute([$status, $matchId]);

            $_SESSION["success"] = ($status === "publie") ? "Match accepté et publié." : "Match refusé.";
        } else {
            $_SESSION["error"] = "Données invalides pour la validation du match.";
        }

        header("Location: dashboard.php");
        exit;
    }

    // 2) Activer/Désactiver user (interdire admin)
    if (isset($_POST["action"]) && $_POST["action"] === "toggle_user") {
        $userId = (int)($_POST["user_id"] ?? 0);

        if ($userId > 0) {
            $stmt = $pdo->prepare("SELECT role_user, is_active FROM users WHERE id_user = ? LIMIT 1");
            $stmt->execute([$userId]);
            $u = $stmt->fetch();

            if (!$u) {
                $_SESSION["error"] = "Utilisateur introuvable.";
            } elseif ($u["role_user"] === "admin") {
                $_SESSION["error"] = "Impossible de modifier un admin.";
            } else {
                $newState = ($u["is_active"] ? 0 : 1);
                $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id_user = ?");
                $stmt->execute([$newState, $userId]);
                $_SESSION["success"] = $newState ? "Utilisateur activé." : "Utilisateur désactivé.";
            }
        } else {
            $_SESSION["error"] = "ID utilisateur invalide.";
        }

        header("Location: dashboard.php");
        exit;
    }
    if (isset($_POST["action"]) && $_POST["action"] === "delete_comment") {

        $commentId = (int)($_POST["comment_id"] ?? 0);

        if ($commentId <= 0) {
            $_SESSION["error"] = "Commentaire invalide.";
            header("Location: dashboard.php");
            exit;
        }

        // Vérifier que le commentaire appartient à un match publié
        $stmtCheck = $pdo->prepare("SELECT c.id_comment
                                    FROM comments c
                                    JOIN matchs m ON m.id_match = c.match_id
                                    WHERE c.id_comment = ? AND m.statut_match = 'publie'
                                    LIMIT 1");
        $stmtCheck->execute([$commentId]);
        $ok = $stmtCheck->fetch();

        if (!$ok) {
            $_SESSION["error"] = "Suppression impossible. (Match non publié ou commentaire introuvable)";
            header("Location: dashboard.php");
            exit;
        }

        $stmtDel = $pdo->prepare("DELETE FROM comments WHERE id_comment = ? LIMIT 1");
        $stmtDel->execute([$commentId]);

        $_SESSION["success"] = "Commentaire supprimé.";
        header("Location: dashboard.php");
        exit;
    }
}

/*
  DATA: Demandes matchs (en_attente)
*/
$stmtDemandes = $pdo->query("SELECT m.id_match, m.equipe1_nom, m.equipe2_nom, m.date_match, m.heure_match, m.lieu_match, m.statut_match,
                                  u.id_user AS org_id, u.nom_user, u.prenom_user, u.email_user
                            FROM matchs m
                            JOIN users u ON u.id_user = m.organisateur_id
                            WHERE m.statut_match = 'en_attente'
                            ORDER BY m.date_match ASC, m.heure_match ASC");
$demandes = $stmtDemandes->fetchAll();

/*
  DATA: Users (sans admin)
*/
$stmtUsers = $pdo->query("SELECT id_user, nom_user, prenom_user, email_user, phone_user, role_user, is_active
                          FROM users
                          WHERE role_user <> 'admin'
                          ORDER BY role_user ASC, id_user DESC");
$users = $stmtUsers->fetchAll();

/*
  DATA: Organisateurs + Acheteurs séparés
*/
$stmtOrgs = $pdo->query("SELECT id_user, nom_user, prenom_user, email_user, is_active
                        FROM users
                        WHERE role_user = 'organisateur'
                        ORDER BY id_user DESC");
$organisateurs = $stmtOrgs->fetchAll();

$stmtAcheteurs = $pdo->query("SELECT id_user, nom_user, prenom_user, email_user, is_active
                              FROM users
                              WHERE role_user = 'acheteur'
                              ORDER BY id_user DESC");
$acheteurs = $stmtAcheteurs->fetchAll();

/*
  DATA: Commentaires (matchs publiés)
*/
$stmtAdminComments = $pdo->query("SELECT c.id_comment, c.note, c.contenu, c.created_at,
                                        u.prenom_user, u.nom_user,
                                        m.id_match, m.equipe1_nom, m.equipe2_nom, m.lieu_match
                                  FROM comments c
                                  JOIN matchs m ON m.id_match = c.match_id
                                  JOIN users u ON u.id_user = c.user_id
                                  WHERE m.statut_match = 'publie'
                                  ORDER BY c.created_at DESC
                                  LIMIT 30");
$adminComments = $stmtAdminComments->fetchAll();


$stmtAllMatches = $pdo->query("SELECT id_match, equipe1_nom, equipe2_nom, date_match, lieu_match
                              FROM matchs
                              ORDER BY date_match DESC
                              LIMIT 50");
$allMatches = $stmtAllMatches->fetchAll();

$matchDetailsSales = null;
$selectedMatchId = (int)($_GET["match_sales_id"] ?? 0);

if ($selectedMatchId > 0) {
    $stmtProc = $pdo->prepare("CALL sp_total_ventes_match(?)");
    $stmtProc->execute([$selectedMatchId]);
    $matchDetailsSales = $stmtProc->fetch();
    $stmtProc->closeCursor(); // important après CALL
}



/*
  STATS GLOBALES
*/
$global = $ms->getGlobalSales();
$globalBillets = $global["billets_vendus"] ?? 0;
$globalCA      = $global["chiffre_affaires"] ?? 0;

$todayBillets = null;
$todayCA = null;
if ($hasTicketCreatedAt) {
    $stmtToday = $pdo->query("SELECT COUNT(*) AS billets_vendus, COALESCE(SUM(prix_ticket), 0) AS chiffre_affaires
                              FROM tickets
                              WHERE DATE(created_at) = CURDATE()");
    $t = $stmtToday->fetch();
    $todayBillets = $t["billets_vendus"] ?? 0;
    $todayCA      = $t["chiffre_affaires"] ?? 0;
}

$stmtCounts = $pdo->query("SELECT SUM(CASE WHEN role_user='organisateur' THEN 1 ELSE 0 END) AS nb_organisateurs,
                              SUM(CASE WHEN role_user='acheteur' THEN 1 ELSE 0 END) AS nb_acheteurs
                            FROM users");
$counts = $stmtCounts->fetch();
$nbOrgs = $counts["nb_organisateurs"] ?? 0;
$nbAch  = $counts["nb_acheteurs"] ?? 0;

/*
  STATS PAR ORGANISATEUR (détails)
  On prépare un tableau: org_id => stats
*/
$orgStats = [];

$stmtOrgStats = $pdo->query("SELECT u.id_user AS org_id,
                              COUNT(DISTINCT m.id_match) AS total_matchs,
                              SUM(CASE WHEN m.statut_match='publie' THEN 1 ELSE 0 END) AS matchs_publies,
                              SUM(CASE WHEN m.statut_match='en_attente' THEN 1 ELSE 0 END) AS matchs_en_attente,
                              SUM(CASE WHEN m.statut_match='refuse' THEN 1 ELSE 0 END) AS matchs_refuses,
                              COUNT(t.id_ticket) AS billets_vendus,
                              COALESCE(SUM(t.prix_ticket), 0) AS chiffre_affaires,
                              ROUND(AVG(c.note), 2) AS note_moyenne
                            FROM users u
                            LEFT JOIN matchs m ON m.organisateur_id = u.id_user
                            LEFT JOIN tickets t ON t.match_id = m.id_match
                            LEFT JOIN comments c ON c.match_id = m.id_match
                            WHERE u.role_user = 'organisateur'
                            GROUP BY u.id_user");
$tmp = $stmtOrgStats->fetchAll();
foreach ($tmp as $row) {
    $orgStats[(int)$row["org_id"]] = $row;
}

/*
  TOP 5 matchs (global)
*/
$topMatchs = $ms->getTopPublishedMatches(5);

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>BuyMatch | Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/style.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="dashboard.php"><i class="fa-solid fa-ticket"></i><span>BuyMatch</span></a>

      <div class="navlinks">
        <a href="#" class="sidebar-link active" data-section="demandes"><i class="fa-solid fa-inbox"></i> Demandes</a>
        <a href="#" class="sidebar-link" data-section="users"><i class="fa-solid fa-users"></i> Utilisateurs</a>
        <a href="#" class="sidebar-link" data-section="stats"><i class="fa-solid fa-chart-column"></i> Stats</a>
      </div>

      <div class="nav-actions" style="display:flex; gap:10px; align-items:center;">
        <a class="btn btn-danger" href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
      </div>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">

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

    <!-- DEMANDES -->
    <div id="demandesSection" class="dashboard-section">
      <div class="section-head">
        <div>
          <h2>Demandes de matchs</h2>
          <p>Valider ou refuser les demandes des organisateurs</p>
        </div>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th>Match</th>
            <th>Date</th>
            <th>Lieu</th>
            <th>Organisateur</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($demandes) === 0): ?>
            <tr><td colspan="5" style="color:var(--muted);">Aucune demande en attente.</td></tr>
          <?php else: ?>
            <?php foreach ($demandes as $m): ?>
              <tr>
                <td><strong><?= $m["equipe1_nom"] ?> vs <?= $m["equipe2_nom"] ?></strong></td>
                <td><?= $m["date_match"] ?> <?= substr($m["heure_match"], 0, 5) ?></td>
                <td><?= $m["lieu_match"] ?></td>
                <td><?= $m["prenom_user"] ?> <?= $m["nom_user"] ?> (<?= $m["email_user"] ?>)</td>
                <td style="display:flex; gap:8px; flex-wrap:wrap;">
                  <form method="POST">
                    <input type="hidden" name="action" value="match_status">
                    <input type="hidden" name="match_id" value="<?= $m["id_match"] ?>">
                    <input type="hidden" name="status" value="publie">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-check"></i> Accepter</button>
                  </form>

                  <form method="POST">
                    <input type="hidden" name="action" value="match_status">
                    <input type="hidden" name="match_id" value="<?= $m["id_match"] ?>">
                    <input type="hidden" name="status" value="refuse">
                    <button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark"></i> Refuser</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- USERS -->
    <div id="usersSection" class="dashboard-section" style="display:none;">
      <div class="section-head">
        <div>
          <h2>Utilisateurs (sans admin)</h2>
          <p>Activer / désactiver les comptes acheteurs et organisateurs</p>
        </div>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Etat</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($users) === 0): ?>
            <tr><td colspan="5" style="color:var(--muted);">Aucun utilisateur.</td></tr>
          <?php else: ?>
            <?php foreach ($users as $u): ?>
              <tr>
                <td><strong>#<?= $u["id_user"] ?></strong> <?= $u["prenom_user"] ?> <?= $u["nom_user"] ?></td>
                <td><?= $u["email_user"] ?></td>
                <td><?= $u["role_user"] ?></td>
                <td><?= $u["is_active"] ? "Actif" : "Désactivé" ?></td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="toggle_user">
                    <input type="hidden" name="user_id" value="<?= $u["id_user"] ?>">
                    <button class="btn btn-ghost" type="submit">
                      <?= $u["is_active"] ? "Désactiver" : "Activer" ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- STATS -->
    <div id="statsSection" class="dashboard-section" style="display:none;">
      <div class="section-head">
        <div>
          <h2>Statistiques</h2>
          <p>Global + détails par organisateur</p>
        </div>
      </div>

      <!-- Stats globales -->
      <div class="kpi" style="margin-bottom:14px;">
        <div class="k">
          <div class="label">Billets vendus (total)</div>
          <div class="value"><i class="fa-solid fa-ticket"></i><?= $globalBillets ?></div>
        </div>

        <div class="k">
          <div class="label">Chiffre d'affaires (total)</div>
          <div class="value"><i class="fa-solid fa-coins"></i><?= $globalCA ?> DH</div>
        </div>

        <div class="k">
          <div class="label">Billets vendus (aujourd'hui)</div>
          <div class="value"><i class="fa-solid fa-calendar-day"></i><?= ($todayBillets === null ? "-" : $todayBillets) ?></div>
        </div>

        <div class="k">
          <div class="label">CA (aujourd'hui)</div>
          <div class="value"><i class="fa-solid fa-sack-dollar"></i><?= ($todayCA === null ? "-" : $todayCA . " DH") ?></div>
        </div>
      </div>

      <div class="kpi" style="margin-bottom:14px;">
        <div class="k">
          <div class="label">Organisateurs</div>
          <div class="value"><i class="fa-solid fa-user-tie"></i><?= $nbOrgs ?></div>
        </div>
        <div class="k">
          <div class="label">Acheteurs</div>
          <div class="value"><i class="fa-solid fa-user"></i><?= $nbAch ?></div>
        </div>
        <div class="k">
          <div class="label">Top matchs</div>
          <div class="value"><i class="fa-solid fa-trophy"></i><?= count($topMatchs) ?></div>
        </div>
      </div>
      <div class="card" style="margin-top:14px;">
        <div class="card-top">
          <div class="card-title">Ventes d’un match (procédure)</div>
        </div>

        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
          <input type="hidden" name="section" value="stats">
          <select class="select" name="match_sales_id" required>
            <option value="">Choisir un match...</option>
            <?php foreach ($allMatches as $mm): ?>
              <option value="<?= (int)$mm["id_match"] ?>" <?= ($selectedMatchId === (int)$mm["id_match"] ? "selected" : "") ?>>
                #<?= (int)$mm["id_match"] ?> — <?= $mm["equipe1_nom"] ?> vs <?= $mm["equipe2_nom"] ?>
              </option>
            <?php endforeach; ?>
          </select>

          <button class="btn btn-primary" type="submit">
            Voir les ventes
          </button>
        </form>

        <?php if ($matchDetailsSales): ?>
          <div class="meta" style="margin-top:12px;">
            <span><i class="fa-solid fa-ticket"></i> Billets vendus: <strong><?= $matchDetailsSales["billets_vendus"] ?></strong></span>
            <span><i class="fa-solid fa-coins"></i> CA: <strong><?= $matchDetailsSales["chiffre_affaires"] ?> DH</strong></span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Top 5 matchs -->
      <div class="card" style="margin-bottom:14px;">
        <div class="card-top">
          <div class="card-title">Top 5 matchs (par chiffre d'affaires)</div>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>Match</th>
              <th>Billets</th>
              <th>CA</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($topMatchs) === 0): ?>
              <tr><td colspan="3" style="color:var(--muted);">Aucun match publié.</td></tr>
            <?php else: ?>
              <?php foreach ($topMatchs as $tm): ?>
                <tr>
                  <td><strong><?= $tm["equipe1_nom"] ?> vs <?= $tm["equipe2_nom"] ?></strong></td>
                  <td><?= $tm["billets_vendus"] ?></td>
                  <td><?= $tm["chiffre_affaires"] ?> DH</td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <!-- Commentaires (matchs publiés) -->
      <div class="card" style="margin-top:14px;">
        <div class="card-top">
          <div class="card-title">Commentaires (matchs publiés)</div>
          <div class="badge"><i class="fa-solid fa-comments"></i><?= count($adminComments) ?></div>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>Match</th>
              <th>Auteur</th>
              <th>Note</th>
              <th>Commentaire</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($adminComments) === 0): ?>
              <tr><td colspan="6" style="color:var(--muted);">Aucun commentaire sur les matchs publiés.</td></tr>
            <?php else: ?>
              <?php foreach ($adminComments as $c): ?>
                <tr>
                  <td>
                    <strong><?= $c["equipe1_nom"] ?> vs <?= $c["equipe2_nom"] ?></strong>
                    <div style="color:var(--muted); font-size:13px; margin-top:4px;">
                      <?= $c["lieu_match"] ?>
                    </div>
                  </td>
                  <td><?= $c["prenom_user"] ?> <?= $c["nom_user"] ?></td>
                  <td><?= ($c["note"] !== null ? $c["note"] : "-") ?></td>
                  <td style="max-width:420px;">
                    <div style="color:var(--muted); line-height:1.5;">
                      <?= $c["contenu"] ?>
                    </div>
                  </td>
                  <td><?= $c["created_at"] ?></td>
                  <td>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="action" value="delete_comment">
                      <input type="hidden" name="comment_id" value="<?= (int)$c["id_comment"] ?>">
                      <button class="btn btn-danger" type="submit"
                              onclick="return confirm('Supprimer ce commentaire ?');">
                        <i class="fa-solid fa-trash"></i> Supprimer
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>


      <!-- Liste Organisateurs (clic -> détails) -->
      <div class="grid" style="grid-template-columns: 1fr 1fr; gap:14px;">
        <div class="card">
          <div class="card-top">
            <div class="card-title">Organisateurs</div>
            <div class="badge"><i class="fa-solid fa-user-tie"></i><?= count($organisateurs) ?></div>
          </div>

          <div style="display:flex; flex-direction:column; gap:10px;">
            <?php if (count($organisateurs) === 0): ?>
              <div style="color:var(--muted);">Aucun organisateur.</div>
            <?php else: ?>
              <?php foreach ($organisateurs as $o): ?>
                <?php
                  $s = $orgStats[(int)$o["id_user"]] ?? null;
                  $miniCA = $s ? $s["chiffre_affaires"] : 0;
                  $miniTickets = $s ? $s["billets_vendus"] : 0;
                ?>
                <button
                  type="button"
                  class="btn btn-ghost"
                  style="justify-content:space-between; width:100%;"
                  onclick="showOrgStats(<?= (int)$o['id_user'] ?>)"
                >
                  <span>
                    <i class="fa-solid fa-user-tie"></i>
                    <?= $o["prenom_user"] ?> <?= $o["nom_user"] ?>
                  </span>
                  <span style="color:var(--muted); font-weight:800;">
                    <?= $miniTickets ?> billets | <?= $miniCA ?> DH
                  </span>
                </button>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-top">
            <div class="card-title">Détails organisateur</div>
            <div class="badge"><i class="fa-solid fa-circle-info"></i> Cliquez à gauche</div>
          </div>

          <!-- Ici, on met toutes les fiches détails cachées (JS affiche seulement une) -->
          <div id="orgStatsEmpty" style="color:var(--muted); line-height:1.7;">
            Sélectionnez un organisateur pour afficher ses statistiques.
          </div>

          <?php foreach ($organisateurs as $o): ?>
            <?php $s = $orgStats[(int)$o["id_user"]] ?? null; ?>
            <div class="org-stats-card" id="orgStats<?= (int)$o["id_user"] ?>" style="display:none;">
              <div style="font-weight:900; font-size:18px; margin-bottom:10px;">
                <?= $o["prenom_user"] ?> <?= $o["nom_user"] ?>
              </div>

              <div class="meta" style="margin-bottom:10px;">
                <span><i class="fa-solid fa-envelope"></i> <?= $o["email_user"] ?></span>
                <span><i class="fa-solid fa-circle"></i> <?= $o["is_active"] ? "Actif" : "Désactivé" ?></span>
              </div>

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="k">
                  <div class="label">Total matchs</div>
                  <div class="value"><i class="fa-solid fa-futbol"></i><?= $s ? $s["total_matchs"] : 0 ?></div>
                </div>
                <div class="k">
                  <div class="label">Matchs publiés</div>
                  <div class="value"><i class="fa-solid fa-circle-check"></i><?= $s ? $s["matchs_publies"] : 0 ?></div>
                </div>
                <div class="k">
                  <div class="label">En attente</div>
                  <div class="value"><i class="fa-solid fa-clock"></i><?= $s ? $s["matchs_en_attente"] : 0 ?></div>
                </div>
                <div class="k">
                  <div class="label">Refusés</div>
                  <div class="value"><i class="fa-solid fa-xmark"></i><?= $s ? $s["matchs_refuses"] : 0 ?></div>
                </div>
                <div class="k">
                  <div class="label">Billets vendus</div>
                  <div class="value"><i class="fa-solid fa-ticket"></i><?= $s ? $s["billets_vendus"] : 0 ?></div>
                </div>
                <div class="k">
                  <div class="label">Chiffre d'affaires</div>
                  <div class="value"><i class="fa-solid fa-coins"></i><?= $s ? $s["chiffre_affaires"] : 0 ?> DH</div>
                </div>
              </div>

              <div style="margin-top:12px; color:var(--muted);">
                Note moyenne : <strong style="color:var(--text);"><?= ($s && $s["note_moyenne"] !== null) ? $s["note_moyenne"] : "-" ?></strong>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Acheteurs (juste liste séparée) -->
      <div class="card" style="margin-top:14px;">
        <div class="card-top">
          <div class="card-title">Acheteurs</div>
          <div class="badge"><i class="fa-solid fa-user"></i><?= count($acheteurs) ?></div>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Email</th>
              <th>Etat</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($acheteurs) === 0): ?>
              <tr><td colspan="3" style="color:var(--muted);">Aucun acheteur.</td></tr>
            <?php else: ?>
              <?php foreach ($acheteurs as $a): ?>
                <tr>
                  <td><strong>#<?= $a["id_user"] ?></strong> <?= $a["prenom_user"] ?> <?= $a["nom_user"] ?></td>
                  <td><?= $a["email_user"] ?></td>
                  <td><?= $a["is_active"] ? "Actif" : "Désactivé" ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
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
