<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartoh Technologies - Autonomous ISP Networking &amp; Fiber Infrastructure</title>
    <meta name="description" content="Next-generation autonomous ISP billing, live fiber optic diagnostics, MikroTik RouterOS v7 automation, RADIUS AAA and instant M-Pesa settlement for modern network operators.">
    <meta name="keywords" content="ISP billing, MikroTik automation, fiber optic diagnostics, OTDR, PPPoE billing, RADIUS AAA, Daraja M-Pesa, Kenya ISP, Smartoh Technologies">
    <meta property="og:title" content="Smartoh Technologies - Next-Gen Futuristic ISP Networking">
    <meta property="og:description" content="Onboard your modern ISP network infrastructure in 60 seconds with live fiber tools, router automation, and real-time billing.">
    <meta property="og:type" content="website">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --brand: #2563eb;
            --brand-glow: #3b82f6;
            --indigo-btn: #4f46e5;
            --indigo-btn-hover: #4338ca;
            --cyan: #00f0ff;
            --cyan-glow: rgba(0, 240, 255, 0.45);
            --magenta: #ec4899;
            --purple: #8b5cf6;
            --orange: #ff5722;
            --green: #10b981;
            --bg-dark: #030712;
            --bg-card: rgba(15, 23, 42, 0.65);
            --bg-glass: rgba(255, 255, 255, 0.04);
            --border: rgba(255, 255, 255, 0.1);
            --border-hover: rgba(0, 240, 255, 0.35);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-dim: #64748b;
        }

        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.5;
            position: relative;
        }

        /* CANVAS BACKGROUND: FIBER MOTION & PARTICLES */
        #fiber-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        /* AMBIENT GLOWS */
        .ambient-glow-1 {
            position: absolute;
            top: 5%;
            left: 50%;
            transform: translateX(-50%);
            width: 900px;
            height: 550px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.22) 0%, rgba(0, 240, 255, 0.12) 45%, transparent 70%);
            filter: blur(80px);
            z-index: 1;
            pointer-events: none;
            animation: pulse-ambient 8s ease-in-out infinite alternate;
        }
        .ambient-glow-2 {
            position: absolute;
            top: 40%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.15) 0%, rgba(139, 92, 246, 0.1) 50%, transparent 75%);
            filter: blur(90px);
            z-index: 1;
            pointer-events: none;
        }
        @keyframes pulse-ambient {
            0% { opacity: 0.7; transform: translateX(-50%) scale(0.95); }
            100% { opacity: 1; transform: translateX(-50%) scale(1.08); }
        }

        /* NAVIGATION BAR */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5rem;
            height: 74px;
            background: rgba(3, 7, 18, 0.75);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .logo-symbol {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5, #00f0ff);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 25px rgba(0, 240, 255, 0.4);
            position: relative;
            overflow: hidden;
        }
        .logo-symbol::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: translateX(-100%);
            animation: light-sweep 3.5s infinite;
        }
        @keyframes light-sweep {
            20%, 100% { transform: translateX(100%); }
        }
        .logo-brand {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        .logo-sub {
            font-size: 0.65rem;
            color: var(--cyan);
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }
        .nav-menu a {
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s, transform 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav-menu a:hover {
            color: #ffffff;
            transform: translateY(-1px);
        }
        .free-badge {
            background: #10b981;
            color: #030712;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.5);
            display: inline-block;
        }
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .btn-ghost {
            padding: 0.55rem 1.3rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #f8fafc;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            background: rgba(255, 255, 255, 0.03);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-ghost:hover {
            border-color: rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-1px);
        }
        .btn-primary-vibrant {
            padding: 0.65rem 1.6rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #4338ca 0%, #3b82f6 100%);
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 25px rgba(59, 130, 246, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-primary-vibrant:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 35px rgba(59, 130, 246, 0.7), inset 0 1px 1px rgba(255, 255, 255, 0.5);
            background: linear-gradient(135deg, #4f46e5 0%, #2563eb 100%);
        }

        /* HERO SECTION */
        .hero-section {
            position: relative;
            z-index: 10;
            min-height: 94vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 8.5rem 2rem 4rem;
        }
        .hero-container {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
        }

        /* PILL BADGE: NEW UPDATE */
        .hero-update-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(90deg, rgba(255, 87, 34, 0.2) 0%, rgba(236, 72, 153, 0.2) 100%);
            border: 1px solid rgba(255, 87, 34, 0.55);
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 800;
            color: #ff6b4a;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 2rem;
            box-shadow: 0 0 20px rgba(255, 87, 34, 0.3);
            animation: fadeInDown 0.6s ease;
        }
        .hero-update-pill span.live-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ff5722;
            box-shadow: 0 0 8px #ff5722;
            animation: ping 1.8s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        @keyframes ping {
            75%, 100% { transform: scale(2); opacity: 0; }
        }

        /* HERO HEADLINE */
        .hero-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2.6rem, 5.8vw, 4.6rem);
            font-weight: 800;
            line-height: 1.08;
            color: #ffffff;
            margin-bottom: 1.6rem;
            letter-spacing: -0.03em;
        }
        .hero-title .text-cyan-gradient {
            background: linear-gradient(90deg, #ffffff 0%, #00f0ff 60%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .blinking-cursor {
            display: inline-block;
            color: #ff5722;
            font-weight: 300;
            animation: blink 0.9s infinite;
        }
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        /* HERO SUBTITLE WITH HAND-DRAWN HIGHLIGHT */
        .hero-sub {
            font-size: 1.22rem;
            color: #cbd5e1;
            line-height: 1.65;
            max-width: 780px;
            margin: 0 auto 2.8rem;
            font-weight: 400;
        }
        .brush-highlight {
            position: relative;
            display: inline-block;
            font-weight: 700;
            color: #ffffff;
        }
        .brush-highlight svg.brush-stroke {
            position: absolute;
            left: -5%;
            bottom: -8px;
            width: 110%;
            height: 14px;
            pointer-events: none;
            z-index: -1;
            filter: drop-shadow(0 0 6px rgba(255, 87, 34, 0.8));
        }

        /* HERO ACTIONS */
        .hero-actions-group {
            display: flex;
            gap: 1.25rem;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .btn-hero-urgent {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 1rem 2.4rem;
            border-radius: 16px;
            background: linear-gradient(135deg, #4f46e5 0%, #2563eb 50%, #00d2ff 100%);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 40px rgba(37, 99, 235, 0.6), inset 0 1px 2px rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.25);
            position: relative;
            overflow: hidden;
        }
        .btn-hero-urgent:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 50px rgba(0, 240, 255, 0.7), inset 0 1px 2px rgba(255, 255, 255, 0.6);
        }
        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 1rem 2.2rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
        }
        .btn-hero-secondary:hover {
            border-color: rgba(0, 240, 255, 0.5);
            background: rgba(30, 41, 59, 0.85);
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(0, 240, 255, 0.25);
        }
        .hero-urgency-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.8rem;
            font-size: 0.85rem;
            color: #94a3b8;
            flex-wrap: wrap;
        }
        .urgency-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .urgency-item svg {
            color: #10b981;
            width: 16px;
            height: 16px;
        }

        /* FLOATING BADGES & WIDGETS (EXACTLY LIKE THE SCREENSHOT) */
        .hero-float-wrapper {
            position: relative;
            width: 100%;
            max-width: 1100px;
            margin-top: 3.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            pointer-events: none;
        }
        .hero-float-wrapper * {
            pointer-events: auto;
        }

        /* 1. FLOATING CIRCULAR BADGE (LEFT) */
        .float-badge-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 10px 40px rgba(168, 85, 247, 0.6), inset 0 2px 4px rgba(255, 255, 255, 0.4);
            border: 2px solid rgba(255, 255, 255, 0.3);
            animation: floatSlow 5s ease-in-out infinite alternate;
            transform: translateY(-20px);
            z-index: 15;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .float-badge-circle:hover {
            transform: scale(1.1) translateY(-25px);
        }
        .float-badge-num {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
        }
        .float-badge-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #fdf2f8;
        }

        /* 2. FLOATING AI COPILOT CHAT BUBBLE (RIGHT) */
        .float-chat-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
            z-index: 20;
            animation: floatMedium 6s ease-in-out infinite alternate;
        }
        .float-chat-bubble {
            background: #ffffff;
            color: #0f172a;
            padding: 12px 20px;
            border-radius: 20px 20px 4px 20px;
            font-size: 0.92rem;
            font-weight: 600;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.45);
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 340px;
            text-align: left;
            position: relative;
        }
        .float-chat-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #00f0ff;
            box-shadow: 0 0 10px rgba(0, 240, 255, 0.5);
        }
        .float-quick-chips {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .quick-chip {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(0, 240, 255, 0.3);
            color: #e2e8f0;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(8px);
            transition: all 0.2s;
            text-decoration: none;
        }
        .quick-chip:hover {
            border-color: #00f0ff;
            color: #00f0ff;
            background: rgba(0, 240, 255, 0.1);
            transform: translateY(-2px);
        }

        /* 3. FLOATING HARDWARE PREVIEW CARD */
        .float-preview-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 16px;
            width: 290px;
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: left;
            position: relative;
            overflow: hidden;
        }
        .float-preview-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #00f0ff, #8b5cf6, #ec4899);
        }
        .preview-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-card-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: #ffffff;
        }
        .preview-card-badge {
            font-size: 0.68rem;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .preview-mini-screen {
            background: #030712;
            border-radius: 10px;
            padding: 10px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.72rem;
            color: #38bdf8;
            line-height: 1.4;
            border: 1px solid rgba(0, 240, 255, 0.15);
        }

        @keyframes floatSlow {
            0% { transform: translateY(-10px) rotate(0deg); }
            100% { transform: translateY(-30px) rotate(3deg); }
        }
        @keyframes floatMedium {
            0% { transform: translateY(0); }
            100% { transform: translateY(-18px); }
        }

        /* LIVE TELEMETRY TICKER BAR */
        .ticker-strip {
            position: relative;
            z-index: 10;
            background: rgba(15, 23, 42, 0.6);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            overflow: hidden;
            padding: 1rem 0;
        }
        .ticker-track {
            display: flex;
            width: max-content;
            animation: scrollTicker 35s linear infinite;
        }
        .ticker-item {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 0 2.5rem;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            flex-shrink: 0;
        }
        .ticker-val {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            color: #00f0ff;
            text-shadow: 0 0 15px rgba(0, 240, 255, 0.4);
        }
        .ticker-desc {
            font-size: 0.78rem;
            color: #94a3b8;
            font-weight: 500;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        @keyframes scrollTicker {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* SECTION STYLING */
        section {
            position: relative;
            z-index: 10;
            padding: 6.5rem 2rem;
            max-width: 1280px;
            margin: 0 auto;
        }
        .section-tag {
            text-align: center;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--cyan);
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .section-tag::before, .section-tag::after {
            content: '';
            width: 24px;
            height: 2px;
            background: var(--cyan);
            opacity: 0.6;
        }
        .section-title {
            text-align: center;
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 1.2rem;
            letter-spacing: -0.02em;
        }
        .section-desc {
            text-align: center;
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.7;
            max-width: 680px;
            margin: 0 auto 3.8rem;
        }

        /* ===================================================
           MOTION NETWORKING TOOLSET: FIBER & ROUTER MODULES
           =================================================== */
        .motion-tools-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3.5rem;
        }
        .tool-panel-card {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.8) 0%, rgba(3, 7, 18, 0.95) 100%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            padding: 2.2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
            transition: all 0.3s;
        }
        .tool-panel-card:hover {
            border-color: rgba(0, 240, 255, 0.4);
            box-shadow: 0 25px 70px rgba(0, 240, 255, 0.15);
        }
        .panel-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .panel-label {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .panel-status-pill {
            font-size: 0.72rem;
            padding: 4px 10px;
            border-radius: 50px;
            background: rgba(0, 240, 255, 0.12);
            color: #00f0ff;
            border: 1px solid rgba(0, 240, 255, 0.3);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* 1. FIBER MOTION TOOL: LASER PHOTON SIMULATOR */
        .fiber-visualizer-box {
            position: relative;
            background: #030712;
            border-radius: 18px;
            padding: 1.5rem;
            border: 1px solid rgba(0, 240, 255, 0.2);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        #fiber-light-canvas {
            width: 100%;
            height: 140px;
            display: block;
            border-radius: 12px;
        }
        .fiber-controls {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }
        .wave-btn {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .wave-btn.active {
            background: rgba(0, 240, 255, 0.15);
            border-color: #00f0ff;
            color: #00f0ff;
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.3);
        }
        .fiber-telemetry-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 14px;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .tele-stat {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .tele-title {
            font-size: 0.72rem;
            color: var(--text-dim);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .tele-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.1rem;
            font-weight: 700;
            color: #10b981;
        }
        .btn-test-fiber {
            width: 100%;
            margin-top: 1.2rem;
            padding: 0.8rem;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.2), rgba(37, 99, 235, 0.25));
            border: 1px solid rgba(0, 240, 255, 0.4);
            color: #00f0ff;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-test-fiber:hover {
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.35), rgba(37, 99, 235, 0.45));
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.35);
        }

        /* 2. ROUTER MOTION TOOL: MIKROTIK CCR2216 HARDWARE FACEPLATE */
        .router-chassis {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border: 2px solid #334155;
            border-radius: 16px;
            padding: 1.5rem 1.25rem;
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.1), 0 15px 35px rgba(0, 0, 0, 0.8);
            position: relative;
            margin-bottom: 1.5rem;
        }
        .chassis-rack-ears {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 12px;
            height: 80%;
            background: #475569;
            border-radius: 4px;
        }
        .chassis-rack-ears.left { left: 4px; }
        .chassis-rack-ears.right { right: 4px; }
        .router-faceplate-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0 0.5rem;
        }
        .brand-badge-mikrotik {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .router-oled-display {
            background: #020617;
            border: 1px solid #1e293b;
            border-radius: 6px;
            padding: 4px 10px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.68rem;
            color: #00f0ff;
            letter-spacing: 0.05em;
        }
        .ports-matrix {
            display: flex;
            gap: 8px;
            background: #020617;
            border-radius: 10px;
            padding: 10px;
            border: 1px solid #1e293b;
            overflow-x: auto;
        }
        .port-socket {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 6px;
            padding: 6px 8px;
            cursor: pointer;
            transition: all 0.2s;
            min-width: 44px;
        }
        .port-socket:hover {
            border-color: #00f0ff;
            background: #0f172a;
            transform: scale(1.05);
        }
        .port-led {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #334155;
            transition: all 0.2s;
        }
        .port-led.active-green {
            background: #10b981;
            box-shadow: 0 0 8px #10b981;
            animation: portBlink 0.4s infinite alternate;
        }
        .port-led.active-amber {
            background: #f59e0b;
            box-shadow: 0 0 8px #f59e0b;
        }
        @keyframes portBlink {
            0% { opacity: 0.4; }
            100% { opacity: 1; }
        }
        .port-hole {
            width: 22px;
            height: 16px;
            background: #020617;
            border-radius: 3px;
            border: 1px solid #475569;
            position: relative;
        }
        .port-name {
            font-size: 0.6rem;
            font-family: 'JetBrains Mono', monospace;
            color: #94a3b8;
        }
        .router-telemetry-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .mini-stat-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            padding: 8px;
            text-align: center;
        }
        .mini-stat-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.92rem;
            font-weight: 700;
            color: #38bdf8;
        }
        .mini-stat-lbl {
            font-size: 0.65rem;
            color: var(--text-dim);
            text-transform: uppercase;
        }

        /* ===================================================
           URGE OF ONBOARDING: 60-SECOND ZERO-TOUCH SIMULATOR
           =================================================== */
        .onboarding-epic-wrapper {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.15) 0%, rgba(0, 240, 255, 0.08) 50%, rgba(236, 72, 153, 0.12) 100%);
            border: 2px solid rgba(0, 240, 255, 0.35);
            border-radius: 32px;
            padding: 3.5rem 3rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.7), inset 0 1px 2px rgba(255, 255, 255, 0.2);
            margin: 4rem auto;
            max-width: 1200px;
        }
        .onboarding-epic-wrapper::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(0, 240, 255, 0.05) 0%, transparent 60%);
            pointer-events: none;
        }
        .onboard-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 3rem;
            align-items: center;
            position: relative;
            z-index: 5;
        }
        .onboard-headline {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.3rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            margin-bottom: 1.25rem;
        }
        .onboard-sub {
            color: #cbd5e1;
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .onboard-step-pill {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 1.25rem;
        }
        .step-num-badge {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: linear-gradient(135deg, #4f46e5, #00f0ff);
            color: #ffffff;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.4);
        }
        .step-text h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 2px;
        }
        .step-text p {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* INTERACTIVE ONBOARDING TERMINAL */
        .onboard-terminal-box {
            background: #030712;
            border: 1px solid rgba(0, 240, 255, 0.35);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9);
        }
        .terminal-header-bar {
            background: #0f172a;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .terminal-dots {
            display: flex;
            gap: 6px;
        }
        .t-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .t-red { background: #ef4444; }
        .t-yellow { background: #f59e0b; }
        .t-green { background: #10b981; }
        .terminal-title {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .terminal-content {
            padding: 1.25rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem;
            color: #38bdf8;
            height: 260px;
            overflow-y: auto;
            line-height: 1.6;
            background: radial-gradient(circle at top right, rgba(0, 240, 255, 0.04), transparent);
        }
        .terminal-line {
            margin-bottom: 6px;
            animation: lineFade 0.3s ease;
        }
        .t-green-text { color: #10b981; }
        .t-cyan-text { color: #00f0ff; }
        .t-magenta-text { color: #ec4899; }
        .terminal-trigger-bar {
            padding: 14px 16px;
            background: #0f172a;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            gap: 10px;
        }
        .btn-run-onboard-sim {
            flex: 1;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.88rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-run-onboard-sim:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.6);
        }

        /* NETWORK TOPOLOGY VISUALIZATION */
        .topology-canvas-wrapper {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        #tc {
            width: 100%;
            height: 380px;
            display: block;
            border-radius: 16px;
        }

        /* FEATURE GRID */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.8rem;
        }
        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.2rem;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--cyan), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .feature-card:hover {
            border-color: var(--border-hover);
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), 0 0 35px rgba(0, 240, 255, 0.12);
        }
        .feature-card:hover::before {
            opacity: 1;
        }
        .feat-icon-box {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.4rem;
        }
        .feat-icon-box svg {
            width: 28px;
            height: 28px;
        }
        .feat-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.75rem;
        }
        .feat-text {
            font-size: 0.92rem;
            color: var(--text-muted);
            line-height: 1.65;
        }
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 1.2rem;
        }
        .tech-pill {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 50px;
            background: rgba(0, 240, 255, 0.08);
            border: 1px solid rgba(0, 240, 255, 0.2);
            color: var(--cyan);
        }

        /* PRICING PLANS */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.8rem;
            max-width: 1050px;
            margin: 0 auto;
        }
        .price-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.3s;
            position: relative;
        }
        .price-card.featured {
            border-color: #00f0ff;
            background: linear-gradient(180deg, rgba(0, 240, 255, 0.08) 0%, rgba(15, 23, 42, 0.8) 100%);
            box-shadow: 0 20px 60px rgba(0, 240, 255, 0.2);
            transform: scale(1.03);
        }
        .price-tier-tag {
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--cyan);
            margin-bottom: 0.75rem;
        }
        .price-number {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.8rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1;
        }
        .price-number span {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-muted);
        }
        .price-features-list {
            list-style: none;
            text-align: left;
            margin: 2rem 0;
        }
        .price-features-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.88rem;
            color: #e2e8f0;
            padding: 0.45rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }
        .price-features-list li svg {
            width: 16px;
            height: 16px;
            color: #10b981;
            flex-shrink: 0;
        }

        /* FOOTER */
        footer {
            position: relative;
            z-index: 10;
            background: rgba(3, 7, 18, 0.95);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 4.5rem 2.5rem 2.5rem;
        }
        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
        }
        .footer-brand-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.75rem;
        }
        .footer-brand-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.65;
            max-width: 320px;
        }
        .footer-col h4 {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #cbd5e1;
            margin-bottom: 1.2rem;
        }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 0.65rem; }
        .footer-col ul li a {
            font-size: 0.88rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-col ul li a:hover { color: #ffffff; }
        .footer-bottom {
            max-width: 1200px;
            margin: 3rem auto 0;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .footer-bottom p {
            font-size: 0.82rem;
            color: #64748b;
        }
        .status-pill-live {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: #10b981;
            font-weight: 600;
        }
        .status-dot-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 1024px) {
            .motion-tools-grid { grid-template-columns: 1fr; }
            .onboard-grid { grid-template-columns: 1fr; }
            .footer-inner { grid-template-columns: 1fr 1fr; }
            .hero-float-wrapper { flex-direction: column; align-items: center; gap: 2rem; }
            .float-badge-circle { margin-bottom: 1rem; }
        }
        @media (max-width: 768px) {
            nav { padding: 0 1.25rem; }
            .nav-menu { display: none; }
            .hero-title { font-size: 2.4rem; }
            .hero-actions-group { flex-direction: column; width: 100%; }
            .btn-hero-urgent, .btn-hero-secondary { width: 100%; justify-content: center; }
            .footer-inner { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<!-- GENERATIVE 3D FIBER WAVE & PHOTON CANVAS -->
<canvas id="fiber-canvas"></canvas>
<div class="ambient-glow-1"></div>
<div class="ambient-glow-2"></div>

<!-- NAVIGATION BAR -->
<nav id="navbar">
    <a href="{{ route('landing') }}" class="nav-logo">
        <div class="logo-symbol">
            <svg width="22" height="22" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div>
            <div class="logo-brand">Smartoh</div>
            <div class="logo-sub">Networking Core</div>
        </div>
    </a>

    <ul class="nav-menu">
        <li><a href="#motion-tools">Fiber &amp; Router Tools</a></li>
        <li><a href="#onboarding-engine">Urgent Onboarding</a></li>
        <li><a href="#features">Architecture</a></li>
        <li><a href="#pricing">Pricing</a></li>
        <li><a href="#netdemo">Live Network <span class="free-badge">FREE</span></a></li>
    </ul>

    <div class="nav-actions">
        <a href="{{ route('login') }}" class="btn-ghost">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            Sign In
        </a>
        @if (Route::has('register'))
        <a href="{{ route('register') }}" class="btn-primary-vibrant" id="nav-onboard-btn">
            <span>Onboard Network</span>
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
        @else
        <a href="{{ route('login') }}" class="btn-primary-vibrant">
            <span>Deploy Console</span>
        </a>
        @endif
    </div>
</nav>

<!-- HERO SECTION: FUTURISTIC NETWORKING THEME (MATCHING SCREENSHOT) -->
<section class="hero-section">
    <div class="hero-container">
        <!-- NEW UPDATE PILL BADGE -->
        <div class="hero-update-pill">
            <span class="live-pulse-dot"></span>
            NEW UPDATE: AUTONOMOUS FIBER &amp; ROUTER CORE v4.2
        </div>

        <!-- GIANT TYPING HEADLINE -->
        <h1 class="hero-title">
            The Only ISP Platform<br>
            With Real <span id="typewriter-text" class="text-cyan-gradient">Autonomous Core</span><span class="blinking-cursor">_</span>
        </h1>

        <!-- SUBTITLE WITH HAND-DRAWN FLUORESCENT HIGHLIGHT -->
        <p class="hero-sub">
            Smartoh Technologies
            <span class="brush-highlight">
                delivers full autonomous ISP networking
                <svg class="brush-stroke" viewBox="0 0 420 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 12C90 4 280 2 414 8C330 14 170 16 12 14" stroke="#ff5722" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            right out of the box with zero-touch MikroTik provisioning, real-time fiber OTDR diagnostics, and instant M-Pesa settlement.
        </p>

        <!-- HIGH URGENCY CALL-TO-ACTION BUTTONS -->
        <div class="hero-actions-group">
            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="btn-hero-urgent" id="hero-onboard-cta">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Onboard Your Network Free</span>
            </a>
            @endif
            <a href="#motion-tools" class="btn-hero-secondary">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                <span>Test Fiber &amp; Router Tools</span>
            </a>
        </div>

        <div class="hero-urgency-note">
            <div class="urgency-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Instant MikroTik v7 Auto-Discovery</div>
            <div class="urgency-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> 0-Touch GPON / XGS-PON Setup</div>
            <div class="urgency-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> 30-Day Full Infrastructure Trial</div>
        </div>

        <!-- FLOATING SCREENSHOT-THEMED WIDGETS -->
        <div class="hero-float-wrapper">
            <!-- 1. CIRCULAR FLOATING BADGE -->
            <div class="float-badge-circle" title="Click to view 100G Fiber specs" onclick="document.getElementById('motion-tools').scrollIntoView({behavior:'smooth'})">
                <span class="float-badge-num">100G</span>
                <span class="float-badge-label">Fiber Core</span>
            </div>

            <!-- 2. FLOATING COPILOT AI CHAT BUBBLE & QUICK ACTIONS -->
            <div class="float-chat-container">
                <div class="float-chat-bubble">
                    <div class="float-chat-avatar">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:0.75rem; color:#64748b; font-weight:700;">NOC AI COPILOT</div>
                        Ready to onboard your router or diagnose fiber link loss?
                    </div>
                </div>
                <div class="float-quick-chips">
                    <a href="#onboarding-engine" class="quick-chip">⚡ 60s Auto-Onboard</a>
                    <a href="#motion-tools" class="quick-chip">🔬 Check Optical dBm</a>
                    <a href="#motion-tools" class="quick-chip">🎛️ MikroTik CCR2216</a>
                </div>
            </div>

            <!-- 3. FLOATING HARDWARE PREVIEW CARD -->
            <div class="float-preview-card">
                <div class="preview-card-header">
                    <div class="preview-card-title">Fiber &amp; Router Core</div>
                    <div class="preview-card-badge">
                        <span class="port-led active-green" style="display:inline-block;"></span>
                        LIVE
                    </div>
                </div>
                <div class="preview-mini-screen">
                    <div>PORT sfp-sfpplus1: <span style="color:#10b981;">10G LINK UP</span></div>
                    <div>OPTICAL POWER: <span style="color:#00f0ff;">-18.4 dBm</span></div>
                    <div>TRAFFIC: <span style="color:#f59e0b;">9.42 Gbps / 1.1M pps</span></div>
                    <div>AUTONOMOUS LATENCY: <span style="color:#10b981;">0.4 ms</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- LIVE TELEMETRY TICKER BAR -->
<div class="ticker-strip">
    <div class="ticker-track">
        <div class="ticker-item"><span class="ticker-val">100 Gbps</span><span class="ticker-desc">Backbone Ready</span></div>
        <div class="ticker-item"><span class="ticker-val">0.3 ms</span><span class="ticker-desc">Autonomous Routing</span></div>
        <div class="ticker-item"><span class="ticker-val">50,000+</span><span class="ticker-desc">Active Subscribers</span></div>
        <div class="ticker-item"><span class="ticker-val">MikroTik v7</span><span class="ticker-desc">Full REST/API Engine</span></div>
        <div class="ticker-item"><span class="ticker-val">M-Pesa STK</span><span class="ticker-desc">Instant Daraja Hook</span></div>
        <div class="ticker-item"><span class="ticker-val">OTDR GIS</span><span class="ticker-desc">Sub-Meter Fiber Trace</span></div>
        <div class="ticker-item"><span class="ticker-val">RADIUS AAA</span><span class="ticker-desc">Sub-Millisecond CoA</span></div>
        <div class="ticker-item"><span class="ticker-val">99.999%</span><span class="ticker-desc">Carrier Grade SLA</span></div>
        <!-- Duplicated for infinite scroll -->
        <div class="ticker-item"><span class="ticker-val">100 Gbps</span><span class="ticker-desc">Backbone Ready</span></div>
        <div class="ticker-item"><span class="ticker-val">0.3 ms</span><span class="ticker-desc">Autonomous Routing</span></div>
        <div class="ticker-item"><span class="ticker-val">50,000+</span><span class="ticker-desc">Active Subscribers</span></div>
        <div class="ticker-item"><span class="ticker-val">MikroTik v7</span><span class="ticker-desc">Full REST/API Engine</span></div>
        <div class="ticker-item"><span class="ticker-val">M-Pesa STK</span><span class="ticker-desc">Instant Daraja Hook</span></div>
        <div class="ticker-item"><span class="ticker-val">OTDR GIS</span><span class="ticker-desc">Sub-Meter Fiber Trace</span></div>
        <div class="ticker-item"><span class="ticker-val">RADIUS AAA</span><span class="ticker-desc">Sub-Millisecond CoA</span></div>
        <div class="ticker-item"><span class="ticker-val">99.999%</span><span class="ticker-desc">Carrier Grade SLA</span></div>
    </div>
</div>

<!-- ===================================================
     MOTION NETWORKING TOOLSET: FIBER & ROUTER MODULES
     =================================================== -->
<section id="motion-tools">
    <div class="section-tag">Interactive Hardware Virtualizer</div>
    <h2 class="section-title">Motion Networking Tools: Fiber &amp; Router Core</h2>
    <p class="section-desc">Experience real carrier-grade hardware telemetry. Simulate optical laser pulses, inspect core router port states, and test live throughput with zero physical equipment.</p>

    <div class="motion-tools-grid">
        <!-- 1. HIGH SPEED FIBER OPTIC & OTDR ENGINE -->
        <div class="tool-panel-card">
            <div class="panel-top-bar">
                <div class="panel-label">
                    <svg width="24" height="24" fill="none" stroke="#00f0ff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Optical Fiber &amp; OTDR Laser Analyzer
                </div>
                <div class="panel-status-pill">
                    <span class="live-pulse-dot" style="background:#00f0ff; box-shadow:0 0 8px #00f0ff;"></span>
                    LASER ACTIVE
                </div>
            </div>

            <!-- Live Laser Photon Canvas -->
            <div class="fiber-visualizer-box">
                <canvas id="fiber-light-canvas"></canvas>
            </div>

            <div class="fiber-controls">
                <button class="wave-btn active" onclick="setWavelength(1310, this)">1310 nm (GPON Up)</button>
                <button class="wave-btn" onclick="setWavelength(1490, this)">1490 nm (GPON Down)</button>
                <button class="wave-btn" onclick="setWavelength(1550, this)">1550 nm (XGS 10G)</button>
            </div>

            <div class="fiber-telemetry-row">
                <div class="tele-stat">
                    <span class="tele-title">Optical Power (Rx)</span>
                    <span class="tele-val" id="fiber-dbm-val">-18.42 dBm</span>
                </div>
                <div class="tele-stat">
                    <span class="tele-title">Fiber Attenuation</span>
                    <span class="tele-val" style="color:#00f0ff;">0.32 dB/km</span>
                </div>
                <div class="tele-stat">
                    <span class="tele-title">Link Distance</span>
                    <span class="tele-val" style="color:#f8fafc;">14.8 km to OLT</span>
                </div>
                <div class="tele-stat">
                    <span class="tele-title">Fiber Core Mode</span>
                    <span class="tele-val" style="color:#ec4899;">G.652.D Single-Mode</span>
                </div>
            </div>

            <button class="btn-test-fiber" onclick="burstLaserPulse()">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Inject Supercharged Photon Burst &amp; Test OTDR
            </button>
        </div>

        <!-- 2. MIKROTIK CCR2216 / CORE ROUTER FACEPLATE -->
        <div class="tool-panel-card">
            <div class="panel-top-bar">
                <div class="panel-label">
                    <svg width="24" height="24" fill="none" stroke="#8b5cf6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    MikroTik CCR2216 Core Edge Hardware
                </div>
                <div class="panel-status-pill" style="color:#10b981; border-color:#10b981; background:rgba(16,185,129,0.1);">
                    <span class="live-pulse-dot" style="background:#10b981;"></span>
                    SYNCED v7.16
                </div>
            </div>

            <!-- Physical Chassis Faceplate -->
            <div class="router-chassis">
                <div class="chassis-rack-ears left"></div>
                <div class="chassis-rack-ears right"></div>

                <div class="router-faceplate-top">
                    <div class="brand-badge-mikrotik">
                        <span style="color:#00f0ff;">MikroTik</span> RouterOS Cloud Core
                    </div>
                    <div class="router-oled-display" id="router-oled-text">
                        CCR2216-16G-2S+ [ONLINE]
                    </div>
                </div>

                <!-- Clickable Interactive Port Matrix -->
                <div class="ports-matrix" id="ports-rack">
                    <div class="port-socket" onclick="togglePort(this, 'sfp28-1 (100G Backbone)', '94.2 Gbps', 'UP')">
                        <div class="port-led active-green"></div>
                        <div class="port-hole" style="border-color:#00f0ff;"></div>
                        <div class="port-name">SFP1</div>
                    </div>
                    <div class="port-socket" onclick="togglePort(this, 'sfp28-2 (100G Redundant)', '88.5 Gbps', 'UP')">
                        <div class="port-led active-green"></div>
                        <div class="port-hole" style="border-color:#00f0ff;"></div>
                        <div class="port-name">SFP2</div>
                    </div>
                    <div class="port-socket" onclick="togglePort(this, 'sfp-10G (OLT Trunk 1)', '9.8 Gbps', 'UP')">
                        <div class="port-led active-green"></div>
                        <div class="port-hole"></div>
                        <div class="port-name">10G-1</div>
                    </div>
                    <div class="port-socket" onclick="togglePort(this, 'sfp-10G (OLT Trunk 2)', '8.4 Gbps', 'UP')">
                        <div class="port-led active-green"></div>
                        <div class="port-hole"></div>
                        <div class="port-name">10G-2</div>
                    </div>
                    <div class="port-socket" onclick="togglePort(this, 'eth1 (Daraja API Gateway)', '1.0 Gbps', 'UP')">
                        <div class="port-led active-amber"></div>
                        <div class="port-hole"></div>
                        <div class="port-name">ETH1</div>
                    </div>
                    <div class="port-socket" onclick="togglePort(this, 'eth2 (RADIUS Auth Core)', '1.0 Gbps', 'UP')">
                        <div class="port-led active-green"></div>
                        <div class="port-hole"></div>
                        <div class="port-name">ETH2</div>
                    </div>
                    <div class="port-socket" onclick="togglePort(this, 'eth3 (Management OOB)', '100 Mbps', 'UP')">
                        <div class="port-led active-green"></div>
                        <div class="port-hole"></div>
                        <div class="port-name">ETH3</div>
                    </div>
                </div>
            </div>

            <!-- Real-time Router Telemetry Readings -->
            <div class="router-telemetry-stats">
                <div class="mini-stat-card">
                    <div class="mini-stat-val" id="cpu-stat">7%</div>
                    <div class="mini-stat-lbl">CPU Load (16-Core)</div>
                </div>
                <div class="mini-stat-card">
                    <div class="mini-stat-val" id="temp-stat">38°C</div>
                    <div class="mini-stat-lbl">Core Temp</div>
                </div>
                <div class="mini-stat-card">
                    <div class="mini-stat-val" id="pppoe-stat">2,480</div>
                    <div class="mini-stat-lbl">PPPoE Tunnels</div>
                </div>
                <div class="mini-stat-card">
                    <div class="mini-stat-val" id="rate-stat">12.8 Gbps</div>
                    <div class="mini-stat-lbl">Total Throughput</div>
                </div>
            </div>

            <button class="btn-test-fiber" style="border-color:rgba(139,92,246,0.4); color:#c084fc; background:rgba(139,92,246,0.15);" onclick="simulatePacketSurge()">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Trigger 10,000 Subscriber Concurrent Session Surge
            </button>
        </div>
    </div>
</section>

<!-- ===================================================
     URGE OF ONBOARDING: 60-SECOND ZERO-TOUCH SIMULATOR
     =================================================== -->
<section id="onboarding-engine" style="padding-top:2rem;">
    <div class="onboarding-epic-wrapper">
        <div class="onboard-grid">
            <div>
                <div class="hero-update-pill" style="background:rgba(16,185,129,0.15); border-color:#10b981; color:#10b981;">
                    <span class="live-pulse-dot" style="background:#10b981;"></span>
                    FEEL THE SPEED OF NEXT-GEN ONBOARDING
                </div>
                <h3 class="onboard-headline">
                    Onboard Your Entire ISP Infrastructure in <span class="text-cyan-gradient">60 Seconds Flat</span>
                </h3>
                <p class="onboard-sub">
                    No painful manual IP routing, no broken scripts, no delayed subscriber activations. Plug in your MikroTik router, run our zero-touch discovery agent, and watch your fiber network light up instantly.
                </p>

                <div class="onboard-step-pill">
                    <div class="step-num-badge">1</div>
                    <div class="step-text">
                        <h4>Plug &amp; Auto-Discover Router Hardware</h4>
                        <p>Smartoh scans RouterOS v7 API and queries all interface ports, queues, and OLT GPON links in under 5 seconds.</p>
                    </div>
                </div>

                <div class="onboard-step-pill">
                    <div class="step-num-badge">2</div>
                    <div class="step-text">
                        <h4>Automated RADIUS AAA &amp; PPPoE Provisioning</h4>
                        <p>Dynamic subscriber pool generation with real-time CoA disconnect tokens and speed tier profile enforcement.</p>
                    </div>
                </div>

                <div class="onboard-step-pill">
                    <div class="step-num-badge">3</div>
                    <div class="step-text">
                        <h4>M-Pesa STK Push Billing Activation</h4>
                        <p>Immediate synchronization with Daraja API for automated invoice settlement, voucher printing, and customer SMS.</p>
                    </div>
                </div>

                <div style="margin-top:2.5rem; display:flex; gap:1rem; flex-wrap:wrap;">
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-hero-urgent">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Start Your Free Onboarding Now
                    </a>
                    @endif
                    <a href="{{ route('login') }}" class="btn-ghost" style="padding:0.9rem 1.6rem;">
                        Sign In Existing ISP
                    </a>
                </div>
            </div>

            <!-- INTERACTIVE SIMULATOR TERMINAL -->
            <div>
                <div class="onboard-terminal-box">
                    <div class="terminal-header-bar">
                        <div class="terminal-dots">
                            <div class="t-dot t-red"></div>
                            <div class="t-dot t-yellow"></div>
                            <div class="t-dot t-green"></div>
                        </div>
                        <div class="terminal-title">smartoh-onboard-agent ~ v4.2</div>
                        <div style="font-size:0.65rem; color:#10b981;">● TLS 1.3 SECURE</div>
                    </div>

                    <div class="terminal-content" id="terminal-screen">
                        <div class="terminal-line"><span class="t-green-text">[BOOT]</span> Smartoh Autonomous Onboarding Engine Ready.</div>
                        <div class="terminal-line"><span class="t-cyan-text">[READY]</span> Target: MikroTik CCR2216-16G-2S+ (10.0.0.1:8728)</div>
                        <div class="terminal-line"><span class="t-magenta-text">[PRESS]</span> Click "Execute Zero-Touch Onboard" below to run simulated live onboarding.</div>
                    </div>

                    <div class="terminal-trigger-bar">
                        <button class="btn-run-onboard-sim" id="run-sim-btn" onclick="executeOnboardingSim()">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                            Execute Zero-Touch Onboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- LIVE NETWORK TOPOLOGY VISUALIZATION -->
<section id="netdemo">
    <div class="section-tag">Real-Time Core Topology</div>
    <h2 class="section-title">Your Network, At a Glance</h2>
    <p class="section-desc">Watch subscriber connections, optical fiber trunks, core MikroTik routers, and instant M-Pesa billing transactions pulsing across your autonomous network.</p>

    <div class="topology-canvas-wrapper">
        <canvas id="tc"></canvas>
    </div>
</section>

<!-- COMPREHENSIVE ISP PLATFORM ARCHITECTURE (FEATURES) -->
<section id="features">
    <div class="section-tag">Carrier-Grade Capabilities</div>
    <h2 class="section-title">Engineered For Modern Network Operators</h2>
    <p class="section-desc">Everything required to build, scale, and automate an unstoppable ISP business with zero manual friction.</p>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feat-icon-box" style="background:rgba(37,99,235,0.15);">
                <svg fill="none" stroke="#3b82f6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
            </div>
            <h3 class="feat-title">MikroTik RouterOS v7 Core</h3>
            <p class="feat-text">Native REST and API automation. Instant PPPoE profile generation, simple queue shaping, reboot triggers, and real-time subscriber sync.</p>
            <div class="tag-list">
                <span class="tech-pill">RouterOS v7</span>
                <span class="tech-pill">PPPoE Server</span>
                <span class="tech-pill">HotSpot</span>
                <span class="tech-pill">Simple Queues</span>
            </div>
        </div>

        <div class="feature-card">
            <div class="feat-icon-box" style="background:rgba(0,240,255,0.15);">
                <svg fill="none" stroke="#00f0ff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="feat-title">Daraja M-Pesa &amp; Smart Billing</h3>
            <p class="feat-text">Automate customer invoice generation, Daraja C2B/B2C callbacks, instant STK Push, voucher PIN generation, and auto-unblock upon payment.</p>
            <div class="tag-list">
                <span class="tech-pill">Daraja API</span>
                <span class="tech-pill">STK Push</span>
                <span class="tech-pill">Auto Re-provision</span>
                <span class="tech-pill">e-Vouchers</span>
            </div>
        </div>

        <div class="feature-card">
            <div class="feat-icon-box" style="background:rgba(139,92,246,0.15);">
                <svg fill="none" stroke="#8b5cf6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3 class="feat-title">High-Velocity RADIUS AAA</h3>
            <p class="feat-text">Built-in FreeRADIUS server cluster with sub-millisecond CoA packet disconnects, accounting telemetry, and centralized subscriber authentication.</p>
            <div class="tag-list">
                <span class="tech-pill">FreeRADIUS</span>
                <span class="tech-pill">RFC 3576 CoA</span>
                <span class="tech-pill">Accounting</span>
                <span class="tech-pill">Failover</span>
            </div>
        </div>

        <div class="feature-card">
            <div class="feat-icon-box" style="background:rgba(16,185,129,0.15);">
                <svg fill="none" stroke="#10b981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h3 class="feat-title">GIS Fiber Route Mapping</h3>
            <p class="feat-text">Visualize physical fiber routes, optical splitters, splice enclosures, and field technician dispatch jobs on live vector maps with zero guesswork.</p>
            <div class="tag-list">
                <span class="tech-pill">Leaflet GIS</span>
                <span class="tech-pill">Optical Splitters</span>
                <span class="tech-pill">Fiber Traces</span>
                <span class="tech-pill">Field Ops</span>
            </div>
        </div>

        <div class="feature-card">
            <div class="feat-icon-box" style="background:rgba(245,158,11,0.15);">
                <svg fill="none" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <h3 class="feat-title">NOC AI Copilot &amp; Rogue Detection</h3>
            <p class="feat-text">Predictive maintenance artificial intelligence, automated fiber optical degradation alerts, rogue router quarantine, and automated incident escalation.</p>
            <div class="tag-list">
                <span class="tech-pill">NOC AI</span>
                <span class="tech-pill">Rogue Detection</span>
                <span class="tech-pill">Optical Loss Alert</span>
                <span class="tech-pill">Automated Ticketing</span>
            </div>
        </div>

        <div class="feature-card">
            <div class="feat-icon-box" style="background:rgba(236,72,153,0.15);">
                <svg fill="none" stroke="#ec4899" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h3 class="feat-title">Multi-Tenant White-Label</h3>
            <p class="feat-text">Run multiple ISP franchise brands on a single high-availability cloud cluster with isolated client databases, custom tenant domains, and unified super-admin.</p>
            <div class="tag-list">
                <span class="tech-pill">White-Label</span>
                <span class="tech-pill">Custom Subdomains</span>
                <span class="tech-pill">Multi-ISP</span>
                <span class="tech-pill">Tenant Isolation</span>
            </div>
        </div>
    </div>
</section>

<!-- TRANSPARENT PRICING -->
<section id="pricing" style="padding-bottom:5rem;">
    <div class="section-tag">Predictable Plans</div>
    <h2 class="section-title">Transparent Pricing For Every ISP</h2>
    <p class="section-desc">Start free, validate your network with real subscribers, and scale up seamlessly as your bandwidth grows.</p>

    <div class="pricing-grid">
        <div class="price-card">
            <div class="price-tier-tag">Starter</div>
            <div class="price-number">Free<span>/mo</span></div>
            <p style="font-size:0.85rem; color:#94a3b8; margin:0.8rem 0 1.5rem;">Perfect for startup WISPs &amp; testing</p>
            <ul class="price-features-list">
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Up to 50 Subscribers</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>1 MikroTik Core Router</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Automated Invoicing</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>M-Pesa STK Push Gateway</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Standard Community Support</li>
            </ul>
            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="btn-ghost" style="display:block; text-align:center; padding:0.8rem;">
                Get Started Free
            </a>
            @endif
        </div>

        <div class="price-card featured">
            <div class="price-tier-tag" style="color:#00f0ff;">⚡ Most Popular</div>
            <div class="price-number" style="color:#00f0ff;">KES 4,999<span>/mo</span></div>
            <p style="font-size:0.85rem; color:#94a3b8; margin:0.8rem 0 1.5rem;">For growing commercial ISPs</p>
            <ul class="price-features-list">
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Up to 1,000 Subscribers</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Unlimited MikroTik Routers</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>High-Speed RADIUS AAA + CoA</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>GIS Fiber Splice &amp; Route Mapping</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Automated WhatsApp &amp; SMS Dunning</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>24/7 Priority SLA Support</li>
            </ul>
            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="btn-primary-vibrant" style="display:block; text-align:center; padding:0.85rem;">
                Launch 30-Day Free Trial
            </a>
            @endif
        </div>

        <div class="price-card">
            <div class="price-tier-tag">Carrier Tier</div>
            <div class="price-number">Custom</div>
            <p style="font-size:0.85rem; color:#94a3b8; margin:0.8rem 0 1.5rem;">For large-scale Tier-2 &amp; Tier-3 Telcos</p>
            <ul class="price-features-list">
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Unlimited Subscribers &amp; Routers</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Multi-Tenant White-Label Setup</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>BGP / OSPF Automated Routing Hub</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Custom On-Premises or Private Cloud</li>
                <li><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Dedicated Network Engineer SLA</li>
            </ul>
            <a href="mailto:info@smartoh.net" class="btn-ghost" style="display:block; text-align:center; padding:0.8rem;">
                Contact Carrier Sales
            </a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <div>
            <div class="footer-brand-title">Smartoh Technologies</div>
            <p class="footer-brand-desc">
                The next-generation autonomous ISP network infrastructure &amp; billing operating system. Automate your core routers, fiber cables, subscriber accounting, and payment collections.
            </p>
        </div>
        <div class="footer-col">
            <h4>Platform</h4>
            <ul>
                <li><a href="#motion-tools">Fiber &amp; Router Tools</a></li>
                <li><a href="#onboarding-engine">Urgent Onboarding</a></li>
                <li><a href="#features">Architecture</a></li>
                <li><a href="#pricing">Pricing Plans</a></li>
                <li><a href="{{ route('login') }}">Sign In</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Integrations</h4>
            <ul>
                <li><a href="#">MikroTik RouterOS v7</a></li>
                <li><a href="#">Safaricom Daraja API</a></li>
                <li><a href="#">Huawei &amp; ZTE GPON</a></li>
                <li><a href="#">FreeRADIUS CoA</a></li>
                <li><a href="#">TR-069 CWMP &amp; USP</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Company</h4>
            <ul>
                <li><a href="#">About Smartoh</a></li>
                <li><a href="#">Network NOC Status</a></li>
                <li><a href="#">Security &amp; Compliance</a></li>
                <li><a href="mailto:info@smartoh.net">Technical Support</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} Smartoh Technologies. All rights reserved. Built for high-speed ISPs and network engineers.</p>
        <div class="status-pill-live">
            <span class="status-dot-pulse"></span>
            ALL AUTONOMOUS NETWORK CORES OPERATIONAL (99.999% SLA)
        </div>
    </div>
</footer>

<!-- INTERACTIVE SCRIPTS: 3D FIBER WAVES, MOTION ROUTER, TERMINAL SIMULATOR -->
<script>
// ====================================================
// 1. GENERATIVE 3D FIBER WAVES & PHOTON STREAM CANVAS
// ====================================================
(function initFiberCanvas() {
    const cv = document.getElementById('fiber-canvas');
    if (!cv) return;
    const ctx = cv.getContext('2d');
    let W, H;
    let particles = [];
    let mouse = { x: -1000, y: -1000, active: false };

    function resize() {
        W = cv.width = window.innerWidth;
        H = cv.height = window.innerHeight;
    }
    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', e => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
        mouse.active = true;
    });

    resize();

    // Create glowing photon particles
    for (let i = 0; i < 90; i++) {
        particles.push({
            x: Math.random() * W,
            y: Math.random() * H,
            vx: (Math.random() - 0.5) * 0.8,
            vy: (Math.random() - 0.5) * 0.8,
            r: Math.random() * 2 + 1,
            color: Math.random() > 0.4 ? '#00f0ff' : (Math.random() > 0.5 ? '#8b5cf6' : '#ec4899'),
            phase: Math.random() * Math.PI * 2
        });
    }

    let time = 0;
    function render() {
        ctx.clearRect(0, 0, W, H);

        // Draw organic fluid fiber optic sine ribbons
        const ribbonCount = 5;
        for (let r = 0; r < ribbonCount; r++) {
            ctx.beginPath();
            const yOffset = H * 0.35 + r * 60;
            const waveLength = 0.0018 + r * 0.0003;
            const speed = time * (0.015 + r * 0.004);

            for (let x = 0; x <= W; x += 15) {
                const y = yOffset + Math.sin(x * waveLength + speed) * 65 + Math.cos(x * 0.001 + speed * 0.7) * 35;
                if (x === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            }

            const alpha = 0.15 - r * 0.02;
            ctx.strokeStyle = r % 2 === 0 ? `rgba(0, 240, 255, ${alpha})` : `rgba(236, 72, 153, ${alpha})`;
            ctx.lineWidth = 1.5 + r * 0.5;
            ctx.shadowBlur = 15;
            ctx.shadowColor = r % 2 === 0 ? '#00f0ff' : '#ec4899';
            ctx.stroke();
            ctx.shadowBlur = 0;
        }

        // Draw traveling photons and connecting fiber meshes
        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];
            p.phase += 0.03;
            const glow = 0.4 + Math.sin(p.phase) * 0.6;

            p.x += p.vx;
            p.y += p.vy;

            if (p.x < 0) p.x = W;
            if (p.x > W) p.x = 0;
            if (p.y < 0) p.y = H;
            if (p.y > H) p.y = 0;

            // Mouse interaction: cursor creates optical deflection
            if (mouse.active) {
                const dx = mouse.x - p.x;
                const dy = mouse.y - p.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 180) {
                    p.x -= (dx / dist) * 2;
                    p.y -= (dy / dist) * 2;
                }
            }

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = p.color;
            ctx.globalAlpha = glow;
            ctx.fill();
            ctx.globalAlpha = 1;

            // Connect nearby fiber nodes
            for (let j = i + 1; j < particles.length; j++) {
                const p2 = particles[j];
                const dx = p.x - p2.x;
                const dy = p.y - p2.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 110) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(p2.x, p2.y);
                    ctx.strokeStyle = `rgba(0, 240, 255, ${(1 - dist / 110) * 0.15})`;
                    ctx.lineWidth = 0.75;
                    ctx.stroke();
                }
            }
        }

        time++;
        requestAnimationFrame(render);
    }
    render();
})();

