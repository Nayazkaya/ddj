<?php
namespace DDJ\Services;

use PDO;
use PDOException;
use DDJ\Services\Database;

class Utilisateurs
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function creerUtilisateur(string $user, string $mdp): bool
    {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $sql = "INSERT INTO utilisateurs (user, mdp) VALUES (:user, :mdp)";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['user' => $user, 'mdp' => $hash]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    public function obtenirUtilisateur(string $user): ?array
    {
        $sql = "SELECT * FROM utilisateurs WHERE user = :user";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user' => $user]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
            return null;
        }
    }

    public function mettreAJourUtilisateur(int $id, string $user, string $mdp): bool
    {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $sql = "UPDATE utilisateurs SET user = :user, mdp = :mdp WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id, 'user' => $user, 'mdp' => $hash]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    public function supprimerUtilisateur(int $id): bool
    {
        $sql = "DELETE FROM utilisateurs WHERE id = :id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    public function verifierMotDePasse(string $user, string $mdp): bool
    {
        $utilisateur = $this->obtenirUtilisateur($user);
        return $utilisateur ? password_verify($mdp, $utilisateur['mdp']) : false;
    }

    public function obtenirTousUtilisateurs(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT id, user FROM utilisateurs");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des utilisateurs: " . $e->getMessage());
            return [];
        }
    }

    public static function creerTableEtAjouterAdmin(): bool
    {
        $pdo = Database::getInstance()->getConnection();

        $sql = "CREATE TABLE IF NOT EXISTS utilisateurs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user VARCHAR(255) NOT NULL UNIQUE,
            mdp VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') NOT NULL DEFAULT 'user'
        )";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $sqlInsert = "INSERT INTO utilisateurs (user, mdp, role) 
                          VALUES ('admin', '$2y$10$0oJi0aKWo67JzL2TmBRBq./kBjdKEiL9nPV9iffvwIpU.cMzfLxEi', 'admin')
                          ON DUPLICATE KEY UPDATE user=user";

            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de la table ou de l'ajout de l'admin: " . $e->getMessage());
            return false;
        }
    }
}
