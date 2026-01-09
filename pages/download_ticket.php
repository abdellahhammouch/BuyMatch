<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../classes/Ticket.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];

$matchId = (int) ($_GET['match_id'] ?? 0);
$catId   = (int) ($_GET['categorie_id'] ?? 0);

// Dossier tickets
$dir = __DIR__ . '/../uploads/tickets';
if (!is_dir($dir)) {
    // si pas de permissions, ça échouera -> message clair
    if (!mkdir($dir, 0775, true)) {
        $_SESSION['error'] = "Impossible de créer le dossier uploads/tickets (permissions).";
        header('Location: acheteur_dashboard.php');
        exit;
    }
}

$baseDir = realpath($dir);
if (!$baseDir) {
    $_SESSION['error'] = "Dossier tickets introuvable.";
    header('Location: acheteur_dashboard.php');
    exit;
}

/**
 * 1) Si on reçoit match_id + categorie_id => on télécharge le PDF lié à cette ligne d'historique
 */
if ($matchId > 0 && $catId > 0) {

    // 1.a) Essayer de trouver un PDF déjà généré (dernier)
    $pattern = $baseDir . '/ticket_' . $matchId . '_' . $userId . '_' . $catId . '_*.pdf';
    $files = glob($pattern);

    $file = null;

    if (!empty($files)) {
        // Prendre le plus récent
        usort($files, function($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });
        $file = $files[0];
    } else {
        // 1.b) Sinon, générer le PDF à partir de la base
        $pdo = Database::getInstance();

        // user
        $stmtMe = $pdo->prepare("SELECT id_user, nom_user, prenom_user, email_user FROM users WHERE id_user = ? LIMIT 1");
        $stmtMe->execute([$userId]);
        $me = $stmtMe->fetch();

        // match
        $stmtMatch = $pdo->prepare("SELECT id_match, equipe1_nom, equipe2_nom, date_match, heure_match, lieu_match
                                    FROM matchs WHERE id_match = ? LIMIT 1");
        $stmtMatch->execute([$matchId]);
        $match = $stmtMatch->fetch();

        // category
        $categoryRepo = new Category($pdo);
        $cat = $categoryRepo->getByIdForMatch($catId, $matchId);

        // tickets (seats + codes)
        $stmtT = $pdo->prepare("SELECT place_numero, code_ticket
                                FROM tickets
                                WHERE acheteur_id = ? AND match_id = ? AND categorie_id = ?
                                ORDER BY place_numero ASC");
        $stmtT->execute([$userId, $matchId, $catId]);
        $rows = $stmtT->fetchAll();

        if (!$me || !$match || !$cat || count($rows) === 0) {
            $_SESSION['error'] = "PDF indisponible: aucun ticket trouvé pour cet achat.";
            header('Location: acheteur_dashboard.php');
            exit;
        }

        $seats = [];
        $codes = [];
        foreach ($rows as $r) {
            $seats[] = $r["place_numero"];
            $codes[] = $r["code_ticket"];
        }

        $ticketService = new Ticket($pdo);
        $pdfInfo = $ticketService->generatePurchasePdf($me, $match, $cat, $seats, $codes);

        $file = $pdfInfo["absolute"] ?? null;
    }

    // sécurité + envoi
    $fileReal = $file ? realpath($file) : null;
    if (!$fileReal || strpos($fileReal, $baseDir) !== 0 || !file_exists($fileReal)) {
        $_SESSION['error'] = "PDF introuvable.";
        header('Location: acheteur_dashboard.php');
        exit;
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($fileReal) . '"');
    header('Content-Length: ' . filesize($fileReal));
    readfile($fileReal);
    exit;
}

/**
 * 2) Sinon => ancien comportement (dernier PDF en session)
 */
$rel = $_SESSION['last_pdf'] ?? '';
if ($rel === '') {
    $_SESSION['error'] = "Aucun PDF disponible.";
    header('Location: acheteur_dashboard.php');
    exit;
}

$file = realpath(__DIR__ . '/../' . $rel);
if (!$file || strpos($file, $baseDir) !== 0 || !file_exists($file)) {
    $_SESSION['error'] = "PDF introuvable.";
    header('Location: acheteur_dashboard.php');
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
