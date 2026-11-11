<?php

namespace App\Support;

class Catalogue
{
    /**
     * Adresse du site public du catalogue (lien « Voir le catalogue », bouton « Annuler » de la connexion).
     *
     * FRONTEND_URL vaut l'adresse locale en développement. Si elle pointe vers la machine locale
     * alors que le back-office est consulté en ligne (FRONTEND_URL absent ou recopié du .env local),
     * on renvoie l'adresse du catalogue en ligne : un téléphone ne peut pas ouvrir 127.0.0.1.
     */
    public static function frontendUrl(): string
    {
        $configured = (string) config('catalogue.frontend_url');

        if (self::isLocal(parse_url($configured, PHP_URL_HOST)) && ! self::isLocal(request()->getHost())) {
            return (string) config('catalogue.frontend_url_online');
        }

        return $configured;
    }

    private static function isLocal(?string $host): bool
    {
        return in_array($host, [null, '', '127.0.0.1', 'localhost', '::1', '0.0.0.0'], true);
    }
}
