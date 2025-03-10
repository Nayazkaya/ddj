<?php

namespace DDJ;

class FreeMobileSMS
{
    /**
     * Envoie un SMS via l'API Free Mobile
     *
     * @param string $message Le message à envoyer
     * @return bool True si l'envoi est réussi, False sinon
     */
    public static function envoyerSMS(string $message): bool
    {
        // Charger la configuration
        $config = require $_SERVER['DOCUMENT_ROOT'] . '/config/configFree.php';

        // Vérifier la présence des identifiants Free Mobile
        if (
            empty($config['identifiant']) ||
            empty($config['cle'])
        ) {
            return false;
        }

        $identifiant = $config['identifiant'];
        $cle         = $config['cle'];

        // Construire l'URL d'appel à l'API
        $url = "https://smsapi.free-mobile.fr/sendmsg?user={$identifiant}&pass={$cle}&msg=" . urlencode($message);

        // Envoyer la requête
        $reponse = file_get_contents($url);

        return $reponse !== false;
    }
}
