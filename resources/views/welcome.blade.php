<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ config('app.name', 'CMPI') }} — API Server</title>

    <link rel="icon" type="image/png" href="/CMPI.png">

    <style>
        :root {
            --bg: #060a09;
            --surface: rgba(255, 255, 255, 0.028);
            --border: rgba(255, 255, 255, 0.075);
            --border-strong: rgba(255, 255, 255, 0.12);
            --text: #e8f0ed;
            --muted: #8a9c96;
            --dim: #5d6e68;
            --accent: #10b981;
            --accent-soft: rgba(16, 185, 129, 0.12);
            --danger: #f43f5e;
            --radius: 14px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            background: var(--bg);
            background-image:
                radial-gradient(60rem 40rem at 50% -12%, rgba(16, 185, 129, 0.16), transparent 62%),
                radial-gradient(38rem 30rem at 88% 108%, rgba(13, 148, 136, 0.10), transparent 60%);
            background-repeat: no-repeat;
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto,
                "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .wrap { width: 100%; max-width: 54rem; }

        /* ---------- header ---------- */
        .brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2.25rem;
        }

        .brand img {
            width: 4rem;
            height: 4rem;
            border-radius: 1.1rem;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-strong);
            box-shadow: 0 0 0 4px var(--accent-soft), 0 12px 32px -12px rgba(16, 185, 129, 0.5);
        }

        .brand h1 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            letter-spacing: -0.015em;
        }

        .brand p {
            margin: 0.15rem 0 0;
            color: var(--muted);
            font-size: 0.875rem;
        }

        /* ---------- status pill ---------- */
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.5rem 0.95rem;
            border-radius: 999px;
            border: 1px solid var(--border-strong);
            background: var(--surface);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
            animation: pulse 2.2s ease-out infinite;
        }

        .dot.down { background: var(--danger); box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.6); }

        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.55); }
            70%  { box-shadow: 0 0 0 0.45rem rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @media (prefers-reduced-motion: reduce) {
            .dot { animation: none; }
        }

        /* ---------- panels ---------- */
        .panel {
            margin-top: 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            backdrop-filter: blur(6px);
            overflow: hidden;
        }

        .panel > h2 {
            margin: 0;
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: var(--dim);
        }

        .rows { display: grid; }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 0.8rem 1.25rem;
            border-bottom: 1px solid var(--border);
        }

        .row:last-child { border-bottom: 0; }

        .row dt { color: var(--muted); font-size: 0.875rem; flex-shrink: 0; }

        .row dd {
            margin: 0;
            font-size: 0.875rem;
            text-align: right;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        code {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace;
            font-size: 0.8125rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.1rem 0.4rem;
        }

        .ok { color: var(--accent); }
        .no { color: var(--danger); }

        /* ---------- portals ---------- */
        .links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
            gap: 0.75rem;
            padding: 1.25rem;
        }

        .link {
            display: block;
            padding: 0.95rem 1.1rem;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: rgba(255, 255, 255, 0.018);
            text-decoration: none;
            color: inherit;
            transition: border-color 0.16s ease, background 0.16s ease, transform 0.16s ease;
        }

        .link:hover {
            border-color: rgba(16, 185, 129, 0.45);
            background: var(--accent-soft);
            transform: translateY(-1px);
        }

        .link:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        .link strong { display: block; font-size: 0.9rem; font-weight: 600; }
        .link span { display: block; margin-top: 0.15rem; color: var(--dim); font-size: 0.78rem; }

        footer {
            margin-top: 2rem;
            color: var(--dim);
            font-size: 0.78rem;
            text-align: center;
        }

        footer code { background: none; border: 0; padding: 0; color: var(--dim); }
    </style>
</head>
<body>
    <main class="wrap">
        <header class="brand">
            <img src="/CMPI.png" alt="CMPI logo" width="64" height="64">
            <div>
                <h1>Cox's Bazar Model Polytechnic Institute</h1>
                <p>College Road, Cox's Bazar 4750, Bangladesh</p>
            </div>
        </header>

        <div class="pill">
            <span class="dot {{ $databaseConnected ? '' : 'down' }}"></span>
            API Server &middot; {{ $databaseConnected ? 'Running' : 'Degraded' }}
        </div>

        <section class="panel">
            <h2>Server</h2>
            <dl class="rows">
                <div class="row">
                    <dt>API base URL</dt>
                    <dd><code>{{ $apiBase }}</code></dd>
                </div>
                <div class="row">
                    <dt>Environment</dt>
                    <dd><code>{{ $environment }}</code></dd>
                </div>
                <div class="row">
                    <dt>Database</dt>
                    <dd>
                        <span class="{{ $databaseConnected ? 'ok' : 'no' }}">
                            {{ $databaseConnected ? 'Connected' : 'Unavailable' }}
                        </span>
                        <code>{{ $dbConnection }}</code>
                    </dd>
                </div>
                <div class="row">
                    <dt>Laravel</dt>
                    <dd><code>v{{ $laravel }}</code></dd>
                </div>
                <div class="row">
                    <dt>PHP</dt>
                    <dd><code>{{ $php }}</code></dd>
                </div>
            </dl>
        </section>

        <section class="panel">
            <h2>Portals</h2>
            <div class="links">
                <a class="link" href="{{ $clientUrl }}" target="_blank" rel="noopener">
                    <strong>Student Portal</strong>
                    <span>{{ $clientUrl }}</span>
                </a>
                <a class="link" href="{{ $adminUrl }}" target="_blank" rel="noopener">
                    <strong>Admin Panel</strong>
                    <span>{{ $adminUrl }}</span>
                </a>
                <a class="link" href="{{ $apiBase }}" target="_blank" rel="noopener">
                    <strong>API Endpoint</strong>
                    <span>{{ $apiBase }}</span>
                </a>
            </div>
        </section>

        <footer>
            {{ config('app.name', 'CMPI') }} &middot; Laravel API server
        </footer>
    </main>
</body>
</html>
