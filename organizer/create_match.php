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

function uploadLogo($fileKey, $uploadDirRelative, &$errors)
{
    if (!isset($_FILES[$fileKey])) return null;
    if ($_FILES[$fileKey]["error"] === UPLOAD_ERR_NO_FILE) return null;

    if ($_FILES[$fileKey]["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "Erreur upload pour $fileKey.";
        return null;
    }

    $tmp  = $_FILES[$fileKey]["tmp_name"];
    $name = $_FILES[$fileKey]["name"];

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "webp"];

    if (!in_array($ext, $allowed)) {
        $errors[] = "Format logo invalide ($fileKey).";
        return null;
    }

    $uploadDirAbsolute = __DIR__ . "/../" . $uploadDirRelative;
    if (!is_dir($uploadDirAbsolute)) {
        $errors[] = "Le dossier '$uploadDirRelative' n'existe pas. Crée-le d'abord.";
        return null;
    }

    if (!is_writable($uploadDirAbsolute)) {
        $errors[] = "Le dossier '$uploadDirRelative' n'est pas accessible en écriture.";
        return null;
    }

    $newName = uniqid("team_", true) . "." . $ext;
    $destAbs = $uploadDirAbsolute . "/" . $newName;

    if (!move_uploaded_file($tmp, $destAbs)) {
        $errors[] = "Impossible de déplacer le fichier ($fileKey).";
        return null;
    }

    return $uploadDirRelative . "/" . $newName;
}

