<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SousRubrique;
use App\Services\ArticleImage;
use App\Support\WebLink;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ClientContentController extends Controller
{
    public function updateSous(Request $request, SousRubrique $sousRubrique)
    {
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'code' => trim((string) $request->input('code', '')),
        ]);

        $data = $request->validate([
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'max:40'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 40 caractères.',
        ]);

        $this->assertCode($sousRubrique->code, $data['code']);

        $sousRubrique->name = $data['name'];
        $sousRubrique->save();

        return response()->json([
            'message' => 'Sous-rubrique enregistrée.',
            'sous_rubrique' => $sousRubrique->toPublicArray(),
        ]);
    }

    public function updateArticle(Request $request, Article $article, ArticleImage $images)
    {
        $article->load('sousRubrique.rubrique');

        $request->merge([
            'title' => trim((string) $request->input('title', '')),
            'code' => trim((string) $request->input('code', '')),
        ]);

        $data = $request->validate([
            'code' => ['required', 'string'],
            'title' => ['required', 'string', 'max:20'],
            'link' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:8192'],
        ], [
            'title.required' => 'Le titre est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 20 caractères.',
            'image.image' => 'Le fichier doit être une photo.',
            'image.max' => 'La photo ne peut pas dépasser 8 Mo.',
        ]);

        $this->assertCode($article->code, $data['code']);

        $article->title = $data['title'];

        if ($article->sousRubrique->rubrique->is_linked) {
            $article->link = WebLink::normalize($data['link'] ?? null);
        }

        if ($request->hasFile('image')) {
            try {
                $path = $images->store($request->file('image'));
            } catch (RuntimeException $exception) {
                throw ValidationException::withMessages([
                    'image' => $exception->getMessage(),
                ]);
            }

            $images->delete($article->image_path);
            $article->image_path = $path;
        }

        $article->save();

        return response()->json([
            'message' => 'Article enregistré.',
            'article' => $article->toPublicArray((bool) $article->sousRubrique->rubrique->is_linked),
        ]);
    }

    private function assertCode(string $expected, string $given): void
    {
        if (strlen($expected) !== strlen($given) || ! hash_equals($expected, $given)) {
            throw ValidationException::withMessages([
                'code' => 'Code incorrect.',
            ]);
        }
    }
}
