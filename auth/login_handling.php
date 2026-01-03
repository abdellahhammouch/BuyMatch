<?php
session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../classes/Auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$email    = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$role     = trim($_POST["role"] ?? "");

if ($email === "" || $password === "" || $role === "") {
    $_SESSION["error"] = "Email, mot de passe et rôle sont obligatoires.";
    header("Location: login.php");
    exit;
}

try {
    $pdo = Database::getInstance();

    $auth = new Auth($pdo);

    $result = $auth->login($email, $password, $role);

    if ($result["success"] === false) {
        $_SESSION["error"] = $result["message"];
        header("Location: login.php");
        exit;
    }

    header("Location: " . $result["redirect"]);
    exit;

} catch (PDOException $e) {
    $_SESSION["error"] = "Erreur base de données.";
    header("Location: login.php");
    exit;
}
