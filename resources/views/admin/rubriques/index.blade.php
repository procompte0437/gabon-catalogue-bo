@extends('admin.layout')

@section('title', 'Rubriques')

@section('content')
    <div class="page-head">
        <div>
            <div class="crumb"><a href="{{ route('admin.dashboard') }}">Tableau de bord</a></div>
            <h1>Rubriques</h1>
        </div>
    </div>
    <p class="muted" style="margin-top:-8px;">Le numéro affiché est l’ordre du catalogue. Ouvrez une rubrique pour renommer ses sous-rubriques.</p>
    <div class="cards">
        @forelse ($rubriques as $rubrique)
            <a class="card-link" href="{{ route('admin.rubriques.show', $rubrique) }}">
                <span class="idx">{{ $rubrique->position }}</span>
                <span class="label">{{ $rubrique->name }}</span>
                @if ($rubrique->is_linked)
                    <span class="pill">Liens externes</span>
                @endif
                <span>›</span>
            </a>
        @empty
            <div class="panel">Le catalogue est vide. Lancez le remplissage initial avec <code>php artisan db:seed</code>.</div>
        @endforelse
    </div>
@endsection
