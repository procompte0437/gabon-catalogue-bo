@extends('admin.layout')

@section('title', $rubrique->name)

@section('content')
    @php
        $editingRubrique = old('_form') === 'rubrique';
        $linked = $editingRubrique ? (string) old('is_linked') === '1' : $rubrique->is_linked;
    @endphp
    <div class="page-head">
        <div>
            <div class="crumb">
                <a href="{{ route('admin.dashboard') }}">Tableau de bord</a>
                · <a href="{{ route('admin.rubriques.index') }}">Rubriques</a>
            </div>
            <h1>{{ $rubrique->name }}</h1>
        </div>
    </div>

    <section class="panel">
        <h2>Modifier la rubrique</h2>
        <p class="muted">Une seule rubrique peut ouvrir des sites externes. L’activer ici retire ce mode aux autres.</p>
        <form method="POST" action="{{ route('admin.rubriques.update', $rubrique) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="rubrique">
            <div class="field">
                <label for="rubrique-name">Nom</label>
                <input id="rubrique-name" name="name" type="text" maxlength="40" required value="{{ $editingRubrique ? old('name', $rubrique->name) : $rubrique->name }}">
            </div>
            <label class="check">
                <input type="hidden" name="is_linked" value="0">
                <input type="checkbox" name="is_linked" value="1" @checked($linked)>
                <span>Les articles de cette rubrique ouvrent un lien web au lieu d’agrandir la photo.</span>
            </label>
            <div class="actions" style="margin-top:14px;">
                <button class="btn btn-primary" type="submit">Enregistrer la rubrique</button>
            </div>
        </form>
    </section>

    <h2>Sous-rubriques</h2>
    <p class="muted">Le code à 4 chiffres est celui que le client saisit pour renommer la sous-rubrique.</p>
    @foreach ($rubrique->sousRubriques as $sous)
        @php $editing = old('_form') === 'sous-'.$sous->id; @endphp
        <form class="line" method="POST" action="{{ route('admin.sous.update', $sous) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="sous-{{ $sous->id }}">
            <span class="idx">{{ $sous->position }}</span>
            <input name="name" type="text" maxlength="40" required aria-label="Nom de la sous-rubrique {{ $sous->position }}" value="{{ $editing ? old('name', $sous->name) : $sous->name }}">
            <input name="code" type="text" inputmode="numeric" maxlength="4" pattern="\d{4}" required aria-label="Code de la sous-rubrique {{ $sous->position }}" value="{{ $editing ? old('code', $sous->code) : $sous->code }}">
            <button class="btn btn-primary btn-tiny" type="submit">Enregistrer</button>
            <a class="btn btn-ghost btn-tiny" href="{{ route('admin.sous.show', $sous) }}">Articles</a>
        </form>
    @endforeach
@endsection
