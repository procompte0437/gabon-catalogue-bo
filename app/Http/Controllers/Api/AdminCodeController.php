<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rubrique;
use App\Models\Setting;
use App\Models\SousRubrique;
use Illuminate\Http\Request;
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

    private function assertMaster(Request $request): void
    {
        $given = trim((string) $request->input('code', ''));
        $expected = Setting::masterCode();

        if (strlen($given) !== strlen($expected) || ! hash_equals($expected, $given)) {
            throw ValidationException::withMessages(['code' => 'Mot de passe incorrect.']);
        }
    }
}
