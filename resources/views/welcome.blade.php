<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crawlix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #fff;
            --sidebar: #f9f9f9;
            --sidebar-hover: #ececec;
            --sidebar-active: #e8e8e8;
            --border: #e5e5e5;
            --text: #0d0d0d;
            --text-secondary: #5d5d5d;
            --text-muted: #8f8f8f;
            --composer-bg: #f4f4f4;
            --composer-border: #0000001a;
            --user-bubble: #f4f4f4;
            --btn-black: #0d0d0d;
            --send-disabled: #d9d9d9;
            --shadow: 0 0 0 1px rgba(0,0,0,.05), 0 2px 8px rgba(0,0,0,.06);
            --radius-pill: 28px;
            --sidebar-w: 260px;
            --font: "Cairo", "Segoe UI", Tahoma, sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: var(--font);
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        button, textarea { font: inherit; color: inherit; }

        .app {
            display: flex;
            height: 100%;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: var(--sidebar);
            display: flex;
            flex-direction: column;
            border-right: 1px solid transparent;
            transition: margin-left .2s ease, transform .2s ease;
            z-index: 40;
        }

        .sidebar.collapsed {
            margin-left: calc(var(--sidebar-w) * -1);
        }

        .sidebar-top {
            padding: 12px 10px 8px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .side-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            border: 0;
            background: transparent;
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            text-align: left;
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            transition: background .15s ease;
        }

        .side-btn:hover { background: var(--sidebar-hover); }
        .side-btn svg { width: 18px; height: 18px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 1.75; }

        .side-btn.new-chat {
            justify-content: space-between;
        }

        .side-btn .kbd {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 400;
        }

        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 8px 10px 12px;
        }

        .nav-section { margin-bottom: 10px; }

        .history-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            padding: 10px 12px 6px;
        }

        .chat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            border: 0;
            background: transparent;
            border-radius: 10px;
            padding: 9px 12px;
            cursor: pointer;
            text-align: left;
            color: var(--text);
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-item:hover,
        .chat-item.active { background: var(--sidebar-active); }

        .chat-item span {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .usage-card {
            margin: 8px 10px 4px;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #fff;
        }
        .usage-card .usage-top {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 8px;
        }
        .usage-card .usage-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
        }
        .usage-card .usage-plan {
            font-size: 11px;
            color: var(--muted);
            font-weight: 600;
        }
        .usage-bar {
            height: 6px;
            border-radius: 999px;
            background: #ececec;
            overflow: hidden;
            margin-bottom: 8px;
        }
        .usage-bar > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #ab68ff, #5436da);
            width: 0%;
            transition: width 0.35s ease;
        }
        .usage-bar.near > span { background: linear-gradient(90deg, #f59e0b, #ef4444); }
        .usage-meta {
            font-size: 11px;
            color: var(--muted);
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .sidebar-bottom {
            padding: 10px;
            border-top: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .user-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            cursor: pointer;
        }

        .user-row:hover { background: var(--sidebar-hover); }

        .avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ab68ff, #5436da);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .user-meta { min-width: 0; }
        .user-meta .name {
            font-size: 14px;
            font-weight: 500;
            line-height: 1.2;
        }
        .user-meta .plan {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Main */
        .main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            background: var(--bg);
            position: relative;
        }

        .topbar {
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            flex-shrink: 0;
        }

        .top-left {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            border: 0;
            background: transparent;
            border-radius: 8px;
            display: grid;
            place-items: center;
            cursor: pointer;
            color: var(--text);
        }

        .icon-btn:hover { background: var(--sidebar-hover); }
        .icon-btn svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 1.75; }

        .model-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 0;
            background: transparent;
            border-radius: 10px;
            padding: 8px 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
        }

        .model-btn:hover { background: var(--sidebar-hover); }
        .model-btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; opacity: .55; }
        .model-btn .sub {
            font-weight: 500;
            color: var(--text-muted);
            font-size: 14px;
        }

        .top-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ghost-btn {
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 999px;
            padding: 7px 14px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        .ghost-btn:hover { background: var(--sidebar-hover); }

        .primary-btn {
            border: 0;
            background: var(--btn-black);
            color: #fff;
            border-radius: 999px;
            padding: 7px 14px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        .primary-btn:hover { opacity: .9; }

        /* Chat area */
        .chat-area {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 0 16px;
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0 16px 40px;
            gap: 24px;
        }

        .empty-state.hidden { display: none; }

        .greeting {
            font-size: 28px;
            font-weight: 600;
            letter-spacing: -0.02em;
            text-align: center;
            color: var(--text);
        }

        .composer-wrap {
            width: min(768px, 100%);
            margin: 0 auto;
        }

        .composer-shell {
            background: var(--composer-bg);
            border: 1px solid var(--composer-border);
            border-radius: var(--radius-pill);
            box-shadow: var(--shadow);
            padding: 10px 12px;
            display: flex;
            align-items: flex-end;
            gap: 8px;
            transition: box-shadow .15s ease;
        }

        .composer-shell:focus-within {
            box-shadow: 0 0 0 1px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.08);
        }

        .round-btn {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 50%;
            background: transparent;
            display: grid;
            place-items: center;
            cursor: pointer;
            color: var(--text);
            flex-shrink: 0;
        }

        .round-btn:hover { background: #e5e5e5; }
        .round-btn svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 1.75; }

        .composer-input {
            flex: 1;
            border: 0;
            outline: none;
            background: transparent;
            resize: none;
            max-height: 200px;
            min-height: 24px;
            line-height: 24px;
            padding: 6px 0;
            font-size: 16px;
            color: var(--text);
        }

        .composer-input::placeholder { color: var(--text-muted); }

        .composer-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .send-btn {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 50%;
            background: var(--send-disabled);
            color: #fff;
            display: grid;
            place-items: center;
            cursor: not-allowed;
            flex-shrink: 0;
            transition: background .15s ease;
        }

        .send-btn.active {
            background: var(--btn-black);
            cursor: pointer;
        }

        .send-btn svg { width: 18px; height: 18px; fill: currentColor; stroke: none; }

        .suggestions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: 14px;
        }

        .chip {
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 14px;
            color: var(--text);
            cursor: pointer;
            transition: background .15s ease;
        }

        .chip:hover { background: var(--sidebar-hover); }

        .footer-note {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            padding: 10px 16px 14px;
        }

        .composer-dock {
            width: min(768px, 100%);
            margin: 0 auto;
            padding: 0 16px 0;
            display: none;
        }

        .composer-dock.visible { display: block; }

        /* Messages layout */
        .thread {
            width: min(768px, 100%);
            margin: 0 auto;
            padding: 24px 0 32px;
            display: none;
        }

        .thread.visible { display: block; }

        .msg {
            display: flex;
            gap: 16px;
            margin-bottom: 28px;
        }

        .msg.user { justify-content: flex-end; }

        .msg-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 2px;
            display: grid;
            place-items: center;
            background: #000;
            color: #fff;
        }

        .msg-avatar svg { width: 16px; height: 16px; }

        .msg-body {
            max-width: 70%;
            font-size: 16px;
            line-height: 1.7;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .msg.user .msg-body {
            background: var(--user-bubble);
            border-radius: 22px;
            padding: 10px 16px;
            max-width: min(70%, 560px);
        }

        .msg.assistant .msg-body { max-width: 100%; padding-top: 2px; white-space: normal; }

        .typing {
            display: inline-flex;
            gap: 5px;
            align-items: center;
            padding: 4px 0;
        }

        .typing span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #b4b4b4;
            animation: bounce 1.2s infinite ease-in-out;
        }

        .typing span:nth-child(2) { animation-delay: .15s; }
        .typing span:nth-child(3) { animation-delay: .3s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: translateY(0); opacity: .45; }
            40% { transform: translateY(-4px); opacity: 1; }
        }

        .progress-box {
            margin-top: 10px;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px 14px;
            background: #fafafa;
            max-width: 480px;
        }

        .progress-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .progress-msg {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            min-height: 1.5em;
        }

        .progress-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .stat-pill {
            font-size: 12px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 4px 10px;
            color: var(--text);
        }

        .progress-bar-wrap {
            margin-top: 12px;
        }

        .progress-bar-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 12px;
            color: var(--text-secondary);
            gap: 10px;
        }

        .stop-record-btn {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 50%;
            background: #0d0d0d;
            color: #fff;
            display: grid;
            place-items: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .stop-record-btn:hover { background: #333; }
        .stop-record-btn:disabled { opacity: .55; cursor: wait; }

        .stop-record-btn .stop-square {
            width: 12px;
            height: 12px;
            background: #fff;
            border-radius: 3px;
            display: block;
        }

        .progress-bar-top strong {
            color: var(--text);
            font-size: 16px;
        }

        .progress-bar {
            height: 10px;
            background: #e8e8e8;
            border-radius: 999px;
            overflow: hidden;
        }

        .stop-btn {
            display: none;
        }

        .stop-btn:hover { background: #b71c1c; }
        .stop-btn:disabled { opacity: .6; cursor: wait; }

        .file-card {
            margin-top: 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #fff;
            max-width: 420px;
        }

        .file-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .file-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #e8f5e9;
            color: #1b5e20;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .file-name {
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-sub {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 0;
            background: var(--btn-black);
            color: #fff;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            white-space: nowrap;
        }

        .download-btn:hover { opacity: .9; }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.35);
            z-index: 30;
        }

        .overlay.show { display: block; }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                transform: translateX(-100%);
                margin-left: 0;
                box-shadow: 8px 0 24px rgba(0,0,0,.08);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar.collapsed {
                margin-left: 0;
                transform: translateX(-100%);
            }

            .top-right .ghost-btn,
            .top-right .primary-btn { display: none; }

            .greeting { font-size: 24px; }
        }
    </style>
