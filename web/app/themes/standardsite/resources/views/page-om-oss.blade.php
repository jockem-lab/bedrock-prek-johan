@extends('layouts.app')

@section('content')

{{-- Hero --}}
<section class="fdr-om-hero" style="height:50vh;min-height:320px;">
  <div class="fdr-hero-slide active" style="background-image:url('/app/uploads/hero/placeholder2.jpg')"></div>
  <div class="fdr-hero-slide" style="background-image:url('/app/uploads/hero/placeholder3.jpg')"></div>
  <div class="fdr-om-hero-overlay"></div>
  <div class="fdr-om-hero-inner" style="justify-content:center;text-align:center;">
    <h1 style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:500;font-size:clamp(24px,4vw,48px);letter-spacing:0.05em;color:#fff;text-transform:uppercase;">Om oss</h1>
  </div>
</section>


{{-- Intro-text --}}
<section class="fdr-om-intro">
  <div class="fdr-om-intro-inner">
    <div class="fdr-om-intro-text">
      @if(!empty($oo_intro_text))
        {!! $oo_intro_text !!}
      @else
        <p>Franzon Du Rietz Fastighetsmäkleri grundades sommaren 2019 av Johan Franzon och Johan Du Rietz, som tillsammans har mer än 33 års samlad erfarenhet av framgångsrik och kvalitetsinriktad bostadsförmedling i Stockholm.</p>
        <p>Våren 2026 gick Farboud Nejad in som delägare i bolaget, vilket ytterligare stärker vår kompetens, vårt engagemang och vår långsiktiga satsning på att erbjuda en förstklassig mäklartjänst.</p>
        <p>Vår främsta målsättning är att upprätthålla högsta kvalitet i varje enskild försäljning. För att säkerställa detta arbetar vi med ett selektivt urval av uppdrag varje år, vilket ger oss möjlighet att erbjuda en genomtänkt, strukturerad och noggrant anpassad försäljningsprocess.</p>
        <p>Valet av fastighetsmäklare är en av de mest avgörande faktorerna för att uppnå högsta möjliga slutpris i en bostadsaffär. Enligt vår filosofi förtjänar varje bostadsaffär samma nivå av engagemang, noggrannhet och ödmjukhet.</p>
      @endif
    </div>
  </div>
</section>

{{-- Team --}}
<section class="fdr-team-sektion">
  <div class="fdr-team-inner">
    @if(false)
      @php $team = $oo_team; @endphp
    @else
      @php $team = [
        (object)['namn' => 'Johan Franzon', 'titel' => 'Fastighetsmäklare', 'telefon' => '070-445 51 80', 'email' => 'franzon@franzondurietz.se', 'bild' => '/app/uploads/team/johan-franzon.jpg'],
        (object)['namn' => 'Johan Du Rietz', 'titel' => 'Fastighetsmäklare', 'telefon' => '070-880 07 99', 'email' => 'durietz@franzondurietz.se', 'bild' => '/app/uploads/team/johan-durietz.jpg'],
        (object)['namn' => 'Farboud Nejad', 'titel' => 'Fastighetsmäklare', 'telefon' => '073-909 49 06', 'email' => 'nejad@franzondurietz.se', 'bild' => '/app/uploads/team/farboud-nejad.jpg'],
        (object)['namn' => 'Emelie Willberg', 'titel' => 'Affärskoordinator', 'telefon' => '076-528 22 68', 'email' => 'willberg@franzondurietz.se', 'bild' => '/app/uploads/team/emelie-willberg.jpg'],
        (object)['namn' => 'Sandra Zeilon', 'titel' => 'Affärskoordinator & kontorsansvarig', 'telefon' => '073-078 19 60', 'email' => 'zeilon@franzondurietz.se', 'bild' => '/app/uploads/team/sandra-zeilon.jpg'],
        (object)['namn' => 'Susanne Hagensgård', 'titel' => 'Fastighetsmäklare', 'telefon' => '070-749 04 43', 'email' => 'hagensgard@franzondurietz.se', 'bild' => '/app/uploads/team/susanne-hagensgard.jpg'],
      ]; @endphp
    @endif

    <div class="fdr-team-grid">
      @foreach($team as $m)
        <div class="fdr-team-kort">
          <div class="fdr-team-bild">
            @if($m->bild)
              <img src="{{ $m->bild }}" alt="{{ $m->namn }}">
            @else
              <div class="fdr-team-bild-placeholder">{{ strtoupper(substr($m->namn, 0, 1)) }}</div>
            @endif
          </div>
          <div class="fdr-team-info">
            <div class="fdr-team-separator"></div>
            <h3 class="fdr-team-namn">{{ $m->namn }}</h3>
            <p class="fdr-team-titel">{{ $m->titel }}</p>
            @if($m->telefon)
              <a href="tel:{{ preg_replace('/[^0-9+]/', '', $m->telefon) }}" class="fdr-team-kontakt">{{ $m->telefon }}</a>
            @endif
            @if($m->email)
              <a href="mailto:{{ $m->email }}" class="fdr-team-kontakt">{{ $m->email }}</a>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

@endsection
