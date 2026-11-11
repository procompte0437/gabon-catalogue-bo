<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleImage;
use App\Support\WebLink;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ArticleController extends Controller
{
    public function edit(Article $article)
    {
        $article->load('sousRubrique.rubrique');

        return view('admin.articles.edit', [
            'article' => $article,
        ]);
    }

    public function update(Request $request, Article $article, ArticleImage $images)
    {
        $article->load('sousRubrique.rubrique');

        $request->merge([
            'title' => trim((string) $request->input('title', '')),
            'code' => trim((string) $request->input('code', '')),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:20'],
            'code' => ['required', 'regex:/^\d{4}$/'],
            'link' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'max:8192'],
        ], [
            'title.required' => 'Le titre est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser 20 caractères.',
            'code.regex' => 'Le mot de passe doit contenir exactement 4 chiffres.',
            'image.image' => 'Le fichier doit être une photo.',
            'image.max' => 'La photo ne peut pas dépasser 8 Mo.',
        ]);

        $article->title = $data['title'];
        $article->code = $data['code'];

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

        return redirect()
            ->route('admin.sous.show', $article->sous_rubrique_id)
            ->with('status', 'Article enregistré.');
    }

    public function destroyPhoto(Article $article, ArticleImage $images)
    {
        $images->delete($article->image_path);
        $article->image_path = null;
        $article->save();

        return back()->with('status', 'Photo retirée.');
    }
}
