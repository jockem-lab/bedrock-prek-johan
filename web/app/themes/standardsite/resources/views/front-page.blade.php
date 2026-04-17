@extends('layouts.app')

@section('content')

{{-- Objekt-sektion --}}
<section class="objekt-sektion">
  <div class="objekt-inner">
    <div class="sektion-header">
      <span class="sektion-eyebrow-label">Fastigheter</span>
      <h2 class="sektion-rubrik">{{ $fp_listings_rubrik ?? 'Aktuella objekt' }}</h2>
    </div>
    @if(!empty($listings))
      <div class="mosaik-v2">

        {{-- Rad 1: stor vänster + två höger --}}
        <div class="mosaik-v2-rad mosaik-v2-rad--1">
          @if(isset($listings[0]))
            @include('partials.mosaik-kort', ['listing' => $listings[0], 'stor' => true])
          @endif
          <div class="mosaik-v2-col">
            @if(isset($listings[1]))
              @include('partials.mosaik-kort', ['listing' => $listings[1], 'stor' => false])
            @endif
            @if(isset($listings[2]))
              @include('partials.mosaik-kort', ['listing' => $listings[2], 'stor' => false])
            @endif
          </div>
        </div>

        {{-- Rad 2: CTA + två mitten + en stor höger --}}
        <div class="mosaik-v2-rad mosaik-v2-rad--2">
          <div class="mosaik-v2-col">
            <a href="{{ home_url('/kontakt') }}" class="mosaik-cta">
              <span class="sektion-eyebrow-label">Oscars Mäkleri</span>
              <h3>Klicka här för att värdera din bostad</h3>
              <span class="mosaik-cta-arrow">→</span>
            </a>
            @if(isset($listings[3]))
              @include('partials.mosaik-kort', ['listing' => $listings[3], 'stor' => false])
            @endif
          </div>
          <div class="mosaik-v2-col">
            @if(isset($listings[4]))
              @include('partials.mosaik-kort', ['listing' => $listings[4], 'stor' => false])
            @endif
            @if(isset($listings[5]))
              @include('partials.mosaik-kort', ['listing' => $listings[5], 'stor' => false])
            @endif
          </div>
          @if(isset($listings[6]))
            @include('partials.mosaik-kort', ['listing' => $listings[6], 'stor' => true])
          @endif
        </div>

      </div>
      <div style="text-align:center;margin-top:48px;">
        <a href="{{ home_url('/objekt') }}" class="btn-primary">Se alla objekt</a>
      </div>
    @else
      <p class="objekt-tomma">Inga objekt tillgängliga just nu.</p>
    @endif

  </div>
</section>



{{-- Journal-sektion --}}
@php
  $journal_query = new WP_Query([
    'post_type'      => 'journal',
    'posts_per_page' => 4,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);
  $journal_artiklar = $journal_query->posts;
@endphp

@if(!empty($journal_artiklar))
<section style="padding:80px 40px;background:#111D33;">
  <div style="max-width:1200px;margin:0 auto;">
    <div class="sektion-header" style="margin-bottom:48px;">
      <span class="sektion-eyebrow-label">Oscars Mäkleri</span>
      <h2 class="sektion-rubrik">Journal</h2>
    </div>
    <div class="journal-grid">
      @foreach($journal_artiklar as $i => $jp)
        @php
          $jbild = get_field('j_hero_bild', $jp->ID);
          $jthumb = get_the_post_thumbnail_url($jp->ID, 'large');
          if (is_array($jbild)) {
            $jimg = $jbild['url'] ?? $jthumb;
          } elseif (is_numeric($jbild)) {
            $jimg = wp_get_attachment_image_url($jbild, 'large') ?: $jthumb;
          } else {
            $jimg = $jthumb;
          }
          $jkat = get_field('j_kategori', $jp->ID);
          $jmin = get_field('j_lasttid', $jp->ID);
          $jtyp = get_field('j_hero_typ', $jp->ID);
        @endphp
        <a href="{{ home_url('/journal/' . $jp->post_name) }}"
           class="journal-kort {{ $i === 0 ? 'journal-kort--featured' : '' }}">
          <div class="journal-bild">
            @if($jtyp === 'video')
              <div class="journal-video-badge">▶</div>
            @endif
            <div class="journal-bild-inner" style="background-image:url('{{ $jimg }}');background-color:#243558;"></div>
          </div>
          <div class="journal-info">
            @if($jkat)<span class="journal-kategori">{{ $jkat }}</span>@endif
            <h3 class="journal-titel">{{ $jp->post_title }}</h3>
            @if($jmin)<span class="journal-meta">{{ $jmin }} min</span>@endif
          </div>
        </a>
      @endforeach
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a href="{{ home_url('/journal') }}" class="btn-primary">Se alla artiklar</a>
    </div>
  </div>
</section>
@endif

@endsection
