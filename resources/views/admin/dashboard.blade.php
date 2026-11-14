@extends('admin.layout')

@section('title', 'Tableau de bord')

@section('content')
    <div class="page-head">
        <div>
            <div class="crumb">Administration</div>
            <h1>Tableau de bord</h1>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.rubriques.index') }}">Gérer les rubriques</a>
    </div>

    <div class="stats">
        <div class="stat"><b>{{ $stats['rubriques'] }}</b><span>Rubriques</span></div>
        <div class="stat"><b>{{ $stats['sous'] }}</b><span>Sous-rubriques</span></div>
        <div class="stat"><b>{{ $stats['articles'] }}</b><span>Articles</span></div>
        <div class="stat"><b>{{ $stats['photos'] }}</b><span>Photos</span></div>
        <div class="stat"><b>{{ $stats['links'] }}</b><span>Liens</span></div>
    </div>

    <section class="panel">
        <h2>{{ $appName }}</h2>
        <p class="muted">
            Chaque rubrique contient ses sous-rubriques, et chaque sous-rubrique dix articles.
            Le code à 4 chiffres d’une sous-rubrique permet au client de la renommer.
            Le code d’un article lui permet de changer le titre et la photo.
            Chaque sous-rubrique cochée « Lien » ouvre un site web à la place du zoom photo.
            Une sous-rubrique décochée « Visible » est masquée du catalogue.
        </p>
        <p class="muted">WhatsApp affiché sur le catalogue : <strong>{{ $whatsapp }}</strong></p>
    </section>
@endsection
