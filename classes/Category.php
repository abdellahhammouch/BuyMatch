<?php

class Category
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 1) Lister toutes les catégories d’un match
    public function listByMatch($matchId)
    {
        $stmt = $this->pdo->prepare("SELECT id_categorie, match_id, nom_categorie, prix_categorie, places_max
                                    FROM categories
                                    WHERE match_id = ?
                                    ORDER BY prix_categorie ASC");
        $stmt->execute([$matchId]);
        return $stmt->fetchAll();
    }

    // 2) Récupérer une catégorie par ID
    public function getById($categoryId)
    {
        $stmt = $this->pdo->prepare("SELECT id_categorie, match_id, nom_categorie, prix_categorie, places_max
                                    FROM categories
                                    WHERE id_categorie = ?
                                    LIMIT 1");
        $stmt->execute([$categoryId]);
        $row = $stmt->fetch();
        return $row ? $row : null;
    }

    // 3) Récupérer une catégorie en vérifiant qu’elle appartient au match
    public function getByIdForMatch($categoryId, $matchId)
    {
        $stmt = $this->pdo->prepare("SELECT id_categorie, match_id, nom_categorie, prix_categorie, places_max
                                    FROM categories
                                    WHERE id_categorie = ? AND match_id = ?
                                    LIMIT 1");
        $stmt->execute([$categoryId, $matchId]);
        $row = $stmt->fetch();
        return $row ? $row : null;
    }

    // 4) Nombre de billets vendus pour une catégorie (dans un match)
    public function soldCount($matchId, $categoryId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*)
                                    FROM tickets
                                    WHERE match_id = ? AND categorie_id = ?");
        $stmt->execute([$matchId, $categoryId]);
        return (int)$stmt->fetchColumn();
    }

    // 5) Places restantes dans une catégorie
    public function remainingPlaces($matchId, $categoryId)
    {
        $cat = $this->getByIdForMatch($categoryId, $matchId);
        if (!$cat) return 0;

        $sold = $this->soldCount($matchId, $categoryId);
        $remaining = (int)$cat["places_max"] - $sold;

        return ($remaining > 0) ? $remaining : 0;
    }

    // 6) Créer une catégorie (utilisé dans create_match.php)
    public function create($matchId, $name, $price, $placesMax)
    {
        $stmt = $this->pdo->prepare("INSERT INTO categories (match_id, nom_categorie, prix_categorie, places_max)
                                    VALUES (?, ?, ?, ?)");
        $stmt->execute([$matchId, $name, $price, $placesMax]);

        return (int)$this->pdo->lastInsertId();
    }
}
