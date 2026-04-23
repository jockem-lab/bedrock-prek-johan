@extends('layouts.app')
@section('content')

{{-- Hero --}}
<section class="fdr-om-hero" style="height:50vh;min-height:320px;">
  <div class="fdr-hero-slide active" style="background-image:url('/app/uploads/hero/placeholder2.jpg')"></div>
  <div class="fdr-hero-slide" style="background-image:url('/app/uploads/hero/placeholder3.jpg')"></div>
  <div class="fdr-om-hero-overlay"></div>
  <div class="fdr-om-hero-inner" style="justify-content:center;text-align:center;">
    <h1 style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:500;font-size:clamp(24px,4vw,48px);letter-spacing:0.05em;color:#fff;text-transform:uppercase;">Underhand</h1>
  </div>
</section>

{{-- Intro --}}
<section style="padding:80px 40px 64px;text-align:center;background:#fff;">
  <div style="max-width:640px;margin:0 auto;">
    <p style="font-size:10px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;color:#000;margin-bottom:16px;">Diskret förmedling</p>
    <h2 style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:clamp(24px,3vw,36px);font-weight:500;color:#000;margin-bottom:20px;letter-spacing:-0.01em;">För utvalda köpare</h2>
    <p style="font-size:15px;color:#666;line-height:1.8;">
      Tack vare vårt stora kundregister har vi goda möjligheter att matcha rätt objekt med rätt köpare — utan att någon annan behöver få veta.
    </p>
  </div>
</section>

{{-- Objekt --}}
<section style="padding:0 40px 80px;background:#fff;">
  <div style="max-width:1100px;margin:0 auto;">
    @if(!empty($objekt))
      <div id="uh-lista">
        @foreach($objekt as $i => $obj)
          <div class="uh-rad{{ $i >= 5 ? ' uh-dold' : '' }}" style="border-top:1px solid #e8e8e8;">

            {{-- Karusell --}}
            <div class="uh-karusell" id="karusell-{{ $i }}" style="background:#F2F2F2;">
              <div class="uh-karusell-track">
                @if(!empty($obj->bilder))
                  @foreach(array_slice($obj->bilder, 0, 5) as $bild)
                    <div class="uh-karusell-bild" style="background-image:url('{{ $bild }}')"></div>
                  @endforeach
                @else
                  <div class="uh-karusell-bild" style="background:#F2F2F2;"></div>
                @endif
              </div>
              @if(!empty($obj->bilder) && count($obj->bilder) > 1)
                <button class="uh-karusell-prev" onclick="uhPrev({{ $i }})" style="background:rgba(255,255,255,0.8);color:#000;">&#8592;</button>
                <button class="uh-karusell-next" onclick="uhNext({{ $i }})" style="background:rgba(255,255,255,0.8);color:#000;">&#8594;</button>
              @endif
            </div>

            {{-- Info --}}
            <div class="uh-rad-info">
              <div class="uh-omrade" style="color:#000;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:500;font-size:18px;letter-spacing:-0.01em;">{{ $obj->omrade }}</div>
              <ul class="uh-punkter">
                @if($obj->kvm)<li style="color:#666;">{{ $obj->kvm }} kvm</li>@endif
                @if($obj->rum)<li style="color:#666;">{{ $obj->rum }} rok</li>@endif
                @foreach($obj->punkter as $punkt)
                  <li style="color:#666;">{{ $punkt }}</li>
                @endforeach
              </ul>
              @if($obj->maklare['email'] || $obj->maklare['telefon'])
                <div class="uh-kontakt" style="border-top:1px solid #e8e8e8;">
                  <div style="font-size:10px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase;color:#000;margin-bottom:8px;">Kontakta</div>
                  @if($obj->maklare['namn'])
                    <span style="font-size:13px;color:#666;display:block;margin-bottom:4px;">{{ $obj->maklare['namn'] }}</span>
                  @endif
                  @if($obj->maklare['email'])
                    <a href="mailto:{{ $obj->maklare['email'] }}" style="color:#000;font-size:13px;font-weight:600;display:block;text-decoration:none;">{{ strtoupper($obj->maklare['email']) }}</a>
                  @endif
                  @if($obj->maklare['telefon'])
                    <a href="tel:{{ $obj->maklare['telefon'] }}" style="color:#000;font-size:13px;font-weight:600;display:block;text-decoration:none;">{{ $obj->maklare['telefon'] }}</a>
                  @endif
                </div>
              @endif
            </div>

          </div>
        @endforeach
      </div>

      @if(count($objekt) > 5)
        <div id="uh-visa-fler-wrap" style="text-align:center;margin-top:40px;">
          <button onclick="visaFlerUH()" style="background:none;border:1px solid #000;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:11px;font-weight:600;letter-spacing:0.14em;padding:14px 40px;cursor:pointer;">
            VISA FLER ({{ count($objekt) - 5 }} TILL)
          </button>
        </div>
      @endif

    @else
      <div style="text-align:center;padding:80px 0;border-top:1px solid #e8e8e8;">
        <p style="font-size:13px;color:#666;margin-bottom:24px;">Inga underhandsobjekt just nu — hör av dig så berättar vi mer.</p>
        <a href="{{ home_url('/kontakt') }}" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:11px;font-weight:600;letter-spacing:0.14em;text-decoration:none;border:1px solid #000;padding:14px 40px;color:#000;">KONTAKTA OSS</a>
      </div>
    @endif
  </div>
