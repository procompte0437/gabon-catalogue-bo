<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — Gabon Catalogue</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --black:#1C1712; --blue:#12206B; --red:#7A0E0E; --cream:#FFF6E0; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: Inter, sans-serif; color: var(--black); background: #f4f6f5; }
        .split { min-height: 100vh; display: grid; grid-template-columns: minmax(340px, 44%) 1fr; }
        .panel { display: flex; align-items: center; justify-content: center; padding: 40px 28px; background: #f7f8f6; }
        form { width: min(380px, 100%); }
        .logo {
            width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 18px; background: var(--blue); color: var(--red);
            display: flex; align-items: center; justify-content: center; font-family: "Baloo 2", sans-serif; font-weight: 800; font-size: 20px;
        }
        h1 { text-align: center; margin: 0; font-size: 28px; font-weight: 700; letter-spacing: -0.02em; }
        .lead { text-align: center; margin: 8px 0 26px; color: #5c6560; font-size: 14px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        input[type="email"], input[type="password"] {
            width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #d5dbd8; margin-bottom: 16px;
            font: inherit; background: #fff; color: var(--black);
        }
        input:focus { outline: 2px solid rgba(18, 32, 107, 0.25); border-color: var(--blue); }
        .row { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; margin: -4px 0 18px; }
        button {
            width: 100%; border: 0; border-radius: 8px; padding: 13px; background: var(--blue); color: white;
            font-weight: 700; font-size: 15px; cursor: pointer;
        }
        button:hover { background: #1a2d86; }
        .error { background: #fde8e8; color: var(--red); border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; font-size: 13px; }
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 32px;
            background:
                linear-gradient(180deg, rgba(10, 18, 32, 0.08), rgba(10, 18, 32, 0.38)),
                url("{{ asset('images/gabon_esplanade2.jpg') }}") center 30% / cover no-repeat;
            color: white;
            text-align: center;
        }
        .hero-mark {
            width: 112px; height: 112px; border-radius: 22px; background: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
        }
        .hero-mark .logo { width: 78px; height: 78px; margin: 0; font-size: 22px; }
        .hero h2 { margin: 6px 0 0; font-size: 32px; font-weight: 700; letter-spacing: 0.04em; text-shadow: 0 2px 16px rgba(0,0,0,.35); }
        .hero p { margin: 0; font-size: 16px; font-weight: 500; text-shadow: 0 2px 12px rgba(0,0,0,.4); }
        @media (max-width: 860px) {
            .split { grid-template-columns: 1fr; }
            .hero { min-height: 280px; order: -1; }
            .panel { padding: 32px 20px 48px; }
        }
    </style>
</head>
<body>
    <main class="split">
        <section class="panel">
            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="logo">lessy</div>
                <h1>Connexion</h1>
                <p class="lead">Entrez votre e-mail et votre mot de passe</p>
                @if ($errors->any())
                    <div class="error">{{ $errors->first() }}</div>
                @endif
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                <label for="password">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <label class="row"><input type="checkbox" name="remember" value="1"> Rester connecté</label>
                <button type="submit">Se connecter</button>
            </form>
        </section>
        <aside class="hero" aria-hidden="true">
            <div class="hero-mark"><div class="logo">lessy</div></div>
            <h2>Gabon Catalogue</h2>
            <p>Libreville · Gabon</p>
        </aside>
    </main>
</body>
</html>
