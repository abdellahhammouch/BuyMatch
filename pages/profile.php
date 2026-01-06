<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$pdo = Database::getInstance();
$userId = (int) $_SESSION["user_id"];
$role   = $_SESSION["role"] ?? "";

$errors = [];
$success = $_SESSION["success"] ?? null;
unset($_SESSION["success"]);

function fileUrlFromPages(string $dbPath): string {
    return "../" . ltrim($dbPath, "/");
}

$stmt = $pdo->prepare("SELECT id_user, nom_user, prenom_user, email_user, phone_user, photo_user, role_user
                       FROM users WHERE id_user = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_profile"])) {
    $nom    = trim($_POST["nom"] ?? "");
    $prenom = trim($_POST["prenom"] ?? "");
    $email  = trim($_POST["email"] ?? "");
    $phone  = trim($_POST["phone"] ?? "");

    if ($nom === "") $errors[] = "Nom obligatoire.";
    if ($prenom === "") $errors[] = "Prénom obligatoire.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email_user = ? AND id_user <> ?");
        $check->execute([$email, $userId]);
        if ((int)$check->fetchColumn() > 0) {
            $errors[] = "Cet email est déjà utilisé.";
        }
    }

    $photoPath = null;
    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES["photo"]["tmp_name"];
        $name = $_FILES["photo"]["name"];

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Photo: format invalide (jpg, jpeg, png, webp).";
        } else {
            $uploadDir = __DIR__ . "/../uploads/users";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $newName = uniqid("user_", true) . "." . $ext;
            $dest = $uploadDir . "/" . $newName;

            if (move_uploaded_file($tmp, $dest)) {
                $photoPath = "uploads/users/" . $newName;
            } else {
                $errors[] = "Upload photo échoué.";
            }
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE users
                SET nom_user = ?, prenom_user = ?, email_user = ?, phone_user = ?,
                    photo_user = COALESCE(?, photo_user)
                WHERE id_user = ?";

        $upd = $pdo->prepare($sql);
        $upd->execute([$nom, $prenom, $email, $phone, $photoPath, $userId]);

        $_SESSION["success"] = "Profil mis à jour avec succès.";
        header("Location: profile.php");
        exit;
    }
}

$stats = [];

