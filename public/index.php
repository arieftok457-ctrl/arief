<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/Wedding.php';

$flashOk = false;
$flashError = null;
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
        $flashError = 'Asma (nama) wajib diisi — maksimal 100 aksara.';
    } elseif (mb_strlen($rsvpMessage) > 1000) {
        $flashError = 'Pangestu (pesan) kegedhen — maksimal 1000 aksara.';
    } else {
        try {
            Wedding::saveRsvp($rsvpName, $rsvpAttendance, $rsvpGuests, $rsvpMessage !== '' ? $rsvpMessage : null);
            $flashOk = true;
            $rsvpName = '';
            $rsvpMessage = '';
            $rsvpGuests = 1;
        } catch (PDOException) {
            $flashError = 'Sedhih, ana kendala sistem. Mangga dipun cobi malih.';
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Undangan Pernikahan Nabila & Arief</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Great+Vibes&family=Inter:wght@300;400;500;600&family=Noto+Sans+Javanese:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --emas: #c9a227;
            --emas-terang: #e0c468;
            --emas-gelap: #8f6f14;
            --gading: #f7f1e1;
            --krim: #efe6cd;
            --tinta: #2b2015;
            --coklat: #6b543c;
            --toska: #2c5e50;
            --serif: 'Cormorant Garamond', serif;
            --script: 'Great Vibes', cursive;
            --sans: 'Inter', sans-serif;
            --java: 'Noto Sans Javanese', serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--sans);
            color: var(--tinta);
            background: var(--gading);
            line-height: 1.65;
            overflow-x: hidden;
        }

        /* ===== Pola Batik Kawung ===== */
        .batik {
            background-color: var(--gading);
            background-image: url("data:image/svg+xml,%3Csvg width='120' height='120' viewBox='0 0 120 120' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='%23c9a227' stroke-opacity='0.10' stroke-width='1.2'%3E%3Cellipse cx='30' cy='30' rx='14' ry='10' transform='rotate(45 30 30)'/%3E%3Cellipse cx='90' cy='90' rx='14' ry='10' transform='rotate(45 90 90)'/%3E%3Cellipse cx='30' cy='30' rx='5' ry='3.5' transform='rotate(45 30 30)'/%3E%3Cellipse cx='90' cy='90' rx='5' ry='3.5' transform='rotate(45 90 90)'/%3E%3Ccircle cx='30' cy='30' r='1.8'/%3E%3Ccircle cx='90' cy='90' r='1.8'/%3E%3Cpath d='M30 2v16M30 42v16M2 30h16M42 30h16M30 30l11 11M30 30l-11 11M30 30l11-11M30 30l-11-11'/%3E%3C/g%3E%3C/svg%3E");
        }

        .ornament {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .9rem;
            color: var(--emas);
            margin-bottom: 1rem;
        }

        .ornament::before, .ornament::after {
            content: '';
            height: 1px;
            width: 90px;
            background: linear-gradient(90deg, transparent, var(--emas));
        }

        .ornament::after { background: linear-gradient(90deg, var(--emas), transparent); }

        .ornament svg { flex-shrink: 0; }

        /* ===== HERO ===== */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 1.5rem 6rem;
        }

        .hero::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 110px;
            background:
                radial-gradient(ellipse 60% 100% at 0% 100%, rgba(201,162,39,.28), transparent 60%),
                radial-gradient(ellipse 60% 100% at 100% 100%, rgba(201,162,39,.28), transparent 60%);
            pointer-events: none;
        }

        .hero .java-hero {
            font-family: var(--java);
            font-size: clamp(1.3rem, 2.6vw, 1.9rem);
            color: var(--coklat);
            letter-spacing: .08em;
            margin-bottom: .8rem;
        }

        .hero .kicker {
            font-size: .7rem;
            letter-spacing: .5em;
            text-transform: uppercase;
            color: var(--emas-gelap);
            margin-bottom: 1.6rem;
        }

        .hero h1 {
            font-family: var(--script);
            font-weight: 400;
            font-size: clamp(3.2rem, 9vw, 6.5rem);
            line-height: 1.12;
            color: var(--tinta);
        }

        .hero h1 .and {
            font-family: var(--serif);
            font-style: italic;
            font-size: clamp(1.8rem, 4.5vw, 3rem);
            color: var(--emas);
            display: block;
            margin: .3rem 0;
        }

        .hero .tgl {
            margin-top: 2.2rem;
            font-family: var(--serif);
            font-size: clamp(1.1rem, 2.4vw, 1.5rem);
            letter-spacing: .12em;
            color: var(--tinta);
        }

        .hero .tgl span { color: var(--emas-gelap); }

        .hero .hadoh {
            margin-top: 1.1rem;
            font-family: var(--java);
            font-size: .95rem;
            color: var(--coklat);
        }

        .scroll-hint {
            position: absolute;
            bottom: 2.2rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: .66rem;
            letter-spacing: .32em;
            text-transform: uppercase;
            color: var(--coklat);
            animation: nglayang 2.2s ease-in-out infinite;
        }

        @keyframes nglayang {
            0%, 100% { transform: translate(-50%, 0); }
            50% { transform: translate(-50%, 9px); }
        }

        /* ===== SEKAR ===== */
        section { padding: 5rem 1.5rem; }

        .container { max-width: 900px; margin: 0 auto; }

        .judul {
            text-align: center;
            font-family: var(--script);
            font-weight: 400;
            font-size: clamp(2.4rem, 6vw, 3.6rem);
            color: var(--tinta);
        }

        .judul-sub {
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .42em;
            font-size: .66rem;
            color: var(--emas-gelap);
            margin-bottom: 2.6rem;
        }

        .fade {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity .9s ease, transform .9s ease;
        }

        .fade.katon { opacity: 1; transform: none; }

        .tunda-1 { transition-delay: .12s; }
        .tunda-2 { transition-delay: .24s; }
        .tunda-3 { transition-delay: .36s; }

        /* ===== BISMILLAH ===== */
        .bismillah {
            text-align: center;
            max-width: 660px;
            margin: 0 auto;
            font-family: var(--serif);
            font-style: italic;
            font-size: clamp(1.02rem, 2.1vw, 1.28rem);
            color: var(--coklat);
            line-height: 2;
        }

        .bismillah .arab { font-family: var(--serif); font-style: normal; color: var(--tinta); }

        /* ===== MEMPELAI ===== */
        .mempelai-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 2.2rem;
            text-align: center;
        }

        .mempelai { padding: 2rem .8rem; position: relative; }

        .mempelai .java-nama { font-family: var(--java); font-size: 1.08rem; color: var(--emas-gelap); margin-bottom: .5rem; }

        .mempelai h3 {
            font-family: var(--serif);
            font-size: 1.85rem;
            font-weight: 600;
            letter-spacing: .05em;
            margin-bottom: .9rem;
        }

        .mempelai .putri {
            display: inline-block;
            padding: .28rem 1.15rem;
            border: 1px solid var(--emas);
            border-radius: 3rem;
            font-size: .66rem;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: var(--emas-gelap);
            margin-bottom: 1rem;
        }

        .mempelai p { font-size: .86rem; color: var(--coklat); line-height: 1.9; }

        .mempelai .wong-tua { margin-top: .9rem; font-family: var(--serif); font-style: italic; font-size: .98rem; color: var(--tinta); }

        .mempelai .sesanti { font-family: var(--java); font-size: .88rem; color: var(--toska); margin-top: .8rem; }

        .hiasan-putri {
            display: flex;
            justify-content: center;
            gap: 2.4rem;
            margin: 3rem auto 0;
            max-width: 520px;
            color: var(--emas);
        }

        .hiasan-putri svg { opacity: .85; }

        /* ===== PITUTUR ===== */
        .pitutur-seksi { background: var(--krim); }

        .pitutur-card {
            max-width: 680px;
            margin: 0 auto;
            text-align: center;
            padding: 2.8rem 1.6rem;
            border: 1px solid rgba(201, 162, 39, .4);
            border-radius: 3px;
            position: relative;
            background: var(--gading);
        }

        .pitutur-card::before, .pitutur-card::after {
            content: '';
            position: absolute;
            width: 38px;
            height: 38px;
            border-color: var(--emas);
            border-style: solid;
        }

        .pitutur-card::before { top: -2px; left: -2px; border-width: 2px 0 0 2px; }
        .pitutur-card::after { bottom: -2px; right: -2px; border-width: 0 2px 2px 0; }

        .pitutur-card .label {
            font-size: .66rem;
            letter-spacing: .38em;
            text-transform: uppercase;
            color: var(--emas-gelap);
            margin-bottom: 1.1rem;
        }

        .pitutur-card blockquote {
            font-family: var(--serif);
            font-size: clamp(1.18rem, 2.7vw, 1.6rem);
            font-style: italic;
            line-height: 1.75;
        }

        .pitutur-card .jawa {
            margin-top: 1.2rem;
            font-family: var(--java);
            font-size: 1.06rem;
            color: var(--coklat);
        }

        .pitutur-card figcaption {
            margin-top: 1.1rem;
            font-size: .76rem;
            letter-spacing: .22em;
            text-transform: uppercase;
            color: var(--coklat);
        }

        /* ===== SESANTI ===== */
        .sesanti-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.1rem;
            margin-top: 2.4rem;
        }

        .sesanti-card {
            background: var(--gading);
            border: 1px solid rgba(201, 162, 39, .3);
            border-radius: 6px;
            padding: 1.5rem 1.3rem;
            text-align: center;
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .sesanti-card:hover { transform: translateY(-5px); box-shadow: 0 14px 30px rgba(43, 32, 21, .09); }

        .sesanti-card .tag {
            font-size: .6rem;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: var(--emas-gelap);
            margin-bottom: .8rem;
        }

        .sesanti-card .jawa {
            font-family: var(--java);
            font-size: .95rem;
            color: var(--toska);
            margin-bottom: .5rem;
        }

        .sesanti-card p { font-family: var(--serif); font-style: italic; font-size: 1.02rem; color: var(--coklat); line-height: 1.7; }

        /* ===== COUNTDOWN ===== */
        .countdown {
            display: flex;
            justify-content: center;
            gap: clamp(.9rem, 3.5vw, 2.2rem);
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .countdown .unit { text-align: center; min-width: 72px; }

        .countdown .angka {
            font-family: var(--serif);
            font-size: clamp(2.1rem, 5.5vw, 3.1rem);
            font-weight: 600;
            line-height: 1;
            color: var(--tinta);
            display: block;
        }

        .countdown .label {
            font-size: .62rem;
            letter-spacing: .3em;
            text-transform: uppercase;
            color: var(--coklat);
            margin-top: .55rem;
            display: block;
        }

        .countdown .pemisah { color: var(--emas); font-family: var(--serif); font-size: 1.9rem; align-self: flex-start; margin-top: .5rem; }

        /* ===== RANGKAIAN ADAT ===== */
        .rangkai-seksi { background: var(--krim); }

        .timeline { max-width: 580px; margin: 0 auto; position: relative; }

        .timeline::before {
            content: '';
            position: absolute;
            left: 17px;
            top: 4px;
            bottom: 4px;
            width: 1.5px;
            background: linear-gradient(180deg, var(--emas), rgba(201,162,39,.15));
        }

        .acara { position: relative; padding: 0 0 2.7rem 3.6rem; }

        .acara:last-child { padding-bottom: 0; }

        .acara::before {
            content: '';
            position: absolute;
            left: 9px;
            top: 7px;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            background: var(--gading);
            border: 2px solid var(--emas);
            box-shadow: 0 0 0 4px rgba(201,162,39,.15);
        }

        .acara .java-acara { font-family: var(--java); font-size: .95rem; color: var(--emas-gelap); margin-bottom: .35rem; }

        .acara h4 { font-family: var(--serif); font-size: 1.5rem; font-weight: 600; letter-spacing: .03em; }

        .acara .waktu { font-size: .78rem; color: var(--emas-gelap); letter-spacing: .14em; text-transform: uppercase; margin: .4rem 0; }

        .acara p { font-size: .87rem; color: var(--coklat); max-width: 470px; }

        /* ===== RSVP ===== */
        .form-grid { max-width: 560px; margin: 0 auto; display: grid; gap: 1.1rem; }

        .form-grid label {
            display: block;
            font-size: .68rem;
            letter-spacing: .26em;
            text-transform: uppercase;
            color: var(--coklat);
            margin-bottom: .35rem;
        }

        .form-grid input, .form-grid select, .form-grid textarea {
            width: 100%;
            padding: .82rem 1rem;
            border: 1px solid rgba(107, 84, 60, .38);
            border-radius: 4px;
            background: var(--gading);
            font-family: var(--sans);
            font-size: .92rem;
            color: var(--tinta);
            transition: border-color .25s ease, box-shadow .25s ease;
        }

        .form-grid input:focus, .form-grid select:focus, .form-grid textarea:focus {
            outline: none;
            border-color: var(--emas);
            box-shadow: 0 0 0 3px rgba(201, 162, 39, .16);
        }

        .form-grid textarea { resize: vertical; min-height: 92px; }

        .btn {
            margin-top: .5rem;
            padding: .95rem 1.7rem;
            background: var(--emas);
            color: #fff8e7;
            border: none;
            border-radius: 4px;
            font-size: .78rem;
            letter-spacing: .3em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background .25s ease, transform .25s ease;
        }

        .btn:hover { background: var(--emas-gelap); transform: translateY(-2px); }

        .flash {
            max-width: 560px;
            margin: 0 auto 1.4rem;
            padding: .9rem 1.2rem;
            border-radius: 4px;
            font-size: .88rem;
            text-align: center;
        }

        .flash.suwun { background: #eef7ee; color: #2f6b2f; border: 1px solid #bfe0bf; }
        .flash.larap { background: #fdf0ef; color: #8c3a32; border: 1px solid #ecc9c5; }

        .stats { text-align: center; font-size: .8rem; color: var(--coklat); margin-bottom: 2rem; letter-spacing: .08em; }

        .stats b { color: var(--emas-gelap); }

        /* ===== PANGGESTU ===== */
        .pangestu { max-width: 560px; margin: 0 auto; display: grid; gap: 1rem; }

        .pesan {
            background: var(--gading);
            border: 1px solid rgba(201, 162, 39, .24);
            border-radius: 6px;
            padding: 1.1rem 1.3rem;
        }

        .pesan .kepala { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; margin-bottom: .4rem; }

        .pesan .kepala strong { font-family: var(--serif); font-size: 1.06rem; }

        .pesan .kepala .chip {
            font-size: .6rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            padding: .18rem .6rem;
            border-radius: 2rem;
            border: 1px solid var(--emas);
            color: var(--emas-gelap);
            white-space: nowrap;
        }

        .pesan p { font-size: .87rem; color: var(--coklat); font-style: italic; }

        .pesan .kala { font-size: .7rem; color: #a08f7c; margin-top: .5rem; letter-spacing: .05em; }

        .kosong { text-align: center; color: var(--coklat); font-style: italic; font-family: var(--serif); font-size: 1.12rem; }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 3.6rem 1.5rem 2.6rem;
            background: var(--tinta);
            color: #cfc0ab;
        }

        footer .java { font-family: var(--java); font-size: 1.15rem; color: var(--emas); margin-bottom: 1rem; }

        footer .nama { font-family: var(--script); font-size: 2.3rem; color: var(--gading); margin-bottom: 1rem; }

        footer p { font-size: .78rem; letter-spacing: .12em; line-height: 2.1; }

        footer .tutup-jawa { margin-top: 1.2rem; color: #8d7f6c; }

        @media (max-width: 480px) {
            .countdown .pemisah { display: none; }
            .mempelai-grid { gap: 1rem; }
        }
    </style>
</head>
<body>

<!-- ================= HERO ================= -->
<header class="hero batik">
    <div class="java-hero">ꦤꦧꦶꦭ ꦭꦤ꧀ ꦄꦫꦶꦥ꦳</div>
    <div class="kicker">Undhuh Undhuh · The Wedding</div>
    <h1>Nabila <span class="and">&</span> Arief</h1>
    <div class="tgl">Minggu, <span>21 Februari 2027</span> · Jam 09.00 WIB</div>
    <div class="hadoh">"Sugeng Rawuh" — ꦱꦸꦒꦼꦁ ꦫꦮꦸꦃ</div>
    <div class="scroll-hint">Scroll</div>
</header>

<!-- ================= BISMILLAH ================= -->
<section class="batik">
    <div class="container">
        <div class="ornament fade">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M12 2l2.4 6.2L21 11l-6.6 2.8L12 20l-2.4-6.2L3 11l6.6-2.8z"/></svg>
        </div>
        <div class="bismillah fade">
            <div class="arab">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</div>
            <p style="margin-top:1.1rem;">
                Kanthi rahayu sarta pangestu saking Gusti Ingkang Maha Welas,<br>
                kawula ngaturaken undhuh-undhuh, kagem putra lan putri kawula:
            </p>
        </div>
    </div>
</section>

<!-- ================= MEMPELAI ================= -->
<section class="batik" id="mempelai">
    <div class="container">
        <div class="ornament fade">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M12 2l2.4 6.2L21 11l-6.6 2.8L12 20l-2.4-6.2L3 11l6.6-2.8z"/></svg>
        </div>
        <h2 class="judul fade">Calon Mempelai</h2>
        <div class="judul-sub fade">Nabila & Arief</div>

        <div class="mempelai-grid">
            <div class="mempelai fade tunda-1">
                <div class="java-nama">ꦟꦧꦶꦭ</div>
                <h3>NABILA PUTRI RAMADHANI</h3>
                <span class="putri">Putri Kawiwitan</span>
                <p>Putri saking Bapak H. Ahmad Fauzi<br>& Ibu Hj. Endang Lestari<br>Solo, Jawa Tengah</p>
                <div class="wong-tua">"Rara kang tansah luhur budi, alus basane"</div>
                <div class="sesanti">ꦟꦧꦶꦭ ꦥꦸꦠꦿꦶ ꦫꦩꦝꦤꦶ</div>
            </div>

            <div class="mempelai fade tunda-2">
                <div class="java-nama">ꦄꦫꦶꦥ꦳</div>
                <h3>ARIF ADI PRASETYO</h3>
                <span class="putri">Putra Kapisan</span>
                <p>Putra saking Bapak Suryanto<br>& Ibu Sri Rahayu<br>Bekasi, Jawa Barat</p>
                <div class="wong-tua">"Kakung kang jujur lan setya tuhu"</div>
                <div class="sesanti">ꦄꦫꦶꦥ꦳ ꦄꦢꦶ ꦥꦿꦱꦺꦠꦾ</div>
            </div>
        </div>

        <div class="hiasan-putri fade">
            <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M12 2C7 7 4 10 4 14a8 8 0 0 0 16 0c0-4-3-7-8-12z"/><path d="M12 22c-1.5-2-2.5-4-2.5-6 0-2 1-3.5 2.5-5 1.5 1.5 2.5 3 2.5 5 0 2-1 4-2.5 6z"/></svg>
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M12 2l2.4 6.2L21 11l-6.6 2.8L12 20l-2.4-6.2L3 11l6.6-2.8z"/></svg>
            <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M12 2C7 7 4 10 4 14a8 8 0 0 0 16 0c0-4-3-7-8-12z"/><path d="M12 22c-1.5-2-2.5-4-2.5-6 0-2 1-3.5 2.5-5 1.5 1.5 2.5 3 2.5 5 0 2-1 4-2.5 6z"/></svg>
        </div>
    </div>
</section>

<!-- ================= PITUTUR JAWA ================= -->
<section class="pitutur-seksi">
    <div class="container">
        <div class="pitutur-card fade">
            <div class="label">Pitutur Jawa · Sesanti</div>
            <blockquote>
                "Witing tresna jalaran saka kulina.<br>Tresna sejati lahir saka kebiasaan kang becik,<br>sinartan sabar lan ikhlas."
            </blockquote>
            <div class="jawa">ꦮꦶꦠꦶꦁ ꦠꦿꦺꦱ꧀ꦤ ꦗꦭꦫꦤ꧀ ꦱꦏ ꦏꦸꦭꦶꦤ</div>
            <figcaption>— Pitutur Luhur —</figcaption>
        </div>
    </div>
</section>

<!-- ================= SESANTI PASANGAN ================= -->
<section class="batik">
    <div class="container">
        <div class="ornament fade">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M12 2l2.4 6.2L21 11l-6.6 2.8L12 20l-2.4-6.2L3 11l6.6-2.8z"/></svg>
        </div>
        <h2 class="judul fade">Sesanti Kawula</h2>
        <div class="judul-sub fade">Kata-kata saking penganten</div>

        <div class="sesanti-grid fade">
            <div class="sesanti-card">
                <div class="tag">Gandheng</div>
                <div class="jawa">ꦒꦤ꧀ꦝꦼꦁ ꦏꦭ꧀ꦧꦸ</div>
                <p>"Kalih manah kang sampun gandheng, kados benang emas — ora gampang pedhot, tansah ngiket sedina-dina."</p>
            </div>
            <div class="sesanti-card">
                <div class="tag">Guyub</div>
                <div class="jawa">ꦒꦸꦪꦸꦧ꧀ ꦫꦸꦏꦸꦤ꧀</div>
                <p>"Rumahtangga kang guyub rukun, kados tanduran kang kramat — yen siram saben dina, mesthi thukul rahayu."</p>
            </div>
            <div class="sesanti-card">
                <div class="tag">Luruh</div>
                <div class="jawa">ꦭꦸꦫꦸꦃ ꦧꦸꦢꦶ</div>
                <p>"Luhur budi, adil basane, sabar panggah — iku gendhinging urip kang bakal tansah kawula senadhiya."</p>
            </div>
            <div class="sesanti-card">
                <div class="tag">Slamet</div>
                <div class="jawa">ꦱ꧀ꦭꦩꦼꦠ꧀ ꦠꦤ꧀ꦱ ꦲꦉꦥ꧀</div>
                <p>"Mugi tansah pinaringan slamet — urip bebarengan, nganti tuwa, bebarengan nggendhong putra-putri."</p>
            </div>
        </div>
    </div>
</section>

<!-- ================= COUNTDOWN ================= -->
<section class="batik" id="countdown">
    <div class="container">
        <div class="ornament fade">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M12 2l2.4 6.2L21 11l-6.6 2.8L12 20l-2.4-6.2L3 11l6.6-2.8z"/></svg>
        </div>
        <h2 class="judul fade">Dina Rahayu</h2>
        <div class="judul-sub fade">Ngetung dina kang dinanti</div>

        <div class="countdown fade" id="countdown">
            <div class="unit"><span class="angka" id="cd-dina">0</span><span class="label">Dina</span></div>
            <span class="pemisah">·</span>
            <div class="unit"><span class="angka" id="cd-jam">0</span><span class="label">Jam</span></div>
            <span class="pemisah">·</span>
            <div class="unit"><span class="angka" id="cd-menit">0</span><span class="label">Menit</span></div>
            <span class="pemisah">·</span>
            <div class="unit"><span class="angka" id="cd-detik">0</span><span class="label">Detik</span></div>
        </div>
    </div>
</section>

<!-- ================= RANGKAIAN ADAT JAWA ================= -->
<section class="rangkai-seksi" id="acara">
    <div class="container">
        <div class="ornament fade">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M12 2l2.4 6.2L21 11l-6.6 2.8L12 20l-2.4-6.2L3 11l6.6-2.8z"/></svg>
        </div>
        <h2 class="judul fade">Rangkaian Adat Jawa</h2>
        <div class="judul-sub fade">Tradhisi Luhur Wangsan Kita</div>

        <div class="timeline fade">
            <div class="acara">
                <div class="java-acara">ꦱꦶꦫꦩ꧀ꦩꦤ꧀</div>
                <h4>Siraman</h4>
                <div class="waktu">Jumat, 19 Februari 2027 · 10.00 WIB</div>
                <p>Siraman ngresiki jiwa raga calon penganten kanthi sekar setaman — simbol nyucikake dhiri sadurunge nglampahi urip anyar.</p>
            </div>
            <div class="acara">
                <div class="java-acara">ꦩꦶꦢꦺꦴꦢꦉꦤꦶ</div>
                <h4>Midodareni</h4>
                <div class="waktu">Sabtu, 20 Februari 2027 · 19.00 WIB</div>
                <p>Wengi kang suci, sesideman penganten putri saha pandonga kulawarga — ngenteni dina esok kang rahayu.</p>
            </div>
            <div class="acara">
                <div class="java-acara">ꦲꦏꦢ꧀ ꦤꦶꦏꦃ</div>
                <h4>Akad Nikah</h4>
                <div class="waktu">Minggu, 21 Februari 2027 · 09.00 WIB</div>
                <p>Ikrar suci ing ngarsane penghulu, saksi, saha kulawarga — ngiket kalih jiwa dados satunggal kulawarga.</p>
            </div>
            <div class="acara">
                <div class="java-acara">ꦔꦸꦤ꧀ꦝꦸꦃ ꦩꦤ꧀ꦠꦸ</div>
                <h4>Ngundhuh Mantu</h4>
                <div class="waktu">Minggu, 21 Februari 2027 · 11.00 WIB</div>
                <p>Pahargyan kabagyan kanthi sungkeman — ngaturaken bekti dhumateng tiyang sepuh, nyuwun pangestu lan berkah.</p>
            </div>
        </div>
    </div>
</section>

<!-- ================= RSVP ================= -->
<section class="batik" id="rsvp">
    <div class="container">
        <div class="ornament fade">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M12 2l2.4 6.2L21 11l-6.6 2.8L12 20l-2.4-6.2L3 11l6.6-2.8z"/></svg>
        </div>
        <h2 class="judul fade">Konfirmasi Kehadiran</h2>
        <div class="judul-sub fade">RSVP · Atur Pangestu</div>

        <?php if ($flashOk): ?>
            <div class="flash suwun fade katon">Matur nuwun sanget! Konfirmasi panjenengan sampun kawula tampi. 🏵️</div>
        <?php elseif ($flashError !== null): ?>
            <div class="flash larap fade katon"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="stats fade katon">
            <?= (int) $countHadir ?> tamu badhe hadir · <?= (int) $countTidak ?> boten saged hadir
        </div>

        <form method="post" action="/#rsvp" class="form-grid fade">
            <div>
                <label for="name">Asma (Nama Lengkap)</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($rsvpName, ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required>
            </div>
            <div>
                <label for="attendance">Kehadiran</label>
                <select id="attendance" name="attendance">
                    <option value="hadir" <?= $rsvpAttendance === 'hadir' ? 'selected' : '' ?>>Insya Allah badhe Hadir</option>
                    <option value="tidak_hadir" <?= $rsvpAttendance === 'tidak_hadir' ? 'selected' : '' ?>>Boten Saged Hadir</option>
                </select>
            </div>
            <div>
                <label for="guests">Cacah Tamu (termasuk panjenengan)</label>
                <input type="number" id="guests" name="guests" min="1" max="10" value="<?= $rsvpGuests ?>" required>
            </div>
            <div>
                <label for="message">Pangestu & Ucapan</label>
                <textarea id="message" name="message" maxlength="1000" placeholder="Tulis pangestu lan ucapan kagem penganten..."><?= htmlspecialchars($rsvpMessage, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <button type="submit" class="btn">Kirim Konfirmasi</button>
        </form>
    </div>
</section>

<!-- ================= PANGGESTU ================= -->
<section class="batik" id="pangestu">
    <div class="container">
        <div class="ornament fade">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M12 2l2.4 6.2L21 11l-6.6 2.8L12 20l-2.4-6.2L3 11l6.6-2.8z"/></svg>
        </div>
        <h2 class="judul fade">Pangestu & Ucapan</h2>
        <div class="judul-sub fade">Doa saking para rawuh</div>

        <div class="pangestu">
            <?php if ($messages === []): ?>
                <div class="kosong fade">Dereng wonten pangestu. Panjenengan sepisanan ingkang ndongakaken kawula. 🥰</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="pesan fade">
                        <div class="kepala">
                            <strong><?= htmlspecialchars($msg['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="chip"><?= $msg['attendance'] === 'hadir' ? 'Hadir' : 'Boten Hadir' ?></span>
                        </div>
                        <?php if ($msg['message'] !== null && $msg['message'] !== ''): ?>
                            <p>"<?= htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8') ?>"</p>
                        <?php endif; ?>
                        <div class="kala"><?= htmlspecialchars(date('d M Y, H:i', strtotime($msg['created_at'])), ENT_QUOTES, 'UTF-8') ?> · <?= (int) $msg['guests'] ?> tamu</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================= FOOTER ================= -->
<footer>
    <div class="java">ꦩꦸꦒꦶ ꦠꦤ꧀ꦱ ꦒꦸꦪꦸꦧ꧀ ꦫꦸꦏꦸꦤ꧀</div>
    <div class="nama">Nabila & Arief</div>
    <p>Satunggaling pakurmatan saha kabingahan kagem kawula<br>manawi Bapak/Ibu/Saudara kersa rawuh saha paring pangestu.</p>
    <p class="tutup-jawa">"Mugi tansah manggih rahayu, sinartan berkah saking Gusti" 🙏</p>
</footer>

<script>
    const DINA_RAHAYU = new Date('2027-02-21T09:00:00+07:00');

    function itungCountdown() {
        const selisih = DINA_RAHAYU - new Date();
        const el = (id) => document.getElementById(id);
        if (selisih <= 0) {
            el('cd-dina').textContent = '0';
            el('cd-jam').textContent = '0';
            el('cd-menit').textContent = '0';
            el('cd-detik').textContent = '0';
            return;
        }
        el('cd-dina').textContent = Math.floor(selisih / 86400000);
        el('cd-jam').textContent = Math.floor(selisih / 3600000) % 24;
        el('cd-menit').textContent = Math.floor(selisih / 60000) % 60;
        el('cd-detik').textContent = Math.floor(selisih / 1000) % 60;
    }
    itungCountdown();
    setInterval(itungCountdown, 1000);

    const pengamat = new IntersectionObserver((entri) => {
        entri.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('katon');
                pengamat.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll('.fade').forEach((el) => pengamat.observe(el));
</script>
</body>
</html>
