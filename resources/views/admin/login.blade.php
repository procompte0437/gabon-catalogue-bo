<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — Gabon Catalogue</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --mustard:#D4A017; --black:#1C1712; --blue:#12206B; --red:#7A0E0E; --cream:#FFF6E0; --deep:#B8860B; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: var(--mustard); font-family: Inter, sans-serif; color: var(--black); padding: 24px;
        }
        .card { width: min(420px, 100%); background: var(--cream); border-radius: 24px; padding: 28px 24px 24px; box-shadow: 0 16px 40px rgba(0,0,0,.18); }
        .logo {
            width: 84px; height: 84px; border-radius: 50%; margin: 0 auto 12px; background: var(--blue); color: var(--red);
            display: flex; align-items: center; justify-content: center; font-family: "Baloo 2", sans-serif; font-weight: 800; font-size: 22px;
            border: 4px solid var(--black);
        }
        h1 { font-family: "Baloo 2", sans-serif; text-align: center; margin: 0 0 4px; font-size: 28px; }
        p { text-align: center; margin: 0 0 18px; color: #26201A; font-size: 14px; }
        label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 12px; border-radius: 10px; border: 1.5px solid var(--deep); margin-bottom: 12px; font: inherit;
        }
        .row { display: flex; align-items: center; gap: 8px; font-size: 14px; margin: 4px 0 16px; }
        button {
            width: 100%; border: 0; border-radius: 12px; padding: 13px; background: var(--blue); color: white;
            font-weight: 700; font-size: 15px; cursor: pointer;
        }
        .error { background: #fde8e8; color: var(--red); border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; font-size: 13px; }
    </style>
</head>
<body>
    <form class="card" method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="logo">lessy</div>
        <h1>Gabon Catalogue</h1>
        <p>Connexion administrateur</p>
        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
        <label for="email">Adresse e-mail</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <label class="row"><input type="checkbox" name="remember" value="1"> Rester connecté</label>
        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
