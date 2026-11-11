<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Connexion — Gabon Catalogue</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --mustard: #D4A017;
            --mustard-deep: #B8860B;
            --black: #1C1712;
            --black-soft: #26201A;
            --blue-dark: #12206B;
            --red-dark: #7A0E0E;
            --cream: #FFF6E0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            background: var(--mustard);
            font-family: Inter, sans-serif;
            color: var(--black);
        }
        form {
            width: 100%;
            max-width: 480px;
            background: var(--cream);
            border-radius: 20px 20px 0 0;
            padding: 22px 20px 28px;
            animation: slideUp 0.2s ease-out;
        }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: none; opacity: 1; } }
        h1 {
            margin: 0 0 16px;
            font-family: "Baloo 2", sans-serif;
            font-size: 18px;
            font-weight: 700;
        }
        .field { margin-bottom: 14px; }
        label { display: block; font-size: 12px; font-weight: 600; color: var(--black-soft); margin-bottom: 6px; }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 11px 42px 11px 12px;
            border-radius: 10px;
            border: 1.5px solid var(--mustard-deep);
            font: inherit;
            font-size: 15px;
            background: #fff;
            color: var(--black);
        }
        input[type="email"] { padding-right: 12px; }
        input:focus { outline: 2px solid var(--blue-dark); }
        .secret-wrap { position: relative; }
        .eye {
            position: absolute;
            top: 0;
            right: 0;
            width: 42px;
            height: 46px;
            border: 0;
            background: transparent;
            color: var(--black-soft);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .eye svg { width: 20px; height: 20px; }
        .row { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; margin: 2px 0 0; }
        .error { color: var(--red-dark); font-size: 12px; margin: -6px 0 10px; }
        .modal-actions { display: flex; gap: 10px; margin-top: 18px; }
        .btn {
            flex: 1;
            padding: 13px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-primary { border: 0; background: var(--blue-dark); color: #fff; }
        .btn-ghost { background: transparent; color: var(--black-soft); border: 1.5px solid var(--mustard-deep); }
        @media (min-width: 760px) {
            body { align-items: center; padding: 24px; }
            form { border-radius: 20px; box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18); }
        }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <h1>Connexion administrateur</h1>
        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
        <div class="field">
            <label for="email">Adresse e-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        </div>
        <div class="field">
            <label for="password">Mot de passe</label>
            <div class="secret-wrap">
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <button class="eye" type="button" aria-label="Afficher le mot de passe" aria-pressed="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
        </div>
        <label class="row"><input type="checkbox" name="remember" value="1"> Rester connecté</label>
        <div class="modal-actions">
            <a class="btn btn-ghost" href="{{ config('catalogue.frontend_url') }}">Annuler</a>
            <button class="btn btn-primary" type="submit">Se connecter</button>
        </div>
    </form>
    <script>
        const password = document.getElementById('password');
        const eye = document.querySelector('.eye');
        const openEye = eye.innerHTML;
        const closedEye = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 6.2A10.7 10.7 0 0 1 12 6c6.5 0 10 6 10 6a18.4 18.4 0 0 1-4.1 4.6"/><path d="M6.1 6.7C3.5 8.4 2 12 2 12s3.5 6 10 6c1.6 0 3-.4 4.3-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';
        eye.addEventListener('click', () => {
            const visible = password.type === 'text';
            password.type = visible ? 'password' : 'text';
            eye.innerHTML = visible ? openEye : closedEye;
            eye.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
            eye.setAttribute('aria-pressed', visible ? 'false' : 'true');
        });
    </script>
</body>
</html>
