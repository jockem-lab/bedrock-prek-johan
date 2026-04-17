@extends('layouts.app')
@section('content')

{{-- Hero --}}
<div class="kontakt-hero page-slideshow" style="height:380px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
  <div class="page-slides">
    <div class="page-slide active" style="background-image:url('{{ content_url('uploads/oscars-hero3.jpg') }}')"></div>
    <div class="page-slide" style="background-image:url('{{ content_url('uploads/oscars-hero2.jpg') }}')"></div>
  </div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner" style="position:relative;z-index:1;text-align:center;">
    <h1 class="undersida-rubrik">Underhand</h1>
  </div>
</div>

{{-- Intro --}}
<section style="padding:64px 40px 48px;text-align:center;">
  <div style="max-width:640px;margin:0 auto;">
    <span class="sektion-eyebrow-label">Diskret förmedling</span>
    <h2 class="sektion-rubrik" style="margin-bottom:20px;">För utvalda köpare</h2>
    <p style="font-family:var(--font-body);font-size:15px;color:var(--text-mid);line-height:1.8;">
      Tack vare vårt stora kundregister har vi goda möjligheter att matcha rätt objekt med rätt köpare — utan att någon annan behöver få veta.
    </p>
  </div>
</section>

{{-- Objekt --}}
<section style="padding:0 40px 80px;">
  <div style="max-width:1100px;margin:0 auto;">
    @if(!empty($objekt))
      <div id="uh-lista">
        @foreach($objekt as $i => $obj)
          <div class="uh-rad{{ $i >= 5 ? ' uh-dold' : '' }}">

            {{-- Karusell --}}
            <div class="uh-karusell" id="karusell-{{ $i }}">
              <div class="uh-karusell-track">
                @if(!empty($obj->bilder))
                  @foreach($obj->bilder as $bild)
                    <div class="uh-karusell-bild" style="background-image:url('{{ $bild }}')"></div>
                  @endforeach
                @else
                  <div class="uh-karusell-bild" style="background:#243558;"></div>
                @endif
              </div>
              @if(count($obj->bilder) > 1)
                <button class="uh-karusell-prev" onclick="uhPrev({{ $i }})">&#8592;</button>
                <button class="uh-karusell-next" onclick="uhNext({{ $i }})">&#8594;</button>
                <div class="uh-karusell-dots">
                  @foreach($obj->bilder as $j => $bild)
                    <span class="uh-dot{{ $j === 0 ? ' active' : '' }}"
                          onclick="uhGoto({{ $i }}, {{ $j }})"></span>
                  @endforeach
                </div>
              @endif
            </div>

            {{-- Info --}}
            <div class="uh-rad-info">
              <div class="uh-omrade">{{ $obj->omrade }}</div>
              <ul class="uh-punkter">
                @if($obj->kvm)
                  <li>{{ $obj->kvm }} kvm</li>
                @endif
                @if($obj->rum)
                  <li>{{ $obj->rum }} rok</li>
                @endif
                @foreach($obj->punkter as $punkt)
                  <li>{{ $punkt }}</li>
                @endforeach
              </ul>
              @if($obj->maklare['email'] || $obj->maklare['telefon'])
                <div class="uh-kontakt">
                  <div class="uh-kontakt-label">Kontakta</div>
                  @if($obj->maklare['namn'])
                    <span style="font-size:13px;color:rgba(255,255,255,0.5);display:block;margin-bottom:4px;">{{ $obj->maklare['namn'] }}</span>
                  @endif
                  @if($obj->maklare['email'])
                    <a href="mailto:{{ $obj->maklare['email'] }}">{{ $obj->maklare['email'] }}</a>
                  @endif
                  @if($obj->maklare['telefon'])
                    <a href="tel:{{ $obj->maklare['telefon'] }}">{{ $obj->maklare['telefon'] }}</a>
                  @endif
                </div>
              @endif
            </div>

          </div>
        @endforeach
      </div>

      @if(count($objekt) > 5)
        <div id="uh-visa-fler-wrap" style="text-align:center;margin-top:40px;">
          <button class="btn-primary" onclick="visaFlerUH()">
            Visa fler ({{ count($objekt) - 5 }} till)
          </button>
        </div>
      @endif

    @else
      <p style="text-align:center;color:var(--text-mid);padding:48px 0;">
        Inga underhandsobjekt just nu — hör av dig så berättar vi mer.
      </p>
    @endif
  </div>
</section>

{{-- CTA --}}
<section style="background:#111D33;padding:80px 24px;text-align:center;">
  <div style="max-width:560px;margin:0 auto;">
    <span class="sektion-eyebrow-label">Underhand</span>
    <h2 class="sektion-rubrik" style="margin-bottom:20px;">Vill du sälja underhand?</h2>
    <p style="font-family:var(--font-body);font-size:15px;color:var(--text-mid);line-height:1.8;margin-bottom:32px;">
      Kontakta oss så berättar vi hur vi kan hjälpa dig med en diskret och professionell försäljning.
    </p>
    <a href="{{ home_url('/kontakt') }}" class="btn-primary">Kontakta oss</a>
  </div>
</section>

<script>
var uhState = {};

function uhInit() {
  document.querySelectorAll('.uh-karusell').forEach(function(k) {
    var id = k.id.replace('karusell-', '');
    uhState[id] = 0;
    // Touch swipe
    var startX = 0;
    k.addEventListener('touchstart', function(e) { startX = e.touches[0].clientX; }, {passive:true});
    k.addEventListener('touchend', function(e) {
      var diff = startX - e.changedTouches[0].clientX;
      if (Math.abs(diff) > 40) {
        if (diff > 0) uhNext(id); else uhPrev(id);
      }
    }, {passive:true});
  });
}

function uhGoto(id, index) {
  var track = document.querySelector('#karusell-' + id + ' .uh-karusell-track');
  var dots  = document.querySelectorAll('#karusell-' + id + ' .uh-dot');
  if (!track) return;
  uhState[id] = index;
  track.style.transform = 'translateX(-' + (index * 100) + '%)';
  dots.forEach(function(d, i) { d.classList.toggle('active', i === index); });
}

function uhNext(id) {
  var bilder = document.querySelectorAll('#karusell-' + id + ' .uh-karusell-bild');
  var next = (uhState[id] + 1) % bilder.length;
  uhGoto(id, next);
}

function uhPrev(id) {
  var bilder = document.querySelectorAll('#karusell-' + id + ' .uh-karusell-bild');
  var prev = (uhState[id] - 1 + bilder.length) % bilder.length;
  uhGoto(id, prev);
}

function visaFlerUH() {
  document.querySelectorAll('.uh-dold').forEach(function(el) {
    el.classList.remove('uh-dold');
    el.style.display = '';
  });
  var wrap = document.getElementById('uh-visa-fler-wrap');
  if (wrap) wrap.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', uhInit);
</script>

@endsection
