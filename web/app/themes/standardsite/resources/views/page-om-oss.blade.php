@extends('layouts.app')

@section('content')

{{-- Hero med bildspel --}}
<div class="kontakt-hero">
  <div class="kontakt-hero-slide active" style="background-image:url('http://localhost:8090/app/uploads/hero1.jpg')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('http://localhost:8090/app/uploads/hero2.jpg')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('http://localhost:8090/app/uploads/hero3.jpg')"></div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner">
    <p class="kontakt-hero-eyebrow">PREK MÄKLERI</p>
    <h1>Om oss</h1>
    <p class="kontakt-hero-sub">Erfarna mäklare med lokal kännedom</p>
  </div>
</div>

{{-- Intro --}}
<section class="page-sektion">
  <div class="page-inner">
    <div class="om-oss-intro">
      <h2>Vi hittar rätt hem för dig</h2>
      <p>På PREK kombinerar vi lång erfarenhet av bostadsmarknaden med ett genuint engagemang för varje kund. Vi guidar dig genom hela processen – från första visning till nyckelöverlämning – med ärlighet och lokal expertis som grund.</p>
    </div>

    <div class="om-oss-grid">
      <div class="om-oss-block">
        <h3>Lokal expertis</h3>
        <p>Med djup kännedom om den lokala marknaden ger vi dig rätt underlag för att fatta välgrundade beslut – oavsett om du köper eller säljer.</p>
      </div>
      <div class="om-oss-block">
        <h3>Personlig service</h3>
        <p>Varje affär är unik. Vi lyssnar, förstår dina behov och anpassar vår service efter dig – inte tvärtom.</p>
      </div>
      <div class="om-oss-block">
        <h3>Trygghet i processen</h3>
        <p>Att köpa eller sälja bostad är ett av livets större beslut. Vi ser till att du känner dig trygg och välinformerad i varje steg.</p>
      </div>
    </div>
  </div>
</section>

{{-- Värderingar --}}
<section class="page-sektion" style="background:var(--bg-warm);padding-top:80px;padding-bottom:80px;">
  <div class="page-inner" style="text-align:center;">
    <p class="sektion-eyebrow">VÅRA VÄRDERINGAR</p>
    <div class="om-oss-grid" style="margin-top:0;">
      <div class="om-oss-block" style="border-top:2px solid var(--accent);padding-top:24px;">
        <h3>Ärlighet</h3>
        <p>Vi ger alltid en ärlig bild av marknaden och objektet – även när det inte är vad man vill höra.</p>
      </div>
      <div class="om-oss-block" style="border-top:2px solid var(--accent);padding-top:24px;">
        <h3>Engagemang</h3>
        <p>Vi engagerar oss fullt ut i varje affär och arbetar hårt för att uppnå bästa möjliga resultat.</p>
      </div>
      <div class="om-oss-block" style="border-top:2px solid var(--accent);padding-top:24px;">
        <h3>Tillgänglighet</h3>
        <p>Vi finns tillgängliga när du behöver oss och svarar alltid snabbt på frågor och funderingar.</p>
      </div>
    </div>
  </div>
</section>

@endsection