if ($user["role_user"] === "organisateur") {
    $stmtM = $pdo->prepare("SELECT COUNT(*) FROM matchs WHERE organisateur_id = ?");
    $stmtM->execute([$userId]);
    $stats["matchs"] = (int)$stmtM->fetchColumn();

    $stmtSales = $pdo->prepare("
        SELECT COUNT(t.id_ticket) AS billets, COALESCE(SUM(t.prix_ticket),0) AS ca
        FROM tickets t
        JOIN matchs m ON m.id_match = t.match_id
        WHERE m.organisateur_id = ?
    ");
    $stmtSales->execute([$userId]);
    $row = $stmtSales->fetch();
    $stats["billets"] = (int)($row["billets"] ?? 0);
    $stats["ca"] = (float)($row["ca"] ?? 0);

} elseif ($user["role_user"] === "acheteur") {
    $stmtT = $pdo->prepare("SELECT COUNT(*) FROM tickets 
                            WHERE acheteur_id = ?");
    $stmtT->execute([$userId]);
    $stats["tickets"] = (int)$stmtT->fetchColumn();

    $stmtSpent = $pdo->prepare("SELECT COALESCE(SUM(prix_ticket),0) FROM tickets WHERE acheteur_id = ?");
    $stmtSpent->execute([$userId]);
    $stats["spent"] = (float)$stmtSpent->fetchColumn();

} elseif ($user["role_user"] === "admin") {
    $stmtU = $pdo->query("SELECT COUNT(*) FROM users");
    $stats["users"] = (int)$stmtU->fetchColumn();

    $stmtAllM = $pdo->query("SELECT COUNT(*) FROM matchs");
    $stats["matchs"] = (int)$stmtAllM->fetchColumn();
}

$returnLink = "../pages/home.php";
if ($user["role_user"] === "organisateur") $returnLink = "../organizer/organisateur_dashboard.php";
if ($user["role_user"] === "admin")        $returnLink = "../admin/dashboard.php";
if ($user["role_user"] === "acheteur")        $returnLink = "../pages/acheteur_dashboard.php";
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BuyMatch | Profil</title>
  <link rel="stylesheet" href="../assets/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topbar">
  <div class="container">
    <div class="nav">
      <a class="brand" href="<?= $returnLink ?>">
        <i class="fa-solid fa-ticket"></i><span>BuyMatch</span>
      </a>
      <div class="nav-actions">
        <a class="btn btn-ghost" href="<?= $returnLink ?>"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        <a class="btn btn-danger" href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Déconnexion</a>
      </div>
    </div>
  </div>
</header>

<section class="section">
  <div class="container">

    <div class="section-head">
      <div>
        <h2>Mon Profil</h2>
        <p>Informations du compte (<?= $user["role_user"] ?>)</p>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-triangle"></i>
        <ul>
          <?php foreach($errors as $e): ?>
            <li><?= $e ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success-message">
        <i class="fas fa-check-circle"></i> <?= $success ?>
      </div>
    <?php endif; ?>

    <div class="card" style="max-width:900px; margin: 0 auto;">
      <div style="display:flex; gap:16px; align-items:center; margin-bottom:14px;">
        <?php if (!empty($user["photo_user"])): ?>
          <img src="<?= fileUrlFromPages($user["photo_user"]) ?>" alt="Photo" style="width:90px;height:90px;border-radius:999px;object-fit:cover;border:1px solid rgba(255,255,255,.12);">
        <?php else: ?>
          <div style="width:90px;height:90px;border-radius:999px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);">
            <i class="fa-solid fa-user" style="font-size:30px;opacity:.7;"></i>
          </div>
        <?php endif; ?>

        <div>
          <h3 style="margin:0; font-weight:900;"><?= $user["prenom_user"] . " " . $user["nom_user"] ?></h3>
          <div class="meta" style="margin-top:6px;">
            <span><i class="fa-solid fa-envelope"></i> <?= $user["email_user"] ?></span>
            <span><i class="fa-solid fa-phone"></i> <?= $user["phone_user"] ?? "-" ?></span>
          </div>
        </div>
      </div>

      <?php if (!empty($stats)): ?>
        <div class="kpi" style="margin: 14px 0;">
          <?php foreach($stats as $k => $v): ?>
            <div class="k">
              <div class="label"><?= htmlspecialchars(strtoupper($k)) ?></div>
              <div class="value"><?= htmlspecialchars((string)$v) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="form">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="update_profile" value="1">

          <div class="form-row">
            <div class="field">
              <label>Nom</label>
              <input class="input" type="text" name="nom" value="<?= htmlspecialchars($user["nom_user"]) ?>" required>
            </div>
            <div class="field">
              <label>Prénom</label>
              <input class="input" type="text" name="prenom" value="<?= htmlspecialchars($user["prenom_user"]) ?>" required>
            </div>
          </div>

          <div class="form-row" style="margin-top:12px;">
            <div class="field">
              <label>Email</label>
              <input class="input" type="email" name="email" value="<?= htmlspecialchars($user["email_user"]) ?>" required>
            </div>
            <div class="field">
              <label>Téléphone</label>
              <input class="input" type="text" name="phone" value="<?= htmlspecialchars($user["phone_user"] ?? "") ?>">
            </div>
          </div>

          <div class="field" style="margin-top:12px;">
            <label>Changer la photo (optionnel)</label>
            <input class="input" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp">
          </div>

          <div style="margin-top:14px; display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;">
            <button class="btn btn-primary" type="submit">
              <i class="fa-solid fa-floppy-disk"></i> Enregistrer
            </button>
          </div>
        </form>
      </div>

    </div>

  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch</div>
</footer>

</body>
</html>
