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
      <div class="mosaik-grid">
        @foreach($listings as $i => $listing)
          <a href="{{ home_url('/objekt/' . $listing->slug) }}"
             class="mosaik-kort {{ $i === 0 ? 'mosaik-kort--stor' : '' }}">
            <div class="mosaik-bild" style="background-image:url('{{ $listing->image }}')">
              @if(!$listing->image)
                <div style="width:100%;height:100%;background:#243558;"></div>
              @endif
            </div>
            @if($listing->status)
              <div class="objekt-status objekt-status--{{ $listing->status }}">
                @php echo match($listing->status) {
                  'sald' => 'Såld', 'kommande' => 'Kommande',
                  'tillsalu' => 'Till salu', 'budgivning' => 'Budgivning',
                  default => ucfirst($listing->status)
                }; @endphp
              </div>
            @endif
            <div class="mosaik-info">
              @if($listing->address)
                <div class="mosaik-adress">{{ $listing->address }}</div>
              @endif
              @if($listing->price)
                <div class="mosaik-pris">{{ $listing->price }}</div>
              @endif
              <div class="mosaik-meta">
                @if($listing->rooms) <span>{{ $listing->rooms }}</span> @endif
                @if($listing->area) <span>{{ $listing->area }}</span> @endif
              </div>
            </div>
          </a>
        @endforeach
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
