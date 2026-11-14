<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rubrique « les ambassades du gabon » (position 12) : ses deux sous-rubriques ouvrent des liens,
 * mais leurs articles étaient vides. On leur donne un pays et un lien de recherche vers l'ambassade
 * du Gabon dans ce pays (même principe que les banques).
 * Seuls les articles encore vides (titre « Article N », sans lien) sont remplis : rien n'est écrasé.
 */
return new class extends Migration
{
    private const AMBASSADES = [
        1 => [ // les ambassades Africaines
            ['Cameroun', 'au Cameroun'],
            ['Congo', 'au Congo Brazzaville'],
            ['RD Congo', 'en RD Congo'],
            ['Guinée équatoriale', 'en Guinée équatoriale'],
            ['Sénégal', 'au Sénégal'],
            ["Côte d'Ivoire", "en Côte d'Ivoire"],
            ['Maroc', 'au Maroc'],
            ['Afrique du Sud', 'en Afrique du Sud'],
            ['Nigeria', 'au Nigeria'],
            ['Égypte', 'en Égypte'],
        ],
        2 => [ // Ambassades occidentaux
            ['France', 'en France'],
            ['Belgique', 'en Belgique'],
            ['Allemagne', 'en Allemagne'],
            ['Espagne', 'en Espagne'],
            ['Italie', 'en Italie'],
            ['Royaume-Uni', 'au Royaume-Uni'],
            ['États-Unis', 'aux États-Unis'],
            ['Canada', 'au Canada'],
            ['Suisse', 'en Suisse'],
            ['Saint-Siège', 'près le Saint-Siège'],
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

            DB::table('sous_rubriques')->where('id', $sousId)->update(['is_linked' => true]);

            foreach ($pays as $index => [$title, $where]) {
                $query = 'ambassade du Gabon '.$where;

                DB::table('articles')
                    ->where('sous_rubrique_id', $sousId)
                    ->where('position', $index + 1)
                    ->whereNull('link')
                    ->where('title', 'like', 'Article %')
                    ->update([
                        'title' => $title,
                        'link' => 'https://www.google.com/search?q='.urlencode($query),
                        'updated_at' => now(),
                    ]);
            }
        }

        DB::table('rubriques')->where('id', $rubriqueId)->update(['is_linked' => true]);
    }

    public function down(): void
    {
        // Contenu : on ne revient pas en arrière (les articles ont pu être modifiés depuis).
    }
};
