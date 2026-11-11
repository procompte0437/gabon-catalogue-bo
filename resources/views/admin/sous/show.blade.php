@extends('admin.layout')

@section('title', $sous->name)

@section('content')
    <div class="page-head">
        <div>
            <div class="crumb">
                <a href="{{ route('admin.rubriques.index') }}">Rubriques</a>
                · <a href="{{ route('admin.rubriques.show', $sous->rubrique) }}">{{ $sous->rubrique->name }}</a>
            </div>
            <h1>{{ $sous->name }}</h1>
        </div>
    </div>

    <section class="panel">
        <h2>Mots de passe des articles</h2>
        <p class="muted">Un code par article. Partagez-le au client pour qu’il puisse changer sa photo et son titre depuis le catalogue.</p>
        <form method="POST" action="{{ route('admin.sous.codes', $sous) }}">
            @csrf
            @method('PUT')
            <div class="codes">
                @foreach ($sous->articles as $article)
                    <div class="code-row">
                        <div class="field" style="margin:0;">
                            <label for="code-{{ $article->id }}">{{ $article->position }}. {{ $article->title }}</label>
                            <input id="code-{{ $article->id }}" name="codes[{{ $article->id }}]" value="{{ old('codes.'.$article->id, $article->code) }}" inputmode="numeric" maxlength="4" required>
                        </div>
                        <button class="btn btn-ghost btn-tiny" type="button" onclick="document.getElementById('code-{{ $article->id }}').value='0000'">↺ 0000</button>
                        <a class="btn btn-primary btn-tiny" href="{{ route('admin.articles.edit', $article) }}">Modifier</a>
                    </div>
                @endforeach
            </div>
            <div class="actions" style="margin-top:14px;">
                <button class="btn btn-primary" type="submit">Enregistrer tous les codes</button>
            </div>
        </form>
    </section>

    <div class="grid">
        @foreach ($sous->articles as $article)
            <article class="article-card">
                <div class="thumb">
                    @if ($article->image_path)
                        <img src="{{ $article->imageUrl() }}" alt="">
                    @else
                        📷
                    @endif
                </div>
                <div class="meta">
                    <strong>{{ $article->title }}</strong>
                    <p>Code {{ $article->code }}@if($sous->rubrique->is_linked && $article->link) · lien @endif</p>
                    <a class="btn btn-ghost btn-tiny" href="{{ route('admin.articles.edit', $article) }}">Photo et titre</a>
                </div>
            </article>
        @endforeach
    </div>
@endsection
