<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class WebLink
{
    public static function normalize(?string $link): ?string
    {
        $link = trim((string) $link);

        if ($link === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $link)) {
            $link = 'https://'.$link;
        }

        if (! filter_var($link, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages([
                'link' => 'Le lien du site n’est pas valide.',
            ]);
        }

        return $link;
    }
}
