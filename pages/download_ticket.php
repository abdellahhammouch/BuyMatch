<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$rel = $_SESSION['last_pdf'] ?? '';
if ($rel === '') {
    $_SESSION['error'] = "Aucun PDF disponible.";
    header('Location: acheteur_dashboard.php');
    exit;
}

// sécurité : on force uploads/tickets
$baseDir = realpath(__DIR__ . '/../uploads/tickets');
$file = realpath(__DIR__ . '/../' . $rel);

if (!$file || !$baseDir || strpos($file, $baseDir) !== 0 || !file_exists($file)) {
    $_SESSION['error'] = "PDF introuvable.";
    header('Location: acheteur_dashboard.php');
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($file));

readfile($file);
exit;
