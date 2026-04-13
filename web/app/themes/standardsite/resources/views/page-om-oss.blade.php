@extends('layouts.app')

@section('content')
{{-- Hero med bildspel --}}
<div class="kontakt-hero">
  <div class="kontakt-hero-slide active" style="background-image:url('https://images.unsplash.com/photo-1497366216548-37526070297c?w=1600&q=80')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1600&q=80')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?w=1600&q=80')"></div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner">
    <p class="kontakt-hero-eyebrow">PREK MÄKLERI</p>
    <h1>Om oss</h1>
    <p class="kontakt-hero-sub">Erfarna mäklare med lokal kännedom</p>
  </div>
</div>

<section class="page-sektion">
  <div class="page-inner">
    <div class="om-oss-intro">
      <h2>Erfarna mäklare med lokal kännedom</h2>
      <p>Vi på PREK är ett mäklarteam med lång erfarenhet av bostadsmarknaden. Vi guidar dig genom hela processen – från första visning till nyckelöverlämning.</p>
    </div>

    <div class="om-oss-grid">
      <div class="om-oss-block">
        <h3>Vår filosofi</h3>
        <p>Vi tror på ärlighet, transparens och personlig service. Varje kund är unik och förtjänar ett engagemang som speglar det.</p>
      </div>
      <div class="om-oss-block">
        <h3>Vår erfarenhet</h3>
        <p>Med över 20 års erfarenhet på marknaden har vi hjälpt hundratals familjer hitta sitt drömhem i Linköping och omgivande kommuner.</p>
      </div>
      <div class="om-oss-block">
        <h3>Vårt område</h3>
        <p>Vi är specialiserade på Linköping och Östergötland, med djup kunskap om lokala prisbilder, bostadsrättsföreningar och villor.</p>
      </div>
    </div>
  </div>
</section>
@endsection