</head>
<body>
<div class="app" id="app">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-top">
            <button class="side-btn new-chat" type="button" id="newChatBtn" title="New chat">
                <span style="display:flex;align-items:center;gap:12px;">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    New chat
                </span>
                <span class="kbd">⌘⇧O</span>
            </button>
            <button class="side-btn" type="button">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                Search chats
            </button>
            <button class="side-btn" type="button">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 9h8M8 13h5"/></svg>
                Library
            </button>
            <button class="side-btn" type="button">
                <svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                Sources
            </button>
            <button class="side-btn" type="button">
                <svg viewBox="0 0 24 24"><path d="M12 3l2.2 4.5L19 8.2l-3.5 3.4.8 4.9L12 14.8 7.7 16.5l.8-4.9L5 8.2l4.8-.7L12 3z"/></svg>
                Projects
            </button>
        </div>

        <div class="sidebar-scroll">
            <div class="nav-section">
                <div class="history-label">Today</div>
                <button class="chat-item active" type="button"><span>Getting started with Crawlix</span></button>
                <button class="chat-item" type="button"><span>شركات عقارية في الرياض</span></button>
                <button class="chat-item" type="button"><span>محلات اثاث في الرياض</span></button>
            </div>
            <div class="nav-section">
                <div class="history-label">Yesterday</div>
                <button class="chat-item" type="button"><span>مطاعم في جدة</span></button>
                <button class="chat-item" type="button"><span>عيادات أسنان في الدمام</span></button>
            </div>
            <div class="nav-section">
                <div class="history-label">Previous 7 Days</div>
                <button class="chat-item" type="button"><span>محلات ملابس في الرياض</span></button>
                <button class="chat-item" type="button"><span>شركات نظافة في جدة</span></button>
                <button class="chat-item" type="button"><span>مكاتب محاماة في الخبر</span></button>
            </div>
        </div>

        @php
            $authUser = auth()->user();
            $usage = $authUser?->usageSummary() ?? [
                'plan' => null,
                'plan_name' => 'بدون باقة',
                'used' => 0,
                'limit' => null,
                'remaining' => null,
                'percent' => 0,
            ];
            $usageInitial = mb_substr($authUser->name ?? 'U', 0, 1);
        @endphp
        <div class="usage-card" id="usageCard" data-usage='@json($usage)'>
            <div class="usage-top">
                <span class="usage-label">استخدام الباقة</span>
                <span class="usage-plan" id="usagePlanName">{{ $usage['plan_name'] }}</span>
            </div>
            <div class="usage-bar {{ ($usage['percent'] ?? 0) >= 85 ? 'near' : '' }}" id="usageBarWrap">
                <span id="usageBarFill" style="width: {{ $usage['percent'] ?? 0 }}%"></span>
            </div>
            <div class="usage-meta">
                <span id="usageCounts">
                    @if ($usage['limit'] === null)
                        بلا حد شهري
                    @else
                        {{ number_format($usage['used']) }} / {{ number_format($usage['limit']) }} سجل
                    @endif
                </span>
                <span id="usagePct">{{ $usage['percent'] ?? 0 }}%</span>
            </div>
        </div>
        <div class="sidebar-bottom">
            <a class="side-btn" href="https://wa.me/966535815072?text={{ urlencode('مرحباً، أريد ترقية باقتي على Crawlix') }}" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l2.5 2.5"/></svg>
                ترقية الباقة
            </a>
            <div class="user-row">
                <div class="avatar">{{ $usageInitial }}</div>
                <div class="user-meta">
                    <div class="name">{{ $authUser->name ?? 'User' }}</div>
                    <div class="plan" id="userPlanLabel">{{ $usage['plan_name'] }}</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="overlay" id="overlay"></div>

    <main class="main">
        <header class="topbar">
            <div class="top-left">
                <button class="icon-btn" type="button" id="toggleSidebar" aria-label="Toggle sidebar">
                    <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <button class="model-btn" type="button">
                    Crawlix
                    <svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                </button>
            </div>
            <div class="top-right">
                <a class="ghost-btn" href="{{ route('dashboard') }}">Dashboard</a>
                <button class="primary-btn" type="button">Sign up for free</button>
                <button class="icon-btn" type="button" aria-label="Share">
                    <svg viewBox="0 0 24 24"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/><path d="M12 3v12"/><path d="m7 8 5-5 5 5"/></svg>
                </button>
            </div>
        </header>

        <div class="chat-area">
            <div class="empty-state" id="emptyState">
                <h1 class="greeting">Where should we begin?</h1>
                <div class="composer-wrap">
                    <form class="composer-shell" id="composerFormEmpty">
                        <button class="round-btn" type="button" aria-label="Attach">
                            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        </button>
                        <textarea class="composer-input" id="inputEmpty" rows="1" placeholder="مثال: شركات عقارية في الرياض"></textarea>
                        <div class="composer-actions">
                            <button class="round-btn" type="button" aria-label="Voice">
                                <svg viewBox="0 0 24 24"><path d="M12 3a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0V6a3 3 0 0 0-3-3z"/><path d="M5 11a7 7 0 0 0 14 0"/><path d="M12 18v3"/></svg>
                            </button>
                            <button class="send-btn" type="submit" id="sendEmpty" aria-label="Send">
                                <svg viewBox="0 0 24 24"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
                            </button>
                        </div>
                    </form>
                    <div class="suggestions" id="suggestions">
                        <button class="chip" type="button">شركات عقارية في الرياض</button>
                        <button class="chip" type="button">مطاعم في جدة</button>
                        <button class="chip" type="button">عيادات أسنان في الدمام</button>
                        <button class="chip" type="button">محلات أثاث في الرياض</button>
                    </div>
                </div>
            </div>

            <div class="messages">
                <div class="thread" id="thread"></div>
            </div>

            <div class="composer-dock" id="composerDock">
                <form class="composer-shell" id="composerFormDock">
                    <button class="round-btn" type="button" aria-label="Attach">
                        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                    <textarea class="composer-input" id="inputDock" rows="1" placeholder="مثال: شركات عقارية في الرياض"></textarea>
                    <div class="composer-actions">
                        <button class="round-btn" type="button" aria-label="Voice">
                            <svg viewBox="0 0 24 24"><path d="M12 3a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0V6a3 3 0 0 0-3-3z"/><path d="M5 11a7 7 0 0 0 14 0"/><path d="M12 18v3"/></svg>
                        </button>
                        <button class="send-btn" type="submit" id="sendDock" aria-label="Send">
                            <svg viewBox="0 0 24 24"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            <p class="footer-note">Crawlix can make mistakes. Check important info.</p>
        </div>
    </main>
