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
    <h1 class="undersida-rubrik">{{ $oo_hero_rubrik ?: 'Om oss' }}</h1>
  </div>
</div>

{{-- Intro --}}
<section class="page-sektion">
  <div class="page-inner">
    <div class="om-oss-intro">
      <h2>{{ $oo_intro_rubrik }}</h2>
      <div class="om-oss-intro-text">{!! $oo_intro_text !!}</div>
    </div>

    @if(!empty($oo_blocks))
    <div class="om-oss-grid">
      @foreach($oo_blocks as $block)
        <div class="om-oss-block">
          @if(!empty($block['ikon']))
            <img src="{{ $block['ikon']['url'] }}" alt="{{ $block['rubrik'] }}" style="width:48px;height:48px;margin-bottom:16px;">
          @endif
          <h3>{{ $block['rubrik'] }}</h3>
          <p>{{ $block['text'] }}</p>
        </div>
      @endforeach
    </div>
    @endif
  </div>
</section>

{{-- Värderingar --}}
@if(!empty($oo_values))
<section class="page-sektion" style="background:var(--bg-warm);">
  <div class="page-inner" style="text-align:center;">
    <p class="sektion-eyebrow">{{ strtoupper($oo_values_rubrik) }}</p>
    <div class="om-oss-grid" style="margin-top:0;">
      @foreach($oo_values as $value)
        <div class="om-oss-block" style="border-top:2px solid var(--accent);padding-top:24px;">
          <h3>{{ $value['rubrik'] }}</h3>
          <p>{{ $value['text'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- Team --}}
@if(!empty($oo_team))
<section style="padding:80px 40px;background:#111D33;">
  <div style="max-width:1200px;margin:0 auto;">
    <div class="sektion-header" style="margin-bottom:48px;">
      <span class="sektion-eyebrow-label">Oscars Mäkleri</span>
      <h2 class="sektion-rubrik">{{ $oo_team_rubrik }}</h2>
    </div>
    <div class="maklare-grid">
      @foreach($oo_team as $m)
        <div class="maklare-kort">
          <div class="maklare-kort-bild">
            @if($m->bild)
              <img src="{{ $m->bild }}" alt="{{ $m->namn }}" style="width:100%;height:100%;object-fit:cover;object-position:top;">
            @else
              <div style="width:100%;height:100%;background:#243558;display:flex;align-items:center;justify-content:center;">
                <span style="font-family:var(--font-heading);font-size:48px;font-weight:300;color:rgba(255,255,255,0.3);">{{ strtoupper(substr($m->namn, 0, 1)) }}</span>
              </div>
            @endif
          </div>
          <div class="maklare-kort-info">
            <h3>{{ $m->namn }}</h3>
            <p class="maklare-kort-titel">{{ $m->titel }}</p>
            @if($m->telefon)
              <p><a href="tel:{{ $m->telefon }}">{{ $m->telefon }}</a></p>
            @endif
            @if($m->email)
              <p><a href="mailto:{{ $m->email }}">{{ $m->email }}</a></p>
            @endif
            @if(!empty($m->instagram))
              <p style="margin-top:8px;">
                <a href="{{ $m->instagram }}" target="_blank" class="social-btn" style="padding:6px 12px;font-size:10px;">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
                  Instagram
                </a>
              </p>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

@endsection
