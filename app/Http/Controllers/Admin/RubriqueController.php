<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rubrique;
use Illuminate\Http\Request;

class RubriqueController extends Controller
{
    public function index()
    {
        return view('admin.rubriques.index', [
            'rubriques' => Rubrique::query()->orderBy('position')->get(),
        ]);
    }

    public function show(Rubrique $rubrique)
    {
        $rubrique->load('sousRubriques');

        return view('admin.rubriques.show', [
            'rubrique' => $rubrique,
        ]);
    }

    public function update(Request $request, Rubrique $rubrique)
    {
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:40'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 40 caractères.',
        ]);

        $rubrique->name = $data['name'];
        $rubrique->save();

        return back()->with('status', 'Rubrique enregistrée.');
    }
}
