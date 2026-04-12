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

{{-- Hero --}}
<div class="till-salu-hero">
  <h1>Hem till salu</h1>
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
        $status = ($status_raw && !empty($status_raw->alias)) ? $status_raw->alias : '';
        $status_label = match($status) {
            'for_sale' => 'TILL SALU',
            'sold' => 'SÅLD',
            'bidding' => 'BUDGIVNING PÅGÅR',
            'coming' => 'KOMMANDE',
            default => strtoupper($status),
        };
      @endphp
      <a href="{{ home_url('/objekt/' . get_post_field('post_name', $pid)) }}" class="objekt-kort">
        <div class="objekt-kort-bild" @if($img_url) style="background-image:url('{{ $img_url }}')" @endif>
          @if($status)
            <span class="objekt-status objekt-status--{{ $status }}">{{ $status_label }}</span>
          @endif
        </div>
        <div class="objekt-kort-info">
          <div class="objekt-adress">{{ $address }}@if($city), {{ $city }}@endif</div>
          @if($price)<div class="objekt-pris">{{ $price }}</div>@endif
          @if($type)<div class="objekt-typ">{{ $type }}</div>@endif
        </div>
      </a>
    @endwhile
    @php wp_reset_postdata(); @endphp
  </div>
</div>
@endsection
