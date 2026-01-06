<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../classes/Auth.php";
require_once __DIR__ . "/../classes/Session.php";
require_once __DIR__ . "/../config/database.php";

$pdo = Database::getInstance();
$auth = new Auth($pdo);
$auth->logout();

header("Location: login.php");
exit;

?>