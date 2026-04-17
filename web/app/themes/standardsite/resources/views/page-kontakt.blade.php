@extends('layouts.app')

@section('content')

{{-- Hero --}}
<div class="kontakt-hero">
  <div class="kontakt-hero-slide active" style="background-image:url('{{ content_url('uploads') }}/oscars-hero1.jpg')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('{{ content_url('uploads') }}/oscars-hero2.jpg')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('{{ content_url('uploads') }}/oscars-hero3.jpg')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('{{ content_url('uploads') }}/oscars-hero4.jpg')"></div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner">
    <h1 class="undersida-rubrik">{{ $k_hero_rubrik ?: 'Kontakt' }}</h1>
  </div>
</div>

{{-- Kontaktinfo + Formulär --}}
<section class="page-sektion">
  <div class="page-inner">
    <div class="kontakt-layout">

      {{-- Vänster: info --}}
      <div class="kontakt-info-col">
        <h2>{{ $k_intro_rubrik }}</h2>
        @if($k_intro_text)
          <p>{{ $k_intro_text }}</p>
        @endif

        <div class="kontakt-detaljer">
          @if($site_phone)
            <div class="kontakt-rad">
              <span class="kontakt-label">Telefon</span>
              <a href="tel:{{ preg_replace('/[^0-9+]/', '', $site_phone) }}">{{ $site_phone }}</a>
            </div>
          @endif
          @if($site_email)
            <div class="kontakt-rad">
              <span class="kontakt-label">E-post</span>
              <a href="mailto:{{ $site_email }}">{{ $site_email }}</a>
            </div>
          @endif
          @if($site_address)
            <div class="kontakt-rad">
              <span class="kontakt-label">Adress</span>
              <span>{{ $site_address }}@if($site_city), {{ $site_city }}@endif</span>
            </div>
          @endif
          @if($site_opening_hours)
            <div class="kontakt-rad">
              <span class="kontakt-label">Öppettider</span>
              <span>{!! nl2br(e($site_opening_hours)) !!}</span>
            </div>
          @endif
        </div>

        <div style="margin-top:32px;padding-top:32px;border-top:0.5px solid rgba(255,255,255,0.08);">
          <p style="font-family:var(--font-body);font-size:14px;color:rgba(255,255,255,0.6);line-height:1.7;margin-bottom:16px;">Letar du efter din nästa bostad? Anmäl dig till vårt spekulantregister så hör vi av oss när rätt objekt dyker upp.</p>
          <button onclick="document.getElementById('spekulant-modal').style.display='flex'"
                  class="btn-primary" style="cursor:pointer;border:none;">
            Anmäl dig som spekulant
          </button>
        </div>
      </div>

      {{-- Höger: formulär --}}
      <div class="kontakt-form-col">
        <h3>{{ $k_form_rubrik }}</h3>
        @if($k_form_text)
          <p style="margin-bottom:24px;color:var(--text-mid);">{{ $k_form_text }}</p>
        @endif

        @if(request('success') == '1')
          <div class="kontakt-success">Tack! Ditt meddelande har skickats.</div>
        @endif
        @if(request('error') == '1')
          <div class="kontakt-error">Något gick fel. Fyll i alla obligatoriska fält och försök igen.</div>
        @endif

        <form class="kontakt-formulär" method="POST" action="{{ home_url('/kontakt-skicka') }}">
          @csrf
          <input type="hidden" name="mottagare" value="{{ $k_form_mottagare }}">

          <div class="form-rad">
            <label for="namn">Namn *</label>
            <input type="text" id="namn" name="namn" required placeholder="Ditt namn">
          </div>

          <div class="form-rad">
            <label for="email">E-post *</label>
            <input type="email" id="email" name="email" required placeholder="din@email.se">
          </div>

          <div class="form-rad">
            <label for="telefon">Telefon</label>
            <input type="tel" id="telefon" name="telefon" placeholder="070-123 45 67">
          </div>

          <div class="form-rad">
            <label for="meddelande">Meddelande *</label>
            <textarea id="meddelande" name="meddelande" required rows="5" placeholder="Hur kan vi hjälpa dig?"></textarea>
          </div>

          <button type="submit" class="btn-primary" style="width:100%;">Skicka meddelande</button>
        </form>
      </div>

    </div>
  </div>
</section>

