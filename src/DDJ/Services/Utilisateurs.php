<?php
namespace DDJ\Services;

use PDO;
use PDOException;

class Utilisateurs
{
    private $pdo;

    /**
     * Constructeur de la classe Utilisateurs.
     * Initialise la connexion à la base de données via l'instance singleton de la classe Database.
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Crée un nouvel utilisateur dans la base de données.
     *
     * @param string $user Le nom d'utilisateur.
     * @param string $mdp Le mot de passe de l'utilisateur.
     * @return bool True si l'utilisateur a été créé avec succès, sinon false.
     */
    public function creerUtilisateur(string $user, string $mdp): bool
    {
        // Hash du mot de passe avant de l'insérer dans la base de données
        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $sql = "INSERT INTO utilisateurs (user, mdp) VALUES (:user, :mdp)";

        try {
            // Préparation de la requête SQL et exécution
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['user' => $user, 'mdp' => $hash]);
        } catch (PDOException $e) {
            // Enregistrement des erreurs dans le log
            error_log("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les informations d'un utilisateur par son nom d'utilisateur.
     *
     * @param string $user Le nom d'utilisateur.
     * @return array|null Un tableau associatif contenant les informations de l'utilisateur ou null si non trouvé.
     */
    public function obtenirUtilisateur(string $user): ?array
    {
        $sql = "SELECT * FROM utilisateurs WHERE user = :user";

        try {
            // Préparation et exécution de la requête SQL
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user' => $user]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            // Enregistrement des erreurs dans le log
            error_log("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Met à jour les informations d'un utilisateur.
     *
     * @param int $id L'ID de l'utilisateur à mettre à jour.
     * @param string $user Le nouveau nom d'utilisateur.
     * @param string $mdp Le nouveau mot de passe de l'utilisateur.
     * @return bool True si la mise à jour a réussi, sinon false.
     */
    public function mettreAJourUtilisateur(int $id, string $user, string $mdp): bool
    {
        // Hash du nouveau mot de passe
        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $sql = "UPDATE utilisateurs SET user = :user, mdp = :mdp WHERE id = :id";

        try {
            // Préparation et exécution de la requête SQL
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id, 'user' => $user, 'mdp' => $hash]);
        } catch (PDOException $e) {
            // Enregistrement des erreurs dans le log
            error_log("Erreur lors de la mise à jour de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un utilisateur de la base de données.
     *
     * @param int $id L'ID de l'utilisateur à supprimer.
     * @return bool True si la suppression a réussi, sinon false.
     */
    public function supprimerUtilisateur(int $id): bool
    {
        $sql = "DELETE FROM utilisateurs WHERE id = :id";

        try {
            // Préparation et exécution de la requête SQL
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            // Enregistrement des erreurs dans le log
            error_log("Erreur lors de la suppression de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si le mot de passe fourni correspond à celui de l'utilisateur.
     *
     * @param string $user Le nom d'utilisateur.
     * @param string $mdp Le mot de passe à vérifier.
     * @return bool True si le mot de passe est correct, sinon false.
     */
    public function verifierMotDePasse(string $user, string $mdp): bool
    {
        // Récupère les informations de l'utilisateur
        $utilisateur = $this->obtenirUtilisateur($user);
        if ($utilisateur && password_verify($mdp, $utilisateur['mdp'])) {
            return true;
        }
        return false;
    }

    /**
     * Récupère tous les utilisateurs (ID et nom d'utilisateur) de la base de données.
     *
     * @return array Un tableau contenant les informations des utilisateurs (ID et nom d'utilisateur).
     */
    public function obtenirTousUtilisateurs(): array
    {
        try {
            // Exécution de la requête SQL pour récupérer tous les utilisateurs
            $stmt = $this->pdo->query("SELECT id, user FROM utilisateurs");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Enregistrement des erreurs dans le log
            error_log("Erreur lors de la récupération des utilisateurs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crée la table utilisateurs et ajoute l'utilisateur administrateur avec un mot de passe crypté.
     *
     * @return bool True si la création de la table et l'ajout de l'admin ont réussi, sinon false.
     */
    public function creerTableEtAjouterAdmin(): bool
    {
        // Création de la table utilisateurs
        $sql = "CREATE TABLE IF NOT EXISTS utilisateurs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user VARCHAR(255) NOT NULL,
            mdp VARCHAR(255) NOT NULL
        )";

        try {
            // Exécution de la requête de création de la table
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            // Ajouter l'utilisateur admin avec le mot de passe crypté
            $sqlInsert = "INSERT INTO utilisateurs (user, mdp) VALUES (:user, :mdp)";
            $stmtInsert = $this->pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                'user' => 'admin',
                'mdp' => '$2y$10$0oJi0aKWo67JzL2TmBRBq./kBjdKEiL9nPV9iffvwIpU.cMzfLxEi' // mot de passe crypté
            ]);

            return true;
        } catch (PDOException $e) {
            // Enregistrement des erreurs dans le log
            error_log("Erreur lors de la création de la table ou de l'ajout de l'admin: " . $e->getMessage());
            return false;
        }
    }
}
