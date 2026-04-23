@extends('layouts.app')

@section('content')

{{-- Hero --}}
<section class="fdr-om-hero" style="height:50vh;min-height:320px;">
  <div class="fdr-hero-slide active" style="background-image:url('/app/uploads/hero/placeholder2.jpg')"></div>
  <div class="fdr-hero-slide" style="background-image:url('/app/uploads/hero/placeholder3.jpg')"></div>
  <div class="fdr-om-hero-overlay"></div>
  <div class="fdr-om-hero-inner" style="justify-content:center;text-align:center;">
    <h1 style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:500;font-size:clamp(24px,4vw,48px);letter-spacing:0.05em;color:#fff;text-transform:uppercase;">Kontakt</h1>
  </div>
</section>


{{-- Kontakt layout --}}
<section class="fdr-kontakt-sektion">
  <div class="fdr-kontakt-inner">

    {{-- Vänster: Kontaktinfo --}}
    <div class="fdr-kontakt-info">
      <h2>Hör av dig</h2>
      <p>Vi finns här för dig — oavsett om du vill sälja, köpa eller bara vill veta mer om marknaden.</p>

      <div class="fdr-kontakt-detaljer">
        <div class="fdr-kontakt-rad">
          <span class="fdr-kontakt-label">Telefon</span>
          <a href="tel:+46704455180">070-445 51 80</a>
        </div>
        <div class="fdr-kontakt-rad">
          <span class="fdr-kontakt-label">E-post</span>
          <a href="mailto:info@ettmakleri.se">info@ettmakleri.se</a>
        </div>
        <div class="fdr-kontakt-rad">
          <span class="fdr-kontakt-label">Adress</span>
          <span>Grev Turegatan 50<br>114 38 Stockholm</span>
        </div>
      </div>

      <div class="fdr-kontakt-spekulant">
        <p>Letar du efter din nästa bostad? Anmäl dig till vårt spekulantregister.</p>
        <button onclick="document.getElementById('spekulant-modal').style.display='flex'" class="fdr-btn-outline">
          Anmäl dig som spekulant
        </button>
      </div>
    </div>

    {{-- Höger: Formulär --}}
    <div class="fdr-kontakt-form">
      <h3>Skicka ett meddelande</h3>

      @if(request('success') == '1')
        <div class="fdr-form-success">Tack! Ditt meddelande har skickats.</div>
      @endif

      <form method="POST" action="{{ home_url('/kontakt-skicka') }}" class="fdr-formulär">
        @csrf
        <div class="fdr-form-grupp">
          <label>Namn</label>
          <input type="text" name="namn" required placeholder="Ditt namn">
        </div>
        <div class="fdr-form-rad">
          <div class="fdr-form-grupp">
            <label>E-post</label>
            <input type="email" name="email" required placeholder="din@email.se">
          </div>
          <div class="fdr-form-grupp">
            <label>Telefon</label>
            <input type="tel" name="telefon" placeholder="070-123 45 67">
          </div>
        </div>
        <div class="fdr-form-grupp">
          <label>Meddelande</label>
          <textarea name="meddelande" required rows="5" placeholder="Hur kan vi hjälpa dig?"></textarea>
        </div>
        <button type="submit" class="fdr-btn-outline" style="width:100%;text-align:center;">Skicka</button>
      </form>
    </div>

  </div>
</section>

{{-- Team-grid --}}
<section class="fdr-kontakt-team">
  <div class="fdr-team-inner">
    <div class="fdr-team-grid">
      @php $team = [
        (object)['namn' => 'Johan Franzon', 'titel' => 'Fastighetsmäklare', 'telefon' => '070-445 51 80', 'email' => 'franzon@franzondurietz.se', 'bild' => '/app/uploads/team/johan-franzon.jpg'],
        (object)['namn' => 'Johan Du Rietz', 'titel' => 'Fastighetsmäklare', 'telefon' => '070-880 07 99', 'email' => 'durietz@franzondurietz.se', 'bild' => '/app/uploads/team/johan-durietz.jpg'],
        (object)['namn' => 'Farboud Nejad', 'titel' => 'Fastighetsmäklare', 'telefon' => '073-909 49 06', 'email' => 'nejad@franzondurietz.se', 'bild' => '/app/uploads/team/farboud-nejad.jpg'],
        (object)['namn' => 'Emelie Willberg', 'titel' => 'Affärskoordinator', 'telefon' => '076-528 22 68', 'email' => 'willberg@franzondurietz.se', 'bild' => '/app/uploads/team/emelie-willberg.jpg'],
        (object)['namn' => 'Sandra Zeilon', 'titel' => 'Affärskoordinator & kontorsansvarig', 'telefon' => '073-078 19 60', 'email' => 'zeilon@franzondurietz.se', 'bild' => '/app/uploads/team/sandra-zeilon.jpg'],
        (object)['namn' => 'Susanne Hagensgård', 'titel' => 'Fastighetsmäklare', 'telefon' => '070-749 04 43', 'email' => 'hagensgard@franzondurietz.se', 'bild' => '/app/uploads/team/susanne-hagensgard.jpg'],
      ]; @endphp
      @foreach($team as $m)
        <div class="fdr-team-kort">
          <div class="fdr-team-bild">
            <img src="{{ $m->bild }}" alt="{{ $m->namn }}">
          </div>
          <div class="fdr-team-info">
            <div class="fdr-team-separator"></div>
            <h3 class="fdr-team-namn">{{ $m->namn }}</h3>
            <p class="fdr-team-titel">{{ $m->titel }}</p>
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $m->telefon) }}" class="fdr-team-kontakt">{{ $m->telefon }}</a>
            <a href="mailto:{{ $m->email }}" class="fdr-team-kontakt">{{ $m->email }}</a>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Spekulant modal --}}
<div id="spekulant-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.7);align-items:center;justify-content:center;">
  <div class="fdr-modal-inner">
    <button onclick="document.getElementById('spekulant-modal').style.display='none'" class="fdr-modal-close">&times;</button>
    <h2>Anmäl ditt intresse</h2>
    <p>Berätta vad du söker så kontaktar vi dig när rätt objekt dyker upp.</p>
    <form method="POST" action="{{ home_url('/kontakt') }}">
      @php echo wp_nonce_field('spekulant_form', 'spekulant_nonce', true, false); @endphp
      <input type="hidden" name="form_type" value="spekulant">
      <div class="fdr-form-grupp">
        <label>Namn</label>
        <input type="text" name="spekulant_namn" required placeholder="Ditt namn">
      </div>
      <div class="fdr-form-grupp">
        <label>E-post</label>
        <input type="email" name="spekulant_email" required placeholder="din@email.se">
      </div>
      <div class="fdr-form-grupp">
        <label>Vad söker du?</label>
        <textarea name="spekulant_soker" rows="4" placeholder="Beskriv vad du söker — område, storlek, budget..."></textarea>
      </div>
      <button type="submit" class="fdr-btn-outline" style="width:100%;text-align:center;">Skicka intresseanmälan</button>
    </form>
  </div>
</div>

<script>
document.getElementById('spekulant-modal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>

@endsection
