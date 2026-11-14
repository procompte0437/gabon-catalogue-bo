<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Rubrique;
use App\Models\Setting;
use App\Models\SousRubrique;
use App\Services\ArticleImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Mode administrateur du catalogue (cadenas 🔒) : tout passe par le mot de passe général.
 */
class AdminCodeController extends Controller
{
    public function login(Request $request)
    {
        $this->assertMaster($request);

        return response()->json(['message' => 'Connecté en administrateur.']);
    }

    /** Codes des sous-rubriques d'une rubrique. */
    public function sousCodes(Request $request, Rubrique $rubrique)
    {
        $this->assertMaster($request);

        return response()->json([
            'codes' => $rubrique->sousRubriques()->orderBy('position')->get()
                ->map(fn (SousRubrique $s) => ['id' => $s->id, 'code' => $s->code])->values(),
        ]);
    }

    /** Codes des articles d'une sous-rubrique. */
    public function articleCodes(Request $request, SousRubrique $sousRubrique)
    {
        $this->assertMaster($request);

        return response()->json([
            'codes' => $sousRubrique->articles()->orderBy('position')->get()
                ->map(fn ($a) => ['id' => $a->id, 'title' => $a->title, 'code' => $a->code])->values(),
        ]);
    }

    /** Enregistre en une fois les codes des articles d'une sous-rubrique. */
    public function saveArticleCodes(Request $request, SousRubrique $sousRubrique)
    {
        $this->assertMaster($request);

        $codes = $request->input('codes');
        if (! is_array($codes)) {
            throw ValidationException::withMessages(['codes' => 'Liste des mots de passe manquante.']);
        }

        $invalid = 0;
        foreach ($sousRubrique->articles as $article) {
            if (! array_key_exists((string) $article->id, $codes) && ! array_key_exists($article->id, $codes)) {
                continue;
            }
            $value = trim((string) ($codes[$article->id] ?? $codes[(string) $article->id]));
            if (preg_match('/^\d{4}$/', $value)) {
                $article->code = $value;
                $article->save();
            } else {
                $invalid++;
            }
        }

        return response()->json([
            'message' => $invalid
                ? "Certains mots de passe n'étaient pas valides (4 chiffres requis) et n'ont pas été changés."
                : 'Mots de passe enregistrés.',
            'invalid' => $invalid,
        ]);
    }

    /**
     * Ajoute une rubrique à la fin du catalogue, avec ses sous-rubriques et leurs articles vides.
     * Seules les « sous_count » premières sous-rubriques sont visibles ; les autres se réaffichent
     * depuis le back-office.
     */
    public function storeRubrique(Request $request)
    {
        $this->assertMaster($request);

        $request->merge(['name' => trim((string) $request->input('name', ''))]);
        $maxSous = (int) config('catalogue.sous_rubriques');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'sous_count' => ['nullable', 'integer', 'min:1', 'max:'.$maxSous],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 40 caractères.',
            'sous_count.min' => 'Il faut au moins 1 sous-rubrique.',
            'sous_count.max' => "Pas plus de {$maxSous} sous-rubriques.",
            'sous_count.integer' => 'Le nombre de sous-rubriques doit être un nombre.',
        ]);

        $visible = (int) ($data['sous_count'] ?? 4);
        $articleCount = (int) config('catalogue.articles');
        $code = (string) config('catalogue.default_code');

        $rubrique = DB::transaction(function () use ($data, $visible, $maxSous, $articleCount, $code) {
            $rubrique = Rubrique::query()->create([
                'position' => ((int) Rubrique::query()->max('position')) + 1,
                'name' => $data['name'],
                'is_linked' => false,
            ]);

            $now = now();
            for ($position = 1; $position <= $maxSous; $position++) {
                $sous = SousRubrique::query()->create([
                    'rubrique_id' => $rubrique->id,
                    'position' => $position,
                    'name' => "Sous-rubrique {$position}",
                    'code' => $code,
                    'is_linked' => false,
                    'is_visible' => $position <= $visible,
                ]);

                $rows = [];
                for ($index = 1; $index <= $articleCount; $index++) {
                    $rows[] = [
                        'sous_rubrique_id' => $sous->id,
                        'position' => $index,
                        'title' => "Article {$index}",
                        'code' => $code,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                Article::query()->insert($rows);
            }

            return $rubrique;
        });

        return response()->json([
            'message' => 'Rubrique ajoutée.',
            'rubrique' => $rubrique->fresh()->toPublicArray(),
        ], 201);
    }

    /** Supprime une rubrique avec ses sous-rubriques, ses articles et leurs photos ; les suivantes remontent d'un rang. */
    public function destroyRubrique(Request $request, Rubrique $rubrique, ArticleImage $images)
    {
        $this->assertMaster($request);

        $paths = Article::query()
            ->whereIn('sous_rubrique_id', $rubrique->sousRubriques()->pluck('id'))
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->unique();

        DB::transaction(function () use ($rubrique) {
            $position = $rubrique->position;
            $rubrique->delete();

            // Un par un, dans l'ordre, pour respecter l'unicité des positions.
            Rubrique::query()->where('position', '>', $position)->orderBy('position')->get()
                ->each(function (Rubrique $next) {
                    $next->position = $next->position - 1;
                    $next->save();
                });
        });

        foreach ($paths as $path) {
            if (! Article::query()->where('image_path', $path)->exists()) {
                $images->delete($path);
            }
        }

        return response()->json([
            'message' => 'Rubrique supprimée.',
            'rubriques' => Rubrique::query()->orderBy('position')->get()->map->toPublicArray()->values(),
        ]);
    }

    private function assertMaster(Request $request): void
    {
        $given = trim((string) $request->input('code', ''));
        $expected = Setting::masterCode();

        if (strlen($given) !== strlen($expected) || ! hash_equals($expected, $given)) {
            throw ValidationException::withMessages(['code' => 'Mot de passe incorrect.']);
        }
    }
}
