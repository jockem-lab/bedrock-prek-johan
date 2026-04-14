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
  <div class="kontakt-hero-slide active" style="background-image:url('{{ content_url('uploads/hero1.jpg') }}')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('{{ content_url('uploads/hero2.jpg') }}')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('{{ content_url('uploads/hero3.jpg') }}')"></div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner">
    <p class="kontakt-hero-eyebrow">PREK MÄKLERI</p>
    <h1>{{ $ts_hero_rubrik }}</h1>
    <p class="kontakt-hero-sub">{{ $ts_hero_underrubrik }}</p>
  </div>
</div>

{{-- Filter --}}
<div class="till-salu-filter">
  <div class="filter-knappar">
    @foreach($ts_filter_knappar as $i => $knapp)
      <button class="filter-knapp {{ $i === 0 ? 'active' : '' }}" data-filter="{{ $knapp['filter'] }}">{{ $knapp['text'] }}</button>
    @endforeach
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

        $is_sold = get_post_meta($pid, '_fasad_sold', true);
        $is_published = get_post_meta($pid, '_fasad_published', true);
        $is_brokered = get_post_meta($pid, '_fasad_firstPublishedAsBrokered', true);
        if ($is_sold == '1') {
            $status = 'sald';
            $status_label = 'SÅLD';
        } elseif ($is_published == '1' && $is_brokered) {
            $status = 'kommande';
            $status_label = 'KOMMANDE';
        } elseif ($is_published == '1') {
            $status = 'tillsalu';
            $status_label = 'TILL SALU';
        } else {
            $status = '';
            $status_label = '';
        }
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