{{-- Mäklargrid --}}
@if(!empty($realtors))
<section class="kontakt-maklare-sektion" style="background:var(--bg-warm);">
  <div class="page-inner">
    <p class="sektion-eyebrow">VÅRA MÄKLARE</p>
    <div class="maklare-grid">
      @foreach($realtors as $realtor)
        <div class="maklare-kort">
          @if(!empty($realtor['image']))
            <img src="{{ $realtor['image'] }}" alt="{{ $realtor['name'] }}">
          @endif
          <h4>{{ $realtor['name'] }}</h4>
          @if(!empty($realtor['title']))
            <p class="maklare-titel">{{ $realtor['title'] }}</p>
          @endif
          @if(!empty($realtor['phone']))
            <a href="tel:{{ $realtor['phone'] }}">{{ $realtor['phone'] }}</a>
          @endif
          @if(!empty($realtor['email']))
            <a href="mailto:{{ $realtor['email'] }}">{{ $realtor['email'] }}</a>
          @endif
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- Karta --}}
@if($k_visa_karta && $k_karta_embed)
<div class="kontakt-karta">
  {!! $k_karta_embed !!}
</div>
@endif

{{-- Spekulant modal --}}
<div id="spekulant-modal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(10,18,35,0.85);align-items:center;justify-content:center;">
  <div style="background:#1B2A4A;max-width:520px;width:90%;padding:48px;position:relative;border:0.5px solid rgba(255,255,255,0.1);">
    <button onclick="document.getElementById('spekulant-modal').style.display='none'"
            style="position:absolute;top:20px;right:24px;background:none;border:none;color:rgba(255,255,255,0.5);font-size:24px;cursor:pointer;line-height:1;">&times;</button>

    <span class="sektion-eyebrow-label">Spekulantregister</span>
    <h2 style="font-family:var(--font-heading);font-size:32px;font-weight:300;color:#fff;margin:12px 0 8px;letter-spacing:-0.01em;">Anmäl ditt intresse</h2>
    <p style="font-family:var(--font-body);font-size:14px;color:rgba(255,255,255,0.6);line-height:1.7;margin-bottom:32px;">Berätta vad du söker så kontaktar vi dig när rätt objekt dyker upp.</p>

    @if(request('spekulant') === 'success')
      <div style="background:rgba(200,169,126,0.15);border:0.5px solid var(--accent);padding:16px;color:var(--accent);font-size:14px;margin-bottom:24px;">
        Tack! Vi hör av oss när rätt objekt dyker upp.
      </div>
    @endif

    <form method="POST" action="{{ home_url('/kontakt') }}">
      @php echo wp_nonce_field('spekulant_form', 'spekulant_nonce', true, false); @endphp
      <input type="hidden" name="form_type" value="spekulant">

      <div style="display:flex;flex-direction:column;gap:16px;">
        <div>
          <label style="display:block;font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.45);margin-bottom:8px;">Namn</label>
          <input type="text" name="spekulant_namn" required placeholder="Ditt namn"
                 style="width:100%;padding:12px 16px;background:#243558;border:0.5px solid rgba(255,255,255,0.1);color:#fff;font-family:var(--font-body);font-size:14px;outline:none;box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block;font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.45);margin-bottom:8px;">E-post</label>
          <input type="email" name="spekulant_email" required placeholder="din@email.se"
                 style="width:100%;padding:12px 16px;background:#243558;border:0.5px solid rgba(255,255,255,0.1);color:#fff;font-family:var(--font-body);font-size:14px;outline:none;box-sizing:border-box;">
        </div>
        <div>
          <label style="display:block;font-size:10px;font-weight:500;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.45);margin-bottom:8px;">Vad söker du?</label>
          <textarea name="spekulant_soker" rows="4" placeholder="Beskriv vad du söker — område, storlek, budget..."
                    style="width:100%;padding:12px 16px;background:#243558;border:0.5px solid rgba(255,255,255,0.1);color:#fff;font-family:var(--font-body);font-size:14px;outline:none;resize:vertical;box-sizing:border-box;"></textarea>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;padding:14px;text-align:center;cursor:pointer;">
          Skicka intresseanmälan
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Öppna modal automatiskt om success
@if(request('spekulant') === 'success')
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('spekulant-modal').style.display = 'flex';
  });
@endif

// Stäng vid klick utanför
document.getElementById('spekulant-modal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>

@endsection
