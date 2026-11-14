<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rubrique « les ambassades du gabon » (position 12) : ce sont les ambassades des pays étrangers
 * installées AU Gabon (Libreville), pas les ambassades du Gabon à l'étranger.
 * La migration précédente avait mis des liens « ambassade du Gabon au/en … » : on les remplace.
 * Seuls les articles encore vides ou portant exactement l'ancien lien généré sont modifiés :
 * un article déjà corrigé à la main par l'admin n'est pas touché.
 */
return new class extends Migration
{
    // [ancien complément de la migration précédente, nouveau titre, lien]
    private const AMBASSADES = [
        1 => [ // les ambassades Africaines
            ['au Cameroun', 'Cameroun', 'Ambassade du Cameroun à Libreville'],
            ['au Congo Brazzaville', 'Congo', 'Ambassade de la République du Congo à Libreville'],
            ['en RD Congo', 'RD Congo', 'Ambassade de la RD Congo à Libreville'],
            ['en Guinée équatoriale', 'Guinée équatoriale', 'Ambassade de Guinée équatoriale à Libreville'],
            ['au Sénégal', 'Sénégal', 'Ambassade du Sénégal à Libreville'],
            ["en Côte d'Ivoire", "Côte d'Ivoire", "Ambassade de Côte d'Ivoire à Libreville"],
            ['au Maroc', 'Maroc', 'Ambassade du Maroc à Libreville'],
            ['en Afrique du Sud', 'Afrique du Sud', "Ambassade d'Afrique du Sud à Libreville"],
            ['au Nigeria', 'Nigeria', 'Ambassade du Nigeria à Libreville'],
            ['en Égypte', 'Égypte', "Ambassade d'Égypte à Libreville"],
        ],
        2 => [ // Ambassades occidentaux
            ['en France', 'France', 'https://ga.ambafrance.org'],
            ['en Belgique', 'Belgique', 'Ambassade de Belgique à Libreville'],
            ['en Allemagne', 'Allemagne', "Ambassade d'Allemagne à Libreville"],
            ['en Espagne', 'Espagne', "Ambassade d'Espagne à Libreville"],
            ['en Italie', 'Italie', "Ambassade d'Italie à Libreville"],
            ['au Royaume-Uni', 'Royaume-Uni', 'Haut-commissariat britannique à Libreville'],
            ['aux États-Unis', 'États-Unis', 'https://ga.usembassy.gov'],
            // Le Canada et la Suisse n'ont pas d'ambassade au Gabon (ils le couvrent depuis Yaoundé).
            ['au Canada', 'Union européenne', 'https://www.eeas.europa.eu/delegations/gabon_fr'],
            ['en Suisse', 'Brésil', 'Ambassade du Brésil à Libreville'],
            ['près le Saint-Siège', 'Saint-Siège', 'Nonciature apostolique à Libreville'],
        ],
    ];

    public function up(): void
    {
        $rubriqueId = DB::table('rubriques')->where('position', 12)->value('id');
        if (! $rubriqueId) {
            return;
        }

        foreach (self::AMBASSADES as $sousPosition => $pays) {
            $sousId = DB::table('sous_rubriques')
                ->where('rubrique_id', $rubriqueId)
                ->where('position', $sousPosition)
                ->value('id');
            if (! $sousId) {
                continue;
            }

            foreach ($pays as $index => [$ancien, $title, $cible]) {
                $ancienLien = 'https://www.google.com/search?q='.urlencode('ambassade du Gabon '.$ancien);
                $lien = str_starts_with($cible, 'https://')
                    ? $cible
                    : 'https://www.google.com/search?q='.urlencode($cible);

                DB::table('articles')
                    ->where('sous_rubrique_id', $sousId)
                    ->where('position', $index + 1)
                    ->where(function ($query) use ($ancienLien) {
                        $query->where('link', $ancienLien)
                            ->orWhere(fn ($vide) => $vide->whereNull('link')->where('title', 'like', 'Article %'));
                    })
                    ->update([
                        'title' => $title,
                        'link' => $lien,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Contenu : pas de retour en arrière.
    }
};
