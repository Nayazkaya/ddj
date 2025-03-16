<?php 

namespace DDJ\Services;

class Queries
{
    // Methode statique pour recuperer tous les resultats d'une table
    public static function obtenirTousLesResultats($table)
    {
        // Recuperer la connexion de la classe Database
        $db = Database::getInstance();
        
        // Construire la requete avec un nom de table dynamique
        $sql = "SELECT * FROM " . $table;
        
        // Preparer et executer la requete
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute();
        
        // Retourner tous les resultats sous forme de tableau associatif
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
