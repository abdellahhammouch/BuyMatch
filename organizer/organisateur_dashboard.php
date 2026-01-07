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
$organisateurId = $_SESSION["user_id"];

/* ===== Supprimer un commentaire (organisateur -> فقط على match ديالو) ===== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_comment"])) {

    $commentId = (int)($_POST["comment_id"] ?? 0);

    if ($commentId <= 0) {
        $_SESSION["error"] = "Commentaire invalide.";
        header("Location: organisateur_dashboard.php");
        exit;
    }

    // vérifier que le commentaire appartient à un match de cet organisateur
    $stmtCheck = $pdo->prepare("SELECT c.id_comment
                                FROM comments c
                                JOIN matchs m ON m.id_match = c.match_id
                                WHERE c.id_comment = ? AND m.organisateur_id = ?
                                LIMIT 1");
    $stmtCheck->execute([$commentId, $organisateurId]);
    $ok = $stmtCheck->fetch();

    if (!$ok) {
        $_SESSION["error"] = "Vous ne pouvez pas supprimer ce commentaire.";
        header("Location: organisateur_dashboard.php");
        exit;
    }

    // supprimer
    $stmtDel = $pdo->prepare("DELETE FROM comments WHERE id_comment = ? LIMIT 1");
    $stmtDel->execute([$commentId]);

    $_SESSION["success"] = "Commentaire supprimé.";
    header("Location: organisateur_dashboard.php");
    exit;
}


$success = $_SESSION["success"] ?? null;
$error   = $_SESSION["error"] ?? null;
$errors  = $_SESSION["errors"] ?? [];
unset($_SESSION["success"], $_SESSION["error"], $_SESSION["errors"]);

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
$totalMatchs = $stmtTotal->fetchColumn();

$stmtPending = $pdo->prepare("SELECT COUNT(*) FROM matchs WHERE organisateur_id = ? AND statut_match = 'en_attente'");
$stmtPending->execute([$organisateurId]);
$pendingMatchs = $stmtPending->fetchColumn();

$stmtPub = $pdo->prepare("SELECT COUNT(*) FROM matchs WHERE organisateur_id = ? AND statut_match = 'publie'");
$stmtPub->execute([$organisateurId]);
$publishedMatchs = $stmtPub->fetchColumn();

$stmtRef = $pdo->prepare("SELECT COUNT(*) FROM matchs WHERE organisateur_id = ? AND statut_match = 'refuse'");
$stmtRef->execute([$organisateurId]);
$refusedMatchs = $stmtRef->fetchColumn();

$stmtSales = $pdo->prepare("SELECT  COUNT(t.id_ticket) AS billets_vendus,
                                    COALESCE(SUM(t.prix_ticket), 0) AS chiffre_affaires
                            FROM matchs m
                            LEFT JOIN tickets t ON t.match_id = m.id_match
                            WHERE m.organisateur_id = ?");
$stmtSales->execute([$organisateurId]);
$sales = $stmtSales->fetch();

$billetsVendus = $sales["billets_vendus"] ?? 0;
$chiffreAffaires = $sales["chiffre_affaires"] ?? 0;

$stmtMatchs = $pdo->prepare("SELECT m.id_match, m.equipe1_nom, m.equipe2_nom, 
                                    m.date_match, m.heure_match, m.lieu_match, m.statut_match,
                                    COUNT(t.id_ticket) AS billets_vendus,
                                    COALESCE(SUM(t.prix_ticket), 0) AS chiffre_affaires
                            FROM matchs m
                            LEFT JOIN tickets t ON t.match_id = m.id_match
                            WHERE m.organisateur_id = ?
                            GROUP BY m.id_match
                            ORDER BY m.date_match DESC, m.heure_match DESC
                            LIMIT 10");
$stmtMatchs->execute([$organisateurId]);
$matchs = $stmtMatchs->fetchAll();

$stmtComments = $pdo->prepare("SELECT c.id_comment, c.note, c.contenu, c.created_at,
                                    u.nom_user, u.prenom_user,
                                    m.id_match, m.equipe1_nom, m.equipe2_nom
                                FROM comments c
                                JOIN matchs m ON m.id_match = c.match_id
                                JOIN users u ON u.id_user = c.user_id
                                WHERE m.organisateur_id = ?
                                ORDER BY c.created_at DESC
                                LIMIT 5");
$stmtComments->execute([$organisateurId]);
$comments = $stmtComments->fetchAll();

function statutLabel($statut) {
    if ($statut === "publie") return "Publié";
    if ($statut === "refuse") return "Refusé";
    return "En attente";
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BuyMatch | Dashboard Organisateur</title>
    <link rel="stylesheet" href="../assets/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <header class="topbar">
    <div class="container">
        <div class="nav">
        <a class="brand" href="organisateur_dashboard.php">
            <i class="fa-solid fa-ticket"></i>
            <span>BuyMatch</span>
        </a>

        <!-- Liens de navigation Organisateur -->
        <div class="navlinks">
            <a href="organisateur_dashboard.php" class="active">
                <i class="fa-solid fa-gauge"></i> Dashboard
            </a>

            <a href="create_match.php">
                <i class="fa-solid fa-circle-plus"></i> Créer un match
            </a>

            <a href="mes-matchs.php">
                <i class="fa-solid fa-futbol"></i> Mes matchs
            </a>

            <a href="stats.php">
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
        <h2>Dashboard Organisateur</h2>
        <p>Bienvenue, <?= $org["prenom_user"] . " " . $org["nom_user"] ?></p>
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

    <!-- Profil rapide -->
        <div class="card" style="margin-bottom:14px;">
        <div style="display:flex; gap:14px; align-items:center;">
            <div style="width:64px; height:64px; border-radius:50%; overflow:hidden; border:1px solid var(--line); background:rgba(255,255,255,.04); display:flex; align-items:center; justify-content:center;">
            <?php if (!empty($org["photo_user"])): ?>
                <img src="../<?= $org["photo_user"] ?>" alt="Photo" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                <i class="fa-solid fa-user" style="font-size:24px; color:rgba(255,255,255,.6)"></i>
            <?php endif; ?>
            </div>

            <div>
            <div style="font-weight:900; font-size:18px;"><?= $org["prenom_user"] . " " . $org["nom_user"] ?></div>
            <div class="meta" style="margin-top:6px;">
                <span><i class="fa-solid fa-envelope"></i> <?= $org["email_user"] ?></span>
                <span><i class="fa-solid fa-phone"></i> <?= $org["phone_user"] ?? "-" ?></span>
            </div>
            </div>
        </div>
        </div>

    <!-- KPI -->
    <div class="kpi" style="margin-bottom:14px;">
        <div class="k"><div class="label">Total matchs</div><div class="value"><i class="fa-solid fa-futbol"></i><?= $totalMatchs ?></div></div>
        <div class="k"><div class="label">En attente</div><div class="value"><i class="fa-solid fa-clock"></i><?= $pendingMatchs ?></div></div>
        <div class="k"><div class="label">Publiés</div><div class="value"><i class="fa-solid fa-circle-check"></i><?= $publishedMatchs ?></div></div>
        <div class="k"><div class="label">Chiffre d'affaires</div><div class="value"><i class="fa-solid fa-coins"></i><?= $chiffreAffaires ?> DH</div></div>
    </div>

    <!-- Matchs -->
        <div class="section-head" style="margin-top:10px;">
        <div>
            <h2>Mes matchs (10 derniers)</h2>
            <p>Billets vendus et statut</p>
        </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                <th>Match</th>
                <th>Date</th>
                <th>Lieu</th>
                <th>Statut</th>
                <th>Billets</th>
                <th>CA</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($matchs) === 0): ?>
                <tr><td colspan="6" style="color:var(--muted);">Aucun match pour le moment.</td></tr>
                <?php else: ?>
                <?php foreach ($matchs as $m): ?>
                    <tr>
                    <td><strong><?= $m["equipe1_nom"] . " vs " . $m["equipe2_nom"] ?></strong></td>
                    <td><?= $m["date_match"] ?> <?= substr($m["heure_match"], 0, 5) ?></td>
                    <td><?= $m["lieu_match"] ?></td>
                    <td><?= statutLabel($m["statut_match"]) ?></td>
                    <td><?= $m["billets_vendus"] ?></td>
                    <td><?= $m["chiffre_affaires"] ?> DH</td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="section-head" style="margin-top:18px;">
        <div>
            <h2>Derniers commentaires</h2>
            <p>Sur vos matchs</p>
        </div>
        </div>

        <div class="grid">
        <?php if (count($comments) === 0): ?>
            <div class="card" style="color:var(--muted);">Aucun commentaire pour le moment.</div>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
            <div class="card">
                <div class="card-top">
                <div class="card-title"><?= $c["equipe1_nom"] . " vs " . $c["equipe2_nom"] ?></div>
                <div class="badge"><i class="fa-solid fa-star"></i><?= $c["note"] ?? "-" ?></div>
                </div>
                <div class="meta" style="margin-bottom:10px;">
                <span><i class="fa-solid fa-user"></i> <?= $c["prenom_user"] . " " . $c["nom_user"] ?></span>
                <span><i class="fa-solid fa-clock"></i> <?= $c["created_at"] ?></span>
                </div>
                <div style="color:var(--muted); line-height:1.6;">
                <?= $c["contenu"] ?>
                </div>
                <form method="POST" action="organisateur_dashboard.php" style="margin-top:12px; display:flex; justify-content:flex-end;">
                    <input type="hidden" name="comment_id" value="<?= (int)$c["id_comment"] ?>">
                    <button class="btn btn-danger btn-sm" type="submit" name="delete_comment" value="1"
                            onclick="return confirm('Supprimer ce commentaire ?');">
                        <i class="fa-solid fa-trash"></i> Supprimer
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>

        </div>
    </section>

<footer class="footer">
    <div class="container">© 2026 BuyMatch</div>
</footer>

</body>
</html>
