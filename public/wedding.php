<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Wedding.php';

$flash = null;
$rsvpName = '';
$rsvpAttendance = 'hadir';
$rsvpGuests = 1;
$rsvpMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rsvpName = trim((string) ($_POST['name'] ?? ''));
    $rsvpAttendance = $_POST['attendance'] ?? 'hadir';
    $rsvpGuests = (int) ($_POST['guests'] ?? 1);
    $rsvpMessage = trim((string) ($_POST['message'] ?? ''));

    if ($rsvpName === '' || mb_strlen($rsvpName) > 100) {
        $flash = 'Nama wajib diisi (maks. 100 karakter).';
    } elseif (mb_strlen($rsvpMessage) > 1000) {
        $flash = 'Pesan terlalu panjang (maks. 1000 karakter).';
    } else {
        try {
            Wedding::saveRsvp($rsvpName, $rsvpAttendance, $rsvpGuests, $rsvpMessage !== '' ? $rsvpMessage : null);
            $flash = 'terima kasih';
            $rsvpName = '';
            $rsvpMessage = '';
            $rsvpGuests = 1;
        } catch (PDOException) {
            $flash = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}

$messages = [];
$countHadir = 0;
$countTidak = 0;

try {
    $messages = Wedding::messages();
    $countHadir = Wedding::count('hadir');
    $countTidak = Wedding::count('tidak_hadir');
} catch (PDOException) {
}

$flashHadir = $flash === 'terima kasih';
$flashError = $flash !== null && !$flashHadir;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Pernikahan Arief & Nabila</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Great+Vibes&family=Inter:wght@300;400;500;600&family=Noto+Sans+Javanese&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #c9a227;
            --gold-dark: #a8851b;
            --ivory: #faf6ec;
            --cream: #f3ecdb;
            --ink: #2b2118;
            --brown: #6b5742;
            --serif: 'Cormorant Garamond', serif;
            --script: 'Great Vibes', cursive;
            --sans: 'Inter', sans-serif;
            --java: 'Noto Sans Javanese', serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--sans);
            color: var(--ink);
            background: var(--ivory);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .batik-bg {
            background-color: var(--ivory);
            background-image:
                radial-gradient(circle at 25% 20%, rgba(201, 162, 39, .10) 0, transparent 45%),
                radial-gradient(circle at 75% 80%, rgba(201, 162, 39, .10) 0, transparent 45%),
                url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='%23c9a227' stroke-opacity='0.08' stroke-width='1'%3E%3Ccircle cx='20' cy='20' r='9'/%3E%3Ccircle cx='60' cy='60' r='9'/%3E%3Ccircle cx='20' cy='20' r='4'/%3E%3Ccircle cx='60' cy='60' r='4'/%3E%3Cpath d='M20 20l9 9M20 20l-9 9M20 20l9-9M20 20l-9-9'/%3E%3C/g%3E%3C/svg%3E");
        }

        /* ---------- HERO ---------- */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 1.5rem;
        }

        .ornament { color: var(--gold); font-size: 1.6rem; letter-spacing: .4em; }

        .hero .java {
            font-family: var(--java);
            font-size: clamp(1.4rem, 3vw, 2.2rem);
            color: var(--brown);
            margin-bottom: 1rem;
            letter-spacing: .1em;
        }

        .hero .small {
            text-transform: uppercase;
            letter-spacing: .45em;
            font-size: .72rem;
            color: var(--brown);
            margin-bottom: 2.2rem;
        }

        .hero h1 {
            font-family: var(--script);
            font-size: clamp(3.4rem, 9vw, 7rem);
            font-weight: 400;
            color: var(--ink);
            line-height: 1.15;
        }

        .hero .ampersand {
            font-family: var(--serif);
            font-style: italic;
            font-size: clamp(2rem, 5vw, 3.4rem);
            color: var(--gold);
            display: block;
            margin: .4rem 0;
        }

        .hero .date {
            margin-top: 2.4rem;
            font-family: var(--serif);
            font-size: clamp(1.1rem, 2.4vw, 1.5rem);
            color: var(--ink);
            letter-spacing: .15em;
        }

        .hero .date span { color: var(--gold); }

        .scroll-hint {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: .7rem;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: var(--brown);
            animation: float 2.4s ease-in-out infinite;
        }

        @keyframes float { 0%, 100% { transform: translate(-50%, 0); } 50% { transform: translate(-50%, 8px); } }

        /* ---------- SECTIONS ---------- */
        section { padding: 5.5rem 1.5rem; }

        .container { max-width: 900px; margin: 0 auto; }

        .section-title {
            text-align: center;
            font-family: var(--script);
            font-size: clamp(2.4rem, 6vw, 3.6rem);
            font-weight: 400;
            color: var(--ink);
            margin-bottom: .4rem;
        }

        .section-sub {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .4em;
            font-size: .68rem;
            color: var(--gold-dark);
            margin-bottom: 2.5rem;
        }

        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .divider::before, .divider::after {
            content: '';
            height: 1px;
            width: 80px;
            background: linear-gradient(90deg, transparent, var(--gold));
        }

        .divider::after { background: linear-gradient(90deg, var(--gold), transparent); }

        .divider svg { color: var(--gold); }

        .fade {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .9s ease, transform .9s ease;
        }

        .fade.visible { opacity: 1; transform: none; }

        /* ---------- ASSALAMU ---------- */
        .assalamu {
            text-align: center;
            font-family: var(--serif);
            font-style: italic;
            font-size: clamp(1rem, 2vw, 1.25rem);
            color: var(--brown);
            max-width: 640px;
            margin: 0 auto;
        }

        .assalamu strong { color: var(--gold-dark); }

        /* ---------- COUPLE ---------- */
        .couple-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 2.5rem;
            text-align: center;
        }

        .couple-card { padding: 2rem 1rem; }

        .couple-card .java-name { font-family: var(--java); font-size: 1.1rem; color: var(--gold-dark); margin-bottom: .6rem; }

        .couple-card h3 {
            font-family: var(--serif);
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: .06em;
            margin-bottom: .8rem;
        }

        .couple-card p { font-size: .86rem; color: var(--brown); line-height: 1.8; }

        .couple-card .role {
            display: inline-block;
            margin-bottom: 1rem;
            padding: .3rem 1.1rem;
            border: 1px solid var(--gold);
            border-radius: 2rem;
            font-size: .7rem;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: var(--gold-dark);
        }

        .couple-card .parents { font-style: italic; font-family: var(--serif); font-size: .95rem; margin-top: .8rem; color: var(--ink); }

        /* ---------- QUOTE (UI/UX) ---------- */
        .quote-section { background: var(--cream); }

        .quote-card {
            max-width: 640px;
            margin: 0 auto;
            text-align: center;
            padding: 2.5rem 1.5rem;
            border: 1px solid rgba(201, 162, 39, .35);
            border-radius: 4px;
            position: relative;
        }

        .quote-card::before, .quote-card::after {
            content: '';
            position: absolute;
            width: 34px;
            height: 34px;
            border-color: var(--gold);
            border-style: solid;
        }

        .quote-card::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
        .quote-card::after { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }

        .quote-card .kicker {
            font-size: .7rem;
            letter-spacing: .35em;
            text-transform: uppercase;
            color: var(--gold-dark);
            margin-bottom: 1.2rem;
        }

        .quote-card blockquote {
            font-family: var(--serif);
            font-size: clamp(1.15rem, 2.6vw, 1.55rem);
            font-style: italic;
            line-height: 1.7;
        }

        .quote-card .jawa-quote {
            margin-top: 1.4rem;
            font-family: var(--java);
            font-size: 1.05rem;
            color: var(--brown);
        }

        .quote-card figcaption {
            margin-top: 1.2rem;
            font-size: .78rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--brown);
        }

        /* ---------- UI/UX KATA ---------- */
        .dev-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.2rem;
            margin-top: 2.5rem;
        }

        .dev-card {
            background: var(--ivory);
            border: 1px solid rgba(201, 162, 39, .25);
            border-radius: 6px;
            padding: 1.4rem 1.3rem;
            text-align: center;
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .dev-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(43, 33, 24, .08); }

        .dev-card .tag {
            font-size: .62rem;
            letter-spacing: .28em;
            text-transform: uppercase;
            color: var(--gold-dark);
            margin-bottom: .7rem;
        }

        .dev-card p { font-size: .88rem; color: var(--brown); font-family: var(--serif); font-size: 1rem; font-style: italic; }

        /* ---------- COUNTDOWN ---------- */
        .countdown {
            display: flex;
            justify-content: center;
            gap: clamp(1rem, 4vw, 2.5rem);
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .countdown .unit { text-align: center; min-width: 74px; }

        .countdown .num {
            font-family: var(--serif);
            font-size: clamp(2.2rem, 6vw, 3.2rem);
            font-weight: 600;
            color: var(--ink);
            line-height: 1;
            display: block;
        }

        .countdown .label {
            font-size: .64rem;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: var(--brown);
            margin-top: .5rem;
            display: block;
        }

        .countdown .sep { color: var(--gold); font-family: var(--serif); font-size: 2rem; align-self: flex-start; margin-top: .6rem; }

        /* ---------- EVENT TIMELINE ---------- */
        .event-list { max-width: 560px; margin: 0 auto; position: relative; }

        .event-list::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 1px;
            background: rgba(201, 162, 39, .45);
        }

        .event {
            position: relative;
            padding: 0 0 2.6rem 3.4rem;
        }

        .event:last-child { padding-bottom: 0; }

        .event::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 6px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: var(--ivory);
            border: 2px solid var(--gold);
        }

        .event h4 {
            font-family: var(--serif);
            font-size: 1.45rem;
            font-weight: 600;
            letter-spacing: .04em;
        }

        .event .java-event { font-family: var(--java); font-size: .92rem; color: var(--gold-dark); margin-bottom: .5rem; }

        .event .date { font-size: .8rem; color: var(--gold-dark); letter-spacing: .12em; text-transform: uppercase; margin-bottom: .5rem; }

        .event p { font-size: .86rem; color: var(--brown); max-width: 460px; }

        /* ---------- FORM ---------- */
        .rsvp { background: var(--cream); }

        .form-grid { max-width: 560px; margin: 0 auto; display: grid; gap: 1.1rem; }

        .form-grid label {
            display: block;
            font-size: .7rem;
            letter-spacing: .25em;
            text-transform: uppercase;
            color: var(--brown);
            margin-bottom: .35rem;
        }

        .form-grid input, .form-grid select, .form-grid textarea {
            width: 100%;
            padding: .8rem 1rem;
            border: 1px solid rgba(107, 87, 66, .35);
            border-radius: 4px;
            background: var(--ivory);
            font-family: var(--sans);
            font-size: .92rem;
            color: var(--ink);
            transition: border-color .25s ease, box-shadow .25s ease;
        }

        .form-grid input:focus, .form-grid select:focus, .form-grid textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201, 162, 39, .15);
        }

        .form-grid textarea { resize: vertical; min-height: 90px; }

        .btn {
            margin-top: .6rem;
            padding: .95rem 1.6rem;
            background: var(--gold);
            color: var(--ivory);
            border: none;
            border-radius: 4px;
            font-size: .8rem;
            letter-spacing: .3em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background .25s ease, transform .25s ease;
        }

        .btn:hover { background: var(--gold-dark); transform: translateY(-2px); }

        .flash {
            max-width: 560px;
            margin: 0 auto 1.4rem;
            padding: .9rem 1.2rem;
            border-radius: 4px;
            font-size: .88rem;
            text-align: center;
        }

        .flash.ok { background: #eef7ee; color: #2f6b2f; border: 1px solid #bfe0bf; }
        .flash.err { background: #fdf0ef; color: #8c3a32; border: 1px solid #ecc9c5; }

        .stats { text-align: center; font-size: .8rem; color: var(--brown); margin-bottom: 2rem; letter-spacing: .08em; }

        .stats b { color: var(--gold-dark); }

        /* ---------- GUESTBOOK ---------- */
        .guestbook { max-width: 560px; margin: 0 auto; display: grid; gap: 1rem; }

        .guest {
            background: var(--ivory);
            border: 1px solid rgba(201, 162, 39, .2);
            border-radius: 6px;
            padding: 1.1rem 1.3rem;
        }

        .guest .head { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; margin-bottom: .4rem; }

        .guest .head strong { font-family: var(--serif); font-size: 1.05rem; }

        .guest .head .chip {
            font-size: .62rem;
            letter-spacing: .18em;
            text-transform: uppercase;
            padding: .18rem .6rem;
            border-radius: 2rem;
            border: 1px solid var(--gold);
            color: var(--gold-dark);
            white-space: nowrap;
        }

        .guest p { font-size: .86rem; color: var(--brown); font-style: italic; }

        .guest .when { font-size: .7rem; color: #a08f7c; margin-top: .5rem; letter-spacing: .05em; }

        .empty { text-align: center; color: var(--brown); font-style: italic; font-family: var(--serif); font-size: 1.1rem; }

        /* ---------- FOOTER ---------- */
        footer {
            text-align: center;
            padding: 3.5rem 1.5rem 2.5rem;
            background: var(--ink);
            color: #cfc3b2;
        }

        footer .java { font-family: var(--java); font-size: 1.1rem; color: var(--gold); margin-bottom: 1rem; }

        footer .names { font-family: var(--script); font-size: 2.2rem; color: var(--ivory); margin-bottom: 1rem; }

        footer p { font-size: .78rem; letter-spacing: .12em; line-height: 2; }

        .reveal-fast { transition-delay: .12s; }
        .reveal-mid { transition-delay: .24s; }
        .reveal-late { transition-delay: .36s; }

        @media (max-width: 480px) {
            .countdown .sep { display: none; }
            .couple-grid { gap: 1rem; }
        }
    </style>
</head>
<body>

<!-- ================= HERO ================= -->
<header class="hero batik-bg">
    <div class="container">
        <div class="ornament">✦ ✦ ✦</div>
        <div class="java">ꦄꦫꦶꦥ꦳ ꦭꦤ꧀ ꦟꦧꦶꦭ</div>
        <div class="small">The Wedding of</div>
        <h1>Arief <span class="ampersand">&</span> Nabila</h1>
        <div class="date">Minggu, <span>21 Februari 2027</span> · Jam 09.00 WIB</div>
    </div>
    <div class="scroll-hint">Scroll</div>
</header>

<!-- ================= ASSALAMU ================= -->
<section class="batik-bg">
    <div class="container">
        <div class="assalamu fade">
            <div class="ornament" style="margin-bottom:1rem;">✧</div>
            <p>Bismillahirrahmanirrahim</p>
            <p style="margin-top:1rem;">
                Dengan memohon rahmat dan ridho Allah SWT, kami bermaksud menyelenggarakan pernikahan putra-putri kami:
            </p>
        </div>
    </div>
</section>

<!-- ================= COUPLE ================= -->
<section class="batik-bg" id="pengantin">
    <div class="container">
        <div class="divider fade">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 3l2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5z"/></svg>
        </div>
        <h2 class="section-title fade">Mempelai</h2>
        <div class="section-sub fade">Arief & Nabila</div>

        <div class="couple-grid">
            <div class="couple-card fade reveal-fast">
                <div class="java-name">ꦄꦫꦶꦥ꦳</div>
                <h3>ARIF ADI PRASETYO</h3>
                <span class="role">Putra Pertama</span>
                <p>Bapak Suryanto & Ibu Sri Rahayu<br>Bekasi, Jawa Barat</p>
                <div class="parents">"Anak lanang kang tansah jujur lan setya"</div>
            </div>

            <div class="couple-card fade reveal-mid">
                <div class="java-name">ꦟꦧꦶꦭ</div>
                <h3>NABILA PUTRI RAMADHANI</h3>
                <span class="role">Putri Kedua</span>
                <p>Bapak H. Ahmad Fauzi & Ibu Hj. Endang Lestari<br>Solo, Jawa Tengah</p>
                <div class="parents">"Wanita kang tansah alus budi lan adil basane"</div>
            </div>
        </div>
    </div>
</section>

<!-- ================= QUOTE ADAT ================= -->
<section class="quote-section">
    <div class="container">
        <div class="quote-card fade">
            <div class="kicker">Pangestu · Doa Restu</div>
            <blockquote>
                "Witing tresna jalaran saka kulina.<br>Tresna kang sejati lahir saka kebiasaan kang becik,<br>lan sabar kang tanpa wates."
            </blockquote>
            <div class="jawa-quote">ꦮꦶꦠꦶꦁꦠꦿꦺꦱ꧀ꦤ ꦗꦭꦫꦤ꧀ ꦱꦏ ꦏꦸꦭꦶꦤ</div>
            <figcaption>— Pitutur Jawa —</figcaption>
        </div>
    </div>
</section>

<!-- ================= KATA UI/UX ================= -->
<section class="batik-bg">
    <div class="container">
        <div class="divider fade">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 3l2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5z"/></svg>
        </div>
        <h2 class="section-title fade">Kata-kata Kami</h2>
        <div class="section-sub fade">Dari dua hati yang "merge"</div>

        <div class="dev-cards fade">
            <div class="dev-card">
                <div class="tag">User Flow</div>
                <p>"Seperti design yang dipikirkan matang, kami menyusun langkah hidup berdua — dari wireframe sederhana menjadi realita yang indah."</p>
            </div>
            <div class="dev-card">
                <div class="tag">Responsive</div>
                <p>"Cinta kami bukan hanya desktop view. Di layar kecil, di tengah badai, kami tetap responsive dan saling menyesuaikan."</p>
            </div>
            <div class="dev-card">
                <div class="tag">No Bug</div>
                <p>"Sebuah code tanpa bug pun masih bisa diperbaiki. Tapi janji kami — 'saklawase bebarengan' — itu yang final, tanpa revisi."</p>
            </div>
            <div class="dev-card">
                <div class="tag">Deploy</div>
                <p>"Menyatu dengan ikhlas dalam 'sakinah, mawaddah, warahmah' — inilah deploy paling istimewa: sehidup semati, selamanya online."</p>
            </div>
        </div>
    </div>
</section>

<!-- ================= COUNTDOWN ================= -->
<section class="batik-bg" id="countdown">
    <div class="container">
        <div class="divider fade">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 3l2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5z"/></svg>
        </div>
        <h2 class="section-title fade">Hari Bahagia</h2>
        <div class="section-sub fade">Menunggu hari yang dinanti</div>

        <div class="countdown fade" id="countdown">
            <div class="unit"><span class="num" id="cd-days">0</span><span class="label">Hari</span></div>
            <span class="sep">·</span>
            <div class="unit"><span class="num" id="cd-hours">0</span><span class="label">Jam</span></div>
            <span class="sep">·</span>
            <div class="unit"><span class="num" id="cd-minutes">0</span><span class="label">Menit</span></div>
            <span class="sep">·</span>
            <div class="unit"><span class="num" id="cd-seconds">0</span><span class="label">Detik</span></div>
        </div>
    </div>
</section>

<!-- ================= RANGKAIAN ACARA ================= -->
<section class="batik-bg" id="acara">
    <div class="container">
        <div class="divider fade">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 3l2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5z"/></svg>
        </div>
        <h2 class="section-title fade">Rangkaian Adat Jawa</h2>
        <div class="section-sub fade">Lestarikan budaya warisan leluhur</div>

        <div class="event-list fade">
            <div class="event">
                <div class="java-event">ꦱꦶꦫꦩ꧀ꦩꦤ꧀</div>
                <h4>Siraman</h4>
                <div class="date">Jumat, 19 Februari 2027 · 10.00 WIB</div>
                <p>Penyucian diri calon pengantin dengan air kembang setaman, sebagai simbol pembersihan lahir dan batin sebelum menempuh hidup baru.</p>
            </div>
            <div class="event">
                <div class="java-event">ꦩꦶꦢꦺꦴꦢꦉꦤꦶ</div>
                <h4>Midodareni</h4>
                <div class="date">Sabtu, 20 Februari 2027 · 19.00 WIB</div>
                <p>Malam penuh doa dan restu, pengantin wanita berpuasa doa semalaman ditemani keluarga — pertanda kesucian menyambut besok yang suci.</p>
            </div>
            <div class="event">
                <div class="java-event">ꦲꦏꦢ꧀ ꦤꦶꦏꦃ</div>
                <h4>Akad Nikah</h4>
                <div class="date">Minggu, 21 Februari 2027 · 09.00 WIB</div>
                <p>Ijab kabul di hadapan penghulu, saksi, dan keluarga — ikrar suci yang mengikat dua jiwa menjadi satu keluarga.</p>
            </div>
            <div class="event">
                <div class="java-event">ꦔꦸꦤ꧀ꦝꦸꦃ ꦩꦤ꧀ꦠꦸ</div>
                <h4>Ngunduh Mantu & Resepsi</h4>
                <div class="date">Minggu, 21 Februari 2027 · 11.00 WIB</div>
                <p>Pesta penuh suka cita menyambut kedua mempelai, ditutup dengan tradisi sungkeman — mohon doa restu kepada kedua orang tua.</p>
            </div>
        </div>
    </div>
</section>

<!-- ================= RSVP ================= -->
<section class="rsvp" id="rsvp">
    <div class="container">
        <div class="divider fade">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 3l2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5z"/></svg>
        </div>
        <h2 class="section-title fade">Konfirmasi Kehadiran</h2>
        <div class="section-sub fade">RSVP</div>

        <?php if ($flashHadir): ?>
            <div class="flash ok fade visible">Matur nuwun! Konfirmasi Anda telah kami terima. 🏵️</div>
        <?php elseif ($flashError): ?>
            <div class="flash err fade visible"><?= htmlspecialchars($flash ?? '', ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="stats fade visible">
            <?= (int) $countHadir ?> tamu konfirmasi hadir · <?= (int) $countTidak ?> mengundurkan diri
        </div>

        <form method="post" action="/wedding.php#rsvp" class="form-grid fade">
            <div>
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($rsvpName, ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required>
            </div>
            <div>
                <label for="attendance">Kehadiran</label>
                <select id="attendance" name="attendance">
                    <option value="hadir" <?= $rsvpAttendance === 'hadir' ? 'selected' : '' ?>>Insya Allah Hadir</option>
                    <option value="tidak_hadir" <?= $rsvpAttendance === 'tidak_hadir' ? 'selected' : '' ?>>Tidak Bisa Hadir</option>
                </select>
            </div>
            <div>
                <label for="guests">Jumlah Tamu (termasuk Anda)</label>
                <input type="number" id="guests" name="guests" min="1" max="10" value="<?= $rsvpGuests ?>" required>
            </div>
            <div>
                <label for="message">Doa & Ucapan</label>
                <textarea id="message" name="message" maxlength="1000" placeholder="Tulis doa dan ucapan untuk kedua mempelai..."><?= htmlspecialchars($rsvpMessage, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <button type="submit" class="btn">Kirim Konfirmasi</button>
        </form>
    </div>
</section>

<!-- ================= GUESTBOOK ================= -->
<section class="batik-bg" id="ucapan">
    <div class="container">
        <div class="divider fade">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 3l2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5z"/></svg>
        </div>
        <h2 class="section-title fade">Doa & Ucapan</h2>
        <div class="section-sub fade">Curhat digital untuk pengantin</div>

        <div class="guestbook">
            <?php if ($messages === []): ?>
                <div class="empty fade">Belum ada ucapan. Jadilah yang pertama berdoa untuk kami. 🥰</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="guest fade">
                        <div class="head">
                            <strong><?= htmlspecialchars($msg['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="chip"><?= $msg['attendance'] === 'hadir' ? 'Hadir' : 'Tidak Hadir' ?></span>
                        </div>
                        <?php if ($msg['message'] !== null && $msg['message'] !== ''): ?>
                            <p>"<?= htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8') ?>"</p>
                        <?php endif; ?>
                        <div class="when"><?= htmlspecialchars(date('d M Y, H:i', strtotime($msg['created_at'])), ENT_QUOTES, 'UTF-8') ?> · <?= (int) $msg['guests'] ?> tamu</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================= FOOTER ================= -->
<footer>
    <div class="java">ꦱꦸꦒꦼꦁ ꦱꦸꦒꦼꦁ ꦠꦁꦒꦶꦁ ꦲꦏ꧀ꦱꦫ</div>
    <div class="names">Arief & Nabila</div>
    <p>Merupakan suatu kehormatan dan kebahagiaan bagi kami<br>apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
    <p style="margin-top:1.2rem; color:#8d7f6c;">"Mugi tansah guyub rukun, manggih rahayu" — 🙏</p>
</footer>

<script>
    const WEDDING_DATE = new Date('2027-02-21T09:00:00+07:00');

    function updateCountdown() {
        const diff = WEDDING_DATE - new Date();
        const el = (id) => document.getElementById(id);
        if (diff <= 0) {
            el('cd-days').textContent = '0';
            el('cd-hours').textContent = '0';
            el('cd-minutes').textContent = '0';
            el('cd-seconds').textContent = '0';
            return;
        }
        el('cd-days').textContent = Math.floor(diff / 86400000);
        el('cd-hours').textContent = Math.floor(diff / 3600000) % 24;
        el('cd-minutes').textContent = Math.floor(diff / 60000) % 60;
        el('cd-seconds').textContent = Math.floor(diff / 1000) % 60;
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('.fade').forEach((el) => observer.observe(el));
</script>
</body>
</html>
