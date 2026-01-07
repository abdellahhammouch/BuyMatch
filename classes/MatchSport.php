<?php

class MatchSport
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getSalesFromView($matchId)
    {
        $stmt = $this->pdo->prepare("SELECT billets_vendus, chiffre_affaires FROM v_match_sales WHERE id_match = ?");
        $stmt->execute([$matchId]);
        return $stmt->fetch();
    }

    public function getSalesFromProcedure($matchId)
    {
        $stmt = $this->pdo->prepare("CALL sp_total_ventes_match(?)");
        $stmt->execute([$matchId]);
        $row = $stmt->fetch();

        $stmt->closeCursor();

        return $row;
    }

    public function getSalesByOrganizer($orgId)
    {
        $stmt = $this->pdo->prepare("SELECT IFNULL(SUM(v.billets_vendus), 0) AS billets_vendus,
                                        IFNULL(SUM(v.chiffre_affaires), 0) AS chiffre_affaires
                                    FROM v_match_sales v
                                    JOIN matchs m ON m.id_match = v.id_match
                                    WHERE m.organisateur_id = ?");
        $stmt->execute([$orgId]);
        return $stmt->fetch();
    }

    public function getTopPublishedMatches($limit)
    {
        $limit = (int)$limit;
        $stmt = $this->pdo->query("SELECT v.id_match, v.equipe1_nom, v.equipe2_nom, v.billets_vendus, v.chiffre_affaires
                                    FROM v_match_sales v
                                    JOIN matchs m ON m.id_match = v.id_match
                                    WHERE m.statut_match = 'publie'
                                    ORDER BY v.chiffre_affaires DESC
                                    LIMIT $limit");
        return $stmt->fetchAll();
    }

    public function getGlobalSales()
    {
        $stmt = $this->pdo->query("SELECT IFNULL(SUM(billets_vendus), 0) AS billets_vendus,
                                        IFNULL(SUM(chiffre_affaires), 0) AS chiffre_affaires
                                    FROM v_match_sales");
        return $stmt->fetch();
    }
}
