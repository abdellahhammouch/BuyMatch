<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../classes/Auth.php";
require_once __DIR__ . "/../classes/Session.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit;
}

$nom      = trim($_POST["nom"] ?? "");
$prenom   = trim($_POST["prenom"] ?? "");
$email    = trim($_POST["email"] ?? "");
$phone    = trim($_POST["phone"] ?? "");
$password = $_POST["password"] ?? "";
$confirm  = $_POST["confirmPassword"] ?? "";
$role     = $_POST["role"] ?? "";

$errors = [];

if ($nom === "") $errors[] = "Nom obligatoire";
if ($prenom === "") $errors[] = "Prénom obligatoire";
if ($email === "") $errors[] = "Email obligatoire";
if ($password === "") $errors[] = "Mot de passe obligatoire";
if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas";
if ($role !== "acheteur" && $role !== "organisateur") $errors[] = "Rôle invalide";

$photoPath = null;

if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES["photo"]["tmp_name"];
    $origName = $_FILES["photo"]["name"];

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "webp"];

    if (!in_array($ext, $allowed)) {
        $errors[] = "Format image invalide (jpg, jpeg, png, webp)";
    } else {
        $uploadDir = __DIR__ . "/../uploads/users";

        $newName = uniqid("user_", true) . "." . $ext;
        $dest = $uploadDir . "/" . $newName;

        if (move_uploaded_file($tmpName, $dest)) {
            $photoPath = "uploads/users/" . $newName;
        } else {
            $errors[] = "Upload Failed";
        }
    }
} else {
    $errors[] = "Photo obligatoire";
}

if (!empty($errors)) {
    $_SESSION["errors"] = $errors;
    header("Location: register.php");
    exit;
}

try {
    $pdo = Database::getInstance();
    $auth = new Auth($pdo);

    $result = $auth->register([
        "nom"      => $nom,
        "prenom"   => $prenom,
        "email"    => $email,
        "phone"    => $phone,
        "password" => $password,
        "role"     => $role,
        "photo"    => $photoPath
    ]);

    if (!empty($result["ok"])) {
        $_SESSION["success"] = "Inscription réussie. Connecte-toi maintenant.";
        header("Location: login.php");
        exit;
    }

    $_SESSION["errors"] = ["Inscription impossible (email déjà utilisé ?)"];
    header("Location: register.php");
    exit;

} catch (PDOException $e) {
    $_SESSION["errors"] = ["Erreur DB: " . $e->getMessage()];
    header("Location: register.php");
    exit;
}
