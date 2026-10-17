<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>دخول الأدمن — Crawlix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --void: #050508;
            --purple: #a855f7;
            --danger: #f87171;
            --grad: linear-gradient(135deg, #a855f7 0%, #6366f1 45%, #3b82f6 100%);
            --font: "Cairo", "Segoe UI", Tahoma, sans-serif;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font);
            color: #fff;
            background: var(--void);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        .stage {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 40px 20px;
            position: relative;
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
            opacity: 0.45;
        }
        .fx .g1 {
            width: min(560px, 80vw);
            height: min(560px, 80vw);
            top: -20%;
            inset-inline-start: -16%;
            background: radial-gradient(circle, rgba(168,85,247,0.55), transparent 68%);
        }
        .fx .g2 {
            width: min(480px, 65vw);
            height: min(480px, 65vw);
            bottom: -14%;
            inset-inline-end: -12%;
            background: radial-gradient(circle, rgba(59,130,246,0.4), transparent 70%);
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
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            border: 1px solid rgba(168,85,247,0.35);
            background: rgba(168,85,247,0.12);
            color: #d8b4fe;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 14px;
        }
        h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .lead {
            color: rgba(255,255,255,0.55);
            font-size: 14px;
            margin-bottom: 22px;
        }
        .shell {
            background: rgba(12,12,18,0.85);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 24px;
            text-align: start;
            backdrop-filter: blur(16px);
        }
        .field { margin-bottom: 14px; }
        label {
            display: block;
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 6px;
        }
        input {
            width: 100%;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
            color: #fff;
            border-radius: 12px;
            padding: 12px 14px;
            font: inherit;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus {
            border-color: rgba(168,85,247,0.55);
            box-shadow: 0 0 0 3px rgba(168,85,247,0.2);
        }
        .error {
            background: rgba(248,113,113,0.12);
            border: 1px solid rgba(248,113,113,0.3);
            color: #fecaca;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 14px;
        }
        .submit {
            width: 100%;
            margin-top: 6px;
            border: 0;
            border-radius: 12px;
            padding: 13px 16px;
            font: inherit;
            font-weight: 700;
            color: #fff;
            background: var(--grad);
            cursor: pointer;
            transition: transform 0.2s, filter 0.2s;
        }
        .submit:hover { transform: translateY(-1px); filter: brightness(1.06); }
        .foot {
            margin-top: 18px;
            font-size: 13px;
            color: rgba(255,255,255,0.45);
        }
        .foot a { color: rgba(255,255,255,0.7); }
        .foot a:hover { color: #fff; }
        @keyframes rise {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <main class="stage">
        <div class="fx" aria-hidden="true">
            <div class="glow g1"></div>
            <div class="glow g2"></div>
        </div>

        <div class="panel">
            <img class="panel-logo" src="{{ asset('images/brand/crawlix-logo.png') }}" alt="Crawlix" width="180" height="52">
            <div class="badge">لوحة التحكم</div>
            <h1>دخول الأدمن</h1>
            <p class="lead">إدارة المستخدمين والبيانات من مكان واحد</p>

            <form class="shell" method="POST" action="{{ route('admin.login') }}" novalidate>
                @csrf

                @if ($errors->any())
                    <div class="error" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="field">
                    <label for="email">البريد الإلكتروني</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        placeholder="admin@crawlix.com"
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

                <button class="submit" type="submit">دخول لوحة الأدمن</button>
            </form>

            <p class="foot"><a href="{{ route('home') }}">العودة للرئيسية</a></p>
        </div>
    </main>
</body>
</html>
