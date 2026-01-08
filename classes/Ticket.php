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

    /**
     * Génère un PDF (1 seul fichier) pour l'achat.
     * Retourne : [ 'relative' => 'uploads/tickets/xxx.pdf', 'absolute' => '/var/.../uploads/tickets/xxx.pdf' ]
     */
    public function generatePurchasePdf($me, $match, $category, $seats, $codes)
    {
        // FPDF (PDF)
        // Tu dois mettre le fichier ici : lib/fpdf/fpdf.php
        require_once __DIR__ . '/../lib/fpdf/fpdf.php';

        $dir = __DIR__ . '/../uploads/tickets';

        $safeMatch = preg_replace('/[^a-zA-Z0-9_-]/', '_', $match['equipe1_nom'] . '_vs_' . $match['equipe2_nom']);
        $filename = 'ticket_' . (int)$match['id_match'] . '_' . (int)$me['id_user'] . '_' . time() . '_' . $safeMatch . '.pdf';

        $absolutePath = $dir . '/' . $filename;
        $relativePath = 'uploads/tickets/' . $filename;

        $pdf = new FPDF();
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'BuyMatch - Ticket', 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont('Arial', '', 12);
        $buyerName = trim(($me['prenom_user'] ?? '') . ' ' . ($me['nom_user'] ?? ''));
        $pdf->Cell(0, 8, 'Acheteur : ' . $buyerName, 0, 1);
        $pdf->Cell(0, 8, 'Email : ' . ($me['email_user'] ?? ''), 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Match', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $match['equipe1_nom'] . ' vs ' . $match['equipe2_nom'], 0, 1);
        $pdf->Cell(0, 8, 'Date : ' . $match['date_match'] . '  Heure : ' . substr($match['heure_match'], 0, 5), 0, 1);
        $pdf->Cell(0, 8, 'Lieu : ' . $match['lieu_match'], 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Categorie', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, ($category['nom_categorie'] ?? '') . ' - ' . ($category['prix_categorie'] ?? '') . ' DH', 0, 1);
        $pdf->Ln(4);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Billets', 0, 1);
        $pdf->SetFont('Arial', '', 11);

        for ($i = 0; $i < count($seats); $i++) {
            $line = 'Place: ' . $seats[$i] . '   |   Code: ' . ($codes[$i] ?? '');
            $pdf->Cell(0, 7, $line, 0, 1);
        }

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->MultiCell(0, 6, "Merci pour votre achat.\nCe PDF est votre preuve de ticket.");

        $pdf->Output('F', $absolutePath);

        return [
            'relative' => $relativePath,
            'absolute' => $absolutePath,
        ];
    }

    /**
     * Envoie un email + attache le PDF.
     * Retourne true si envoyé, false sinon.
     */
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
            $mail->SMTPSecure = $cfg['encryption']; // 'tls' ou 'ssl'
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
