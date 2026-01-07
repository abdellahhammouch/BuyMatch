<?php

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
}
