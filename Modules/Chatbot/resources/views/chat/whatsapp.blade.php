{{-- Halaman chat WhatsApp-like: template berdeda (bukan public/admin), guest via cookie --}}
@php
    $siteName = config('website.name', config('app.name', 'Mayur'));
    // Warna ikut pengaturan website (config/website.php)
    $primary = config('website.colors.primary', '#008069');
    $onPrimary = config('website.colors.on_primary', '#ffffff');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat - {{ $siteName }}</title>
    <link rel="icon" href="{{ \App\Models\WebsiteSetting::fileUrl(\App\Models\WebsiteSetting::merged()['favicon'] ?? null) ?? asset('favicon.ico') }}" sizes="any">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #dcdcd4;
            display: flex; justify-content: center;
        }
        .app {
            position: relative;
            width: 100%; max-width: 480px; height: 100dvh;
            display: flex; flex-direction: column;
            background: #efeae2;
            box-shadow: 0 0 30px rgba(0,0,0,.25);
        }

        /* Header */
        .chat-header {
            background: {{ $primary }}; color: {{ $onPrimary }};
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; flex-shrink: 0;
        }
        .chat-header .avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: {{ $onPrimary }}; color: {{ $primary }};
            display: grid; place-items: center; flex-shrink: 0;
        }
        .chat-header .info { flex: 1; min-width: 0; }
        .chat-header .name { font-weight: 600; font-size: 15px; }
        .chat-header .status { font-size: 12px; opacity: .85; }
        .chat-header .material-symbols-outlined { font-size: 22px; }
        .back-link { color: #fff; text-decoration: none; display: grid; place-items: center; }
        .cart-btn {
            position: relative; border: none; background: transparent; color: {{ $onPrimary }};
            cursor: pointer; padding: 6px; display: grid; place-items: center;
        }
        .cart-btn .material-symbols-outlined {
            font-size: 22px; font-weight: 400; font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0;
        }
        .cart-badge {
            position: absolute; top: -1px; right: -3px;
            min-width: 17px; height: 17px; padding: 0 4px;
            background: {{ $onPrimary }}; color: {{ $primary }};
            border-radius: 9px; font-size: 10px; font-weight: 400;
            display: grid; place-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,.25);
        }
        .floating-cta {
            position: absolute; top: 66px; right: 14px; z-index: 25;
            border: none; background: {{ $primary }}; color: {{ $onPrimary }};
            border-radius: 20px; padding: 9px 15px;
            font-size: 12.5px; font-weight: 600; cursor: pointer;
            box-shadow: 0 4px 14px rgba(0,0,0,.28);
            transition: transform .1s ease, filter .15s ease;
        }
        .floating-cta:hover { filter: brightness(1.08); }
        .floating-cta:active { transform: scale(.95); }
        .cart-item { display: flex; gap: 10px; align-items: center; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .cart-item:last-of-type { border-bottom: none; }
        .cart-item img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; background: #f2f2f2; }
        .ci-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
        .ci-name { font-size: 13px; font-weight: 600; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
        .ci-qty { font-size: 11.5px; color: #888; margin: 0; display: block; }
        .ci-total { font-size: 13.5px; font-weight: 700; color: {{ $primary }}; white-space: nowrap; }
        .ci-right { display: flex; flex-direction: column; align-items: flex-end; justify-content: space-between; gap: 6px; flex-shrink: 0; align-self: stretch; padding: 2px 0; }
        .ci-del {
            border: none; background: transparent; color: #c0392b;
            cursor: pointer; padding: 4px; border-radius: 6px; flex-shrink: 0;
            display: grid; place-items: center;
        }
        .ci-del:hover { background: #fdecea; }
        .ci-del .material-symbols-outlined { font-size: 18px; font-weight: 400; }
        .ci-num { width: 18px; font-size: 12px; font-weight: 700; color: {{ $primary }}; flex-shrink: 0; }

        /* Messages */
        .messages {
            margin-top:2rem;
            flex: 1; overflow-y: auto; padding: 16px 12px 10px;
            background-image: radial-gradient(rgba(0,0,0,.03) 1px, transparent 1px);
            background-size: 18px 18px;
            display: flex; flex-direction: column;
        }
        .msg {
            max-width: 78%; padding: 7px 10px 5px; margin-bottom: 8px;
            border-radius: 9px; font-size: 14px; line-height: 1.45;
            position: relative; word-wrap: break-word; white-space: pre-wrap;
            box-shadow: 0 1px 1px rgba(0,0,0,.12);
        }
        .msg.in { background: #fff; color: #111; border-top-left-radius: 0; margin-right: auto; }
        .msg.out { background: #d9fdd3; color: #111; border-top-right-radius: 0; margin-left: auto; }
        .msg a { color: #027eb5; word-break: break-all; }
        .msg .time { float: right; font-size: 10.5px; color: rgba(17,17,17,.45); margin: 6px 0 0 8px; }
        .typing {
            display: none; align-self: flex-start; margin-bottom: 8px;
            background: #fff; border-radius: 9px; border-top-left-radius: 0;
            padding: 10px 14px; width: fit-content;
            box-shadow: 0 1px 1px rgba(0,0,0,.12);
        }
        .typing span {
            display: inline-block; width: 7px; height: 7px; margin: 0 1.5px;
            background: #aaa; border-radius: 50%; animation: blink 1.2s infinite both;
        }
        .typing span:nth-child(2) { animation-delay: .2s; }
        .typing span:nth-child(3) { animation-delay: .4s; }
        @keyframes blink { 0%,80%,100%{opacity:.3} 40%{opacity:1} }

        /* Input bar */
        .chat-input { display: flex; align-items: center; gap: 8px; padding: 8px 10px; background: #f0f2f5; flex-shrink: 0; }
        .chat-input input {
            flex: 1; height: 44px; padding: 0 16px;
            border: none; outline: none; border-radius: 22px;
            font-size: 14.5px; background: #fff; color: #111;
        }
        .send-btn {
            width: 44px; height: 44px; border-radius: 50%; border: none;
            background: {{ $primary }}; color: {{ $onPrimary }}; cursor: pointer;
            display: grid; place-items: center; flex-shrink: 0;
            transition: transform .1s ease, filter .15s ease;
        }
        .send-btn:hover { filter: brightness(1.1); }
        .send-btn:disabled { opacity: .55; cursor: default; }
        .send-btn .material-symbols-outlined { font-size: 22px; }
        /* Menu slash command */
        .chat-input { position: relative; }
        .cmd-menu {
            display: none; position: absolute; bottom: calc(100% + 8px);
            left: 10px; right: 10px; background: #fff;
            border-radius: 12px; box-shadow: 0 6px 24px rgba(0,0,0,.18);
            overflow: hidden; z-index: 55;
        }
        .cmd-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; cursor: pointer; font-size: 13.5px;
        }
        .cmd-item:hover, .cmd-item.active { background: #f4f6f4; }
        .cmd-item .material-symbols-outlined { font-size: 19px; color: {{ $primary }}; }
        .cmd-item strong { color: #222; font-weight: 600; margin-right: 6px; }
        .cmd-item small { color: #999; }
        .link-cmd { color: {{ $primary }}; cursor: pointer; text-decoration: underline; }

        /* Kartu interaktif umum */
        .wizard {
            background: #fff; border-radius: 12px; padding: 14px;
            margin: 4px 0 8px; max-width: 88%; margin-right: auto;
            box-shadow: 0 1px 2px rgba(0,0,0,.15); font-size: 13.5px;
        }
        .wizard.full { max-width: none; width: 100%; }

        /* Kartu ringkasan keranjang */
        .cart-card {
            background: #fff; border-radius: 16px; padding: 14px;
            margin: 4px 0 10px; width: 100%;
            box-shadow: 0 2px 10px rgba(0,0,0,.10);
        }
        .cart-card h4 { font-size: 14px; font-weight: 700; color: #222; margin-bottom: 12px; }
        .cc-row { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 1px solid #f2f2f2; }
        .cc-row img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; background: #f2f2f2; flex-shrink: 0; }
        .cc-num { width: 20px; font-size: 12px; font-weight: 700; color: {{ $primary }}; flex-shrink: 0; }
        .cc-name { flex: 1; min-width: 0; font-size: 13px; color: #333;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cc-qty { font-size: 11.5px; color: #999; flex-shrink: 0; }
        .cc-total { font-size: 12.5px; font-weight: 700; color: {{ $primary }}; white-space: nowrap; flex-shrink: 0; }
        .cc-subtotal {
            display: flex; justify-content: space-between; align-items: center;
            padding-top: 11px; margin-top: 3px; border-top: 1.5px solid #eee;
            font-size: 14px; font-weight: 800;
        }
        .wizard h4 { font-size: 13px; color: #555; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; }
        .w-error { color: #c0392b; font-size: 12px; margin-top: 8px; display: none; }
        .btn-primary-w {
            flex: 1; border: none; background: {{ $primary }}; color: {{ $onPrimary }};
            border-radius: 22px; padding: 11px; font-size: 14px; font-weight: 600; cursor: pointer;
        }
        .btn-primary-w:disabled { opacity: .5; cursor: default; }
        .btn-ghost-w {
            flex: 1; border: 1.5px solid {{ $primary }}; background: #fff; color: {{ $primary }};
            border-radius: 22px; padding: 11px; font-size: 14px; font-weight: 600; cursor: pointer;
        }
        .w-actions { display: flex; gap: 8px; margin-top: 14px; }

        /* Picker produk - komponen mandiri, full width */
        .prod-picker {
            background: #fff; border-radius: 16px; padding: 14px;
            margin: 4px 0 10px; width: 100%;
            box-shadow: 0 2px 10px rgba(0,0,0,.10);
        }
        .pp-head {
            display: flex; justify-content: space-between; align-items: center;
            font-weight: 700; font-size: 14px; color: #222; margin-bottom: 12px;
        }
        .pp-head .cnt { font-size: 12px; font-weight: 600; color: {{ $primary }}; }
        .prod-list { display: flex; flex-direction: column; gap: 10px; max-height: 300px; overflow-y: auto; }
        .prod-card {
            position: relative; display: flex; gap: 12px; align-items: center;
            background: #f7f7f5; border: 1.5px solid transparent; border-radius: 14px;
            padding: 10px; cursor: pointer; user-select: none;
            transition: border-color .15s ease, background .15s ease;
        }
        .prod-card.selected { border-color: {{ $primary }}; background: #fff; }
        .pimg-wrap { position: relative; flex-shrink: 0; cursor: zoom-in; }
        .prod-card .pnum {
            position: absolute; top: -8px; left: -8px; z-index: 1;
            min-width: 23px; height: 23px; padding: 0 6px;
            background: {{ $primary }}; color: {{ $onPrimary }};
            border: 2px solid #fff;
            border-radius: 12px; font-size: 11.5px; font-weight: 400;
            display: flex; align-items: center; justify-content: center;
            padding-bottom: 1px;
            box-shadow: 0 1px 4px rgba(0,0,0,.3);
        }
        .prod-card img { width: 68px; height: 68px; border-radius: 12px; object-fit: cover; background: #eee; display: block; }
        .prod-card .noimg { width: 68px; height: 68px; border-radius: 12px; background: #eee; display: grid; place-items: center; color: #bbb; }
        .pp-search { position: relative; margin-bottom: 12px; }
        .pp-search .material-symbols-outlined {
            position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
            font-size: 18px; color: #999; pointer-events: none;
        }
        .pp-search input {
            width: 100%; height: 38px; padding: 0 12px 0 36px;
            border: 1.5px solid #e5e5e5; border-radius: 20px;
            font-size: 13.5px; outline: none; background: #fafafa;
        }
        .pp-search input:focus { border-color: {{ $primary }}; background: #fff; }
        .prod-card.hidden { display: none; }

        /* Lightbox foto produk */
        .lightbox {
            position: fixed; inset: 0; z-index: 80; background: rgba(0,0,0,.85);
            display: none; place-items: center; cursor: zoom-out; padding: 20px;
        }
        .lightbox.open { display: grid; }
        .lightbox img { max-width: 92vw; max-height: 80vh; border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,.5); }
        .pinfo { flex: 1; min-width: 0; }
        .prow1 { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .prod-card .pprice { font-size: 15.5px; font-weight: 800; color: {{ $primary }}; letter-spacing: -.2px; }
        .prod-card input[type=checkbox] { accent-color: {{ $primary }}; width: 19px; height: 19px; cursor: pointer; flex-shrink: 0; }
        .prod-card .pname { font-size: 13px; color: #444; line-height: 1.4; margin-top: 3px;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .prod-card .pname .pnum {
            display: inline-grid; place-items: center;
            min-width: 20px; height: 19px; padding: 0 6px; margin-right: 5px;
            background: {{ $primary }}12; color: {{ $primary }};
            border-radius: 9px; font-size: 11px; font-weight: 800;
            vertical-align: 1px;
        }
        .prod-card .pname b { color: #222; }
        .qty-step { display: none; align-items: center; gap: 10px; margin-top: 9px; }
        .prod-card.selected .qty-step { display: flex; }
        .qty-step button {
            width: 27px; height: 27px; border-radius: 50%; border: none;
            background: {{ $primary }}; color: {{ $onPrimary }};
            cursor: pointer; font-size: 16px; line-height: 1; display: grid; place-items: center;
        }
        .qty-step .qv { min-width: 22px; text-align: center; font-weight: 800; font-size: 14.5px; }
        .order-bar { display: flex; align-items: center; gap: 10px; margin-top: 14px; }
        .order-btn {
            flex: 1; border: none; background: {{ $primary }}; color: {{ $onPrimary }};
            border-radius: 24px; padding: 12px; font-size: 14.5px; font-weight: 700; cursor: pointer;
        }
        .order-btn:disabled { opacity: .45; cursor: default; }
        .w-error { color: #c0392b; font-size: 12px; margin-top: 8px; display: none; }
        .w-error.show { display: block; }
        .qty-step { display: none; align-items: center; gap: 7px; flex-shrink: 0; }
        .prod-card.selected .qty-step { display: flex; }
        .qty-step button {
            width: 24px; height: 24px; border-radius: 50%; border: 1px solid #ccc;
            background: #fff; cursor: pointer; font-size: 15px; line-height: 1; color: #444;
        }
        .qty-step .qv { min-width: 18px; text-align: center; font-weight: 700; font-size: 13.5px; }
        .pay-btn {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            width: 100%; margin-top: 10px; border: none; background: {{ $primary }};
            color: {{ $onPrimary }}; border-radius: 22px; padding: 11px;
            font-size: 14px; font-weight: 700; cursor: pointer;
        }
        .pay-btn:disabled { opacity: .5; }

        /* Chips metode */
        .chips { display: flex; flex-direction: column; gap: 7px; margin-top: 6px; }
        .chip {
            border: 1.5px solid #ddd; background: #fff; border-radius: 10px;
            padding: 10px 12px; text-align: left; cursor: pointer;
            font-size: 13.5px; display: flex; align-items: center; gap: 8px;
            transition: all .15s ease;
        }
        .chip:hover { border-color: {{ $primary }}; background: #f6fbf6; }
        .chip.active { border-color: {{ $primary }}; background: {{ $primary }}14; font-weight: 600; }
        .chip .material-symbols-outlined { font-size: 20px; color: {{ $primary }}; }
        .radio-cod { display: flex; align-items: flex-start; gap: 12px !important; padding: 9px 12px; border: 1.5px solid #eee; border-radius: 8px; margin-top: 6px; cursor: pointer; }
        .radio-cod input[type=radio] { margin-top: 2px; accent-color: {{ $primary }}; flex-shrink: 0; width: 12px; height: 12px; }
        .radio-cod > span { margin-left: 6px; }
        .radio-cod:has(input:checked) { border-color: {{ $primary }}; background: {{ $primary }}10; }
        .radio-cod input { accent-color: {{ $primary }}; margin-top: 2px; }
        .loc-btn {
            width: 100%; border: 1.5px dashed {{ $primary }}; background: {{ $primary }}08; color: {{ $primary }};
            border-radius: 10px; padding: 10px; font-size: 13px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 6px;
        }
        .loc-btn.ok { border-style: solid; background: {{ $primary }}18; }        .map-box { height: 190px; border-radius: 10px; border: 1.5px solid #e2e2e2; margin-top: 8px; z-index: 1; }
        .map-hint { font-size: 11px; color: #999; margin-top: 5px; }
        .wizard label { display: block; font-size: 11.5px; font-weight: 600; color: #666; margin: 10px 0 4px; }
        .wizard input[type=text], .wizard input[type=tel], .wizard textarea {
            width: 100%; border: 1px solid #ddd; border-radius: 8px;
            padding: 9px 12px; font-size: 13.5px; outline: none; background: #fafafa;
        }
        .wizard input:focus, .wizard textarea:focus { border-color: {{ $primary }}; background: #fff; }

        /* Bottom sheet */
        .sheet-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 60;
            display: none; align-items: flex-end; justify-content: center;
        }
        .sheet-overlay.open { display: flex; }
        .sheet {
            width: 100%; max-width: 480px; background: #fff;
            border-radius: 18px 18px 0 0; padding: 18px 16px calc(18px + env(safe-area-inset-bottom));
            transform: translateY(100%); transition: transform .25s ease;
            max-height: 85dvh; overflow-y: auto;
        }
        .sheet-overlay.open .sheet { transform: translateY(0); }
        .sheet h3 { font-size: 16px; font-weight: 700; color: #222; margin-bottom: 4px; }
        .sheet .sub { font-size: 12px; color: #888; margin-bottom: 8px; }

        /* Form di dalam sheet */
        .sheet label {
            display: block; font-size: 11.5px; font-weight: 700;
            color: #666; text-transform: uppercase; letter-spacing: .4px;
            margin: 16px 0 6px;
        }
        .sheet input[type=text],
        .sheet input[type=tel] {
            width: 100%; height: 48px; padding: 0 14px;
            border: 1.5px solid #e2e2e2; border-radius: 10px;
            font-size: 14.5px; background: #fafafa; color: #111;
            outline: none; transition: border-color .15s ease, background .15s ease;
        }
        .sheet textarea {
            width: 100%; padding: 11px 14px; min-height: 72px; resize: vertical;
            border: 1.5px solid #e2e2e2; border-radius: 10px;
            font-size: 14px; background: #fafafa; color: #111;
            outline: none; font-family: inherit; line-height: 1.45;
            transition: border-color .15s ease, background .15s ease;
        }
        .sheet input:focus, .sheet textarea:focus { border-color: {{ $primary }}; background: #fff; }
        .sheet .w-actions { margin-top: 20px; }

        @media (min-width: 481px) {
            body { padding: 18px 0; }
            .app { height: calc(100dvh - 36px); border-radius: 10px; overflow: hidden; }
            .sheet-overlay { align-items: flex-end; }
        }
    </style>
</head>
<body>
<div class="app">
    <header class="chat-header">
        <a href="{{ url('/') }}" class="back-link" title="Kembali"><span class="material-symbols-outlined">arrow_back</span></a>
        <div class="avatar"><span class="material-symbols-outlined">storefront</span></div>
        <div class="info">
            <div class="name">{{ $siteName }}</div>
            <div class="status" id="chat-status">online</div>
        </div>
        <button type="button" id="cart-btn" class="cart-btn" title="Keranjang">
            <span class="material-symbols-outlined">shopping_cart</span>
            <span class="cart-badge" id="cart-count">0</span>
        </button>
    </header>

    {{-- Floating CTA: pin kanan atas --}}
    <button type="button" id="cta-products" class="floating-cta">Lihat Produk</button>

    <main class="messages" id="messages">
        @forelse($messages as $m)
            <div class="msg {{ $m->role === 'user' ? 'out' : 'in' }}"><span class="msg-text">{{ $m->content }}</span><span class="time">{{ $m->created_at?->format('H:i') }}</span></div>
        @empty
            <div class="msg in">Halo! 👋 Selamat datang di *{{ $siteName }}*.<br>Ada yang bisa saya bantu? Ketik <span class="link-cmd" data-cmd="/product">/product</span> atau klik tombol <strong>Lihat Produk</strong> di kanan atas 🛒</div>
        @endforelse
        <div class="typing" id="typing"><span></span><span></span><span></span></div>
    </main>

    <form class="chat-input" id="chat-form">
        <div class="cmd-menu" id="cmd-menu"></div>
        <input type="text" id="chat-text" placeholder="{{ __('Ketik pesan...') }}" autocomplete="off" maxlength="2000">
        <button type="submit" class="send-btn" id="send-btn" title="Kirim">
            <span class="material-symbols-outlined">send</span>
        </button>
    </form>
</div>

{{-- Lightbox foto --}}
<div class="lightbox" id="lightbox"><img id="lightbox-img" src="" alt=""></div>

{{-- Bottom sheet checkout --}}
<div class="sheet-overlay" id="sheet-overlay">
    <div class="sheet" id="sheet-body"></div>
</div>

<script>
(function () {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-text');
    const btn = document.getElementById('send-btn');
    const box = document.getElementById('messages');
    const typing = document.getElementById('typing');
    const status = document.getElementById('chat-status');

    const CSRF = document.querySelector('meta[name=csrf-token]').content;
    const SHIPPING = { pickup: {{ config('frontend.shipping.pickup', true) ? 'true' : 'false' }}, cod: {{ config('frontend.shipping.cod', true) ? 'true' : 'false' }}, delivery: {{ config('frontend.shipping.delivery', true) ? 'true' : 'false' }} };

    const scrollDown = () => { box.scrollTop = box.scrollHeight; };
    scrollDown();

    const escapeHtml = (s) => s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const rp = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

    // WhatsApp-ish formatting: *bold*, _italic_, linkify URL
    const render = (s) => escapeHtml(s)
        .replace(/\*(.+?)\*/g, '<strong>$1</strong>')
        .replace(/_(.+?)_/g, '<em>$1</em>')
        .replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>')
        .replace('<strong>/product</strong>', '<strong><span class="link-cmd" data-cmd="/product">/product</span></strong>');

    // Format pesan riwayat dari server
    document.querySelectorAll('.msg-text').forEach(el => { el.innerHTML = render(el.textContent); });


    function nowTime() { return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); }

    function bubble(content, out) {
        const div = document.createElement('div');
        div.className = 'msg ' + (out ? 'out' : 'in');
        div.innerHTML = render(content) + '<span class="time">' + nowTime() + '</span>';
        box.insertBefore(div, typing);
        scrollDown();
        return div;
    }

    function widget(html, extraClass) {
        const div = document.createElement('div');
        div.className = 'wizard' + (extraClass ? ' ' + extraClass : '');
        div.innerHTML = html;
        box.insertBefore(div, typing);
        scrollDown();
        return div;
    }

    async function postJson(url, data) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify(data),
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(json.message || 'Terjadi kesalahan.');
        return json;
    }

    // Mode isi-lewat-chat: null | 'name' | 'phone'
    let collect = null;
    const pendingInfo = {};

        // ---------- Slash commands ----------
    const COMMANDS = [
        { cmd: '/product', icon: 'inventory_2', desc: 'Lihat daftar produk' },
        { cmd: '/howto', icon: 'help', desc: 'Cara pemesanan' },
        { cmd: '/about', icon: 'info', desc: 'Tentang toko' },
    ];
    const cmdMenu = document.getElementById('cmd-menu');
    let cmdActive = -1;

    function hideCmdMenu() { cmdMenu.style.display = 'none'; cmdActive = -1; }

    function showCmdMenu(filter) {
        const list = COMMANDS.filter(c => c.cmd.startsWith(filter));
        if (!list.length) { hideCmdMenu(); return; }
        cmdMenu.innerHTML = list.map((c, i) =>
            '<div class="cmd-item' + (i === 0 ? ' active' : '') + '" data-cmd="' + c.cmd + '">' +
            '<span class="material-symbols-outlined">' + c.icon + '</span>' +
            '<span><strong>' + c.cmd + '</strong> <small>' + c.desc + '</small></span></div>'
        ).join('');
        cmdMenu.style.display = 'block';
        cmdActive = 0;
        cmdMenu.querySelectorAll('.cmd-item').forEach(el => {
            el.addEventListener('click', () => execCommand(el.dataset.cmd));
        });
    }

    function moveCmdActive(dir) {
        const items = [...cmdMenu.querySelectorAll('.cmd-item')];
        if (!items.length) return;
        cmdActive = (cmdActive + dir + items.length) % items.length;
        items.forEach((el, i) => el.classList.toggle('active', i === cmdActive));
    }

    async function execCommand(cmd) {
        hideCmdMenu();
        bubble(cmd, true);

        if (cmd === '/product') {
            typing.style.display = 'block';
            scrollDown();
            try {
                const res = await fetch('{{ route('chat.web.products') }}');
                const json = await res.json();
                renderProductPicker(json.products);
            } catch (e) {
                bubble('Gagal memuat produk, coba lagi ya.', false);
            }
            typing.style.display = 'none';
            scrollDown();
            return;
        }

        const msg = cmd === '/howto'
            ? 'Bagaimana cara memesan produk?'
            : 'Ceritakan tentang toko ini dan layanannya';
        await sendToAI(msg);
    }

    input.addEventListener('input', () => {
        const v = input.value;
        if (v.startsWith('/') && !v.includes(' ')) showCmdMenu(v.toLowerCase());
        else hideCmdMenu();
    });

    input.addEventListener('keydown', (e) => {
        if (cmdMenu.style.display !== 'block') return;
        if (e.key === 'ArrowDown') { e.preventDefault(); moveCmdActive(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); moveCmdActive(-1); }
        else if (e.key === 'Enter') { e.preventDefault(); const el = cmdMenu.querySelector('.cmd-item.active'); if (el) execCommand(el.dataset.cmd); }
        else if (e.key === 'Escape') hideCmdMenu();
    });

    // ---------- Kirim pesan ----------
    async function sendToAI(message) {
        btn.disabled = true;
        typing.style.display = 'block';
        status.textContent = 'mengetik...';
        scrollDown();

        try {
            const res = await fetch('{{ route('chat.web.send') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
                body: JSON.stringify({ message }),
            });
            const json = await res.json();
            bubble(json.reply || 'Maaf, terjadi kesalahan.', false);
            if (json.ui && json.ui.type === 'products') renderProductPicker(json.ui.products);
            if (json.ui && json.ui.type === 'shipping') openSheet('method');
        } catch (err) {
            bubble('Koneksi bermasalah 😥 coba kirim ulang pesannya.', false);
        } finally {
            typing.style.display = 'none';
            status.textContent = 'online';
            btn.disabled = false;
            input.focus();
            scrollDown();
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message || btn.disabled) return;

        // Tangkap nama/telepon via chat jika mode aktif
        if (collect === 'name') {
            pendingInfo.name = message;
            collect = 'phone';
            bubble(message, true);
            input.value = '';
            bubble('Oke! Sekarang ketik *No. HP / WhatsApp* kamu ya 📱', false);
            return;
        }
        if (collect === 'phone') {
            pendingInfo.phone = message;
            bubble(message, true);
            input.value = '';
            collect = null;
            try {
                await postJson('{{ route('chat.web.details') }}', pendingInfo);
                bubble('Data tersimpan ✅', false);
                openSheet('method');
            } catch (err) { bubble(err.message, false); }
            return;
        }

        // Slash command persis → eksekusi
        if (message.startsWith('/')) {
            const exact = COMMANDS.find(c => c.cmd === message.toLowerCase());
            if (exact) {
                input.value = '';
                hideCmdMenu();
                await execCommand(exact.cmd);
                return;
            }
        }

        bubble(message, true);
        input.value = '';
        hideCmdMenu();
        await sendToAI(message);
    });
// ---------- Picker produk ----------
    const letterOf = (i) => i + 1; // 1, 2, 3, ...

    function showLightbox(src) {
        const lb = document.getElementById('lightbox');
        document.getElementById('lightbox-img').src = src;
        lb.classList.add('open');
    }
    document.getElementById('lightbox').addEventListener('click', function () {
        this.classList.remove('open');
    });

    // Klik tautan command di dalam bubble (misal /product pada sambutan)
    document.addEventListener('click', (e) => {
        const el = e.target.closest('.link-cmd');
        if (!el) return;
        e.preventDefault();
        execCommand(el.dataset.cmd);
    });

    function renderCartCard(items, subtotal) {
        const card = document.createElement('div');
        card.className = 'cart-card';
        card.innerHTML =
            '<h4>🛒 Keranjang Diperbarui</h4>' +
            items.map((it, i) =>
                '<div class="cc-row">' +
                '<span class="cc-num">' + (i + 1) + '.</span>' +
                (it.image ? '<img src="' + escapeHtml(it.image) + '" alt="">' : '') +
                '<span class="cc-name">' + escapeHtml(it.name) + '</span>' +
                '<span class="cc-qty">x' + it.qty + '</span>' +
                '<span class="cc-total">' + rp(it.line_total) + '</span>' +
                '</div>'
            ).join('') +
            '<div class="cc-subtotal"><span>Total</span><span style="color:{{ $primary }}">' + rp(subtotal) + '</span></div>';
        box.insertBefore(card, typing);
        scrollDown();
    }

    function renderProductPicker(products) {
        const picked = {};
        const wrap = document.createElement('div');
        wrap.className = 'prod-picker';
        wrap.innerHTML =
            '<div class="pp-head"><span>Pilih Produk</span><span class="cnt" id="pp-count">Belum ada yang dipilih</span></div>' +
            '<div class="pp-search"><span class="material-symbols-outlined">search</span>' +
            '<input type="text" id="pp-q" placeholder="Cari produk..."></div>' +
            '<div class="prod-list">' +
            products.map((p, i) =>
                '<div class="prod-card" data-name="' + escapeHtml(p.name.toLowerCase()) + '">' +
                '<span class="pimg-wrap">' +
                '<span class="pnum">' + letterOf(i) + '</span>' +
                (p.image
                    ? '<img class="pimg" src="' + escapeHtml(p.image) + '" alt="">'
                    : '<div class="noimg"><span class="material-symbols-outlined">image</span></div>') +
                '</span>' +
                '<span class="pinfo">' +
                '<span class="prow1"><span class="pprice">' + rp(p.price) + '</span>' +
                '<input type="checkbox" data-id="' + p.id + '"></span>' +
                '<span class="pname">' + escapeHtml(p.name) + '</span>' +
                '<span class="qty-step"><button type="button" class="qminus">-</button><span class="qv">1</span><button type="button" class="qplus">+</button></span>' +
                '</span>' +
                '</div>'
            ).join('') +
            '</div>' +
            '<div class="order-bar"><button class="order-btn" id="pp-order" disabled>Pesan Sekarang</button></div>' +
            '<div class="w-error" id="pp-err"></div>';
        box.insertBefore(wrap, typing);

        // Live search: filter kartu setiap ketikan
        wrap.querySelector('#pp-q').addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            wrap.querySelectorAll('.prod-card').forEach(pc => {
                const hay = pc.dataset.name || '';
                pc.classList.toggle('hidden', q !== '' && !hay.includes(q));
            });
        });

        // Klik gambar ? lightbox
        wrap.querySelectorAll('.pimg').forEach(img => {
            img.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                showLightbox(img.src);
            });
        });

        const orderBtn = wrap.querySelector('#pp-order');
        const cntEl = wrap.querySelector('#pp-count');

        function refresh() {
            const n = Object.keys(picked).length;
            orderBtn.disabled = n === 0;
            cntEl.textContent = n === 0 ? 'Belum ada yang dipilih' : n + ' produk dipilih';
        }

        wrap.querySelectorAll('.prod-card').forEach(pc => {
            const cb = pc.querySelector('input[type=checkbox]');
            const qv = pc.querySelector('.qv');

            pc.addEventListener('click', (e) => {
                // Biarkan checkbox & stepper mengelola event-nya sendiri
                if (e.target === cb || e.target.closest('.qty-step')) return;
                cb.checked = !cb.checked;
                syncCard();
            });

            function syncCard() {
                pc.classList.toggle('selected', cb.checked);
                if (cb.checked) picked[cb.dataset.id] = { qty: parseInt(qv.textContent) };
                else delete picked[cb.dataset.id];
                refresh();
            }

            cb.addEventListener('change', () => syncCard());

            pc.querySelector('.qminus').addEventListener('click', (e) => {
                e.stopPropagation();
                qv.textContent = Math.max(1, parseInt(qv.textContent) - 1);
                if (picked[cb.dataset.id]) { picked[cb.dataset.id].qty = parseInt(qv.textContent); refresh(); }
            });
            pc.querySelector('.qplus').addEventListener('click', (e) => {
                e.stopPropagation();
                qv.textContent = Math.min(999, parseInt(qv.textContent) + 1);
                if (picked[cb.dataset.id]) { picked[cb.dataset.id].qty = parseInt(qv.textContent); refresh(); }
            });
        });

        orderBtn.addEventListener('click', async () => {
            const items = Object.entries(picked).map(([id, v]) => ({ id: parseInt(id), qty: v.qty }));
            if (!items.length) return;
            orderBtn.disabled = true;
            try {
                const r = await postJson('{{ route('chat.web.addItems') }}', { items });
                wrap.remove();
                renderCartCard(r.items, r.subtotal);
                cartCache = { items: r.items, subtotal: r.subtotal };
                refreshCartBadge();
                renderCartActions();
            } catch (e) {
                const errEl = wrap.querySelector('#pp-err');
                errEl.textContent = e.message;
                errEl.classList.add('show');
                orderBtn.disabled = false;
            }
            scrollDown();
        });
    }

    // ---------- Tombol aksi keranjang ----------
    function renderCartActions() {
        const card = widget(
            '<div class="w-actions" style="margin-top:0">' +
            '<button class="btn-ghost-w" id="ca-more">Tambah Lagi</button>' +
            '<button class="btn-primary-w" id="ca-checkout">Checkout</button>' +
            '</div>', 'full');

        card.querySelector('#ca-more').addEventListener('click', async () => {
            card.remove();
            typing.style.display = 'block';
            scrollDown();
            try {
                const res = await fetch('{{ route('chat.web.products') }}');
                const json = await res.json();
                bubble('Mau tambah apa lagi kak? Pilih di sini 👇', false);
                renderProductPicker(json.products);
            } catch (e) { bubble('Gagal memuat produk, coba lagi ya.', false); }
            typing.style.display = 'none';
            scrollDown();
        });

        card.querySelector('#ca-checkout').addEventListener('click', async () => {
            card.remove();
            try {
                const r = await postJson('{{ route('chat.web.checkoutStart') }}', {});
                bubble(r.summary + '\nLengkapi datanya dulu ya 👇', false);
                openSheet('info');
            } catch (e) { bubble(e.message, false); }
            scrollDown();
        });
        scrollDown();
    }

    // ---------- Bottom sheet ----------
    const overlay = document.getElementById('sheet-overlay');
    const sheetBody = document.getElementById('sheet-body');
    let coords = null;
    let cartCache = null;

    // Prefetch keranjang saat halaman dibuka → sheet terbuka instan
    function prefetchCart() {
        fetch('{{ route('chat.web.cart') }}')
            .then(r => r.json())
            .then(json => { cartCache = json; refreshCartBadgeFrom(json); })
            .catch(() => {});
    }

    function openSheet(step) {
        overlay.classList.add('open');
        if (step === 'info') renderSheetInfo();
        else if (step === 'cart') renderSheetCart();
        else renderSheetMethod();
    }
    function closeSheet() { overlay.classList.remove('open'); }
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeSheet(); });

    // ---------- Cart ----------
    function refreshCartBadgeFrom(json) {
        const total = (json.items || []).reduce((s, it) => s + it.qty, 0);
        const b = document.getElementById('cart-count');
        b.textContent = total;
        b.style.display = total > 0 ? 'grid' : 'none';
    }

    async function refreshCartBadge() {
        try {
            const res = await fetch('{{ route('chat.web.cart') }}');
            const json = await res.json();
            cartCache = json;
            refreshCartBadgeFrom(json);
        } catch (e) { /* ignore */ }
    }

    document.getElementById('cart-btn').addEventListener('click', () => openSheet('cart'));

    function renderSheetCart() {
        // Render instan dari cache, lalu segarkan dari server di belakang
        if (cartCache) {
            sheetBody.innerHTML = buildCartHtml(cartCache);
            bindCheckoutBtn();
        } else {
            sheetBody.innerHTML = '<h3>Keranjang</h3><p class="sub">Memuat...</p>';
        }

        fetch('{{ route('chat.web.cart') }}')
            .then(r => r.json())
            .then(json => {
                cartCache = json;
                sheetBody.innerHTML = buildCartHtml(json);
                bindCheckoutBtn();
            })
            .catch(() => {
                if (!cartCache) {
                    sheetBody.innerHTML = '<p style="color:#c0392b;padding:16px 0">Gagal memuat keranjang.</p>';
                }
            });
    }

    function buildCartHtml(json) {
        const items = json.items || [];
        if (!items.length) {
            return '<h3>Keranjang</h3>' +
                '<p style="text-align:center;color:#999;padding:24px 0;font-size:13.5px">Keranjang masih kosong 🛒<br>Pilih produk dulu ya 😊</p>';
        }
        return '<h3>Keranjang</h3><p class="sub">' + items.length + ' jenis produk</p>' +
            items.map((it, i) =>
                '<div class="cart-item">' +
                '<span class="ci-num">' + (i + 1) + '.</span>' +
                (it.image ? '<img src="' + escapeHtml(it.image) + '" alt="">' : '') +
                '<span class="ci-info">' +
                '<span class="ci-name">' + escapeHtml(it.name) + '</span>' +
                '<span class="ci-qty">' + it.qty + ' x ' + rp(it.price) + '</span>' +
                '</span>' +
                '<span class="ci-right">' +
                '<button type="button" class="ci-del" data-id="' + it.id + '" title="Hapus"><span class="material-symbols-outlined">delete</span></button>' +
                '<span class="ci-total">' + rp(it.line_total) + '</span>' +
                '</span>' +
                '</div>'
            ).join('') +
            '<div class="prow1" style="margin-top:12px;font-weight:700;font-size:14px">' +
            '<span>Subtotal</span><span style="color:{{ $primary }}">' + rp(json.subtotal) + '</span></div>' +
            '<div class="w-actions"><button class="btn-primary-w" id="sh-cart-checkout">Checkout</button></div>';
    }

    function bindCheckoutBtn() {
        const btn = sheetBody.querySelector('#sh-cart-checkout');
        if (btn) {
            btn.addEventListener('click', async () => {
                const json = cartCache;
                try {
                    await postJson('{{ route('chat.web.checkoutStart') }}', {});
                    bubble((json.items.map(it => it.name + ' x' + it.qty).join(', ')) + ' - subtotal ' + rp(json.subtotal), true);
                    openSheet('info');
                } catch (e) { /* keranjang berubah, reload */ renderSheetCart(); }
            });
        }

        sheetBody.querySelectorAll('.ci-del').forEach(del => {
            del.addEventListener('click', async (e) => {
                e.stopPropagation();
                del.disabled = true;
                try {
                    const r = await postJson('{{ route('chat.web.removeItem') }}', { id: parseInt(del.dataset.id) });
                    cartCache = r;
                    sheetBody.innerHTML = buildCartHtml(r);
                    bindCheckoutBtn();
                    refreshCartBadgeFrom(r);
                } catch (err) { del.disabled = false; }
            });
        });
    }

    refreshCartBadge();

    function renderSheetInfo() {
        coords = null;
        sheetBody.innerHTML =
            '<h3>Data Penerima</h3><p class="sub">Untuk konfirmasi dan keperluan pengiriman.</p>' +
            '<label>Nama lengkap *</label><input type="text" id="sh-name" placeholder="cth: Budi Santoso">' +
            '<label>No. HP / WhatsApp *</label><input type="tel" id="sh-phone" placeholder="cth: 081234567890">' +
            '<div class="w-error" id="sh-err"></div>' +
            '<div class="w-actions">' +
            '<button class="btn-ghost-w" id="sh-chat">Isi lewat chat</button>' +
            '<button class="btn-primary-w" id="sh-save">Simpan</button></div>';

        // Prefill dari data yang sudah tersimpan di sesi
        fetch('{{ route('chat.web.contact') }}')
            .then(r => r.json())
            .then(c => {
                if (c.name) sheetBody.querySelector('#sh-name').value = c.name;
                if (c.phone) sheetBody.querySelector('#sh-phone').value = c.phone;
                if (c.name && c.phone) {
                    sheetBody.querySelector('#sh-save').textContent = 'Lanjutkan Pengiriman';
                }
            })
            .catch(() => {});

        sheetBody.querySelector('#sh-save').addEventListener('click', async () => {
            const name = sheetBody.querySelector('#sh-name').value.trim();
            const phone = sheetBody.querySelector('#sh-phone').value.trim();
            const errEl = sheetBody.querySelector('#sh-err');
            errEl.style.display = 'none';
            if (name.length < 2 || phone.length < 8) {
                errEl.textContent = 'Nama dan No. HP wajib diisi dengan benar.';
                errEl.style.display = 'block';
                return;
            }
            try {
                await postJson('{{ route('chat.web.details') }}', { name, phone });
                renderSheetMethod();
            } catch (e) { errEl.textContent = e.message; errEl.style.display = 'block'; }
        });

        sheetBody.querySelector('#sh-chat').addEventListener('click', () => {
            closeSheet();
            collect = 'name';
            pendingInfo.name = undefined;
            pendingInfo.phone = undefined;
            bubble('Oke! Ketik *nama lengkap* kamu dulu ya 😊', false);
        });
    }

    function renderSheetMethod() {
        coords = null;
        sheetBody.innerHTML =
            '<h3>Metode Pengiriman</h3><p class="sub">Pilih cara pesananmu sampai.</p>' +
            '<div class="chips">' +
            (SHIPPING.pickup ? '<button type="button" class="chip" data-m="pickup"><span class="material-symbols-outlined">storefront</span> Diambil di Gudang (gratis)</button>' : '') +
            (SHIPPING.cod ? '<button type="button" class="chip" data-m="cod"><span class="material-symbols-outlined">payments</span> COD - bayar di titik temu</button>' : '') +
            (SHIPPING.delivery ? '<button type="button" class="chip" data-m="delivery"><span class="material-symbols-outlined">local_shipping</span> Dikirim ke alamat saya</button>' : '') +
            '</div><div id="sh-extra"></div><div class="w-error" id="sh-err"></div>' +
            '<div class="w-actions"><button class="btn-primary-w" id="sh-confirm" style="display:none">Konfirmasi Pengiriman</button></div>';

        let method = null;
        const extra = sheetBody.querySelector('#sh-extra');
        const confirmBtn = sheetBody.querySelector('#sh-confirm');
        const errEl = sheetBody.querySelector('#sh-err');

        sheetBody.querySelectorAll('.chip').forEach(chip => chip.addEventListener('click', async () => {
            sheetBody.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            method = chip.dataset.m;
            extra.innerHTML = '';
            confirmBtn.style.display = method === 'pickup' ? 'none' : 'flex';

            if (method === 'pickup') {
                await confirmShipping({ method: 'pickup' });
                return;
            }

            if (method === 'cod') {
                extra.innerHTML = '<p style="font-size:12px;color:#666;margin-top:10px">Pilih titik COD:</p><div id="cod-list">Memuat...</div>';
                try {
                    const res = await fetch('{{ route('chat.web.codLocations') }}');
                    const { locations } = await res.json();
                    const list = sheetBody.querySelector('#cod-list');
                    if (!locations.length) { list.textContent = 'Belum ada titik COD tersedia.'; return; }
                    list.innerHTML = locations.map(l =>
                        '<label class="radio-cod"><input type="radio" name="codloc" value="' + l.id + '">' +
                        '<span><strong>' + escapeHtml(l.name) + '</strong>' + (l.fee !== null ? ' - ongkir ' + rp(l.fee) : '') +
                        '<br><small style="color:#888">' + escapeHtml(l.address || '') + '</small></span></label>').join('');
                } catch (e) { sheetBody.querySelector('#cod-list').textContent = 'Gagal memuat lokasi.'; }
            }

            if (method === 'delivery') {
                extra.innerHTML =
                    '<label>Klik peta untuk pin lokasi, atau pakai GPS</label>' +
                    '<div id="sh-map" class="map-box"></div>' +
                    '<div class="map-hint">Geser pin untuk koreksi lokasi. Ongkir dihitung dari jarak.</div>' +
                    '<button type="button" class="loc-btn" id="sh-loc"><span class="material-symbols-outlined">my_location</span> Gunakan Lokasi Saat Ini</button>' +
                    '<label>Alamat lengkap *</label>' +
                    '<textarea id="sh-address" rows="2" placeholder="Nama jalan, nomor rumah, patokan..."></textarea>';

                const wh = @json(config('so.shipping.warehouse'));
                const mapEl = extra.querySelector('#sh-map');
                const map = L.map(mapEl).setView([parseFloat(wh.lat), parseFloat(wh.lng)], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

                let marker = null;
                function setMarker(lat, lng) {
                    coords = { lat, lng };
                    const locBtnNow = sheetBody.querySelector('#sh-loc');
                    if (marker) {
                        marker.setLatLng([lat, lng]);
                        locBtnNow.classList.add('ok');
                        locBtnNow.innerHTML = '<span class="material-symbols-outlined">check_circle</span> Lokasi terpasang ✓';
                        return;
                    }
                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    marker.on('dragend', () => {
                        const ll = marker.getLatLng();
                        coords = { lat: ll.lat, lng: ll.lng };
                    });
                    locBtnNow.classList.add('ok');
                    locBtnNow.innerHTML = '<span class="material-symbols-outlined">check_circle</span> Lokasi terpasang ✓';
                }

                map.on('click', (e) => setMarker(e.latlng.lat, e.latlng.lng));
                setTimeout(() => map.invalidateSize(), 200);

                sheetBody.querySelector('#sh-loc').addEventListener('click', () => {
                    if (!navigator.geolocation) {
                        sheetBody.querySelector('#sh-loc').textContent = 'Browser tidak mendukung GPS';
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            setMarker(pos.coords.latitude, pos.coords.longitude);
                            map.setView([pos.coords.latitude, pos.coords.longitude], 15);
                        },
                        () => {},
                        { enableHighAccuracy: true, timeout: 15000 },
                    );
                });
            }
        }));

        confirmBtn.addEventListener('click', () => {
            errEl.style.display = 'none';
            const data = { method };
            if (method === 'cod') {
                const picked = sheetBody.querySelector('input[name=codloc]:checked');
                if (!picked) { errEl.textContent = 'Pilih salah satu titik COD dulu ya.'; errEl.style.display = 'block'; return; }
                data.cod_location_id = parseInt(picked.value);
            }
            if (method === 'delivery') {
                if (!coords) { errEl.textContent = 'Bagikan lokasi kamu dulu lewat tombol lokasi ya.'; errEl.style.display = 'block'; return; }
                data.lat = coords.lat;
                data.lng = coords.lng;
                data.address = sheetBody.querySelector('#sh-address').value.trim();
                if (!data.address) { errEl.textContent = 'Alamat lengkap wajib diisi ya.'; errEl.style.display = 'block'; return; }
            }
            confirmBtn.disabled = true;
            confirmShipping(data).catch(e => { errEl.textContent = e.message; errEl.style.display = 'block'; confirmBtn.disabled = false; });
        });
    }

        async function confirmShipping(data) {
        closeSheet();
        typing.style.display = 'block';
        status.textContent = 'Memproses pesanan...';
        scrollDown();

        try {
            const s = await postJson('{{ route('chat.web.shipping') }}', data);
            bubble(s.summary, false);

            const r = await postJson('{{ route('chat.web.pay') }}', {});
            bubble('🎉 Pesanan *' + r.code + '* berhasil dibuat!\n\n' + r.summary
                + '\n\n💳 Link pembayaran QRIS:\n' + r.payment_url
                + '\nScan QRIS-nya ya kak, status otomatis PAID setelah dibayar. Terima kasih! 🥬😊', false);
            refreshCartBadge();
        } catch (e) {
            bubble(e.message || 'Gagal memproses pesanan, coba lagi ya.', false);
        } finally {
            typing.style.display = 'none';
            status.textContent = 'online';
            scrollDown();
        }
    }
    // Tombol CTA di sambutan awal
    const ctaBtn = document.getElementById('cta-products');
    if (ctaBtn) {
        ctaBtn.addEventListener('click', async function () {
            // Jika picker sudah terbuka, cukup scroll ke sana
            const existing = box.querySelector('.prod-picker');
            if (existing) {
                existing.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            this.disabled = true;
            typing.style.display = 'block';
            scrollDown();
            try {
                const res = await fetch('{{ route('chat.web.products') }}');
                const json = await res.json();
                renderProductPicker(json.products);
            } catch (e) {
                bubble('Gagal memuat produk, coba lagi ya.', false);
                this.disabled = false;
            }
            typing.style.display = 'none';
            scrollDown();
        });
    }

    input.focus();
})();
</script>
</body>
</html>
