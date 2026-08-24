{{-- Halaman chat WhatsApp-like: template berdeda (bukan public/admin), guest via cookie --}}
@php
    $siteName = config('app.name', 'Mayur');
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
    <title>Chat — {{ $siteName }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #dcdcd4;
            display: flex; justify-content: center;
        }
        .app {
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

        /* Messages */
        .messages {
            flex: 1; overflow-y: auto; padding: 16px 12px 10px;
            background-image: radial-gradient(rgba(0,0,0,.03) 1px, transparent 1px);
            background-size: 18px 18px;
        }
        .msg {
            max-width: 78%; padding: 7px 10px 5px; margin-bottom: 8px;
            border-radius: 9px; font-size: 14px; line-height: 1.45;
            position: relative; word-wrap: break-word; white-space: pre-wrap;
            box-shadow: 0 1px 1px rgba(0,0,0,.12);
        }
        .msg.in {
            background: #fff; color: #111;
            border-top-left-radius: 0; align-self: flex-start;
            margin-right: auto;
        }
        .msg.out {
            background: #d9fdd3; color: #111;
            border-top-right-radius: 0; align-self: flex-end;
            margin-left: auto;
        }
        .msg a { color: #027eb5; word-break: break-all; }
        .msg .time {
            float: right; font-size: 10.5px; color: rgba(17,17,17,.45);
            margin: 6px 0 0 8px;
        }
        .typing {
            display: none; align-self: flex-start; margin-bottom: 8px;
            background: #fff; border-radius: 9px; border-top-left-radius: 0;
            padding: 10px 14px; width: fit-content;
            box-shadow: 0 1px 1px rgba(0,0,0,.12);
        }
        .typing span {
            display: inline-block; width: 7px; height: 7px; margin: 0 1.5px;
            background: #aaa; border-radius: 50%;
            animation: blink 1.2s infinite both;
        }
        .typing span:nth-child(2) { animation-delay: .2s; }
        .typing span:nth-child(3) { animation-delay: .4s; }
        @keyframes blink { 0%,80%,100%{opacity:.3} 40%{opacity:1} }

        /* Input bar */
        .chat-input {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 10px; background: #f0f2f5; flex-shrink: 0;
        }
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
        .send-btn:active { transform: scale(.92); }
        .send-btn:disabled { opacity: .55; cursor: default; }
        .send-btn .material-symbols-outlined { font-size: 22px; }

        @media (min-width: 481px) {
            body { padding: 18px 0; }
            .app { height: calc(100dvh - 36px); border-radius: 10px; overflow: hidden; }
        }
    </style>
</head>
<body>
<div class="app">
    <header class="chat-header">
        <a href="{{ url('/') }}" class="back-link" title="Kembali"><span class="material-symbols-outlined">arrow_back</span></a>
        <div class="avatar"><span class="material-symbols-outlined">storefront</span></div>
        <div class="info">
            <div class="name">Warung Sayur Online</div>
            <div class="status" id="chat-status">online</div>
        </div>
        <span class="material-symbols-outlined">more_vert</span>
    </header>

    <main class="messages" id="messages">
        @forelse($messages as $m)
            <div class="msg {{ $m->role === 'user' ? 'out' : 'in' }}"><span class="msg-text">{{ $m->content }}</span><span class="time">{{ $m->created_at?->format('H:i') }}</span></div>
        @empty
            <div class="msg in">Halo! 👋 Selamat datang di *Warung Sayur Online*.
Ada yang bisa saya bantu? Ketik *"cara pesan"* untuk info cara belanja ya 😊<span class="time">{{ now()->format('H:i') }}</span></div>
        @endforelse
        <div class="typing" id="typing"><span></span><span></span><span></span></div>
    </main>

    <form class="chat-input" id="chat-form">
        <input type="text" id="chat-text" placeholder="Ketik pesan..." autocomplete="off" maxlength="2000">
        <button type="submit" class="send-btn" id="send-btn" title="Kirim">
            <span class="material-symbols-outlined">send</span>
        </button>
    </form>
</div>

<script>
(function () {
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-text');
    const btn = document.getElementById('send-btn');
    const box = document.getElementById('messages');
    const typing = document.getElementById('typing');
    const status = document.getElementById('chat-status');

    const scrollDown = () => { box.scrollTop = box.scrollHeight; };
    scrollDown();

    const escapeHtml = (s) => s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    // WhatsApp-ish formatting: *bold*, _italic_, linkify URL
    const render = (s) => escapeHtml(s)
        .replace(/\*(.+?)\*/g, '<strong>$1</strong>')
        .replace(/_(.+?)_/g, '<em>$1</em>')
        .replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>');

    // Format ulang pesan yang dimuat dari server (riwayat) agar link bisa diklik
    document.querySelectorAll('.msg-text').forEach(el => {
        el.innerHTML = render(el.textContent);
    });

    const bubble = (content, out) => {
        const div = document.createElement('div');
        div.className = 'msg ' + (out ? 'out' : 'in');
        const time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        div.innerHTML = render(content) + '<span class="time">' + time + '</span>';
        box.insertBefore(div, typing);
        scrollDown();
        return div;
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = input.value.trim();
        if (!message || btn.disabled) return;

        bubble(message, true);
        input.value = '';
        btn.disabled = true;
        typing.style.display = 'block';
        status.textContent = 'mengetik...';
        scrollDown();

        try {
            const res = await fetch('{{ route('chat.web.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message }),
            });
            const json = await res.json();
            bubble(json.reply || 'Maaf, terjadi kesalahan.', false);
        } catch (err) {
            bubble('Koneksi bermasalah 😥 coba kirim ulang pesannya.', false);
        } finally {
            typing.style.display = 'none';
            status.textContent = 'online';
            btn.disabled = false;
            input.focus();
            scrollDown();
        }
    });

    input.focus();
})();
</script>
</body>
</html>
