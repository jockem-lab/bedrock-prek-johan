@extends('layouts.app')

@section('content')
@php
$listings_active = new WP_Query([
    'post_type'      => 'fasad_listing',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => [
        ['key' => '_fasad_sold', 'value' => '0', 'compare' => '='],
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
        ['key' => '_fasad_sold', 'value' => '1', 'compare' => '='],
        ['key' => '_fasad_minilist', 'value' => '1', 'compare' => '!='],
    ],
]);
$all_posts = array_merge($listings_active->posts, $listings_sold->posts);

function em_unserialize($raw) {
    if (!is_string($raw)) return $raw;
    $s1 = @unserialize($raw);
    return is_string($s1) ? @unserialize($s1) : $s1;
}

// Testdata om inga objekt finns
$demo_listings = [];
if (empty($all_posts)) {
    $demo_listings = [
        ['address' => 'Sibyllegatan 5', 'area' => 'Östermalm', 'kvm' => 151, 'rum' => 3, 'pris' => '5 034 kr/mån', 'status' => 'visning', 'status_label' => 'VISNING 22/3'],
        ['address' => 'Katarina Bangata 62', 'area' => 'Södermalm', 'kvm' => 68, 'rum' => 3, 'pris' => '4 410 kr/mån', 'status' => 'visning', 'status_label' => 'VISNING 24/3'],
        ['address' => 'Vanadisvägen 31 B', 'area' => 'Vasastan', 'kvm' => 66, 'rum' => 3, 'pris' => '3 689 kr/mån', 'status' => 'kommande', 'status_label' => 'FÖRHANDSVISNING'],
        ['address' => 'Sibyllegatan 5', 'area' => 'Östermalm', 'kvm' => 81, 'rum' => 2, 'pris' => '4 200 kr/mån', 'status' => 'budgivning', 'status_label' => 'BUDGIVNING PÅGÅR'],
    ];
}
@endphp

{{-- Hero --}}
<section class="em-objekt-hero">
  <div class="fdr-hero-slide active" style="background-image:url('/app/uploads/hero/placeholder3.jpg')"></div>
  <div class="fdr-hero-slide" style="background-image:url('/app/uploads/hero/start-hero.jpg')"></div>
  <div class="em-objekt-hero-overlay"></div>
</section>

{{-- Filter --}}
<div class="em-objekt-filter">
  <button class="em-filter-knapp active" data-filter="alla">Alla</button>
  <button class="em-filter-knapp" data-filter="tillsalu">Till salu</button>
  <button class="em-filter-knapp" data-filter="kommande">Kommande</button>
  <button class="em-filter-knapp" data-filter="budgivning">Budgivning</button>
  <button class="em-filter-knapp" data-filter="sald">Sålda</button>
</div>

