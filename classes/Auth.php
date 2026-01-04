<?php

require_once __DIR__ . "/Session.php";
require_once __DIR__ . "/Acheteur.php";
require_once __DIR__ . "/Organisateur.php";
require_once __DIR__ . "/Admin.php";

class Auth
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function register($data)
    {
        $nom      = trim($data["nom"] ?? "");
        $prenom   = trim($data["prenom"] ?? "");
        $email    = trim($data["email"] ?? "");
        $phone    = trim($data["phone"] ?? "");
        $password = $data["password"] ?? "";
        $role     = trim($data["role"] ?? "");
        $photo    = trim($data["photo"] ?? "");

        if ($nom === "" || $prenom === "" || $email === "" || $password === "" || $role === "") {
            return ["ok" => false, "error" => "Champs obligatoires manquants."];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["ok" => false, "error" => "Email invalide."];
        }

        if (!in_array($role, ["acheteur", "organisateur", "admin"])) {
            return ["ok" => false, "error" => "Rôle invalide."];
        }

        $check = $this->pdo->prepare("SELECT id_user FROM users 
                                    WHERE email_user = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            return ["ok" => false, "error" => "Email déjà utilisé."];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO users (nom_user, prenom_user, email_user, phone_user, photo_user, password_user, role_user, is_active)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)");

        $ok = $stmt->execute([$nom, $prenom, $email, $phone, $photo, $hash, $role]);
        if (!$ok) {
            return ["ok" => false, "error" => "Erreur lors de l'inscription."];
        }

        return ["ok" => true, "user_id" => $this->pdo->lastInsertId()];
    }

    public function login(string $email, string $password, string $role): array
    {
        $stmt = $this->pdo->prepare("SELECT id_user, password_user, role_user, is_active
                                    FROM users
                                    WHERE email_user = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ["success" => false, "message" => "Email ou mot de passe incorrect."];
        }

        if (!$user["is_active"]) {
            return ["success" => false, "message" => "Compte désactivé."];
        }

        if ($user["role_user"] !== $role) {
            return ["success" => false, "message" => "Rôle incorrect."];
        }

        if (!password_verify($password, $user["password_user"])) {
            return ["success" => false, "message" => "Email ou mot de passe incorrect."];
        }

        $_SESSION["user_id"] = $user["id_user"];
        $_SESSION["role"]    = $user["role_user"];

        if ($role === "acheteur") {
            return ["success" => true, "redirect" => "/../pages/home.php"];
        }

        if ($role === "organisateur") {
            return ["success" => true, "redirect" => "/../organizer/create_match.php"];
        }

        if ($role === "admin") {
            return ["success" => true, "redirect" => "/../Admin/dashboard.php"];
        }

        return ["success" => true, "redirect" => "/../login.php"];
    }

    public function logout()
    {
        Session::destroy();
    }

    public function makeUserObject($row)
    {
        $role = $row["role_user"] ?? "";

        if ($role === "admin") return new Admin($row);
        if ($role === "organisateur") return new Organisateur($row);
        return new Acheteur($row);
    }

    public function requireRole($role)
    {
        if (!Session::has("user_id") || Session::get("role") !== $role) {
            return false;
        }
        return true;
    }
}
