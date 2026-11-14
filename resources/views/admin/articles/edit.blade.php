@extends('admin.layout')

@section('title', $article->title)

@section('content')
    <div class="page-head">
        <div>
            <div class="crumb">
                <a href="{{ route('admin.rubriques.show', $article->sousRubrique->rubrique) }}">{{ $article->sousRubrique->rubrique->name }}</a>
                · <a href="{{ route('admin.sous.show', $article->sousRubrique) }}">{{ $article->sousRubrique->name }}</a>
            </div>
            <h1>Article {{ $article->position }}</h1>
        </div>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="title">Titre (20 caractères maximum)</label>
                <input id="title" name="title" type="text" maxlength="20" required value="{{ old('title', $article->title) }}">
            </div>
            <div class="field">
                <label for="code">Mot de passe client (4 chiffres)</label>
                <input id="code" name="code" type="text" inputmode="numeric" maxlength="4" pattern="\d{4}" required value="{{ old('code', $article->code) }}">
            </div>
            @if ($article->sousRubrique->is_linked)
                <div class="field">
                    <label for="link">Lien du site</label>
                    <input id="link" name="link" type="text" placeholder="https://exemple.com" value="{{ old('link', $article->link) }}">
                </div>
            @endif
            <div class="field">
                <label for="image">Photo</label>
                @if ($article->imageUrl())
                    <div style="margin-bottom:10px;"><img class="preview" src="{{ $article->imageUrl() }}" alt=""></div>
                @endif
                <input id="image" name="image" type="file" accept="image/*">
            </div>
            <div class="actions">
                <button class="btn btn-primary" type="submit">Enregistrer</button>
                <a class="btn btn-ghost" href="{{ route('admin.sous.show', $article->sousRubrique) }}">Retour</a>
            </div>
        </form>
        @if ($article->image_path)
            <form method="POST" action="{{ route('admin.articles.photo.destroy', $article) }}" style="margin-top:12px;">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Retirer la photo</button>
            </form>
        @endif
    </section>
@endsection
