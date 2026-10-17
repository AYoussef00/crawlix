<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول — Crawlix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --void: #050508;
            --surface: #0c0c12;
            --ink: #0b0c0f;
            --muted: #9ca3af;
            --line: rgba(255, 255, 255, 0.08);
            --purple: #a855f7;
            --danger: #f87171;
            --grad: linear-gradient(135deg, #a855f7 0%, #6366f1 45%, #3b82f6 100%);
            --focus: rgba(168, 85, 247, 0.35);
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
            --font: "Cairo", "Segoe UI", Tahoma, sans-serif;
            --font-body: var(--font);
            --font-display: var(--font);
            --font-accent: var(--font);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { min-height: 100%; }

        body {
            font-family: var(--font-body);
            color: #fff;
            background: var(--void);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }

        .stage {
            position: relative;
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: 88px 20px 48px;
            isolation: isolate;
        }

        .fx {
            position: absolute;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            overflow: hidden;
        }

        .fx .glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.5;
            animation: drift 16s ease-in-out infinite alternate;
        }

        .fx .g1 {
            width: min(640px, 80vw);
            height: min(640px, 80vw);
            top: -22%;
            inset-inline-start: -18%;
            background: radial-gradient(circle, rgba(168,85,247,0.55), transparent 68%);
        }

        .fx .g2 {
            width: min(520px, 65vw);
            height: min(520px, 65vw);
            bottom: -16%;
            inset-inline-end: -14%;
            background: radial-gradient(circle, rgba(59,130,246,0.4), transparent 70%);
            animation-delay: -5s;
        }

        .topbar {
            position: absolute;
            top: 0;
            inset-inline: 0;
            height: 84px;
            display: flex;
            align-items: center;
            z-index: 5;
        }

        .topbar-inner {
            width: min(1120px, calc(100% - 40px));
            margin-inline: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .nav-brand img {
            height: 56px;
            width: auto;
            object-fit: contain;
        }

        .nav-back {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 980px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.03);
            font-family: var(--font-accent);
            font-size: 13px;
            font-weight: 500;
            color: rgba(255,255,255,0.7);
            transition: background 0.2s, color 0.2s;
        }

        .nav-back:hover {
            background: rgba(255,255,255,0.07);
            color: #fff;
        }

        .panel {
            width: min(420px, 100%);
            text-align: center;
            animation: rise 0.9s var(--ease) both;
        }

        .panel-logo {
            height: 52px;
            width: auto;
            margin: 0 auto 18px;
        }

        .brand {
            font-family: var(--font-display);
            font-size: clamp(32px, 6vw, 40px);
            font-weight: 900;
            letter-spacing: -0.02em;
            line-height: 1.25;
            margin-bottom: 28px;
        }

        .shell {
            background: rgba(12, 12, 18, 0.9);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 22px;
            padding: 28px 24px 24px;
            box-shadow: 0 0 0 1px rgba(168,85,247,0.08), 0 32px 80px rgba(0,0,0,0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            text-align: start;
            animation: rise 1.05s var(--ease) 0.08s both;
        }

        .field { margin-bottom: 14px; }

        .field label {
            display: block;
            font-family: var(--font-accent);
            font-size: 12px;
            font-weight: 500;
            color: rgba(255,255,255,0.5);
            margin-bottom: 8px;
            padding-inline: 2px;
        }

        .field input {
            width: 100%;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
            border-radius: 14px;
            padding: 14px 16px;
            font: inherit;
            font-size: 16px;
            color: #fff;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .field input::placeholder { color: rgba(255,255,255,0.28); }
        .field input:hover { background: rgba(255,255,255,0.06); }
        .field input:focus {
            outline: none;
            background: rgba(255,255,255,0.06);
            border-color: var(--purple);
            box-shadow: 0 0 0 4px var(--focus);
        }

        .error {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(248, 113, 113, 0.1);
            border: 1px solid rgba(248, 113, 113, 0.22);
            color: var(--danger);
            font-family: var(--font-accent);
            font-size: 13px;
            font-weight: 500;
        }

        .submit {
            width: 100%;
            margin-top: 8px;
            border: 0;
            border-radius: 980px;
            padding: 14px 18px;
            background: var(--grad);
            color: #fff;
            font-family: var(--font-accent);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 10px 36px rgba(124, 58, 237, 0.4);
        }

        .submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 44px rgba(124, 58, 237, 0.5);
        }

        .submit:active { transform: translateY(0) scale(0.985); }
        .submit:focus-visible { outline: none; box-shadow: 0 0 0 4px var(--focus); }

        .submit svg {
            width: 16px;
            height: 16px;
            transform: scaleX(-1);
        }

        .foot {
            margin-top: 22px;
            font-family: var(--font-accent);
            color: rgba(255,255,255,0.4);
            font-size: 12px;
            animation: rise 1.15s var(--ease) 0.16s both;
        }

        .foot a {
            color: rgba(255,255,255,0.65);
            transition: color 0.2s;
        }

        .foot a:hover { color: #fff; }

        @keyframes rise {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes drift {
            from { transform: translate3d(0, 0, 0) scale(1); }
            to { transform: translate3d(2%, 3%, 0) scale(1.06); }
        }

        @media (prefers-reduced-motion: reduce) {
            .panel, .shell, .foot, .fx .glow {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
            .submit:hover { transform: none; }
        }

        @media (max-width: 480px) {
            .shell { padding: 22px 18px 18px; border-radius: 18px; }
            .brand { font-size: 30px; }
            .panel-logo { height: 44px; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="nav-brand" href="{{ route('home') }}" aria-label="Crawlix الصفحة الرئيسية">
                <img src="{{ asset('images/brand/crawlix-logo.png') }}" alt="Crawlix" width="210" height="56">
            </a>
            <a class="nav-back" href="{{ route('home') }}">العودة للرئيسية</a>
        </div>
    </header>

    <main class="stage">
        <div class="fx" aria-hidden="true">
            <div class="glow g1"></div>
            <div class="glow g2"></div>
        </div>

        <div class="panel">
            <img class="panel-logo" src="{{ asset('images/brand/crawlix-logo.png') }}" alt="Crawlix" width="180" height="95">
            <h1 class="brand">تسجيل الدخول</h1>

            <form class="shell" method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                @if ($errors->any())
                    <div class="error" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="field">
                    <label for="username">اسم المستخدم</label>
                    <input
                        id="username"
                        name="username"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        autocapitalize="none"
                        spellcheck="false"
                        placeholder="أدخل اسم المستخدم"
                        required
                        autofocus
                    >
                </div>

                <div class="field">
                    <label for="password">كلمة المرور</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="أدخل كلمة المرور"
                        required
                    >
                </div>

                <button class="submit" type="submit">
                    متابعة
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 6l6 6-6 6M3 12h17"/>
                    </svg>
                </button>
            </form>

            <p class="foot">Smarter Crawling. Bigger Insights. · <a href="{{ route('home') }}">Crawlix</a></p>
        </div>
    </main>
</body>
</html>
