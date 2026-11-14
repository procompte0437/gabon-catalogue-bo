<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ambassades (rubrique position 12) : le drapeau du pays comme photo (public/flags/xx.png)
 * et le site officiel de l'ambassade quand il existe (sinon la recherche Google reste).
 * On ne touche qu'aux articles dont le titre est encore le nom du pays posé par la migration
 * précédente ; une photo déjà importée par l'admin n'est pas remplacée.
 */
return new class extends Migration
{
    // [titre, code drapeau, site officiel ou null]
    private const AMBASSADES = [
        1 => [
            ['Cameroun', 'cm', null],
            ['Congo', 'cg', null],
            ['RD Congo', 'cd', null],
            ['Guinée équatoriale', 'gq', null],
            ['Sénégal', 'sn', null],
            ["Côte d'Ivoire", 'ci', null],
            ['Maroc', 'ma', null],
            ['Afrique du Sud', 'za', null],
            ['Nigeria', 'ng', null],
            ['Égypte', 'eg', null],
        ],
        2 => [
            ['France', 'fr', 'https://ga.diplomatie.gouv.fr'],
            ['Belgique', 'be', null],
            ['Allemagne', 'de', 'https://libreville.diplo.de'],
            ['Espagne', 'es', 'https://www.exteriores.gob.es/Embajadas/libreville'],
            ['Italie', 'it', 'https://amblibreville.esteri.it'],
            // Le Royaume-Uni suit le Gabon depuis son haut-commissariat de Yaoundé.
            ['Royaume-Uni', 'gb', 'https://www.gov.uk/world/organisations/british-high-commission-yaounde'],
            ['États-Unis', 'us', 'https://ga.usembassy.gov'],
            ['Union européenne', 'eu', 'https://www.eeas.europa.eu/delegations/gabon_fr'],
            ['Brésil', 'br', 'https://www.gov.br/mre/pt-br/embaixada-libreville'],
            ['Saint-Siège', 'va', null],
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

            foreach ($pays as $index => [$title, $flag, $site]) {
                $article = DB::table('articles')
                    ->where('sous_rubrique_id', $sousId)
                    ->where('position', $index + 1)
                    ->where('title', $title)
                    ->first();
                if (! $article) {
                    continue;
                }

                $changes = ['updated_at' => now()];
                if (! $article->image_path) {
                    $changes['image_path'] = 'flags/'.$flag.'.png';
                }
                // Le site officiel remplace la recherche Google (ou l'ancien lien) posée automatiquement.
                if ($site && (! $article->link || str_starts_with($article->link, 'https://www.google.com/search')
                    || in_array($article->link, ['https://ga.ambafrance.org'], true))) {
                    $changes['link'] = $site;
                }

                DB::table('articles')->where('id', $article->id)->update($changes);
            }
        }
    }

    public function down(): void
    {
        // Contenu : pas de retour en arrière.
    }
};
