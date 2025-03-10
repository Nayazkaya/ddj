<?php

namespace DDJ\Utils;

/**
 * Classe ControleAcces pour gérer l'accès des utilisateurs en fonction de leur adresse IP.
 */
class ControleAcces 
{
    /**
     * Vérifie l'accès de l'utilisateur en fonction de son adresse IP.
     * Si l'IP appartient à un réseau privé, l'utilisateur est redirigé vers le tableau de bord.
     * Sinon, il est redirigé vers la page de connexion.
     *
     * @return void
     */
    public static function verifierAcces(): void 
    {
        session_start(); // Démarrer la session avant toute modification de $_SESSION
        $ip_client = self::obtenirIpUtilisateur();

        // Bloquer les tentatives d'usurpation via un proxy non local
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && 
            !preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1]))/', $_SERVER['HTTP_X_FORWARDED_FOR'])) {
            header('Location: login.php');
            exit;
        }

        // Vérifier si l'IP appartient à un réseau privé
        if (self::estReseauPrive($ip_client)) {
            $_SESSION['user'] = 1;
            header('Location: dashboard.php');
        } else {
            header('Location: login.php');
        }
        exit;
    }

    /**
     * Obtient l'adresse IP de l'utilisateur en vérifiant plusieurs sources.
     *
     * @return string L'adresse IP de l'utilisateur ou '0.0.0.0' si aucune IP valide n'est trouvée.
     */
    public static function obtenirIpUtilisateur(): string 
    {
        $ip_sources = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ip_sources as $key) {
            if (!empty($_SERVER[$key])) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Vérifie si une adresse IP appartient à un réseau privé.
     *
     * @param string $ip L'adresse IP à vérifier.
     * @return bool True si l'IP est privée, False sinon.
     */
    public static function estReseauPrive(string $ip): bool 
    {
        $ip_long = ip2long($ip);
        if ($ip_long === false) {
            return false; // Éviter les erreurs si l'IP est invalide
        }
        
        return 
            ($ip_long >= ip2long('10.0.0.0') && $ip_long <= ip2long('10.255.255.255')) || 
            ($ip_long >= ip2long('172.16.0.0') && $ip_long <= ip2long('172.31.255.255')) || 
            ($ip_long >= ip2long('192.168.0.0') && $ip_long <= ip2long('192.168.255.255'));
    }
}
