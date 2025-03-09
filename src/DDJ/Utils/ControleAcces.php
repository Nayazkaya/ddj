<?php

namespace DDJ\Utils;

class ControleAcces {
    public static function verifierAcces() {
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

    public static function obtenirIpUtilisateur() {
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

    public static function estReseauPrive($ip) {
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
