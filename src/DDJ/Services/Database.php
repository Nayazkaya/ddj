<?php
namespace DDJ\Services;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // Utilisation d'un chemin absolu ou d'une constante pour éviter les problèmes de chemin relatif
        $config = require $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';
        
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
            error_log("Database connection error: " . $e->getMessage(), 3, $_SERVER['DOCUMENT_ROOT'] . '/mnt/Systeme/Nexus/logs/db_error.log');
            throw new \Exception("Database connection error: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
