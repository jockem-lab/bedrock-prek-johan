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

@endsection