// ====================================================
// 2. DYNAMIC HEADLINE CYCLING TYPING EFFECT
// ====================================================
(function initHeadlineTypewriter() {
    const el = document.getElementById('typewriter-text');
    if (!el) return;
    const words = [
        "Autonomous Core",
        "100G Fiber Grid",
        "MikroTik v7 Engine",
        "Sub-1ms Latency",
        "0-Touch Billing"
    ];
    let wordIdx = 0;
    let charIdx = words[0].length;
    let isDeleting = false;
    let pause = 2200;

    function tick() {
        const currentWord = words[wordIdx];
        if (isDeleting) {
            charIdx--;
            el.textContent = currentWord.substring(0, charIdx);
        } else {
            charIdx++;
            el.textContent = currentWord.substring(0, charIdx);
        }

        let speed = isDeleting ? 40 : 80;

        if (!isDeleting && charIdx === currentWord.length) {
            speed = pause;
            isDeleting = true;
        } else if (isDeleting && charIdx === 0) {
            isDeleting = false;
            wordIdx = (wordIdx + 1) % words.length;
            speed = 300;
        }

        setTimeout(tick, speed);
    }
    setTimeout(tick, pause);
})();

// ====================================================
// 3. MOTION FIBER TOOL: LASER & OTDR PULSE CANVAS
// ====================================================
(function initFiberLightCanvas() {
    const cv = document.getElementById('fiber-light-canvas');
    if (!cv) return;
    const ctx = cv.getContext('2d');
    let W, H;
    let laserPulses = [];

    function resize() {
        W = cv.width = cv.clientWidth || 450;
        H = cv.height = 140;
    }
    resize();
    window.addEventListener('resize', resize);

    // Initial pulses
    for (let i = 0; i < 6; i++) {
        laserPulses.push({ x: (i / 6) * W, speed: 4 + Math.random() * 2, size: 24, color: '#00f0ff' });
    }

    window.burstLaserPulse = function() {
        for (let i = 0; i < 5; i++) {
            laserPulses.push({ x: 0, speed: 7 + i * 1.5, size: 36, color: '#ff5722' });
        }
        const dbmEl = document.getElementById('fiber-dbm-val');
        if (dbmEl) {
            dbmEl.textContent = '-16.10 dBm (PEAK)';
            dbmEl.style.color = '#ff5722';
            setTimeout(() => {
                dbmEl.textContent = '-18.42 dBm';
                dbmEl.style.color = '#10b981';
            }, 1800);
        }
    };

    window.setWavelength = function(nm, btn) {
        document.querySelectorAll('.wave-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        const dbmEl = document.getElementById('fiber-dbm-val');
        if (nm === 1310) {
            if (dbmEl) dbmEl.textContent = '-18.42 dBm';
        } else if (nm === 1490) {
            if (dbmEl) dbmEl.textContent = '-17.85 dBm';
        } else {
            if (dbmEl) dbmEl.textContent = '-15.20 dBm (10G XGS)';
        }
    };

    function draw() {
        ctx.clearRect(0, 0, W, H);

        // Cladding & Glass Core background
        const midY = H / 2;

        // Outer buffer cable
        ctx.fillStyle = '#090d16';
        ctx.fillRect(0, midY - 32, W, 64);
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, midY - 32, W, 64);

        // Silica Glass Core 9/125µm
        ctx.fillStyle = '#020617';
        ctx.fillRect(0, midY - 10, W, 20);

        // Core central optical guideline
        ctx.beginPath();
        ctx.moveTo(0, midY);
        ctx.lineTo(W, midY);
        ctx.strokeStyle = 'rgba(0, 240, 255, 0.25)';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Animate propagating laser pulses
        for (let i = 0; i < laserPulses.length; i++) {
            const p = laserPulses[i];
            p.x += p.speed;
            if (p.x > W + 50) p.x = -20;

            const grad = ctx.createLinearGradient(p.x - p.size, midY, p.x + 10, midY);
            grad.addColorStop(0, 'transparent');
            grad.addColorStop(0.7, p.color);
            grad.addColorStop(1, '#ffffff');

            ctx.beginPath();
            ctx.moveTo(p.x - p.size, midY);
            ctx.lineTo(p.x, midY);
            ctx.strokeStyle = grad;
            ctx.lineWidth = 8;
            ctx.shadowBlur = 18;
            ctx.shadowColor = p.color;
            ctx.stroke();
            ctx.shadowBlur = 0;

            // Photon spark
            ctx.beginPath();
            ctx.arc(p.x, midY, 3, 0, Math.PI * 2);
            ctx.fillStyle = '#ffffff';
            ctx.fill();
        }

        // Live grid tick markers
        ctx.fillStyle = 'rgba(148, 163, 184, 0.4)';
        ctx.font = '9px JetBrains Mono';
        for (let x = 0; x < W; x += 80) {
            ctx.fillText(`${(x / 30).toFixed(1)} km`, x + 5, midY + 28);
        }

        requestAnimationFrame(draw);
    }
    draw();
})();