</section>

{{-- CTA --}}
<section style="background:#000;padding:80px 24px;text-align:center;">
  <div style="max-width:560px;margin:0 auto;">
    <p style="font-size:10px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;color:#666;margin-bottom:16px;">Underhand</p>
    <h2 style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:clamp(24px,3vw,36px);font-weight:500;color:#fff;margin-bottom:20px;letter-spacing:-0.01em;">Vill du sälja underhand?</h2>
    <p style="font-size:15px;color:#666;line-height:1.8;margin-bottom:32px;">
      Kontakta oss så berättar vi hur vi kan hjälpa dig med en diskret och professionell försäljning.
    </p>
    <a href="{{ home_url('/kontakt') }}" style="display:inline-block;background:#fff;color:#000;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:11px;font-weight:600;letter-spacing:0.14em;padding:14px 40px;text-decoration:none;text-transform:uppercase;transition:all 0.2s;">KONTAKTA OSS</a>
  </div>
</section>

<script>
var uhState = {};
function uhInit() {
  document.querySelectorAll('.uh-karusell').forEach(function(k) {
    var id = k.id.replace('karusell-', '');
    uhState[id] = 0;
    var startX = 0;
    k.addEventListener('touchstart', function(e) { startX = e.touches[0].clientX; }, {passive:true});
    k.addEventListener('touchend', function(e) {
      var diff = startX - e.changedTouches[0].clientX;
      if (Math.abs(diff) > 40) { if (diff > 0) uhNext(id); else uhPrev(id); }
    }, {passive:true});
  });
}
function uhGoto(id, index) {
  var track = document.querySelector('#karusell-' + id + ' .uh-karusell-track');
  if (!track) return;
  uhState[id] = index;
  track.style.transform = 'translateX(-' + (index * 100) + '%)';
}
function uhNext(id) {
  var bilder = document.querySelectorAll('#karusell-' + id + ' .uh-karusell-bild');
  uhGoto(id, (uhState[id] + 1) % bilder.length);
}
function uhPrev(id) {
  var bilder = document.querySelectorAll('#karusell-' + id + ' .uh-karusell-bild');
  uhGoto(id, (uhState[id] - 1 + bilder.length) % bilder.length);
}
function visaFlerUH() {
  document.querySelectorAll('.uh-dold').forEach(function(el) { el.classList.remove('uh-dold'); });
  var wrap = document.getElementById('uh-visa-fler-wrap');
  if (wrap) wrap.style.display = 'none';
}
document.addEventListener('DOMContentLoaded', uhInit);
</script>

@endsection
