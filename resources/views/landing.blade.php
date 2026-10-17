<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Crawlix: اجمع بيانات تواصل الشركات حسب القطاع والمدينة من استعلام عربي واحد.">
    <title>Crawlix | Smarter Crawling. Bigger Insights.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --black: #000000;
            --void: #050508;
            --surface: #0c0c12;
            --surface-2: #12121a;
            --ink: #ffffff;
            --muted: #9ca3af;
            --muted-2: #6b7280;
            --line: rgba(255, 255, 255, 0.08);
            --purple: #a855f7;
            --purple-deep: #7c3aed;
            --blue: #3b82f6;
            --blue-bright: #60a5fa;
            --grad: linear-gradient(135deg, #a855f7 0%, #6366f1 45%, #3b82f6 100%);
            --grad-soft: linear-gradient(135deg, rgba(168,85,247,0.25), rgba(59,130,246,0.18));
            --max: 1120px;
            --nav-h: 84px;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
            --font: "Cairo", "Segoe UI", Tahoma, sans-serif;
            --font-body: var(--font);
            --font-display: var(--font);
            --font-accent: var(--font);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-body);
            background: var(--void);
            color: var(--ink);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; border: 0; background: none; cursor: pointer; }
        ul, ol { list-style: none; }
        img { display: block; max-width: 100%; }

        .wrap {
            width: min(var(--max), calc(100% - 40px));
            margin-inline: auto;
        }

        .visually-hidden {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        .grad-text {
            background: var(--grad);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* Icons */
        .ico { width: 20px; height: 20px; flex-shrink: 0; }
        .ico-sm { width: 14px; height: 14px; }
        .ico-well {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: grid; place-items: center;
            background: var(--grad-soft);
            color: #c4b5fd;
            margin-bottom: 16px;
            border: 1px solid rgba(168,85,247,0.2);
        }
        .ico-well.green { background: rgba(52,211,153,0.12); color: #34d399; border-color: rgba(52,211,153,0.2); }
        .ico-well.danger { background: rgba(248,113,113,0.12); color: #f87171; border-color: rgba(248,113,113,0.2); }
        .ico-well.blue { background: rgba(59,130,246,0.12); color: #60a5fa; border-color: rgba(59,130,246,0.2); }
        [dir="rtl"] .btn .ico { transform: scaleX(-1); }

        /* Nav */
        .nav {
            position: fixed;
            inset-inline: 0; top: 0;
            z-index: 80;
            height: var(--nav-h);
            display: flex; align-items: center;
            background: rgba(5, 5, 8, 0.72);
            backdrop-filter: blur(18px) saturate(140%);
            -webkit-backdrop-filter: blur(18px) saturate(140%);
            border-bottom: 1px solid transparent;
            transition: border-color 0.25s, background 0.25s;
        }
        .nav.is-scrolled { border-bottom-color: var(--line); background: rgba(5,5,8,0.9); }
        .nav-inner {
            width: min(var(--max), calc(100% - 40px));
            margin-inline: auto;
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
        }
        .nav-brand {
            display: inline-flex; align-items: center;
            height: 56px;
        }
        .nav-brand img {
            height: 56px;
            width: auto;
            object-fit: contain;
            object-position: center right;
        }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-links a:not(.nav-cta) {
            font-family: var(--font-accent);
            font-size: 14px; font-weight: 500;
            padding: 8px 14px; border-radius: 980px;
            color: var(--muted);
            transition: color 0.2s, background 0.2s;
        }
        .nav-links a:not(.nav-cta):hover { color: #fff; background: rgba(255,255,255,0.05); }
        .nav-cta {
            display: inline-flex; align-items: center; gap: 6px;
            margin-inline-start: 8px;
            padding: 10px 18px; border-radius: 980px;
            font-family: var(--font-accent); font-size: 13px; font-weight: 600;
            background: var(--grad); color: #fff !important;
            box-shadow: 0 8px 28px rgba(124, 58, 237, 0.35);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .nav-cta:hover { transform: translateY(-1px); box-shadow: 0 12px 32px rgba(124,58,237,0.45); }
        .nav-toggle {
            display: none; width: 42px; height: 42px; border-radius: 12px;
            border: 1px solid var(--line); color: #fff;
            align-items: center; justify-content: center;
        }
        .nav-toggle span,
        .nav-toggle span::before,
        .nav-toggle span::after {
            display: block; width: 16px; height: 1.5px; background: currentColor; position: relative;
        }
        .nav-toggle span::before, .nav-toggle span::after { content: ""; position: absolute; inset-inline: 0; }
        .nav-toggle span::before { top: -5px; }
        .nav-toggle span::after { top: 5px; }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 14px 26px; border-radius: 980px;
            font-family: var(--font-accent); font-size: 15px; font-weight: 600;
            transition: transform 0.2s var(--ease), box-shadow 0.2s, background 0.2s, opacity 0.2s;
        }
        .btn:focus-visible { outline: none; box-shadow: 0 0 0 4px rgba(168,85,247,0.35); }
        .btn-primary {
            background: var(--grad); color: #fff;
            box-shadow: 0 10px 36px rgba(124, 58, 237, 0.4);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 44px rgba(124,58,237,0.5); }
        .btn-ghost {
            color: rgba(255,255,255,0.78);
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.03);
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.07); color: #fff; }
        .btn .ico { width: 16px; height: 16px; }

        /* Hero */
        .hero {
            position: relative;
            min-height: 100vh;
            min-height: 100dvh;
            padding: calc(var(--nav-h) + 56px) 0 0;
            overflow: hidden;
            isolation: isolate;
            display: flex;
            flex-direction: column;
        }
        .hero-fx {
            position: absolute; inset: 0; z-index: -1; pointer-events: none;
        }
        .hero-fx .glow {
            position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.5;
            animation: drift 18s ease-in-out infinite alternate;
        }
        .hero-fx .g1 {
            width: min(700px, 90vw); height: min(700px, 90vw);
            top: -25%; inset-inline-start: -20%;
            background: radial-gradient(circle, rgba(168,85,247,0.55), transparent 68%);
        }
        .hero-fx .g2 {
            width: min(560px, 70vw); height: min(560px, 70vw);
            top: 10%; inset-inline-end: -18%;
            background: radial-gradient(circle, rgba(59,130,246,0.4), transparent 70%);
            animation-delay: -6s;
        }
        .hero-fx .g3 {
            width: min(420px, 55vw); height: min(420px, 55vw);
            bottom: 10%; inset-inline-start: 30%;
            background: radial-gradient(circle, rgba(99,102,241,0.25), transparent 70%);
            animation-delay: -11s;
        }

        .hero-copy {
            text-align: center;
            max-width: 820px;
            margin: 0 auto;
            padding-bottom: 28px;
        }
        .hero-logo {
            height: 56px;
            width: auto;
            margin: 0 auto 28px;
            animation: rise 0.8s var(--ease) both;
        }
        .hero-title {
            font-family: var(--font-display);
            font-size: clamp(36px, 6.5vw, 64px);
            font-weight: 900;
            letter-spacing: -0.02em;
            line-height: 1.25;
            margin-bottom: 16px;
            animation: rise 0.9s var(--ease) 0.06s both;
        }
        .hero-lead {
            font-family: var(--font-accent);
            font-size: clamp(15px, 2vw, 18px);
            color: var(--muted);
            max-width: 36ch;
            margin: 0 auto 28px;
            animation: rise 1s var(--ease) 0.1s both;
        }
        .hero-tag {
            display: block;
            font-family: var(--font-accent);
            font-size: 13px;
            letter-spacing: 0.04em;
            color: rgba(255,255,255,0.45);
            margin-bottom: 22px;
            animation: rise 0.85s var(--ease) 0.04s both;
        }
        .hero-actions {
            display: flex; flex-wrap: wrap; justify-content: center; gap: 12px;
            animation: rise 1.05s var(--ease) 0.14s both;
        }

        .hero-visual {
            position: relative;
            margin-top: auto;
            width: 100%;
            min-height: 220px;
            padding-top: 40px;
            animation: rise 1.15s var(--ease) 0.18s both;
        }
        .network {
            width: min(920px, 100%);
            height: 240px;
            margin: 0 auto;
            display: block;
            opacity: 0.95;
            overflow: visible;
        }
        .network .wave {
            fill: none;
            stroke: url(#lineGrad);
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .network .wave-1 {
            stroke-width: 2;
            opacity: 0.9;
            stroke-dasharray: 18 22;
            animation:
                flow-dash 3.2s linear infinite,
                wave-drift-a 7s ease-in-out infinite alternate;
        }
        .network .wave-2 {
            stroke-width: 1.5;
            opacity: 0.5;
            stroke-dasharray: 14 20;
            animation:
                flow-dash 4.2s linear infinite reverse,
                wave-drift-b 9s ease-in-out infinite alternate;
        }
        .network .wave-3 {
            stroke-width: 1.25;
            opacity: 0.35;
            stroke-dasharray: 10 18;
            animation:
                flow-dash 5s linear infinite,
                wave-drift-c 8s ease-in-out infinite alternate;
        }
        .network .node {
            transform-origin: center;
            transform-box: fill-box;
            animation: node-pulse 3.2s ease-in-out infinite;
        }
        .network .node:nth-child(2) { animation-delay: 0.4s; }
        .network .node:nth-child(3) { animation-delay: 0.8s; }
        .network .node:nth-child(4) { animation-delay: 1.2s; }
        .network .node:nth-child(5) { animation-delay: 1.6s; }
        .network .node:nth-child(6) { animation-delay: 2s; }
        .network .node-core {
            filter: drop-shadow(0 0 6px rgba(168, 85, 247, 0.65));
        }
        .hero-fade {
            position: absolute;
            inset-inline: 0; bottom: 0; height: 100px;
            background: linear-gradient(to top, var(--void), transparent);
            pointer-events: none;
        }

        /* Strip */
        .strip {
            padding: 22px 0;
            border-block: 1px solid var(--line);
            background: rgba(255,255,255,0.015);
        }
        .strip-inner {
            display: flex; flex-wrap: wrap; justify-content: center; gap: 10px 28px;
            font-family: var(--font-accent); font-size: 13px; color: var(--muted-2);
        }
        .strip-inner span { display: inline-flex; align-items: center; gap: 8px; }

        /* Sections */
        section.block { padding: clamp(72px, 10vw, 110px) 0; }
        .section-head { max-width: 40rem; margin-bottom: clamp(32px, 5vw, 48px); }
        .section-head.center { margin-inline: auto; text-align: center; }
        .eyebrow {
            display: inline-block;
            font-family: var(--font-accent);
            font-size: 12px; font-weight: 600;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--purple);
            margin-bottom: 12px;
        }
        h2 {
            font-family: var(--font-display);
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 900; letter-spacing: -0.02em; line-height: 1.3;
            margin-bottom: 12px;
        }
        .section-lead {
            font-family: var(--font-accent);
            font-size: 16px; color: var(--muted);
            max-width: 44ch;
        }
        .section-head.center .section-lead { margin-inline: auto; }

        .card-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .card {
            padding: 26px 22px;
            border-radius: 20px;
            background: var(--surface);
            border: 1px solid var(--line);
            transition: transform 0.3s var(--ease), border-color 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(168,85,247,0.35);
            box-shadow: 0 24px 48px rgba(0,0,0,0.35), 0 0 0 1px rgba(168,85,247,0.12);
        }
        .card h3 {
            font-family: var(--font-display);
            font-size: 16px; font-weight: 700; margin-bottom: 8px;
        }
        .card p {
            font-family: var(--font-accent);
            font-size: 14px; color: var(--muted);
        }

        /* Bento */
        .bento {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 14px;
        }
        .bento-main, .bento-side {
            border-radius: 24px;
            border: 1px solid var(--line);
            background: var(--surface);
            overflow: hidden;
        }
        .bento-main {
            padding: clamp(28px, 4vw, 40px);
            background:
                radial-gradient(ellipse 70% 60% at 100% 0%, rgba(168,85,247,0.16), transparent 55%),
                var(--surface);
        }
        .bento-main > p {
            font-family: var(--font-accent);
            color: var(--muted); font-size: 16px;
            margin-bottom: 24px; max-width: 40ch;
        }
        .points { display: flex; flex-direction: column; gap: 12px; }
        .point {
            display: flex; align-items: center; gap: 12px;
            font-size: 14px; font-weight: 500;
        }
        .point-ico {
            width: 34px; height: 34px; border-radius: 10px;
            display: grid; place-items: center;
            background: var(--grad-soft); color: #c4b5fd;
            border: 1px solid rgba(168,85,247,0.2);
            flex-shrink: 0;
        }
        .dash-panel {
            height: 100%; min-height: 320px;
            padding: 24px;
            background:
                radial-gradient(ellipse 80% 50% at 0% 0%, rgba(59,130,246,0.18), transparent 55%),
                var(--surface-2);
            display: flex; flex-direction: column; gap: 14px;
        }
        .dash-live {
            display: flex; align-items: center; gap: 8px;
            font-family: var(--font-accent); font-size: 12px; color: var(--muted);
        }
        .live-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #34d399;
            box-shadow: 0 0 0 3px rgba(52,211,153,0.2);
        }
        .dash-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .dash-stat {
            padding: 14px 12px; border-radius: 14px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--line);
        }
        .dash-stat small {
            display: block; font-family: var(--font-accent);
            font-size: 11px; color: var(--muted); margin-bottom: 6px;
        }
        .dash-stat strong {
            font-family: var(--font-display);
            font-size: 22px; font-weight: 800;
        }
        .dash-list {
            flex: 1; padding: 14px; border-radius: 14px;
            background: rgba(255,255,255,0.025); border: 1px solid var(--line);
        }
        .dash-list h4 {
            font-family: var(--font-display); font-size: 14px;
            margin-bottom: 10px; display: flex; align-items: center; gap: 8px;
        }
        .dash-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-top: 1px solid var(--line);
            font-family: var(--font-accent); font-size: 12px; color: var(--muted);
        }

        .flow {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 28px;
        }
        .flow-item {
            text-align: center; padding: 22px 14px; border-radius: 18px;
            background: var(--surface); border: 1px solid var(--line);
        }
        .flow-item .ico-well { margin: 0 auto 12px; }
        .flow-item span { font-family: var(--font-accent); font-size: 13px; font-weight: 600; }

        .steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .step {
            padding: 24px; border-radius: 20px;
            background: var(--surface); border: 1px solid var(--line);
            transition: border-color 0.25s, transform 0.25s var(--ease);
        }
        .step:hover { border-color: rgba(168,85,247,0.4); transform: translateY(-3px); }
        .step-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .step-num {
            font-family: var(--font-display); font-size: 12px; font-weight: 700;
            background: var(--grad); -webkit-background-clip: text; background-clip: text; color: transparent;
            letter-spacing: 0.06em;
        }
        .step .ico-well { margin: 0; width: 40px; height: 40px; }
        .step h3 { font-family: var(--font-display); font-size: 16px; font-weight: 700; margin-bottom: 8px; }
        .step p { font-family: var(--font-accent); font-size: 13px; color: var(--muted); }

        .use-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .use-card {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 20px; border-radius: 16px;
            background: var(--surface); border: 1px solid var(--line);
            transition: transform 0.25s var(--ease), border-color 0.25s;
        }
        .use-card:hover { transform: translateY(-2px); border-color: rgba(168,85,247,0.35); }
        .use-card .ico-well { margin: 0; width: 42px; height: 42px; }
        .use-card span { font-family: var(--font-accent); font-size: 15px; font-weight: 600; }

        /* Pricing */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            align-items: stretch;
        }
        .price-card {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 28px 24px 24px;
            border-radius: 24px;
            background: var(--surface);
            border: 1px solid var(--line);
            transition: transform 0.3s var(--ease), border-color 0.3s, box-shadow 0.3s;
        }
        .price-card:hover {
            transform: translateY(-4px);
            border-color: rgba(168,85,247,0.35);
            box-shadow: 0 24px 48px rgba(0,0,0,0.35);
        }
        .price-card.featured {
            background:
                radial-gradient(ellipse 80% 50% at 50% 0%, rgba(168,85,247,0.22), transparent 60%),
                var(--surface-2);
            border-color: rgba(168,85,247,0.45);
            box-shadow: 0 0 0 1px rgba(168,85,247,0.15), 0 28px 64px rgba(124,58,237,0.2);
        }
        .price-badge {
            position: absolute;
            top: -11px;
            left: 50%;
            transform: translateX(-50%);
            padding: 5px 14px;
            border-radius: 980px;
            background: var(--grad);
            font-family: var(--font-accent);
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            box-shadow: 0 8px 20px rgba(124,58,237,0.35);
        }
        .price-name {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .price-desc {
            font-family: var(--font-accent);
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 22px;
            min-height: 2.6em;
        }
        .price-amount {
            display: flex;
            align-items: baseline;
            gap: 4px;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }
        .price-amount .currency {
            font-family: var(--font-accent);
            font-size: 18px;
            font-weight: 700;
            color: var(--muted);
        }
        .price-amount .value {
            font-family: var(--font-display);
            font-size: clamp(40px, 5vw, 48px);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1;
            background: var(--grad);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .price-card:not(.featured) .price-amount .value {
            background: none;
            -webkit-background-clip: unset;
            background-clip: unset;
            color: #fff;
        }
        .price-period {
            font-family: var(--font-accent);
            font-size: 13px;
            color: var(--muted-2);
            margin-bottom: 22px;
        }
        .price-features {
            display: flex;
            flex-direction: column;
            gap: 11px;
            margin-bottom: 26px;
            flex: 1;
        }
        .price-features li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-family: var(--font-accent);
            font-size: 13px;
            color: rgba(255,255,255,0.82);
            line-height: 1.45;
        }
        .price-features .check {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            margin-top: 1px;
            background: rgba(52,211,153,0.14);
            color: #34d399;
        }
        .price-features .check svg { width: 11px; height: 11px; }
        .price-card .btn { width: 100%; }
        .price-note {
            text-align: center;
            margin-top: 22px;
            font-family: var(--font-accent);
            font-size: 13px;
            color: var(--muted-2);
        }

        .cta-band { padding-bottom: clamp(72px, 10vw, 110px); }
        .cta-panel {
            position: relative; overflow: hidden; isolation: isolate;
            text-align: center;
            padding: clamp(48px, 7vw, 80px) 28px;
            border-radius: 28px;
            background: var(--surface);
            border: 1px solid var(--line);
        }
        .cta-panel::before {
            content: "";
            position: absolute; inset: 0; z-index: -1;
            background:
                radial-gradient(ellipse 55% 80% at 15% 0%, rgba(168,85,247,0.35), transparent 55%),
                radial-gradient(ellipse 50% 70% at 90% 100%, rgba(59,130,246,0.28), transparent 50%);
        }
        .cta-logo {
            height: 48px; width: auto; margin: 0 auto 20px;
        }
        .cta-panel h2 { max-width: 16ch; margin-inline: auto; }
        .cta-panel p {
            font-family: var(--font-accent); color: var(--muted);
            font-size: 16px; max-width: 38ch; margin: 0 auto 26px;
        }
        .cta-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; }

        .site-footer {
            padding: 28px 0 40px;
            border-top: 1px solid var(--line);
        }
        .footer-inner {
            display: flex; flex-wrap: wrap; align-items: center;
            justify-content: space-between; gap: 12px;
        }
        .footer-brand img { height: 28px; width: auto; }
        .footer-note { font-family: var(--font-accent); font-size: 13px; color: var(--muted-2); }

        .wa-float {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 90;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #25d366;
            color: #fff;
            box-shadow: 0 10px 28px rgba(37, 211, 102, 0.4);
            transition: transform 0.2s var(--ease), box-shadow 0.2s;
        }
        .wa-float:hover {
            transform: translateY(-3px) scale(1.04);
            box-shadow: 0 14px 36px rgba(37, 211, 102, 0.5);
        }
        .wa-float:focus-visible {
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.35);
        }
        .wa-float svg {
            width: 30px;
            height: 30px;
            fill: currentColor;
        }

        .reveal {
            opacity: 0; transform: translateY(22px);
            transition: opacity 0.75s var(--ease), transform 0.75s var(--ease);
        }
        .reveal.is-in { opacity: 1; transform: none; }

        @keyframes rise {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: none; }
        }
        @keyframes drift {
            from { transform: translate3d(0,0,0) scale(1); }
            to { transform: translate3d(3%, 4%, 0) scale(1.08); }
        }
        @keyframes pulse-node {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.45; }
        }
        @keyframes flow-dash {
            to { stroke-dashoffset: -80; }
        }
        @keyframes wave-drift-a {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }
        @keyframes wave-drift-b {
            from { transform: translateY(4px); }
            to { transform: translateY(-6px); }
        }
        @keyframes wave-drift-c {
            from { transform: translateY(-3px); }
            to { transform: translateY(8px); }
        }
        @keyframes node-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.72; transform: scale(1.12); }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .reveal, .hero-logo, .hero-title, .hero-lead, .hero-tag, .hero-actions, .hero-visual, .glow,
            .network .wave, .network .node {
                animation: none !important; opacity: 1 !important; transform: none !important; transition: none !important;
            }
            .card:hover, .step:hover, .use-card:hover, .btn:hover, .wa-float:hover { transform: none; }
        }

        @media (max-width: 980px) {
            .card-grid, .use-grid, .bento, .pricing-grid { grid-template-columns: 1fr 1fr; }
            .steps, .flow { grid-template-columns: 1fr 1fr; }
            .network { height: 180px; }
            .price-card.featured { grid-column: span 2; }
        }
        @media (max-width: 720px) {
            .wrap, .nav-inner { width: min(var(--max), calc(100% - 28px)); }
            .nav-toggle { display: inline-flex; }
            .nav-links {
                display: none; position: absolute;
                top: calc(var(--nav-h) + 8px); inset-inline: 14px;
                flex-direction: column; align-items: stretch; gap: 4px;
                padding: 10px; border-radius: 16px;
                background: #0c0c12; border: 1px solid var(--line);
                box-shadow: 0 24px 48px rgba(0,0,0,0.5);
            }
            .nav-links.is-open { display: flex; }
            .nav-links a:not(.nav-cta) { padding: 12px 14px; color: #fff; }
            .nav-cta { margin: 4px; justify-content: center; }
            .card-grid, .use-grid, .bento, .steps, .flow, .dash-stats, .pricing-grid { grid-template-columns: 1fr; }
            .price-card.featured { grid-column: auto; }
            .hero { padding-top: calc(var(--nav-h) + 40px); }
            .hero-logo { height: 44px; }
            .network { height: 150px; }
            .wa-float {
                bottom: 18px;
                right: 18px;
                width: 54px;
                height: 54px;
            }
        }
    </style>
</head>
<body>
    <svg xmlns="http://www.w3.org/2000/svg" class="visually-hidden" aria-hidden="true" focusable="false">
        <symbol id="i-map" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l-6 3V6l6-3 6 3 6-3v15l-6 3-6-3z"/><path d="M9 3v15M15 6v15"/></g></symbol>
        <symbol id="i-table" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M3 15h18M9 10v10M15 10v10"/></g></symbol>
        <symbol id="i-copy" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></g></symbol>
        <symbol id="i-search" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></g></symbol>
        <symbol id="i-phone" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h4l1 5-2.5 1.5a12 12 0 0 0 5 5L16 12l5 1v4a2 2 0 0 1-2 2A15 15 0 0 1 5 5a2 2 0 0 1 2-2z"/></g></symbol>
        <symbol id="i-database" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></g></symbol>
        <symbol id="i-chart" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5M4 19h16"/><path d="M8 16v-5M12 16V8M16 16v-8"/></g></symbol>
        <symbol id="i-check" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></g></symbol>
        <symbol id="i-shield" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/></g></symbol>
        <symbol id="i-stop" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><rect x="9" y="9" width="6" height="6" rx="1"/></g></symbol>
        <symbol id="i-download" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v12M7 11l5 5 5-5"/><path d="M5 20h14"/></g></symbol>
        <symbol id="i-building" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21V5a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v16M14 10h5a1 1 0 0 1 1 1v10"/><path d="M8 8h2M8 12h2M8 16h2M17 14h1M17 17h1M10 21h4"/></g></symbol>
        <symbol id="i-car" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 13l2-5a2 2 0 0 1 2-1h10a2 2 0 0 1 2 1l2 5"/><path d="M3 13h18v4a1 1 0 0 1-1 1h-1M5 18H4a1 1 0 0 1-1-1v-4"/><circle cx="7.5" cy="16.5" r="1.5"/><circle cx="16.5" cy="16.5" r="1.5"/></g></symbol>
        <symbol id="i-clinic" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21V8l8-4 8 4v13"/><path d="M9 21v-7h6v7M12 9v4M10 11h4"/></g></symbol>
        <symbol id="i-food" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11h16v1a8 8 0 0 1-16 0v-1z"/><path d="M8 11V5M12 11V3M16 11V5"/></g></symbol>
        <symbol id="i-home" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11l8-7 8 7"/><path d="M6 10v10h12V10"/><path d="M10 20v-5h4v5"/></g></symbol>
        <symbol id="i-shop" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 9l1.5-5h13L20 9"/><path d="M4 9h16v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9z"/><path d="M9 13h6"/></g></symbol>
        <symbol id="i-arrow" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l6 6-6 6M3 12h17"/></g></symbol>
        <symbol id="i-pin" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></g></symbol>
        <symbol id="i-spark" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.6 5.4L19 10l-5.4 1.6L12 17l-1.6-5.4L5 10l5.4-1.6L12 3z"/><path d="M19 15l.7 2.3L22 18l-2.3.7L19 21l-.7-2.3L16 18l2.3-.7L19 15z"/></g></symbol>
        <symbol id="i-message" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a8 8 0 0 1-8 8H7l-4 3V12a8 8 0 1 1 18 0z"/></g></symbol>
    </svg>

    <header class="nav" id="siteNav">
        <div class="nav-inner">
            <a class="nav-brand" href="{{ route('home') }}" aria-label="Crawlix الصفحة الرئيسية">
                <img src="{{ asset('images/brand/crawlix-logo.png') }}" alt="Crawlix" width="210" height="56">
            </a>
            <button class="nav-toggle" type="button" id="navToggle" aria-expanded="false" aria-controls="navMenu" aria-label="القائمة">
                <span></span>
            </button>
            <ul class="nav-links" id="navMenu">
                <li><a href="#features">المميزات</a></li>
                <li><a href="#how">كيف يعمل</a></li>
                <li><a href="#pricing">الباقات</a></li>
                <li><a href="#audience">لمن</a></li>
                @auth
                    <li><a class="nav-cta" href="{{ route('dashboard') }}">لوحة التحكم</a></li>
                @else
                    <li><a class="nav-cta" href="{{ route('login') }}">تسجيل الدخول</a></li>
                @endauth
            </ul>
        </div>
    </header>

    <main>
        <section class="hero" id="top" aria-labelledby="hero-title">
            <div class="hero-fx" aria-hidden="true">
                <div class="glow g1"></div>
                <div class="glow g2"></div>
                <div class="glow g3"></div>
            </div>

            <div class="wrap hero-copy">
                <img class="hero-logo" src="{{ asset('images/brand/crawlix-logo.png') }}" alt="Crawlix" width="220" height="116">
                <span class="hero-tag">Smarter Crawling. Bigger Insights.</span>
                <h1 class="hero-title" id="hero-title">
                    حوّل الويب إلى
                    <span class="grad-text">بيانات</span>
                    جاهزة للمبيعات
                </h1>
                <p class="hero-lead">اكتب القطاع والمدينة بالعربية. يجمع Crawlix الشركات وأرقام التواصل من الخرائط والبحث، يحفظها بدون تكرار، ويصدّر Excel.</p>
                <div class="hero-actions">
                    @auth
                        <a class="btn btn-primary" href="{{ route('search') }}">
                            افتح مساحة العمل
                            <svg class="ico" aria-hidden="true"><use href="#i-arrow"></use></svg>
                        </a>
                        <a class="btn btn-ghost" href="#how">كيف يعمل</a>
                    @else
                        <a class="btn btn-primary" href="{{ route('login') }}">
                            ابدأ الآن
                            <svg class="ico" aria-hidden="true"><use href="#i-arrow"></use></svg>
                        </a>
                        <a class="btn btn-ghost" href="#how">كيف يعمل</a>
                    @endauth
                </div>
            </div>

            <div class="hero-visual" aria-hidden="true">
                <svg class="network" viewBox="0 0 920 240" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="lineGrad" x1="0" y1="0" x2="920" y2="0" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#a855f7"/>
                            <stop offset="0.5" stop-color="#6366f1"/>
                            <stop offset="1" stop-color="#3b82f6"/>
                        </linearGradient>
                        <radialGradient id="nodeGlow" cx="0.5" cy="0.5" r="0.5">
                            <stop stop-color="#c4b5fd" stop-opacity="0.9"/>
                            <stop offset="1" stop-color="#3b82f6" stop-opacity="0"/>
                        </radialGradient>
                        <filter id="lineGlow" x="-20%" y="-40%" width="140%" height="180%">
                            <feGaussianBlur stdDeviation="2.5" result="blur"/>
                            <feMerge>
                                <feMergeNode in="blur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                    </defs>
                    <g filter="url(#lineGlow)">
                        <path class="wave wave-1" d="M40 180 C160 40, 280 220, 400 90 S620 200, 740 70 S860 160, 900 120">
                            <animate attributeName="d" dur="10s" repeatCount="indefinite" values="
                                M40 180 C160 40, 280 220, 400 90 S620 200, 740 70 S860 160, 900 120;
                                M40 165 C160 55, 280 200, 400 105 S620 175, 740 85 S860 145, 900 110;
                                M40 190 C160 30, 280 230, 400 80 S620 210, 740 60 S860 170, 900 130;
                                M40 180 C160 40, 280 220, 400 90 S620 200, 740 70 S860 160, 900 120"/>
                        </path>
                        <path class="wave wave-2" d="M20 120 C140 200, 260 40, 380 150 S580 40, 700 160 S820 80, 910 100">
                            <animate attributeName="d" dur="12s" repeatCount="indefinite" values="
                                M20 120 C140 200, 260 40, 380 150 S580 40, 700 160 S820 80, 910 100;
                                M20 135 C140 185, 260 55, 380 135 S580 55, 700 145 S820 95, 910 115;
                                M20 110 C140 210, 260 30, 380 160 S580 30, 700 170 S820 70, 910 90;
                                M20 120 C140 200, 260 40, 380 150 S580 40, 700 160 S820 80, 910 100"/>
                        </path>
                        <path class="wave wave-3" d="M60 80 C180 160, 300 20, 420 120 S640 200, 780 90 S870 140, 905 150">
                            <animate attributeName="d" dur="14s" repeatCount="indefinite" values="
                                M60 80 C180 160, 300 20, 420 120 S640 200, 780 90 S870 140, 905 150;
                                M60 95 C180 145, 300 35, 420 105 S640 185, 780 105 S870 125, 905 140;
                                M60 70 C180 170, 300 15, 420 130 S640 210, 780 80 S870 150, 905 160;
                                M60 80 C180 160, 300 20, 420 120 S640 200, 780 90 S870 140, 905 150"/>
                        </path>
                    </g>
                    <g class="nodes">
                        <g class="node">
                            <circle cx="120" cy="95" r="18" fill="url(#nodeGlow)" opacity="0.55"/>
                            <circle class="node-core" cx="120" cy="95" r="5" fill="#c4b5fd"/>
                        </g>
                        <g class="node">
                            <circle cx="260" cy="160" r="14" fill="url(#nodeGlow)" opacity="0.45"/>
                            <circle class="node-core" cx="260" cy="160" r="4.5" fill="#818cf8"/>
                        </g>
                        <g class="node">
                            <circle cx="400" cy="90" r="22" fill="url(#nodeGlow)" opacity="0.6"/>
                            <circle class="node-core" cx="400" cy="90" r="6" fill="#a855f7"/>
                        </g>
                        <g class="node">
                            <circle cx="540" cy="150" r="16" fill="url(#nodeGlow)" opacity="0.5"/>
                            <circle class="node-core" cx="540" cy="150" r="5" fill="#60a5fa"/>
                        </g>
                        <g class="node">
                            <circle cx="680" cy="85" r="20" fill="url(#nodeGlow)" opacity="0.55"/>
                            <circle class="node-core" cx="680" cy="85" r="5.5" fill="#3b82f6"/>
                        </g>
                        <g class="node">
                            <circle cx="800" cy="130" r="15" fill="url(#nodeGlow)" opacity="0.45"/>
                            <circle class="node-core" cx="800" cy="130" r="4.5" fill="#93c5fd"/>
                        </g>
                    </g>
                </svg>
                <div class="hero-fade"></div>
            </div>
        </section>

        <div class="strip" aria-hidden="true">
            <div class="wrap strip-inner">
                <span><svg class="ico ico-sm"><use href="#i-map"></use></svg> Google Maps</span>
                <span><svg class="ico ico-sm"><use href="#i-search"></use></svg> Google Search</span>
                <span><svg class="ico ico-sm"><use href="#i-database"></use></svg> MySQL ديناميكي</span>
                <span><svg class="ico ico-sm"><use href="#i-download"></use></svg> تصدير Excel</span>
                <span><svg class="ico ico-sm"><use href="#i-chart"></use></svg> داشبورد حي</span>
            </div>
        </div>

        <section class="block" id="audience" aria-labelledby="problem-title">
            <div class="wrap">
                <div class="section-head reveal">
                    <span class="eyebrow">التحدي</span>
                    <h2 id="problem-title">البحث اليدوي يبطئ فرق المبيعات</h2>
                    <p class="section-lead">التنقل بين الخرائط والجداول المبعثرة يستهلك الوقت ويُضاعف البيانات المكررة.</p>
                </div>
                <ul class="card-grid">
                    <li class="card reveal">
                        <div class="ico-well danger"><svg class="ico"><use href="#i-map"></use></svg></div>
                        <h3>صيد يدوي على الخرائط</h3>
                        <p>نسخ أرقام الهواتف سطراً بسطر من نتائج متفرقة حسب الحي والقطاع.</p>
                    </li>
                    <li class="card reveal">
                        <div class="ico-well danger"><svg class="ico"><use href="#i-table"></use></svg></div>
                        <h3>جداول فوضوية</h3>
                        <p>ملفات Excel غير موحّدة، أعمدة ناقصة، وبيانات بلا تصنيف حسب المدينة.</p>
                    </li>
                    <li class="card reveal">
                        <div class="ico-well danger"><svg class="ico"><use href="#i-copy"></use></svg></div>
                        <h3>تكرار يُهدر الجهد</h3>
                        <p>إعادة البحث عن نفس الشركات تُدخل أرقاماً مكررة إلى قوائم الاتصال.</p>
                    </li>
                </ul>
            </div>
        </section>

        <section class="block" aria-labelledby="solution-title" style="padding-top:0">
            <div class="wrap">
                <div class="bento reveal">
                    <div class="bento-main">
                        <span class="eyebrow">الحل</span>
                        <h2 id="solution-title">جملة واحدة تتحول إلى <span class="grad-text">بيانات</span> منظمة</h2>
                        <p>اكتب ما تحتاجه بالعربية مثل قطاع ومدينة، فيجمع Crawlix الشركات من خرائط جوجل مع إثراء من البحث، ويحفظها بدون تكرار حسب القطاع.</p>
                        <ul class="points">
                            <li class="point">
                                <span class="point-ico"><svg class="ico ico-sm"><use href="#i-phone"></use></svg></span>
                                استخراج الاسم ورقم الهاتف والبريد عند توفره
                            </li>
                            <li class="point">
                                <span class="point-ico"><svg class="ico ico-sm"><use href="#i-database"></use></svg></span>
                                تخزين فوري في جدول ديناميكي باسم القطاع
                            </li>
                            <li class="point">
                                <span class="point-ico"><svg class="ico ico-sm"><use href="#i-chart"></use></svg></span>
                                متابعة مباشرة من لوحة التحكم وتصدير Excel
                            </li>
                        </ul>
                    </div>
                    <div class="bento-side">
                        <div class="dash-panel" aria-hidden="true">
                            <div class="dash-live"><span class="live-dot"></span> تحديث مباشر</div>
                            <div class="dash-stats">
                                <div class="dash-stat"><small>الشركات</small><strong>1٬284</strong></div>
                                <div class="dash-stat"><small>القطاعات</small><strong>6</strong></div>
                                <div class="dash-stat"><small>بهواتف</small><strong>1٬102</strong></div>
                            </div>
                            <div class="dash-list">
                                <h4><svg class="ico ico-sm"><use href="#i-building"></use></svg> عقارات · realstate</h4>
                                <div class="dash-row"><span>الرياض</span><span>418</span></div>
                                <div class="dash-row"><span>جدة</span><span>291</span></div>
                                <div class="dash-row"><span>الدمام</span><span>164</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="flow reveal" aria-label="مسار العمل">
                    <li class="flow-item">
                        <div class="ico-well"><svg class="ico"><use href="#i-message"></use></svg></div>
                        <span>استعلام عربي</span>
                    </li>
                    <li class="flow-item">
                        <div class="ico-well blue"><svg class="ico"><use href="#i-map"></use></svg></div>
                        <span>خرائط وبحث</span>
                    </li>
                    <li class="flow-item">
                        <div class="ico-well green"><svg class="ico"><use href="#i-shield"></use></svg></div>
                        <span>حفظ بلا تكرار</span>
                    </li>
                    <li class="flow-item">
                        <div class="ico-well"><svg class="ico"><use href="#i-download"></use></svg></div>
                        <span>تصدير Excel</span>
                    </li>
                </ul>
            </div>
        </section>

        <section class="block" id="how" aria-labelledby="how-title" style="background:rgba(255,255,255,0.015)">
            <div class="wrap">
                <div class="section-head center reveal">
                    <span class="eyebrow">كيف يعمل</span>
                    <h2 id="how-title">أربع خطوات واضحة</h2>
                    <p class="section-lead">من الاستعلام إلى ملف Excel جاهز لفريقك بدون تعقيد.</p>
                </div>
                <ol class="steps">
                    <li class="step reveal">
                        <div class="step-top">
                            <span class="step-num">01</span>
                            <div class="ico-well"><svg class="ico"><use href="#i-message"></use></svg></div>
                        </div>
                        <h3>اكتب الاستعلام</h3>
                        <p>صِغ طلبك بالعربية: القطاع والمدينة أو المحافظة.</p>
                    </li>
                    <li class="step reveal">
                        <div class="step-top">
                            <span class="step-num">02</span>
                            <div class="ico-well blue"><svg class="ico"><use href="#i-map"></use></svg></div>
                        </div>
                        <h3>اجمع من الخرائط والبحث</h3>
                        <p>يبحث النظام في خرائط جوجل مع توسيع المناطق وإثراء من نتائج البحث.</p>
                    </li>
                    <li class="step reveal">
                        <div class="step-top">
                            <span class="step-num">03</span>
                            <div class="ico-well green"><svg class="ico"><use href="#i-shield"></use></svg></div>
                        </div>
                        <h3>احفظ بدون تكرار</h3>
                        <p>يُخزَّن كل سجل فوراً، ويُستبعد المكرر عند إعادة البحث.</p>
                    </li>
                    <li class="step reveal">
                        <div class="step-top">
                            <span class="step-num">04</span>
                            <div class="ico-well"><svg class="ico"><use href="#i-download"></use></svg></div>
                        </div>
                        <h3>صدّر وراقب</h3>
                        <p>حمّل Excel، أوقف الجولة إن احتجت، وتابع الإجماليات من الداشبورد.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section class="block" id="features" aria-labelledby="features-title">
            <div class="wrap">
                <div class="section-head reveal">
                    <span class="eyebrow">المميزات</span>
                    <h2 id="features-title">ما تحصل عليه فعلياً</h2>
                    <p class="section-lead">قدرات موثوقة مبنية لما يعمل المنتج اليوم بلا وعود زائدة.</p>
                </div>
                <ul class="card-grid">
                    <li class="card reveal">
                        <div class="ico-well"><svg class="ico"><use href="#i-spark"></use></svg></div>
                        <h3>بحث عربي طبيعي</h3>
                        <p>اكتب كما تتحدث: قطاع + مدينة، ويبدأ الجمع فوراً.</p>
                    </li>
                    <li class="card reveal">
                        <div class="ico-well blue"><svg class="ico"><use href="#i-map"></use></svg></div>
                        <h3>خرائط + مناطق + بحث</h3>
                        <p>تغطية خرائط جوجل مع توسيع المناطق وإثراء من بحث جوجل.</p>
                    </li>
                    <li class="card reveal">
                        <div class="ico-well green"><svg class="ico"><use href="#i-check"></use></svg></div>
                        <h3>منع التكرار</h3>
                        <p>إعادة البحث لا تُضاعف نفس الشركات في جدولك.</p>
                    </li>
                    <li class="card reveal">
                        <div class="ico-well"><svg class="ico"><use href="#i-database"></use></svg></div>
                        <h3>حفظ فوري حسب القطاع</h3>
                        <p>جدول MySQL ديناميكي باسم القطاع الإنجليزي مع المحافظة.</p>
                    </li>
                    <li class="card reveal">
                        <div class="ico-well danger"><svg class="ico"><use href="#i-stop"></use></svg></div>
                        <h3>إيقاف وحفظ جزئي</h3>
                        <p>أوقف الجولة في أي لحظة. ما جُمع يبقى محفوظاً.</p>
                    </li>
                    <li class="card reveal">
                        <div class="ico-well blue"><svg class="ico"><use href="#i-chart"></use></svg></div>
                        <h3>Excel وداشبورد حي</h3>
                        <p>تصدير جاهز للمتابعة، وإحصاءات قطاعات تتحدث مباشرة.</p>
                    </li>
                </ul>
            </div>
        </section>

        <section class="block" aria-labelledby="use-title" style="padding-top:0">
            <div class="wrap">
                <div class="section-head center reveal">
                    <span class="eyebrow">لمن</span>
                    <h2 id="use-title">فرق تحتاج بيانات تواصل حسب القطاع</h2>
                    <p class="section-lead">مناسب لفرق المبيعات والبحث وتطوير الأعمال في السعودية والأسواق المشابهة.</p>
                </div>
                <ul class="use-grid reveal">
                    <li class="use-card"><div class="ico-well"><svg class="ico"><use href="#i-building"></use></svg></div><span>عقارات</span></li>
                    <li class="use-card"><div class="ico-well blue"><svg class="ico"><use href="#i-car"></use></svg></div><span>تأجير سيارات</span></li>
                    <li class="use-card"><div class="ico-well"><svg class="ico"><use href="#i-clinic"></use></svg></div><span>عيادات ومراكز طبية</span></li>
                    <li class="use-card"><div class="ico-well"><svg class="ico"><use href="#i-food"></use></svg></div><span>مطاعم وضيافة</span></li>
                    <li class="use-card"><div class="ico-well blue"><svg class="ico"><use href="#i-home"></use></svg></div><span>خدمات منزلية</span></li>
                    <li class="use-card"><div class="ico-well"><svg class="ico"><use href="#i-shop"></use></svg></div><span>تجارة وتجزئة</span></li>
                </ul>
            </div>
        </section>

        <section class="block" id="pricing" aria-labelledby="pricing-title" style="background:rgba(255,255,255,0.015)">
            <div class="wrap">
                <div class="section-head center reveal">
                    <span class="eyebrow">الباقات</span>
                    <h2 id="pricing-title">أسعار واضحة</h2>
                    <p class="section-lead">اختر الباقة المناسبة لحجم فريقك. جميع الباقات تشمل البحث العربي، الجمع من الخرائط، والتصدير إلى Excel.</p>
                </div>

                <div class="pricing-grid">
                    <article class="price-card reveal">
                        <h3 class="price-name">Starter</h3>
                        <p class="price-desc">للانطلاق وجمع قوائم أساسية لقطاع واحد أو قطاعين.</p>
                        <div class="price-amount">
                            <span class="currency">$</span>
                            <span class="value">59</span>
                        </div>
                        <p class="price-period">شهرياً · USD</p>
                        <ul class="price-features">
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>بحث عربي طبيعي</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>جمع من خرائط جوجل</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>حفظ بدون تكرار</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>تصدير Excel</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>حتى 3٬000 سجل / شهر</li>
                        </ul>
                        <a class="btn btn-ghost" href="https://wa.me/966535815072?text={{ urlencode('مرحباً، أريد الاشتراك في باقة Starter') }}" target="_blank" rel="noopener noreferrer">اطلب الباقة</a>
                    </article>

                    <article class="price-card featured reveal">
                        <span class="price-badge">الأكثر طلباً</span>
                        <h3 class="price-name">Growth</h3>
                        <p class="price-desc">لفرق المبيعات التي تحتاج تغطية أوسع ومراقبة مباشرة.</p>
                        <div class="price-amount">
                            <span class="currency">$</span>
                            <span class="value">119</span>
                        </div>
                        <p class="price-period">شهرياً · USD</p>
                        <ul class="price-features">
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>كل مميزات Starter</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>إثراء من بحث جوجل + توسيع المناطق</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>داشبورد حي بإحصاءات القطاعات</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>إيقاف الجولة مع حفظ جزئي</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>حتى 12٬000 سجل / شهر</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>دعم عبر واتساب</li>
                        </ul>
                        <a class="btn btn-primary" href="https://wa.me/966535815072?text={{ urlencode('مرحباً، أريد الاشتراك في باقة Growth') }}" target="_blank" rel="noopener noreferrer">
                            ابدأ مع Growth
                            <svg class="ico" aria-hidden="true"><use href="#i-arrow"></use></svg>
                        </a>
                    </article>

                    <article class="price-card reveal">
                        <h3 class="price-name">Scale</h3>
                        <p class="price-desc">للعمليات الكبيرة والفرق التي تجمع بيانات عبر عدة قطاعات ومدن.</p>
                        <div class="price-amount">
                            <span class="currency">$</span>
                            <span class="value">249</span>
                        </div>
                        <p class="price-period">شهرياً · USD</p>
                        <ul class="price-features">
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>كل مميزات Growth</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>حتى 40٬000 سجل / شهر</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>قطاعات ومدن متعددة بدون حدود عملية</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>أولوية في الدعم والمتابعة</li>
                            <li><span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg></span>تهيئة مخصصة لحالات الاستخدام</li>
                        </ul>
                        <a class="btn btn-ghost" href="https://wa.me/966535815072?text={{ urlencode('مرحباً، أريد الاشتراك في باقة Scale') }}" target="_blank" rel="noopener noreferrer">تواصل للمشاركة</a>
                    </article>
                </div>

                <p class="price-note reveal">للتفعيل تواصل معنا عبر واتساب وسنجهّز حسابك.</p>
            </div>
        </section>

        <section class="cta-band" aria-labelledby="cta-title">
            <div class="wrap">
                <div class="cta-panel reveal">
                    <img class="cta-logo" src="{{ asset('images/brand/crawlix-logo.png') }}" alt="Crawlix" width="180" height="95">
                    <h2 id="cta-title">جاهز لبناء قائمتك التالية؟</h2>
                    <p>سجّل الدخول وابدأ باستعلام عربي. راقب التقدم، احفظ النتائج، وصدّر Excel لفريقك.</p>
                    <div class="cta-actions">
                        @auth
                            <a class="btn btn-primary" href="{{ route('search') }}">
                                الذهاب إلى البحث
                                <svg class="ico" aria-hidden="true"><use href="#i-arrow"></use></svg>
                            </a>
                            <a class="btn btn-ghost" href="{{ route('dashboard') }}">لوحة التحكم</a>
                        @else
                            <a class="btn btn-primary" href="{{ route('login') }}">
                                تسجيل الدخول
                                <svg class="ico" aria-hidden="true"><use href="#i-arrow"></use></svg>
                            </a>
                            <a class="btn btn-ghost" href="#how">كيف يعمل</a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="wrap footer-inner">
            <a class="footer-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/brand/crawlix-logo.png') }}" alt="Crawlix" width="120" height="36">
            </a>
            <p class="footer-note">Smarter Crawling. Bigger Insights.</p>
        </div>
    </footer>

    <a
        class="wa-float"
        href="https://wa.me/966535815072"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="تواصل عبر واتساب"
    >
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>

    <script>
        (() => {
            const nav = document.getElementById('siteNav');
            const toggle = document.getElementById('navToggle');
            const menu = document.getElementById('navMenu');

            const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 8);
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            if (toggle && menu) {
                toggle.addEventListener('click', () => {
                    const open = menu.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
                menu.querySelectorAll('a').forEach((link) => {
                    link.addEventListener('click', () => {
                        menu.classList.remove('is-open');
                        toggle.setAttribute('aria-expanded', 'false');
                    });
                });
            }

            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduce) {
                document.querySelectorAll('.reveal').forEach((el) => el.classList.add('is-in'));
                document.querySelectorAll('.network animate').forEach((el) => el.remove());
                return;
            }

            const io = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-in');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -36px 0px' });

            document.querySelectorAll('.reveal').forEach((el) => io.observe(el));
        })();
    </script>
</body>
</html>
