<?php
namespace DDJ\Services;

use PDO;
use PDOException;

/**
 * Classe Database pour gérer la connexion à la base de données avec le pattern Singleton.
 */
class Database
{
    /**
     * Instance unique de la classe Database.
     *
     * @var Database|null
     */
    private static $instance = null;

    /**
     * Instance de PDO pour la connexion à la base de données.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * Constructeur privé pour empêcher l'instanciation directe.
     * Initialise la connexion à la base de données.
     *
     * @throws \Exception En cas d'échec de connexion à la base de données.
     */
    private function __construct()
    {
        // Utilisation d'un chemin absolu ou d'une constante pour éviter les problèmes de chemin relatif
        $config = require $_SERVER['DOCUMENT_ROOT'] . '/config/configDB.php';
        
        try {
            $this->pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8", 
                $config['username'], 
                $config['password']
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Utilisation de error_log pour enregistrer les erreurs
            error_log("Database connection error: " . $e->getMessage(), 3, $_SERVER['DOCUMENT_ROOT'] . 'db_error.log');
            throw new \Exception("Database connection error: " . $e->getMessage());
        }
    }

    /**
     * Retourne l'instance unique de la classe Database (Singleton).
     *
     * @return Database L'instance unique de Database.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retourne l'instance PDO pour effectuer des requêtes sur la base de données.
     *
     * @return PDO L'objet PDO connecté à la base de données.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