{{-- Objektgrid --}}
<div class="em-objekt-innehall">
  <div class="em-objekt-grid" id="em-objekt-grid">

    @if(!empty($all_posts))
      @foreach($all_posts as $lp)
        @php
          $pid = $lp->ID;
          $loc = em_unserialize(get_post_meta($pid, '_fasad_location', true));
          $address = ($loc && !empty($loc->address)) ? $loc->address : get_the_title($pid);
          $area = ($loc && !empty($loc->area)) ? $loc->area : (($loc && !empty($loc->city)) ? $loc->city : '');

          $eco = em_unserialize(get_post_meta($pid, '_fasad_economy', true));
          $pris = '';
          if ($eco && !empty($eco->price->primary->amount))
              $pris = number_format($eco->price->primary->amount, 0, ',', ' ') . ' kr';
          $manadskostnad = '';
          if ($eco && !empty($eco->fee->amount))
              $manadskostnad = number_format($eco->fee->amount, 0, ',', ' ') . ' kr/mån';

          $details = em_unserialize(get_post_meta($pid, '_fasad_details', true));
          $kvm = ($details && !empty($details->livingArea)) ? $details->livingArea : '';
          $rum = ($details && !empty($details->rooms)) ? $details->rooms : '';

          $imgs = em_unserialize(get_post_meta($pid, '_fasad_images', true));
          $bilder = [];
          if (is_array($imgs)) {
              foreach ($imgs as $img) {
                  $url = '';
                  foreach ($img->variants ?? [] as $v) {
                      if (($v->type ?? '') === 'large') { $url = $v->path; break; }
                  }
                  if ($url) $bilder[] = $url;
              }
          }

          $is_sold = get_post_meta($pid, '_fasad_sold', true);
          $is_published = get_post_meta($pid, '_fasad_published', true);
          $is_brokered = get_post_meta($pid, '_fasad_firstPublishedAsBrokered', true);

          $visning = em_unserialize(get_post_meta($pid, '_fasad_showings', true));
          $visning_datum = '';
          if ($visning && !empty($visning[0]->date)) {
              $visning_datum = date('j/n', strtotime($visning[0]->date));
          }

          if ($is_sold == '1') {
              $status = 'sald'; $status_label = 'SÅLD';
          } elseif ($visning_datum) {
              $status = 'visning'; $status_label = 'VISNING ' . $visning_datum;
          } elseif ($is_brokered) {
              $status = 'kommande'; $status_label = 'FÖRHANDSVISNING';
          } elseif ($is_published == '1') {
              $status = 'tillsalu'; $status_label = 'TILL SALU';
          } else {
              $status = 'kommande'; $status_label = 'KOMMANDE';
          }
        @endphp
        <div class="em-objekt-kort" data-status="{{ $status }}">
          <a href="{{ home_url('/objekt/' . get_post_field('post_name', $pid)) }}">
            <div class="em-objekt-bild-wrap">
              @if(!empty($bilder))
                @foreach($bilder as $bi => $burl)
                  <div class="em-objekt-bild {{ $bi === 0 ? 'active' : '' }}" style="background-image:url('{{ $burl }}')"></div>
                @endforeach
                @if(count($bilder) > 1)
                  <button class="em-karusell-prev" onclick="emKarusellPrev(event, this)">&#8249;</button>
                  <button class="em-karusell-next" onclick="emKarusellNext(event, this)">&#8250;</button>
                @endif
              @else
                <div class="em-objekt-bild active" style="background:#e8e6e1;"></div>
              @endif
              <div class="em-objekt-status em-status--{{ $status }}">{{ $status_label }}</div>
            </div>
            <div class="em-objekt-info">
              <div class="em-objekt-rad-huvud">
                <span class="em-objekt-adress">{{ $address }}</span>
                <span class="em-objekt-omrade">{{ strtoupper($area) }}</span>
              </div>
              <div class="em-objekt-rad-fakta">
                @if($kvm)<span>{{ $kvm }} kvm</span>@endif
                @if($pris)<span>{{ $pris }}</span>@endif
                @if($manadskostnad)<span>{{ $manadskostnad }}</span>@endif
                @if($rum)<span>{{ $rum }} rum</span>@endif
              </div>
            </div>
          </a>
        </div>
      @endforeach

    @else
      {{-- Demo-kort --}}
      @foreach($demo_listings as $dl)
        <div class="em-objekt-kort" data-status="{{ $dl['status'] }}">
          <div>
            <div class="em-objekt-bild-wrap">
              <div class="em-objekt-bild active" style="background:#e8e6e1;"></div>
              <div class="em-objekt-status em-status--{{ $dl['status'] }}">{{ $dl['status_label'] }}</div>
            </div>
            <div class="em-objekt-info">
              <div class="em-objekt-rad-huvud">
                <span class="em-objekt-adress">{{ $dl['address'] }}</span>
                <span class="em-objekt-omrade">{{ $dl['area'] }}</span>
              </div>
              <div class="em-objekt-rad-fakta">
                <span>{{ $dl['kvm'] }} kvm</span>
                <span>{{ $dl['pris'] }}</span>
                <span>{{ $dl['rum'] }} rum</span>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    @endif

    @php wp_reset_postdata(); @endphp
  </div>
</div>

<script>
function emKarusellPrev(e, btn) {
  e.preventDefault();
  var wrap = btn.closest('.em-objekt-bild-wrap');
  var bilder = wrap.querySelectorAll('.em-objekt-bild');
  var aktiv = wrap.querySelector('.em-objekt-bild.active');
  var idx = Array.from(bilder).indexOf(aktiv);
  bilder[idx].classList.remove('active');
  bilder[(idx - 1 + bilder.length) % bilder.length].classList.add('active');
}
function emKarusellNext(e, btn) {
  e.preventDefault();
  var wrap = btn.closest('.em-objekt-bild-wrap');
  var bilder = wrap.querySelectorAll('.em-objekt-bild');
  var aktiv = wrap.querySelector('.em-objekt-bild.active');
  var idx = Array.from(bilder).indexOf(aktiv);
  bilder[idx].classList.remove('active');
  bilder[(idx + 1) % bilder.length].classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
  var knappar = document.querySelectorAll('.em-filter-knapp');
  var kort = document.querySelectorAll('#em-objekt-grid .em-objekt-kort');

  function filtrera(filter) {
    kort.forEach(function(k) {
      var status = k.getAttribute('data-status');
      if (filter === 'alla') {
        k.style.display = status === 'sald' ? 'none' : '';
      } else {
        k.style.display = status === filter ? '' : 'none';
      }
    });
  }
  filtrera('alla');

  knappar.forEach(function(knapp) {
    knapp.addEventListener('click', function() {
      knappar.forEach(function(k) { k.classList.remove('active'); });
      knapp.classList.add('active');
      filtrera(knapp.getAttribute('data-filter'));
    });
  });
});
</script>
@endsection
