@extends('layouts.app')

@section('content')

{{-- Hero med overlay-text --}}
<div class="kontakt-hero">
  <div class="kontakt-hero-slide active" style="background-image:url('https://images.unsplash.com/photo-1497366216548-37526070297c?w=1600&q=80')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=1600&q=80')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=1600&q=80')"></div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner">
    <p class="kontakt-hero-eyebrow">PREK MÄKLERI</p>
    <h1>Kontakta oss</h1>
    <p class="kontakt-hero-sub">Vi hjälper dig hitta rätt hem i Linköping och Östergötland</p>
  </div>
</div>

{{-- Mäklare från FasAD --}}
@php
$maklare = [];
$posts = get_posts(['post_type' => 'fasad_listing', 'posts_per_page' => -1]);
$seen = [];
foreach ($posts as $post) {
    $raw = get_post_meta($post->ID, '_fasad_realtors', true);
    $s1 = @unserialize($raw);
    $realtors = is_string($s1) ? @unserialize($s1) : $s1;
    if (!is_array($realtors)) continue;
    foreach ($realtors as $r) {
        $key = ($r->firstname ?? '') . ($r->lastname ?? '');
        if (!$key || in_array($key, $seen)) continue;
        $seen[] = $key;
        $maklare[] = $r;
    }
}
@endphp

@if(!empty($maklare))
<section class="kontakt-maklare-sektion">
  <div class="page-inner">
    <p class="sektion-eyebrow">VÅRA MÄKLARE</p>
    <div class="maklare-grid">
      @foreach($maklare as $m)
      <div class="maklare-kort">
        @php
          $bild = '';
          if (!empty($m->image)) {
              $bild = is_string($m->image) ? $m->image : ($m->image->path ?? '');
          }
        @endphp
        @if($bild)
          <div class="maklare-kort-bild">
            <img src="{{ $bild }}" alt="{{ $m->firstname ?? '' }} {{ $m->lastname ?? '' }}">
          </div>
        @else
          <div class="maklare-kort-bild maklare-kort-bild--placeholder"></div>
        @endif
        <div class="maklare-kort-info">
          <h3>{{ strtoupper(($m->firstname ?? '') . ' ' . ($m->lastname ?? '')) }}</h3>
          @if(!empty($m->title))<p class="maklare-kort-titel">{{ $m->title }}</p>@endif
          @if(!empty($m->email))<p><a href="mailto:{{ $m->email }}">{{ $m->email }}</a></p>@endif
          @if(!empty($m->cellphone))<p><a href="tel:{{ $m->cellphone }}">{{ $m->cellphone }}</a></p>@endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- Kontaktinfo + formulär --}}
<section class="page-sektion kontakt-sektion-ljus">
  <div class="page-inner">
    <div class="kontakt-grid">
      <div class="kontakt-info">
        <p class="sektion-eyebrow">HITTA OSS</p>
        <div class="kontakt-info-block">
          <p class="kontakt-info-label">Adress</p>
          <p>Storgatan 1<br>582 24 Linköping</p>
        </div>
        <div class="kontakt-info-block">
          <p class="kontakt-info-label">Telefon</p>
          <p><a href="tel:+4613123456">013-12 34 56</a></p>
        </div>
        <div class="kontakt-info-block">
          <p class="kontakt-info-label">E-post</p>
          <p><a href="mailto:info@prek.se">info@prek.se</a></p>
        </div>
        <div class="kontakt-info-block">
          <p class="kontakt-info-label">Öppettider</p>
          <p>Måndag–Fredag: 09–17<br>Lördag: 10–14<br>Söndag: Stängt</p>
        </div>
      </div>
      <div class="kontakt-formular">
        <p class="sektion-eyebrow">SKICKA MEDDELANDE</p>
        <div class="kontakt-form">
          <div class="form-group">
            <label for="namn">Namn</label>
            <input type="text" id="namn" placeholder="Ditt namn">
          </div>
          <div class="form-group">
            <label for="email">E-post</label>
            <input type="email" id="email" placeholder="din@epost.se">
          </div>
          <div class="form-group">
            <label for="telefon">Telefon</label>
            <input type="tel" id="telefon" placeholder="070-123 45 67">
          </div>
          <div class="form-group">
            <label for="meddelande">Meddelande</label>
            <textarea id="meddelande" rows="5" placeholder="Hur kan vi hjälpa dig?"></textarea>
          </div>
          <button type="button" class="btn-primary">Skicka meddelande</button>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