// ====================================================
// 4. MOTION ROUTER INTERACTIVITY & TELEMETRY
// ====================================================
window.togglePort = function(el, portName, speed, status) {
    const oled = document.getElementById('router-oled-text');
    const led = el.querySelector('.port-led');

    if (led.classList.contains('active-green')) {
        led.classList.remove('active-green');
        led.classList.add('active-amber');
        if (oled) oled.textContent = `${portName}: INSPECTING`;
    } else {
        led.classList.remove('active-amber');
        led.classList.add('active-green');
        if (oled) oled.textContent = `${portName}: ${speed} [${status}]`;
    }
};

window.simulatePacketSurge = function() {
    const cpuEl = document.getElementById('cpu-stat');
    const pppoeEl = document.getElementById('pppoe-stat');
    const rateEl = document.getElementById('rate-stat');
    const oled = document.getElementById('router-oled-text');

    if (cpuEl) cpuEl.textContent = '48%';
    if (pppoeEl) pppoeEl.textContent = '12,480';
    if (rateEl) rateEl.textContent = '84.6 Gbps';
    if (oled) oled.textContent = 'SURGE: 10K SESSIONS LOADED';

    setTimeout(() => {
        if (cpuEl) cpuEl.textContent = '12%';
        if (pppoeEl) pppoeEl.textContent = '3,120';
        if (rateEl) rateEl.textContent = '14.2 Gbps';
        if (oled) oled.textContent = 'CCR2216-16G-2S+ [ONLINE]';
    }, 3000);
};

