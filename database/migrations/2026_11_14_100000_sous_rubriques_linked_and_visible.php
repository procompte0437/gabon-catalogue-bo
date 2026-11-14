<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Demande du client (14/11/2026) :
 *  - chaque sous-rubrique choisit seule si ses articles ouvrent un lien web (is_linked) ;
 *  - chaque rubrique ne garde qu'un certain nombre de sous-rubriques : les autres sont masquées
 *    (is_visible = false), jamais supprimées, et se réaffichent depuis le back-office ;
 *  - les liens des articles ne sont plus limités à 500 caractères.
 */
return new class extends Migration
{
    /**
     * Position de la rubrique => [nombre de sous-rubriques gardées, positions des sous-rubriques avec lien].
     * Les rubriques absentes (Espace liquidation, Locations/ventes, etc.) ne changent pas.
     */
    private const PLAN = [
        1 => [10, [1, 2, 3, 10]],  // Hôtels : liens sauf hôtels appartements (4-6) et motels (7-9)
        2 => [6, [1, 2, 3]],       // Transports : 3 avec lien, 3 simples
        3 => [3, []],              // Zoom : 3 simples
        4 => [4, [1, 2]],          // Restaurants : 2 avec lien, 2 simples
        5 => [4, []],              // Services médicaux : 4 simples
        6 => [2, []],              // Annonces : 2 simples
        7 => [2, []],              // Contacts d'urgences : 2 simples
        8 => [4, [4]],             // Entreprises : 3 simples, 1 lien
        9 => [4, [1, 2, 3, 4]],    // Banques : 4 liens
        10 => [4, [1, 2, 3, 4]],   // Universités : 4 liens
        12 => [2, [1, 2]],         // Ambassades : 2 liens
        13 => [3, []],             // Un technicien : 3 simples
    ];

    public function up(): void
    {
        Schema::table('sous_rubriques', function (Blueprint $table) {
            $table->boolean('is_linked')->default(false)->after('code');
            $table->boolean('is_visible')->default(true)->after('is_linked');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->text('link')->nullable()->change();
        });

        // Les rubriques qui ouvraient déjà des liens gardent ce mode sur toutes leurs sous-rubriques.
        $linkedRubriques = DB::table('rubriques')->where('is_linked', true)->pluck('id');
        DB::table('sous_rubriques')->whereIn('rubrique_id', $linkedRubriques)->update(['is_linked' => true]);

        foreach (self::PLAN as $position => [$keep, $links]) {
            $rubriqueId = DB::table('rubriques')->where('position', $position)->value('id');
            if (! $rubriqueId) {
                continue;
            }

            $sous = DB::table('sous_rubriques')->where('rubrique_id', $rubriqueId);
            (clone $sous)->update(['is_linked' => false]);
            (clone $sous)->where('position', '>', $keep)->update(['is_visible' => false]);
            if ($links) {
                (clone $sous)->whereIn('position', $links)->update(['is_linked' => true]);
            }
        }

        // rubriques.is_linked devient un résumé : « au moins une sous-rubrique visible avec lien ».
        foreach (DB::table('rubriques')->pluck('id') as $id) {
            DB::table('rubriques')->where('id', $id)->update([
                'is_linked' => DB::table('sous_rubriques')
                    ->where('rubrique_id', $id)->where('is_visible', true)->where('is_linked', true)->exists(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('sous_rubriques', function (Blueprint $table) {
            $table->dropColumn(['is_linked', 'is_visible']);
        });
    }
};
