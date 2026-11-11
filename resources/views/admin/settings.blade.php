@extends('admin.layout')

@section('title', 'Réglages')

@section('content')
    <div class="page-head">
        <div>
            <div class="crumb"><a href="{{ route('admin.dashboard') }}">Tableau de bord</a></div>
            <h1>Réglages</h1>
        </div>
    </div>

    <section class="panel">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="app_name">Nom du catalogue</label>
                <input id="app_name" name="app_name" type="text" maxlength="40" required value="{{ old('app_name', $appName) }}">
            </div>
            <div class="field">
                <label for="whatsapp_display">Numéro WhatsApp affiché</label>
                <input id="whatsapp_display" name="whatsapp_display" type="text" maxlength="30" required value="{{ old('whatsapp_display', $whatsapp) }}">
                <p class="muted">Exemple : 062-70-47-45. Le lien wa.me ajoute l’indicatif du Gabon (241) si le numéro commence par 0.</p>
            </div>
            <div class="field">
                <label for="master_code">Mot de passe général du catalogue (4 chiffres)</label>
                <input id="master_code" name="master_code" type="text" inputmode="numeric" maxlength="4" required value="{{ old('master_code', $masterCode) }}">
                <p class="muted">Dans le catalogue (site et application), ce code permet de renommer les rubriques et de modifier n’importe quelle sous-rubrique ou article, en plus du code propre à chacun.</p>
            </div>
            <h2>Changer le mot de passe administrateur</h2>
            <p class="muted">Laissez ces champs vides pour conserver le mot de passe actuel.</p>
            <div class="field">
                <label for="current_password">Mot de passe actuel</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password">
            </div>
            <div class="field">
                <label for="password">Nouveau mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="password_confirmation">Confirmation</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
            </div>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </form>
    </section>
@endsection