// ====================================================
// 5. INTERACTIVE 60-SECOND ONBOARDING TERMINAL SIMULATOR
// ====================================================
let isSimRunning = false;
window.executeOnboardingSim = function() {
    if (isSimRunning) return;
    isSimRunning = true;
    const term = document.getElementById('terminal-screen');
    const btn = document.getElementById('run-sim-btn');
    if (!term) return;

    btn.disabled = true;
    btn.textContent = 'Provisioning in Progress...';
    btn.style.opacity = '0.6';

    const logs = [
        { text: "[1/6] Scanning RouterOS API TLS on port 8728...", color: "t-cyan-text", delay: 400 },
        { text: "[2/6] Handshake OK: MikroTik CCR2216 (Serial: 9FA80C14).", color: "t-green-text", delay: 900 },
        { text: "[3/6] Discovering 16x SFP+ interfaces & GPON PON trees... [8 PON active]", color: "t-cyan-text", delay: 1500 },
        { text: "[4/6] Creating PPPoE Server Concentrator & High-Speed Queues...", color: "t-magenta-text", delay: 2100 },
        { text: "[5/6] Binding FreeRADIUS AAA with CoA Real-Time Disconnect...", color: "t-green-text", delay: 2700 },
        { text: "[6/6] Syncing Safaricom Daraja M-Pesa STK Callback Webhook...", color: "t-green-text", delay: 3300 },
        { text: "✔ ONBOARDING COMPLETE: 2,400 Subscribers Ready in 14.8 seconds!", color: "t-green-text", delay: 4000 }
    ];

    term.innerHTML = '<div class="terminal-line"><span class="t-green-text">[START]</span> Initiating Zero-Touch Hardware Discovery...</div>';

    logs.forEach(item => {
        setTimeout(() => {
            const line = document.createElement('div');
            line.className = 'terminal-line';
            line.innerHTML = `<span class="${item.color}">${item.text}</span>`;
            term.appendChild(line);
            term.scrollTop = term.scrollHeight;
        }, item.delay);
    });

    setTimeout(() => {
        isSimRunning = false;
        btn.disabled = false;
        btn.textContent = 'Re-Run Zero-Touch Onboard';
        btn.style.opacity = '1';
    }, 4500);
};

