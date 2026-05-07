<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>500 - Gestion Projets</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --brand-900: #0f172a;
            --brand-700: #1d4ed8;
            --brand-600: #2563eb;
            --brand-500: #3b82f6;
            --surface: rgba(255, 255, 255, 0.92);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Figtree', sans-serif;
            color: var(--brand-900);
            background:
                radial-gradient(circle at top right, rgba(239, 68, 68, 0.14), transparent 30%),
                radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.16), transparent 35%),
                linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .card {
            width: min(920px, 100%);
            background: var(--surface);
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.14);
            overflow: hidden;
            backdrop-filter: blur(14px);
        }
        .grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
        }
        .content {
            padding: clamp(28px, 4vw, 56px);
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            font-weight: 600;
            font-size: 14px;
        }
        .code {
            margin: 18px 0 8px;
            font-size: clamp(64px, 10vw, 120px);
            line-height: 0.9;
            font-weight: 800;
            letter-spacing: -0.05em;
            color: var(--brand-900);
        }
        h1 {
            margin: 0;
            font-size: clamp(28px, 4vw, 44px);
            line-height: 1.05;
        }
        p {
            margin: 16px 0 0;
            max-width: 54ch;
            color: #475569;
            font-size: 16px;
            line-height: 1.7;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-600), var(--brand-700));
            color: white;
            box-shadow: 0 18px 30px rgba(37, 99, 235, 0.28);
        }
        .btn-secondary {
            background: white;
            color: var(--brand-900);
            border: 1px solid rgba(148, 163, 184, 0.35);
        }
        .brand-panel {
            padding: 28px;
            background:
                linear-gradient(180deg, rgba(239, 68, 68, 0.08), rgba(15, 23, 42, 0.04)),
                radial-gradient(circle at center, rgba(59, 130, 246, 0.12), transparent 68%);
            display: grid;
            place-items: center;
            min-height: 100%;
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 20px;
            background: white;
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.08);
            font-weight: 700;
        }
        .brand-badge svg {
            width: 28px;
            height: 28px;
            color: var(--brand-600);
            flex: none;
        }
        .hint {
            margin-top: 18px;
            font-size: 14px;
            color: #64748b;
        }
        @media (max-width: 860px) {
            .grid { grid-template-columns: 1fr; }
            .brand-panel { order: -1; min-height: 220px; }
        }
    </style>
</head>
<body>
    <main class="card" role="main" aria-labelledby="page-title">
        <div class="grid">
            <section class="content">
                <div class="eyebrow">
                    <span>Erreur 500</span>
                    <span>Problème serveur</span>
                </div>

                <div class="code">500</div>
                <h1 id="page-title">Le serveur a rencontré une erreur.</h1>
                <p>
                    Une erreur inattendue est survenue côté application. L'équipe DSI peut consulter les journaux pour diagnostiquer le problème et rétablir le service.
                </p>

                <div class="actions">
                    <a href="{{ auth()->check() && Route::has('dashboard') ? route('dashboard') : url('/') }}" class="btn btn-primary">
                        Retour à l'accueil
                    </a>
                    <a href="javascript:location.reload()" class="btn btn-secondary">
                        Réessayer
                    </a>
                </div>

                <div class="hint">Gestion Projets • erreur serveur interne</div>
            </section>

            <aside class="brand-panel" aria-hidden="true">
                <div class="brand-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    </svg>
                    <span>Gestion Projets</span>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