</div>

<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const scrapeUrl = @json(route('scrape'));
    const stopUrl = @json(route('scrape.stop'));
    let busy = false;
    let currentRunId = null;
    let stopping = false;
    let lastProgressEvent = null;

    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const toggleSidebar = document.getElementById('toggleSidebar');
    const emptyState = document.getElementById('emptyState');
    const thread = document.getElementById('thread');
    const composerDock = document.getElementById('composerDock');
    const newChatBtn = document.getElementById('newChatBtn');

    const inputs = {
        empty: document.getElementById('inputEmpty'),
        dock: document.getElementById('inputDock'),
    };
    const sends = {
        empty: document.getElementById('sendEmpty'),
        dock: document.getElementById('sendDock'),
    };

    const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

    function formatCount(n) {
        return Number(n || 0).toLocaleString('en-US');
    }

    function updateUsageUI(usage) {
        if (!usage) return;
        const planName = document.getElementById('usagePlanName');
        const userPlan = document.getElementById('userPlanLabel');
        const barWrap = document.getElementById('usageBarWrap');
        const barFill = document.getElementById('usageBarFill');
        const counts = document.getElementById('usageCounts');
        const pct = document.getElementById('usagePct');
        const percent = Number(usage.percent || 0);

        if (planName) planName.textContent = usage.plan_name || 'بدون باقة';
        if (userPlan) userPlan.textContent = usage.plan_name || 'بدون باقة';
        if (barFill) barFill.style.width = `${percent}%`;
        if (barWrap) barWrap.classList.toggle('near', percent >= 85);
        if (pct) pct.textContent = `${percent}%`;
        if (counts) {
            counts.textContent = usage.limit == null
                ? 'بلا حد شهري'
                : `${formatCount(usage.used)} / ${formatCount(usage.limit)} سجل`;
        }
    }

    function openSidebar() {
        if (isMobile()) {
            sidebar.classList.add('open');
            sidebar.classList.remove('collapsed');
            overlay.classList.add('show');
        } else {
            sidebar.classList.remove('collapsed');
        }
    }

    function closeSidebar() {
        if (isMobile()) {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        } else {
            sidebar.classList.add('collapsed');
        }
    }

    toggleSidebar.addEventListener('click', () => {
        if (isMobile()) {
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        } else {
            sidebar.classList.toggle('collapsed');
        }
    });

    overlay.addEventListener('click', closeSidebar);

    function autosize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 200) + 'px';
    }

    function syncSend(input, btn) {
        const hasText = input.value.trim().length > 0 && !busy;
        btn.classList.toggle('active', hasText);
        btn.disabled = busy;
    }

    function setBusy(state) {
        busy = state;
        Object.entries(inputs).forEach(([key, input]) => {
            input.disabled = state;
            syncSend(input, sends[key]);
        });
    }

    Object.entries(inputs).forEach(([key, input]) => {
        input.addEventListener('input', () => {
            autosize(input);
            syncSend(input, sends[key]);
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                input.closest('form').requestSubmit();
            }
        });
    });

    function assistantShell() {
        const msg = document.createElement('div');
        msg.className = 'msg assistant';
        msg.innerHTML = `
            <div class="msg-avatar" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a4 4 0 0 1 4 4v1h1a3 3 0 0 1 0 6h-.1A4.5 4.5 0 0 1 12 20.5 4.5 4.5 0 0 1 7.1 13H7a3 3 0 0 1 0-6h1V6a4 4 0 0 1 4-4z"/></svg>
            </div>
            <div class="msg-body"></div>
        `;
        thread.appendChild(msg);
        thread.parentElement.scrollTop = thread.parentElement.scrollHeight;
        return msg.querySelector('.msg-body');
    }

    function appendMessage(role, text) {
        if (role === 'assistant') {
            const body = assistantShell();
            body.textContent = text;
            return body;
        }

        const msg = document.createElement('div');
        msg.className = 'msg user';
        msg.innerHTML = `<div class="msg-body"></div>`;
        msg.querySelector('.msg-body').textContent = text;
        thread.appendChild(msg);
        thread.parentElement.scrollTop = thread.parentElement.scrollHeight;
        return msg.querySelector('.msg-body');
    }

    function showProgress(body, event = {}) {
        const found = Number(event.found || event.saved || 0);
        const listed = Number(event.listed || 0);
        const inserted = Number(event.inserted || 0);
        const existing = Number(event.existing || 0);
        const stored = Number(event.stored || 0);
        const percent = Math.max(0, Math.min(100, Number(event.percent ?? 0)));
        const message = event.message || 'جاري البحث في خرائط جوجل...';
        const stopBtn = event.can_stop === false ? '' : `
            <button class="stop-record-btn" type="button" data-stop-scrape aria-label="إيقاف البحث" title="إيقاف" ${stopping ? 'disabled' : ''}>
                <span class="stop-square"></span>
            </button>
        `;

        body.innerHTML = `
            <div>جاري البحث واستخراج البيانات...</div>
            <div class="progress-box">
                <div class="progress-title">التقدم الحالي</div>
                <div class="progress-msg">${escapeHtml(message)}</div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-top">
                        <span>نسبة الإنجاز <strong style="margin-inline-start:6px;">${percent}%</strong></span>
                        ${stopBtn}
                    </div>
                    <div class="progress-bar"><i style="width:${percent}%"></i></div>
                </div>
                <div class="progress-stats">
                    <span class="stat-pill">في الداتابيز: <strong>${stored}</strong></span>
                    <span class="stat-pill">في هذا البحث: <strong>${found}</strong></span>
                    <span class="stat-pill">جديد: <strong>${inserted}</strong></span>
                    <span class="stat-pill">كانت موجودة: <strong>${existing}</strong></span>
                    <span class="stat-pill">روابط: <strong>${listed || '—'}</strong></span>
                </div>
                <div class="typing" style="margin-top:10px;" aria-hidden="true"><span></span><span></span><span></span></div>
            </div>
        `;
        thread.parentElement.scrollTop = thread.parentElement.scrollHeight;
    }

    function showResult(body, data) {
        body.innerHTML = `
            <div>تم العثور على <strong>${data.count}</strong> نتيجة لـ “${escapeHtml(data.query)}”${data.stopped ? ' (تم الإيقاف يدويًا)' : ''}.</div>
            <div style="margin-top:8px;color:#5d5d5d;font-size:14px;line-height:1.7;">
                الجدول: <strong>${escapeHtml(data.table || data.sector || '')}</strong>
                | المحافظة: <strong>${escapeHtml(data.governorate || '')}</strong><br>
                جديد في قاعدة البيانات: <strong>${Number(data.inserted || 0)}</strong>
                | موجود مسبقًا: <strong>${Number(data.existing || 0)}</strong>
            </div>
            <div class="file-card">
                <div class="file-meta">
                    <div class="file-icon">XLS</div>
                    <div style="min-width:0;">
                        <div class="file-name">${escapeHtml(data.filename)}</div>
                        <div class="file-sub">${data.count} صف جاهز للتحميل</div>
                    </div>
                </div>
                <a class="download-btn" href="${escapeAttr(data.download_url)}" download>تحميل</a>
            </div>
        `;
        thread.parentElement.scrollTop = thread.parentElement.scrollHeight;
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;');
    }

    function escapeAttr(value) {
        return escapeHtml(value).replaceAll("'", '&#39;');
    }

    function enterChatMode() {
        emptyState.classList.add('hidden');
        thread.classList.add('visible');
        composerDock.classList.add('visible');
        inputs.dock.focus();
    }

    function resetChat() {
        if (busy) return;
        thread.innerHTML = '';
        thread.classList.remove('visible');
        composerDock.classList.remove('visible');
        emptyState.classList.remove('hidden');
        inputs.empty.value = '';
        inputs.dock.value = '';
        autosize(inputs.empty);
        autosize(inputs.dock);
        syncSend(inputs.empty, sends.empty);
        syncSend(inputs.dock, sends.dock);
        inputs.empty.focus();
    }

    async function requestStop() {
        if (!currentRunId || stopping || !busy) return;
        stopping = true;
        const btn = document.querySelector('[data-stop-scrape]');
        if (btn) {
            btn.disabled = true;
            btn.title = 'جاري الإيقاف...';
        }

        const progressBody = document.querySelector('#thread .msg.assistant:last-child .msg-body');
        if (progressBody) {
            showProgress(progressBody, {
                ...(lastProgressEvent || {}),
                message: 'تم طلب الإيقاف... جاري تجهيز ملف Excel بما تم جمعه حتى الآن.',
                percent: 95,
                can_stop: false,
            });
        }

        try {
            await fetch(stopUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ run_id: currentRunId }),
            });
        } catch (_) {
            // The scrape stream will still wrap up if the flag was written.
        }
    }

    async function scrape(query, onProgress) {
        const response = await fetch(scrapeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/x-ndjson',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ query }),
        });

        if (!response.ok || !response.body) {
            const fallback = await response.json().catch(() => ({}));
            throw new Error(fallback.message || 'فشل البحث.');
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let result = null;
        let lastProgress = null;

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop() || '';

            for (const line of lines) {
                const trimmed = line.trim();
                if (!trimmed) continue;

                let event;
                try {
                    event = JSON.parse(trimmed);
                } catch (_) {
                    continue;
                }

                if (event.run_id) {
                    currentRunId = event.run_id;
                }
                if (event.usage) {
                    updateUsageUI(event.usage);
                }
                if (event.type === 'progress') {
                    lastProgress = event;
                    lastProgressEvent = event;
                    onProgress?.(event);
                } else if (event.type === 'result') {
                    result = event;
                } else if (event.type === 'error') {
                    throw new Error(event.message || 'فشل البحث.');
                }
            }
        }

        if (buffer.trim()) {
            try {
                const event = JSON.parse(buffer.trim());
                if (event.usage) updateUsageUI(event.usage);
                if (event.type === 'result') result = event;
                if (event.type === 'error') throw new Error(event.message || 'فشل البحث.');
                if (event.type === 'progress') {
                    lastProgress = event;
                    onProgress?.(event);
                }
            } catch (e) {
                if (e.message && e.message !== 'Unexpected end of JSON input') throw e;
            }
        }

        if (!result) {
            const found = Number(lastProgress?.found || 0);
            const listed = Number(lastProgress?.listed || 0);
            if (stopping && found > 0) {
                throw new Error(
                    `تم الإيقاف. انتظر لحظات إضافية لتجهيز Excel، أو أعد البحث لو لم يظهر زر التحميل. (تم جمع ${found} نتيجة)`
                );
            }
            if (found > 0 || listed > 0) {
                throw new Error(
                    `انقطع الاتصال قبل حفظ الملف. كان التقدم: ${found} مستخرجة / ${listed} روابط. أعد المحاولة وانتظر حتى يظهر زر التحميل.`
                );
            }
            throw new Error('انتهى البحث بدون نتيجة.');
        }

        return result;
    }

    async function sendFrom(input, sendBtn) {
        const text = input.value.trim();
        if (!text || busy) return;

        enterChatMode();
        appendMessage('user', text);
        input.value = '';
        autosize(input);
        syncSend(input, sendBtn);

        const body = assistantShell();
        showProgress(body, { message: 'بدء البحث...', found: 0, listed: 0, total: 0, percent: 1, can_stop: true });
        setBusy(true);
        stopping = false;
        currentRunId = null;
        lastProgressEvent = null;

        try {
            const data = await scrape(text, (event) => {
                if (event.usage) updateUsageUI(event.usage);
                lastProgressEvent = event;
                showProgress(body, event);
            });
            if (data.usage) updateUsageUI(data.usage);
            showResult(body, data);
        } catch (error) {
            body.textContent = error.message || 'حدث خطأ أثناء البحث. حاول مرة أخرى.';
        } finally {
            setBusy(false);
            stopping = false;
            currentRunId = null;
            inputs.dock.focus();
        }
    }

    document.getElementById('composerFormEmpty').addEventListener('submit', (e) => {
        e.preventDefault();
        sendFrom(inputs.empty, sends.empty);
    });

    document.getElementById('composerFormDock').addEventListener('submit', (e) => {
        e.preventDefault();
        sendFrom(inputs.dock, sends.dock);
    });

    document.getElementById('suggestions').addEventListener('click', (e) => {
        const chip = e.target.closest('.chip');
        if (!chip || busy) return;
        inputs.empty.value = chip.textContent.trim();
        autosize(inputs.empty);
        syncSend(inputs.empty, sends.empty);
        document.getElementById('composerFormEmpty').requestSubmit();
    });

    thread.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-stop-scrape]');
        if (!btn) return;
        requestStop();
    });

    newChatBtn.addEventListener('click', resetChat);

    inputs.empty.focus();
})();
</script>
</body>
</html>
