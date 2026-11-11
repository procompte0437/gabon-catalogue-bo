<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administration') — {{ \App\Models\Setting::appName() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --mustard: #D4A017;
            --mustard-deep: #B8860B;
            --mustard-pale: #F1C94A;
            --black: #1C1712;
            --black-soft: #26201A;
            --blue-dark: #12206B;
            --red-dark: #7A0E0E;
            --cream: #FFF6E0;
            --line: rgba(28, 23, 18, 0.12);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, sans-serif;
            background: var(--cream);
            color: var(--black);
        }
        h1, h2, h3, .display { font-family: "Baloo 2", sans-serif; }
        a { color: inherit; }
        button, input, select { font-family: inherit; }
        .shell { min-height: 100vh; display: flex; }
        .sidebar {
            width: 250px;
            background: var(--black);
            color: var(--mustard-pale);
            display: flex;
            flex-direction: column;
            padding: 22px 16px;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; text-decoration: none; }
        .logo {
            width: 46px; height: 46px; border-radius: 50%;
            background: var(--blue-dark); color: var(--red-dark);
            display: flex; align-items: center; justify-content: center;
            font-family: "Baloo 2", sans-serif; font-weight: 800; border: 2px solid var(--mustard);
        }
        .brand b { display: block; font-size: 16px; line-height: 1.1; }
        .brand span { font-size: 12px; opacity: 0.7; }
        .nav { display: flex; flex-direction: column; gap: 6px; }
        .nav a, .nav button {
            text-align: left; text-decoration: none; background: transparent; color: var(--mustard-pale);
            border: 0; border-radius: 10px; padding: 11px 12px; font-weight: 600; font-size: 14px; cursor: pointer;
        }
        .nav a.active, .nav a:hover, .nav button:hover { background: var(--mustard); color: var(--black); }
        .sidebar .grow { flex: 1; }
        .who { font-size: 12px; opacity: 0.7; padding: 8px 12px; word-break: break-all; }
        .main { flex: 1; padding: 28px 28px 60px; min-width: 0; }
        .page-head { display: flex; justify-content: space-between; gap: 16px; align-items: flex-end; margin-bottom: 18px; }
        .page-head h1 { margin: 0; font-size: 30px; }
        .crumb { font-size: 13px; color: var(--black-soft); margin-bottom: 6px; }
        .crumb a { color: var(--blue-dark); }
        .flash { background: #e7f6ea; color: #145c28; border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; font-size: 14px; font-weight: 600; }
        .flash.error { background: #fde8e8; color: var(--red-dark); font-weight: 500; }
        .panel { background: #fff; border-radius: 16px; padding: 18px; margin-bottom: 16px; box-shadow: 0 1px 0 var(--line); }
        .panel h2 { margin: 0 0 6px; font-size: 20px; }
        .muted { color: var(--black-soft); font-size: 13.5px; line-height: 1.45; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 16px; }
        .stat { background: var(--black); color: var(--mustard-pale); border-radius: 16px; padding: 16px; }
        .stat b { display: block; font-family: "Baloo 2", sans-serif; font-size: 28px; line-height: 1; }
        .stat span { font-size: 12.5px; opacity: 0.8; }
        .cards { display: flex; flex-direction: column; gap: 8px; }
        .card-link {
            display: flex; align-items: center; gap: 12px; text-decoration: none;
            background: var(--black); color: var(--mustard-pale); border-radius: 14px; padding: 14px;
        }
        .idx {
            width: 34px; height: 34px; border-radius: 9px; background: var(--mustard); color: var(--black);
            display: flex; align-items: center; justify-content: center; font-family: "Baloo 2", sans-serif; font-weight: 700; flex-shrink: 0;
        }
        .card-link .label { flex: 1; font-weight: 650; font-weight: 600; }
        .pill { font-size: 11px; background: var(--blue-dark); color: white; border-radius: 20px; padding: 4px 8px; }
        .field { margin-bottom: 14px; }
        .field label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; }
        .field input[type="text"], .field input[type="email"], .field input[type="password"], .field input[type="url"], .line input, .codes input {
            width: 100%; padding: 11px 12px; border-radius: 10px; border: 1.5px solid var(--mustard-deep); background: #fff; color: var(--black); font-size: 15px;
        }
        .check { display: flex; gap: 8px; align-items: flex-start; font-size: 14px; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .btn {
            border: 0; border-radius: 12px; padding: 11px 14px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-primary { background: var(--blue-dark); color: white; }
        .btn-ghost { background: transparent; color: var(--black); border: 1.5px solid var(--mustard-deep); }
        .btn-tiny { padding: 8px 10px; border-radius: 9px; font-size: 12.5px; }
        /* Sur les fonds noirs (lignes et cartes), le bouton contour passe en moutarde clair pour rester lisible. */
        .line .btn-ghost, .article-card .btn-ghost { color: var(--mustard-pale); border-color: var(--mustard); }
        .line .btn-ghost:hover, .article-card .btn-ghost:hover { background: rgba(212, 160, 23, 0.18); }
        .btn-danger { background: transparent; color: var(--red-dark); border: 1.5px solid var(--red-dark); }
        .line {
            display: grid; grid-template-columns: 42px minmax(0, 1fr) 92px auto auto; gap: 8px; align-items: center;
            background: var(--black); padding: 10px; border-radius: 14px; margin-bottom: 8px;
        }
        .line input { background: #fff; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
        .article-card { background: var(--black); border-radius: 14px; overflow: hidden; color: var(--mustard-pale); }
        .thumb { aspect-ratio: 1; background: linear-gradient(135deg, var(--mustard-deep), var(--black-soft)); display: flex; align-items: center; justify-content: center; font-size: 28px; }
        .thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .article-card .meta { padding: 10px; }
        .article-card strong { display: block; font-size: 13.5px; margin-bottom: 4px; }
        .article-card p { margin: 0 0 8px; font-size: 12px; opacity: 0.75; }
        .codes { display: grid; gap: 8px; }
        .code-row { display: grid; grid-template-columns: minmax(0, 1fr) 110px auto; gap: 8px; align-items: end; }
        .preview { width: 120px; height: 120px; object-fit: cover; border-radius: 12px; background: var(--black); }
        @media (max-width: 860px) {
            .shell { display: block; }
            .sidebar { width: auto; height: auto; position: static; }
            .nav { flex-direction: row; overflow: auto; }
            .who, .sidebar .grow { display: none; }
            .main { padding: 18px 14px 40px; }
            .line, .code-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}">
            <div class="logo">L</div>
            <div>
                <b>{{ \App\Models\Setting::appName() }}</b>
                <span>Back-office</span>
            </div>
        </a>
        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Tableau de bord</a>
            <a href="{{ route('admin.rubriques.index') }}" class="{{ request()->routeIs('admin.rubriques.*') || request()->routeIs('admin.sous.*') || request()->routeIs('admin.articles.*') ? 'active' : '' }}">Rubriques</a>
            <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Réglages</a>
            <a href="{{ \App\Support\Catalogue::frontendUrl() }}" target="_blank" rel="noopener">Voir le catalogue</a>
        </nav>
        <div class="grow"></div>
        <div class="who">{{ auth()->user()->email }}</div>
        <form class="nav" method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Déconnexion</button>
        </form>
    </aside>
    <main class="main">
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash error">
                @foreach (collect($errors->all())->unique() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
