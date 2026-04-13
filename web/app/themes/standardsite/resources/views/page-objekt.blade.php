@extends('layouts.app')

@section('content')
@php
// Hämta alla fasad_listing-poster
$listings_query = new WP_Query([
    'post_type'      => 'fasad_listing',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
]);

function fasad_unserialize_listing($raw) {
    if (!is_string($raw)) return $raw;
    $s1 = @unserialize($raw);
    return is_string($s1) ? @unserialize($s1) : $s1;
}
@endphp

{{-- Hero med bildspel --}}
<div class="kontakt-hero">
  <div class="kontakt-hero-slide active" style="background-image:url('https://images.unsplash.com/photo-1600585154526-990dced4db0d?w=1600&q=80')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=1600&q=80')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1600&q=80')"></div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner">
    <p class="kontakt-hero-eyebrow">PREK MÄKLERI</p>
    <h1>Hem till salu</h1>
    <p class="kontakt-hero-sub">Linköping och Östergötland</p>
  </div>
</div>

{{-- Filter --}}
<div class="till-salu-filter">
  <div class="filter-knappar">
    <button class="filter-knapp active" data-filter="alla">ALLA</button>
    <button class="filter-knapp" data-filter="kommande">KOMMANDE</button>
    <button class="filter-knapp" data-filter="tillsalu">TILL SALU</button>
    <button class="filter-knapp" data-filter="budgivning">BUDGIVNING PÅGÅR</button>
    <button class="filter-knapp" data-filter="sald">SÅLDA</button>
  </div>
</div>

{{-- Objektgrid --}}
<div class="till-salu-innehall">
  <div class="objekt-grid">
    @while($listings_query->have_posts())
      @php $listings_query->the_post(); $pid = get_the_ID(); @endphp
      @php
        $loc = fasad_unserialize_listing(get_post_meta($pid, '_fasad_location', true));
        $address = ($loc && !empty($loc->address)) ? $loc->address : get_the_title();
        $city    = ($loc && !empty($loc->city)) ? $loc->city : '';

        $eco = fasad_unserialize_listing(get_post_meta($pid, '_fasad_economy', true));
        $price = '';
        if ($eco && !empty($eco->price->primary->amount))
            $price = number_format($eco->price->primary->amount, 0, ',', ' ') . ' kr';

        $imgs_raw = get_post_meta($pid, '_fasad_images', true);
        $imgs = fasad_unserialize_listing($imgs_raw);
        $img_url = '';
        if (is_array($imgs) && !empty($imgs)) {
            foreach ($imgs[0]->variants ?? [] as $v) {
                if (($v->type ?? '') === 'large') { $img_url = $v->path; break; }
            }
        }

        $tp = fasad_unserialize_listing(get_post_meta($pid, '_fasad_descriptionType', true));
        $type = ($tp && !empty($tp->alias) && is_string($tp->alias)) ? strtoupper($tp->alias) : '';

        $status_raw = fasad_unserialize_listing(get_post_meta($pid, '_fasad_status', true));
        // Status är ett array med ett objekt
        $status_obj = is_array($status_raw) ? ($status_raw[0] ?? null) : $status_raw;
        $status = ($status_obj && !empty($status_obj->tag)) ? $status_obj->tag : '';
        $status_label = ($status_obj && !empty($status_obj->alias)) ? strtoupper($status_obj->alias) : strtoupper($status);
      @endphp
      <a href="{{ home_url('/objekt/' . get_post_field('post_name', $pid)) }}" class="objekt-kort-inner" data-status="{{ $status }}">
        <div class="objekt-bild">
          @if($img_url)
            <img src="{{ $img_url }}" alt="{{ $address }}">
          @else
            <div class="objekt-bild-placeholder"></div>
          @endif
          @if($status)
            <div class="objekt-status objekt-status--{{ $status }}">{{ $status_label }}</div>
          @endif
          <div class="objekt-overlay">
            <div class="objekt-info">
              <div class="objekt-adress">{{ $address }}@if($city), {{ $city }}@endif</div>
              @if($price)<div class="objekt-pris">{{ $price }}</div>@endif
              <div class="objekt-meta">
                @if($type)<span>{{ $type }}</span>@endif
              </div>
            </div>
          </div>
        </div>
      </a>
    @endwhile
    @php wp_reset_postdata(); @endphp
  </div>
</div>
@endsection
