<?php

require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Ticket
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function countUserTicketsForMatch($userId, $matchId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tickets WHERE acheteur_id = ? AND match_id = ?");
        $stmt->execute([$userId, $matchId]);
        return (int)$stmt->fetchColumn();
    }

    public function generatePurchasePdf($me, $match, $category, $seats, $codes)
    {
        require_once __DIR__ . '/../lib/fpdf/fpdf.php';

        // Petit helper pour éviter les bugs d'accents avec FPDF (UTF-8)
        $t = function ($s) {
            return utf8_decode((string)$s);
        };

        $dir = __DIR__ . '/../uploads/tickets';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $safeMatch = preg_replace('/[^a-zA-Z0-9_-]/', '_', $match['equipe1_nom'] . '_vs_' . $match['equipe2_nom']);
        $filename = 'ticket_' . (int)$match['id_match'] . '_' . (int)$me['id_user'] . '_' . time() . '_' . $safeMatch . '.pdf';

        $absolutePath = $dir . '/' . $filename;
        $relativePath = 'uploads/tickets/' . $filename;

        // --------- Couleurs (theme) ----------
        // bg: #0b1220 -> (11, 18, 32)
        // panel: #0f1a2d -> (15, 26, 45)
        // soft: #111f36 -> (17, 31, 54)
        // accent red: #d71920 -> (215, 25, 32)
        // gold: #f5c542 -> (245, 197, 66)
        // text: clair -> (233, 238, 251)
        // muted -> (167, 178, 204)

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        // Fond global (bg)
        $pdf->SetFillColor(11, 18, 32);
        $pdf->Rect(0, 0, 210, 297, 'F');

        // Carte principale (panel)
        $cardX = 12;
        $cardY = 18;
        $cardW = 186;
        $cardH = 120;

        $pdf->SetFillColor(15, 26, 45);
        $pdf->Rect($cardX, $cardY, $cardW, $cardH, 'F');

        // Bordure accent rouge
        $pdf->SetDrawColor(215, 25, 32);
        $pdf->SetLineWidth(0.8);
        $pdf->Rect($cardX, $cardY, $cardW, $cardH);

        // Header rouge
        $pdf->SetFillColor(215, 25, 32);
        $pdf->Rect($cardX, $cardY, $cardW, 18, 'F');

        // Titre BuyMatch
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetXY($cardX + 8, $cardY + 5);
        $pdf->Cell(0, 8, $t('BuyMatch — Ticket'), 0, 1);

        // Badge GOLD (ex: "OFFICIAL")
        $pdf->SetFillColor(245, 197, 66);
        $pdf->SetTextColor(11, 18, 32);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($cardX + $cardW - 50, $cardY + 5);
        $pdf->Cell(42, 8, $t('OFFICIAL'), 0, 0, 'C', true);

        // Zone contenu
        $pdf->SetTextColor(233, 238, 251);

        $buyerName = trim(($me['prenom_user'] ?? '') . ' ' . ($me['nom_user'] ?? ''));
        $buyerEmail = $me['email_user'] ?? '';

        $matchTitle = ($match['equipe1_nom'] ?? '') . ' vs ' . ($match['equipe2_nom'] ?? '');
        $matchDate  = ($match['date_match'] ?? '') . '  ' . substr(($match['heure_match'] ?? ''), 0, 5);
        $matchLieu  = $match['lieu_match'] ?? '';

        $catName  = $category['nom_categorie'] ?? '';
        $catPrice = $category['prix_categorie'] ?? '';

        // Ligne de séparation "perforation"
        $perfY = $cardY + 74;
        $pdf->SetDrawColor(255, 255, 255);
        $pdf->SetLineWidth(0.2);
        for ($x = $cardX + 6; $x < $cardX + $cardW - 6; $x += 4) {
            $pdf->Line($x, $perfY, $x + 2, $perfY);
        }

        // Colonne gauche (détails)
        $leftX = $cardX + 8;
        $topDetailsY = $cardY + 24;

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY($leftX, $topDetailsY);
        $pdf->Cell(0, 7, $t('MATCH'), 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetX($leftX);
        $pdf->Cell(0, 7, $t($matchTitle), 0, 1);

        $pdf->SetTextColor(167, 178, 204);
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetX($leftX);
        $pdf->Cell(0, 6, $t('Date & Heure : ') . $t($matchDate), 0, 1);
        $pdf->SetX($leftX);
        $pdf->Cell(0, 6, $t('Lieu : ') . $t($matchLieu), 0, 1);

        // Catégorie (petite carte soft)
        $pdf->SetTextColor(233, 238, 251);
        $pdf->SetFillColor(17, 31, 54);
        $pdf->Rect($leftX, $cardY + 50, 110, 18, 'F');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY($leftX + 6, $cardY + 54);
        $pdf->Cell(0, 6, $t('Catégorie : ') . $t($catName), 0, 1);

        $pdf->SetFont('Arial', '', 11);
        $pdf->SetXY($leftX + 6, $cardY + 60);
        $pdf->Cell(0, 6, $t('Prix : ') . $t($catPrice) . $t(' DH'), 0, 1);

        // Acheteur (sous la perforation)
        $pdf->SetTextColor(233, 238, 251);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY($leftX, $perfY + 8);
        $pdf->Cell(0, 6, $t('ACHETEUR'), 0, 1);

        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(167, 178, 204);
        $pdf->SetX($leftX);
        $pdf->Cell(0, 6, $t($buyerName), 0, 1);
        $pdf->SetX($leftX);
        $pdf->Cell(0, 6, $t($buyerEmail), 0, 1);

        // Colonne droite (bloc code + places)
        $rightX = $cardX + 130;

        // Carte "QR" placeholder (simple)
        $pdf->SetFillColor(17, 31, 54);
        $pdf->Rect($rightX, $cardY + 24, 56, 44, 'F');
        $pdf->SetDrawColor(245, 197, 66);
        $pdf->Rect($rightX + 6, $cardY + 30, 44, 32);

        $pdf->SetTextColor(245, 197, 66);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($rightX + 6, $cardY + 24 + 2);
        $pdf->Cell(44, 6, $t('SCAN'), 0, 0, 'C');

        // Code principal (1er ticket)
        $mainCode = $codes[0] ?? '';
        $pdf->SetTextColor(233, 238, 251);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($rightX, $cardY + 70);
        $pdf->Cell(56, 6, $t('CODE'), 0, 1);

        $pdf->SetTextColor(167, 178, 204);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetX($rightX);
        $pdf->MultiCell(56, 5, $t($mainCode), 0, 'L');

        // Liste places (simple, lisible)
        $pdf->SetTextColor(233, 238, 251);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($rightX, $perfY + 8);
        $pdf->Cell(56, 6, $t('PLACES'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(167, 178, 204);

        $y = $perfY + 15;
        foreach ($seats as $i => $seat) {
            if ($y > $cardY + $cardH - 10) break; // sécurité
            $pdf->SetXY($rightX, $y);
            $pdf->Cell(56, 6, $t('• Place ') . $t($seat), 0, 1);
            $y += 6;
        }

        // Footer mini
        $pdf->SetTextColor(167, 178, 204);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetXY($cardX, $cardY + $cardH + 10);
        $pdf->MultiCell($cardW, 5, $t("Ce PDF est votre preuve d'achat. Conservez-le et présentez-le à l'entrée."), 0, 'C');

        $pdf->Output('F', $absolutePath);

        return [
            'relative' => $relativePath,
            'absolute' => $absolutePath,
        ];
    }


    public function sendTicketEmail($toEmail, $toName, $subject, $htmlBody, $pdfAbsolutePath)
    {
        $cfg = require __DIR__ . '/../config/mail.php';

        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = $cfg['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['username'];
            $mail->Password   = $cfg['password'];
            $mail->SMTPSecure = $cfg['encryption'];
            $mail->Port       = $cfg['port'];

            $mail->setFrom($cfg['from_email'], $cfg['from_name']);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;

            if (file_exists($pdfAbsolutePath)) {
                $mail->addAttachment($pdfAbsolutePath, basename($pdfAbsolutePath));
            }

            $mail->send();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }
}
