<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>لوحة الأدمن — Crawlix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --void: #050508;
            --surface: #0c0c12;
            --panel: #111118;
            --line: rgba(255,255,255,0.08);
            --muted: #9ca3af;
            --text: #f5f5f7;
            --purple: #a855f7;
            --danger: #f87171;
            --ok: #34d399;
            --grad: linear-gradient(135deg, #a855f7 0%, #6366f1 45%, #3b82f6 100%);
            --font: "Cairo", "Segoe UI", Tahoma, sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font);
            background: var(--void);
            color: var(--text);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        header {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 22px;
            background: rgba(5,5,8,0.88);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(14px);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand img { height: 36px; width: auto; }
        .brand span {
            font-size: 13px;
            color: #d8b4fe;
            border: 1px solid rgba(168,85,247,0.35);
            background: rgba(168,85,247,0.12);
            border-radius: 999px;
            padding: 4px 10px;
            font-weight: 600;
        }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-radius: 999px;
            padding: 8px 14px;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--line);
            background: transparent;
            color: var(--text);
        }
        .btn-primary {
            border: 0;
            background: var(--grad);
            color: #fff;
        }
        .btn-danger {
            border-color: rgba(248,113,113,0.35);
            color: #fecaca;
            background: rgba(248,113,113,0.08);
        }
        main {
            width: min(1120px, calc(100% - 40px));
            margin: 28px auto 48px;
        }
        h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .intro {
            color: var(--muted);
            margin-bottom: 22px;
            font-size: 14px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .stat {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px 18px;
        }
        .stat .label { color: var(--muted); font-size: 13px; margin-bottom: 8px; }
        .stat .value { font-size: 28px; font-weight: 800; }
        .grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 16px;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
        }
        .card h2 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .flash {
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 14px;
        }
        .flash.ok {
            background: rgba(52,211,153,0.12);
            border: 1px solid rgba(52,211,153,0.28);
            color: #a7f3d0;
        }
        .flash.err {
            background: rgba(248,113,113,0.12);
            border: 1px solid rgba(248,113,113,0.28);
            color: #fecaca;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        .form-row.full { grid-template-columns: 1fr; }
        label {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.03);
            color: var(--text);
            border-radius: 10px;
            padding: 10px 12px;
            font: inherit;
            outline: none;
        }
        select option { color: #111; }
        input:focus,
        select:focus {
            border-color: rgba(168,85,247,0.5);
            box-shadow: 0 0 0 3px rgba(168,85,247,0.15);
        }
        .check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 10px 0 14px;
            font-size: 13px;
            color: var(--muted);
        }
        .check input { accent-color: var(--purple); }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            text-align: start;
            padding: 10px 8px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }
        th { color: var(--muted); font-weight: 600; }
        .pill {
            display: inline-flex;
            border-radius: 999px;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 700;
        }
        .pill.admin {
            background: rgba(168,85,247,0.15);
            color: #d8b4fe;
        }
        .pill.user {
            background: rgba(255,255,255,0.06);
            color: var(--muted);
        }
        .sector-list { display: grid; gap: 10px; }
        .sector-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.02);
        }
        .sector-item strong { display: block; margin-bottom: 2px; }
        .sector-item span { color: var(--muted); font-size: 12px; }
        .sector-item .num { font-weight: 800; font-size: 18px; }
        .empty {
            color: var(--muted);
            font-size: 13px;
            padding: 12px 0;
        }
        @media (max-width: 900px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 560px) {
            .stats { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            header { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <header>
        <div class="brand">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/brand/crawlix-logo.png') }}" alt="Crawlix" width="140" height="36">
            </a>
            <span>Admin</span>
        </div>
        <div class="actions">
            <a class="btn" href="{{ route('dashboard') }}">داشبورد البيانات</a>
            <a class="btn" href="{{ route('search') }}">البحث</a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn btn-danger" type="submit">خروج</button>
            </form>
        </div>
    </header>

    <main>
        <h1>لوحة التحكم</h1>
        <p class="intro">مرحباً {{ auth()->user()->name }} — إدارة المستخدمين ومتابعة بيانات القطاعات.</p>

        <section class="stats" aria-label="ملخص سريع">
            <article class="stat">
                <div class="label">المستخدمون</div>
                <div class="value">{{ $usersCount }}</div>
            </article>
            <article class="stat">
                <div class="label">الأدمن</div>
                <div class="value">{{ $adminsCount }}</div>
            </article>
            <article class="stat">
                <div class="label">القطاعات</div>
                <div class="value">{{ $sectorsCount }}</div>
            </article>
            <article class="stat">
                <div class="label">إجمالي السجلات</div>
                <div class="value">{{ number_format($totalRecords) }}</div>
            </article>
        </section>

        <div class="grid">
            <section class="card">
                <h2>المستخدمون</h2>

                @if (session('success'))
                    <div class="flash ok">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="flash err">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('admin.users.store') }}" style="margin-bottom: 18px;">
                    @csrf
                    <div class="form-row">
                        <div>
                            <label for="name">الاسم</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label for="email">الإيميل</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div>
                            <label for="password">كلمة المرور</label>
                            <input id="password" name="password" type="password" required>
                        </div>
                        <div>
                            <label for="plan">الباقة</label>
                            <select id="plan" name="plan">
                                <option value="" @selected(! old('plan'))>اختر الباقة</option>
                                @foreach ($plans as $key => $plan)
                                    <option value="{{ $key }}" @selected(old('plan') === $key)>
                                        {{ $plan['name'] }} — {{ number_format($plan['monthly_limit']) }} سجل / شهر (${{ $plan['price'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <label class="check">
                        <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin'))>
                        صلاحية أدمن
                    </label>
                    <button class="btn btn-primary" type="submit">إضافة مستخدم</button>
                </form>

                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>الإيميل</th>
                                <th>الباقة</th>
                                <th>الاستخدام</th>
                                <th>الصلاحية</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $usage = $user->usageSummary();
                                @endphp
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.users.plan', $user) }}" style="display:flex;gap:6px;align-items:center;">
                                            @csrf
                                            @method('PATCH')
                                            <select name="plan" onchange="this.form.submit()" style="min-width:120px;">
                                                <option value="">بدون باقة</option>
                                                @foreach ($plans as $key => $plan)
                                                    <option value="{{ $key }}" @selected($user->plan === $key)>{{ $plan['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        @if ($usage['limit'] === null)
                                            بلا حد
                                        @else
                                            {{ number_format($usage['used']) }} / {{ number_format($usage['limit']) }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="pill {{ $user->is_admin ? 'admin' : 'user' }}">
                                            {{ $user->is_admin ? 'أدمن' : 'مستخدم' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('حذف هذا المستخدم؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger" type="submit">حذف</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty">لا يوجد مستخدمون بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card">
                <h2>القطاعات والبيانات</h2>
                <div class="sector-list">
                    @forelse ($sectorStats as $sector)
                        <div class="sector-item">
                            <div>
                                <strong>{{ $sector['name_ar'] ?: $sector['slug'] }}</strong>
                                <span>{{ $sector['slug'] }} · إيميل {{ number_format($sector['with_email']) }} · هاتف {{ number_format($sector['with_phone']) }}</span>
                            </div>
                            <div class="num">{{ number_format($sector['total']) }}</div>
                        </div>
                    @empty
                        <p class="empty">لا توجد قطاعات مجمّعة بعد. ابدأ من صفحة البحث.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
</body>
</html>