// ====================================================
// 6. NETWORK TOPOLOGY LIVE CANVAS WITH M-PESA PACKETS
// ====================================================
(function initTopology() {
    const cv = document.getElementById('tc');
    if (!cv) return;
    const ctx = cv.getContext('2d');

    const N = [
        { id: 'core', lbl: 'CCR2216 Core', x: 0.5, y: 0.22, col: '#2563eb', r: 28, ico: '⚡' },
        { id: 'olt1', lbl: 'Huawei OLT A', x: 0.22, y: 0.48, col: '#00f0ff', r: 20, ico: '⊕' },
        { id: 'olt2', lbl: 'ZTE OLT B', x: 0.5, y: 0.48, col: '#00f0ff', r: 20, ico: '⊕' },
        { id: 'olt3', lbl: 'Fiber Trunk C', x: 0.78, y: 0.48, col: '#00f0ff', r: 20, ico: '⊕' },
        { id: 'cpe1', lbl: 'GPON ONT 1', x: 0.1, y: 0.8, col: '#10b981', r: 12 },
        { id: 'cpe2', lbl: 'GPON ONT 2', x: 0.22, y: 0.84, col: '#10b981', r: 12 },
        { id: 'cpe3', lbl: 'GPON ONT 3', x: 0.34, y: 0.8, col: '#10b981', r: 12 },
        { id: 'cpe4', lbl: 'GPON ONT 4', x: 0.44, y: 0.84, col: '#10b981', r: 12 },
        { id: 'cpe5', lbl: 'GPON ONT 5', x: 0.56, y: 0.84, col: '#10b981', r: 12 },
        { id: 'cpe6', lbl: 'GPON ONT 6', x: 0.68, y: 0.8, col: '#10b981', r: 12 },
        { id: 'cpe7', lbl: 'GPON ONT 7', x: 0.8, y: 0.84, col: '#10b981', r: 12 },
        { id: 'cpe8', lbl: 'GPON ONT 8', x: 0.92, y: 0.8, col: '#10b981', r: 12 },
        { id: 'rad', lbl: 'RADIUS AAA', x: 0.14, y: 0.22, col: '#8b5cf6', r: 16, ico: '🔒' },
        { id: 'mpesa', lbl: 'M-Pesa STK', x: 0.86, y: 0.22, col: '#10b981', r: 16, ico: '💳' }
    ];

    const L = [
        ['core', 'olt1'], ['core', 'olt2'], ['core', 'olt3'],
        ['olt1', 'cpe1'], ['olt1', 'cpe2'], ['olt1', 'cpe3'],
        ['olt2', 'cpe4'], ['olt2', 'cpe5'],
        ['olt3', 'cpe6'], ['olt3', 'cpe7'], ['olt3', 'cpe8'],
        ['rad', 'core'], ['mpesa', 'core']
    ];

    let packets = [];
    let tick = 0;
    const gn = id => N.find(n => n.id === id);

    function spawnPacket() {
        const link = L[Math.floor(Math.random() * L.length)];
        const forward = Math.random() > 0.4;
        packets.push({
            from: forward ? link[0] : link[1],
            to: forward ? link[1] : link[0],
            p: 0,
            spd: 0.012 + Math.random() * 0.014,
            col: Math.random() > 0.5 ? '#00f0ff' : (Math.random() > 0.5 ? '#10b981' : '#ec4899')
        });
    }

    function resize() {
        cv.width = cv.clientWidth || 900;
        cv.height = 380;
    }

    function render() {
        const W = cv.width, H = cv.height;
        ctx.clearRect(0, 0, W, H);

        // Dark grid background
        ctx.fillStyle = 'rgba(3, 7, 18, 0.96)';
        ctx.fillRect(0, 0, W, H);

        // Draw Fiber Links
        for (const [a, b] of L) {
            const na = gn(a), nb = gn(b);
            ctx.beginPath();
            ctx.moveTo(na.x * W, na.y * H);
            ctx.lineTo(nb.x * W, nb.y * H);
            ctx.strokeStyle = 'rgba(0, 240, 255, 0.22)';
            ctx.lineWidth = 1.8;
            ctx.stroke();
        }

        // Draw Nodes
        for (const n of N) {
            const nx = n.x * W, ny = n.y * H;
            const pulse = 0.7 + Math.sin(tick * 0.04 + n.x * 10) * 0.3;

            // Halo glow
            const gr = ctx.createRadialGradient(nx, ny, 0, nx, ny, n.r * 2.2);
            gr.addColorStop(0, n.col + '44');
            gr.addColorStop(1, 'transparent');
            ctx.beginPath();
            ctx.arc(nx, ny, n.r * 2.2, 0, Math.PI * 2);
            ctx.fillStyle = gr;
            ctx.fill();

            // Core circle
            ctx.beginPath();
            ctx.arc(nx, ny, n.r, 0, Math.PI * 2);
            ctx.fillStyle = n.col;
            ctx.globalAlpha = pulse;
            ctx.fill();
            ctx.globalAlpha = 1;
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.35)';
            ctx.lineWidth = 1.5;
            ctx.stroke();

            if (n.ico) {
                ctx.fillStyle = '#ffffff';
                ctx.font = `${n.r * 0.75}px sans-serif`;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(n.ico, nx, ny);
            }

            ctx.fillStyle = 'rgba(248, 250, 252, 0.8)';
            ctx.font = '10px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            ctx.fillText(n.lbl, nx, ny + n.r + 5);
        }

        // Animate Packets
        packets = packets.filter(p => p.p <= 1);
        for (const p of packets) {
            const na = gn(p.from), nb = gn(p.to);
            const px = (na.x + (nb.x - na.x) * p.p) * W;
            const py = (na.y + (nb.y - na.y) * p.p) * H;

            ctx.beginPath();
            ctx.arc(px, py, 4.5, 0, Math.PI * 2);
            ctx.fillStyle = p.col;
            ctx.shadowBlur = 14;
            ctx.shadowColor = p.col;
            ctx.fill();
            ctx.shadowBlur = 0;

            p.p += p.spd;
        }

        if (tick % 12 === 0) spawnPacket();
        tick++;
        requestAnimationFrame(render);
    }

    resize();
    new ResizeObserver(resize).observe(cv);
    render();
})();

// ====================================================
// 7. NAVBAR SCROLL EFFECT
// ====================================================
window.addEventListener('scroll', () => {
    const n = document.getElementById('navbar');
    if (!n) return;
    if (window.scrollY > 30) {
        n.style.background = 'rgba(3, 7, 18, 0.95)';
        n.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.6)';
    } else {
        n.style.background = 'rgba(3, 7, 18, 0.75)';
        n.style.boxShadow = 'none';
    }
});
</script>
</body>
</html>
