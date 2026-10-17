<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard — Crawlix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6f6f7;
            --text: #0d0d0d;
            --muted: #6f6f6f;
            --border: #e8e8e8;
            --card: #fff;
            --font: "Cairo", "Segoe UI", Tahoma, sans-serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .brand { font-weight: 700; }
        .header-actions { display: flex; gap: 10px; align-items: center; }
        .ghost, .danger {
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 999px;
            padding: 8px 14px;
            font: inherit;
            font-size: 13px;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }
        .danger { border-color: #ead7d7; }
        main { padding: 24px; max-width: 1100px; margin: 0 auto; }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 22px;
        }
        .summary-card, .sector-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px 18px;
        }
        .label { color: var(--muted); font-size: 13px; margin-bottom: 6px; }
        .value { font-size: 28px; font-weight: 700; }
        .live { font-size: 12px; color: #2e7d32; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 14px;
        }
        .sector-card h2 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: var(--muted); font-size: 13px; margin-bottom: 12px; }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 13px;
            padding: 7px 0;
            border-top: 1px solid #f1f1f1;
        }
        .empty { color: var(--muted); padding: 40px 0; text-align: center; }
        .gov { color: var(--muted); }
    </style>
</head>
<body>
<header>
    <div class="brand">Crawlix Dashboard</div>
    <div class="header-actions">
        <span class="live" id="liveStatus">تحديث مباشر</span>
        <a class="ghost" href="{{ route('search') }}">البحث</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="danger" type="submit">خروج</button>
        </form>
    </div>
</header>
<main>
    <div class="summary">
        <div class="summary-card">
            <div class="label">إجمالي الشركات</div>
            <div class="value" id="totalCompanies">0</div>
        </div>
        <div class="summary-card">
            <div class="label">عدد القطاعات</div>
            <div class="value" id="totalSectors">0</div>
        </div>
        <div class="summary-card">
            <div class="label">آخر تحديث</div>
            <div class="value" id="lastUpdate" style="font-size:16px;padding-top:8px;">—</div>
        </div>
    </div>
    <div class="grid" id="sectors"></div>
    <div class="empty" id="emptyState" hidden>لا توجد قطاعات محفوظة بعد.</div>
</main>
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const statsUrl = @json(route('dashboard.stats'));
    const sectorsEl = document.getElementById('sectors');
    const emptyState = document.getElementById('emptyState');

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;');
    }

    function render(payload) {
        const sectors = payload.sectors || [];
        document.getElementById('totalCompanies').textContent = payload.total ?? 0;
        document.getElementById('totalSectors').textContent = sectors.length;
        document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('ar-SA');

        if (!sectors.length) {
            sectorsEl.innerHTML = '';
            emptyState.hidden = false;
            return;
        }

        emptyState.hidden = true;
        sectorsEl.innerHTML = sectors.map((sector) => {
            const govs = (sector.governorates || []).map((gov) => `
                <div class="row">
                    <span class="gov">${escapeHtml(gov.name)}</span>
                    <strong>${gov.total}</strong>
                </div>
            `).join('');

            return `
                <article class="sector-card" data-slug="${escapeHtml(sector.slug)}">
                    <h2>${escapeHtml(sector.slug)}</h2>
                    <div class="meta">${sector.name_ar ? escapeHtml(sector.name_ar) : 'قطاع'} · جدول ${escapeHtml(sector.table)}</div>
                    <div class="row"><span>إجمالي البيانات</span><strong>${sector.total}</strong></div>
                    <div class="row"><span>معها إيميل</span><strong>${sector.with_email}</strong></div>
                    <div class="row"><span>معها هاتف</span><strong>${sector.with_phone}</strong></div>
                    ${govs}
                </article>
            `;
        }).join('');
    }

    async function refresh() {
        const response = await fetch(statsUrl, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!response.ok) return;
        render(await response.json());
    }

    refresh();
    setInterval(refresh, 3000);
})();
</script>
</body>
</html>
