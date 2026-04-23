@extends('layouts.app')

@section('content')

{{-- SPLASH SCREEN --}}
<div id="em-splash">
  <div id="em-splash-logo">
    <div id="em-splash-text">ETT MÄKLERI</div>
    <div id="em-splash-tagline">FASTIGHETSMÄKLERI</div>
  </div>
</div>

{{-- HERO med bildkarusell --}}
<section class="em-hero" id="em-hero">
  <div class="em-hero-slide active" style="background-image:url('/app/uploads/hero/start-hero.jpg')"></div>
  <div class="em-hero-slide" style="background-image:url('/app/uploads/hero/placeholder3.jpg')"></div>
  <div class="em-hero-overlay"></div>
</section>

{{-- SEKTIONER --}}
<div class="em-sektioner" id="em-sektioner">

  {{-- Lägenheter --}}
  <section class="em-sektion" data-index="0">
    <div class="em-sektion-bild">
      <img src="/app/uploads/hero/placeholder2.jpg" alt="Lägenheter">
    </div>
    <div class="em-sektion-text">
      <p class="em-sektion-eyebrow">TILL SALU</p>
      <h2 class="em-sektion-rubrik">LÄGENHETER</h2>
      <p class="em-sektion-beskrivning">Ett kurerat urval av lägenheter i Östermalm, Södermalm, Vasastan och på Kungsholmen. Tidlösa hem med tydlig karaktär. Utöver publicerade objekt förmedlar vi även bostäder underhand, med samma omsorg och diskretion.</p>
      <a href="{{ home_url('/objekt') }}" class="em-sektion-btn">UTFORSKA VÅRA LÄGENHETER</a>
    </div>
  </section>

  {{-- Hus --}}
  <section class="em-sektion em-sektion--reverse" data-index="1">
    <div class="em-sektion-bild">
      <img src="/app/uploads/hero/placeholder3.jpg" alt="Hus">
    </div>
    <div class="em-sektion-text">
      <p class="em-sektion-eyebrow">TILL SALU</p>
      <h2 class="em-sektion-rubrik">HUS</h2>
      <p class="em-sektion-beskrivning">Ett kurerat urval av hus i Stockholm och skärgården. Permanenta boenden och landställen. Villor, radhus och fritidshus. Arkitektur, läge och helhet i fokus.</p>
      <a href="{{ home_url('/objekt') }}" class="em-sektion-btn">UTFORSKA VÅRA HUS</a>
    </div>
  </section>

  {{-- Underhand --}}
  <section class="em-sektion" data-index="2">
    <div class="em-sektion-bild">
      <img src="/app/uploads/hero/placeholder2.jpg" alt="Underhand" style="filter:brightness(0.95);">
    </div>
    <div class="em-sektion-text">
      <p class="em-sektion-eyebrow">INTRESSEANMÄLAN</p>
      <h2 class="em-sektion-rubrik">UNDERHAND</h2>
      <p class="em-sektion-beskrivning">En del av de bostäder vi förmedlar når aldrig den öppna marknaden, detta i enlighet med våra uppdragsgivares önskemål. ETT MÄKLERI disponerar över ett omfattande kontaktnät och en köpstark databas som ständigt hålls uppdaterad.</p>
      <a href="{{ home_url('/underhand') }}" class="em-sektion-btn">MAILA OSS</a>
    </div>
  </section>

  {{-- Anlita oss --}}
  <section class="em-sektion em-sektion--reverse" data-index="3">
    <div class="em-sektion-bild">
      <img src="/app/uploads/hero/placeholder3.jpg" alt="Anlita oss">
    </div>
    <div class="em-sektion-text">
      <p class="em-sektion-eyebrow">VÄRDERING AV BOSTAD</p>
      <h2 class="em-sektion-rubrik">ANLITA OSS</h2>
      <p class="em-sektion-beskrivning">Överväger ni att sälja och önskar en värdering av ert hem? Vi ser fram emot att träffa er för ett helt förutsättningslöst möte. Vänligen fyll i formuläret nedan, så återkommer vi snarast möjligt för att diskutera era specifika behov.</p>
      <a href="{{ home_url('/kontakt') }}" class="em-sektion-btn">FYLL I FORMULÄRET</a>
    </div>
  </section>

</div>

<script>
(function() {
  var splash = document.getElementById('em-splash');
  var hero = document.getElementById('em-hero');
  var header = document.querySelector('.fdr-header');
  var splashLogo = document.getElementById('em-splash-logo');
  var splashTagline = document.getElementById('em-splash-tagline');

  // Dölj header under splash
  if (header) header.style.opacity = '0';

  // Steg 1: Logo fade in centrerad (0 → 0.8s)
  setTimeout(function() {
    splashLogo.style.visibility = 'visible';
    splashLogo.classList.add('em-splash-logo--visible');
  }, 100);

  // Steg 2: Tagline tonar in (1.4s)
  setTimeout(function() {
    splashTagline.classList.add('em-splash-tagline--visible');
  }, 1400);

  // Steg 3: Logotyp glider till nav-position (2.8s — stannar kvar 1.4s i steg 2)
  setTimeout(function() {
    splashLogo.classList.add('em-splash-logo--nav');
  }, 2200);

  // Steg 4: Splash övergår till hero med bildspel (3.8s)
  setTimeout(function() {
    splash.classList.add('em-splash--exit');
    hero.classList.add('em-hero--visible');
    if (header) {
      header.style.transition = 'opacity 0.6s ease';
      header.style.opacity = '1';
    }
  }, 2400);

  // Steg 5: Ta bort splash helt (4.8s)
  setTimeout(function() {
    splash.style.display = 'none';
  }, 4800);

  // Hero karusell
  setTimeout(function() {
    var slides = document.querySelectorAll('.em-hero-slide');
    if (slides.length < 2) return;
    var current = 0;
    setInterval(function() {
      slides[current].classList.remove('active');
      current = (current + 1) % slides.length;
      slides[current].classList.add('active');
    }, 5000);
  }, 3000);

  // Scroll-animation för sektioner
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('em-sektion--visible');
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll('.em-sektion').forEach(function(s) {
    observer.observe(s);
  });

})();
</script>

@endsection