$old = [
    "equipe1_nom" => "",
    "equipe2_nom" => "",
    "date_match" => "",
    "heure_match" => "",
    "lieu_match" => "",
    "total_places" => "2000",
    "cat_name" => ["VIP", "Standard", "Économie"],
    "cat_price" => ["", "", ""],
    "cat_places" => ["", "", ""],
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $equipe1 = trim($_POST["equipe1_nom"] ?? "");
    $equipe2 = trim($_POST["equipe2_nom"] ?? "");
    $date    = trim($_POST["date_match"] ?? "");
    $heure   = trim($_POST["heure_match"] ?? "");
    $lieu    = trim($_POST["lieu_match"] ?? "");
    $places  = (int)($_POST["total_places"] ?? 0);

    $old["equipe1_nom"] = $equipe1;
    $old["equipe2_nom"] = $equipe2;
    $old["date_match"] = $date;
    $old["heure_match"] = $heure;
    $old["lieu_match"] = $lieu;
    $old["total_places"] = (string)$places;

    $catNames  = $_POST["cat_name"] ?? [];
    $catPrices = $_POST["cat_price"] ?? [];
    $catPlaces = $_POST["cat_places"] ?? [];

    $old["cat_name"] = $catNames;
    $old["cat_price"] = $catPrices;
    $old["cat_places"] = $catPlaces;

    $formErrors = [];

    // validations simples
    if ($equipe1 === "") $formErrors[] = "Nom équipe 1 obligatoire.";
    if ($equipe2 === "") $formErrors[] = "Nom équipe 2 obligatoire.";
    if ($date === "") $formErrors[] = "Date obligatoire.";
    if ($heure === "") $formErrors[] = "Heure obligatoire.";
    if ($lieu === "") $formErrors[] = "Lieu obligatoire.";
    if ($places < 100 || $places > 5000) $formErrors[] = "Capacité doit être entre 100 et 5000.";

    $logo1 = uploadLogo("logo1", "uploads/logos", $formErrors);
    $logo2 = uploadLogo("logo2", "uploads/logos", $formErrors);

    $catsToInsert = [];
    $sumCatPlaces = 0;

    for ($i = 0; $i < 3; $i++) {
        $n = trim($catNames[$i] ?? "");
        $p = trim($catPrices[$i] ?? "");
        $pl = trim($catPlaces[$i] ?? "");

        if ($n === "" && $p === "" && $pl === "") continue;

        if ($n === "" || $p === "" || $pl === "") {
            $formErrors[] = "Catégorie " . ($i + 1) . " incomplète (nom + prix + places).";
            continue;
        }

        $price = (float)$p;
        $placesCat = (int)$pl;

        if ($price < 0) $formErrors[] = "Prix invalide pour catégorie " . ($i + 1) . ".";
        if ($placesCat <= 0) $formErrors[] = "Places invalides pour catégorie " . ($i + 1) . ".";

        $sumCatPlaces += $placesCat;

        $catsToInsert[] = [
            "nom" => $n,
            "prix" => $price,
            "places" => $placesCat
        ];
    }

    if (!empty($catsToInsert) && $sumCatPlaces > $places) {
        $formErrors[] = "La somme des places des catégories dépasse la capacité totale.";
    }

    if (!empty($formErrors)) {
        $_SESSION["errors"] = $formErrors;
        header("Location: create_match.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO matchs
            (organisateur_id, equipe1_nom, equipe1_logo, equipe2_nom, equipe2_logo, date_match, heure_match, lieu_match, total_places, statut_match)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')
        ");

        $stmt->execute([
            $organisateurId,
            $equipe1,
            $logo1,
            $equipe2,
            $logo2,
            $date,
            $heure,
            $lieu,
            $places
        ]);

        $matchId = (int)$pdo->lastInsertId();

        if (!empty($catsToInsert)) {
            $stmtCat = $pdo->prepare("INSERT INTO categories (match_id, nom_categorie, prix_categorie, places_max)
                                      VALUES (?, ?, ?, ?)");
            foreach ($catsToInsert as $c) {
                $stmtCat->execute([$matchId, $c["nom"], $c["prix"], $c["places"]]);
            }
        }

        $pdo->commit();

        $_SESSION["success"] = "Demande envoyée avec succès. En attente de validation.";
        header("Location: organisateur_dashboard.php");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION["errors"] = ["Erreur DB: " . $e->getMessage()];
        header("Location: create_match.php");
        exit;
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BuyMatch | Organisateur - Créer match</title>
  <link rel="stylesheet" href="../assets/style.css" />
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

        <a href="create_match.php" class="active">
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
        <h2><i class="fa-solid fa-plus-circle"></i> Créer une demande de match</h2>
        <p style="margin:4px 0 0;color:var(--muted)">Le match sera validé par l'administrateur avant publication</p>
      </div>
      <span class="badge pending">
        <i class="fa-solid fa-clock"></i> En attente de validation
      </span>
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

    <div class="form">
      <form action="create_match.php" method="POST" enctype="multipart/form-data">

        <h3 style="margin:0 0 20px;display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-futbol"></i> Équipes
        </h3>

        <div class="form-row">
          <div class="field">
            <label><i class="fa-solid fa-shield"></i> Équipe 1 *</label>
            <input class="input" name="equipe1_nom" value="<?= $old["equipe1_nom"] ?>" placeholder="Nom de l'équipe 1" required />
          </div>
          <div class="field">
            <label><i class="fa-solid fa-shield"></i> Équipe 2 *</label>
            <input class="input" name="equipe2_nom" value="<?= $old["equipe2_nom"] ?>" placeholder="Nom de l'équipe 2" required />
          </div>
        </div>

        <div class="form-row" style="margin-top:12px;">
          <div class="field">
            <label><i class="fa-solid fa-image"></i> Logo Équipe 1 (optionnel)</label>
            <input class="input" type="file" name="logo1" accept="image/*" />
          </div>
          <div class="field">
            <label><i class="fa-solid fa-image"></i> Logo Équipe 2 (optionnel)</label>
            <input class="input" type="file" name="logo2" accept="image/*" />
          </div>
        </div>

        <h3 style="margin:24px 0 20px;display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-calendar-alt"></i> Date et lieu
        </h3>

        <div class="form-row">
          <div class="field">
            <label><i class="fa-solid fa-calendar-day"></i> Date du match *</label>
            <input class="input" type="date" name="date_match" value="<?= $old["date_match"] ?>" required />
          </div>
          <div class="field">
            <label><i class="fa-solid fa-clock"></i> Heure *</label>
            <input class="input" type="time" name="heure_match" value="<?= $old["heure_match"] ?>" required />
          </div>
        </div>

        <div class="form-row" style="margin-top:12px;">
          <div class="field">
            <label><i class="fa-solid fa-location-dot"></i> Lieu (Stade + Ville) *</label>
            <input class="input" name="lieu_match" value="<?= $old["lieu_match"] ?>" placeholder="Ex: Stade Mohammed V - Casablanca" required />
          </div>
          <div class="field">
            <label><i class="fa-solid fa-users"></i> Capacité totale (max 5000) *</label>
            <input class="input" type="number" name="total_places" min="100" max="5000" value="<?= $old["total_places"] ?>" required />
          </div>
        </div>

        <h3 style="margin:24px 0 20px;display:flex;align-items:center;gap:10px">
          <i class="fa-solid fa-tags"></i> Catégories et tarifs (max 3)
        </h3>

        <?php for ($i=0; $i<3; $i++): ?>
          <div class="form-row" style="margin-top:12px;">
            <div class="field">
              <label>Catégorie <?= $i+1 ?></label>
              <div style="display:grid;grid-template-columns:2fr 1fr;gap:10px">
                <input class="input" name="cat_name[]" value="<?= $old["cat_name"][$i] ?? "" ?>" placeholder="Nom (ex: VIP)" />
                <input class="input" type="number" name="cat_price[]" value="<?= $old["cat_price"][$i] ?? "" ?>" placeholder="Prix (DH)" min="0" />
              </div>
            </div>
            <div class="field">
              <label>Places</label>
              <input class="input" type="number" name="cat_places[]" value="<?= $old["cat_places"][$i] ?? "" ?>" placeholder="Nombre de places" min="0" />
            </div>
          </div>
        <?php endfor; ?>

        <div style="margin-top:24px; display:flex; gap:12px; flex-wrap:wrap;">
          <button class="btn btn-primary" type="submit">
            <i class="fa-solid fa-paper-plane"></i> Envoyer la demande
          </button>
          <button class="btn btn-ghost" type="button" onclick="location.href='organisateur_dashboard.php'">
            <i class="fa-solid fa-times"></i> Annuler
          </button>
        </div>

      </form>
    </div>

  </div>
</section>

<footer class="footer">
  <div class="container">© 2026 BuyMatch - Plateforme Organisateurs</div>
</footer>

<script src="../assets/script.js"></script>
</body>
</html>
