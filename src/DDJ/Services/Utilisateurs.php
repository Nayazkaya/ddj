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
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user' => $user]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
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
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
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
    public function obtenirTousUtilisateurs()
    {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT id, user FROM utilisateurs");
        return $stmt->fetchAll();
    }

    /**
     * Ajoute un utilisateur dans la base de données.
     *
     * @param string $user Le nom d'utilisateur.
     * @param string $mdp Le mot de passe de l'utilisateur.
     */
    public function ajouterUtilisateur($user, $mdp)
    {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (user, mdp) VALUES (:user, :mdp)");
        $stmt->execute([
            'user' => $user,
            'mdp' => password_hash($mdp, PASSWORD_DEFAULT)
        ]);
    }

    /**
     * Modifie les informations d'un utilisateur existant dans la base de données.
     *
     * @param int $id L'ID de l'utilisateur à modifier.
     * @param string $user Le nouveau nom d'utilisateur.
     * @param string $mdp Le nouveau mot de passe de l'utilisateur.
     */
    public function modifierUtilisateur($id, $user, $mdp)
    {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("UPDATE utilisateurs SET user = :user, mdp = :mdp WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'user' => $user,
            'mdp' => password_hash($mdp, PASSWORD_DEFAULT)
        ]);
    }
}
