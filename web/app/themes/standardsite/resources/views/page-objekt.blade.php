@extends('layouts.app')

@section('content')
@php
// Hämta alla fasad_listing-poster
// Hämta aktiva objekt först, sedan sålda
$listings_active = new WP_Query([
    'post_type'      => 'fasad_listing',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => [
        'relation' => 'AND',
        ['key' => '_fasad_sold',    'value' => '0', 'compare' => '='],
        ['key' => '_fasad_minilist', 'value' => '1', 'compare' => '!='],
    ],
]);
$listings_sold = new WP_Query([
    'post_type'      => 'fasad_listing',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => [
        'relation' => 'AND',
        ['key' => '_fasad_sold',    'value' => '1', 'compare' => '='],
        ['key' => '_fasad_minilist', 'value' => '1', 'compare' => '!='],
    ],
]);
$listings_query = $listings_active;
$all_posts = array_merge($listings_active->posts, $listings_sold->posts);

function fasad_unserialize_listing($raw) {
    if (!is_string($raw)) return $raw;
    $s1 = @unserialize($raw);
    return is_string($s1) ? @unserialize($s1) : $s1;
}
@endphp

{{-- Hero med bildspel --}}
<div class="kontakt-hero">
  <div class="kontakt-hero-slide active" style="background-image:url('{{ content_url('uploads/oscars-hero1.jpg') }}')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('{{ content_url('uploads/oscars-hero2.jpg') }}')"></div>
  <div class="kontakt-hero-slide" style="background-image:url('{{ content_url('uploads/oscars-hero3.jpg') }}')"></div>
  <div class="kontakt-hero-overlay"></div>
  <div class="kontakt-hero-inner">
    <h1 class="undersida-rubrik">{{ $ts_hero_rubrik ?: 'Våra hem' }}</h1>
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
  <div class="objekt-grid" id="objekt-grid">
    @foreach($all_posts as $lp)
      @php $pid = $lp->ID; @endphp
      <div class="objekt-kort">
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

        // Hämta visningar
        $showings_raw = get_post_meta($pid, '_fasad_showings', true);
        $kort_showings = fasad_unserialize_listing($showings_raw);
        if (!is_array($kort_showings)) $kort_showings = [];
        $kort_showings = array_filter($kort_showings, function($s) {
            return !empty($s->endDate) && strtotime($s->endDate) > time();
        });

        // Hämta bud (budgivning pågår)
        $bids_raw = get_post_meta($pid, '_fasad_bids', true);
        $kort_bids = fasad_unserialize_listing($bids_raw);
        $has_bids = is_array($kort_bids) && count($kort_bids) > 0;

        // Nyinkommet = publicerad inom senaste 7 dagarna
        $publish_date = get_post_field('post_date', $pid);
        $is_new = $publish_date && (time() - strtotime($publish_date)) < (7 * 86400);

        // Bestäm primär status (för main badge)
        if ($is_sold == '1') {
            $status = 'sald';
            $status_label = 'SÅLD';
        } elseif ($is_published == '1') {
            $status = 'tillsalu';
            $status_label = 'TILL SALU';
        } else {
            $status = 'tillsalu';
            $status_label = 'TILL SALU';
        }

        // Sekundär status (visas som extra badge på till-salu-objekt)
        $sub_status = '';
        $sub_label = '';
        if ($status === 'tillsalu') {
            if ($has_bids) {
                $sub_status = 'budgivning';
                $sub_label = 'BUDGIVNING';
            } elseif (!empty($kort_showings)) {
                $sub_status = 'visning';
                $sub_label = 'VISNING';
            } elseif ($is_new) {
                $sub_status = 'nyinkommet';
                $sub_label = 'NYINKOMMET';
            }
        }

        // Storlek + rum för kortinfo — hantera både skalärer och objekt
        $sz = fasad_unserialize_listing(get_post_meta($pid, '_fasad_size', true));
        $area = '';
        $rooms = '';
        if ($sz) {
            // Area
            if (!empty($sz->area)) {
                if (is_object($sz->area) && !empty($sz->area->primary->amount)) {
                    $area = $sz->area->primary->amount . ' m²';
                } elseif (is_scalar($sz->area)) {
                    $area = $sz->area . ' ' . ($sz->areaInformation ?? 'm²');
                }
            }
            // Rooms
            if (!empty($sz->rooms)) {
                if (is_object($sz->rooms) && !empty($sz->rooms->primary->amount)) {
                    $rooms = $sz->rooms->primary->amount . ' rum';
                } elseif (is_scalar($sz->rooms)) {
                    $rooms = $sz->rooms . ' ' . ($sz->roomsInformation ?? 'rum');
                }
            }
        }
      @endphp
      <a href="{{ home_url('/objekt/' . get_post_field('post_name', $pid)) }}" class="objekt-kort-inner" data-status="{{ $status }}" data-substatus="{{ $sub_status }}">
        <div class="objekt-bild">
          @if($img_url)
            <img src="{{ $img_url }}" alt="{{ $address }}">
          @else
            <div class="objekt-bild-placeholder"></div>
          @endif
          @if($status === 'sald')
            <div class="objekt-status objekt-status--sald">{{ $status_label }}</div>
          @endif
          @if($sub_status)
            <div class="objekt-substatus objekt-substatus--{{ $sub_status }}">{{ $sub_label }}</div>
          @endif
          <div class="objekt-overlay">
            <div class="objekt-info">
              <div class="objekt-adress">{{ $address }}@if($city), {{ $city }}@endif</div>
              @if($price)<div class="objekt-pris">{{ $price }}</div>@endif
              <div class="objekt-meta">
                @if($area)<span>{{ $area }}</span>@endif
                @if($rooms)<span>{{ $rooms }}</span>@endif
              </div>
            </div>
          </div>
        </div>
      </a>
      </div>
    @endforeach
    @php wp_reset_postdata(); @endphp
  </div>
</div>

@endsection
