<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Rubrique;
use App\Models\Setting;
use App\Models\SousRubrique;
use App\Services\ArticleImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contenu initial du catalogue (noms des rubriques, sous-rubriques renommées, articles et photos),
 * repris de l'ancienne version Firebase. Les photos vont dans la table article_images.
 *
 * Ne s'applique qu'une fois (réglage « import_initial_fait ») pour ne jamais écraser
 * les modifications faites ensuite dans le back-office. Pour forcer : php artisan db:seed --class=CatalogueContentSeeder --force
 * après avoir supprimé ce réglage.
 */
class CatalogueContentSeeder extends Seeder
{
    public function run(ArticleImage $images): void
    {
        if (Setting::get('import_initial_fait') === '1') {
            $this->command?->info('Contenu initial déjà importé : rien à faire.');

            return;
        }

        $data = json_decode((string) file_get_contents(__DIR__.'/data/catalogue_initial.json'), true);

        DB::transaction(function () use ($data, $images) {
            $rubriques = Rubrique::query()->get()->keyBy('position');

            foreach ($data['rubriques'] as $row) {
                $rubriques->get($row['position'])?->update(['name' => $row['name']]);
            }

            foreach ($data['sous_rubriques'] as $row) {
                $this->sous($rubriques, $row['rubrique'], $row['position'])
                    ?->update(['name' => $row['name'], 'code' => $row['code']]);
            }

            foreach ($data['articles'] as $row) {
                $sous = $this->sous($rubriques, $row['rubrique'], $row['sous_rubrique']);
                $article = $sous ? Article::query()
                    ->where('sous_rubrique_id', $sous->id)
                    ->where('position', $row['position'])
                    ->first() : null;

                if (! $article) {
                    continue;
                }

                $article->title = $row['title'];
                $article->code = $row['code'];
                $article->link = $row['link'];

                if ($row['image_base64']) {
                    $article->image_path = $images->storeBytes((string) base64_decode($row['image_base64'], true));
                }

                $article->save();
            }

            Setting::put('import_initial_fait', '1');
        });

        $this->command?->info(sprintf(
            'Contenu initial importé : %d rubriques, %d sous-rubriques, %d articles.',
            count($data['rubriques']),
            count($data['sous_rubriques']),
            count($data['articles']),
        ));
    }

    private function sous($rubriques, int $rubriquePosition, int $position): ?SousRubrique
    {
        $rubrique = $rubriques->get($rubriquePosition);

        return $rubrique
            ? SousRubrique::query()->where('rubrique_id', $rubrique->id)->where('position', $position)->first()
            : null;
    }
}
