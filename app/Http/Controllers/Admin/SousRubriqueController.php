<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SousRubrique;
use Illuminate\Http\Request;

class SousRubriqueController extends Controller
{
    public function show(SousRubrique $sousRubrique)
    {
        $sousRubrique->load('rubrique', 'articles');

        return view('admin.sous.show', [
            'sous' => $sousRubrique,
        ]);
    }

    public function update(Request $request, SousRubrique $sousRubrique)
    {
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'code' => trim((string) $request->input('code', '')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
            'code' => ['required', 'regex:/^\d{4}$/'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 40 caractères.',
            'code.regex' => 'Le mot de passe doit contenir exactement 4 chiffres.',
        ]);

        $sousRubrique->name = $data['name'];
        $sousRubrique->code = $data['code'];
        $sousRubrique->is_linked = $request->boolean('is_linked');
        $sousRubrique->is_visible = $request->boolean('is_visible');
        $sousRubrique->save();
        $sousRubrique->rubrique->refreshLinked();

        return back()->with('status', 'Sous-rubrique enregistrée.');
    }

    public function updateCodes(Request $request, SousRubrique $sousRubrique)
    {
        $data = $request->validate([
            'codes' => ['required', 'array'],
            'codes.*' => ['required', 'regex:/^\d{4}$/'],
        ], [
            'codes.*.regex' => 'Chaque mot de passe doit contenir exactement 4 chiffres.',
            'codes.*.required' => 'Chaque mot de passe doit contenir exactement 4 chiffres.',
        ]);

        $articles = $sousRubrique->articles()->get()->keyBy('id');

        foreach ($data['codes'] as $id => $code) {
            /** @var Article|null $article */
            $article = $articles->get((int) $id);
            if (! $article) {
                continue;
            }

            $article->code = $code;
            $article->save();
        }

        return back()->with('status', 'Mots de passe des articles enregistrés.');
    }
}
